<?php 
namespace Baytonia\CustomApis\Model;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider; 
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\Xsearch\Controller\RegistryConstants;
use Amasty\Xsearch\Model\Search\SearchAdapterResolver;
use Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer as RuleViewer;
use Amasty\Xsearch\Model\Config;
use Magento\Catalog\Helper\Image;
 
class WebServicesManagement {
    
    
    /**
     * @var array|null
     */
    private $products;
    
    
    /**
     * @var array|null
     */
    private $counts;
    
    
    
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;
    
    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    private $searchHelper;
    
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;
    
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    
    /**
     * @var SearchAdapterResolver
     */
    private $searchAdapterResolver;
    
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
protected $collectionFactory;

/**
     * @var Config
     */
    private $moduleConfigProvider;

    public function __construct(
        QueryFactory $queryFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        \Amasty\Xsearch\Helper\Data $helper,
        \Magento\CatalogSearch\Helper\Data $searchHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry,
        SearchAdapterResolver $searchAdapterResolver,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        Config $moduleConfigProvider,
        \Amasty\Xsearch\Block\Search\Popular $popsearchblock,
        \Amasty\Xsearch\Block\Search\Recent $recentsearchblock,
        \Amasty\Xsearch\Block\Search\BrowsingHistory $bhsearchblock,
        \Amasty\Xsearch\Block\Search\Category $catsearchblock,
        \Amasty\Xsearch\Block\Search\Product $prodsearchblock,
        Image $imageHelper,\Magento\Store\Model\App\Emulation $emulate,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Apptrian\Subcategories\Helper\Data
        $moduleHelper, \Aheadworks\Raf\Model\Advocate\Account\Viewer $advocateinfo, RuleViewer $ruleViewer
    ) {
        
        $this->queryFactory = $queryFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        $this->layoutFactory = $layoutFactory;
        $this->coreRegistry = $coreRegistry;
        $this->searchAdapterResolver = $searchAdapterResolver;
        $this->collectionFactory = $collectionFactory;
        $this->moduleConfigProvider = $moduleConfigProvider;
        $this->popsearchblock = $popsearchblock;
        $this->catsearchblock = $catsearchblock;
        $this->prodsearchblock = $prodsearchblock;
        $this->bhsearchblock = $bhsearchblock;
        $this->recentsearchblock = $recentsearchblock;
        $this->categoryFactory = $categoryFactory;
        $this->moduleHelper = $moduleHelper;
        $this->advocateinfo = $advocateinfo;
        $this->ruleViewer = $ruleViewer;
        $this->imageHelper = $imageHelper;
        $this->emulate = $emulate;
    }
    
    public function getPositions($position){
        $positions = explode('/', $position);
        $type = isset($positions[0]) ? $positions[0] : false;
        $position = $this->helper->getModuleConfig($position) * 10; // x10 - fix sorting issue
        while (isset($result[$position])) {
            $position++;
        }
        
        return $position;
    }
    
    
    public function getSearchResults($param,$pagenumber)
	{
	   $pagenumber = (int)$pagenumber;
	   $_searchParam = urldecode($param);
       $_resultArray = array();
       $_returnCode = 202;
       $_message = "Searched Successfully";
       
       try{
        $responseArray = array();
       $responseArray["results"] = $_resultArray;
       $responseArray["message"] = $_message;
       $responseArray["returnCode"] = $_returnCode;
       
       
       // Start to search
       
       //Query Object
       /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        $query->setQueryText($_searchParam);
        $query->setStoreId($this->storeManager->getStore()->getId());
        $engine = $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH);
        $query = $this->helper->setStrippedQueryText(
            $query,
            $engine
        ); //replace only for mysql (for a search by products)

        //$this->coreRegistry->register(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY, $query);
        if ($query->getQueryText() != '') {
            if ($this->searchHelper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
        }
        
        
        
        $result = $this->getProducts($query,$pagenumber); 
       
       $responseArray["results"] = $result;
       $count = (isset($this->counts["products"]))?$this->counts["products"]:0;
       $responseArray["total_products"] = $count;
       $total_pages = (isset($this->counts["total_pages"]))?$this->counts["total_pages"]:1;
       $responseArray["total_pages"] = $total_pages;
       $responseArray["message"] = ($count == 0)?"No Products found":$responseArray["message"];
       
       // GetHtml Data
       
       
       
       // Start to search
        
       }catch(exception $e){
        $responseArray = array();
       
       $responseArray["results"] = array();
       $responseArray["message"] = $e->getMessage();
       $responseArray["returnCode"] = 500;
       }
       
    
          echo json_encode($responseArray,JSON_UNESCAPED_UNICODE); die();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchQuery($param)
	{
	   $_searchParam = urldecode($param);
       $_resultArray = array();
       $_returnCode = 202;
       $_message = "Searched Successfully";
       
       try{
        $responseArray = array();
       $responseArray["results"] = $_resultArray;
       $responseArray["message"] = $_message;
       $responseArray["returnCode"] = $_returnCode;
       
       
       // Start to search
       
       //Query Object
       /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        $query->setQueryText($_searchParam);
        $query->setStoreId($this->storeManager->getStore()->getId());
        $engine = $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH);
        $query = $this->helper->setStrippedQueryText(
            $query,
            $engine
        ); //replace only for mysql (for a search by products)

        //$this->coreRegistry->register(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY, $query);
        if ($query->getQueryText() != '') {
            if ($this->searchHelper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
        }
        
        
        
        $result = [];

        if ($this->moduleConfigProvider->isPopularSearchesEnabled()) {
            $result[$this->getPositions(Config::XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION)]["popular_searches"] = $this->popsearchblock->getResults();
        }

        if ($this->helper->getModuleConfig(\Amasty\Xsearch\Helper\Data::XML_PATH_TEMPLATE_PRODUCT_ENABLED)) {
            $result[$this->getPositions(\Amasty\Xsearch\Helper\Data::XML_PATH_TEMPLATE_PRODUCT_POSITION)]["product"] = $this->getProducts($query);
        }
        
        if ($this->helper->getModuleConfig(\Amasty\Xsearch\Helper\Data::XML_PATH_TEMPLATE_CATEGORY_ENABLED)) {
            $result[$this->getPositions(\Amasty\Xsearch\Helper\Data::XML_PATH_TEMPLATE_CATEGORY_POSITION)]["category"] = $this->catsearchblock->getResults();
             
        } 
        
        
        if ($this->helper->getModuleConfig(\Amasty\Xsearch\Helper\Data::XML_PATH_TEMPLATE_PAGE_ENABLED)) {
            //$result[$this->getPositions(\Amasty\Xsearch\Helper\Data::XML_PATH_TEMPLATE_PAGE_POSITION)]["page"] = $this->catsearchblock->getResults();
        }

        if ($this->moduleConfigProvider->isRecentSearchesEnabled()) {
            $result[$this->getPositions(Config::XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION)]["recent_searches"] = $this->recentsearchblock->getResults();
        } 
        
        if ($this->moduleConfigProvider->isBrowsingHistoryEnabled()) {
            $result[$this->getPositions(Config::XML_PATH_BROWSING_HISTORY_POSITION)]["browsing_history"] = $this->bhsearchblock->getResults();
        }

         

        ksort($result);
        
        $newresult = [];
        foreach($result as $position => $data){
            foreach($data as $key => $value){
                $newresult[$key] = $value;
            }
             
        }

        
        
        
        
        
        
        
        
        
       
       
       //Query Object
       
       
       
       // GetHtml Data
       
       
       $responseArray["results"] = $newresult;
       
       $count = (isset($this->counts["products"]))?$this->counts["products"]:0;
       $responseArray["total_products"] = $count;
       $responseArray["message"] = ($count == 0)?"No Products found":$responseArray["message"];
       // GetHtml Data
       
       
       
       // Start to search
        
       }catch(exception $e){
        $responseArray = array();
       
       $responseArray["results"] = array();
       $responseArray["message"] = $e->getMessage();
       $responseArray["returnCode"] = 500;
       }
       
    
          echo json_encode($responseArray,JSON_UNESCAPED_UNICODE); die();
	}
    
    
    public function getProducts($query,$pagenumber = 0)
    {
        if ($this->products === null) {
            $this->products = $this->getResults($query,$pagenumber);

            if ($query && isset($this->counts["products"]) && $this->counts["products"] !== null) {
                $query->saveNumResults($this->counts["products"]);
            }
        }

        return $this->products;
    }
    
    public function getResults($query,$pagenumber = 0)
    {
        
        $result = $query ? $this->searchAdapterResolver->getResults("product", $query) : null;
        
        if ($result !== null) {
            $this->counts["products"] = $result->getResultsCount();
            $searchResult = $result->getItems();
        } else {
             
            $searchResult =  $this->getCollectionData($query,$pagenumber);
        }

        return $searchResult;
    }
    
    public function getLoadedProductCollection($query){
            $productCollection = $this->collectionFactory->create();
            $productCollection->addAttributeToSelect('*')->addFinalPrice();
            $productCollection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $productCollection->addAttributeToFilter('price', array('gt' => 0));
            
            //$productCollection->addAttributeToFilter([['attribute'=>'meta_keyword','like'=> "%".$query->getQueryText()."%"], 
            //['attribute'=>'name','like'=> "%".$query->getQueryText()."%"]]);
            
            $productCollection->addFieldToFilter(array(
    array(
        'attribute' => 'meta_keyword',
        'like' => '%'.$query->getQueryText().'%'),
    array(
        'attribute' => 'name',
        'like' => '%'.$query->getQueryText().'%'),
        array(
        'attribute' => 'sku',
        'like' => '%'.$query->getQueryText().'%')
));
            
            return $productCollection; 
        
        
    }
    
    private function getCollectionData($query,$pagenumber = 0)
    {
        $environment = $this->emulate->startEnvironmentEmulation(1,\Magento\Framework\App\Area::AREA_FRONTEND, true);
        $imageId = "amasty_xsearch_page_list";
        $this->counts["products"] = $this->getLoadedProductCollection($query)->getSize();
         
        $results = [];
        $i = 1; 
        if($pagenumber){
           $_pageSize = 10;
        //$pagenumber = $pagenumber - 1;
        $_productCollection = $this->getLoadedProductCollection($query)->setPageSize($_pageSize); 
        $this->counts["total_pages"] = $_productCollection->getLastPageNumber();
        $_productCollection->setCurPage($pagenumber);
        }else{
           $_pageSize = $this->helper->getModuleConfig("product/limit");
        $_productCollection = $this->getLoadedProductCollection($query)->setPageSize($_pageSize); 
        }
        
        
        foreach ($_productCollection as $product) {
            
            $data['image_url'] = $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();//$this->prodsearchblock->getImage($product, $imageId)->getImageUrl();
            $data['url'] = $product->getProductUrl();
            $data['name'] = $product->getName();
            $data['sku'] = $product->getSku();
            $data['keywords'] = $product->getMetaKeyword();
            $data['description'] = $product->getDescription();
            $data['price'] = $product->getPrice();
            $data['min_price'] = $product->getData('min_price');
            $data['final_price'] = $product->getData('final_price');
            $data['is_salable'] = $product->isSaleable();
            $data['product_data'] = [
                'entity_id' => (string)$product->getId(),
                'request_path' => (string)$product->getRequestPath()
            ];
            
            
            $data['entity_id'] = (string)$product->getId();
            $results[$i] = $data;
            $i++;
        }
        $this->emulate->stopEnvironmentEmulation($environment);
        return $results;
    }
    
    
    public function isExcluded($id = 0)
    {
        if ($id > 0) {
            $excluded = trim($this->moduleHelper->getConfig('apptrian_subcategories/category_page/exclude_ids'),
                ',');

            if (!empty($excluded) && in_array($id, $excluded)) {
                return true;
                // Exclude list is empty
            } else {
                return false;
            }

            // Not a category page
        } else {
            return false;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getSubCategories($categoryid)
    {

        $responseArray = array();
        $_resultArray = array();
        $_message = "No Subcategory found for this category";
        $_returnCode = 404;
        $responseArray["category_id"] = $categoryid;
        $responseArray["results"] = $_resultArray;
        $responseArray["message"] = $_message;
        $responseArray["returnCode"] = $_returnCode;

        $mainModuleHelper = $this->moduleHelper;

        $categoryId = $mainModuleHelper->filterAndValidateCategoryId($categoryid);

        if ($categoryId) {

            if ($this->isExcluded($categoryId)) {
                $responseArray["category_id"] = $categoryid;
                $responseArray["results"] = $_resultArray;
                $responseArray["message"] = "This Category is excluded from the configuration";
                $responseArray["returnCode"] = 202;
                echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
                die();
            }

            $pageType = "category_page";
            $attributesToSelect = ['name', 'url_key', 'url_path', 'image', 'description',
                'meta_description', 'meta_title'];

            if ($mainModuleHelper->getConfig("apptrian_subcategories/$pageType/image") ==
                'thumbnail') {
                $attributesToSelect[] = 'thumbnail';
            }


            $sortAttribute = $mainModuleHelper->getConfig('apptrian_subcategories/' . $pageType .
                '/sort_attribute');
            $sortDirection = $mainModuleHelper->getConfig('apptrian_subcategories/' . $pageType .
                '/sort_direction');

            $categoryModel = $this->categoryFactory->create();

            $category = $categoryModel->load($categoryId);
            $childrenIds = $category->getChildren();

            $collection = $categoryModel->getCollection()->addAttributeToSelect($attributesToSelect)->
                addAttributeToFilter('is_active', 1)->addAttributeToSort($sortAttribute, $sortDirection)->
                addIdFilter($childrenIds)->load();


            $mainModuleHelper->options['name'] = $mainModuleHelper->getConfig('apptrian_subcategories/' .
                $pageType . '/name');

            $mainModuleHelper->options['image'] = $mainModuleHelper->getConfig('apptrian_subcategories/' .
                $pageType . '/image');

            $mainModuleHelper->options['description'] = $mainModuleHelper->getConfig('apptrian_subcategories/' .
                $pageType . '/description');


            // Get categories array from collection
            $categories = $mainModuleHelper->getCategoriesFromCollection($collection);
            $_newCategories = array();
            foreach($categories as $cate_id => $cate_data){
                $_tempCategory = $cate_data;
                $_tempCategory["id"] = $cate_id;
                $_newCategories[] = $_tempCategory; 
            }
            
            
            
            if(count($categories)){
               $responseArray["message"] = "Subcategories fetched successfully"; 
            }else{
               $responseArray["message"] = "No Subcategory found for this category"; 
            }
            
            $responseArray["category_id"] = $categoryid;
            $responseArray["results"] = array("subcategories" => $_newCategories,"count"=>count($categories));
            
            $responseArray["returnCode"] = 202;
            echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
            die();
        } else {

            $responseArray["category_id"] = $categoryid;
            $responseArray["results"] = $_resultArray;
            $responseArray["message"] = "Enter Valid Category ID";
            $responseArray["returnCode"] = 200;
            echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
            die();
        }


    }
    
    /**
     * {@inheritdoc}
     */
    public function getReferralData($customerId, $websiteId)
    {
        $storeId = $websiteId;
            $responseArray["total_friends_referred"] = $this->advocateinfo->getInvitedFriendsCount($customerId, $websiteId);
            $responseArray["current_reward_balance"] = $this->advocateinfo->getCumulativeBalanceFormatted($customerId, $storeId);
            $responseArray["expired_balance"] = $this->advocateinfo->getBalanceExpiredFormatted($customerId, $storeId);
            $responseArray["reward_message"] = $this->advocateinfo->getRewardMessage($customerId, $websiteId);
            
            $responseArray["rules_data"]["registration_required"] = $this->ruleViewer->checkIfRegistrationIsRequired($storeId);
            $responseArray["rules_data"]["friends_off"] = $this->ruleViewer->getFriendOffFormatted($storeId);
            $responseArray["rules_data"]["advocate_off"] = $this->ruleViewer->getAdvocateOffFormatted($storeId);
            $responseArray["returnCode"] = 200;
            echo json_encode($responseArray, JSON_UNESCAPED_UNICODE);
            die();
    }
    
            
    
    
}