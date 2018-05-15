<?php

namespace Urbit\Shipping\Controller\Date;

use DateTime;

/**
 * Class HourOptions
 *
 * @package Urbit\Shipping\Controller\Date
 */
class HourOptions extends AbstractDateAction
{
    /**
     * Get options for possible delivery hours selectbox on frontend Urb-it form's
     */
    public function execute()
    {
        $startDate = $this->getRequest()->getPost('selected_date');
        $openHours = $this->urbitDateHelper->getDeliveryHours();

        $nextPossible = new DateTime(null, $this->urbitDateHelper->getCetTimeZone());
        $nextPossible->setTimestamp($this->urbitDateHelper->getNextPossibleDeliveryTime());

        $hours = [];

        foreach ($openHours as $item) {
            $firstDeliveryDateTime = $this->urbitDateHelper->getFirstDeliveryDateTime($item['first_delivery']);

            if ($startDate == $firstDeliveryDateTime->format('Y-m-d')) {
                $lastDeliveryDateTime = $this->urbitDateHelper->getLastDeliveryDateTime($item['last_delivery']);

                $startHour = $nextPossible->format('H');
                $endHour = $lastDeliveryDateTime->format('H');

                /**
                 * If now time before the time of the first delivery
                 * => set first possible delivery time = first delivery time + preparation time + 1h 30m + 15min
                 */
                if ($nextPossible->getTimestamp() < $firstDeliveryDateTime->getTimestamp()) {
                    $startHour = $firstDeliveryDateTime->format('H');
                }

                //add to array hours between first delivery hour and last delivery hour
                for (; $startHour <= $endHour; $startHour++) {
                    $hours[] = (int)$startHour;
                }
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($hours));
    }
}
