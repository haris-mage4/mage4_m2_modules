<?php 
namespace Baytonia\CustomApis\Api;
 
 
interface HomePageServicesInterface {

    /**
	 * GET for Post api
	 * @param string $storeid
     * @param string $websiteid
	 * @return string
	 */
    
    public function getSettingsData($storeId,$websiteId);
    
    /**
	 * GET for Post api
	 * @param string $quoteId
     * @param string $storeId
     * @param int $mFactor
     * @param float $width
	 * @return string
	 */
    
    public function getCategoriesData($quoteId = 0,$storeId = 1,$mFactor = 1,$width=1125.000000);
    
    /**
	 * GET for Post api
     * @param string $storeId
     * @param int $mFactor
     * @param float $width
     * @param string $customerToken
	 * @return string
	 */
    
    public function getBlocksData($storeId = 1,$mFactor = 1,$width=1125.000000,$customerToken = "");
    
    /**
	 * GET for Post api
	 * @param string $quoteId
     * @param string $storeId
     * @param string $websiteId
     * @param int $mFactor
     * @param float $width
     * @param string $customerToken
	 * @return string
	 */
    
    public function getFullData($quoteId = 0,$storeId = 1,$websiteId = 1,$mFactor = 1,$width=1125.000000,$customerToken = "");
}