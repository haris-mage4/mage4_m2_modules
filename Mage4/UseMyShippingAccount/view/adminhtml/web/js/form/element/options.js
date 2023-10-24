define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function ($,_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {

            var option_values = uiRegistry.get('index = option_values');
            var default_value = uiRegistry.get('index = option_default_value');

            if (value === 'select' || value === 'multiselect') {
                option_values.show();
                default_value.hide();
            } else {
                option_values.hide();
                default_value.show();
            }

            return this._super();
        }
    });
});
