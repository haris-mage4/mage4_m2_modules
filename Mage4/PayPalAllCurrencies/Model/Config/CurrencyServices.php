<?php

namespace Mage4\PayPalAllCurrencies\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory;

/**
 * Class ConverterServices
 *
 * @package Mage4\PayPalAllCurrencies\Model\Config
 */
class CurrencyServices implements ArrayInterface
{
    protected $_services;

    /**
     * CurrencyServices constructor.
     *
     * @param \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $serviceFactory
     */
    public function __construct(CurrencyServiceFactory $serviceFactory)
    {
        $this->_services = $serviceFactory->getServices();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_services as $key => $service) {
            $options[] = ['value' => $key, 'label' => $service['label']];
        }

        return $options;
    }
}
