<?php

namespace Baytonia\Overridecleversoft\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaFactory as FullTextSearchCriteriaFactory;
use Magento\Framework\Api\Search\SearchInterface as FullTextSearchApi;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\Request\Http;

class Autocomplete extends \CleverSoft\CleverSearchAutoComplete\Model\Autocomplete\SearchDataProvider
{
    /** @var QueryFactory */
    protected $queryFactory;

    /** @var ItemFactory */
    protected $itemFactory;

    /** @var \Magento\Framework\Api\Search\SearchInterface */
    protected $fullTextSearchApi;

    /** @var FullTextSearchCriteriaFactory */
    protected $fullTextSearchCriteriaFactory;

    /** @var FilterGroupBuilder */
    protected $searchFilterGroupBuilder;

    /** @var FilterBuilder */
    protected $filterBuilder;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /** @var \Magento\Catalog\Helper\Image */
    protected $imageHelper;

    /**
     * Initialize dependencies.
     *
     * @param QueryFactory                                      $queryFactory
     * @param ItemFactory                                       $itemFactory
     * @param FullTextSearchApi                                 $search
     * @param FullTextSearchCriteriaFactory                     $searchCriteriaFactory
     * @param FilterGroupBuilder                                $searchFilterGroupBuilder
     * @param FilterBuilder                                     $filterBuilder
     * @param ProductRepositoryInterface                        $productRepository
     * @param SearchCriteriaBuilder                             $searchCriteriaBuilder
     * @param StoreManagerInterface                             $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Image                     $imageHelper
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        FullTextSearchApi $search,
        FullTextSearchCriteriaFactory $searchCriteriaFactory,
        FilterGroupBuilder $searchFilterGroupBuilder,
        FilterBuilder $filterBuilder,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        Image $imageHelper,
        ListProduct $listProductBlock,
        Http $request
    )
    {
        $this->queryFactory                  = $queryFactory;
        $this->itemFactory                   = $itemFactory;
        $this->fullTextSearchApi             = $search;
        $this->fullTextSearchCriteriaFactory = $searchCriteriaFactory;
        $this->filterBuilder                 = $filterBuilder;
        $this->searchFilterGroupBuilder      = $searchFilterGroupBuilder;
        $this->productRepository             = $productRepository;
        $this->searchCriteriaBuilder         = $searchCriteriaBuilder;
        $this->storeManager                  = $storeManager;
        $this->priceCurrency                 = $priceCurrency;
        $this->imageHelper                   = $imageHelper;
        $this->listProductBlock = $listProductBlock;
        $this->request = $request;
    }

    public function getQueryText() {
        return $this->queryFactory->get()->getQueryText();
    }

    /**
     * getItems method
     *
     * @return array
     */
    public function getItems()
    {
        $result     = [ ];
        $query      = $this->queryFactory->get()->getQueryText();
        $productIds = $this->searchProductsFullText($query);

        // Check if products are found
        if ( $productIds )
        {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in')->create();
            $products       = $this->productRepository->getList($searchCriteria);
            $items = $products->getItems();

            $cat = $this->request->getParam('cat');

            foreach ( $items as $product )
            {
                if ($cat) {
                    if (!in_array($cat, $product->getCategoryIds())) {
                        continue;
                    }
                }
                $image = $this->imageHelper->init($product, 'product_page_image_small')->getUrl();

                $resultItem = $this->itemFactory->create([
                    'title'             => $product->getName(),
                    'price'             => $this->priceCurrency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),false),
                    'special_price'     => $this->priceCurrency->format($product->getPriceInfo()->getPrice('special_price')->getAmount()->getValue(),false),
                    'has_special_price' => $product->getSpecialPrice() > 0 ? true : false,
                    'image'             => $image,
                    'url'               => $product->getProductUrl(),
                    'reviews_rating'    => $product->getReviewsRating(),
                    'short_description' => $product->getShortDescription(),
                    'description' => $product->getDescription(),
                    'add_to_cart' => $this->getAddToCartData($product)
                ]);
                $result[]   = $resultItem;
            }
        }

        return $result;
    }

    public function getProductCollection() {
        $result     = [ ];
        $query      = $this->queryFactory->get()->getQueryText();
        $productIds = $this->searchProductsFullText($query);
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in')->create();
        $products       = $this->productRepository->getList($searchCriteria);
        $items = $products->getItems();

        return $items;
    }

    /**
     * Perform full text search and find IDs of matching products.
     *
     * @param $query
     *
     * @return array
     */
    protected function searchProductsFullText($query)
    {
        $filter1 = $this->filterBuilder
                        ->setField("sku")
                        ->setConditionType("like")
                        ->setValue($query.'%')
                        ->create();
             
        $filter2 = $this->filterBuilder
                        ->setField("name")
                        ->setConditionType("like")
                        ->setValue('%'.$query.'%')
                        ->create();

        $filterGroup1 = $this->searchFilterGroupBuilder
                        ->addFilter($filter1)
                        ->addFilter($filter2)
                        ->create();
      
        $searchCriteria = $this->searchCriteriaBuilder
                                ->setFilterGroups([$filterGroup1])
                                ->create();
        $products       = $this->productRepository->getList($searchCriteria);
        $productIds     = [];
        foreach ( $products->getItems() as $searchDocument ) {
            $productIds[] = $searchDocument->getId();
        }
        return $productIds;
    }

    protected function getAddToCartData($product) {
        $data =  $this->listProductBlock->getAddToCartPostParams($product);
        $data['formUrl'] = $data['action'];
        $data['productId'] = $data['data']['product'];
        $data['paramNameUrlEncoded'] = 'uenc';
        $data['urlEncoded'] = $data['data']['uenc'];

        return $data;
    }
}