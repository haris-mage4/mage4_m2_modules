<?php

namespace Baytonia\CartUpdate\Model\Total;

use Magento\Sales\Model\Order\Total\AbstractTotal;

class TotalSavings extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        // $quote->getSubtotal() - $quote->getSubtotalWithDiscount();

        $items = $quote->getAllVisibleItems();

        $saving = 0;
        $normal = 0;

        foreach ($items as $product) {
            $special = $product->getProduct()->getSpecialPrice() * $product->getQty();
            $normal = $normal + $product->getProduct()->getPrice() * $product->getQty();
            $saving = $saving + $product->getProduct()->getSpecialPrice() * $product->getQty();
        }
        $discountedValue =  abs($quote->getShippingAddress()->getDiscountAmount());


        $totalSavings = ($normal - $saving) + $discountedValue;

        return [
            'code' => 'totalsavings',
            'title' => 'Total Savings',
            'value' => $totalSavings
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Total Savings');
    }
}
