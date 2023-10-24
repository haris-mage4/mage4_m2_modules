var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Mage4_CheckoutSteps/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/payment': {
                'Mage4_CheckoutSteps/js/view/payment-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Mage4_CheckoutSteps/js/view/shipping-information-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Mage4_CheckoutSteps/js/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Mage4_CheckoutSteps/js/view/summary/abstract-total-mixin': true
            },
            'Magento_Checkout/js/view/summary/item/details/thumbnail': {
                'Mage4_CheckoutSteps/js/view/summary/item/details/thumbnail-mixin': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Checkout/template/shipping.html': 'Mage4_CheckoutSteps/template/shipping.html',
            'Magento_Checkout/template/payment.html': 'Mage4_CheckoutSteps/template/payment.html',
            'Magento_Checkout/template/progress-bar.html': 'Mage4_CheckoutSteps/template/progress-bar.html',
            'Magento_Checkout/template/shipping-information.html': 'Mage4_CheckoutSteps/template/shipping-information.html',
            'Magento_Checkout/template/shipping-information/address-renderer/default.html': 'Mage4_CheckoutSteps/template/shipping-information/address-renderer/default.html',
            'Magento_Checkout/template/summary/cart-items.html': 'Mage4_CheckoutSteps/template/summary/cart-items.html',
            'Magento_Checkout/template/payment-methods/list.html': 'Mage4_CheckoutSteps/template/payment-methods/list.html',
            'Magento_Checkout/template/summary/item/details/thumbnail.html': 'Mage4_CheckoutSteps/template/summary/item/details/thumbnail.html',
            'Magento_Checkout/js/model/step-navigator': 'Mage4_CheckoutSteps/js/model/step-navigator'
        }
    }
};
