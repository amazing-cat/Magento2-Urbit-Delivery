<?php

namespace Urbit\Shipping\Model\Config\Source;

/**
 * Class AllowedProductsList
 *
 * @package Urbit\Shipping\Model\Config\Source
 */
class AllowedProductsList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns options for selectbox
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => __('All')],
            ['value' => 'specific', 'label' => __('Only specific')]
        ];
    }
}