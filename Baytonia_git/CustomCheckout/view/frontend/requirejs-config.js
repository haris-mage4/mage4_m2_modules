var config = {
    'config': {
        'mixins': {
            'Magento_Ui/js/lib/validation/validator': {
                'Baytonia_CustomCheckout/js/validator-mixin': true
            }
        }
    },
    'map': {
    	'*': {
            'Magento_Checkout/js/view/shipping':'Baytonia_CustomCheckout/js/view/shipping'
		 }
    }
};