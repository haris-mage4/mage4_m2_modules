<?php 
namespace Baytonia\ReviewAPI\Api;
 
 
interface WebServicesManagementInterface {


	/**
	 * GET for Post api
	 * @param string $param
     * @param int $pagenumber
	 * @return string
	 */
	
	public function getProductReview($param,$pagenumber);
    
    /**
	 * POST for Post api
     * @param int $productId
	 * @param string $nickname
     * @param string $title
     * @param string $detail
     * @param string $image
     * @param int $is_recommended
     * @param string $rating
     * @param int $storeId
     * @param string $customerToken
	 * @return string
	 */
	
	public function addProductReview($productId,$nickname,$title,$detail,$image,$is_recommended,$rating,$storeId,$customerToken);
    
    
    
    /**
	 * POST for Post api
     * @param int $reviewId
	 * @param string $remote_ip
     * @param string $vote_type
	 * @return string
	 */
	
	public function addReviewVote($reviewId,$remote_ip,$vote_type);
    
}