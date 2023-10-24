<?php

namespace InformaticsCommerce\GenerateHashUrls\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;


class Generate extends Command
{
    protected $_product;
    protected $_productRepository;
    protected $_configurableProduct;
    protected $_productAttributeRepository;

    public function __construct(ProductAttributeRepositoryInterface $productAttributeRepository, Configurable $configurableProduct, ProductRepository $productRepository, Product $product, string $name = null)
    {
        $this->_productRepository = $productRepository;
        $this->_product = $product;
        $this->_configurableProduct = $configurableProduct;
        $this->_productAttributeRepository = $productAttributeRepository;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('ic:generate:urls')->setDescription('Generate hash urls for configurable products only.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parentParoduct = $this->_product->getCollection()->addFieldToFilter('type_id', ['eq' => 'configurable'])->getData();

        foreach ($parentParoduct as $item) {
            $productData = $this->_productRepository->get($item['sku']);
            $product_url = $productData->getProductUrl();
            $product_url = $product_url . '#';
            $usedProducts = $this->_configurableProduct->getUsedProducts($productData);

            foreach ($usedProducts as $productu) {
                foreach ($productu->getCustomAttributes() as $dd) {
                    var_dump($dd->getData());
                }
            }
        }
        exit;

        if ($productD->getTypeId() === Configurable::TYPE_CODE) {
            $swatchAttributes = $productD->getTypeInstance()->getConfigurableAttributes($productD);
            $product_url = $productD->getProductUrl() . '#';
            $length = count($swatchAttributes);

            foreach ($swatchAttributes as $k => $swatchAttribute) {
                $attributeId = $swatchAttribute->getProductAttribute()->getId();
                // Get the swatch values
                $swatchOptions = $swatchAttribute->getOptions();
                foreach ($swatchOptions as $swatchOption) {
                    var_dump($swatchOption['value_index']);
                    if ($length !== $k) {
                        $product_url = $product_url . $attributeId . "=" . $swatchOption['value_index'] . '&';

                    } else {
                        $product_url = $product_url . $attributeId . "=" . $swatchOption['value_index'];
                    }
                }
            }
            echo $product_url;
        }
    }

//         $obj = ObjectManager::getInstance();
//
//        $attributeCollection = $obj->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
//        $attributeCollection->addVisibleFilter()
//            ->addFieldToFilter('frontend_input', ['notnull' => true]);
//
//        foreach ($attributeCollection as $attribute) {
//            $attributeCode = $attribute->getAttributeCode();
//            $attributeLabel = $attribute->getDefaultFrontendLabel();
//
//            // Get attribute values
//            $valuesCollection = $obj->create('Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection');
//            $valuesCollection->setAttributeFilter($attribute->getId());
//
//            // Check if attribute is swatch-enabled
//            $isSwatchEnabled = $attribute->getIsSwatch();
//
//            echo "Attribute Code: $attributeCode, Label: $attributeLabel\n";
//
//            foreach ($valuesCollection as $value) {
//                $optionId = $value->getOptionId();
//                $optionValue = $value->getValue();
//
//                echo "Value ID: $optionId, Value: $optionValue\n";
//
//                // If the attribute is a swatch-enabled attribute, retrieve the swatch value
//                if ($isSwatchEnabled) {
//                    $swatchValue = $value->getSwatchValue();
//                    $swatchThumb = $value->getSwatchThumb();
//                    echo "Swatch Value: $swatchValue, Swatch Thumbnail: $swatchThumb\n";
//                }
//            }
//
//        }

}
