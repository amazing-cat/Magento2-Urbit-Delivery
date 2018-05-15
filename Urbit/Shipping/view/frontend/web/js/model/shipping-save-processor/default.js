define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function (
        $,
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction
    ) {
        'use strict';

        return {
            /**
             * Override saveShippingInformation method
             *
             * Save data from urbit form to extension_attributes
             */
            saveShippingInformation: function () {
                let payload;

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
                        extension_attributes: {
                            urbit_specific_time_date: $('#urbit-shipping-now-radio').is(':checked') ?
                                'now' : $('#urbit_specific_time_date').val(),
                            urbit_specific_time_hour: $('#urbit_specific_time_hour').val(),
                            urbit_specific_time_minute: $('#urbit_specific_time_minute').val(),
                            urbit_shipping_firstname: $('#urbit_shipping_firstname').val(),
                            urbit_shipping_lastname: $('#urbit_shipping_lastname').val(),
                            urbit_shipping_postcode: $('#urbit_shipping_postcode').val(),
                            urbit_shipping_street: $('#urbit_shipping_street').val(),
                            urbit_shipping_city: $('#urbit_shipping_city').val(),
                            urbit_shipping_telephone: !!$('#urbit-shipping-gift-checkbox').is(':checked') ?
                                $('#urbit_gift_telephone').val() : $('#urbit_shipping_telephone').val(),
                            urbit_shipping_email: $('#urbit_shipping_email').val(),
                            urbit_message: $('#urbit_message_textarea').val()
                        }
                    }
                };

                fullScreenLoader.startLoader();

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        };
    }
);