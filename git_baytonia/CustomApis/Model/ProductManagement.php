<?php
namespace Baytonia\CustomApis\Model;

class ProductManagement
{

    protected $_scopeConfig;

    public function __construct(\Magento\Catalog\Model\ProductFactory $_productloader, \Magento\ConfigurableProduct\Model\Product\Type\Configurable
        $customOptions, \Magento\Catalog\Helper\Image $imageHelper, \Magento\Quote\Model\QuoteFactory
        $quoteFactory, \Baytonia\CustomApis\Helper\Cache $cacheHelper, \Webkul\MobikulCore\Helper\Data
        $helper, \Magento\Customer\Model\Session $customerSession, \Magento\Store\Model\Store
        $store, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Catalog\Api\ProductRepositoryInterface
        $productRepository, \Magento\Framework\Registry $coreRegistry, \Magento\Review\Model\Review
        $review, \Magento\Framework\Locale\Format $localeFormat, \Magento\Review\Model\Rating
        $rating, \Webkul\MobikulCore\Helper\Catalog $helperCatalog,\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Bundle\Model\Product\Price $bundlePriceModel,\Magento\Framework\Pricing\Helper\Data $pricingHelper,\Magento\Catalog\Helper\Data $taxHelper,\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,\Magento\Cms\Model\Template\FilterProvider $filterProvider,\Magento\Store\Model\App\Emulation $emulate,\Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Catalog\Block\Product\View\Options $productOptionBlock,\Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,\Magento\Framework\App\ResourceConnection $resource,
         \Webkul\MobikulApi\Block\Configurable $configurableBlock,\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->_productloader = $_productloader;
        $this->_customOptions = $customOptions;
        $this->imageHelper = $imageHelper;
        $this->quoteFactory = $quoteFactory;
        $this->_cacheHelper = $cacheHelper;
        $this->helper = $helper;
        $this->store = $store;
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->coreRegistry = $coreRegistry;
        $this->review = $review;
        $this->localeFormat = $localeFormat;
        $this->rating = $rating;
        $this->helperCatalog = $helperCatalog;
        $this->stockRegistry = $stockRegistry;
        $this->bundlePriceModel = $bundlePriceModel;
        $this->pricingHelper = $pricingHelper;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
        $this->filterProvider = $filterProvider;
        $this->emulate = $emulate;
        $this->date = $date;
        $this->productOptionBlock = $productOptionBlock;
        $this->groupedProduct = $groupedProduct;
        $this->resourceConnection = $resource->getConnection();
        $this->configurableBlock = $configurableBlock;
        $this->localeDate = $localeDate;
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    public function getCustomOptions($data)
    {
        return $this->_customOptions->getConfigurableAttributesAsArray($data);
    }

    public function getOptionHtml($prd_data, \Magento\Catalog\Model\Product\Option $option)
    {
        $type = $this->getGroupOfOption($option->getType());
        $renderer = $this->getChildBlock($type);
        $renderer->setProduct($prd_data)->setOption($option);
        return $this->getChildHtml($type, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableProductOptions($productid)
    {
        $_returnArray = [];
        $prd_data = $this->getLoadProduct($productid);

        if ($prd_data->getTypeId() == "configurable") {
            $prd_custom_op = $this->getCustomOptions($prd_data);
            $option = false;
            foreach ($prd_custom_op as $attrid => $optionData) {
                $option = true;
                $tempArray = [];
                $tempArray["attribute_id"] = $attrid;
                $tempArray["label"] = $optionData["label"];
                $tempArray["use_default"] = $optionData["use_default"];
                $tempArray["position"] = $optionData["position"];
                $tempArray["attribute_code"] = $optionData["attribute_code"];


                if (isset($optionData["values"])) {
                    foreach ($optionData["values"] as $value) {
                        $tempArray["options"][] = array("id" => $value["value_index"], "label" => $value["label"]);
                    }
                }
                $_returnArray["configurable_options"][] = $tempArray;
            }

            $_returnArray["product_id"] = $productid;
            if ($option) {
                $_returnArray["success"] = true;

            } else {
                $_returnArray["success"] = false;
                $_returnArray["message"] = "No Oprions Available";
            }
        } else {
            $_returnArray["success"] = false;
            $_returnArray["message"] = "This is not a configurable product";
        }


        $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();

    }


    /**
     * {@inheritdoc}
     */
    public function addProductOptions($productId, $quoteId, $options)
    {
        $quote = $this->quoteFactory->create()->load($quoteId);
        $product = $this->getLoadProduct($productId);
        $_returnArray["product_id"] = $productId;
        $_returnArray["quote"] = $quoteId;
        $_returnArray["options"] = $options;


        $requestInfo = ["super_attribute" => $options, "options" => [], "product" => $productId,
            "qty" => 1, ];

        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(["qty" => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("We found an invalid request for adding product to quote."));
        }

        $requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class)->filter($request);

        /**
         *  * @var $quote \Magento\Quote\Model\Quote $quote */
        $productAdded = $quote->addProduct($product, $request);

        if (!$productAdded || is_string($productAdded)) {
            $_returnArray["success"] = false;
            $_returnArray["message"] = __("Unable to add product to cart.");
        } else {
            $quote->collectTotals()->save();


            $_returnArray["quote_data"] = $quote->getData();

            $_returnArray["success"] = true;
            $_returnArray["message"] = "Product Added Successfully";
        }


        $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
        echo $dataTosend;
        die();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductPageDetails($productId, $storeId, $quoteId, $width, $websiteId,
        $customerToken, $currency, $sku="")
    {

        try {

            //check/set the variables
            $width = ($width) ? $width : "1000";
            $quoteId = ($quoteId) ? $quoteId : 0;
            $storeId = ($storeId) ? $storeId : 1;
            $websiteId = ($websiteId) ? $websiteId : 1;
            $productId = ($productId) ? $productId : 0;
            $sku = ($sku) ? $sku : '';

            $customerToken = ($customerToken) ? $customerToken : "";


            // cache pull
            $cacheId = $this->_cacheHelper->getId("product_page", $productId, array(
                $storeId,
                $width,
                $websiteId));
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                echo $cache;
                die();
            }
            // cache pull


            $customerId = $this->helper->getCustomerByToken($customerToken);
             $customerId = ($customerId)?$customerId:0;

            if (!$customerId && $customerToken != "") {
                $_returnArray["success"] = false;
                $_returnArray["message"] = __("Customer you are requesting does not exist, so you need to logout.");
                $_returnArray["otherError"] = "customerNotExist";
                $customerId = 0;
            } elseif ($customerId != 0) {
                $this->customerSession->setCustomerId($customerId);
            }


            $currency = ($currency)?$currency:$this->store->getBaseCurrencyCode();

            $environment = $this->emulate->startEnvironmentEmulation($storeId);

            $this->store->setCurrentCurrencyCode($currency);

            if ($sku) {
                $this->product = $this->productRepository->get($sku);
                $productId = $this->product->getId();
            } else {
                $this->product = $this->productFactory->create()->load($productId);
            }


            if (!$this->product->getId()) {

                $_returnArray["success"] = false;
                $_returnArray["message"] = __("Invalid product.");

                $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
                echo $dataTosend;
                die();
            }

            $this->coreRegistry->register("product", $this->product);
            $this->coreRegistry->register("current_product", $this->product);
            $this->isIncludeTaxInPrice = false;
            if ($this->helper->getConfigData("tax/display/type") == 2) {
                $this->isIncludeTaxInPrice = true;
            }
            $_returnArray["arUrl"] = "";
            $_returnArray["arType"] = "";
            $_returnArray["arTextureImages"] = "";

            // Getting review list //////////////////////////////////////////////////////
            $reviewList = [];
            $ratingsArr = [];
            $reviewCount = 1;

            $ratingArray = ["1" => 0, "2" => 0, "3" => 0, "4" => 0, "5" => 0];
            $reviews = $this->review->getResourceCollection()->addStoreFilter($storeId)->
                addEntityFilter("product", $productId)->addStatusFilter(\Magento\Review\Model\Review::
                STATUS_APPROVED)->setDateOrder()->addRateVotes();
            $_returnArray["reviewCount"] = $reviews->getSize();
            if ($reviews->getSize() > 0) {
                foreach ($reviews->getItems() as $review) {
                    $oneRating = [];
                    foreach ($review->getRatingVotes() as $vote) {
                        $oneRating[] = $vote->getValue();
                    }
                    $avgRating = array_sum($oneRating) / (count($oneRating) ? count($oneRating) : 1);
                    if ($avgRating < 2) {
                        $avgRating = 1;
                    } elseif ($avgRating < 3) {
                        $avgRating = 2;
                    } elseif ($avgRating < 4) {
                        $avgRating = 3;
                    } elseif ($avgRating < 5) {
                        $avgRating = 4;
                    } elseif ($avgRating == 5) {
                        $avgRating = 5;
                    }
                    $ratingArray[$avgRating] = (($ratingArray[$avgRating])?$ratingArray[$avgRating]:0) + 1;
                    if ($reviewCount <= 5) {
                        $oneReview = [];
                        $ratings = [];
                        $oneReview["title"] = $this->helperCatalog->stripTags($review->getTitle());
                        $oneReview["details"] = $this->helperCatalog->stripTags($review->getDetail());
                        $votes = $review->getRatingVotes();
                        $totalRatings = 0;
                        $totalRatingsCount = 0;
                        if (count($votes)) {
                            foreach ($votes as $_vote) { 
                                $oneVote = [];
                                $oneVote["label"] = $this->helperCatalog->stripTags($_vote->getRatingCode());
                                $oneVote["value"] = number_format($_vote->getValue(), 1, ".", "");
                                $totalRatings += number_format($_vote->getValue(), 1, ".", "");
                                $totalRatingsCount++;
                                $ratings[] = $oneVote;
                                $ratingsArr[] = $_vote->getPercent();
                            }
                        }
                        $oneReview["avgRatings"] = number_format($totalRatings / ($totalRatingsCount ? $totalRatingsCount :
                            1), 1, ".", "");
                        $oneReview["ratings"] = $ratings;
                        $oneReview["reviewBy"] = __("Review by %1", $this->helperCatalog->stripTags($review->
                            getNickname()));
                        $oneReview["reviewOn"] = __("(Posted on %1)", $this->helperCatalog->formatDate($review->
                            getCreatedAt()), "long");
                        $reviewList[] = $oneReview;
                    }
                }


                $ratingVal = 0;
                if (count($ratingsArr) > 0) {
                    $ratingVal = number_format((5 * (array_sum($ratingsArr) / count($ratingsArr))) /
                        100, 1, ".", "");
                }
                $returnArray["rating"] = $ratingVal;
            }
            $returnArray["reviewList"] = $reviewList;


            $crossSell = $this->product->getCrossSellProducts();
            $crossSellEixst = count($crossSell);
            if ($crossSellEixst > 0) {
                $_returnArray["crossSellExist"] = true;
                ;
            } else {
                $_returnArray["crossSellExist"] = false;
            }
            $_returnArray["ratingArray"] = $ratingArray;
            $this->getProductBasicDeatils($productId,$_returnArray,$width);
            // Getting price format /////////////////////////////////////////////////
            $_returnArray["priceFormat"] = $this->localeFormat->getPriceFormat();
            // Getting additional information ///////////////////////////////////////
            $_returnArray["additionalInformation"] = $this->getAdditionalInformation();
            // Getting rating form data /////////////////////////////////////////////
            $this->getRatingData($storeId, $_returnArray,$productId);
            
            
            // Getting custom options ///////////////////////////////////////////////
            $_returnArray["customOptions"] = $this->getCustomOptionsFroDetails();
            // Getting grouped product data /////////////////////////////////////////
            $this->getGroupedProductData($width,$_returnArray);
            // Getting bundle product options ///////////////////////////////////////
            $this->getBundleProductData($_returnArray);
            ////$this->getAmastyLabel();
            $this->getAmastyBundlePack($this->product->getFinalPrice(),$_returnArray,$productId,$storeId,$width,$customerId);
            // Configurable product options /////////////////////////////////////////
            if ($this->product->getTypeId() == "configurable") {
                $configurableBlock = $this->configurableBlock;
                $config_data = $configurableBlock->getJsonConfigs();
                $_returnArray["configurableData"] = $config_data;
            }
            //checking if the product is new/////////////////////////////////////////
            $_returnArray["is_new"] = $this->isProductNew();
            // Getting tier prices //////////////////////////////////////////////////
            ////$this->getTierPrice();
            // Getting related product list /////////////////////////////////////////
            ////$this->getRelatedProduct($this->productId);
            // Getting upsell product list //////////////////////////////////////////
            ////$this->getUpsellProduct();
            
            
            
            $stockItem = $this->stockRegistry->getStockItem($productId, $this->
                product->getStore()->getWebsiteId());
            $minQty = $stockItem->getMinSaleQty();
            $config = $this->product->getPreconfiguredValues();
            $configQty = $config->getQty();
            if ($configQty > $minQty) {
                $minQty = $configQty;
            }
            $_returnArray["isCheckoutAllowed"] = true;
            $_returnArray["minAddToCartQty"] = $minQty;
            $_returnArray["success"] = true;
            $this->customerSession->setCustomerId(null);
            $this->emulate->stopEnvironmentEmulation($environment);
            


            $_returnArray["productId"] = $productId;

            $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);
            $this->_cacheHelper->save($dataTosend, $cacheId);

            echo $dataTosend;
            die();


        }
        catch (\Exception $e) {
            $_returnArray["success"] = false;
            $_returnArray["message"] = __($e->getMessage());
            $dataTosend = json_encode($_returnArray, JSON_UNESCAPED_UNICODE);

            echo $dataTosend;
            die();
        }


    }
    
     protected function isProductNew()
    {
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $productNewFromDate = $this->product->getNewsFromDate();
        $productNewToDate = $this->product->getNewsToDate();
        if (strtotime($todayStartOfDayDate) > strtotime($productNewFromDate)
            && strtotime($todayEndOfDayDate) < strtotime($productNewToDate)
        ) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getAmastyBundlePack($finalPrice,&$_returnArray,$productId,$storeId,$width,$customerId) {

        
        $connection = $this->resourceConnection;
        $sql    = "SELECT `pack_id` FROM `amasty_mostviewed_pack_product` WHERE `product_id`= $productId ORDER BY `entity_id` DESC";
        $result = $connection->fetchAll($sql);
        foreach ($result as $key => $value) {
            $packId = @$value['pack_id'];
            $amastyBundlePack = $proArray = [];
            if(!empty($packId)) {
                $sql    = "SELECT * FROM `amasty_mostviewed_pack` WHERE `pack_id`= $packId AND `status` = 1";
                $variable = $connection->fetchAll($sql);
                foreach ($variable as $key => $val) {

                    $productArray = explode(',', $val['product_ids']);
                    $key = array_search($productId, $productArray);

                    if ($key !== false)unset($productArray[$key]);
                    if (count($productArray) > 0) {

                        $productCollection = $this->productFactory->create()->getCollection()
                            ->addAttributeToSelect("*")
                            ->addFieldToFilter("entity_id", ["in"=>$productArray])
                            ->setPageSize(5)
                            ->setCurPage(1);


                        foreach ($productCollection as $key => $eachProduct) {

                            $amastyBundlePack[] = $this->helperCatalog->getOneProductRelevantData(
                                $eachProduct,
                                $storeId,
                                $width,
                                $customerId
                            );
                        }
                        foreach ($amastyBundlePack as $key1 => $value) {
                            //discount_type  == 0  Fixed
                            //discount_type  == 1  Percentage
                            if($val['discount_type']  == 0) {

                                $totalprdocutsPrice             = $finalPrice + $value['price'];
                                $updatePrice                    = $totalprdocutsPrice - $val['discount_amount'];

                                $value['price']             =  $value['price'];
                                $value['finalPrice']        =  $value['finalPrice'];
                                $value['formattedPrice']    =  $value['formattedPrice'];
                                $value['formattedFinalPrice'] = $value['formattedFinalPrice'];
                                $value['discount_type']        = $val['discount_type'];
                                $value['apply_for_parent']     = $val['apply_for_parent'];
                                $value['discount_amount']      = $val['discount_amount'];
                                $value['discount_price']       = $updatePrice;

                            } elseif($val['discount_type'] == 1) {

                                $totalprdocutsPrice             = $finalPrice + $value['price'];
                                $updatePrice                    = $totalprdocutsPrice * $val['discount_amount'] / 100;
                                $calculatedPrice                = $totalprdocutsPrice - $updatePrice;
                                $value['price']                 = $value['price'];
                                $value['finalPrice']            = $value['finalPrice'];
                                $value['formattedPrice']        = $this->helperCatalog->stripTags($this->priceCurrency->format($updatePrice));
                                $value['formattedFinalPrice']   = $value['formattedFinalPrice'];
                                $value['discount_type']         = $val['discount_type'];
                                $value['apply_for_parent']      = $val['apply_for_parent'];
                                $value['discount_amount']       = $val['discount_amount'];
                                $value['discount_price']        = $updatePrice;
                                $value['discount_calcuated_price']       = $calculatedPrice;
                            }
                            $proArray['bundlePack'][$key1] =   $value;
                        }
                    }
                }
            //    print_r($proArray); die;
                $_returnArray["amastyBundlePack"][] = $proArray;
            } else {
                $_returnArray["amastyBundlePack"][] = $proArray;
            }
        }
    }
    
    protected function getBundleProductData(&$_returnArray)
    {

        if ($this->product->getTypeId() == "bundle") {
            $typeInstance = $this->product->getTypeInstance(true);
            $typeInstance->setStoreFilter($this->product->getStoreId(), $this->product);
            $optionCollection = $typeInstance->getOptionsCollection($this->product);
            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($this->product),
                $this->product
            );


            $bundleOptionCollection = $optionCollection
                ->appendSelections(
                    $selectionCollection,
                    false,
                    $this->productFactory->create()->getSkipSaleableCheck()
                );


            $bundleOptions = [];
            foreach ($bundleOptionCollection as $bundleOption) {

                $oneOption = [];
                if (!$bundleOption->getSelections()) {
                    continue;
                }
                $oneOption = $bundleOption->getData();
                $selections = $bundleOption->getSelections();
                unset($oneOption["selections"]);
                $bundleOptionValues = [];
                foreach ($selections as $selection) {
                    $eachBundleOptionValues = [];
                    if ($selection->isSaleable()) {
                        $coreHelper = $this->pricingHelper;

                        $price = $this->product->getPriceModel()->getSelectionPreFinalPrice($this->product, $selection, 1);

                        $priceTax = $this->taxHelper->getTaxPrice($this->product, $price);
                        if ($oneOption["type"] == "checkbox" || $oneOption["type"] == "multi") {
                            $eachBundleOptionValues["title"] = str_replace(
                                "&nbsp;",
                                " ",
                                $this->helperCatalog->stripTags(
                                    $this->getSelectionQtyTitlePrice($priceTax, $selection, true)
                                )
                            );
                        }
                        if ($oneOption["type"] == "radio" || $oneOption["type"] == "select") {
                            $eachBundleOptionValues["title"] = str_replace(
                                "&nbsp;",
                                " ",
                                $this->helperCatalog->stripTags(
                                    $this->getSelectionTitlePrice($priceTax, $selection, false)
                                )
                            );
                        }
                        $eachBundleOptionValues["price"] = $coreHelper->currencyByStore(
                            $priceTax,
                            $this->product->getStore(),
                            false,
                            false
                        );
                        $eachBundleOptionValues["isSingle"] = (count($selections) == 1 && $bundleOption->getRequired());
                        $eachBundleOptionValues["isDefault"] = $selection->getIsDefault();
                        $eachBundleOptionValues["defaultQty"] = $selection->getSelectionQty();
                        $eachBundleOptionValues["optionValueId"] = $selection->getSelectionId();
                        $eachBundleOptionValues["foramtedPrice"] = $coreHelper->currencyByStore(
                            $priceTax,
                            $this->product->getStore(),
                            true,
                            true
                        );
                        $eachBundleOptionValues["isQtyUserDefined"] = $selection->getSelectionCanChangeQty();
                        $bundleOptionValues[] = $eachBundleOptionValues;
                    }
                }
                $oneOption["optionValues"] = $bundleOptionValues;
                $bundleOptions[] = $oneOption;
            }
            $_returnArray["bundleOptions"] = $bundleOptions;
            $_returnArray["priceView"] = $this->product->getPriceView();
        }
    }
    
    
    protected function getSelectionTitlePrice($amount, $selection, $includeContainer = true)
    {
        $priceTitle = '<span class="product-name">' .
            $this->helperCatalog->escapeHtml($selection->getName()) . '</span>';
        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+' .
            $this->getPriceInCurrency($amount). ($includeContainer ? '</span>' : '');
        return $priceTitle;
    }
    
    
    protected function getGroupedProductData($width,&$_returnArray)
    {
        if ($this->product->getTypeId() == "grouped") {
            $groupedParentId = $this->groupedProduct->getParentIdsByChild($this->product->getId());
            $associatedProducts = $this->product->getTypeInstance(true)->getAssociatedProducts($this->product);
            $minPrice = [];
            $groupedData = [];
            foreach ($associatedProducts as $associatedProduct) {
                $defaultQty = $associatedProduct->getQty();
                $associatedProduct = $this->productFactory->create()->load($associatedProduct->getId());
                $eachAssociatedProduct = [];
                $eachAssociatedProduct["name"] = $this->helperCatalog->stripTags($associatedProduct->getName());
                $eachAssociatedProduct["id"] = $associatedProduct->getId();
                if ($associatedProduct->isAvailable()) {
                    $eachAssociatedProduct["isAvailable"] = (bool)$associatedProduct->isAvailable();
                } else {
                    $eachAssociatedProduct["isAvailable"] = false;
                }
                $fromdate = $associatedProduct->getSpecialFromDate();
                $todate = $associatedProduct->getSpecialToDate();
                $isInRange = false;
                if (isset($fromdate) && isset($todate)) {
                    $today = $this->date->date("Y-m-d H:i:s");
                    $todayTime = $this->date->timestamp($today);
                    $fromTime = $this->date->timestamp($fromdate);
                    $toTime = $this->date->timestamp($todate);
                    if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                        $isInRange = true;
                    }
                }
                if (isset($fromdate) && !isset($todate)) {
                    $today = $this->date->date("Y-m-d H:i:s");
                    $todayTime = $this->date->timestamp($today);
                    $fromTime = $this->date->timestamp($fromdate);
                    if ($todayTime >= $fromTime) {
                        $isInRange = true;
                    }
                }
                if (!isset($fromdate) && isset($todate)) {
                    $today = $this->date->date("Y-m-d H:i:s");
                    $todayTime = $this->date->timestamp($today);
                    $fromTime = $this->date->timestamp($fromdate);
                    if ($todayTime <= $fromTime) {
                        $isInRange = true;
                    }
                }
                $eachAssociatedProduct["isInRange"] = $isInRange;
                $eachAssociatedProduct["defaultQty"] = (int)$defaultQty;
                $eachAssociatedProduct["specialPrice"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($associatedProduct->getSpecialPrice())
                );
                $eachAssociatedProduct["foramtedPrice"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($associatedProduct->getPrice())
                );
                $eachAssociatedProduct["thumbNail"] = $this->helperCatalog->getImageUrl(
                    $associatedProduct,
                    $width/5
                );
                $eachAssociatedProduct["dominantColor"] = $this->helper->getDominantColor(
                    $this->helper->getDominantColorFilePath(
                        $this->helperCatalog->getImageUrl($associatedProduct, $width/5)
                    )
                );
                $groupedData[] = $eachAssociatedProduct;
            }
            $_returnArray["groupedData"] = $groupedData;
            $minPrice = 0;
            if ($this->product->getMinimalPrice() == "") {
                $associatedProducts = $this->product->getTypeInstance(true)->getAssociatedProducts($this->product);
                $minPriceArr = [];
                foreach ($associatedProducts as $associatedProduct) {
                    if ($ogPrice = $associatedProduct->getPrice()) {
                        $minPriceArr[] = $ogPrice;
                    }
                }
                if (!empty($minPriceArr)) {
                    $minPrice = min($minPriceArr);
                }
            } else {
                $minPrice = $this->product->getMinimalPrice();
            }
            if ($this->isIncludeTaxInPrice) {
                $_returnArray["groupedPrice"] = $this->helperCatalog->stripTags(
                    $this->pricingHelper->currency($this->taxHelper->getTaxPrice($this->product, $minPrice))
                );
            } else {
                $_returnArray["groupedPrice"] = $this->helperCatalog->stripTags(
                    $this->pricingHelper->currency($minPrice)
                );
            }
        }
    }
    
    
    
    protected function getCustomOptionsFroDetails()
    {
        $optionBlock = $this->productOptionBlock;
        $options = $optionBlock->decorateArray($optionBlock->getOptions());
        $customOptions = [];
        if (count($options)) {
            $eachOption = [];
            foreach ($options as $option) {
                $eachOption = $option->getData();
                $eachOption["unformatted_default_price"] = $option->getDefaultPrice();
                $eachOption["formatted_default_price"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($option->getDefaultPrice())
                );
                $eachOption["unformatted_price"] = $option->getPrice();
                $eachOption["formatted_price"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($option->getPrice())
                );
                $optionValueCollection = $option->getValues();
                if (is_array($optionValueCollection) || is_object($optionValueCollection)) {
                    foreach ($optionValueCollection as $optionValue) {
                        $eachOptionValue = [];
                        $eachOptionValue = $optionValue->getData();
                        $eachOptionValue["formatted_price"] = $this->helperCatalog->stripTags(
                            $this->getPriceInCurrency($optionValue->getPrice())
                        );
                        $eachOptionValue["formatted_default_price"] = $this->helperCatalog->stripTags(
                            $this->getPriceInCurrency($optionValue->getDefaultPrice())
                        );
                        $eachOption["optionValues"][] = $eachOptionValue;
                    }
                }
                $customOptions[] = $eachOption;
            }
            
        }
        
        return $customOptions;
    }
    
    
    protected function getProductBasicDeatils($productId,&$returnArray,$width)
    {
        $returnArray["id"] = $productId;
        $returnArray["name"] = html_entity_decode($this->product->getName(),ENT_QUOTES);
        $returnArray["typeId"] = $this->product->getTypeId();
        $returnArray["productUrl"] = $this->product->getProductUrl();
        $returnArray["guestCanReview"] = (bool)$this->helper
            ->getConfigData("catalog/review/allow_guest");
        $returnArray["showPriceDropAlert"] = (bool)$this->helper
            ->getConfigData("catalog/productalert/allow_price");
        $returnArray["showBackInStockAlert"] = (bool)$this->helper
            ->getConfigData("catalog/productalert/allow_stock");
        $returnArray["isAllowedGuestCheckout"] = (bool)$this->helper
            ->getConfigData("checkout/options/guest_checkout");

        $price = $this->product->getPrice();
        $finalPrice = $this->product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        if ($this->product->getTypeId() == "bundle") {
            
            $returnArray["minPrice"] = $finalPrice;
            $returnArray["maxPrice"] = $finalPrice;
            $returnArray["formattedMinPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($finalPrice)
            );
            $returnArray["formattedMaxPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($finalPrice)
            );
            if ($this->product->getSpecialPrice()) {
                $price = $this->product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
                $finalPrice = $this->product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            }
        } else {
            $returnArray["minPrice"] = $this->product->getMinPrice();
            $returnArray["maxPrice"] = $this->product->getMaxPrice();
            $returnArray["formattedMinPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($this->product->getMinPrice())
            );
            $returnArray["formattedMaxPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($this->product->getMaxPrice())
            );
        }

        if ($this->product->getTypeId() == "configurable") {
            $regularPrice = $this->product->getPriceInfo()->getPrice("regular_price");
            $price = $regularPrice->getAmount()->getBaseAmount();
        } elseif (!empty($price)) {
            $price = $this->pricingHelper->currency($price, false, false);
            $finalPrice = $this->pricingHelper->currency($finalPrice, false, false);
        } elseif (empty($price)) {
            $price = 0.0;
        }
        $this->isIncludeTaxInPrice = false;
        if ($this->helper->getConfigData("tax/display/type") == 2) {
            $this->isIncludeTaxInPrice = true;
        }
        if ($this->isIncludeTaxInPrice) {
            $returnArray["price"] = $this->taxHelper->getTaxPrice($this->product, $price);
            $returnArray["finalPrice"] = $this->taxHelper->getTaxPrice($this->product, $finalPrice);
            $returnArray["specialPrice"] = $this->taxHelper->getTaxPrice(
                $this->product,
                $this->product->getSpecialPrice()
            );
            $returnArray["formattedPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($this->taxHelper->getTaxPrice($this->product, $price))
            );
            $returnArray["formattedFinalPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format(
                    $this->taxHelper->getTaxPrice($this->product, $finalPrice)
                )
            );
            $returnArray["formattedSpecialPrice"] = $this->helperCatalog->stripTags($this->priceCurrency->format(
                $this->taxHelper->getTaxPrice($this->product, $this->product->getSpecialPrice())
            ));
        } else {
            $returnArray["price"] = $price;
            $returnArray["finalPrice"] = $finalPrice;
            $returnArray["specialPrice"] = $this->product->getSpecialPrice();
            $returnArray["formattedPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($price)
            );
            $returnArray["formattedFinalPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($finalPrice)
            );
            $returnArray["formattedSpecialPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($this->product->getSpecialPrice())
            );
        }
        $returnArray["msrp"] = $this->product->getMsrp();
        $returnArray["thumbNail"] = $this->helperCatalog->getImageUrl($this->product, $width/2.5);
        $returnArray["msrpEnabled"] = $this->product->getMsrpEnabled();
        if ($this->product->getDescription() != "") {
            $returnArray["description"] = $this->filterProvider->getBlockFilter()->filter(htmlspecialchars_decode(
                $this->product->getDescription()
            ));
        } else {
            $returnArray["description"] = "";
        }
        $formattedMsrp = $this->getPriceInCurrency($this->product->getMsrp());
        $returnArray["formattedMsrp"] = $this->helperCatalog->stripTags($formattedMsrp);
        $returnArray["shortDescription"] = $this->filterProvider->getBlockFilter()
            ->filter(htmlspecialchars_decode($this->product->getShortDescription()));
        $returnArray["msrpDisplayActualPriceType"] = $this->product->getMsrpDisplayActualPriceType();
        $returnArray["isInRange"] = $this->checkIsInRange();


        if ($this->product->isAvailable()) {
                $returnArray["availability"] = __("In stock");
                $returnArray["isAvailable"] = true;
            } else {
                $returnArray["availability"] = __("Out of stock");
                $returnArray["isAvailable"] = false;
            }
    }
    
    protected function checkIsInRange()
    {
        $fromdate = $this->product->getSpecialFromDate();
        $todate = $this->product->getSpecialToDate();
        $isInRange = false;
        if (isset($fromdate) && isset($todate)) {
            $today = $this->date->date("Y-m-d H:i:s");
            $todayTime = strtotime($today);
            $fromTime = strtotime($fromdate);
            $toTime = strtotime($todate);
            if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                $isInRange = true;
            }
        }
        if (isset($fromdate) && !isset($todate)) {
            $today = $this->date->date("Y-m-d H:i:s");
            $todayTime = strtotime($today);
            $fromTime = strtotime($fromdate);
            if ($todayTime >= $fromTime) {
                $isInRange = true;
            }
        }
        if (!isset($fromdate) && isset($todate)) {
            $today = $this->date->date("Y-m-d H:i:s");
            $today_time = strtotime($today);
            $from_time = strtotime($fromdate);
            if ($today_time <= $from_time) {
                $isInRange = true;
            }
        }
        return $isInRange;
    }
    
    protected function getPriceInCurrency($price)
    {
        return $this->pricingHelper->currency($price);
    }


    protected function getRatingData($storeId, &$_returnArray,$productId)
    {
        $ratingFormData = [];
        $ratingCollection = $this->rating->getResourceCollection()->addEntityFilter("product")->
            setPositionOrder()->setStoreFilter($storeId)->addRatingPerStoreName($storeId)->
            load()->addOptionToItems();
        foreach ($ratingCollection as $rating) {
            $eachTypeRating = [];
            $eachRatingFormData = [];
            foreach ($rating->getOptions() as $option) {
                $eachTypeRating[] = $option->getId();
            }
            $eachRatingFormData["id"] = $rating->getId();
            $eachRatingFormData["name"] = $this->helperCatalog->stripTags($rating->
                getRatingCode());
            $eachRatingFormData["values"] = $eachTypeRating;
            $ratingFormData[] = $eachRatingFormData;
        }
        $_returnArray["ratingFormData"] = $ratingFormData;
        // Getting rating data //////////////////////////////////////////////////////
        $ratingCollection->addEntitySummaryToItem($productId, $storeId);
        $ratingData = [];
        foreach ($ratingCollection as $rating) {
            if ($rating->getSummary()) {
                $eachRating = [];
                $eachRating["ratingCode"] = $this->helperCatalog->stripTags($rating->
                    getRatingCode());
                $eachRating["ratingValue"] = number_format((5 * $rating->getSummary()) / 100, 1,
                    ".", "");
                $ratingData[] = $eachRating;
            }
        }
        $_returnArray["ratingData"] = $ratingData;

    }


    protected function getAdditionalInformation()
    {
        $additionalInformation = [];
        foreach ($this->product->getAttributes() as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $value = $attribute->getFrontend()->getValue($this->product);
                if (!$this->product->hasData($attribute->getAttributeCode())) {
                    $value = __("N/A");
                } elseif ((string )$value == "") {
                    $value = __("No");
                } elseif ($attribute->getFrontendInput() == "price" && is_string($value)) {
                    $value = $this->helperCatalog->stripTags($this->getPriceInCurrency($value));
                }
                if (is_string($value) && strlen($value)) {
                    $eachAttribute = [];
                    $eachAttribute["label"] = htmlspecialchars_decode($attribute->getStoreLabel());
                    $eachAttribute["value"] = htmlspecialchars_decode($value);
                    $additionalInformation[] = $eachAttribute;
                }
            }
        }
        return $additionalInformation;
    }


}
