<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model\Adminhtml\System\Config\Source\Customer;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Group implements ArrayInterface
{
    protected $groupCollectionFactory;

    public function __construct(CollectionFactory $groupCollectionFactory)
    {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = $this->groupCollectionFactory->create()->loadData()->toOptionArray();
        return $options;
    }
}
