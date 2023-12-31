<?php

namespace Mage4\PayPalAllCurrencies\Plugin\Sales\Model\Order\Payment\State;

use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Mage4\PayPalAllCurrencies\Helper\Data;
use Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory;

/**
 * Class CaptureCommandPlugin
 *
 * @package Mage4\PayPalAllCurrencies\Plugin\Sales\Model\Order\Payment\State
 */
class CaptureCommandPlugin
{
    /** @var \Mage4\PayPalAllCurrencies\Helper\Data $helper */
    protected $helper;

    /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory */
    protected $currencyServiceFactory;

    /** @var \Magento\Directory\Model\CurrencyFactory $currencyFactory */
    protected $currencyFactory;

    /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface|null $currencyService */
    protected $currencyService = null;

    /** @var \Psr\Log\LoggerInterface $logger */
    protected $logger;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /**
     * QuoteRepositoryPlugin constructor.
     *
     * @param \Mage4\PayPalAllCurrencies\Helper\Data                  $helper
     * @param \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory
     * @param \Magento\Directory\Model\CurrencyFactory                   $currencyFactory
     * @param \Psr\Log\LoggerInterface                                   $logger
     * @param \Magento\Framework\Message\ManagerInterface                $messageManager
     */
    public function __construct(
        Data $helper,
        CurrencyServiceFactory $currencyServiceFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        LoggerInterface $logger,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->currencyServiceFactory = $currencyServiceFactory;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment\State\CaptureCommand $captureCommand
     * @param callable                                                $proceed
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface           $payment
     * @param                                                         $amount
     * @param \Magento\Sales\Api\Data\OrderInterface                  $order
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Model\Order\Payment\State\CaptureCommand $captureCommand,
        callable $proceed,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ) {
        if ($order && $this->helper->isOrderPlacedByPaypal($order)) {
            $baseCurrencyCode = $order->getBaseCurrencyCode();

            $amount = $this->getCurrencyService()->exchange($amount);
            $order->setBaseCurrencyCode($this->helper->getPayPalCurrency());

            $returnValue = $proceed($payment, $amount, $order);

            $order->setBaseCurrencyCode($baseCurrencyCode);
        } else {
            $returnValue = $proceed($payment, $amount, $order);
        }

        return $returnValue;
    }

    /**
     * @return false|null|\Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface
     */
    public function getCurrencyService()
    {
        if (!$this->currencyService) {
            $this->currencyService = $this->currencyServiceFactory->load($this->helper->getCurrencyServiceId());
        }

        return $this->currencyService;
    }
}
