<?php

namespace Baytonia\OutOfStockFix\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Alert extends AbstractDb
{


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('guest_alert', 'alert_id');
    }


}

