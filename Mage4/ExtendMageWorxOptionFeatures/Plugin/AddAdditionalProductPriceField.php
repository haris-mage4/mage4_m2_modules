<?php

namespace Mage4\ExtendMageWorxOptionFeatures\Plugin;


use MageWorx\OptionFeatures\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Product\View;

class AddAdditionalProductPriceField extends  \MageWorx\OptionFeatures\Plugin\AddAdditionalProductPriceField {
    protected $_storeManager;
public function __construct(View $block, StoreManagerInterface $storeManager,Data $helper)
{
    $this->_storeManager = $storeManager;
    $this->block = $block;
    parent::__construct($helper);
}
public function beforeSetTemplate($subject, $template)
{
    parent::beforeSetTemplate($subject, $template);
    $blockName = $subject->getNameInLayout();
    if ($blockName !== 'product.info.addtocart.additional' || !$subject->getProduct()) {
        return $template;
    }

    if ($this->isNeedToShowAdditionalField($subject->getProduct()) && $this->block->getRequest()->getActionName() === "configure") {
         $subject->setData('show_additional_price', true);
        $template = "Mage4_ExtendMageWorxOptionFeatures::cart/item/configure/updatecart.phtml";
    }
    if ($this->isNeedToShowAdditionalField($subject->getProduct()) && $this->block->getRequest()->getActionName() === "view") {
        $subject->setData('show_additional_price', true);
        $template = "MageWorx_OptionFeatures::catalog/product/addtocart.phtml";
    }
    if ($this->isNeedToShowShareableLink($subject->getProduct())) {
        $subject->setData('show_shareable_link', true);
        $subject->setData(
            'shareable_link_text',
            $this->helper->getShareableLinkText($subject->getProduct()->getStoreId())
        );
        $subject->setData(
            'shareable_link_success_text',
            $this->helper->getShareableLinkSuccessText($subject->getProduct()->getStoreId())
        );
        $subject->setData(
            'shareable_link_hint_text',
            $this->helper->getShareableLinkHintText($subject->getProduct()->getStoreId())
        );
        $template = "MageWorx_OptionFeatures::catalog/product/addtocart.phtml";
    }

    return $template;
}
}
