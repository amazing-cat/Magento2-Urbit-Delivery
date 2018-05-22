<?php

namespace Urbit\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class UrbitCarrier
 *
 * @package Urbit\Shipping\Model\Carrier
 */
class UrbitCarrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * Max weight in cart (kg)
     */
    const PACKAGE_MAX_WEIGHT = 10;

    /**
     * Max products size dimension in cart (m3)
     */
    const PACKAGE_MAX_DIMENSION_SIZE = 0.25;

    /**
     * @var string
     */
    protected $_code = 'urbit';

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrbitConfigHelper
     */
    protected $_urbitConfigHelper;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * UrbitCarrier constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param UrbitConfigHelper $urbitConfigHelper
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        UrbitConfigHelper $urbitConfigHelper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_storeManager = $storeManager;
        $this->_urbitConfigHelper = $urbitConfigHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Return allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['urbit' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|Result
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        $configModuleStatus = $this->_urbitConfigHelper->getModuleStatus();

        $cartQuote = $this->_checkoutSession->getQuote();

        if (!$this->getConfigFlag('active') || !$configModuleStatus || !$this->checkCarrierAvailability($cartQuote)) {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();

        $configUrbitServiceName = $this->_urbitConfigHelper->getUrbitServiceName();

        $method->setCarrier('urbit');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('urbit');
        $method->setMethodTitle($configUrbitServiceName ?: $this->getConfigData('name'));

        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

        $defaultPrice = (double)$this->getConfigData('price');

        //get delivery price for store's currency from module config page
        switch ($currencyCode) {
            case 'EUR':
                $price = $this->_urbitConfigHelper->getDeliveryPrice('EUR') ?: $defaultPrice;
                break;
            case 'SEK':
                $price = $this->_urbitConfigHelper->getDeliveryPrice('SEK') ?: $defaultPrice;
                break;
            case 'GBP':
                $price = $this->_urbitConfigHelper->getDeliveryPrice('GBP') ?: $defaultPrice;
                break;
            default:
                $price = $defaultPrice;
                break;
        }

        $method->setPrice($price);
        $method->setCost($price);

        $result->append($method);

        return $result;
    }

    /**
     * Checks urbit delivery's availability
     *
     * @param $cartQuote
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkCarrierAvailability($cartQuote)
    {
        $isAllowed = true;

        if (!$this->checkAllowedProducts($cartQuote)) {
            $isAllowed = false;
        }

        $maxWeight = $this->_urbitConfigHelper->getMaxWeight();
        $maxDimensionSize = $this->_urbitConfigHelper->getMaxDimensionSize();

        if (!$this->checkCartItemsWeight($maxWeight > 0 ? $maxWeight : self::PACKAGE_MAX_WEIGHT, $cartQuote)) {
            $isAllowed = false;
        }

        if (!$this->checkCartItemsSize($maxDimensionSize > 0 ? $maxDimensionSize : self::PACKAGE_MAX_DIMENSION_SIZE,
            $cartQuote)) {
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * Checks that Cart's items weight <= $maxWeight
     *
     * @param $maxWeight
     * @param $cartQuote
     *
     * @return bool
     */
    protected function checkCartItemsWeight($maxWeight, $cartQuote)
    {
        $cartWeight = 0;

        foreach ($cartQuote->getAllItems() as $item) {
            $cartWeight += (float)$item->getWeight() * (int)$item->getQty();
        }

        return $maxWeight >= $cartWeight;
    }

    /**
     * Checks that Cart's items dimension size <= maxDimensionSize
     *
     * @param $maxDimensionSize
     * @param $cartQuote
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkCartItemsSize($maxDimensionSize, $cartQuote)
    {
        $cartDimensionSize = 0;

        //get chosen in config width, height, length attributes
        $useDimensions = $this->_urbitConfigHelper->getUseDimensions();
        $widthAttributeCode = $this->_urbitConfigHelper->getWidthAttributeCode();
        $heightAttributeCode = $this->_urbitConfigHelper->getHeightAttributeCode();
        $lengthAttributeCode = $this->_urbitConfigHelper->getLengthAttributeCode();

        if ($useDimensions && $widthAttributeCode && $heightAttributeCode && $lengthAttributeCode) {
            foreach ($cartQuote->getAllItems() as $item) {
                $productId = $item->getProductId();
                $product = $this->_productRepository->getById($productId);

                //get product's width, height, length attribute values
                $productWidth = (float)$product->getData($widthAttributeCode);
                $productHeight = (float)$product->getData($heightAttributeCode);
                $productLength = (float)$product->getData($lengthAttributeCode);

                if ($productWidth > 0 && $productHeight > 0 && $productLength > 0) {
                    $cartDimensionSize += $productWidth * $productHeight * $productLength * (int)$item->getQty();
                }
            }
        }

        return $maxDimensionSize >= $cartDimensionSize;
    }

    /**
     * Check that all products in cart are available for Urb-it
     *
     * @param $cartQuote
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkAllowedProducts($cartQuote)
    {
        $available = true;

        //if all products in store are available for urbit => return true
        $allowedProductsConfigValue = $this->_urbitConfigHelper->getAllowedProducts();

        if ($allowedProductsConfigValue == 'all') {
            return true;
        }

        foreach ($cartQuote->getAllItems() as $item) {
            $productId = $item->getProductId();
            $product = $this->_productRepository->getById($productId);
            if ($product->getTypeId() !== 'simple') continue;
            $productUrbitAvailability = $product->getData('available_for_urbit');

            if (!$productUrbitAvailability) {
                $available = false;
            }
        }

        return $available;
    }
}