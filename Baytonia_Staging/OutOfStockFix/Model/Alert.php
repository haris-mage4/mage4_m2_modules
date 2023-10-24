<?php

namespace Baytonia\OutOfStockFix\Model;

use \Magento\Framework\Model\AbstractModel;

class Alert extends AbstractModel
{


    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('Baytonia\OutOfStockFix\Model\ResourceModel\Alert');
    }


}

