<?php

namespace Urbit\Shipping\Observer\Actions;

use DateTime;
use DateTimeZone;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;
use Urbit\Shipping\Helper\Date as UrbitDateHelper;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Urbit\Shipping\Model\Urbit\StoreApi;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SaveOrderInfo
 *
 * @package Urbit\Shipping\Observer\Actions
 */
class SaveOrderInfo extends AbstractCheckoutObserver
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var UrbitConfigHelper
     */
    protected $urbitConfigHelper;

    /**
     * @var UrbitDateHelper
     */
    protected $urbitDateHelper;

    /**
     * SaveOrderInfo constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param StoreApi $storeApi
     * @param UrbitConfigHelper $urbitConfigHelper
     * @param UrbitDateHelper $urbitDateHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        StoreApi $storeApi,
        UrbitConfigHelper $urbitConfigHelper,
        UrbitDateHelper $urbitDateHelper,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->urbitConfigHelper = $urbitConfigHelper;
        $this->urbitDateHelper = $urbitDateHelper;

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
        $checkoutId = $this->checkoutSession->getCheckoutIdFromApi();
        $deliveryInfo = $this->getDeliveryInfoFromSession();

        $nowDeliveryDate = $this->urbitDateHelper->getNowDeliveryDate();

        $dateString = $deliveryInfo['day'] == "now" ?
            $nowDeliveryDate : $deliveryInfo['day'] . " " . $deliveryInfo['hour'] . ":" . $deliveryInfo['minute'] . ":00";

        $deliveryDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString,
            $this->urbitDateHelper->getCetTimeZone());
        $deliveryDate->setTimezone($this->urbitDateHelper->getUtcTimeZone());

        $formattedDeliveryDate = $deliveryDate->format('Y-m-d\TH:i:sP');

        $nowTime = new DateTime(null, $this->urbitDateHelper->getUtcTimeZone());
        $nowTimestamp = $nowTime->getTimestamp();

        $preparationTime = $this->urbitConfigHelper->getAutoValidationTime();

        if ($preparationTime) {
            $nowTimestamp += (int)$preparationTime * 60;
        }

        $attributes = [
            'urbit_checkout_id'          => $checkoutId,
            'urbit_update_checkout_time' => $nowTimestamp,
            'urbit_triggered'            => 'false',
            'urbit_delivery_time'        => $formattedDeliveryDate,
            'urbit_message'              => $deliveryInfo['message'],
            'urbit_first_name'           => $deliveryInfo['firstname'],
            'urbit_last_name'            => $deliveryInfo['lastname'],
            'urbit_street'               => $deliveryInfo['street'],
            'urbit_city'                 => $deliveryInfo['city'],
            'urbit_postcode'             => $deliveryInfo['postcode'],
            'urbit_phone_number'         => $deliveryInfo['phone'],
            'urbit_email'                => $deliveryInfo['email']
        ];

        $orderId = $observer->getEvent()->getOrderIds()[0];
        $order = $this->orderRepository->get($orderId);

        foreach ($attributes as $attrName => $attrValue) {
            $order->setData($attrName, $attrValue);
        }

        $order->save();
    }

    /**
     * Get Urb-it delivery information from session
     *
     * @return array
     */
    protected function getDeliveryInfoFromSession()
    {
        return [
            'day'       => $this->checkoutSession->getData('urbit_shipping_day', ''),
            'hour'      => $this->checkoutSession->getData('urbit_shipping_hour', ''),
            'minute'    => $this->checkoutSession->getData('urbit_shipping_minute', ''),
            'firstname' => $this->checkoutSession->getData('urbit_shipping_firstname', ''),
            'lastname'  => $this->checkoutSession->getData('urbit_shipping_lastname', ''),
            'street'    => $this->checkoutSession->getData('urbit_shipping_street', ''),
            'city'      => $this->checkoutSession->getData('urbit_shipping_city', ''),
            'email'     => $this->checkoutSession->getData('urbit_shipping_email', ''),
            'message'   => $this->checkoutSession->getData('urbit_message', ''),
            'postcode'  => $this->checkoutSession->getData('urbit_shipping_postcode', ''),
            'phone'     => $this->checkoutSession->getData('urbit_shipping_telephone', '')
        ];
    }
}