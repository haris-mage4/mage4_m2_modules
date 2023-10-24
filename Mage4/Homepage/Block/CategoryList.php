<?php

namespace Mage4\Homepage\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Helper\Category as CategoryHelper;

class CategoryList extends Template
{
    private CategoryHelper $categoryHelper;

	public function __construct(Context $context, CategoryHelper $categoryHelper)
	{
        $this->categoryHelper = $categoryHelper;
		parent::__construct($context);
	}

    public function categories()
    {
        return $this->categoryHelper->getStoreCategories(false,true,true);
    }

    public function categoryProducts($category) {
        return $category->getProductCollection()->addAttributeToSelect(['name', 'url_key', 'url_path', 'sku'])->setOrder('name','asc');
    }
}