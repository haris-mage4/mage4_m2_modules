<?php

namespace Mage4\ReviewPage\Block;

use Mage4\ReviewPage\Model\ResourceModel\Review\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\View\Element\Template;
use Magento\Review\Model\RatingFactory;

class Reviews extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;
    protected $storeManager;
    protected $ratingFactory;
    protected $productRepository;

    /**
     * constructor.
     * @param CollectionFactory $reviewCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Template\Context                                             $context,
        CollectionFactory                                            $reviewCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface                   $storeManager,
        RatingFactory                                                $ratingFactory,
        ProductRepository                                            $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array                                                        $data = []
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->storeManager = $storeManager;
        $this->ratingFactory = $ratingFactory;
        $this->productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('PRODUCT REVIEWS'));

        if ($this->getAllReviews()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'product.review.pager'
            )->setAvailableLimit([20 => 20, 40 => 40, 100 => 100])->setShowPerPage(true)->setCollection(
                $this->getAllReviews()
            );
            $this->setChild('pager', $pager);
            $this->getAllReviews()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getAllReviews()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 20;
        $reviewsCollection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getStoreId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addProductStatusFilter()
            ->setDateOrder()
            ->setPageSize($pageSize)
            ->setCurPage($page)
            ->addRateVotes();
        foreach ($reviewsCollection as $review) {
            $productId = $review->getEntityPkValue();

            try {
                $product = $this->productRepository->getById($productId);
                $review->setData('product_url', $product->getProductUrl());
                $review->setData('product_name', $product->getName());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        return $reviewsCollection;
    }
}
