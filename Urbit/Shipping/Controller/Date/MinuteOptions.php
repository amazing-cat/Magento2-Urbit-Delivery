<?php

namespace Urbit\Shipping\Controller\Date;

use DateTime;

/**
 * Class MinuteOptions
 *
 * @package Urbit\Shipping\Controller\Date
 */
class MinuteOptions extends AbstractDateAction
{
    /**
     * Get options for possible delivery minutes selectbox on frontend Urb-it form's
     * AJAX
     */
    public function execute()
    {
        $startDate = $this->getRequest()->getPost('selected_date');
        $startHour = $this->getRequest()->getPost('selected_hour');
        $openHours = $this->urbitDateHelper->getDeliveryHours();

        $possibleTime = $this->urbitDateHelper->getNextPossibleDeliveryTime();
        $nextPossible = new DateTime(null, $this->urbitDateHelper->getCetTimeZone());
        $nextPossible->setTimestamp($possibleTime);

        $minutesArray = ["00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55"];
        $possibleMinutesResult = null;

        foreach ($openHours as $item) {
            $firstDeliveryDateTime = $this->urbitDateHelper->getFirstDeliveryDateTime($item['first_delivery']);
            $lastDeliveryDateTime = $this->urbitDateHelper->getLastDeliveryDateTime($item['last_delivery']);

            if ($startDate == $firstDeliveryDateTime->format('Y-m-d')) {
                if ($startHour == $firstDeliveryDateTime->format('H') && $nextPossible->getTimestamp() < $firstDeliveryDateTime->getTimestamp()) {
                    $possibleMinutes = $firstDeliveryDateTime->format('i');
                    $possibleMinutesResult = $this->getFutureMinutes($possibleMinutes);
                } elseif ($startDate == $nextPossible->format('Y-m-d') && $startHour == $nextPossible->format('H')) {
                    //if chosen date == today and selected_hour == nearest possible delivery hour
                    $possibleMinutes = $nextPossible->format('i');
                    $possibleMinutesResult = $this->getFutureMinutes($possibleMinutes);
                } elseif ($startHour == $lastDeliveryDateTime->format('H')) {
                    //if selected_hour == last delivery hour
                    $possibleMinutes = $lastDeliveryDateTime->format('i');
                    $possibleMinutesResult = $this->getPastMinutes($possibleMinutes);
                } else {
                    $possibleMinutesResult = $minutesArray;
                }
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($possibleMinutesResult));
    }

    /**
     * Returns minutes, which is greater than param
     *
     * @param $possibleMinutes
     *
     * @return array
     */
    protected function getFutureMinutes($possibleMinutes)
    {
        $minutesArray = ["00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55"];
        $filteredMinutesArray = [];

        foreach ($minutesArray as $minute) {
            if ((int)$minute >= (int)$possibleMinutes) {
                $filteredMinutesArray[] = $minute;
            }
        }

        return $filteredMinutesArray;
    }

    /**
     * Returns minutes, which is lower than param
     *
     * @param $possibleMinutes
     *
     * @return array
     */
    protected function getPastMinutes($possibleMinutes)
    {
        $minutesArray = ["00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55"];
        $filteredMinutesArray = [];

        foreach ($minutesArray as $minute) {
            if ((int)$minute <= (int)$possibleMinutes) {
                $filteredMinutesArray[] = $minute;
            }
        }

        return $filteredMinutesArray;
    }
}
