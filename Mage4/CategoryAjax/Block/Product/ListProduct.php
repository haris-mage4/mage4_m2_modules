<?php
namespace Mage4\CategoryAjax\Block\Product;

use Magento\Framework\View\Element\Template;
class ListProduct extends Template
{
    protected $categoryFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
	)
	{
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }
    public function getProductCollectionFromCategory($categoryId) {
        $category = $this->categoryFactory->create()->load($categoryId);
        return $category->getProductCollection()->addAttributeToSelect('*');
    }
}
