define([
    'jquery'
], function($) {
    'use strict';

    var mixin = {
        getQnt: function (item) {
            return item.qty;
        }
    };
    return function(target) {
        return target.extend(mixin);
    }
});
