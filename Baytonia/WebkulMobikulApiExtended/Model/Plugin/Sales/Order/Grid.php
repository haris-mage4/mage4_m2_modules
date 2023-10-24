<?php

namespace Baytonia\WebkulMobikulApiExtended\Model\Plugin\Sales\Order;
 
 /**
  * Grid Class for order purchase point.
  */
class Grid
{
 
    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'mobikul_orderPurchasePoint';
 
    public function afterSearch($intercepter, $collection)
    {
        echo 'aaa';exit;
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
 
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
 
            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.increment_id = main_table.increment_id",
                    [
                        'purchase_point' => 'co.purchase_point'
                    ]
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
 
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
 
        }
        return $collection;
    }
}
