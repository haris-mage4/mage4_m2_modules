<?php

namespace Baytonia\AmastyVisualMerchExtended\Model\ResourceModel;

class Product extends \Amasty\VisualMerch\Model\ResourceModel\Product
{
 public function getProductPositionDataByIds($productId): array
 {
     $connection = $this->getConnection();
     $select = $connection->select()->from(
         ['main_table' => $this->getMainTable()],
         ['product_id', 'position']
     )->where('product_id = ?', $productId);
     return $connection->fetchPairs($select);
 }
<<<<<<< HEAD
}
=======
}
>>>>>>> c4e73009da0a4ec43aeb6a465b292dbfb6ecc020
