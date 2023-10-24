<?php

namespace Baytonia\BankInstallment\Model\ResourceModel;

class Bank extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('baytonia_bankinstallment_bank', 'entity_id');
    }
}
