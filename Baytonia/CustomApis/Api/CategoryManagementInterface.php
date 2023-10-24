<?php 
namespace Baytonia\CustomApis\Api;
 
 
interface CategoryManagementInterface {

    /**
	 * GET for Post api
	 * @param string $storeId
     * @param string $categoryid
	 * @return string
	 */
    
    public function getProductSorting($storeId,$categoryid);
    
    /**
	 * GET for Post api
	 * @param string $id
     * @param string $currency
     * @param string $customerToken
     * @param string $width
     * @param string $sortData
     * @param string $pageNumber
     * @param string $websiteId
     * @param string $quoteId
     * @param string $storeId
     * @param string $filterData
	 * @return string
	 */
    
    public function getCategoryPageDetails($id, $currency, $customerToken, $width,$sortData = "" ,$pageNumber =
        1, $websiteId = 1, $quoteId = 0, $storeId = 1,$filterData = "");
    
    /**
	 * GET for Post api
	 * @param string $categoryid
     * @param string $storeId
	 * @return string
	 */
    
    public function getCategoryLayerData($categoryid,$storeId = 1);
}