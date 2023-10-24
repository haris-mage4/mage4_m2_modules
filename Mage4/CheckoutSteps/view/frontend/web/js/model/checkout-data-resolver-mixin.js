define([
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-method'
],function (_, wrapper, checkoutData, selectShippingMethodAction) {
    'use strict';

    return function (checkoutDataResolver) {

        var resolveShippingRates = wrapper.wrap(
            checkoutDataResolver.resolveShippingRates,
            function (originalResolveShippingRates, ratesData) {

                // if selected shipping method is null then set default shipping method using the ratesData and selectShippingMethodAction in case of multiple shipping method
                if (!checkoutData.getSelectedShippingRate() && ratesData.length > 0) {
                    selectShippingMethodAction(ratesData[0]);
                }

                return originalResolveShippingRates(ratesData);
            }
        );

        return _.extend(checkoutDataResolver, {
            resolveShippingRates: resolveShippingRates
        });
    };
});
