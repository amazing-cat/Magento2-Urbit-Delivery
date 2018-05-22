<?php

namespace Urbit\Shipping\Model\Config\Source;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Class ProductAttributesList
 *
 * @package Urbit\ProductFeed\Model\Config\Source
 */
class ProductAttributesList
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * ProductAttributesList constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Returns options for selectbox
     *
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $attributeRepository = $this->attributeRepository->getList(
            'catalog_product',
            $searchCriteria
        );

        $optionArray = [['value' => null, 'label' => '-- Select Attribute --']];

        foreach ($attributeRepository->getItems() as $items) {
            $optionArray[] = ['value' => $items->getAttributeCode(), 'label' => __($items->getFrontendLabel())];
        }

        return $optionArray;
    }
}