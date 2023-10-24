<?php

namespace Baytonia\CustomApis\Helper\Webkul\MobikulCore\Helper;

use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\ProductProvider;
use Baytonia\AmastyVisualMerchExtended\Model\ResourceModel\Product as PinnedProducts;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config as EavConfigAttribute;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Swatches\Helper\Data as SwatchData;

class Catalog extends \Webkul\MobikulCore\Helper\Catalog
{

    public function __construct(PinnedProducts $productsPinned, EavConfigAttribute $eavConfig, SwatchData $swatchData, DateTime $date, \Magento\Framework\Escaper $escaper, \Magento\Framework\Registry $coreRegistry, \Magento\Review\Model\Review $reviewModel, \Magento\Framework\HTTP\Header $httpHeader, \Magento\Catalog\Helper\Image $imageHelper, \Magento\Catalog\Helper\Data $catalogHelper, \Magento\Store\Block\Switcher $storeSwitcher, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Checkout\Helper\Data $checkoutHelper, \Magento\Framework\App\Helper\Context $context, \Magento\Framework\Image\Factory $imageFactory, CategoryRepositoryInterface $categoryRepository, \Magento\Catalog\Model\Layer\Search $layerSearch, \Magento\Framework\Stdlib\StringUtils $stringUtils, \Magento\Framework\Pricing\Helper\Data $priceFormat, \Magento\Catalog\Model\Layer\Resolver $layerResolver, \Magento\Store\Model\StoreRepository $storeRepository, \Magento\Framework\Filesystem\DirectoryList $directory, \Webkul\MobikulCore\Block\Configurable $configurableBlock, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Cms\Model\Template\FilterProvider $filterProvider, \Magento\Customer\Api\CustomerRepositoryInterface $customer, \Magento\Wishlist\Model\WishlistFactory $wishlistRepository, \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Framework\Session\SessionManagerInterface $sessionManager, \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory, \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute, \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishListCollection, \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute $layerFilterAttribute, \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider, \Magento\Framework\Filesystem\Driver\File $fileDriver, \Magento\Store\Model\StoreManagerInterface $storeInterface, ProductRepositoryInterface $productRepository, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, ProductProvider $productProvider, GroupRepositoryInterface $groupRepository, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\App\ResourceConnection $resource)
    {
        parent::__construct($productsPinned, $eavConfig, $swatchData, $date, $escaper, $coreRegistry, $reviewModel, $httpHeader, $imageHelper, $catalogHelper, $storeSwitcher, $dateTime, $checkoutHelper, $context, $imageFactory, $categoryRepository, $layerSearch, $stringUtils, $priceFormat, $layerResolver, $storeRepository, $directory, $configurableBlock, $storeManager, $filterProvider, $customer, $wishlistRepository, $priceCurrency, $localeDate, $sessionManager, $filterItemFactory, $stockRegistry, $layerAttribute, $wishListCollection, $layerFilterAttribute, $filterPriceDataprovider, $fileDriver, $storeInterface, $productRepository, $resultJsonFactory, $productProvider, $groupRepository, $productFactory, $resource);
    }

    /**
     * Function to get one product;s relevant data to display at any page
     *
     * @param \Magento\Catalog\Model\Product $product loaded product
     * @param integer $storeId store Id
     * @param integer $width width
     * @param integer $customerId customer Id
     *
     * @return array
     */
    public function getOneProductRelevantData($product, $storeId, $width, $customerId = 0)
    {
        $this->coreRegistry->unregister("current_product");
        $this->coreRegistry->unregister("product");
        $this->coreRegistry->register("current_product", $product);
        $this->coreRegistry->register("product", $product);
        $pinnedProduct = $this->productsPinned->getProductPositionDataByIds($product->getId());

        $reviews = $this->reviewModel
            ->getResourceCollection()
            ->addStoreFilter($storeId)
            ->addEntityFilter("product", $product->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();

        $eachProduct = [];
        $eachProduct["reviewCount"] = $reviews->getSize();
        $ratings = [];
        if (count($reviews) > 0) {
            foreach ($reviews->getItems() as $review) {
                foreach ($review->getRatingVotes() as $vote) {
                    $ratings[] = $vote->getPercent();
                }
            }
        }
        $isIncludeTaxInPrice = false;
        if ($this->getIfTaxIncludeInPrice() == 2) {
            $isIncludeTaxInPrice = true;
        }
        if ($product->getTypeId() == "configurable") {
            $configurableBlock = $this->configurableBlock;
            $eachProduct["configurableData"] = $configurableBlock->getJsonConfigNew();
        } else {
            $eachProduct["configurableData"] = new \stdClass();
        }
        $eachProduct["isInWishlist"] = false;
        $eachProduct["wishlistItemId"] = 0;
        if ($customerId != 0) {
            $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);
            $wishlistCollection = $this->wishListCollection
                ->create()
                ->addFieldToFilter("wishlist_id", $wishlist->getId())
                ->addFieldToFilter("product_id", $product->getId());
            $item = $wishlistCollection->getFirstItem();
            if ($item->getId() > 0) {
                $eachProduct["isInWishlist"] = true;
                $eachProduct["wishlistItemId"] = (int)$item->getId();
            } else {
                $eachProduct["isInWishlist"] = false;
                $eachProduct["wishlistItemId"] = 0;
            }
        }
        $eachProduct["typeId"] = $product->getTypeId();
        $eachProduct["entityId"] = $product->getId();
        if ($product->getTypeId() == "downloadable") {
            $eachProduct["linksPurchasedSeparately"] = $product->getLinksPurchasedSeparately();
        }
        $rating = 0;
        if (count($ratings) > 0) {
            $rating = number_format((5 * (array_sum($ratings) / count($ratings))) / 100, 1, ".", "");
        }
        $eachProduct["rating"] = $rating;
        if ($product->isAvailable()) {
            $eachProduct["isAvailable"] = true;
        } else {
            $eachProduct["isAvailable"] = false;
        }

        if ($pinnedProduct) {
            $eachProduct['isPinned'] = true;
            $eachProduct['pinned_position'] = $pinnedProduct[$product->getId()];
        } else {
            $eachProduct['isPinned'] = false;
        }

        $price = $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        if ($product->getTypeId() == "configurable") {
            $regularPrice = $product->getPriceInfo()->getPrice("regular_price");
            $price = $regularPrice->getAmount()->getBaseAmount();
        } elseif (!empty($price)) {
            $price = $this->priceFormat->currency($price, false, false);
            $finalPrice = $this->priceFormat->currency($finalPrice, false, false);
        } elseif (empty($price)) {
            $price = 0.0;
        }
        if ($isIncludeTaxInPrice) {
            $eachProduct["price"] = $this->catalogHelper->getTaxPrice($product, $price);
            $eachProduct["finalPrice"] = $this->catalogHelper->getTaxPrice($product, $finalPrice);
            $eachProduct["formattedPrice"] = $this->stripTags(
                $this->priceCurrency->format($this->catalogHelper->getTaxPrice($product, $price))
            );
            $eachProduct["formattedFinalPrice"] = $this->stripTags(
                $this->priceCurrency->format($this->catalogHelper->getTaxPrice($product, $product->getFinalPrice()))
            );
        } else {
            $eachProduct["price"] = $price;
            $eachProduct["finalPrice"] = $finalPrice;
            $eachProduct["formattedPrice"] = $this->stripTags($this->priceCurrency->format($price));
            $eachProduct["formattedFinalPrice"] = $this->stripTags($this->priceCurrency->format($finalPrice));
        }
        $eachProduct["name"] = html_entity_decode($product->getName(), ENT_QUOTES);
        $eachProduct["sku"] = $product->getSku();
        $returnArray["msrpEnabled"] = $product->getMsrpEnabled();
        $eachProduct["hasRequiredOptions"] = ((bool)$product->getRequiredOptions() || (bool)$product->getHasOptions());
        $returnArray["msrpDisplayActualPriceType"] = $product->getMsrpDisplayActualPriceType();
        if ($product->getTypeId() == "grouped") {
            $minPrice = 0;
            $minPrice = $product->getMinimalPrice();
            if ($isIncludeTaxInPrice) {
                $eachProduct["groupedPrice"] = $this->stripTags(
                    $this->priceFormat->currency($this->catalogHelper->getTaxPrice($product, $minPrice))
                );
            } else {
                $eachProduct["groupedPrice"] = $this->stripTags($this->priceFormat->currency($minPrice));
            }
            $eachProduct["formattedFinalPrice"] = $eachProduct["groupedPrice"];
        }
        if ($product->getTypeId() == "bundle") {
            $eachProduct["priceView"] = $product->getPriceView();
            $priceModel = $product->getPriceModel();
            if ($isIncludeTaxInPrice) {
                list($minimalPriceInclTax, $maximalPriceInclTax) = $priceModel->getTotalPrices(
                    $product,
                    null,
                    true,
                    false
                );
                $eachProduct["minPrice"] = $this->catalogHelper->getTaxPrice($product, $minimalPriceInclTax);
                $eachProduct["maxPrice"] = $this->catalogHelper->getTaxPrice($product, $maximalPriceInclTax);
                $eachProduct["formattedMaxPrice"] = $this->stripTags(
                    $this->priceFormat->currency($this->catalogHelper->getTaxPrice($product, $maximalPriceInclTax))
                );
                $eachProduct["formattedMinPrice"] = $this->stripTags(
                    $this->priceFormat->currency($this->catalogHelper->getTaxPrice($product, $minimalPriceInclTax))
                );
            } else {
                list($minimalPriceTax, $maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
                $eachProduct["minPrice"] = $minimalPriceTax;
                $eachProduct["maxPrice"] = $maximalPriceTax;
                $eachProduct["formattedMinPrice"] = $this->stripTags($this->priceFormat->currency($minimalPriceTax));
                $eachProduct["formattedMaxPrice"] = $this->stripTags($this->priceFormat->currency($maximalPriceTax));
            }
            $eachProduct["formattedPrice"] = $eachProduct['minPrice'];
            $eachProduct["formattedFinalPrice"] = $eachProduct['formattedMinPrice'];
        }
        $todate = $product->getSpecialToDate();
        $fromdate = $product->getSpecialFromDate();
        $eachProduct["isNew"] = $this->isProductNew($product);
        $eachProduct["isInRange"] = $this->getIsInRange($todate, $fromdate);
        $eachProduct["thumbNail"] = $this->getImageUrl($product, $width / 2.5);
        $objectManager = \Magento\Framework\app\ObjectManager::getInstance();
        $mobikulHelper = $objectManager->create(\Webkul\MobikulCore\Helper\Data::class);
        //  $eachProduct["dominantColor"] = $mobikulHelper->getDominantColor($eachProduct["thumbNail"]);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minQty = $stockItem->getMinSaleQty();
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        $tier_price = $product->getTierPrice();
        if (count($tier_price) > 0) {
            foreach ($tier_price as $pirces) {
                foreach (array_reverse($pirces) as $k => $v) {
                    if ($k == "price") {
                        $tp = number_format($v, 2, '.', '');
                        $eachProduct['tierPrice'] = $tp;
                        $eachProduct['formattedTierPrice'] = $this->stripTags($this->priceCurrency->format($tp));
                    }
                }
            }
        } else {
            $eachProduct['tierPrice'] = "";
            $eachProduct['formattedTierPrice'] = "";
        }

        if ($configQty > $minQty) {
            $minQty = $configQty;
        }
        $eachProduct["minAddToCartQty"] = $minQty;
 if ($product->getTypeId() == 'simple') {
        $item = $this->stockRegistry->getStockItemBySku($product->getSku());

        if ($item->getManageStock() === 1) {
            if ($product->isAvailable()) {
                $eachProduct["availability"] = __("In stock");
                $eachProduct["isAvailable"] = true;
            } else {
                $eachProduct["availability"] = __("Out of stock");
                $eachProduct["isAvailable"] = false;
            }
        }

        if ($item->getManageStock() === 0) {
            if ($item->getQty() > 0) {
                $eachProduct["availability"] = __("In stock");
                $eachProduct["isAvailable"] = true;
            } else {
                $eachProduct["availability"] = __("Out of stock");
                $eachProduct["isAvailable"] = false;
            }
	  }
        }
        $eachProduct["arUrl"] = (string)$this->getArModelUrl($product);
        $eachProduct["arType"] = $this->getArModelType($product);
        $eachProduct["arTextureImages"] = $this->getTextureImages($product);
        $eachProduct["amastylabel"] = $this->getAmastyLabel($product->getId());
        return $eachProduct;
    }
}
