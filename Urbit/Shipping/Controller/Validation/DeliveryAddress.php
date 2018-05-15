<?php

namespace Urbit\Shipping\Controller\Validation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Urbit\Shipping\Model\Urbit\StoreApi;
use Magento\Framework\App\Action\Context;

/**
 * Class DeliveryAddress
 *
 * @package Urbit\Shipping\Controller\Validation
 */
class DeliveryAddress extends Action
{
    /**
     * @var StoreApi
     */
    protected $api;

    /**
     * DeliveryAddress constructor.
     *
     * @param Context $context
     * @param StoreApi $storeApi
     */
    public function __construct(
        Context $context,
        StoreApi $storeApi
    ) {
        parent::__construct($context);

        $this->api = $storeApi;
    }

    /**
     * Validate delivery address by Urb-it API (GET request)
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $street = $this->getRequest()->getParam("street", false);
        $postcode = $this->getRequest()->getParam("postcode", false);
        $city = $this->getRequest()->getParam("city", false);

        $responseObj = $this->api->validateDeliveryAddress($street, $postcode, $city);

        $result = [
            'code'    => $responseObj->getHttpCode(),
            'message' => $responseObj->getErrorMessage(),
        ];

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
}
