define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Customer/js/model/customer',
        'mage/url',
        'Magento_Checkout/js/action/get-totals'
    ],
    function (
        $,
        ko,
        Component,
        _,
        customerData,
        url,
        getTotalsAction
    ) {
        'use strict';
        /**
         * check-login - is the name of the component's .html template
         */
        return Component.extend({
            defaults: {
                template: 'InformaticsCommerce_TaxExempt/test/tax',
                tax_exempt_options: [
                    { value: 'yes', label: 'Yes' },
                    { value: 'no', label: 'No' }
                ],
                selectedOption: ko.observable('no')
            },

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                this.selectedOption.subscribe(this.onChangeSelectedOption.bind(this));
                return this;
            },

            onChangeSelectedOption: function (newValue) {
                if (newValue === 'yes'){
                    $('.tax_exempt_number').removeClass('hide');
                }else if (newValue === 'no'){
                    $('.tax_exempt_number').addClass('hide');
                }
            },
            onSubmit: function (event){
               var form = $('#tax_exempt_form')[0];
                //event.preventDefault();
                var formData = new FormData(form);
                const formObject = Object.fromEntries(formData.entries());
               // var formData = form.serializeArray();
               // var formObj = {};
                // $.each(formData, function (i, input) {
                //     formObj[input.name] = input.value;
                // });
                if($('#tax_exempt_number').val() === ''){
                    $('.mage-error.tax_exempt_number').show();
                }else {
                    $('.mage-error.tax_exempt_number').hide();
                }

                if($('#tax_exempt_file').val() === ''){
                    $('.mage-error.tax_exempt_file').show();
                }else {
                    $('.mage-error.tax_exempt_file').hide();
                }
                $.ajax(
                   {
                       type: "POST",
                       url: url.build('tax/taxexempt/apply'),
                       showLoader: true,
                       data: formData,
                       dataType: 'json',
                       processData: false, contentType: false,
                       success: function (response) {
                           if (response.success) {
                               var deferred = $.Deferred();
                               getTotalsAction([], deferred);
                               $('#tax_error').hide();
                               $('#tax_error').html('');
                           }else {
                               $('#tax_error').show();
                               $('#tax_error').html(response.message);
                           }
                       },
                       error: function (res) {
                           console.log(res);
                       }
                   }
               );
            },
            isCustomerLoggedIn: function () {
                return customerData.isLoggedIn();
               // return  customerData.get('customer').isCustomerLoggedIn();
            }
        });
    }
);
