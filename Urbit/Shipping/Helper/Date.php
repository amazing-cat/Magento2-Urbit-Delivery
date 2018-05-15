<?php

namespace Urbit\Shipping\Helper;

use DateTime;
use DateTimeZone;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Urbit\Shipping\Model\Urbit\StoreApi;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;

/**
 * Class AbstractDate
 *
 * @package Urbit\Shipping\Controller\Date
 */
class Date extends AbstractHelper
{
    /**
     * @var StoreApi
     */
    protected $api;

    /**
     * @var DateTimeZone
     */
    protected $utcTimeZone;

    /**
     * @var DateTimeZone
     */
    protected $cetTimeZone;

    /**
     * @var UrbitConfigHelper
     */
    protected $urbitConfigHelper;

    /**
     * Date constructor.
     *
     * @param Context $context
     * @param StoreApi $storeApi
     * @param Config $urbitConfigHelper
     */
    public function __construct(
        Context $context,
        StoreApi $storeApi,
        UrbitConfigHelper $urbitConfigHelper
    ) {
        parent::__construct($context);

        $this->api = $storeApi;
        $this->urbitConfigHelper = $urbitConfigHelper;
        $this->utcTimeZone = new DateTimeZone('UTC');
        $this->cetTimeZone = new DateTimeZone('CET');
    }

    /**
     * Get delivery hours from Urb-it API
     * API returns information about: closing_time, opening_time, closed, last_delivery, first_delivery, pickup_delay
     *
     * @return array
     */
    public function getDeliveryHours()
    {
        $responseObj = $this->api->getDeliveryHours();
        $response = $responseObj->getResponse();

        return isset($response['items']) ? $response['items'] : [];
    }

    /**
     * Get now delivery time
     * result = Preparation time (defined in BO) + 1h30 min
     *
     * @return false|float|int
     */
    public function getNowDeliveryTime()
    {
        $nowTime = new DateTime(null, $this->cetTimeZone);
        $nowTime->modify('+ 1 hour +30 minutes');

        $preparationTime = $this->urbitConfigHelper->getAutoValidationTime();

        if ($preparationTime) {
            $nowTimeTimestamp = $nowTime->getTimestamp();
            $nowTimeTimestamp += (int)$preparationTime * 60;
            $nowTime->setTimestamp($nowTimeTimestamp);
        }

        return $nowTime->getTimestamp();
    }

    /**
     * Get now delivery date
     *
     * @return string
     */
    public function getNowDeliveryDate()
    {
        $nowTime = new DateTime(null, $this->cetTimeZone);
        $nowTime->modify('+ 1 hour +30 minutes');

        $preparationTime = $this->urbitConfigHelper->getAutoValidationTime();

        if ($preparationTime) {
            $nowTimeTimestamp = $nowTime->getTimestamp();
            $nowTimeTimestamp += (int)$preparationTime * 60;
            $nowTime->setTimestamp($nowTimeTimestamp);
        }

        return $nowTime->format("Y-m-d H:i:s");
    }

    /**
     * Get nearest possible specific delivery time
     * result = Preparation time (defined in BO) + 1h30 min + 15 min
     *
     * @return int
     */
    public function getNextPossibleDeliveryTime()
    {
        $nowTime = $this->getNowDeliveryTime();

        //add 15 min (900s) to now delivery time
        return $nowTime + 900;
    }

    /**
     * Returns DateTime Object for first delivery date
     *
     * @param $firstDeliveryTimeString
     *
     * @return DateTime
     */
    public function getFirstDeliveryDateTime($firstDeliveryTimeString)
    {
        $firstDeliveryObj = DateTime::createFromFormat('Y-m-d\TH:i:sP', $firstDeliveryTimeString, $this->utcTimeZone);
        $firstDeliveryObj->setTimezone($this->cetTimeZone);

        return $firstDeliveryObj;
    }

    /**
     * Returns DateTime Object for last delivery date
     *
     * @param $lastDeliveryTimeString
     *
     * @return DateTime
     */
    public function getLastDeliveryDateTime($lastDeliveryTimeString)
    {
        $lastDeliveryObj = DateTime::createFromFormat('Y-m-d\TH:i:sP', $lastDeliveryTimeString, $this->utcTimeZone);
        $lastDeliveryObj->setTimezone($this->cetTimeZone);

        return $lastDeliveryObj;
    }

    /**
     * Returns timestamp for first delivery date
     *
     * @param string $firstDeliveryTimeString
     *
     * @return int Last delivery timestamp
     */
    public function getFirstDeliveryTimestamp($firstDeliveryTimeString)
    {
        $firstDeliveryDateTime = $this->getFirstDeliveryDateTime($firstDeliveryTimeString);

        return $firstDeliveryDateTime->getTimestamp();
    }

    /**
     * Returns timestamp for last delivery date
     *
     * @param string $lastDeliveryTimeString
     *
     * @return int Last delivery timestamp
     */
    public function getLastDeliveryTimestamp($lastDeliveryTimeString)
    {
        $lastDeliveryDateTime = $this->getLastDeliveryDateTime($lastDeliveryTimeString);

        return $lastDeliveryDateTime->getTimestamp();
    }

    /**
     * Add urbit devlivery time and store preparation time to current timestamp
     *
     * @param $timeStamp
     *
     * @return int
     */
    public function addDeliveryAndPreparationTime($timeStamp)
    {
        $deliveryTime = strtotime('+1 hour +45 minutes', $timeStamp);
        $preparationTime = $this->urbitConfigHelper->getAutoValidationTime();

        if ($preparationTime) {
            $deliveryTime += (int)$preparationTime * 60;
        }

        return $deliveryTime;
    }

    /**
     * Returns utcTimeZone property value
     *
     * @return DateTimeZone
     */
    public function getUtcTimeZone()
    {
        return $this->utcTimeZone;
    }

    /**
     * Returns cetTimeZone property value
     *
     * @return DateTimeZone
     */
    public function getCetTimeZone()
    {
        return $this->cetTimeZone;
    }
}
