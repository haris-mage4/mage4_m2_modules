<?php

namespace Baytonia\MageplazaSearchExtended\Helper;

use Mageplaza\Search\Helper\Data as MageplazaSearchHelper;
use Magento\Framework\App\ObjectManager;

class Data extends MageplazaSearchHelper
{
    /**
     * @param $store
     * @param $group
     * @return $this
     */
    public function createJsonFileForStore($store, $group)
    {
        if(!$this->isEnabled($store->getId())){
            return $this;
        }

        $productList = [];

        $escaper = ObjectManager::getInstance()->create('Magento\Framework\Escaper');

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $productList[] = [
                'value' => $escaper->escapeHtml($product->getName()),
                'c'     => $product->getCategoryIds(), //categoryIds
                'd'     => $escaper->escapeHtml($this->getProductDescription($product, $store)), //short description
                'p'     => $this->_priceHelper->currencyByStore($product->getFinalPrice(), $store, false, false), //price
                'i'     => $this->getMediaHelper()->getProductImage($product),//image
                'u'     => $escaper->escapeHtml($this->getProductUrl($product)) //product url
            ];
        }

        $this->getMediaHelper()->createJsFile(
            $this->getJsFilePath($group, $store),
            'var mageplazaSearchProducts = ' . self::jsonEncode($productList)
        );

        return $this;
    }
}