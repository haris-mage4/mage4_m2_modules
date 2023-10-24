<?php

namespace Baytonia\OutOfStockFix\Plugin;

use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\App\ResourceConnection;

class ProductAvailability
{
    private $getSalableQuantityDataBySku;



    protected $productdata;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;


    public function __construct( \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, GetSalableQuantityDataBySku $getSalableQuantityDataBySku, \Magento\Catalog\Model\ProductFactory $productdata, \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository, ResourceConnection $resourceConnection)
    {
        $this->stockRegistry = $stockRegistry;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->_stockItemRepository = $stockItemRepository;
        $this->productdata = $productdata;
        $this->resourceConnection = $resourceConnection;
    }

    public function getStockItem($productId)
    {
        try {
            return $this->_stockItemRepository->get($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

    }

    public function afterIsAvailable(\Magento\Catalog\Model\Product $subject, $result)
    {
        $sku = $subject->getSku(); //pass your product sku
        $type = $subject->getTypeId();

        if ($type == "simple") {
            if ($sku) {
                try {
                
           
		/** Changed Code */	
             $manageStock = 1;
                    $__qty = 0;

                    $item = $this->stockRegistry->getStockItemBySku($sku);
                    if ($item) {
                        $manageStock = $item->getManageStock();
                        $__qty = $item->getQty();
                    }

                    if ($manageStock == 0) {
                        if ($__qty > 0) { 
                            $result = true;
                        }
                        if ($__qty <= 0) {
                            $result = false;
                        }
                        return $result;
                    } 
                    
                    /** Manage stock yes, backorders, check neg values */
                    if ($manageStock == 1) {
                        if ($item->getBackorders() == 0) {
                            $result = $item->getIsInStock();
                        } elseif ($item->getBackorders() == 1) {
                            $salable = $this->getSalableQuantityDataBySku->execute($sku);
                            if (count($salable)) {
                                foreach ($salable as $stkitem) {
                                    if ($stkitem['qty'] > 0) {
                                        $result = true;
                                    } else {                                  
                                        $result = false;
                                    }
                                }
                            }
                        }
                        return $result;
                    }
                    /** Manage stock yes, backorders, check neg values */
                    
                    
               
//                        if ($manageStock) {
//                            $salable = $this->getSalableQuantityDataBySku->execute($sku);
//                            $qty = 0;
//                            if (count($salable)) {
//                                foreach ($salable as $stkitem) {
//                                    $qty = $qty + $stkitem["qty"];
//                                }
//                            }
//
//                            if ($qty <= 0) {
//                                return false;
//                            }
//                        } else {
//                            if ($__qty <= 0) {
//                                return false;
//                            }
//                        }
 
                    return $result;


                    /**  $manageStock = 1;
                    $__qty = 0;

                    $_stockItem = $this->getStockItem($subject->getId());
                    if ($_stockItem) {
                    $manageStock = $_stockItem->getData("manage_stock");
                    $__qty = $_stockItem->getData("qty");
                    }
                    if ($manageStock == 0) {
                    if ($__qty > 0) {
                    $result = true;
                    }
                    if ($__qty <= 0) {
                    $result = false;
                    }
                    }

                    //                        if ($manageStock) {
                    //                            $salable = $this->getSalableQuantityDataBySku->execute($sku);
                    //                            $qty = 0;
                    //                            if (count($salable)) {
                    //                                foreach ($salable as $stkitem) {
                    //                                    $qty = $qty + $stkitem["qty"];
                    //                                }
                    //                            }
                    //
                    //                            if ($qty <= 0) {
                    //                                return false;
                    //                            }
                    //                        } else {
                    //                            if ($__qty <= 0) {
                    //                                return false;
                    //                            }
                    //                        }

                    return $result; */
                } catch (\Magento\Framework\Exception\InputException $exception) {

                }

            }
        } else
            if ($type == "configurable") {

                $_allSkus = array();
                $_outOfStockSkus = array();
                $_children = $subject->getTypeInstance()->getUsedProducts($subject);
                foreach ($_children as $child) {

                    $childsku = $child->getSku();
                    $manageStock = 1;
                    $__qty = 0;
                    $_stockItem = $this->getStockItem($child->getId());
                    if ($_stockItem) {
                        $manageStock = $_stockItem->getData("manage_stock");
                        $__qty = $_stockItem->getData("qty");
                    }

                    if ($manageStock) {
                        $_allSkus[] = $childsku;
                        $salable = $this->getSalableQuantityDataBySku->execute($childsku);
                        $qty = 0;
                        if (count($salable)) {
                            foreach ($salable as $stkitem) {
                                $qty = $qty + $stkitem["qty"];
                            }
                        }

                        if ($qty <= 0) {
                            $_outOfStockSkus[] = $childsku;
                        }

                    } else {
                        if ($__qty <= 0) {
                            $_outOfStockSkus[] = $childsku;
                        }
                    }

                }

                if ($_allSkus == $_outOfStockSkus) {

                    return false;
                }
            } elseif ($type == "bundle") {

                $typeInstance = $subject->getTypeInstance();
                $requiredChildrenIds = $typeInstance->getChildrenIds($subject->getId(), false);
                $parentItem = $this->stockRegistry->getStockItemBySku($sku);
                $checkAvailable = [];
                foreach ($requiredChildrenIds as $Childrenkey => $Childrenvalue) {
                    foreach ($Childrenvalue as $key => $value) {
                        $child = $this->productdata->create()->load($value);
                        echo $child->getData('salable_quantity');
                        $manageStock = 1;
                        $__qty = 0;
                        $item = $this->stockRegistry->getStockItemBySku($child->getSku());
                        if ($item) {
                            $manageStock = $item->getManageStock();
                            $__qty = $item->getQty();
                        }

                        if ($manageStock == 0) {
                            if ($__qty > 0) {
                                $checkAvailable[] = true;
                            }
                            if ($__qty <= 0) {
                                $checkAvailable[] = false;
                            }
                        }

                        if ($manageStock == 1) {
                            if ($item->getBackorders() == 0) {
                                $checkAvailable[] = $item->getIsInStock();
                            } elseif ($item->getBackorders() == 1) {
                                $salable = $this->getSalableQuantityDataBySku->execute($child->getSku());
                                if (count($salable)) {
                                    foreach ($salable as $stkitem) {
                                        if ($stkitem['qty'] > 0) {
                                            $checkAvailable[] = true;
                                        } else {
                                            $checkAvailable[] = false;
                                        }
                                    }
                                }
                            }
                        }

                    }

                }


                $result = (in_array(false, $checkAvailable)) ? false : true;

                if($parentItem->getManageStock()) {
                    $isInStock = $parentItem->getIsInStock();
                    if ($isInStock === false){
                        $result = $isInStock;
                    }
                }

                return $result;
            }

        return $result;

    }

    /**
     * get Product Qty Query
     *
     * @return array
     */
    public function getProductQtyQuery($product_id)
    {
        $cataloginventoryStockItem = $this->resourceConnection->getTableName('cataloginventory_stock_item');
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['c' => $cataloginventoryStockItem],
                ['manage_stock', 'qty']
            )
            ->where(
                "c.product_id = :product_id"
            );
        $bind = ['product_id' => $product_id];
        $productStock = $connection->fetchRow($select, $bind);

        return $productStock;
    }
}

