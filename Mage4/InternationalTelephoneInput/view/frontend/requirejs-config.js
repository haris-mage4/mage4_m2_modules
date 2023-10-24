var config = {
    paths: {
        "intlTelInput": 'Mage4_InternationalTelephoneInput/js/intlTelInput',
        "intlTelInputUtils": 'Mage4_InternationalTelephoneInput/js/utils',
        "internationalTelephoneInput": 'Mage4_InternationalTelephoneInput/js/internationalTelephoneInput'
    },

    shim: {
        'intlTelInput': {
            'deps':['jquery', 'knockout']
        },
        'internationalTelephoneInput': {
            'deps':['jquery', 'intlTelInput']
        }
    }
};