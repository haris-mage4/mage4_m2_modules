<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

class NewAction extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank
{
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();
         $this->_getSession()->unsBankName();
         $this->_getSession()->unsImageAlt();
        return $resultForward->forward('edit');
    }
}
