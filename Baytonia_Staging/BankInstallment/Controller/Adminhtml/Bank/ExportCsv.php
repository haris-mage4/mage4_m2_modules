<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank
{
    public function execute()
    {
        $fileName = 'banks.csv';

        /** @var \\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $content = $resultPage->getLayout()->createBlock('Baytonia\BankInstallment\Block\Adminhtml\Bank\Grid')->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
