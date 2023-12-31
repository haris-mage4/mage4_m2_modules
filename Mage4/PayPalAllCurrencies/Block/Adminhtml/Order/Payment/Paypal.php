<?php

namespace Mage4\PayPalAllCurrencies\Block\Adminhtml\Order\Payment;

use Psr\Log\LoggerInterface;

use Mage4\PayPalAllCurrencies\Helper\Data;

/**
 * Class Paypal
 *
 * @package Mage4\PayPalAllCurrencies\Block\Adminhtml\Order\Payment
 */
class Paypal extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /** @var \Mage4\PayPalAllCurrencies\Helper\Data $helper */
    protected $helper;

    /** @var array $data */
    protected $data;

    /** @var \Psr\Log\LoggerInterface $logger */
    protected $logger;

    /**
     * Paypal constructor.
     *
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Sales\Helper\Admin               $adminHelper
     * @param \Mage4\PayPalAllCurrencies\Helper\Data $Mage4Helper
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        Data $Mage4Helper,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->helper = $Mage4Helper;
        $this->data = $data;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getPaypalMessage()
    {
        if ($this->helper->isOrderPlacedByPaypal($this->getOrder())) {
            $format = '<br/>Paypal currency rate (%s/%s): %s';

            return sprintf(
                $format,
                $this->getOrder()->getBaseCurrencyCode(),
                $this->getOrder()->getPaypalCurrencyCode(),
                $this->getOrder()->getPaypalRate()
            );
        }

        return '';
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        $key = 'Mage4_paypalallcurrencies';
        $order = null;
        if (array_key_exists($key, $this->data)) {
            try {

                switch ($this->data[$key]['doc_type']) {
                    case 'order':
                        $order = $this->_coreRegistry->registry('current_order');
                        break;
                    case 'invoice':
                        $order = $this->getInvoice()->getOrder();
                        break;
                    case 'creditmemo':
                        $order = $this->getCreditmemo()->getOrder();
                        break;
                    default:
                        break;
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $order;
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }
}
