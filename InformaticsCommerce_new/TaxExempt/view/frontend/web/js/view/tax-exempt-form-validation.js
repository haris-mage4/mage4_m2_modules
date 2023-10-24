define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'InformaticsCommerce_TaxExempt/js/model/validate'
    ],
    function (Component, additionalValidators, taxValidation) {
        'use strict';
        additionalValidators.registerValidator(taxValidation);
        return Component.extend({});
    }
);
