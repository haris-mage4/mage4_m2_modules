<?php
namespace Baytonia\CustomApis\Model;

use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Model\ResourceModel\Profile\Collection;

class LookBookManagement
{


    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface, \Magento\Store\Model\StoreManagerInterface
        $storeManager, \Magento\Store\Model\StoreRepository $storeRepository, \Magezon\LookBook\Helper\Data
        $lookbookHelper, \Magezon\LookBook\Model\ResourceModel\Profile\Collection $lookbookCollection, \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $lookbookCategoryFactory, \Baytonia\CustomApis\Helper\Cache $cacheHelper, \Magezon\LookBook\Block\Sidebar\Categories $categoriesBlock, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->_scopeConfig = $scopeInterface;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->lookbookHelper = $lookbookHelper;
        $this->lookbookCollection = $lookbookCollection;
        $this->lookbookCategories = $lookbookCategoryFactory;
        $this->_cacheHelper = $cacheHelper;
        $this->_categoriesBlock = $categoriesBlock;
        $this->productRepository = $productRepository;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getLookBookProfile($profileid)
    {
        $storeId = 1;
        $cacheId = $this->_cacheHelper->getId("lookbook_profile", $storeId,array("profile_id"=>$profileid));
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                echo $cache;
                die();
            }
        
        $_selectedProfile = $this->lookbookCollection->addFieldToFilter("profile_id",$profileid)->getFirstItem();
        
        $_returnData = array();
        if($_selectedProfile->getProfileId()){
            $_returnData["success"] = true;
            $_profileData =  $_selectedProfile->getData();
            $_profileData["image"] = $_selectedProfile->getImageUrl();
            $_profileData["url"] = $_selectedProfile->getUrl();
            
            
            $categories = array();
            $categoryList = $_selectedProfile->getCategoryList();
				    foreach ($categoryList as $key => $category) {
				       $categories[] = array("category_id"=>$category->getData("category_id"),"title"=>$category->getData("title"));
				    }
            $_profileData["categories"] = $categories;
            
            
            $_markers = $_selectedProfile->getListMarker();
            
            $_markerData = array();
            
            $_productCollection = array();
            $_skus = array();
            foreach($_markers as $marker){
                
                try{
                   $_product = $this->productRepository->get($marker->getData("sku"))->getData(); 
                }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
                   $_product = $e->getMessage();
                }
                
                if(isset($_product['estimated_shipping_text']) && 
                    $this->isJson($_product['estimated_shipping_text'])){
                    $_product['estimated_shipping_text'] = json_decode($_product['estimated_shipping_text']);
                }

                $_markerData[] = array("marker_label" => $marker->getData("marker_label"),
                 "title" => $marker->getData("title"),
                 "description" => $marker->getData("description"),
                 "left" => $marker->getData("left"),
                 "top" => $marker->getData("top"),
                 "sku" => $marker->getData("sku"),
                 "popup" => $marker->getData("popup"),
                 "product"=>$_product);
                 
                 
                 
                 
            }
            
            $_profileData["marker"] = $_markerData;
            $_returnData["profile_data"] = $_profileData;
        }else{
            
            $_returnData["success"] = false;
            $_returnData["message"] = "No Profile found with this id : $profileid";
            
        }
        
        $this->_cacheHelper->save(json_encode($_returnData, JSON_UNESCAPED_UNICODE), $cacheId);
            echo json_encode($_returnData, JSON_UNESCAPED_UNICODE);
            die();
        
    }
    
    public function isJson($string) {
       json_decode($string);
       return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * {@inheritdoc}
     */
    public function getLookBookCategory($categoryid)
    {
        $storeId = 1;
        $cacheId = $this->_cacheHelper->getId("lookbook_category", $storeId,array("category_id"=>$categoryid));
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                echo $cache;
                die();
            }
        
        $_selectedCategory = $this->lookbookCategories->create()->addFieldToFilter("category_id",$categoryid)->getFirstItem();
        
        $_returnData = array();
        if($_selectedCategory->getCategoryId()){
            $_returnData["success"] = true;
            $_categoryData =  $_selectedCategory->getData();
            $_profilesData = $_selectedCategory->getProfileCollection();
            $_profiles = array();
            foreach($_profilesData as $_profile){
              $_profileData = $this->getProfileData($_profile);
            $_profiles[] = $_profileData;  
            }
            $_categoryData["profiles"] = $_profiles;
            
            $_returnData["profiles"] = $_profiles;
            
        }else{
            
            $_returnData["success"] = false;
            $_returnData["message"] = "No Category found with this id : $categoryid";
            
        }
        
        $this->_cacheHelper->save(json_encode($_returnData, JSON_UNESCAPED_UNICODE), $cacheId);
            echo json_encode($_returnData, JSON_UNESCAPED_UNICODE);
            die();
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLookBookCategories($currentPage = 1)
    {
        try {
            $storeId = 1;
            $_perPage = 5;
        
        $cacheId = $this->_cacheHelper->getId("lookbook_categories", $storeId,array("current_page"=>$currentPage));
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                //echo $cache;
                //die();
            }
        
        
        $_profiles = array();
        $_profileCollection = $this->lookbookCollection->addFieldToFilter("is_active",1);
        
        $_profileCollection = $_profileCollection->setPageSize($_perPage);
        $_totalpages = $_profileCollection->getLastPageNumber();
                $_profileCollection->setCurPage($currentPage);
        foreach($_profileCollection as $_profile){
            $_profileData = $this->getProfileData($_profile);
            $_profiles[] = $_profileData;   
        }
        $ress["total_pages"] = $_totalpages;
        $ress["profiles"] = $_profiles;
        
        $allCategories = $this->_categoriesBlock->getCollection();
        $categories = array();
        foreach($allCategories as $_category){
            $_categoryData = array();
            $_categoryData["category_id"] = $_category->getData("category_id");
            $_categoryData["identifier"] = $_category->getData("identifier");
            $_categoryData["title"] = $_category->getData("title");
            $_categoryData["is_active"] = $_category->getData("is_active");
            $_categoryData["position"] = $_category->getData("position");
            $_categoryData["total_profiles"] = $_category->getData("total_profiles");
            $categories[] = $_categoryData;
        }
        
        $ress["categories"] = $categories;
        $this->_cacheHelper->save(json_encode($ress, JSON_UNESCAPED_UNICODE), $cacheId);
            echo json_encode($ress, JSON_UNESCAPED_UNICODE);
            die();

        }
        catch (\Exception $e) {
            $ress["message"] = __($e->getMessage());
            $dataTosend = json_encode($ress, JSON_UNESCAPED_UNICODE);
            echo $dataTosend;
            die();
        }


    }
    

    /**
     * {@inheritdoc}
     */
    public function getLookBookData($currentPage = 1)
    {
        try {
            $storeId = 1;
            $_perPage = 5;
        
        $cacheId = $this->_cacheHelper->getId("lookbook_page", $storeId,array("current_page"=>$currentPage));
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                echo $cache;
                die();
            }
        
        
        $_profiles = array();
        $_profileCollection = $this->lookbookCollection->addFieldToFilter("is_active",1);
        
        $_profileCollection = $_profileCollection->setPageSize($_perPage);
        $_totalpages = $_profileCollection->getLastPageNumber();
                $_profileCollection->setCurPage($currentPage);
        foreach($_profileCollection as $_profile){
            $_profileData = $this->getProfileData($_profile);
            $_profiles[] = $_profileData;   
        }
        $ress["total_pages"] = $_totalpages;
        $ress["profiles"] = $_profiles;
        
        $allCategories = $this->_categoriesBlock->getCollection();
        $categories = array();
        foreach($allCategories as $_category){
            $_categoryData = array();
            $_categoryData["category_id"] = $_category->getData("category_id");
            $_categoryData["identifier"] = $_category->getData("identifier");
            $_categoryData["title"] = $_category->getData("title");
            $_categoryData["is_active"] = $_category->getData("is_active");
            $_categoryData["position"] = $_category->getData("position");
            $_categoryData["total_profiles"] = $_category->getData("total_profiles");
            $categories[] = $_categoryData;
        }
        
        $ress["categories"] = $categories;
        $this->_cacheHelper->save(json_encode($ress, JSON_UNESCAPED_UNICODE), $cacheId);
            echo json_encode($ress, JSON_UNESCAPED_UNICODE);
            die();

        }
        catch (\Exception $e) {
            $ress["message"] = __($e->getMessage());
            $dataTosend = json_encode($ress, JSON_UNESCAPED_UNICODE);
            echo $dataTosend;
            die();
        }


    }
    
    protected function getProfileData($_profile){
        
        $_profileData = array();
            $_profileData["profile_id"] = $_profile->getData("profile_id");
            $_profileData["title"] = $_profile->getData("title");
            $_profileData["description"] = $_profile->getData("description");
            $_profileData["identifier"] = $_profile->getData("identifier");
            $_profileData["image"] = $_profile->getImageUrl();
            $_profileData["url"] = $_profile->getUrl();
            $categories = array();
            $categoryList = $_profile->getCategoryList();
				    foreach ($categoryList as $key => $category) {
				       $categories[] = array("category_id"=>$category->getData("category_id"),"title"=>$category->getData("title"));
				    }
            $_profileData["categories"] = $categories;
            //$_profileData["data"] = $_profile->getData();
        
        return $_profileData;
    }
}
