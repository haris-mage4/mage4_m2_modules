<?php
namespace Baytonia\CustomApis\Model;
use Magento\Eav\Model\Config as EavConfigAttribute;

class CategoryManagement
{

    protected $_scopeConfig;

    public function __construct(\Amasty\VisualMerch\Model\Product\IndexDataProvider
        $dataProvider, \Magento\Catalog\Model\Category $categoryModel, \Magento\Framework\App\ResourceConnection
        $resourceConnection, \Magento\Catalog\Helper\Image $imageHelper, \Baytonia\CustomApis\Helper\Cache
        $cacheHelper, \Magento\Store\Model\App\Emulation $emulate, \Magento\Store\Model\Store
        $store, \Magento\Catalog\Model\CategoryFactory $category, \Magento\Framework\Registry
        $coreRegistry, \Webkul\MobikulCore\Helper\Catalog $helperCatalog, \Magento\CatalogInventory\Helper\Stock
        $stockFilter, \Webkul\MobikulCore\Helper\Data $helper, \Magento\Customer\Model\Session
        $customerSession,\Magento\Framework\Filesystem\DirectoryList $dir,\Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        EavConfigAttribute  $eavConfig,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,\Magento\Framework\Json\Helper\Data $jsonHelper,\Magento\Catalog\Model\Layer\Filter\Category $categoryLayer,\Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,\Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider)
    {
        $this->resourceConnection = $resourceConnection;
        $this->dataProvider = $dataProvider;
        $this->categoryModel = $categoryModel;
        $this->imageHelper = $imageHelper;
        $this->_cacheHelper = $cacheHelper;
        $this->eventManager = $eventManager;
        $this->emulate = $emulate;
        $this->eavConfig = $eavConfig;
        $this->store = $store;
        $this->category = $category;
        $this->coreRegistry = $coreRegistry;
        $this->helperCatalog = $helperCatalog;
        $this->stockFilter = $stockFilter;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->baseDir = $dir->getPath("media");
        $this->fileDriver = $fileDriver;
        $this->toolbar = $toolbar;
        $this->jsonHelper = $jsonHelper;
        $this->categoryLayer = $categoryLayer;
        $this->layerAttribute = $layerAttribute;
        $this->filterableAttributes = $filterableAttributes;
        $this->catalogLayer = $layerResolver->get();
        $this->filterPriceDataprovider = $filterPriceDataprovider;
    }
    
    protected function getCustomLayeredData($filters, $doPrice, $layeredData,$collection)
    {
        foreach ($filters as $filter) {
            $type = $filter->getFrontendInput();
            if  ($filter->getFrontendInput() == "price") {
                if ($doPrice) {
                    $priceFilterModel = $this->filterPriceDataprovider->create();
                    if ($priceFilterModel) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["type"] = $type;
                        $each["options"] = $this->helperCatalog->getPriceFilterOptions($filter, $collection);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            } else {
                $doAttribute = true;
                if (count($this->filterData) > 0) {
                    if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                        $doAttribute = false;
                    }
                }
                if ($doAttribute) {
                        $options = $this->helperCatalog->getFilterOptions($filter, $collection);
                         if(!empty($options) && isset($each["options"][0]) && isset($each["options"][0]["swatch"])){
                          $type = "swatch";  
                        }
                    $each = [];
                    $each["code"] = $filter->getAttributeCode();
                    $each["label"] = $filter->getStoreLabel();
                    $each["type"] = $type;
                    $each["options"] = $options;
                    if (!empty($each["options"])) {
                        $layeredData[] = $each;
                    }
                }
            }
        }
        return $layeredData;
    }
    
    
    /**
     * Function to get layered data
     *
     * @return object
     */
    protected function getLayeredData($collection,$storeId)
    {
        $layeredData = [];
        
            $categoryFilterModel = $this->categoryLayer;
            if ($categoryFilterModel->getItemsCount()) {
                $each = [];
                $each["code"] = "cat";
                $each["label"] = $categoryFilterModel->getName();
                $each["options"] = $this->addCountToCategories($this->loadedCategory->getChildrenCategories(),$collection,$storeId);
                if (!empty($each["options"])) {
                    $layeredData[] = $each;
                }
            }
        
        $doPrice = true;
        $filters = $this->filterableAttributes->getList();
        $layeredData = $this->getCustomLayeredData($filters, $doPrice, $layeredData,$collection);
        return $layeredData;
    }
    
    
    /**
     * Fucntion to add Count to categories
     *
     * @param object $categoryCollection categoryCollection
     *
     * @return array
     */
    public function addCountToCategories($categoryCollection,$collection,$storeId)
    {
        $isAnchor = [];
        $isNotAnchor = [];
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[] = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = [];
        if ($isAnchor || $isNotAnchor) {
            $select = $this->getProductCountSelect($collection,$storeId);
            $this->eventManager->dispatch(
                "catalog_product_collection_before_add_count_to_categories",
                ["collection" => $collection]
            );
            if ($isAnchor) {
                $anchorStmt = clone $select;
                $anchorStmt->limit();
                $anchorStmt->where("count_table.category_id IN (?)", $isAnchor);
                $productCounts += $collection->getConnection()->fetchPairs($anchorStmt);
                $anchorStmt = null;
            }
            if ($isNotAnchor) {
                $notAnchorStmt = clone $select;
                $notAnchorStmt->limit();
                $notAnchorStmt->where("count_table.category_id IN (?)", $isNotAnchor);
                $notAnchorStmt->where("count_table.is_parent = 1");
                $productCounts += $collection->getConnection()->fetchPairs($notAnchorStmt);
                $notAnchorStmt = null;
            }
            $select = null;
            $this->productCountSelect = null;
        }
        $data = [];
        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            $_count = $category->getProductCollection()->count();
            if ($category->getIsActive() && $_count > 0) {
                $data[] = [
                    "id" => $category->getId(),
                    "label" => htmlspecialchars_decode($this->helperCatalog->stripTags($category->getName())),
                    "count" => $_count
                ];
            }
        }
        return $data;
    }

    /**
     * Function to get selected product Count
     *
     * @return integer
     */
    public function getProductCountSelect($collection,$storeId)
    {
        $this->productCountSelect = clone $collection->getSelect();
        $this->productCountSelect->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->reset(\Magento\Framework\DB\Select::GROUP)
            ->reset(\Magento\Framework\DB\Select::ORDER)
            ->distinct(false)
            ->join(
                [
                    "count_table" => $collection->getTable("catalog_category_product_index")
                ],
                "count_table.product_id = e.entity_id",
                [
                    "count_table.category_id",
                    "product_count" => new \Zend_Db_Expr("COUNT(DISTINCT count_table.product_id)")
                ]
            )
            ->where("count_table.store_id = ?", $storeId)
            ->group("count_table.category_id");
        return $this->productCountSelect;
    }
    
    
    
    
    /**
     * Function to get sorting data
     *
     * @return void
     */
    protected function getSortingData()
    {
        $sortingData = [];
        $toolbar = $this->toolbar;
        foreach ($toolbar->getAvailableOrders() as $key => $order) {
            $each = [];
            $each["code"] = $key;
            $each["label"] = __($order);
            $sortingData[] = $each;
        }
        return $sortingData;
    }
    
    
    
    
    /**
     * {@inheritdoc}
     */
    public function getCategoryLayerData($categoryid,$storeId = 1)
    {
        
        
        // cache pull
        $cacheId = $this->_cacheHelper->getId("category_data", $categoryid, array(
            $storeId));
        //if ($cache = $this->_cacheHelper->load($cacheId)) {
          //  echo $cache;
            //die();
        //}
        // cache pull
        $_returnArray = array();
        
        
        $_returnArray["success"] = true;
        $environment = $this->emulate->startEnvironmentEmulation($storeId);
        $this->loadedCategory = $this->category->create()->setStoreId($storeId)->load($categoryid);
        $this->coreRegistry->register("current_category", $this->loadedCategory);
        
        
        $sortingData = $this->getSortingData();
        if(count($sortingData)){
            $_returnArray["sortingData"] = $sortingData;
        }
        
        $_returnArray["categoryid"] = $categoryid;
        $_returnArray["storeId"] = $storeId;


        $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
        $this->_cacheHelper->save($dataTosend, $cacheId);
        echo $dataTosend;
        exit;
    }
    
    /**
     * Function to filter Product Collection
     *
     * @return void
     */
    protected function filterProductCollection($collection, $storeId = 1)
    {
        if (count($this->filterData) > 0) {
            $filterCount = count($this->filterData[0]);
            for ($i=0; $i<$filterCount; ++$i) {
                if ($this->filterData[0][$i] != "" && $this->filterData[1][$i] == "price") {
                    $priceRange = explode("-", $this->filterData[0][$i]);
                    $currencyRate = $collection->getCurrencyRate();
                    list($from, $to) = $priceRange;
                    $collection->addFieldToFilter(
                        "price",
                        ["from"=>$from, "to"=>empty($to) || $from == $to ? $to : $to - 0.001]
                    );
                    $this->catalogLayer->getState()->addFilter(
                        $this->helperCatalog->_createItem(empty($from) ? 0 : $from, $to, $priceRange)
                    );
                } elseif ($this->filterData[0][$i] != "" && $this->filterData[1][$i] == "cat") {
                    $categoryToFilter = $this->category->create()->load($this->filterData[0][$i]);
                    //$collection->setStoreId($this->storeId)->addCategoryFilter($categoryToFilter);
                      $collection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);

                } else {
                    $attribute = $this->eavConfig->getAttribute("catalog_product", $this->filterData[1][$i]);
                    $attributeModel = $this->layerAttribute->create()->setAttributeModel($attribute);
                    $collection->addFieldToFilter($attribute->getAttributeCode(), $this->filterData[0][$i]);
                    $this->catalogLayer
                        ->getState()
                        ->addFilter($this->helperCatalog->_createItem(
                            $this->filterData[0][$i],
                            $this->filterData[0][$i]
                        ));
                }
            }
        }
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryPageDetails($id, $currency, $customerToken, $width,$sortData = "" ,$pageNumber =
        1, $websiteId = 1, $quoteId = 0, $storeId = 1,$filterData = "")
    {


        // cache pull
        $cacheId = $this->_cacheHelper->getId("category_page", $id, array(
            $storeId,
            $width,
            $websiteId,$sortData,$filterData));
        //if ($cache = $this->_cacheHelper->load($cacheId)) {
          //  echo $cache;
            //die();
        //}
        
        
        // cache pull
        $_returnArray = array();
        $customerId = $this->helper->getCustomerByToken($customerToken);
        $customerId = $customerId ? $customerId : 0;
        if (!$customerId && $customerToken != "") {
            $_returnArray["message"] = __("Customer you are requesting does not exist, so you need to logout.");
            $_returnArray["otherError"] = "customerNotExist";
            $customerId = 0;
        } elseif ($customerId != 0) {
            $this->customerSession->setCustomerId($customerId);
        }

        $environment = $this->emulate->startEnvironmentEmulation($storeId,\Magento\Framework\App\Area::AREA_FRONTEND, true);

        // missing Data
        
        if($sortData){
            $sortData = $this->jsonHelper->jsonDecode($sortData);
        }
        
        if($filterData){
            $this->filterData = $this->jsonHelper->jsonDecode($filterData);
        }else{
            $this->filterData = array();
        }
        
        
        // missing Data

        $this->store->setCurrentCurrencyCode($currency);
        $this->loadedCategory = $this->category->create()->setStoreId($storeId)->load($id);
        $this->coreRegistry->register("current_category", $this->loadedCategory);
        $collection = $this->helperCatalog->getProductListColl($id, "", $pageNumber);
        $collection->addAttributeToSelect("*");
        $categoryToFilter = $this->loadedCategory;
        $collection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
        if ($collection && $this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($collection);
        }

         // missing Data
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->filterProductCollection($collection, $storeId);



        if (is_array($sortData) && isset($sortData[0])) {

            $sortBy['code'] = $sortData[0];
            $sorting = 'ASC';

            /**
             * Changes Start
             * Sorting Sorting by attributes
             */

            if ($sortBy['code'] === 'price_asc') {
                $sortBy['code'] = 'price';
                $sorting = "ASC";
            } elseif ($sortBy['code'] === 'price_desc') {
                $sortBy['code'] = 'price';
                $sorting = "DESC";
            } elseif ($sortBy['code'] === 'new') {
                 $sortBy['code'] = 'news_from_date';
                $sorting = "DESC";
            } elseif ($sortBy['code'] === 'raw_materials_n') {
                $sorting = "ASC";
            } elseif ($sortBy['code'] === 'capacity_kilo') {
                     $sorting = 'ASC';
            } elseif ($sortBy['code'] === 'mbeds_sizes') {
                     $sorting = 'ASC';
            } elseif ($sortBy['code'] === 'color') {
                $sorting = 'ASC';
            } elseif ($sortBy['code'] === 'rating_summary') {
                $sortBy['code'] = 'rating_summary_field';
                $sorting = 'DESC';
                $collection->joinField(
                    $this->getSortingColumnName(),          // alias
                    $this->getIndexTableName(),         // table
                    $this->getSortingFieldName(),   // field
                    $this->getProductColumn() . '=entity_id',     // bind
                    $this->getConditions(),          // conditions
                    'left'                          // join type
                );
            }elseif ($sortBy['code'] === 'bestsellers'){
                $sorting = 'DESC';

                $collection->getSelect()->joinLeft(
                    'sales_order_item',
                    'e.entity_id = sales_order_item.product_id',
                    array( $sortBy['code']=>'SUM(sales_order_item.qty_ordered)'))
                    ->group('e.entity_id')->order($sortBy['code'],'ASC');
            }

            if (isset($sortData["sort"])) {
                $sorting = strtoupper($sortData["sort"]);
            }

            if ($collection->getFlatState()->isFlatEnabled()) {
                $collection->setOrder( $sortBy['code'], $sorting);
            } else {
                $collection->addAttributeToSort($sortBy["code"], $sorting);
            }
        }else {
            /** Pinned products sorting */
            $sorting = "ASC";
            $collection->setOrder('position', $sorting);
        }

        /**
         * Changes End
         * Sorting by attributes
         */


        if ($pageNumber >= 1) {
            if ($collection) {
                $_returnArray["totalCount"] = $collection->getSize();
            } else {
                $_returnArray["totalCount"] = 0;
            }
        }


        // Creating product collection //////////////////////////////////////////
        $productList = [];
        if ($collection) {
            $collection->addMinimalPrice();
            foreach ($collection as $eachProduct) {

                $productList[] = $this->helperCatalog->getOneProductRelevantData($eachProduct, $storeId,
                    $width, $customerId);
            }
        }
        $_returnArray["productList"] = $productList;
        
        $_returnArray["layeredData"] = $this->getLayeredData($collection,$storeId);
        
        
        $banners = $this->getCategoryImages($this->loadedCategory,$width);
        if(count($banners)){
          $_returnArray["banners"] = $banners;  
        }
        
        
        // missing Data

        $_returnArray["success"] = true;
        $this->emulate->stopEnvironmentEmulation($environment);


        $_returnArray["id"] = $id;
        $_returnArray["currency"] = $currency;
        $_returnArray["customerToken"] = $customerToken;
        $_returnArray["width"] = $width;
        $_returnArray["pageNumber"] = $pageNumber;
        $_returnArray["websiteId"] = $websiteId;
        $_returnArray["quoteId"] = $quoteId;
        $_returnArray["storeId"] = $storeId;


        $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
        $this->_cacheHelper->save($dataTosend, $cacheId);
        echo $dataTosend;
        die();
    }
    
    protected function getCategoryImages($categoryImage,$width)
    {
        $banners = array();
        $bannerWidth = $this->helper->getValidDimensions(1, $width);
        $bannerHeight = $this->helper->getValidDimensions(1, 2*($width/3));
        
        if($categoryImage->getBanner()){
            $bannerArray = explode(",", $categoryImage->getBanner());
            if (!empty($bannerArray)) {
                foreach ($bannerArray as $banner) {
                    $basePath = $this->baseDir.DS."mobikul".DS."categoryimages".DS."banner".DS.$banner;
                    $newUrl = "";
                    $dominantColorPath = "";
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$bannerWidth."x".
                            $bannerHeight.DS."categoryimages".DS."banner".DS.$banner;
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                            $bannerHeight.DS."categoryimages".DS."banner".DS.$banner;
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$bannerWidth."x".
                            $bannerHeight.DS."categoryimages".DS."banner".DS.$banner;
                    }
                    $bannerData = [];
                    $bannerData["bannerImage"] = $newUrl;
                    $bannerData["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                    $banners[] = $bannerData;
                }
            }
        }
            
            return $banners;
    }


    /**
     * {@inheritdoc}
     */
    public function getProductSorting($storeId, $categoryid)
    {
        $_category = $this->categoryModel->load($categoryid);
        $currentProductPositions = $_category->getProductsPosition();
        asort($currentProductPositions);

        $_returnArray = array();
        $_productArray = array();
        $_pinnedPostion = array();
        $allProductsinCategory = array();

        //$_productCollection = $_category->getProductCollection()->addAttributeToSelect('*');

        //foreach($_productCollection as $product){
        //
        //            $imageUrl = $this->imageHelper->init($product, 'product_page_image_small')
        //                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
        //                ->resize(380)
        //                ->getUrl();
        //
        //            $_productData = array("name" => $product->getName() , "sku" => $product->getSku(),"image"=>$imageUrl);
        //            $allProductsinCategory[$product->getId()] = $_productData;
        //        }

        //ksort($_productArray);
        $_returnArray["product_data"] = $allProductsinCategory;
        $_returnArray["positions"] = $currentProductPositions;


        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('catalog_category_product_static');

        $query = "SELECT * FROM `$table` WHERE `category_id` = '$categoryid'";
        $staticPositions = $connection->fetchAll($query);

        foreach ($staticPositions as $data) {
            $_pinnedPostion[$data["product_id"]] = $data["position"];
        }

        asort($_pinnedPostion);

        $_returnArray["pinned_products"] = $_pinnedPostion;

        $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();


        //$productIds = $this->dataProvider->getProductPositionData($_category, $storeId);
    }
    
      /**
     * Returns Sorting method Table Column name
     * which is using for order collection rating_summmary
     *
     * @return string
     */
    private function getSortingColumnName()
    {
        return 'rating_summary_field';
    }

    private function getIndexTableName()
    {
        return 'review_entity_summary';
    }

    /**
     * @return string
     */
    private function getSortingFieldName()
    {
        return 'rating_summary';
    }

    private function getProductColumn()
    {
        return 'entity_pk_value';
    }

    private function getConditions()
    {
        return [
            'store_id' => 1,
            'entity_type' => 1
        ];
    }


}
