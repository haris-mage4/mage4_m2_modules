<?php

namespace Baytonia\SalesRuleSubtotal\Model\Rule\Action\Discount;

class ByPercent extends \Magento\SalesRule\Model\Rule\Action\Discount\ByPercent
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param float $rulePercent
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $address = $item->getAddress();
        $baseSubtotal= $address->getBaseSubtotal();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $_rulePct = $rulePercent / 100;
        $discountData->setAmount(($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseAmount(($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct);
        $discountData->setOriginalAmount(($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseOriginalAmount(
            ($qty * $baseItemOriginalPrice - $item->getBaseDiscountAmount()) * $_rulePct
        );

        if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $qty) {
            $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
            $item->setDiscountPercent($discountPercent);
        }

        return $discountData;
    }
}
