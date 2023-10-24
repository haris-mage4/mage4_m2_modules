<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Baytonia\OutOfStockFix\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;

/**
 * Sign up for an alert when the product price changes grid
 *
 * @api
 * @since 100.0.2
 */
class Stock extends Extended
{
    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    protected $_stockFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ProductAlert\Model\StockFactory $stockFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Baytonia\OutOfStockFix\Model\AlertFactory $stockFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_stockFactory = $stockFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('alertStock');
        $this->setDefaultSort('add_date');
        $this->setDefaultSort('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no customers for this alert.'));
    }

    /**
     * @inheritDoc
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $productId = $this->getRequest()->getParam('id');
        $websiteId = 0;
        if ($store = $this->getRequest()->getParam('store')) {
            $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        }
        if ($this->moduleManager->isEnabled('Magento_ProductAlert')) {
            $collection = $this->_stockFactory->create()->getCollection()
            ->addFieldToFilter("type",0)
            ->addFieldToFilter("product_id",$productId)->addFieldToFilter("sync_status",0);
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    /**
     * @inheritDoc
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);
        $this->addColumn('updated_at', ['header' => __('Subscribe Date'), 'index' => 'updated_at', 'type' => 'date']);

        return parent::_prepareColumns();
    }

    /**
     * Get grid url.
     *
     * @return string
     */
    public function getGridUrl()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }
        return $this->getUrl('outofstockfix/product/alertsStockGrid', ['id' => $productId, 'store' => $storeId]);
    }
}
