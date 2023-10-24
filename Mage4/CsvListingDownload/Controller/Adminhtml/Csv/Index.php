<?php

namespace Mage4\CsvListingDownload\Controller\Adminhtml\Csv;


class Index extends \Mage4\CsvListingDownload\Controller\Adminhtml\Csv
{

    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }
}
