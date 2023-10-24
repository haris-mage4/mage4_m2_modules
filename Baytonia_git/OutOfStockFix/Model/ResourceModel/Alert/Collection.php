<?php

namespace Baytonia\OutOfStockFix\Model\ResourceModel\Alert;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Baytonia\OutOfStockFix\Model\Alert', 'Baytonia\OutOfStockFix\Model\ResourceModel\Alert');
    }
}
