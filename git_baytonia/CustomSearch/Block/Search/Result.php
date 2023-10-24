<?php
namespace  Baytonia\CustomSearch\Block\Search;
use Magento\Framework\View\Element\Template\Context;
use Baytonia\CustomSearch\Model\Autocomplete;
use Magento\Catalog\Model\ProductFactory;
use  Magento\Catalog\Helper\Image;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Result extends \Magento\Framework\View\Element\Template
{
    protected $autocomplete;
    protected $productloader;
    protected $image;
    protected $listproduct;
    protected $pricehelper;
    protected $productCollectionFactory;

	public function __construct(Context $context,
    Autocomplete $autocomplete,
    ProductFactory $productloader,
    Image $image,
    ListProduct $listproduct,
    Data $pricehelper,
    CollectionFactory $productCollectionFactory
    )
	{
        $this->customSearch = $autocomplete;
        $this->productloader = $productloader;
        $this->image = $image;
        $this->listproduct = $listproduct;
        $this->pricehelper = $pricehelper;
        $this->productCollectionFactory = $productCollectionFactory;
        
		parent::__construct($context);
	}

	public function getResult($queryString)
	{
		//return __('Products here');
        //echo "tets";
        $productIds=$this->customSearch->searchProductsFullText($queryString);
        return $productIds;
}
public function getProductDetials($productId){
    return $this->productloader->create()->load($productId);

}
public function getImageUrl($product){
    return $this->image->init($product, 'product_base_image')->constrainOnly(FALSE)
    ->keepAspectRatio(TRUE)
    ->keepFrame(FALSE)
    ->getUrl();
}
public function getAddToCartUrl($product){
    $addToCartUrl =  $this->listproduct->getAddToCartUrl($product);
    return $addToCartUrl;
}

public function getFormatedPrice($price){
    $formattedPrice = $this->pricehelper->currency($price, true, false);
    return  $formattedPrice;
}

public function loadMoreProductIds($totalProducts,$queryString){
    $collection = $this->productCollectionFactory->create();
    //$collection->addAttributeToFilter('name', ['like' => "%$queryString%"]);
    $collection->addAttributeToFilter('description', ['like' => "%$queryString%"]);
    // array(
    //     array('attribute' => 'someattribute', 'like' => 'value'),
    //     array('attribute' => 'otherattribute', 'like' => 'value'),
    //     array('attribute' => 'anotherattribute', 'like' => 'value'),
    // )

    // $collection->addFieldToFilter(['sku', 'name'],
    // [
    //     ['like' => "%$queryString%"],
    //     ['like' => "%$queryString%"]
    // ]);
    $collection->setPageSize($totalProducts);      
    foreach ($collection as $product) {
        print_r($product->getData());
    }
}
public function getProductCollection()
{
    $collection = $this->productCollectionFactory->create();
   $collection->setPageSize(3);      
   foreach ($collection as $product) {
       print_r($product->getData());
   }
    return $collection;
}

}