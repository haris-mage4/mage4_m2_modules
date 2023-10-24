<?php

namespace Mage4\PayPalAllCurrencies\Model\ResourceModel\Rates;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Mage4\PayPalAllCurrencies\Model\ResourceModel\Rates
 */
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Mage4\PayPalAllCurrencies\Model\Rates',
            'Mage4\PayPalAllCurrencies\Model\ResourceModel\Rates'
        );
    }
}

