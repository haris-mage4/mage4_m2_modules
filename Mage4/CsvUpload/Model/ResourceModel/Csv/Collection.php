<?php

namespace Mage4\CsvUpload\Model\ResourceModel\Csv;

/**
 * Mage4 CsvUpload Collection class
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Mage4\CsvUpload\Model\Csv::class,
            \Mage4\CsvUpload\Model\ResourceModel\Csv::class
        );
    }
}
