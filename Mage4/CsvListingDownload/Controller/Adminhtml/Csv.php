<?php

namespace Mage4\CsvListingDownload\Controller\Adminhtml;

abstract class Csv extends \Mage4\CsvListingDownload\Controller\Adminhtml\AbstractAction
{
    const PARAM_CRUD_ID = 'csv_id';


    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage4_CsvListingDownload::CsvListingDownload_csv');
    }
}
