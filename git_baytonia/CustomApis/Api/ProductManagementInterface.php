<?php 
namespace Baytonia\CustomApis\Api;
 
 
interface ProductManagementInterface {
    
    /**
	 * GET for Post api
	 * @param string $productid
	 * @return string
	 */
	
	public function getConfigurableProductOptions($productid);
    
    /**
	 * POST for Post api
     * @param int $productId
	 * @param string $quoteId
     * @param string[] $options
	 * @return string
	 */
	
	public function addProductOptions($productId,$quoteId,$options);
    
    /**
	 * GET for Post api
	 * @param string $productId
     * @param string $storeId
     * @param string $quoteId
     * @param string $width
     * @param string $websiteId
     * @param string $customerToken
     * @param string $currency
     * @param string $sku
	 * @return string
	 */
	
	public function getProductPageDetails($productId,$storeId,$quoteId,$width,$websiteId,$customerToken,$currency,$sku="");
}