define([
    'ko',
    'Magento_Ui/js/form/form',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'uiRegistry',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    ko,
    Component,
    _,
    quote,
    shippingService,
    checkoutDataResolver,
    checkoutData,
    shippingRatesValidator,
    selectShippingMethodAction,
    rateRegistry,
    registry,
    stepNavigator,
    setShippingInformationAction
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mage4_CheckoutSteps/shipping-methods',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item'
        },

        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isVisible: ko.observable(true),
        isLoading: shippingService.isLoading,
        rates: shippingService.getShippingRates(),
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @returns {*}
         */
        initialize: function () {
            this._super();
            var self = this,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            stepNavigator.registerStep(
                'shipping_methods',
                null,
                'Shipping',
                this.isVisible,
                _.bind(this.navigate, this),
                15
            );

            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });

            shippingRatesValidator.initFields(fieldsetName);

            return this;
        },

        navigate: function () {
            this.isVisible(true);
        },

        getRateTitle: function (rate) {
            const title = rate.method_title.split('(');
            return title[0].trim();
        },

        getRateDescription: function (rate) {
            const title = rate.method_title.split('(');
            let trimText = rate.method_title.replace(title[0].trim(), '').replace(')', '').replace('(', '').trim();
            if (trimText.includes('BEST VALUE')) {
                return trimText.replace("BEST VALUE", "").replaceAll('*', '');
            } else if (trimText.includes('*EXPECT COVID DELAYS*')) {
                return trimText.replace("*EXPECT COVID DELAYS*", "");
            } else {
                return trimText
            }
        },

        getBestLabel: function (rate) {
            const title = rate.method_title.split(/([\(\d]+)/);
            const trimText = rate.method_title.replace(title[0].trim(), '').replace(/[\(\)]/g, '').trim();
            if (trimText.includes('BEST VALUE')) {
                return "BEST VALUE";
            }
        },

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            setShippingInformationAction().done(function () {
                //alternatePayment.updatePaymentOptions()
            });
            return true;
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            if (!quote.shippingMethod()) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );

                return false;
            }

            return true;
        },

        navigateToNextStep: function () {
            if (this.validateShippingInformation()) {
                setShippingInformationAction().done(function () {
                    //alternatePayment.updatePaymentOptions();
                    stepNavigator.next();
                });
            }
        },
        backToShipping: function () {
            stepNavigator.navigateTo('shipping');
        }
    });
});
