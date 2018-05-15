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
use Psr\Log\LoggerInterface;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;

/**
 * Class UrbitCarrier
 *
 * @package Urbit\Shipping\Model\Carrier
 */
class UrbitCarrier extends AbstractCarrier implements CarrierInterface
{
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
     * UrbitCarrier constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
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
        StoreManagerInterface $storeManager,
        UrbitConfigHelper $urbitConfigHelper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_storeManager = $storeManager;
        $this->_urbitConfigHelper = $urbitConfigHelper;

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

        if (!$this->getConfigFlag('active') || !$configModuleStatus) {
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
}