<?php

namespace Baytonia\CustomCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class HideGroupedIfSimpleNotAvailable implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;
    
    /**
     *
     * @var \Magento\GroupedProduct\Model\ResourceModel\Product\Link
     */
    protected $productLinks;
    
    /**
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * 
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\GroupedProduct\Model\ResourceModel\Product\Link $productLinks
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\GroupedProduct\Model\ResourceModel\Product\Link $productLinks,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->productLinks = $productLinks;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        
        if ($product->getTypeId()=='grouped') {
            $childrenIds = $this->getChildrenIds($product->getId());
            $isOutOfStock = false;
            foreach ($childrenIds[3] as $childrenId) {
                $product = $this->getProductById($childrenId, $this->storeManager->getStore()->getId());
                if ($product) {
                    if ($product->getStatus() == Status::STATUS_DISABLED || !$product->getQuantityAndStockStatus()['is_in_stock']) {
                        $isOutOfStock = true;
                        break;
                    }
                }
            }
            if ($isOutOfStock) {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        }
    }
    
    /**
     * 
     * @param int $parentId
     * @return array
     */
    public function getChildrenIds($parentId)
    {
        return $this->productLinks->getChildrenIds(
            $parentId,
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
        );
    }
    
    /**
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductById($productId, $storeId) {
        try{
            $product = $this->productRepository->getById($productId, false, $storeId);
            return $product;
        } catch (\Exception $e) {
            return false;
        }
    }
}