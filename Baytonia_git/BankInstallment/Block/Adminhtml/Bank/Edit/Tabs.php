<?php

namespace Baytonia\BankInstallment\Block\Adminhtml\Bank\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * construct.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bank_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bank Information'));
    }
}
