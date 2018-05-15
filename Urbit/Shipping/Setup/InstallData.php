<?php

namespace Urbit\Shipping\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
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
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->_salesSetupFactory = $salesSetupFactory;
        $this->_quoteSetupFactory = $quoteSetupFactory;
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
}