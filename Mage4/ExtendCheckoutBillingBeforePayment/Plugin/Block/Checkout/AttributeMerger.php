<?php

namespace Mage4\ExtendCheckoutBillingBeforePayment\Plugin\Block\Checkout;

/**
 * Class AttributeMerger
 * @package Mage4\ExtendCheckoutBillingBeforePayment\Plugin\Block\Checkout\AttributeMerger
 */
class AttributeMerger
{
    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param $result
     * @return mixed
     */

    public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result)
    {
        $result['firstname']['placeholder'] = __('First Name');
        $result['lastname']['placeholder'] = __('Last Name');
        $result['street']['children'][0]['placeholder'] = __('Address 1');
        $result['street']['children'][1]['placeholder'] = __('Address 2');
        $result['city']['placeholder'] = __('Enter City');
        $result['postcode']['placeholder'] = __('Enter Zip/Postal Code');
        $result['telephone']['placeholder'] = __('Enter Phone Number');
        return $result;
    }
}
