<?php

namespace  InformaticsCommerce\ProductAskQuestions\Model\ResourceModel\Question;

class Collection extends  \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'InformaticsCommerce\ProductAskQuestions\Model\Question',
            'InformaticsCommerce\ProductAskQuestions\Model\ResourceModel\Question'
        );
    }
}
