<?php

namespace Urbit\Shipping\Observer\Actions;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Urbit\Shipping\Model\Urbit\StoreApi;

/**
 * Class AbstractCheckoutObserver
 *
 * @package Urbit\Shipping\Observer\Actions
 */
abstract class AbstractCheckoutObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var StoreApi
     */
    protected $api;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Action execute method
     *
     * @param Observer $observer
     */
    abstract public function execute(Observer $observer);

    /**
     * AbstractCheckoutObserver constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param StoreApi $storeApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        StoreApi $storeApi,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->api = $storeApi;
        $this->logger = $logger;
    }
}