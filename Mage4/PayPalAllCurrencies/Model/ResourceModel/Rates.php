<?php

namespace Mage4\PayPalAllCurrencies\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Rates
 *
 * @package Mage4\PayPalAllCurrencies\Model\ResourceModel
 */
class Rates extends AbstractDb

{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('Mage4_paypalallcurrencies_rates', 'entity_id');
    }
}
