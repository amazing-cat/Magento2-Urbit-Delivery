<?php

namespace Urbit\Shipping\Model\Config\Source;

/**
 * Class SpecificTimeDaysList
 *
 * @package Urbit\Shipping\Model\Config\Source
 */
class SpecificTimeDaysList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns options for selectbox
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('1')],
            ['value' => 2, 'label' => __('2')],
            ['value' => 3, 'label' => __('3')],
            ['value' => 4, 'label' => __('4')],
            ['value' => 5, 'label' => __('5')],
            ['value' => 6, 'label' => __('6')]
        ];
    }
}