<?php

namespace Baytonia\BankInstallment\Model;
use Baytonia\BankInstallment\Model\Status;

class BankRepository
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Baytonia\BankInstallment\Model\ResourceModel\Bank\CollectionFactory
     */
    protected $_bankCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Baytonia\BankInstallment\Model\ResourceModel\Bank\CollectionFactory $bankCollectionFactory
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_bankCollectionFactory = $bankCollectionFactory;
    }
    
    /**
     * get bank collection of bankinstallment.
     *
     * @return \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection
     */
    public function getBankCollection()
    {
        $storeViewId = $this->_storeManager->getStore()->getId();

        /** @var \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection $bankCollection */
        $bankCollection = $this->_bankCollectionFactory->create()
            ->setStoreViewId($storeViewId)            
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('store_id', ['in' => [0,$storeViewId]])
            ->setOrder('sort_order', 'ASC');
        
        return $bankCollection;
    }
    
    
    /**
     * get categories array.
     *
     * @return array
     */
    public function getCategoriesArray()
    {
        $categoriesArray = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->load()
            ->toArray();

        $categories = array();
        foreach ($categoriesArray as $categoryId => $category) {
            if (isset($category['name']) && isset($category['level'])) {
                $categories[] = array(
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $categoryId,
                );
            }
        }

        return $categories;
    }
}