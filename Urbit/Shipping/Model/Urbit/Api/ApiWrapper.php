<?php

namespace Urbit\Shipping\Model\Urbit\Api;

use Psr\Log\LoggerInterface;
use Urbit\Shipping\Model\Urbit\Api\ResponseFactory;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;

/**
 * Class ApiWrapper
 *
 * @package Urbit\Shipping\Model\Urbit\Api
 */
class ApiWrapper
{
    /**
     * @var string
     */
    protected $_apiDomain = "urb-it.com";

    /**
     * @var mixed|string
     */
    protected $_apiEnvironment = "";

    /**
     * @var string
     */
    protected $_apiUrl = "";

    /**
     * @var mixed|string
     */
    protected $_apiXKey = "";

    /**
     * @var mixed|string
     */
    protected $_apiBearerJWTToken = "";

    /**
     * @var bool|mixed
     */
    protected $_apiDebug = true;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var UrbitConfigHelper
     */
    protected $_urbitConfigHelper;

    /**
     * Urbit_Shipping_Model_Urbit_Api_Client constructor.
     *
     * @param LoggerInterface $logger
     * @param ResponseFactory $responseFactory
     * @param UrbitConfigHelper $urbitConfigHelper
     */
    public function __construct(
        LoggerInterface $logger,
        ResponseFactory $responseFactory,
        UrbitConfigHelper $urbitConfigHelper
    ) {
        $this->_responseFactory = $responseFactory;
        $this->_logger = $logger;
        $this->_urbitConfigHelper = $urbitConfigHelper;
        $this->_apiEnvironment = $this->_urbitConfigHelper->getEnvironment();
        $this->_apiUrl = "https://" . $this->_apiEnvironment . "." . $this->_apiDomain . "/v2/";
        $this->_apiXKey = $this->_urbitConfigHelper->getApiKey($this->_apiEnvironment);
        $this->_apiBearerJWTToken = $this->_urbitConfigHelper->getJWTToken($this->_apiEnvironment);

    }

    /**
     * Call to API
     *
     * @param $method
     * @param $apiPath
     * @param array $data
     *
     * @return Response
     */
    public function send($method, $apiPath, $data = [])
    {
        $url = $this->_apiUrl . $apiPath;
        $curl = curl_init($url);

        if ($method === "GET") {
            $jsonData = "";
            $queryString = http_build_query($data);
        } else {
            $jsonData = json_encode($data);
            $queryString = "";
        }

        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->_apiXKey,
            'Authorization: ' . $this->_apiBearerJWTToken,
        ];

        $options = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if ($method === "POST" || $method === "PUT") {
            $options[CURLOPT_POSTFIELDS] = $jsonData;
            $headers[] = 'Content-Length: ' . strlen($jsonData);
        } else {
            $options[CURLOPT_URL] = $url . "?" . $queryString;
        }

        curl_setopt_array($curl, $options);

        $response = json_decode(curl_exec($curl), true);
        $info = curl_getinfo($curl);

        curl_close($curl);

        $responseObject = $this->_responseFactory->create([
            "info"     => $info,
            "response" => $response,
            "method"   => $method
        ]);

        return $responseObject;
    }

    /**
     * Write to debug log file
     *
     * @param $type
     * @param $object
     * @param bool $forceLog
     */
    protected function writeToLog($type, $object, $forceLog = false)
    {
        if ($forceLog || $this->_apiDebug) {
            $this->_logger->debug($type . ": " . print_r($object, true));
        }
    }
}
