<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model\ResourceModel\Data;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'option_id';

    public function _construct()
    {
        // @codingStandardsIgnoreEnd
        $this->_init('InformaticsCommerce\UseMyShippingAccount\Model\Data', 'InformaticsCommerce\UseMyShippingAccount\Model\ResourceModel\Data');
    }
}
