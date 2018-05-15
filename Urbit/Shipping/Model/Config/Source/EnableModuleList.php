<?php

namespace Urbit\Shipping\Model\Config\Source;

/**
 * Class EnableModuleList
 *
 * @package Urbit\Shipping\Model\Config\Source
 */
class EnableModuleList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns options for selectbox
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Enabled')],
            ['value' => 0, 'label' => __('Disabled')]
        ];
    }
}