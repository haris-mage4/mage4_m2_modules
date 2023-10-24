<?php

namespace Baytonia\BankInstallment\Model;

use \Baytonia\BankInstallment\Api\Data\BankInterface;
use \Magento\Framework\Model\AbstractModel;

class Bank extends AbstractModel implements BankInterface
{
    /**
     * store view id.
     *
     * @var int
     */
    protected $_storeViewId = null;

    /**
     * bank factory.
     *
     * @var \Baytonia\BankInstallment\Model\BankFactory
     */
    protected $_bankFactory;

    /**
     * [$_formFieldHtmlIdPrefix description].
     *
     * @var string
     */
    protected $_formFieldHtmlIdPrefix = 'page_';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * logger.
     *
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_monolog;
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Baytonia\BankInstallment\Model\ResourceModel\Bank $resource
     * @param \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection $resourceCollection
     * @param \Baytonia\BankInstallment\Model\BankFactory $bankFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Logger\Monolog $monolog
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Baytonia\BankInstallment\Model\ResourceModel\Bank $resource,
        \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection $resourceCollection,
        \Baytonia\BankInstallment\Model\BankFactory $bankFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Logger\Monolog $monolog
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_bankFactory = $bankFactory;
        $this->_storeManager = $storeManager;

        $this->_monolog = $monolog;

        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    /**
     * get form field html id prefix.
     *
     * @return string
     */
    public function getFormFieldHtmlIdPrefix()
    {
        return $this->_formFieldHtmlIdPrefix;
    }

    /**
     * get available slides.
     *
     * @return []
     */
    public function getAvailableSlides()
    {
        $option[] = [
            'value' => '',
            'label' => __('---- Please select a Bank Slider ------'),
        ];

        $bankinstallmentCollection = $this->_bankinstallmentCollectionFactory->create();
        foreach ($bankinstallmentCollection as $bankinstallment) {
            $option[] = [
                'value' => $bankinstallment->getId(),
                'label' => $bankinstallment->getTitle(),
            ];
        }

        return $option;
    }

    /**
     * get store attributes.
     *
     * @return array
     */
    public function getStoreAttributes()
    {
        return array(
            'name',
            'status',
            'url',
            'image',
        );
    }

    /**
     * get store view id.
     *
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * set store view id.
     *
     * @param int $storeViewId
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

    /**
     * before save.
     */
    public function beforeSave()
    {
        
        return parent::beforeSave();
    }

    /**
     * after save.
     */
    public function afterSave()
    {
        return parent::afterSave();
    }

    /**
     * load info multistore.
     *
     * @param mixed  $id
     * @param string $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        if ($this->getStoreViewId()) {
            $this->getStoreViewValue();
        }

        return $this;
    }

    /**
     * get store view value.
     *
     * @param string|null $storeViewId
     *
     * @return $this
     */
    public function getStoreViewValue($storeViewId = null)
    {
        
        return $this;
    }

}
