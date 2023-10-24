<?php

namespace InformaticsCommerce\ProductAskQuestions\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Data extends AbstractHelper
{
    protected $_registry;
    protected $_directoryList;

    protected $_filesystem;


    protected $_storeManager;
    protected $_priceHelper;

    protected $_eavConfig;
    protected $_productFactory;
    protected $_imageHelper;
    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    protected $_configurableProduct;


    public function __construct(Configurable $configurableProduct,ProductRepository $productRepository, ImageHelper $imageHelper, ProductFactory $productFactory, Config $eavConfig, PriceHelper $priceHelper, StoreManagerInterface $storeManager, DirectoryList $directoryList, Filesystem $filesystem, Registry $registry, Context $context)
    {
        $this->_productRepository = $productRepository;
        $this->_productFactory = $productFactory;
        $this->_eavConfig = $eavConfig;
        $this->_directoryList = $directoryList;
        $this->_filesystem = $filesystem;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_priceHelper = $priceHelper;
        $this->_imageHelper = $imageHelper;
        $this->_configurableProduct = $configurableProduct;
        parent::__construct($context);
    }

    public function getProductMediaPath()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/product';
    }

    public function getFormattedPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    public function getCurrentProduct()
    {
        $productRegistry = $this->_registry->registry('current_product');
        return $this->getProductBySku($productRegistry->getSku());
    }

    /**
     * @param $sku
     * @return ProductInterface|Product|null
     * @throws NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    public function getImagePlaceHolder()
    {
        return $this->_imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * @param $sku
     * @return bool|string
     * @throws LocalizedException
     */
    public function getAttributeValueBySku($sku, $attributeCode)
    {
        $_item = $this->getProductBySku($sku);
        $attrValue = ($_item->getCustomAttribute($attributeCode))
            ?
            $this->_eavConfig->getAttribute($_item::ENTITY, $attributeCode)
                ->getSource()
                ->getOptionText($_item->getCustomAttribute($attributeCode)
                ->getValue())
            :
            '';

        return $attrValue;
    }
    public function getSelectedChildProductByOptions($sku, $selectedOptions)
    {
        try {
            $configurableProduct = $this->getProductBySku($sku);

            $attributes = $configurableProduct->getTypeInstance()->getConfigurableAttributesAsArray($configurableProduct);

            $configurableOptions = [];
            foreach ($attributes as $attribute) {
                $attributeCode = $attribute['attribute_code'];
                $configurableOptions[$attributeCode] = $attribute['values'];
            }

            $usedProducts = $this->_configurableProduct->getUsedProducts($configurableProduct,null);

            foreach ($usedProducts as $product) {
                foreach ($selectedOptions as $attributeCode => $optionId) {

                    $match = true;
                    $attributeOptions = $configurableOptions[$attributeCode];
                    $option = $this->getOptionById($attributeOptions, $optionId);

                    if (!$option || $product->getData($attributeCode) != $option['value_index']) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    // Found the selected child product based on options
                    return $product;
                }
            }
        } catch (\Exception $e) {
            // Handle exception
            return null;
        }

        return null;
    }

    private function getOptionById($options, $optionId)
    {
        foreach ($options as $option) {
            if ($option['value_index'] == $optionId) {
                return $option;
            }
        }
        return null;
    }
}
