<?php

namespace Baytonia\BankInstallment\Block\Adminhtml;

class Bank extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_bank';
        $this->_blockGroup = 'Baytonia_BankInstallment';
        $this->_headerText = __('Banks');
        $this->_addButtonLabel = __('Add New Bank Logo');
        parent::_construct();
    }
}
