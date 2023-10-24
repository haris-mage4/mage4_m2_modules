<?php
namespace Baytonia\ReviewAPI\Model;


class WebServicesManagement
{


    public function __construct(\Amasty\AdvancedReview\Block\Summary $summary, \Magento\Catalog\Model\Product
        $product, \Amasty\AdvancedReview\Helper\BlockHelper $helper, \Amasty\AdvancedReview\Model\Repository\VoteRepository
        $voteRepository, \Amasty\AdvancedReview\Model\ResourceModel\Images\CollectionFactory
        $imageCollection, \Amasty\AdvancedReview\Helper\ImageHelper $imageHelper,\Magento\Framework\Filesystem\Io\File $file,
    \Magento\Framework\Filesystem\DirectoryList $dir, \Magento\Review\Model\Review $_reviewModel,\Amasty\AdvancedReview\Model\ImagesFactory $imagesFactory, \Amasty\AdvancedReview\Model\ImageUploader $imageUploader,\Amasty\AdvancedReview\Model\Repository\ImagesRepository $imagesRepository, \Webkul\MobikulCore\Helper\Data $mobikulhelper,\Amasty\AdvancedReview\Model\VoteFactory $voteFactory)
    {
        $this->summary = $summary;
        $this->product = $product;
        $this->helper = $helper;
        $this->voteRepository = $voteRepository;
        $this->imageCollection = $imageCollection;
        $this->imageHelper = $imageHelper;
        $this->file = $file;
        $this->dir = $dir;
        $this->_reviewModel = $_reviewModel;
        $this->imagesFactory = $imagesFactory;
        $this->imageUploader = $imageUploader;
        $this->imagesRepository = $imagesRepository;
        $this->mobikulhelper = $mobikulhelper;
        $this->voteFactory = $voteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductReview($param, $pagenumber)
    {
        $_productId = (int)$param;
        $pagenumber = (int)$pagenumber;
        $_pageSize = 5;
        $block = $this->summary;
        $product = $this->product->load($_productId);
        $block->setProduct($product);
        $displayedCollection = $block->getReviewsCollection();
        $block->setDisplayedCollection($displayedCollection);
        $sumaryDetails = $block->getDetailedSummary();

        $count = $block->getReviewsCount();
        $rating = $block->getRatingSummary();
        $stars = $block->getRatingSummaryValue();

        $format = $block->getDateFormat() ? : \IntlDateFormatter::MEDIUM;
        $advancedHelper = $this->helper;

        $responseArray = array();
        $responseArray["product_id"] = $_productId;
        $responseArray["count"] = $count;
        $responseArray["rating"] = $rating;
        $responseArray["stars"] = $stars;
        $responseArray["summary"] = $sumaryDetails;

        if ($block->shouldShowRecommended()):
            $responseArray["recomended_percentage"] = $block->getRecomendedPercent();
        endif;


        $displayedCollection = $displayedCollection->addRateVotes()->setPageSize($_pageSize)->
            setCurPage($pagenumber);
        $_items = $displayedCollection->getItems();
        $_reviewArray = array();
        $i = 1;
        foreach ($_items as $review) {
            $_reviewArray[$i]["id"] = (int)$review->getId();
            $_reviewArray[$i]["nick_name"] = $review->getNickname();
            $_reviewArray[$i]["title"] = $review->getTitle();
            $_reviewArray[$i]["details"] = $review->getDetail();
            $_reviewArray[$i]["created_at"] = $block->formatDate($review->getCreatedAt(), $format);


            $ratiings = array();
            $j = 1;
            $reviewarray = $review->getRatingVotes();
            if (count($reviewarray)):
                foreach ($review->getRatingVotes() as $_vote):

                    $ratiings[$j]["rating_code"] = $_vote->getRatingCode();
                    $ratiings[$j]["rating_stars"] = $_vote->getValue();

                    $j++;
                endforeach;
            endif;
            $_reviewArray[$i]["ratings"] = $ratiings;


            $like = $review->getData('like_about');
            $disLike = $review->getData('not_like_about');
            if ($advancedHelper->isProsConsEnabled() && ($like || $disLike)):
                if ($like):
                    $_reviewArray[$i]["like"] = nl2br($block->escapeHtml($like));
                endif;
                if ($disLike):
                    $_reviewArray[$i]["dislike"] = nl2br($block->escapeHtml($disLike));
                endif;

            endif;

            $votes = $this->voteRepository->getVotesCount($review->getId());
            $_reviewArray[$i]["votes"] = $votes;


            $imagecollection = $this->imageCollection->create()->addFieldToSelect('*')->
                addFieldToFilter('review_id', $review->getId());
            $_imageArray = array();
            foreach ($imagecollection as $image) {
                $_imageArray[] = $this->imageHelper->getFullPath($image->getPath());
            }

            $_reviewArray[$i]["images"] = $_imageArray;
            $i++;
        }

        $responseArray["reviews"] = $_reviewArray;


        $responseArray["returnCode"] = 200;
        echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * {@inheritdoc}
     */
    public function addProductReview($productId,$nickname, $title, $detail, $image, $is_recommended,$rating,$storeId,$customerToken)
    {
        $responseArray = array();
        
        $ErrorArray = array();
        $imageNAme = "review_image_" . rand(0,9999) . "_" . rand(0,9999) . "_" . rand(0,9999);
       
        if($image){
          
          if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            $image = substr($image, strpos($image, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                $ErrorArray["invalid_image_type"] = __("Invalid Image Type");
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                $ErrorArray["image_data_error"] = __("Invalid Image Data");
            }
        } else {
            $ErrorArray["image_data_error"] = __("Invalid Image Base 64");
        }
        
        if(count($ErrorArray)){
            $responseArray["success"] = false;
            $responseArray["errors"] = $ErrorArray;
            
            echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        die();
        }
        
        $imageFolder = $this->getImageImportFolder();
        $finalnametoupload = $imageNAme.".".$type;
        $outputImage = "$imageFolder/$finalnametoupload";

        file_put_contents($outputImage, $image);
            
        }
        
        
        $_review = $this->_reviewModel
        ->setEntityPkValue($productId)    //product Id
        ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)// pending/approved
        ->setTitle($title)
        ->setDetail($detail)
        ->setEntityId(1)
        ->setStoreId($storeId)
        ->setStores(1)//get dynamically here 
        ->setNickname($nickname);
        
        if($customerToken && $customerId = $this->mobikulhelper->getCustomerByToken($customerToken)){
            $_review->setCustomerId($customerId);
        }
                
        
         $recommend = (int)$is_recommended;
        if ($recommend) {
            $_review->setIsRecommended($recommend);
        }else{
            $_review->setIsRecommended(\Amasty\AdvancedReview\Model\Sources\Recommend::NOT_RECOMMENDED);
            
        }
        
        
        $_review->save();
        
        $reviewId = $_review->getId();
        
        if(isset($finalnametoupload) && $finalnametoupload && $reviewId){
            /** @var \Amasty\AdvancedReview\Model\Images $model */
        $model = $this->imagesFactory->create();
        $model->setReviewId($reviewId);
        $model->setPath($finalnametoupload);
        $this->imagesRepository->save($model);
        }
        
        

        $responseArray["success"] = true;
        $responseArray["message"] = __("Review Submitted Successfully.");
        echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        die();

    }
    
    /**
     * {@inheritdoc}
     */
    public function addReviewVote($reviewId,$remote_ip,$vote_type)
    {
        $responseArray = array();
        
        $ErrorArray = array();
        
        
        
        
                $type = $vote_type;
                $reviewId = (int)$reviewId;
                if ($reviewId > 0 && in_array($type, ['plus', 'minus', 'update'])) {
                    $ip = $remote_ip;

                    if ($type != 'update') {
                        $type = ($type == 'plus') ? '1' : '0';

                        /** @var  \Amasty\AdvancedReview\Model\Vote $model */
                        $model = $this->voteRepository->getByIdAndIp($reviewId, $ip);
                        $modelType = $model->getType();
                        if ($model->getVoteId()) {
                            $this->voteRepository->delete($model);
                        }

                        if ($modelType === null || $modelType != $type) {
                            $model = $this->voteFactory->create();
                            $model->setIp($ip);
                            $model->setReviewId($reviewId);
                            $model->setType($type);
                            $this->voteRepository->save($model);
                        }
                    }

                    $votesForReview = $this->voteRepository->getVotesCount($reviewId);
                    $voted = $this->voteRepository->getVotesCount($reviewId, $ip);
                    $message = [
                        'data' => $votesForReview,
                        'voted' => $voted
                    ];
                }else{
                    $responseArray["success"] = false;
                    $responseArray["message"] = __("Invalid Request Data.");
                    echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
                    die();
                }
        
        
        
        

        $responseArray["success"] = true;
        $responseArray["message"] = __("Vote Added Successfully.");
        if(isset($message)){
            $responseArray["data"] = $message;
        }
        echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        die();

    }
    
    private function getImageImportFolder()
        {
            $images = $this->dir->getPath('media').'/amasty/review';
            if ( ! file_exists($images)) {
                $this->file->mkdir($images);
            }
            return $images;
        }


}
