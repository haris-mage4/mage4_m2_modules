<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created By : Rohan Hapani
 */
namespace Baytonia\OutOfStockFix\Block\Adminhtml\AlertGrid;

class AlertGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Rh\Blog\Model\Status
     */
    protected $_status;

    /**
     * @var \Baytonia\OutOfStockFix\Model\AlertFactory
     */
    protected $_alertFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param \Baytonia\OutOfStockFix\Model\AlertFactory             $alertFactory
     * @param \Magento\Framework\Module\Manager       $moduleManager
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Baytonia\OutOfStockFix\Model\AlertFactory $alertFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_alertFactory = $alertFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('alert_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_alertFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'alert_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'alert_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'type' => 'options',
                'options' => array(''=> " " , 0=> 'Guest', 1 => 'Customer')
            ]
        );
       
       
       $this->addColumn(
            'product_name',
            [
                'header' => __('Product'),
                'index' => 'product_name',
            ]
        );
        
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_name',
            ]
        );
        
        $this->addColumn(
            'send_date',
            [
                'header' => __('Send Date'),
                'index' => 'send_date',
                'type' => 'datetime'
            ]
        );

        

        $block = $this->getLayout()->getBlock('grid.bottom.links');

        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}