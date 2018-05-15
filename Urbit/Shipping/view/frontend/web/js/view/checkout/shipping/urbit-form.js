define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'mage/validation'
], function ($, ko, Component, quote) {
    'use strict';

    let self;

    return Component.extend({
        defaults: {
            template: 'Urbit_Shipping/checkout/shipping/urbit-form',
            selectedSpecificDay: '',
            selectedSpecificHour: '',
            selectedSpecificMinute: '',
        },

        /**
         * Options for specific time Day selectox
         * @type {array} specificTimeDayOptions
         */
        specificTimeDayOptions: ko.observableArray([{label: 'Select Date', value: null}]),

        /**
         * Options for specific time Hour selectbox
         * @type {array} specificTimeHourOptions
         */
        specificTimeHourOptions: ko.observableArray([{label: 'HH', value: null}]),

        /**
         * Options for specific time Minute selectbox
         * @type {array} specificTimeMinuteOptions
         */
        specificTimeMinuteOptions: ko.observableArray([{label: 'MM', value: null}]),

        /**
         * Is specific time chosen
         * @type {boolean} isSpecificTime
         */
        isSpecificTime: ko.observable(false),

        /**
         * Is gift checkbox checked
         * @type {boolean} isGift
         */
        isGift: ko.observable(false),

        /**
         * Is specific time day selectbox disabled
         * @type {boolean} IsDaysDisable
         */
        IsDaysDisable: ko.observable(true),

        /**
         * Is specific time hour selectbox disabled
         * @type {boolean} IsHoursDisable
         */
        IsHoursDisable: ko.observable(true),

        /**
         * Is specific time minute selectbox disabled
         * @type {boolean} IsMinutesDisable
         */
        IsMinutesDisable: ko.observable(true),

        /**
         * Selected shipping method on magento2 checkout
         * @type {string}
         */
        selectedMethod: ko.computed(
            function () {
                let method = quote.shippingMethod();

                return method != null ? method.carrier_code + '_' + method.method_code : null;
            },
            this
        ),

        /**
         * Delivery address inputs ids and values
         *
         * @type {array}
         */
        deliveryAddress: [
            {urbit_input_id: '#urbit_shipping_firstname', value: ''},
            {urbit_input_id: '#urbit_shipping_lastname', value: ''},
            {urbit_input_id: '#urbit_shipping_postcode', value: ''},
            {urbit_input_id: '#urbit_shipping_postcode', value: ''},
            {urbit_input_id: '#urbit_shipping_street', value: ''},
            {urbit_input_id: '#urbit_shipping_city', value: ''},
        ],

        /**
         * Component initialize function
         */
        initialize: function () {
            self = this;
            this._super();

            this.getPossibleDays();
            this.initEventHandlers();
            this.initSubscribers();
        },

        /**
         * Initialize event handlers
         */
        initEventHandlers: function () {
            /**
             * Change urbit shipping inputs, when magento shipping inputs have been changed
             */
            let inputsArray = [
                {magento_input_name: 'postcode', urbit_input_id: 'urbit_shipping_postcode'},
                {magento_input_name: 'city', urbit_input_id: 'urbit_shipping_city'},
                {magento_input_name: 'firstname', urbit_input_id: 'urbit_shipping_firstname'},
                {magento_input_name: 'lastname', urbit_input_id: 'urbit_shipping_lastname'},
                {magento_input_name: 'telephone', urbit_input_id: 'urbit_shipping_telephone'},
                {magento_input_name: 'username', urbit_input_id: 'urbit_shipping_email'}
            ];

            $.each(inputsArray, function (key, value) {
                $(document).on('change', '[name=' + value.magento_input_name + ']', function () {
                    $('#' + value.urbit_input_id).val($(this).val()).change();
                });
            });

            $(document).on('change', '[name=street\\[0\\]]', function () {
                $('#urbit_shipping_street').val($(this).val()).change();
            });
        },

        /**
         * Initialize subscribers
         */
        initSubscribers: function () {
            /**
             * Gift checkbox checked event handler
             *
             * checked => save delivery inputs values to deliveryAddress array and clear inputs
             * unchecked => fill delivery inputs from deliveryAddress array
             */
            self.isGift.subscribe(function (newValue) {
                if (newValue) {
                    self.saveDeliveryInputsValues();
                    self.clearDeliveryInputs();
                } else {
                    self.fillDeliveryInputsFromSavedObject();
                }
                $("#urbit_gift_telephone ").stop(true, true).slideToggle('fast');
            }, this);
        },

        /**
         * Save delivery inputs values to deliveryAddress array
         */
        saveDeliveryInputsValues: function () {
            $.each(self.deliveryAddress, function (key, input) {
                input.value = $(input.urbit_input_id).val()
            });
        },

        /**
         * Fill delivery inputs from deliveryAddress array
         */
        fillDeliveryInputsFromSavedObject: function () {
            $.each(self.deliveryAddress, function (key, input) {
                $(input.urbit_input_id).val(input.value)
            });
        },

        /**
         * Clear delivery inputs which are contained in deliveryAddress array
         */
        clearDeliveryInputs: function () {
            $.each(self.deliveryAddress, function (key, input) {
                $(input.urbit_input_id).val("")
            });
        },

        /**
         * Fill urbit delivery inputs by current authorized user's address information
         *
         * called by urbit-form afterRender event
         */
        fillShippingInputs: function () {
            let customerData = window.checkoutConfig.customerData;

            let currentEmailValue = $('[name=username]').val();

            if (currentEmailValue) {
                $('#urbit_shipping_email').val(currentEmailValue).change();
            }

            if (!Array.isArray(customerData) && !$.isEmptyObject({customerData})) {
                let addressData = window.checkoutConfig.customerData.addresses[0];

                // customerInfo: {urbit_shipping_input_id : value}
                let valuesArray = {
                    'urbit_shipping_firstname': addressData.firstname,
                    'urbit_shipping_lastname': addressData.lastname,
                    'urbit_shipping_postcode': addressData.postcode,
                    'urbit_shipping_street': addressData.street[0],
                    'urbit_shipping_city': addressData.city,
                    'urbit_shipping_telephone': addressData.telephone,
                    'urbit_shipping_email': customerData.email
                };

                $.each(valuesArray, function (urbitInputId, value) {
                    $('#' + urbitInputId).val(value).change();
                });

                self.validateForm('#urbit-shipping-form');
            }
        },

        /**
         * Disable Now delivery time if now time is not in possible range
         */
        checkNowPossibility: function () {
            $.ajax({
                url: '/urbit_shipping/date/nowpossibility',
                type: 'post',
                success: function (isShowNow) {
                    if (!isShowNow) {
                        $('#urbit-shipping-now-radio').prop('disabled', true);
                        $('#urbit-shipping-now-label').css('cursor', 'default').fadeTo("slow", 0.5);
                        $('#urbit-shipping-specific-time-radio').prop('checked', true);
                        self.isSpecificTime(true);
                    }
                }
            });
        },

        /**
         * Return possible delivery days
         *
         * Ajax call to Date controller (DayOptions action)
         */
        getPossibleDays: function () {
            $.ajax({
                url: '/urbit_shipping/date/dayoptions',
                type: 'post',
                success: function (possibleDates) {
                    if (possibleDates) {
                        let optionsArray = [{label: 'Select Date', value: null}];

                        jQuery.each(possibleDates, function (i, val) {
                            optionsArray.push({label: val.label, value: val.date});
                        });

                        self.specificTimeDayOptions(optionsArray);
                        self.IsDaysDisable(false);
                    }

                    self.checkNowPossibility();
                }
            });
        },

        /**
         * Return possible delivery hours for selected day
         *
         * Ajax call to Date controller (HourOptions action)
         * Function is called from template
         */
        getPossibleHours: function () {
            if (self.selectedSpecificDay) {
                self.IsHoursDisable(true);
                self.IsMinutesDisable(true);

                $.ajax({
                    url: '/urbit_shipping/date/houroptions',
                    type: 'post',
                    data: {
                        'selected_date': self.selectedSpecificDay,
                    },
                    success: function (possibleHours) {
                        if (possibleHours) {
                            let optionsArray = [{label: 'HH', value: null}];

                            jQuery.each(possibleHours, function (i, val) {
                                optionsArray.push({label: val, value: val});
                            });

                            self.specificTimeHourOptions(optionsArray);
                            self.IsHoursDisable(false);

                            let hourValue = $('#urbit_specific_time_hour').val();

                            if (hourValue) {
                                self.getPossibleMinutes();
                            }
                        }
                    }
                });
            }
        },

        /**
         * Return possible delivery minutes for selected hour
         *
         * Ajax call to Date controller (MinuteOptions action)
         */
        getPossibleMinutes: function () {
            if (self.selectedSpecificHour) {
                self.IsMinutesDisable(true);

                $.ajax({
                    url: '/urbit_shipping/date/minuteoptions',
                    type: 'post',
                    data: {
                        'selected_date': self.selectedSpecificDay,
                        'selected_hour': self.selectedSpecificHour
                    },
                    success: function (possibleMinutes) {
                        if (possibleMinutes) {
                            let optionsArray = [{label: 'MM', value: null}];

                            jQuery.each(possibleMinutes, function (i, val) {
                                optionsArray.push({label: val, value: val});
                            });

                            self.specificTimeMinuteOptions(optionsArray);
                            self.IsMinutesDisable(false);
                        }
                    }
                });
            }
        },

        /**
         * Address validation
         *
         * Send chosen city, street, and postcode to Validation controller (DeliveryAddress action)
         */
        validateAddress: function () {
            $.ajax({
                url: '/urbit_shipping/validation/deliveryaddress',
                type: 'post',
                data: {
                    'street': $('#urbit_shipping_street').val(),
                    'postcode': $('#urbit_shipping_postcode').val(),
                    'city': $('#urbit_shipping_city').val()
                },
                success: function (response) {
                    self.showErrorMessage('#urbit_address_validation_error_message', response.code !== '200');
                }
            });
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
                    self.showErrorMessage('#urbit_date_validation_error', true);

                    return false;
                }
            }

            self.showErrorMessage('#urbit_date_validation_error', false);

            return true;
        },

        /**
         * Form validation function
         *
         * @param formId
         * @returns {jQuery}
         */
        validateForm: function (formId) {
            return $(formId).validation() && $(formId).validation('isValid');
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
});