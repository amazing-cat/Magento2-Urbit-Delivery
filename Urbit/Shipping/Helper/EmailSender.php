<?php

namespace Urbit\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EmailSender
 *
 * @package Urbit\Shipping\Helper
 */
class EmailSender extends AbstractHelper
{
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * EmailSender constructor.
     *
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Send email with failure info to recipient
     *
     * @param $recipientEmail
     * @param $orderId
     * @param $errorMessage
     *
     * @return $this
     */
    public function sendOrderFailureReport($recipientEmail, $orderId, $errorMessage)
    {
        $store = $this->storeManager->getStore()->getId();

        $transport = $this->transportBuilder->setTemplateIdentifier('urbit_order_failure')
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars(
                [
                    'store' => $this->storeManager->getStore(),
                ]
            )
            ->setTemplateVars([
                'orderId'      => $orderId,
                'errorMessage' => $errorMessage
            ])
            ->setFrom('general')
            ->addTo($recipientEmail)
            ->getTransport();

        /** @var TYPE_NAME $transport */
        $transport->sendMessage();

        return $this;
    }
}