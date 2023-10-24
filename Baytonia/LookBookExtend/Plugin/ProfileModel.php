<?php
namespace Baytonia\LookBookExtend\Plugin;

class ProfileModel
{
    
    protected $coreHelper;
    public function __construct(\Magezon\Core\Helper\Data $coreHelper,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Catalog\Model\Config $catalogConfig)
    {
        $this->coreHelper = $coreHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogConfig      = $catalogConfig;
    }
    
    public function afterGetMakerProductCollection(\Magezon\LookBook\Model\Profile $subject, $result)
    {

        $listSku = [];
        $markers = $this->coreHelper->unserialize($subject->getData('marker'));
        foreach ($markers as $marker) {
            $listSku[] = $marker['sku'];
        }
        
        $_catalogconfigarray = $this->catalogConfig->getProductAttributes();
        $_catalogconfigarray[] = "final_price";
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('sku', ['in' => $listSku])
                    //->addMinimalPrice()
                    //->addFinalPrice()
                    ->addTaxPercents()
                    ->addAttributeToSelect("*")
                    ->addUrlRewrite();
        return $collection;

    }
}
