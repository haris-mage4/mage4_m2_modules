define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar',
    'Magento_Checkout/js/checkout-data',
    'Magento_Catalog/js/price-utils'
], function(ko, $, Component, quote, stepNavigator, sidebarModel, checkoutData, priceUtils) {
    'use strict';

    var mixin = {
        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
        },

        isVisibleMethods: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping_methods');
        },

        isCustomerLoggedIn: function () {
            return window.isCustomerLoggedIn;
        },

        getCustomerEmail:function () {
            var mail = checkoutData.getInputFieldEmailValue();
            if(this.isCustomerLoggedIn() && window.customerData.email) {
                mail = window.customerData.email;
            }
            return mail;
        },

        /**
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod();

            if (!shippingMethod) {
                return '';
            }

            const title = shippingMethod['method_title'].split(/([\(\d]+)/);

            return title[0].trim();
        },

        /**
         * @return {String}
         */
        getShippingMethodDescription: function () {
            var shippingMethod = quote.shippingMethod();

            if (!shippingMethod) {
                return '';
            }

            const title = shippingMethod['method_title'].split(/([\(\d]+)/);

            return shippingMethod['method_title'].replace(title[0].trim(), '').replace(/[\(\)]/g, '').trim();
        },

        getShippingMethodPrice: function () {
            var shippingMethod = quote.shippingMethod(),
                getShippingMethodPrice = '';

            if (!shippingMethod) {
                return '';
            }

            if (typeof shippingMethod['price_excl_tax'] !== 'undefined') {
                getShippingMethodPrice = shippingMethod['price_excl_tax'];
            }

            return priceUtils.formatPrice(getShippingMethodPrice, quote.getPriceFormat());
        },
        /**
         * Back step.
         */
        back: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');
        },

        back2: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');

            document.getElementById('checkout-step-shipping').scrollIntoView();
        },

        /**
         * Back to shipping method.
         */
        backToShippingMethod: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping_methods');
        }
    };

    return function(target) {
        return target.extend(mixin);
    }
});
