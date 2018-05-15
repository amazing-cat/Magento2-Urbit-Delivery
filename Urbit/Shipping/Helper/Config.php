<?php

namespace Urbit\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Urbit\Shipping\Helper
 */
class Config extends AbstractHelper
{
    /**
     * Config constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Returns specific time days fields's value from config
     *
     * @return string
     */
    public function getSpecificTimeDays()
    {
        return $this->getConfigValue('urbit_specific_time_days');
    }

    /**
     * Returns order autovalidation time field's value from config
     *
     * @return string
     */
    public function getAutoValidationTime()
    {
        return $this->getConfigValue('urbit_now_order_autovalidation_time');
    }

    /**
     * Returns order status trigger field's value from config
     *
     * @return string
     */
    public function getOrderTriggerStatus()
    {
        return $this->getConfigValue('urbit_order_status_trigger');
    }

    /**
     * Returns failure email recipient field's value from config
     *
     * @return string
     */
    public function getFailureEmailRecipient()
    {
        return $this->getConfigValue('urbit_order_failure_email');
    }

    /**
     * Returns chosen API environment field's value from config ('api' or 'sandbox')
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getConfigValue('urbit_environment');
    }

    /**
     * Returns Api Key field's value from config for current environment ('api' or 'sandbox')
     *
     * @param $env
     *
     * @return string
     */
    public function getApiKey($env)
    {
        $apiField = $env == 'api' ? 'urbit_production_api_key' : 'urbit_test_api_key';

        return $this->getConfigValue($apiField);
    }

    /**
     * Returns JWT token field's value from config for current environment ('api' or 'sandbox')
     *
     * @param $env
     *
     * @return string
     */
    public function getJWTToken($env)
    {
        $tokenField = $env == 'api' ? 'urbit_production_bearer' : 'urbit_test_bearer';

        return $this->getConfigValue($tokenField);
    }

    /**
     * Returns delivery price for store's currency from config
     *
     * @param $currencyCode
     *
     * @return int
     */
    public function getDeliveryPrice($currencyCode)
    {
        return (double)$this->getConfigValue('urbit_flat_fee_' . strtolower($currencyCode));
    }

    /**
     * Returns module status field's value from config
     *
     * @return int
     */
    public function getModuleStatus()
    {
        return (int)$this->getConfigValue('urbit_enable');
    }

    /**
     * Returns shipping method service name field's value from config
     *
     * @return string
     */
    public function getUrbitServiceName()
    {
        return $this->getConfigValue('urbit_service_name');
    }

    /**
     * Returns field's value from config for $field
     *
     * @param $field
     *
     * @return string
     */
    protected function getConfigValue($field)
    {
        return $this->scopeConfig->getValue(
            'carriers/urbit_shipping_settings/' . $field,
            ScopeInterface::SCOPE_STORE
        );
    }
}