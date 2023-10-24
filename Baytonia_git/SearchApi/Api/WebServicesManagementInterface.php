<?php 
namespace Baytonia\SearchApi\Api;
 
 
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
}