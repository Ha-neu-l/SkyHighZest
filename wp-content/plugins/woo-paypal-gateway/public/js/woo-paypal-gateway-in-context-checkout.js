(function($, window, document) {
    window.paypalCheckoutReady = function() {
        document.querySelectorAll('.wpg_express_checkout_paypal_button').forEach(function(selector) {
            paypal.checkout.setup(wpg_in_content_param.MerchantID, {
                container: selector,
                environment: 'sandbox',
                locale: wpg_in_content_param.LOCALE,
                buttons: [{
                    container: selector,
                    type: 'checkout',
                    size: wpg_in_content_param.SIZE,
                    shape: wpg_in_content_param.SHAPE,
                    color: wpg_in_content_param.COLOR
                }],
                condition: function () {
                    if ($('.wpg_express_checkout_paypal_button').hasClass("disabled")) {
                        return false;
                    } else {
                        return true;
                    }
                },
                click: function(event) {
                    event.preventDefault();
                    if (wpg_in_content_param.enable_in_context_checkout_flow === 'yes') {
                        paypal.checkout.initXO()
                    } else {
                        $(selector).block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: .6
                            }
                        })
                    }
                    var get_attributes = function() {
                        var select = $('.variations_form').find('.variations select'),
                            data = {},
                            count = 0,
                            chosen = 0;
                        select.each(function() {
                            var attribute_name = $(this).data('attribute_name') || $(this).attr('name');
                            var value = $(this).val() || '';
                            if (value.length > 0) {
                                chosen++
                            }
                            count++;
                            data[attribute_name] = value
                        });
                        return {
                            'count': count,
                            'chosenCount': chosen,
                            'data': data
                        }
                    };
                    var postdata = {
                        'nonce': wpg_in_content_param.GENERATE_NONCE,
                        'qty': $('.quantity .qty').val(),
                        'attributes': $('.variations_form').length ? get_attributes().data : [],
                        'wc-paypal_express-new-payment-method': $("#wc-paypal_express-new-payment-method").is(':checked'),
                        'is_add_to_cart': wpg_in_content_param.IS_PRODUCT,
                        'product_id': wpg_in_content_param.POST_ID
                    };
                    $.ajax({
                        type: 'POST',
                        data: postdata,
                        url: wpg_in_content_param.add_to_cart_ajaxurl,
                        success: function(data) {
                            if (wpg_in_content_param.enable_in_context_checkout_flow === 'yes') {
                                paypal.checkout.startFlow(wpg_in_content_param.CREATE_PAYMENT_URL)
                            } else {
                                window.location.replace(wpg_in_content_param.CREATE_PAYMENT_URL)
                            }
                        },
                        error: function(e) {
                            if (wpg_in_content_param.enable_in_context_checkout_flow === 'yes') {
                                paypal.checkout.closeFlow()
                            }
                        }
                    })
                }
            })
        })
    };
    window.paypalCheckoutReady = function() {
        document.querySelectorAll('.wpg_express_checkout_paypal_cc_button').forEach(function(selector) {
            paypal.checkout.setup(wpg_in_content_param.MerchantID, {
                container: selector,
                environment: 'sandbox',
                locale: wpg_in_content_param.LOCALE,
                buttons: [{
                    container: selector,
                    type: 'credit',
                    size: wpg_in_content_param.SIZE,
                    shape: wpg_in_content_param.SHAPE
                }],
                condition: function () {
                    if ($('.wpg_express_checkout_paypal_cc_button').hasClass("disabled")) {
                        return false;
                    } else {
                        return true;
                    }
                },
                click: function(event) {
                    event.preventDefault();
                    if (wpg_in_content_param.enable_in_context_checkout_flow === 'yes') {
                        paypal.checkout.initXO()
                    } else {
                        $(selector).block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: .6
                            }
                        })
                    }
                    var get_attributes = function() {
                        var select = $('.variations_form').find('.variations select'),
                            data = {},
                            count = 0,
                            chosen = 0;
                        select.each(function() {
                            var attribute_name = $(this).data('attribute_name') || $(this).attr('name');
                            var value = $(this).val() || '';
                            if (value.length > 0) {
                                chosen++
                            }
                            count++;
                            data[attribute_name] = value
                        });
                        return {
                            'count': count,
                            'chosenCount': chosen,
                            'data': data
                        }
                    };
                    var postdata = {
                        'nonce': wpg_in_content_param.GENERATE_NONCE,
                        'qty': $('.quantity .qty').val(),
                        'attributes': $('.variations_form').length ? get_attributes().data : [],
                        'wc-paypal_express-new-payment-method': $("#wc-paypal_express-new-payment-method").is(':checked'),
                        'is_add_to_cart': wpg_in_content_param.IS_PRODUCT,
                        'product_id': wpg_in_content_param.POST_ID
                    };
                    $.ajax({
                        type: 'POST',
                        data: postdata,
                        url: wpg_in_content_param.add_to_cart_ajaxurl,
                        success: function(data) {
                            if (wpg_in_content_param.enable_in_context_checkout_flow === 'yes') {
                                paypal.checkout.startFlow(wpg_in_content_param.CC_CREATE_PAYMENT_URL)
                            } else {
                                window.location.replace(wpg_in_content_param.CC_CREATE_PAYMENT_URL)
                            }
                        },
                        error: function(e) {
                            if (wpg_in_content_param.enable_in_context_checkout_flow === 'yes') {
                                paypal.checkout.closeFlow()
                            }
                        }
                    })
                }
            })
        })
    }
})(jQuery, window, document)