<?php

namespace Urbit\Shipping\Controller\Date;

/**
 * Class NowPossibility
 *
 * @package Urbit\Shipping\Controller\Date
 */
class NowPossibility extends AbstractDateAction
{
    /**
     * Returns Now delivery possibility
     *
     * AJAX
     */
    public function execute()
    {
        $isShowNow = false;

        $openHours = $this->urbitDateHelper->getDeliveryHours();
        $nowDeliveryTime = $this->urbitDateHelper->getNowDeliveryTime();

        foreach ($openHours as $item) {
            if ($item['closed'] == 1) {
                continue;
            }

            $deliveryTimestamp = $this->urbitDateHelper->getNextPossibleDeliveryTime();

            $firstDeliveryTimestamp = $this->urbitDateHelper->getFirstDeliveryTimestamp($item['first_delivery']);
            $lastDeliveryTimestamp = $this->urbitDateHelper->getLastDeliveryTimestamp($item['last_delivery']);

            if ($lastDeliveryTimestamp < $deliveryTimestamp) {
                continue;
            }

            if ($firstDeliveryTimestamp <= $nowDeliveryTime && $lastDeliveryTimestamp >= $nowDeliveryTime) {
                $isShowNow = true;
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($isShowNow));
    }
}
