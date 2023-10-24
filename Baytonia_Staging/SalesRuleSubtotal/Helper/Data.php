<?php

namespace Baytonia\SalesRuleSubtotal\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PERCENT_SUBTOTAL = 'by_percent_subtotal';

    /**
     * @param bool $asOptions
     *
     * @return array
     */
    public function getDiscountTypes($asOptions = false)
    {
        $values[] = [
                        'value' => self::PERCENT_SUBTOTAL,
                        'label' => 'Percent of subtotal discount',
                ];
        
        $types = $values;
        return $types;
    }
}
