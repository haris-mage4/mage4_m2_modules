<?php 
namespace Baytonia\CustomApis\Api;
 
 
interface LookBookManagementInterface {

    /**
	 * GET for Post api
     * @param int $currentPage
	 * @return string
	 */
    
    public function getLookBookData($currentPage = 1);
    
    /**
	 * GET for Post api
     * @param int $profileid
	 * @return string
	 */
    
    public function getLookBookProfile($profileid);
    
    /**
	 * GET for Post api
     * @param int $categoryid
	 * @return string
	 */
    
    public function getLookBookCategory($categoryid);
    /**
	 * GET for Post api
     * @param int $currentPage
	 * @return string
	 */
    
    public function getLookBookCategories($currentPage = 1);
}