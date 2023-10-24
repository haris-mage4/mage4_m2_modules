<?php

namespace Mage4\CsvUpload\Model\ResourceModel;

/**
 * Mage4CsvUpload Csv class
 */
class Csv extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage4_csvupload_csv', 'csv_id');
    }
}
