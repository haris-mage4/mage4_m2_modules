<?php
namespace Baytonia\OutOfStockFix\Observer;

class UpdateProductQty implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        return true;
     
     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
     $logger = $objectManager->create(\Psr\Log\LoggerInterface::class);
    
        
     $order = $observer->getEvent()->getOrder();
     $orderId = $order->getId();
     $logger->debug('Here it is'); 
     $visibleItems = $order->getAllVisibleItems();
     
     foreach($visibleItems as $_item){
        $_product = $_item->getProduct();
        $logger->debug("Product Sku " . $_product->getSku() . "<br>");
        //echo "Product Sku " . $_product->getSku() . "<br>";
           $_stockItem = $_product->getExtensionAttributes()->getStockItem();
           if($_stockItem){
            //echo "skock Item in <br>";
            $logger->debug("skock Item in <br>");
           $manageStock = $_stockItem->getData("manage_stock");
           $_currentQty = $_stockItem->getData("qty");
           
           //echo "Before Login $manageStock : : : $_currentQty <br>";
           $logger->debug("Before Login $manageStock : : : $_currentQty <br>");
           if(!$manageStock){
            
            $qtyOrdered = $_item->QtyOrdered();
            $actualQty = $_currentQty - $qtyOrdered;
            
            
            //echo "Quantities $qtyOrdered : : : $actualQty <br>";
            $logger->debug("Quantities $qtyOrdered : : : $actualQty <br>");
            
            $_stockItem->setData("manage_stock",1);
            $_stockItem->setData("qty",$actualQty);
            $_stockItem->setData("manage_stock",0)->save();
            
            
           }
               }
        
     }
     
    }
}