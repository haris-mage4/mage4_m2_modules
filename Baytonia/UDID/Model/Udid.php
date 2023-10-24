<?php

namespace Baytonia\UDID\Model;

use \Magento\Framework\Model\AbstractModel;

class Udid extends AbstractModel
{


    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('Baytonia\UDID\Model\ResourceModel\Udid');
    }


}

