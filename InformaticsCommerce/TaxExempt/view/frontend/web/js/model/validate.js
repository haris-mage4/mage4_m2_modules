define(
    [
        'jquery',
        'mage/validation'
    ],
    function ($) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                let selectedOption = $("input[name='tax_exempt_options']:checked").val();
                if (selectedOption === 'yes'){
                    if ($('#tax_exempt_number').val()  === '' || $('#tax_exempt_file').val() === ''){
                        $('.mage-error').show()
                        return false;
                    }else {
                        $('.mage-error').hide();
                        return true;
                    }
                }else if(selectedOption === 'no'){
                    return true
                }
            }
        };
    }
);
