define([
    'jquery',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'mage/validation',
], function (
    $,
    setShippingInformationAction,
    stepNavigator
) {
    'use strict';

    return function (Component) {
        let AJAX_PASS = false;
        let RESULT_CODE = null;

        return Component.extend({
            /**
             * Override default address validation function from Magento_Checkout/js/view/shipping
             *
             * @returns boolean
             */
            validateShippingInformation: function () {
                let defaultValidatorResult = this._super();

                return defaultValidatorResult ? this.urbitValidation() : false;
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if (this.validateShippingInformation()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },

            /**
             * Validation for urbit delivery form's fields
             *
             * @returns boolean
             */
            urbitValidation: function () {
                let self = this;
                const isPass = AJAX_PASS;

                this.showErrorMessage('#urbit_address_validation_error_message', false);

                if (!AJAX_PASS) {
                    this.validateAddress(() => {
                        AJAX_PASS = false;
                    }, (result) => {
                        if (result.code) {
                            AJAX_PASS = true;
                            RESULT_CODE = result.code;
                            self.setShippingInformation();
                        }
                    });
                } else {
                    AJAX_PASS = false;
                }

                return isPass && this.validateForm('#urbit-shipping-form') && this.validateDeliveryTime() && this.checkAddressResponse();
            },

            /**
             * Check API response code
             *
             * Show error message if code !== 200
             *
             * @returns {boolean}
             */
            checkAddressResponse: function () {
                if (RESULT_CODE === '200') {
                    this.showErrorMessage('#urbit_address_validation_error_message', false);

                    return true;
                } else {
                    this.showErrorMessage('#urbit_address_validation_error_message', true);

                    return false;
                }
            },

            /**
             * Form validation function
             *
             * @param formId
             *
             * @returns jQuery
             */
            validateForm: function (formId) {
                return $(formId).validation() && $(formId).validation('isValid');
            },

            /**
             * Validate specific time
             *
             * @returns {boolean}
             */
            validateDeliveryTime: function () {
                if ($('#urbit-shipping-specific-time-radio').is(':checked')) {
                    let specificDay = $("#urbit_specific_time_date").val();
                    let specificHour = $("#urbit_specific_time_hour").val();
                    let specificMinute = $("#urbit_specific_time_minute").val();

                    if (specificDay === "" || specificHour === "" || specificMinute === "") {
                        this.showErrorMessage('#urbit_date_validation_error', true);

                        return false;
                    }
                }

                this.showErrorMessage('#urbit_date_validation_error', false);

                return true;
            },

            /**
             * Address validation
             *
             * Send chosen city, street, and postcode to Validation controller (DeliveryAddress action)
             *
             * @returns {boolean}
             */
            validateAddress: function (beforeCallback, afterCallback) {
                beforeCallback();

                $.ajax({
                    url: '/urbit_shipping/validation/deliveryaddress',
                    type: 'post',
                    data: {
                        'street': $('#urbit_shipping_street').val(),
                        'postcode': $('#urbit_shipping_postcode').val(),
                        'city': $('#urbit_shipping_city').val()
                    },
                    success: afterCallback,
                });
            },

            /**
             * Show/hide error message with id == errorMsgId
             *
             * @param errorMsgId
             * @param needShow
             */
            showErrorMessage: function (errorMsgId, needShow) {
                needShow ? $(errorMsgId).show() : $(errorMsgId).hide();
            },
        });
    }
});