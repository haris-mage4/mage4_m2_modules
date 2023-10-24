<?php
namespace Baytonia\CustomCheckout\Helper;
use Magento\Store\Model\ScopeInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const COUNTRY_CODE_PATH = 'general/country/default';
    protected $scopeConfig;
    protected $country;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\Country $country,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product $product

    ) {
        $this->country          = $country;
        $this->scopeConfig      = $scopeConfig;
        $this->cart = $cart;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->product          = $product;
        parent::__construct($context);
    }

    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
                                        self::COUNTRY_CODE_PATH,
                                        ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getRegions()
    {
        $countryCode        = $this->getCountryByWebsite() ? $this->getCountryByWebsite() : 'SA';
        $regionCollection   = $this->country->loadByCode($countryCode)->getRegions();
        $regions            = $regionCollection->loadData()->toOptionArray(false);
        return json_encode($regions);
    }

    public function getRegionsList()
    {
        $countryCode        = 'SA';
        $regionCollection   = $this->country->loadByCode($countryCode)->getRegions();
        $regions            = $regionCollection->loadData()->toOptionArray(false);
        return json_encode($regions);
    }

    public function getAddress() {

        $cart           = $this->cart->getQuote();
        $items          = $cart->getAllItems();
        $shippingAddress = $cart->getShippingAddress();
        if(isset($shippingAddress->getStreet()[0]) && !empty($shippingAddress->getStreet()[0])) {
            $address = $shippingAddress->getStreet()[0].', '.$shippingAddress->getCity().', '.$shippingAddress->getCountryId();
        } else {
            $address ="";
        }
        return $address;
    }

    public function dataLayerCheckout() {

        $cart           = $this->cart->getQuote();
        $items          = $cart->getAllItems();
        $html = [];
        $counter = 1;
        foreach($items as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $categoryIds = $product->getCategoryIds();
            $categories = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $categoryIds);
            foreach ($categories as $category)$categoryName =  $category->getName();

            $html[] =  [    
                            "name"      => $item->getName(),
                            "id"        => (INT)$item->getProductId(),
                            "sku"       => $item->getSku(),
                            "price"     => (INT)$item->getPrice(),
                            "quantity"  => (INT)$item->getQty(),
                            "category"  => $categoryName,
                            "brand"     => $this->product->load($item->getProductId())->getAttributeText('mgs_brand'),
                            "position"  => (INT)$counter,
                            "list"      => "Similar Products" 
                        ];
            $counter++;
        }
        $html = json_encode($html, JSON_UNESCAPED_UNICODE);
        return $html;
    }

    public function getPhone() {
        if($this->customerSession->isLoggedIn()) {  
            $phone = $this->customerSession->getCustomer()->getTelephone(); 
            return $phone;
        } else {
            return "";
        }
    }
}
