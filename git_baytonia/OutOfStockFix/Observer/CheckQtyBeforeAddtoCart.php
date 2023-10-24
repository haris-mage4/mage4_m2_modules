<?php

namespace Baytonia\OutOfStockFix\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Http\Context as customerSession;
use Magento\Framework\App\ResourceConnection;

class CheckQtyBeforeAddtoCart implements ObserverInterface{
    protected $cart;
    protected $messageManager;
    protected $redirect;
    protected $request;
    protected $product;
    protected $customerSession;
    protected $resourceConnection;

    public function __construct(RedirectInterface $redirect, Cart $cart, ManagerInterface $messageManager,  RequestInterface $request, Product $product, customerSession $session, ResourceConnection $resourceConnection){
        $this->redirect = $redirect;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->product = $product;
        $this->customerSession = $session;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        $itemQty = 0;
        $postValues = $this->request->getPostValue();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        if ($request->getFullActionName() == 'catalog_product_view') {
            $qty = $postValues['qty'];
            $product = $postValues['product'];
            $cartItemsCount = $this->cart->getQuote()->getItemsCount();
            $getItems = $this->cart->getQuote()->getItems();
            $productStock = $this->getProductQtyQuery($product);
            if($cartItemsCount > 0){
                foreach ($getItems as $item){
                    if($product == $item->getProductId()){
                        $itemQty += $item->getQty();
                        break;
                    }
                }
            }
            $totalQty = $itemQty+$qty;
            //your code to restrict add to cart
            if(!empty($productStock)){ 
                if($productStock['manage_stock'] == 0 && $totalQty > $productStock['qty']){
                    $observer->getRequest()->setParam('product', false);
                    $this->messageManager->addErrorMessage(__('The requested qty is not available'));
                }
            }
        }
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
                ['manage_stock','qty']
            )
            ->where(
                "c.product_id = :product_id"
            );
        $bind = ['product_id'=>$product_id];
        $productStock = $connection->fetchRow($select, $bind);

        return $productStock;
    }
}