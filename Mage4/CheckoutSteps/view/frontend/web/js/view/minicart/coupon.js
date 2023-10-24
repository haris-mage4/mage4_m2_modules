define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (ko, $, Component, customerData, $t) {
    'use strict';

    var guestUrl    = 'guest-carts/:cartId/coupons/:couponCode',
        customerUrl = 'carts/mine/coupons/:couponCode';

    var cartData = customerData.get('cart')();

    return Component.extend({
        /**
         * @return {Boolean}
         */
        isVisible: function(){
            console.info(cartData.summary_count);
            console.info(cartData.summary_count > 0);
            if(cartData.summary_count > 0){
                $('.mini-cart-coupon-wrapper').show();
                $('#block-discount-cartmini-content').hide();
                return true;
            }
            $('.mini-cart-coupon-wrapper').hide();
            return false;
        },
        couponCode: ko.observable(),
        isApplied: ko.observable(),
        shouldVisible: ko.observable(),
        quoteId: ko.observable(),
        isLoggedIn: ko.observable(),
        apiUrl: ko.observable(),
        errorMsg: ko.observable(),
        successMsg: ko.observable(),
        formKey: ko.observable(),
        hideMsgTimeout: null,
        submitUrl: null,

        initialize: function () {
            this.submitUrl = window.checkoutSteps.minicartCouponSubmitUrl;
            this.isApplied(window.checkoutSteps.isCouponApplied === 1);
            this.shouldVisible(window.checkoutSteps.isCouponApplied === 1);
            this.formKey(window.checkoutSteps.formKey);
            this.couponCode(window.checkoutSteps.couponCode);
            this._super();
            console.info(this.formKey());
            this.initAdvanceCartData(customerData.get('cart')());

            $(document).on('click', '#block-discount-heading-mini', function (){
                $('#block-discount-cartmini-content').slideDown('slow');
                $('#minicart-content-wrapper .minicart-items-wrapper').addClass('deactive');
            });
            $(document).on('click', '.c-cart-discount__close', function (){
                $('#block-discount-cartmini-content').slideUp('slow');
                $('#minicart-content-wrapper .minicart-items-wrapper').removeClass('deactive');
            });
            return this;
        },

        initObservable: function () {
            var self = this;

            this._super();

            customerData.get('cart').subscribe(function (_cartData) {
                cartData = _cartData;
                self.isVisible(cartData);
                self.initAdvanceCartData(_cartData);
            });

            return this;
        },

        initAdvanceCartData: function (cartData) {
            if (cartData.hasOwnProperty('advancecart')) {
                this.couponCode(cartData.advancecart.coupon_code);
                //this.isApplied(!!cartData.advancecart.coupon_code);
                this.isLoggedIn(cartData.advancecart.isLoggedIn);
                this.quoteId(cartData.advancecart.quoteId);
                this.apiUrl(cartData.advancecart.apiUrl);
            }
        },

        handleMsg: function (type) {
            $('#slidingcart-coupon-form .message-' + type).show();

            this.hideMsgTimeout = setTimeout(function () {
                $('#slidingcart-coupon-form .message-' + type).hide('blind', {}, 500);
            }, 4000);
        },

        apply: function () {
            var self  = this,
                field = $('#slidingcart-coupon-code');
            clearTimeout(this.hideMsgTimeout);
            if (!field.val()) {
                field.focus().trigger('focusin');
                field.css('border-color', '#ed8380');

                return;
            }
            field.css('border-color', '');

            $.ajax({
                method: 'post',
                showLoader: true,
                url: this.submitUrl,
                data: $('#slidingcart-coupon-form').serialize(),
                success: function (response) {
                    var cartData = customerData.get('cart')();
                    cartData.couponCode = self.couponCode();
                    customerData.set('cart', cartData);
                    response.type === 'success' ? self.successMsg($t(response.message)) : self.errorMsg($t(response.message));
                    self.handleMsg(response.type);
                    self.handleSuccessApply(response);
                    self.isApplied(response.type === 'success');
                    self.shouldVisible(true);
                },
                error: function (response) {
                    self.handleErrorResponse(response);
                    self.handleMsg('error');
                }
            });
        },

        cancel: function () {
            var self = this;
            clearTimeout(this.hideMsgTimeout);

            $.ajax({
                method: 'post',
                showLoader: true,
                url: this.submitUrl,
                data: $('#slidingcart-coupon-form').serialize(),
                success: function (response) {
                    var cartData = customerData.get('cart')();
                    cartData.couponCode = '';
                    customerData.set('cart', cartData);
                    self.couponCode(null);

                    response.type === 'success' ? self.successMsg($t(response.message)) : self.errorMsg($t(response.message));

                    self.handleMsg(response.type);
                    self.isApplied(response.type === 'success' ? 0 : 1);
                    self.shouldVisible(true);
                    if (response.type === 'success') {
                        var field = $('#slidingcart-coupon-code');
                        field.removeAttr('disabled');
                        field.val('')
                    }
                    self.handleSuccessApply(response);
                console.info(self.isApplied());

                },
                error: function (response) {
                    self.handleErrorResponse(response);
                    self.handleMsg('error');
                }
            });
        },

        handleResponseMessage: function (data) {

        },

        handleSuccessApply: function (response) {
            if (response.type === 'success') {
                customerData.reload(['cart'], true);
            }
        },

        handleSuccessCancel: function () {
            customerData.reload(['cart'], true);
        },

        handleErrorResponse: function (response) {
            this.errorMsg(response.responseJSON ? response.responseJSON.message : '');
        },

        buildUrl: function (cartId, couponCode) {
            var url = guestUrl;

            if (this.isLoggedIn()) {
                url = customerUrl;
            }

            return this.apiUrl() + url.replace(':cartId', cartId).replace(':couponCode', couponCode);
        }
    });
});
