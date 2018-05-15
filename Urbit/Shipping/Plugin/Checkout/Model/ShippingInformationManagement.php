<?php

namespace Urbit\Shipping\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class ShippingInformationManagement
 *
 * @package Urbit\Shipping\Plugin\Checkout\Model
 */
class ShippingInformationManagement
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param LoggerInterface $logger
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        LoggerInterface $logger,
        CheckoutSession $checkoutSession
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();

        //get delivery field's values
        $deliveryFields = [
            'urbit_shipping_day'       => $extAttributes->getUrbitSpecificTimeDate(),
            'urbit_shipping_hour'      => $extAttributes->getUrbitSpecificTimeHour(),
            'urbit_shipping_minute'    => $extAttributes->getUrbitSpecificTimeMinute(),
            'urbit_shipping_firstname' => $extAttributes->getUrbitShippingFirstname(),
            'urbit_shipping_lastname'  => $extAttributes->getUrbitShippingLastname(),
            'urbit_shipping_postcode'  => $extAttributes->getUrbitShippingPostcode(),
            'urbit_shipping_street'    => $extAttributes->getUrbitShippingStreet(),
            'urbit_shipping_city'      => $extAttributes->getUrbitShippingCity(),
            'urbit_shipping_telephone' => $extAttributes->getUrbitShippingTelephone(),
            'urbit_shipping_email'     => $extAttributes->getUrbitShippingEmail(),
            'urbit_message'            => $extAttributes->getUrbitMessage()
        ];

        //save delivery field's values to session
        if (!empty($deliveryFields)) {
            foreach ($deliveryFields as $key => $value) {
                $this->checkoutSession->setData($key, $value);
            }
        }
    }
}
