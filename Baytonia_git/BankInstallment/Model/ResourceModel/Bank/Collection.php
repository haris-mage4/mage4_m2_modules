<?php

namespace Baytonia\BankInstallment\Model\ResourceModel\Bank;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * store view id.
     *
     * @var int
     */
    protected $_storeViewId = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * added table
     * @var array
     */
    protected $_addedTable = [];

    /**
     * @var bool
     */
    protected $_isLoadBankInstallmentTitle = FALSE;

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Baytonia\BankInstallment\Model\Bank', 'Baytonia\BankInstallment\Model\ResourceModel\Bank');
    }

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Zend_Db_Adapter_Abstract                                    $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb              $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;

        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    /**
     * @param $isLoadBankInstallmentTitle
     * @return $this
     */
    public function setIsLoadBankInstallmentTitle($isLoadBankInstallmentTitle)
    {
        $this->_isLoadBankInstallmentTitle = $isLoadBankInstallmentTitle;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLoadBankInstallmentTitle()
    {
        return $this->_isLoadBankInstallmentTitle;
    }

    /**
     * Before load action.
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->isLoadBankInstallmentTitle()) {
            $this->joinBankInstallmentTitle();
        }

        return parent::_beforeLoad();
    }

    /**
     * join table to get BankInstallment Title of Bank
     * @return $this
     */
    public function joinBankInstallmentTitle()
    {
        $this->getSelect()->joinLeft(
            ['bankinstallmentTable' => $this->getTable('baytonia_bankinstallment_bankinstallment')],
            'main_table.bankinstallment_id = bankinstallmentTable.bankinstallment_id',
            ['title' => 'bankinstallmentTable.title', 'bankinstallment_status' => 'bankinstallmentTable.status']
        );

        return $this;
    }

    /**
     * set order random by bank id
     *
     * @return $this
     */
    public function setOrderRandByBankId()
    {
        $this->getSelect()->orderRand('main_table.entity_id');

        return $this;
    }

    /**
     * get store view id.
     *
     * @return int [description]
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * set store view id.
     *
     * @param int $storeViewId [description]
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

    /**
     * Multi store view.
     *
     * @param string|array      $field
     * @param null|string|array $condition
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $attributes = array(
            'name',
            'status',
            'url',
            'maintable',
        );
        $storeViewId = $this->getStoreViewId();
        
        if (in_array($field, $attributes) && $storeViewId) 
        {
            $mainfieldCondition = $this->_translateCondition("main_table.$field", $condition);
            $this->_select->where($mainfieldCondition, NULL, \Magento\Framework\DB\Select::TYPE_CONDITION);
            return $this;
        }
        
        if ($field == 'store_id') {
            $field = 'main_table.store_id';
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * get read connnection.
     */
    public function getConnection()
    {
        return $this->getResource()->getConnection();
    }

    /**
     * Multi store view.
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($storeViewId = $this->getStoreViewId()) {
            foreach ($this->_items as $item) {
                $item->setStoreViewId($storeViewId)->getStoreViewValue();
            }
        }

        return $this;
    }
}
