<?php

namespace Urbit\Shipping\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

/**
 * Class InstallData
 *
 * @package Urbit\Shipping\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $_salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $_quoteSetupFactory;

    /**
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * @var array
     */
    protected $_orderCustomAttributes = [
        'urbit_checkout_id',
        'urbit_update_checkout_time',
        'urbit_triggered',
        'urbit_delivery_time',
        'urbit_message',
        'urbit_first_name',
        'urbit_last_name',
        'urbit_street',
        'urbit_city',
        'urbit_postcode',
        'urbit_phone_number',
        'urbit_email'
    ];

    /**
     * InstallData constructor.
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_salesSetupFactory = $salesSetupFactory;
        $this->_quoteSetupFactory = $quoteSetupFactory;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $salesInstaller = $this->_salesSetupFactory
            ->create(
                [
                    'resourceName' => 'sales_setup',
                    'setup'        => $setup
                ]
            );

        $quoteInstaller = $this->_quoteSetupFactory
            ->create(
                [
                    'resourceName' => 'quote_setup',
                    'setup'        => $setup
                ]
            );

        $this->addOrderAttributes($salesInstaller, $quoteInstaller);

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $this->addProductAttributes($eavSetup);
    }

    /**
     * Add attribute in quote
     *
     * @param $salesInstaller
     * @param $quoteInstaller
     */
    public function addOrderAttributes($salesInstaller, $quoteInstaller)
    {
        foreach ($this->_orderCustomAttributes as $attribute) {
            $salesInstaller->addAttribute('order', $attribute, ['type' => 'varchar']);
            $quoteInstaller->addAttribute('quote', $attribute, ['type' => 'varchar']);
        }
    }

    /**
     * Add custom attributes to product
     *
     * @param $eavSetup
     */
    public function addProductAttributes($eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'available_for_urbit',
            [
                'group'                   => 'General',
                'type'                    => 'int',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'Available For Urb-it',
                'input'                   => 'boolean',
                'class'                   => '',
                'source'                  => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global'                  => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'required'                => false,
                'user_defined'            => false,
                'default'                 => '0',
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true,
                'unique'                  => false,
                'apply_to'                => 'simple,configurable,virtual,bundle,downloadable'
            ]
        );

    }
}