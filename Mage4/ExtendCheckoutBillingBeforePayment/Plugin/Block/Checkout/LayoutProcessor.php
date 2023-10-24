<?php

namespace Mage4\ExtendCheckoutBillingBeforePayment\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as CheckoutLayoutProcessor;

/**
 * Move Billing address to top
 */
class LayoutProcessor
{
    /**
     * @param CheckoutLayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        CheckoutLayoutProcessor $subject,
        array                   $jsLayout
    ): array
    {
        $paymentLayout = $jsLayout['components']['checkout']['children']['steps']
        ['children']['billing-step']['children']['payment']['children'];

        if (isset($paymentLayout['afterMethods']['children']['billing-address-form'])) {
            $jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']['children']
            ['beforeMethods']['children']['billing-address-form']
                = $paymentLayout['afterMethods']['children']['billing-address-form'];

            unset($jsLayout['components']['checkout']['children']['steps']
                ['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']['billing-address-form']);
        }

        return $jsLayout;
    }
}

