<?php
namespace Baytonia\Overridecatalog\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Category;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product $product,
        \Magento\Bundle\Model\Product\Type $bundleProduct,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->stockRegistry        = $stockRegistry;
        $this->registry             = $registry;
        $this->product              = $product;
        $this->bundleProduct        = $bundleProduct;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        parent::__construct(
            $context
        );
    }

    public function getCurrentProduct() {
        $currentProduct = $this->registry->registry('current_product');
        return $currentProduct;
    }
 
    public function showQtyOptionLabel($_product) {

        $show_quantity_less_option 	= $_product->getResource()->getAttribute('show_quantity_less_option')->getFrontend()->getValue($_product);
        $text_for_quantity_less 	= $_product->getResource()->getAttribute('text_for_quantity_less')->getFrontend()->getValue($_product);
        $qty_threshold 				= $_product->getResource()->getAttribute('qty_threshold')->getFrontend()->getValue($_product);
    
        $productType = $this->getCurrentProduct()->getTypeId();
            if ($productType  == 'simple') {
                if($show_quantity_less_option == "نعم") {
                    if($this->stockRegistry->getStockItem($this->getCurrentProduct()->getId())->getData('qty') <= $qty_threshold) {
                        return true;
                    }
                }else{
                    if($this->stockRegistry->getStockItem($this->getCurrentProduct()->getId())->getData('qty') <= 5) {
                        return false;
                    }
                }
            }
    }

    public function getBundleProductCheck($_product) {

        $selections = $this->bundleProduct->getSelectionsCollection($this->bundleProduct->getOptionsIds($_product), $_product);
        foreach($selections as $selection){
                $defaultqty = $selection->getSelectionQty();
                $salebleqty = $this->getSalableQuantityDataBySku->execute($selection->getSku());
                foreach ($salebleqty as $value) {
                    $salebleqtys = $value['qty'];
                    if($salebleqtys  < $defaultqty) {
                       return true;
                    } else {
                        return false;
                    }
            }
        }

    }
}
