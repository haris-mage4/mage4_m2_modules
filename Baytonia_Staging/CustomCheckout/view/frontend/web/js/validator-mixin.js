    define([
    'jquery',
    'moment',
    'Magento_Checkout/js/model/quote'], function ($, moment, quote) {
    'use strict';

    return function (validator) {
        validator.addRule(
            'phoneSA',
            function (value, params, additionalParams) {
                var countryId = quote.shippingAddress().countryId;
                if(countryId === 'SA'){
                    return $.mage.isEmptyNoTrim(value) ||
                        (value.length == 10 && value.startsWith("05"));
                }
                return true;
            },
            $.mage.__(" رقم الجوال مكون من 10 آرقام " +  "مثال: 05xxxxxxxx")
        );
        return validator;
};
});