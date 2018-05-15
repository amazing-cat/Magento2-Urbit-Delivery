<?php

namespace Urbit\Shipping\Cron;

use DateTime;
use DateTimeZone;
use Urbit\Shipping\Model\Urbit\StoreApi;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;
use Urbit\Shipping\Helper\EmailSender;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateCheckouts
 *
 * @package Urbit\Shipping\Cron
 */
class UpdateCheckouts
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var StoreApi
     */
    protected $_api;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var UrbitConfigHelper
     */
    protected $_urbitConfigHelper;

    /**
     * @var EmailSender
     */
    protected $_emailSender;

    /**
     * UpdateCheckouts constructor.
     *
     * @param StoreApi $storeApi
     * @param CollectionFactory $orderCollectionFactory
     * @param UrbitConfigHelper $urbitConfigHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreApi $storeApi,
        CollectionFactory $orderCollectionFactory,
        UrbitConfigHelper $urbitConfigHelper,
        EmailSender $emailSender,
        LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_api = $storeApi;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_urbitConfigHelper = $urbitConfigHelper;
        $this->_emailSender = $emailSender;
    }

    /**
     * Check orders for checkout updating
     *
     * execute by cron
     */
    public function execute()
    {
        $orderCollection = $this->_orderCollectionFactory->create()->addFieldToSelect(['*']);

        //get current timestamp
        $nowTime = new DateTime(null, new DateTimeZone("UTC"));
        $nowTimestamp = $nowTime->getTimestamp();

        foreach ($orderCollection as $order) {
            $isTriggered = $order->getUrbitTriggered();

            if (isset($isTriggered) && $isTriggered == 'false') {
                $orderUpdateCheckoutTime = $order->getUrbitUpdateCheckoutTime();

                if (isset($orderUpdateCheckoutTime) && $orderUpdateCheckoutTime != "" && (int)$orderUpdateCheckoutTime <= $nowTimestamp) {
                    $this->sendUpdateCheckout($order);
                    $order->setData('urbit_triggered', 'true');
                    $order->save();
                }
            }
        }
    }

    /**
     * Update checkout information by PUT request to Urb-it API
     *
     * @param $order
     */
    public function sendUpdateCheckout($order)
    {
        $checkoutId = $order->getUrbitCheckoutId();

        if (!$checkoutId) {
            return;
        }

        $requestArray = [
            'delivery_time' => $order->getUrbitDeliveryTime(),
            'message'       => $order->getUrbitMessage(),
            'recipient'     => [
                'first_name'   => $order->getUrbitFirstName(),
                'last_name'    => $order->getUrbitLastName(),
                'address_1'    => $order->getUrbitStreet(),
                'address_2'    => "",
                'city'         => $order->getUrbitCity(),
                'postcode'     => $order->getUrbitPostcode(),
                'phone_number' => $order->getUrbitPhoneNumber(),
                'email'        => $order->getUrbitEmail()
            ]
        ];

        $responseObj = $this->_api->updateCheckout($checkoutId, $requestArray);
        $response = $responseObj->getResponse();

        //$this->_logger->debug("RESULT (SENDED BY CRON): " . print_r($responseObj, true));

        /**
         * Send email if order was not created
         */
        $recipientEmail = $this->_urbitConfigHelper->getFailureEmailRecipient();

        if ((int)$responseObj->getHttpCode() != 204 && $recipientEmail) {
            $orderId = $order->getEntityId();

            $errorMessage = isset($response['errors'][0]['message']) ?
                $response['errors'][0]['message'] :
                (isset($response['errors']['message']) ? $response['errors']['message'] : 'Error');

            $this->_emailSender->sendOrderFailureReport($recipientEmail, $orderId, $errorMessage);
        }
    }

}