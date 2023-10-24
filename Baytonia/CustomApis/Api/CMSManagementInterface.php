<?php 
namespace Baytonia\CustomApis\Api;
 
 
interface CMSManagementInterface {

    /**
	 * GET for Post api
     * @param int $pageid
	 * @return string
	 */
    
    public function getCMSpages($pageid);
    
}