<?php

namespace Urbit\Shipping\Observer\Actions;

use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Urbit\Shipping\Model\Urbit\StoreApi;
use Psr\Log\LoggerInterface;
use Urbit\Shipping\Helper\EmailSender;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;

/**
 * Class UpdateCheckout
 *
 * @package Urbit\Shipping\Observer\Actions
 */
class UpdateCheckout extends AbstractCheckoutObserver
{
    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var UrbitConfigHelper
     */
    protected $urbitConfigHelper;

    /**
     * UpdateCheckout constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param StoreApi $storeApi
     * @param UrbitConfigHelper $urbitConfigHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        StoreApi $storeApi,
        UrbitConfigHelper $urbitConfigHelper,
        EmailSender $emailSender,
        LoggerInterface $logger
    ) {
        $this->emailSender = $emailSender;
        $this->urbitConfigHelper = $urbitConfigHelper;

        parent::__construct($checkoutSession, $storeApi, $logger);
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        $configStatusTrigger = $this->urbitConfigHelper->getOrderTriggerStatus();

        $checkoutId = $order->getUrbitCheckoutId();
        $isTriggered = $order->getUrbitTriggered();

        if ($checkoutId && $isTriggered == 'false') {
            $orderStatus = $order->getState();

            if ($orderStatus == $configStatusTrigger) {
                $this->sendUpdateCheckout($order);
                $order->setData('urbit_triggered', 'true');
                $order->save();
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

        $responseObj = $this->api->updateCheckout($checkoutId, $requestArray);
        $response = $responseObj->getResponse();

        //$this->logger->debug("RESULT: " . print_r($responseObj, true));

        /**
         * Send email if order was not created
         */
        $recipientEmail = $this->urbitConfigHelper->getFailureEmailRecipient();

        if ((int)$responseObj->getHttpCode() != 204 && $recipientEmail) {
            $orderId = $order->getEntityId();

            $errorMessage = isset($response['errors'][0]['message']) ?
                $response['errors'][0]['message'] :
                (isset($response['errors']['message']) ? $response['errors']['message'] : 'Error');

            $this->emailSender->sendOrderFailureReport($recipientEmail, $orderId, $errorMessage);
        } else {
            $order->setData('urbit_done', true);
            $order->save();
        }
    }
}