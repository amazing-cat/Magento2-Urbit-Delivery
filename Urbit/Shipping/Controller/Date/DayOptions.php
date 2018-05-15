<?php

namespace Urbit\Shipping\Controller\Date;

/**
 * Class DayOptions
 *
 * @package Urbit\Shipping\Controller\Date
 */
class DayOptions extends AbstractDateAction
{
    /**
     * Get options for possible delivery days selectbox on frontend Urb-it form's
     * AJAX
     */
    public function execute()
    {
        $openHours = $this->urbitDateHelper->getDeliveryHours();

        $optionArray = [];

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

            $dateDiff = $lastDeliveryTimestamp - $deliveryTimestamp;
            $days_from_today = floor($dateDiff / (60 * 60 * 24));

            switch ($days_from_today) {
                case 0:
                    $optionArray[] = [
                        'label' => "Today",
                        'date'  => date('Y-m-d', $firstDeliveryTimestamp),
                    ];
                    break;
                case 1:
                    $optionArray[] = [
                        'label' => "Tomorrow",
                        'date'  => date('Y-m-d', $firstDeliveryTimestamp),
                    ];
                    break;
                default:
                    $optionArray[] = [
                        'label' => date('d/m', $firstDeliveryTimestamp),
                        'date'  => date('Y-m-d', $firstDeliveryTimestamp),
                    ];
            }
        }

        $configPossibleDaysCount = (int)$this->urbitConfigHelper->getSpecificTimeDays();

        if ($configPossibleDaysCount && count($optionArray) > $configPossibleDaysCount) {
            $optionArray = array_slice($optionArray, 0, $configPossibleDaysCount);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($optionArray));
    }
}
