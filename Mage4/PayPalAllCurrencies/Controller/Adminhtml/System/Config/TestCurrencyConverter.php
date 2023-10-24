<?php

namespace Mage4\PayPalAllCurrencies\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Mage4\PayPalAllCurrencies\Helper\Data;

/**
 * Class TestCurrencyConverter
 *
 * @package Mage4\PayPalAllCurrencies\Controller\Adminhtml\System\Config
 */
class TestCurrencyConverter extends Action
{
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     * @param Data        $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Test currency service
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        $serviceId = $this->getRequest()->getPost('serviceId');

        /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface $currencyConverter */
        $currencyConverter = $this->_objectManager->create(
            '\Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory'
        )->load($serviceId);

        if ($currencyConverter) {
            $currencyConverter->setPayPalCurrencyCode($this->getRequest()->getPost('payPalCurrency'));

            return $result->setData(
                [
                    'success' => true,
                    'info'    => sprintf(
                        '1 %s = %s %s',
                        $currencyConverter->getStoreCurrencyCode(),
                        $currencyConverter->exchangeFromService(1),
                        $currencyConverter->getPayPalCurrencyCode()
                    )
                ]
            );
        } else {
            return $result->setData(
                [
                    'success' => false,
                    'info'    => sprintf('Error. Can`t load service with id: %s', $serviceId)
                ]
            );
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage4_PayPalAllCurrencies::config');
    }
}
