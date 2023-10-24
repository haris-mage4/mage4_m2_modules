<?php

namespace Baytonia\CustomCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class HideGroupedCategoryPage implements ObserverInterface
{
    
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * 
     * @param \Magento\GroupedProduct\Model\ResourceModel\Product\Link $productLinks
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\GroupedProduct\Model\ResourceModel\Product\Link $productLinks,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productLinks = $productLinks;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        
        foreach ($collection as $key => $product) {
            
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
                    $collection->removeItemByKey($key);
                }
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