<?php

namespace Mage4\Homepage\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;

class MixitUp extends AbstractProduct
{
    private CategoryHelper $categoryHelper;
    private ?CategoryCollection $categoryCollection = null;

	public function __construct(Context $context, CategoryHelper $categoryHelper, array $data = [])
	{
        $this->categoryHelper = $categoryHelper;
		parent::__construct($context, $data);
	}

    public function categories()
    {
        return $this->categoryCollection ?: $this->categoryCollection = $this->categoryHelper->getStoreCategories(false,true,true);
    }

    public function categoryProducts($category) {
        return $category->getProductCollection()->addAttributeToSelect('*');
    }

}