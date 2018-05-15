<?php

namespace Urbit\Shipping\Controller\Date;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Urbit\Shipping\Helper\Date as UrbitDateHelper;
use Urbit\Shipping\Helper\Config as UrbitConfigHelper;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractDateAction
 *
 * @package Urbit\Shipping\Controller\Date
 */
abstract class AbstractDateAction extends Action
{
    /**
     * @var UrbitDateHelper
     */
    protected $urbitDateHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UrbitConfigHelper
     */
    protected $urbitConfigHelper;

    /**
     * Action abstract method
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    abstract public function execute();

    /**
     * AbstractDateAction constructor.
     *
     * @param Context $context
     * @param UrbitDateHelper $urbitDateHelper
     * @param UrbitConfigHelper $urbitConfigHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        UrbitDateHelper $urbitDateHelper,
        UrbitConfigHelper $urbitConfigHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->urbitDateHelper = $urbitDateHelper;
        $this->logger = $logger;
        $this->urbitConfigHelper = $urbitConfigHelper;
    }
}