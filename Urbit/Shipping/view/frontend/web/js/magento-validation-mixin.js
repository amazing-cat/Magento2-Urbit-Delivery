define([
    'jquery'
], function ($) {
    "use strict";

    /**
     * Add custom validation method for phone number
     *
     * In urbit system phone number must include country code
     */
    return function () {
        $.validator.addMethod(
            'urbitphonevalidation',
            function (value) {
                let urbitPhoneReg = /^\+[1-9]\d{6,}/;

                return urbitPhoneReg.test(value);
            },
            $.mage.__('Phone number must include country code')
        );
        $.validator.addMethod(
            'urbitgiftphonevalidation',
            function (value) {
                let urbitPhoneReg = /^\+[1-9]\d{6,}/;

                return !!$('#urbit-shipping-gift-checkbox').is(':checked') ? urbitPhoneReg.test(value) : true;
            },
            $.mage.__('Phone number must include country code')
        );
    }
});