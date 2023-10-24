define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/step-navigator'
], function($, ko, stepNavigator) {
    'use strict';

    var mixin = {
        initialize: function() {
            $(function() {
                $('body').on("click", '#place-order-btn-alone', function () {
                    $(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
                });
            });
            this._super();
        },
        backToShippingMethods: function () {
            stepNavigator.navigateTo('shipping_methods');
        }
    };

    return function(target) {
        return target.extend(mixin);
    }
});
