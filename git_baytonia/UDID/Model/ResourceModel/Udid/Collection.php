<?php

namespace Baytonia\UDID\Model\ResourceModel\Udid;

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
        $this->_init('Baytonia\UDID\Model\Udid', 'Baytonia\UDID\Model\ResourceModel\Udid');
    }
}
