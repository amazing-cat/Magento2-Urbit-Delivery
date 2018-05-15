<?php

namespace Urbit\Shipping\Model\Config\Source;

use \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class OrderStatusesList
 *
 * @package Urbit\Shipping\Model\Config\Source
 */
class OrderStatusesList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * OrderStatusesList constructor.
     *
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(CollectionFactory $statusCollectionFactory)
    {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * Returns options for selectbox
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();

        array_unshift($options, ['value' => 'none', 'label' => __('None')]);

        return $options;
    }
}