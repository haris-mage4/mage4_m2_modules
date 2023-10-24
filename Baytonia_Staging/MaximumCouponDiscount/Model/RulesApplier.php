<?php

namespace Baytonia\MaximumCouponDiscount\Model;

/**
 * Class RulesApplier
 * @package Magento\SalesRule\Model\Validator
 */
class RulesApplier extends  \Mexbs\ApBase\Model\Rewrite\SalesRule\RulesApplier
{

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param mixed $couponCode
     * @return $this
     */
    protected function applyRule($item, $rule, $address, $couponCode)
    {
        $itemOriginalPrice  = $item->getOriginalPrice();
        $itemQty        = $item->getQty();
        $address        = $item->getAddress();
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $dataHelper     = $objectManager->create(\Baytonia\MaximumCouponDiscount\Helper\Data::class);
        $isEnabled      = $dataHelper->isEnabled();
        $discountData   = $this->getDiscountData($item, $rule, $address);
        $maxDiscount    = $rule->getMaxDiscount();
        $simpleAction   = $rule->getSimpleAction();
        $discountAmount = $rule->getDiscountAmount();
        $baseSubtotal   = $address->getBaseSubtotal();
        $discountTotalAmount = $baseSubtotal * $discountAmount/100;

        if ($isEnabled  && $maxDiscount > 0 && $simpleAction == 'by_percent') { 
            $finalItemPrice = $itemOriginalPrice  * $itemQty;
            $itemDiscount = $finalItemPrice * $discountAmount / 100;
           if($itemDiscount > $maxDiscount) {
                $discountData->setAmount($maxDiscount) ;
                $discountData->setBaseAmount($maxDiscount) ;
            }
        }
        $this->setDiscountData($discountData, $item);
        $this->maintainAddressCouponCode($address, $rule, $couponCode);
        $this->addDiscountDescription($address, $rule);
        return $this;
    }
}
