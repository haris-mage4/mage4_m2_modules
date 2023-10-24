define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    "Magento_Ui/js/modal/modal",
    'jquery/validate'
], function ($, customerData, url, confirmation, messageList, $t, modal) {
    'use strict';
    var options = {
        type: 'popup',
        responsive: true,
        //title: 'Have a Question?',
        // buttons: [{
        //     text: $.mage.__('Ok'),
        //     class: 'testttt',
        //     click: function () {
        //         this.closeModal();
        //     }
        // }]
    };

    var popup = modal(options, $('#modal'));
    $("#button").click(function () {
        $('#modal').modal('openModal');
    });

    var form = $('#form-askquestion');

    $(form).submit(function (event) {
        event.preventDefault();
        var formData = form.serializeArray();
        var formObj = {};
        $.each(formData, function (i, input) {
            formObj[input.name] = input.value;
        });
        var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
        var pattern2 = /^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/;
        if (!pattern.test(formObj.email) || !pattern2.test(formObj.phonenumber) || formObj.email === "" || formObj.customername === "" || formObj.phonenumber === "" || formObj.question === "") {
            return;
        }




        $.ajax({
            type: "POST",
            url: url.build('ask/productquestion/submit'),
            showLoader: false,
            data: formObj,
            success: function (response) {
                if (response.success) {
                    $('#modal').modal('closeModal');
                    setTimeout(
                        function () {
                            form.trigger("reset");
                            customerData.set('messages', {
                                messages: [{
                                    text: response.message,
                                    type: 'success'
                                }]
                            });
                        },
                        5000
                    );
                }
            },
            error: function (res) {
                console.log(res);
            }
        });
    });
});

