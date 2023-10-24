<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

class Grid extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();

        return $resultLayout;
    }
}
