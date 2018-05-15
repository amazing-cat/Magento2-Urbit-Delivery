<?php

namespace Urbit\Shipping\Model\Urbit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Urbit\Shipping\Model\Urbit\Api\ApiWrapper;
use Urbit\Shipping\Model\Urbit\Api\Response;

/**
 * Class StoreApi
 *
 * @package Urbit\Shipping\Model\Urbit
 */
class StoreApi
{
    /**
     * @var ApiWrapper
     */
    protected $api;

    /**
     * StoreApi constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ApiWrapper $api
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ApiWrapper $api
    ) {
        $this->api = $api;
    }

    /**
     * POST request to Urb-it API for create cart
     *
     * @param $cartInfo
     *
     * @return Response
     */
    public function createCart($cartInfo)
    {
        return $this->api->send("POST", "carts", $cartInfo);
    }

    /**
     * POST request to Urb-it API for create checkout
     *
     * @param $args
     *
     * @return Response
     */
    public function createCheckout($args)
    {
        return $this->api->send("POST", "checkouts", $args);
    }

    /**
     * PUT request to Urb-it API for update delivery information
     *
     * @param $checkoutId
     * @param $args
     *
     * @return Response
     */
    public function updateCheckout($checkoutId, $args)
    {
        return $this->api->send("PUT", "checkouts/" . $checkoutId . "/delivery", $args);
    }

    /**
     * Call to API. Request possible delivery hours
     *
     * @return Response
     */
    public function getDeliveryHours()
    {
        return $this->api->send(
            "GET",
            "deliveryhours"
        );
    }

    /**
     * Call to API. Validate delivery address
     *
     * @param string $street
     * @param string $postcode
     * @param string $city
     *
     * @return Response
     */
    public function validateDeliveryAddress($street = '', $postcode = "", $city = "")
    {
        return $this->api->send(
            "get",
            "address?" . http_build_query(
                [
                    'street'   => $street,
                    'postcode' => $postcode,
                    'city'     => $city,
                ]
            )
        );
    }
}
