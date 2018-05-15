<?php

namespace Urbit\Shipping\Model\Urbit\Api;

use Magento\Framework\Exception\RuntimeException;

/**
 * Class Response
 *
 * @package Urbit\Shipping\Model\Urbit\Api
 */
class Response
{
    const NO_ERROR = "success";
    const HAS_ERROR = "error";

    const HTTP_STATUS_GET = "GET";
    const HTTP_STATUS_POST = "POST";
    const HTTP_STATUS_PUT = "PUT";

    const HTTP_STATUS_SUCCESS_GET = "200";
    const HTTP_STATUS_SUCCESS_POST = "201";
    const HTTP_STATUS_SUCCESS_PUT = "204";

    const HTTP_STATUS_ERROR_BAD_REQUEST = "400";
    const HTTP_STATUS_ERROR_UNAUTHORISED = "404";
    const HTTP_STATUS_ERROR_NOT_FOUND = "404";
    const HTTP_STATUS_ERROR_CONFLICT = "409";
    const HTTP_STATUS_ERROR_UNPROCESSABLE_ENTITY = "422";
    const HTTP_STATUS_ERROR_TOO_MANY_REQUESTS = "429";

    const HTTP_STATUS_SERVER_ERROR = "500";
    const HTTP_STATUS_SERVER_ERROR_SERVICE_UNAVAILABLE = "503";
    const HTTP_STATUS_SERVER_ERROR_GATEWAY_TIMEOUT = "504";

    /**
     * @var array
     */
    protected $_info = [
        "http_code"   => null,
        "http_method" => null,
    ];

    /**
     * @var array
     */
    protected $_response;

    /**
     * @var string
     */
    protected $_method = "";

    /**
     * @var bool
     */
    protected $_success = false;

    /**
     * @var string
     */
    protected $_status = "";

    /**
     * @var string
     */
    protected $_httpCode = "";

    /**
     * @var string
     */
    protected $_errorMessage = "";

    /**
     * Response constructor.
     *
     * @param $info
     * @param $response
     * @param $method
     */
    public function __construct($info, $response, $method)
    {
        $this->_info = $info ?: [];
        $this->_response = $response ?: [];
        $this->_method = $method ?: '';

        $this->processResponse();
    }

    /**
     * Process Info
     */
    protected function processInfo()
    {
        $statuses = [
            self::HTTP_STATUS_GET  => self::HTTP_STATUS_SUCCESS_GET,
            self::HTTP_STATUS_POST => self::HTTP_STATUS_SUCCESS_POST,
            self::HTTP_STATUS_PUT  => self::HTTP_STATUS_SUCCESS_PUT,
        ];

        $code = isset($statuses[$this->getHttpMethod()]) ? $statuses[$this->getHttpMethod()] : "";

        $this->_success = $code === $this->getHttpCode() ? self::NO_ERROR : self::HAS_ERROR;
    }

    /**
     * Process Response
     */
    protected function processResponse()
    {
        $args = (object)$this->_response;

        $this->processInfo();
        $hasError = $this->hasError();

        switch (true) {
            case isset($args->message) && $args->message == "An error has occurred.":
            case $hasError:
                $this->_errorMessage = isset($args->message) ? $args->message : "An error has occurred.";
                $this->_httpCode = isset($args->code) ? $args->code : $this->getHttpCode();
                break;
            default:
                break;
        }
    }

    /**
     * Returns response success value
     *
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_success === self::NO_ERROR;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return !$this->getSuccess();
    }

    /**
     * Returns response http method
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->_method;
    }

    /**
     * Returns response http code
     *
     * @return string
     */
    public function getHttpCode()
    {
        return isset($this->_info["http_code"]) ? (string)$this->_info["http_code"] : "";
    }

    /**
     * @param string $name
     * @param mixed $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (preg_match("/^get(.*)$/", $name, $matches)) {
            $param = isset($matches[1]) ? lcfirst($matches[1]) : false;

            if ($param && property_exists($this, $param)) {
                return $this->{$param};
            }
        }
    }

    /**
     * Returns response field's value
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
