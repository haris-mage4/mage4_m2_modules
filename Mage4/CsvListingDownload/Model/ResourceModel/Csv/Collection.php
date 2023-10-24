<?php


namespace Mage4\CsvListingDownload\Model\ResourceModel\Csv;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'csv_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mage4\CsvListingDownload\Model\Csv', 'Mage4\CsvListingDownload\Model\ResourceModel\Csv');
    }
}
