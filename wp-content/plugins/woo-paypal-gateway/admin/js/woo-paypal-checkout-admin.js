(function ($) {
    'use strict';
    $(function () {
        $('#woocommerce_wpg_paypal_express_sandbox').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_express_sandbox_api_username").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_sandbox_api_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_sandbox_api_signature").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_api_username").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_api_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_api_signature").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_express_sandbox_api_username").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_sandbox_api_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_sandbox_api_signature").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_api_username").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_api_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_api_signature").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_paypal_express_sandbox').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_express_sandbox_api_username").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_sandbox_api_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_sandbox_api_signature").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_api_username").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_api_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_api_signature").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_express_sandbox_api_username").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_sandbox_api_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_sandbox_api_signature").closest('tr').hide();
                $("#woocommerce_wpg_paypal_express_api_username").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_api_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_express_api_signature").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_paypal_pro_testmode').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_pro_sandbox_api_username").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_sandbox_api_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_sandbox_api_signature").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_api_username").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_api_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_api_signature").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_pro_sandbox_api_username").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_sandbox_api_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_sandbox_api_signature").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_api_username").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_api_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_api_signature").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_braintree_sandbox').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_braintree_sandbox_public_key").closest('tr').show();
                $("#woocommerce_wpg_braintree_sandbox_private_key").closest('tr').show();
                $("#woocommerce_wpg_braintree_sandbox_merchant_id").closest('tr').show();
                $("#woocommerce_wpg_braintree_live_public_key").closest('tr').hide();
                $("#woocommerce_wpg_braintree_live_private_key").closest('tr').hide();
                $("#woocommerce_wpg_braintree_live_merchant_id").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_braintree_sandbox_public_key").closest('tr').hide();
                $("#woocommerce_wpg_braintree_sandbox_private_key").closest('tr').hide();
                $("#woocommerce_wpg_braintree_sandbox_merchant_id").closest('tr').hide();
                $("#woocommerce_wpg_braintree_live_public_key").closest('tr').show();
                $("#woocommerce_wpg_braintree_live_private_key").closest('tr').show();
                $("#woocommerce_wpg_braintree_live_merchant_id").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_paypal_advanced_testmode').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_partner").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_vendor").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_user").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_paypal_partner").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_paypal_vendor").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_paypal_user").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_paypal_password").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_partner").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_vendor").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_user").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_sandbox_paypal_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_advanced_paypal_partner").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_paypal_vendor").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_paypal_user").closest('tr').show();
                $("#woocommerce_wpg_paypal_advanced_paypal_password").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_paypal_pro_payflow_testmode').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_partner").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_vendor").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_user").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_password").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_partner").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_vendor").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_user").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_password").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_partner").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_vendor").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_user").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_sandbox_paypal_password").closest('tr').hide();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_partner").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_vendor").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_user").closest('tr').show();
                $("#woocommerce_wpg_paypal_pro_payflow_paypal_password").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_paypal_rest_sandbox').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_rest_rest_client_id_sandbox").closest('tr').show();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_sandbox").closest('tr').show();
                $("#woocommerce_wpg_paypal_rest_rest_client_id_live").closest('tr').hide();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_live").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_rest_rest_client_id_sandbox").closest('tr').hide();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_sandbox").closest('tr').hide();
                $("#woocommerce_wpg_paypal_rest_rest_client_id_live").closest('tr').show();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_live").closest('tr').show();
            }
        }).change();
        $('#woocommerce_wpg_paypal_checkout_enable_advanced_card_payments').change(function () {
            if ($(this).is(':checked')) {
                $("#woocommerce_wpg_paypal_rest_rest_client_id_sandbox").closest('tr').show();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_sandbox").closest('tr').show();
                $("#woocommerce_wpg_paypal_rest_rest_client_id_live").closest('tr').hide();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_live").closest('tr').hide();
            } else {
                $("#woocommerce_wpg_paypal_rest_rest_client_id_sandbox").closest('tr').hide();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_sandbox").closest('tr').hide();
                $("#woocommerce_wpg_paypal_rest_rest_client_id_live").closest('tr').show();
                $("#woocommerce_wpg_paypal_rest_rest_secret_id_live").closest('tr').show();
            }
        }).change();
    });
})(jQuery);
