define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, customerData, url, confirmation,messageList, $t) {
    'use strict';
    return function (config) {
        var customerId = config.customerId;
        var accountdeactive = $('#account-deactive');
        var formData = accountdeactive.serializeArray();
        var formObj = {};
        $.each(formData, function (i, input) {
            formObj[input.name] = input.value;
        });
        $('#account-deactive-form').click(function (event) {

                event.preventDefault();

                confirmation({
                     content: $.mage.__('Do you wish to deactivate account?'),
                    actions: {
                        confirm: function () {
                            $.ajax({
                                type: "POST",
                                url: url.build('customer/deactive/formPost'),
                                showLoader: false,
                                data : formObj,
                                success: function (response) {
                                     if (response.success){
                                      location.reload();
                                        setTimeout(
                                            function () {
                                               
                                                customerData.set('messages', {
                                                    messages: [{
                                                        text: response.message,
                                                        type: 'success'
                                                    }]
                                                });
                                            },
                                            5000
                                        ) ;
                                     }
                                }
                            })
                        },
                        cancel: function () {},
                        always: function () {}
                    }
                });
                return false;
        })
    }
});
