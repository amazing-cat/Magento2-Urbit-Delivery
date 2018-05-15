<?php

namespace Urbit\Shipping\Model\Config\Source;

/**
 * Class EnvironmentList
 *
 * @package Urbit\Shipping\Model\Config\Source
 */
class EnvironmentList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns options for selectbox
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'api', 'label' => __('Production')],
            ['value' => 'sandbox', 'label' => __('Staging(Test)')]
        ];
    }
}