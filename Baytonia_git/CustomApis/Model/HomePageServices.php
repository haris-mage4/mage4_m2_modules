<?php
namespace Baytonia\CustomApis\Model;

class HomePageServices
{


    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,\Magento\Framework\Locale\Format $localeFormat,
    \Magento\Store\Model\StoreManagerInterface $storeManager,\Magento\Store\Model\StoreRepository $storeRepository,\Baytonia\CustomApis\Helper\Cache $cacheHelper, \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory, \Webkul\MobikulCore\Model\CategoryimagesFactory $categoryImageFactory,\Magento\Framework\Filesystem\DirectoryList $dir,\Magento\Framework\Filesystem\Driver\File $fileDriver,\Magento\Framework\Image\Factory $imageFactory,\Webkul\MobikulCore\Helper\Data $helper,\Webkul\MobikulCore\Model\Featuredcategories $featuredCategories,\Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,\Magento\Catalog\Model\ResourceModel\Product $productResourceModel,\Webkul\MobikulCore\Model\Bannerimage $bannerImage,\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,\Magento\Catalog\Model\Config $catalogConfig,\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,\Magento\Catalog\Model\Product\Visibility $productVisibility,\Webkul\MobikulCore\Helper\Catalog $helperCatalog, \Magento\CatalogInventory\Helper\Stock $stockFilter,\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,\Webkul\MobikulCore\Model\CarouselFactory $carouselFactory,\Webkul\MobikulCore\Model\CarouselimageFactory $carouselImageFactory,\Magento\Cms\Model\ResourceModel\Page\Collection $cmsCollection,\Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory)
    {
        $this->_scopeConfig = $scopeInterface;
        $this->localeFormat = $localeFormat;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryImageFactory = $categoryImageFactory;
        $this->_cacheHelper = $cacheHelper;
        $this->baseDir = $dir->getPath("media");
        $this->fileDriver = $fileDriver;
        $this->imageFactory = $imageFactory;
        $this->helper = $helper;
        $this->featuredCategories = $featuredCategories;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->productResourceModel = $productResourceModel;
        $this->bannerImage = $bannerImage;
        $this->productCollection = $productCollection;
        $this->catalogConfig = $catalogConfig;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->helperCatalog = $helperCatalog;
        $this->stockFilter = $stockFilter;
        $this->localeDate = $localeDate;
        $this->carouselFactory = $carouselFactory;
        $this->carouselImageFactory = $carouselImageFactory;
        $this->cmsCollection = $cmsCollection;
        $this->appcreatorFactory = $appcreatorFactory;
    }
    
    public function getWebsiteData()
    {
        $websiteData = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteData[] = [
                "id" => $website->getWebsiteId(),
                "name" => $website->getName()
            ];
        }
        return $websiteData;
    }
    
    
    public function getStoreData()
    {
        $storeList = $this->storeRepository->getList();
        $_allStores = array();
        foreach ($storeList as $store) {
            if($store->getStoreId() > 0){
                $storeArr = [];
                    $storeArr["id"] =  $store->getStoreId();
                    $storeArr["code"] = $store->getCode();
                    $storeArr["name"] = $store->getName();
                    $storeArr["sort_order"] = $store->getSortOrder();
                    $storeArr["is_active"] = $store->getIsActive();
                    $_allStores[] = $storeArr;
            }
                    
        }
        return $_allStores;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsData($storeId, $websiteId)
    {
        $responseArray = $this->getSettings($storeId, $websiteId);
        $dataTosend = json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();
    }
    
     /**
     * {@inheritdoc}
     */
    public function getCategoriesData($quoteId = 0,$storeId = 1,$mFactor = 1,$width=1125.000000)
    {
        $responseArray["categories"] = $this->getCategories($storeId,$mFactor,$width);
        
        if ($quoteId != 0) {
                $responseArray["cartCount"] = $this->helper->getCartCount($quoteId);
            }
        
        
        $dataTosend = json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();
    }
    
    public function getSettings($storeId, $websiteId){
        
        $cacheId = $this->_cacheHelper->getId("homepage_setting",$storeId);
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        
        $currencies[] =[
                "label" => 'Saudi Riyals',
                "code" => 'SAR'
            ];
        $responseArray["success"] = true;
        $responseArray["message"] = "";
        $responseArray["storeId"] = $storeId;
        $responseArray["allowedCurrencies"] = $currencies;
        $responseArray["defaultCurrency"] = $this->_scopeConfig->getValue("currency/options/default", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["allowIosDownload"] = (bool)$this->_scopeConfig->getValue("mobikul/appdownload/allowiOS", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["iosDownloadLink"] = $this->_scopeConfig->getValue("mobikul/appdownload/ioslink", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["allowAndroidDownload"] = (bool)$this->_scopeConfig->getValue("mobikul/appdownload/allowAndroid", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["androidDownloadLink"] = $this->_scopeConfig->getValue("mobikul/appdownload/androidlink", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["showSwatchOnCollection"] = (bool)$this->_scopeConfig->getValue("catalog/frontend/show_swatches_in_product_list", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["priceFormat"] = $this->localeFormat->getPriceFormat();
        $responseArray["themeCode"] = $this->_scopeConfig->getValue("mobikul/theme/code", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["wishlistEnable"] = (bool)$this->_scopeConfig->getValue("wishlist/general/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        $responseArray["websiteData"] = $this->getWebsiteData();
        $responseArray["storeData"] = $this->getStoreData();
        $responseArray["themeType"] = (int)$this->_scopeConfig->getValue("mobikul/themeConfig/themeType", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        
        
        $this->_cacheHelper->save(json_encode($responseArray), $cacheId);
        
        return $responseArray;
    }
    
    public function getCategories($storeId,$mFactor,$width)
    {
        
        $cacheId = $this->_cacheHelper->getId("homepage_categories",$storeId);
        if($cache = $this->_cacheHelper->load($cacheId)){
           return json_decode($cache,true);
        }
        
        $categoryImages = $this->getCategoryImages($storeId,$mFactor,$width);
        $catCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect("*")
            ->addFieldToFilter("is_active", ["eq" => 1])
            ->addFieldToFilter("include_in_menu", ["eq" => 1])
            ->addFieldToFilter("level", "2")
            ->addAttributeToSort('position', 'ASC');
        $categories = [];
        foreach ($catCollection as $cc) {
            if (array_key_exists($cc->getEntityId(), $categoryImages)) {
                $categories[] = [
                    "id" => $cc->getEntityId(),
                    "name" => $cc->getName(),
                    "banner" => $categoryImages[$cc->getEntityId()]["banner"],
                    "thumbnail" => $categoryImages[$cc->getEntityId()]["thumbnail"],
                    "hasChildren" => $cc->getChildrenCount() > 0 ? true:false,
                    "bannerDominantColor" => $categoryImages[$cc->getEntityId()]["bannerDominantColor"],
                    "thumbnailDominantColor" => $categoryImages[$cc->getEntityId()]["thumbnailDominantColor"]
                ];
            } else {
                $categories[] = [
                    "id" => $cc->getEntityId(),
                    "name" => $cc->getName(),
                    "hasChildren" => $cc->getChildrenCount() > 0 ? true:false
                ];
            }
        }
        
        $this->_cacheHelper->save(json_encode($categories), $cacheId);
        
        
        return $categories;
    }
    
    protected function getCategoryImages($storeId,$mFactor,$width)
    {
        $bannerWidth = $this->helper->getValidDimensions($mFactor, $width);
        $height = $this->helper->getValidDimensions($mFactor, 2*($width/3));
        
        $cacheId = $this->_cacheHelper->getId("homepage_categories_images",$storeId,array($bannerWidth,$height));
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
       
        
        
        
        $categoryImages = [];
        $categoryImgCollection = $this->categoryImageFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $storeId]
            ]);
        foreach ($categoryImgCollection as $categoryImage) {
            if ($categoryImage->getBanner() != "" && $categoryImage->getIcon() != "") {
                $eachCategoryImage["id"] = $categoryImage->getCategoryId();
                if ($categoryImage->getIcon() != "") {
                    $basePath = $this->baseDir.DS."mobikul".DS."categoryimages".DS."icon".DS.$categoryImage->getIcon();
                    $newUrl = "";
                    $dominantColorPath = "";
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS."144x144".DS.
                            "categoryimages".DS."icon".DS.$categoryImage->getIcon();
                        $this->resizeNCache($basePath, $newPath, 144, 144);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS."144x144".DS.
                            "categoryimages".DS."icon".DS.$categoryImage->getIcon();
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS."144x144".
                            DS."categoryimages".DS."icon".DS.$categoryImage->getIcon();
                    }
                    $eachCategoryImage["thumbnail"] = $newUrl;
                    $eachCategoryImage["thumbnailDominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                }
                if ($categoryImage->getBanner() != "") {
                    $basePath = $this->baseDir.DS."mobikul".DS."categoryimages".DS."banner".DS.
                        $categoryImage->getBanner();
                    $newUrl = "";
                    $dominantColorPath = "";
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$bannerWidth."x".
                            $height.DS."categoryimages".DS."banner".DS.$categoryImage->getBanner();
                        $this->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                            $height.DS."categoryimages".DS."banner".DS.$categoryImage->getBanner();
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.
                            $bannerWidth."x".$height.DS."categoryimages".DS."banner".DS.
                            $categoryImage->getBanner();
                    }
                    $eachCategoryImage["banner"] = $newUrl;
                    $eachCategoryImage["bannerDominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                }

                $categoryImages[$eachCategoryImage["id"]] = $eachCategoryImage;
            }
        }
        
        $dataTosend = json_encode($categoryImages);
        $this->_cacheHelper->save($dataTosend, $cacheId);
        return $categoryImages;
    }
    
    
    public function resizeNCache($basePath, $newPath, $width, $height, $forCustomer = false)
    {
        if (!$this->fileDriver->isFile($newPath) || $forCustomer) {
            $imageObj = $this->imageFactory->create($basePath);
            $imageObj->keepAspectRatio(false);
            $imageObj->backgroundColor([255, 255, 255]);
            $imageObj->keepFrame(false);
            $imageObj->resize($width, $height);
            $imageObj->save($newPath);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBlocksData($storeId = 1,$mFactor = 1,$width=1125.000000,$customerToken = "")
    {  
        $customerId = $this->helper->getCustomerByToken($customerToken);
        $customerId = ($customerId)?$customerId:0;
            
            if (!$customerId && $customerToken != "") {
                $responseArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $responseArray["success"] = false;
                $responseArray["otherError"] = "customerNotExist";
                $customerId = 0;
                $dataTosend = json_encode($responseArray, JSON_UNESCAPED_UNICODE);
                echo $dataTosend;
                die();
            }
        
        
        $responseArray["featuredCategories"] = $this->getFeaturedCategories($storeId,$mFactor,$width);
        $responseArray["bannerImages"] = $this->getBannerImages($storeId,$mFactor,$width);
        $responseArray["carousel"][] = $this->getFeaturedDeals($storeId,$width,$customerId);
        $responseArray["carousel"][] = $this->getNewDeals($storeId,$width,$customerId);
        $responseArray["carousel"][] = $this->getHotDeals($storeId,$width,$customerId);
        $cccarousal = $responseArray["carousel"];
        $responseArray["carousel"] = $this->getImageNProductCarousel($storeId,$width,$customerId,$mFactor,$cccarousal);
        $responseArray["cmsData"] = $this->getCmsData($storeId);
        $responseArray["sort_order"] = $this->getSortingOrder();
        

        $dataTosend = json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();
    }
    
    private function getSortingOrder()
    {
        $appCreatorModal = $this->appcreatorFactory->create();
        
        return $appCreatorModal->getCollection()->getData();
    }
    
    protected function getCmsData($storeId)
    {
        $cacheId = $this->_cacheHelper->getId("homepage_cms_pages",$storeId);
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        $cmsData = [];
        $allowedCmsPages = $this->_scopeConfig->getValue("mobikul/configuration/cms", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        if ($allowedCmsPages != "") {
            $allowedIds = explode(",", $allowedCmsPages);
            $collection = $this->cmsCollection
                ->addFieldToFilter("is_active", \Magento\Cms\Model\Page::STATUS_ENABLED)
                ->addFieldToFilter("store_id", $storeId)
                ->addFieldToFilter("page_id", ["in"=>$allowedIds]);
            foreach ($collection as $cms) {
                $cmsData[] = ["id"=>$cms->getId(), "title"=>$cms->getTitle()];
            }
        }
        $this->_cacheHelper->save(json_encode($cmsData), $cacheId);
        return $cmsData;
    }
    
    
    protected function getImageNProductCarousel($storeId,$width,$customerId,$mFactor,$carousaldata)
    {
        //$carousaldata = [];
        $bannerWidth = $this->helper->getValidDimensions($mFactor, $width);
        $height = $this->helper->getValidDimensions($mFactor, 2*($width/3));
        
        $cacheId = $this->_cacheHelper->getId("homepage_image_prod_carosal",$storeId,array($width,$customerId));
        
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        $collection = $this->carouselFactory->create()->getCollection()
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $storeId]
            ])
            ->setOrder("sort_order", "ASC");
        foreach ($collection as $eachCarousel) {
            //echo $eachCarousel->getTitle() . "<br>";
            if ($eachCarousel->getType() == 2) {
                $oneCarousel = [];
                $productList = [];
                $oneCarousel["id"] = $eachCarousel->getId();
                $oneCarousel["type"] = "product";
                $oneCarousel["label"] = $eachCarousel->getTitle();
                $oneCarousel["show_title"] = $eachCarousel->getShowTitle();
                $oneCarousel["total_rows"] = $eachCarousel->getTotalRows();
                $oneCarousel["items_per_rows"] = $eachCarousel->getItemsPerRows();
                if ($eachCarousel->getColorCode()) {
                    $oneCarousel["color"] = $eachCarousel->getColorCode();
                }
                if ($eachCarousel->getFilename()) {
                    $filePath = $this->helper->getUrl("media")."mobikul/carousel/".$eachCarousel->getFilename();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikul/carousel/".$eachCarousel
                        ->getFilename();
                    $oneCarousel["image"] = $filePath;
                    $oneCarousel["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                }
                // $oneCarousel["order"] = $eachCarousel->getSortOrder();
                $selectedProdctIds = explode(",", $eachCarousel->getProductIds());
                $productCollection = $this->productCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                    ->addAttributeToSelect("image")
                    ->addAttributeToSelect("thumbnail")
                    ->addAttributeToSelect("small_image")
                    ->addAttributeToFilter("entity_id", ["in"=>$selectedProdctIds])
                    ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                    ->addStoreFilter();
                if ($this->helperCatalog->showOutOfStock() == 0) {
                    $this->stockFilter->addInStockFilterToCollection($productCollection);
                }
                $productCollection->setPageSize(5)->setCurPage(1);
                foreach ($productCollection as $eachProduct) {
                    $productList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $storeId,
                        $width,
                        $customerId
                    );
                }
                $oneCarousel["productList"] = $productList;
                $carousaldata[] = $oneCarousel;
                
            } else {
                $banners = [];
                $oneCarousel = [];
                $oneCarousel["id"] = $eachCarousel->getId();
                $oneCarousel["type"] = "image";
                $oneCarousel["label"] = $eachCarousel->getTitle();
                $oneCarousel["show_title"] = $eachCarousel->getShowTitle();
                $oneCarousel["total_rows"] = $eachCarousel->getTotalRows();
                $oneCarousel["items_per_rows"] = $eachCarousel->getItemsPerRows();
                if ($eachCarousel->getColorCode()) {
                    $oneCarousel["color"] = $eachCarousel->getColorCode();
                }
                if ($eachCarousel->getFilename()) {
                    $filePath = $this->helper->getUrl("media")."mobikul/carousel/".$eachCarousel->getFilename();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikul/carousel/".$eachCarousel
                        ->getFilename();
                    $oneCarousel["image"] = $filePath;
                    $oneCarousel["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                }
                // $oneCarousel["order"] = $eachCarousel->getSortOrder();
                $sellectedBanners = explode(",", $eachCarousel->getImageIds());
                $carouselImageColelction = $this->carouselImageFactory->create()->getCollection()
                    ->addFieldToFilter("id", ["in"=>$sellectedBanners]);
                foreach ($carouselImageColelction as $each) {
                    $oneBanner = [];
                    $newUrl = "";
                    $dominantColorPath = "";
                    $basePath = $this->baseDir.DS.$each->getFilename();
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$bannerWidth."x".
                            $height.DS.$each->getFilename();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                            $height.DS.$each->getFilename();
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.
                            $bannerWidth."x".$height.DS.$each->getFilename();
                    }
                    $oneBanner["url"] = $newUrl;
                    $oneBanner["title"] = $each->getTitle();
                    $oneBanner["bannerType"] = $each->getType();
                    $oneBanner["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                    if ($each->getType() == "category") {
                        $categoryName = $this->categoryResourceModel->getAttributeRawValue(
                            $each->getProCatId(),
                            "name",
                            $storeId
                        );
                        if (is_array($categoryName)) {
                            continue;
                        }
                        $oneBanner["id"] = $each->getProCatId();
                        $oneBanner["name"] = $categoryName;
                    } elseif ($each->getType() == "product") {
                        $productName = $this->productResourceModel->getAttributeRawValue(
                            $each->getProCatId(),
                            "name",
                            $storeId
                        );
                        if (is_array($productName)) {
                            continue;
                        }
                        $oneBanner["id"] = $each->getProCatId();
                        $oneBanner["name"] = $productName;
                    } elseif ($each->getType() == "customlink") {
                        $oneBanner["custom_link"] = $each->getCustomLink();
                    }
                    $banners[] = $oneBanner;
                }
                $oneCarousel["banners"] = $banners;
                $carousaldata[] = $oneCarousel;
            }
        }
        
        $this->_cacheHelper->save(json_encode($carousaldata), $cacheId);
        return $carousaldata;
    }
    
    
    
    protected function getHotDeals($storeId,$width,$customerId)
    {
        $cacheId = $this->_cacheHelper->getId("homepage_hot_deals",$storeId,array($width,$customerId));
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        $productList = [];
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $hotDealCollection = $this->productCollection->create()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect("image")
            ->addAttributeToSelect("thumbnail")
            ->addAttributeToSelect("small_image")
            ->addAttributeToSelect("special_from_date")
            ->addAttributeToSelect("special_to_date")
            ->addAttributeToFilter('type_id', 'simple')
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes());
        $hotDealCollection->addStoreFilter()
            ->addAttributeToFilter(
                "special_from_date",
                ["or"=>[
                    0=>["date"=>true, "to"=>$todayEndOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "special_to_date",
                ["or"=>[
                    0=>["date"=>true, "from"=>$todayStartOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [["attribute"=>"special_from_date", "is"=>new \Zend_Db_Expr("not null")],
                ["attribute"=>"special_to_date", "is"=>new \Zend_Db_Expr("not null")]]
            );
        if ($this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($hotDealCollection);
        }
        $hotDealCollection->setPageSize(5)->setCurPage(1);
        foreach ($hotDealCollection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $storeId,
                $width,
                $customerId
            );
        }
        $carousel = [];
        $carousel["id"] = "hotDeals";
        $carousel["type"] = "product";
        $carousel["label"] = __("Hot Deals");
        $carousel["productList"] = $productList;
        
        $this->_cacheHelper->save(json_encode($carousel), $cacheId);
        return $carousel;
    }
    
    protected function getNewDeals($storeId,$width,$customerId)
    {
        $cacheId = $this->_cacheHelper->getId("homepage_new_deals",$storeId,array($width,$customerId));
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        $productList = [];
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $newProductCollection = $this->productCollection->create()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addAttributeToFilter(
                "news_from_date",
                ["or"=>[
                    0=>["date"=>true, "to"=>$todayEndOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "news_to_date",
                ["or"=>[
                    0=>["date"=>true, "from"=>$todayStartOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [["attribute"=>"news_from_date", "is"=>new \Zend_Db_Expr("not null")],
                ["attribute"=>"news_to_date", "is"=>new \Zend_Db_Expr("not null")]]
            )
            ->addAttributeToSelect("image")
            ->addAttributeToSelect("thumbnail")
            ->addAttributeToSelect("small_image")
            ->addAttributeToFilter('type_id', 'simple')
            ->addAttributeToSort("news_from_date", "desc");
        if ($this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($newProductCollection);
        }
        $newProductCollection->setPageSize(5)->setCurPage(1);
        foreach ($newProductCollection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $storeId,
                $width,
                $customerId
            );
        }
        $carousel = [];
        $carousel["id"] = "newProduct";
        $carousel["type"] = "product";
        $carousel["label"] = __("New Products");
        $carousel["productList"] = $productList;
        
         $this->_cacheHelper->save(json_encode($carousel), $cacheId);
        return $carousel;
    }
    
    
    
    /**
     * Function to get Featured deals
     * Set Featured deals to return array
     *
     * @return none
     */
    protected function getFeaturedDeals($storeId,$width,$customerId)
    {
        
        $cacheId = $this->_cacheHelper->getId("homepage_featured_deals",$storeId,array($width,$customerId));
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        $productList = [];
        $collection = new \Magento\Framework\DataObject();
        if ($this->_scopeConfig->getValue("mobikul/configuration/featuredproduct", \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId) == 1) {
            $collection = $this->productCollection->create()->addAttributeToSelect(
                $this->catalogConfig->getProductAttributes()
            );
            $collection->addAttributeToSelect('*');
            $collection->getSelect()->order("rand()");
            $collection->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            if ($this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($collection);
            }
            $collection->addAttributeToFilter('type_id', 'simple');
            $collection->setPage(1, 5)->load();
        } else {
            $collection = $this->productCollection->create()
                ->setStore($storeId)
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addAttributeToSelect("as_featured")
                ->addAttributeToSelect("image")
                ->addAttributeToSelect("thumbnail")
                ->addAttributeToSelect("small_image")
                ->addAttributeToSelect("visibility")
                ->addStoreFilter()
                ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                ->addAttributeToFilter("as_featured", 1);
            if ($this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($collection);
            }
            $collection->setPageSize(5)->setCurPage(1);
        }
        foreach ($collection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $storeId,
                $width,
                $customerId
            );
        }
        $carousel = [];
        $carousel["id"] = "featuredProduct";
        $carousel["type"] = "product";
        $carousel["label"] = __("Featured Products");
        $carousel["productList"] = $productList;
        
        
        
        $this->_cacheHelper->save(json_encode($carousel), $cacheId);
        return $carousel;
    }
    
    protected function getBannerImages($storeId,$mFactor,$width)
    {
        $bannerWidth = $this->helper->getValidDimensions($mFactor, $width);
        $height = $this->helper->getValidDimensions($mFactor, 2*($width/3));
        
        $cacheId = $this->_cacheHelper->getId("homepage_banner_images",$storeId,array($bannerWidth,$height));
        if($cache = $this->_cacheHelper->load($cacheId)){
            return json_decode($cache,true);
        }
        
        
        $collection = $this->bannerImage
            ->getCollection()
            ->addFieldToFilter("status", true)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $storeId]
            ])->setOrder("sort_order", "ASC");
        $bannerImages = [];
        foreach ($collection as $eachBanner) {
            $oneBanner = [];
            $newUrl = "";
            $dominantColorPath = "";
            $basePath = $this->baseDir.DS.$eachBanner->getFilename();
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.DS."mobikulresized".DS.$bannerWidth."x".
                    $height.DS.$eachBanner->getFilename();
                $this->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                    $height.DS.$eachBanner->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$bannerWidth."x".
                    $height.DS.$eachBanner->getFilename();
            }
            $oneBanner["url"] = $newUrl;
            $oneBanner["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
            $oneBanner["bannerType"] = $eachBanner->getType();
            if ($eachBanner->getType() == "category") {
                $categoryName = $this->categoryResourceModel->getAttributeRawValue(
                    $eachBanner->getProCatId(),
                    "name",
                    $storeId
                );
                if (is_array($categoryName)) {
                    continue;
                }
                $oneBanner["id"] = $eachBanner->getProCatId();
                $oneBanner["name"] = $categoryName;
            } elseif ($eachBanner->getType() == "product") {
                $productName = $this->productResourceModel->getAttributeRawValue(
                    $eachBanner->getProCatId(),
                    "name",
                    $storeId
                );
                if (is_array($productName)) {
                    continue;
                }
                $oneBanner["id"] = $eachBanner->getProCatId();
                $oneBanner["name"] = $productName;
            } elseif ($eachBanner->getType() == "customlink") {
                        $oneBanner["custom_link"] = $eachBanner->getCustomLink();
                    }
            $bannerImages[] = $oneBanner;
        }
        $this->_cacheHelper->save(json_encode($bannerImages), $cacheId);
        return $bannerImages;
    }
    
    protected function getFeaturedCategories($storeId,$mFactor,$width)
    {
        
        $iconHeight = $iconWidth = $this->helper->getValidDimensions($mFactor, 288);
        
        $cacheId = $this->_cacheHelper->getId("homepage_featurecategories",$storeId,array($iconHeight,$iconWidth));
        if($cache = $this->_cacheHelper->load($cacheId)){
           return json_decode($cache,true);
        }
        $featuredCategoryCollection = $this->featuredCategories
            ->getCollection()
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $storeId]
            ])
            ->setOrder("sort_order", "ASC");
        $featuredCategories = [];
        foreach ($featuredCategoryCollection as $eachCategory) {
            $newUrl = "";
            $dominantColorPath = "";
            $basePath = $this->baseDir.DS.$eachCategory->getFilename();
            $oneCategory = [];
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.DS."mobikulresized".DS.$iconWidth."x".
                    $iconHeight.DS.$eachCategory->getFilename();
                $this->resizeNCache($basePath, $newPath, $iconWidth, $iconHeight);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$iconWidth."x".
                    $iconHeight.DS.$eachCategory->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$iconWidth."x".
                    $iconHeight.DS.$eachCategory->getFilename();
            }
            $oneCategory["url"] = $newUrl;
            $oneCategory["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
            $oneCategory["categoryId"] = $eachCategory->getCategoryId();
            $oneCategory["categoryName"] = $this->categoryResourceModel->getAttributeRawValue(
                $eachCategory->getCategoryId(),
                "name",
                $storeId
            );
            if (is_array($oneCategory["categoryName"])) {
                continue;
            }
            if ($eachCategory->getCategoryId()) {
                $featuredCategories[] = $oneCategory;
            }
        }
        
        $this->_cacheHelper->save(json_encode($featuredCategories), $cacheId);
        
        
        return $featuredCategories;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFullData($quoteId = 0,$storeId = 1,$websiteId = 1,$mFactor = 1,$width=1125.000000,$customerToken = "")
    {   
        
        $customerId = $this->helper->getCustomerByToken($customerToken);
        $customerId = ($customerId)?$customerId:0;
            
            if (!$customerId && $customerToken != "") {
                $responseArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $responseArray["success"] = false;
                $responseArray["otherError"] = "customerNotExist";
                $customerId = 0;
                $dataTosend = json_encode($responseArray, JSON_UNESCAPED_UNICODE);
                echo $dataTosend;
                die();
            }
        
        $responseArray = $this->getSettings($storeId, $websiteId);
        $responseArray["featuredCategories"] = $this->getFeaturedCategories($storeId,$mFactor,$width);
        $responseArray["categories"] = $this->getCategories($storeId,$mFactor,$width);
        $responseArray["bannerImages"] = $this->getBannerImages($storeId,$mFactor,$width);
        $responseArray["carousel"][] = $this->getFeaturedDeals($storeId,$width,$customerId);
        $responseArray["carousel"][] = $this->getNewDeals($storeId,$width,$customerId);
        $responseArray["carousel"][] = $this->getHotDeals($storeId,$width,$customerId);
        $cccarousal = $responseArray["carousel"];
        $responseArray["carousel"] = $this->getImageNProductCarousel($storeId,$width,$customerId,$mFactor,$cccarousal);
        $responseArray["cmsData"] = $this->getCmsData($storeId);
        $responseArray["sort_order"] = $this->getSortingOrder();
        
        if ($quoteId != 0) {
        $responseArray["cartCount"] = $this->helper->getCartCount($quoteId);
            }
        
        $dataTosend = json_encode($responseArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();
    }


}
