<?php

namespace Mage4\OrderGrid\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected $helper;

    public function __construct(
        EntityFactory $entityFactory,
        Logger        $logger,
        FetchStrategy $fetchStrategy,
        EventManager  $eventManager,
                      $mainTable = 'sales_order_grid',
                      $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('sales_invoice');
        $this->getSelect()
            ->joinLeft('sales_order', 'main_table.entity_id = sales_order.entity_id', ['merchant_account' => 'merchant_account'])
            ->joinLeft($joinTable, 'main_table.entity_id = sales_invoice.order_id',
            ['inv_status' => 'entity_id'])
            ->columns('IF(sales_invoice.entity_id, "Invoiced", "Invoice Pending") as inv_status ');
        parent::_renderFiltersBefore();
    }
}

