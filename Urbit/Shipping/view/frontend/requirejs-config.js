var config = {
    "map": {
        "*": {
            'Magento_Checkout/js/model/shipping-save-processor/default': 'Urbit_Shipping/js/model/shipping-save-processor/default',
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Urbit_Shipping/js/shipping-validation-mixin': true
            },
            'mage/validation': {
                'Urbit_Shipping/js/magento-validation-mixin': true
            }
        }
    }
};