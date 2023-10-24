<?php

namespace Mage4\CsvListingDownload\Model\ResourceModel;


class Csv extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage4_csvupload_csv', 'csv_id' , 'filename');
    }
}
