<?php 
namespace Baytonia\SearchApi\Model;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider; 
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\Xsearch\Controller\RegistryConstants;
use Amasty\Xsearch\Model\Search\SearchAdapterResolver;
use Amasty\Xsearch\Model\Config;
 
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
        \Amasty\Xsearch\Block\Search\Category $catsearchblock
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
        $this->bhsearchblock = $bhsearchblock;
        $this->recentsearchblock = $recentsearchblock;
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
            $productCollection->addAttributeToSelect('*');
            
            
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
            
            $data['image_url'] = "";
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

        return $results;
    }
    
            
    
    
}