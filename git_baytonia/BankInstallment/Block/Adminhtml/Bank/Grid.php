<?php

namespace Baytonia\BankInstallment\Block\Adminhtml\Bank;

use Baytonia\BankInstallment\Model\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * bank collection factory.
     *
     * @var \Baytonia\BankInstallment\Model\ResourceModel\Bank\CollectionFactory
     */
    protected $_bankCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Baytonia\BankInstallment\Model\ResourceModel\Bank\CollectionFactory $bankCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Baytonia\BankInstallment\Model\ResourceModel\Bank\CollectionFactory $bankCollectionFactory,
        array $data = []
    ) {
        $this->_bankCollectionFactory = $bankCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('bankGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $storeViewId = $this->getRequest()->getParam('store');

        /** @var \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection $collection */
        $collection = $this->_bankCollectionFactory->create()->setStoreViewId($storeViewId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Bank ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
                    'store_id',
                    [
                        'header' => __('Store Views'),
                        'index' => 'store_id',                        
                        'type' => 'store',
                        'store_all' => true,
                        'store_view' => true,
                        'renderer'=>  'Baytonia\BankInstallment\Block\Adminhtml\Bank\Edit\Tab\Renderer\Store',
                        'filter_condition_callback' => [$this, '_filterStoreCondition']
                    ]
                );
        $this->addColumn(
            'name',
            [
                'header' => __('Bank Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'image',
            [
                'header' => __('Image'),
                'class' => 'xxx',
                'width' => '50px',
                'filter' => false,
                'renderer' => 'Baytonia\BankInstallment\Block\Adminhtml\Bank\Helper\Renderer\Image',
            ]
        );

        $this->addColumn(
            'image_alt',
            [
                'header' => __('Image Alt'),
                'index' => 'image_alt',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => Status::getAvailableStatuses(),
            ]
        );
        
        $this->addColumn(
            'sort_order',
            [
                'header' => __('Sort Order'),
                'index' => 'sort_order',
                'filter' => false,
            ]
        );
        
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        $this->addExportType('*/*/exportExcel', __('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * get bankinstallment vailable option
     *
     * @return array
     */

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('bank');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('bankinstallment/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        $statuses = Status::getAvailableStatuses();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('bankinstallment/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses,
                    ],
                ],
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * get row url
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['entity_id' => $row->getId()]
        );
    }
}
