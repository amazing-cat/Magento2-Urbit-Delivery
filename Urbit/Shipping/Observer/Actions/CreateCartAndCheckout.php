<?php

namespace Urbit\Shipping\Observer\Actions;

use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Urbit\Shipping\Model\Urbit\StoreApi;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CreateCartAndCheckout
 *
 * @package Urbit\Shipping\Observer\Actions
 */
class CreateCartAndCheckout extends AbstractCheckoutObserver
{
    /**
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CreateCart constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param StoreApi $storeApi
     * @param TaxCalculation $taxCalculation
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        StoreApi $storeApi,
        TaxCalculation $taxCalculation,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($checkoutSession, $storeApi, $logger);

        $this->taxCalculation = $taxCalculation;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $cartId = $this->createCart();

        if ($cartId) {
            $this->createCheckout($cartId);
        }
    }

    /**
     * Create cart in Urbit system
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createCart()
    {
        $cart = $this->checkoutSession->getQuote();
        $cartItems = $cart->getAllItems();

        $result = [];
        $apiItems = [];

        foreach ($cartItems as $item) {
            $product = $item->getProduct();

            $apiItems[] = [
                'sku'      => $product->getSku(),
                'name'     => $product->getName(),
                'vat'      => $this->priceFormat($this->getTaxPercent($product->getTaxClassId())),
                'price'    => $this->priceFormat($product->getPrice()),
                'quantity' => $item->getQty(),
            ];
        }

        $result['items'] = $apiItems;

        $responseObj = $this->api->createCart($result);
        $response = $responseObj->getResponse();

        // $this->logger->debug("Create Cart response" . print_r($responseObj, true));

        //save cart id to session
        if (isset($response['id'])) {
            $this->checkoutSession->setCartIdFromApi($response['id']);
        }

        //$this->logger->debug("Session cart ID: " . print_r($this->checkoutSession->getCartIdFromApi(), true));

        return isset($response['id']) ? $response['id'] : null;
    }

    /**
     * Create checkout in Urbit system
     *
     * @param $cartId
     */
    protected function createCheckout($cartId)
    {
        $bodyForRequest = [
            'cart_reference' => $cartId
        ];

        $responseObj = $this->api->createCheckout($bodyForRequest);
        $response = $responseObj->getResponse();

        $this->logger->debug("Create Checkout response" . print_r($responseObj, true));

        if (isset($response['id'])) {
            $this->checkoutSession->setCheckoutIdFromApi($response['id']);
        }

        //$this->logger->debug("Session checkout ID: " . print_r($this->checkoutSession->getCheckoutIdFromApi(), true));
    }

    /**
     * Return tax percent for product class
     *
     * @param $productClassId
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTaxPercent($productClassId)
    {
        $store = $this->storeManager->getStore();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $store);

        return $this->taxCalculation->getRate($request->setProductClassId($productClassId));
    }

    /**
     * Format price for Urb-it API (ex. 59.99 => 5999 integer)
     *
     * @param $price
     *
     * @return float|int
     */
    protected function priceFormat($price)
    {
        return number_format((float)$price, 2, '.', '') * 100;
    }

}