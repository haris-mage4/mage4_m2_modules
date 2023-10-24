<?php 
namespace Baytonia\CustomApis\Api;
 
 
interface WebServicesManagementInterface {


	/**
	 * GET for Post api
	 * @param string $param
	 * @return string
	 */
	
	public function getSearchQuery($param);
    
    /**
	 * GET for Post api
	 * @param string $param
     * @param string $param
	 * @return string
	 */
    
    public function getSearchResults($param,$pagenumber);
    
    /**
	 * GET for Post api
	 * @param string $categoryid
	 * @return string
	 */
	
	public function getSubCategories($categoryid);
    
    /**
     * Create referral link for customer
     *
     * @param int $customerId
     * @param int $websiteId
     * @return string
     */
    public function getReferralData($customerId, $websiteId);
}