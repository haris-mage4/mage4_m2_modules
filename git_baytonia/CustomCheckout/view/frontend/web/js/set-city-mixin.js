define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
    $(document).ready(function () {
        $(document).on('change', 'select[name="region_id"]', function() {
            if (!!$($('select[name="region_id"] option:selected')[0]).val()) {
                $('input[name="city"]').val($('select[name="region_id"] option:selected')[0].text);
                $('input[name="city"]').keyup();
            }
        });
        $(document).on('keyup', 'input[name="region"]', function() {
            $('input[name="city"]').val($('input[name="region"]').val());
            $('input[name="city"]').keyup();
        });
    });
    
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            return originalAction();
        });
    }
});