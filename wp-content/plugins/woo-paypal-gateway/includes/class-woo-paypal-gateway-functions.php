<?php

function wpg_get_posted_card($payment_method) {
    $card_number = isset($_POST[$payment_method . '-card-number']) ? wc_clean($_POST[$payment_method . '-card-number']) : '';
    $card_cvc = isset($_POST[$payment_method . '-card-cvc']) ? wc_clean($_POST[$payment_method . '-card-cvc']) : '';
    $card_expiry = isset($_POST[$payment_method . '-card-expiry']) ? wc_clean($_POST[$payment_method . '-card-expiry']) : '';
    $card_number = str_replace(array(' ', '-'), '', $card_number);
    $card_expiry = array_map('trim', explode('/', $card_expiry));
    $card_exp_month = str_pad($card_expiry[0], 2, "0", STR_PAD_LEFT);
    $card_exp_year = isset($card_expiry[1]) ? $card_expiry[1] : '';
    if (strlen($card_exp_year) == 2) {
        $card_exp_year += 2000;
    }
    $first_four = substr($card_number, 0, 4);
    return (object) array(
                'number' => $card_number,
                'type' => wpg_card_type_from_account_number($first_four),
                'cvc' => $card_cvc,
                'exp_month' => $card_exp_month,
                'exp_year' => $card_exp_year,
    );
}

function is_wpg_credit_supported() {
    if (substr(get_option("woocommerce_default_country"), 0, 2) == 'US' || substr(get_option("woocommerce_default_country"), 0, 2) == 'GB') {
        return true;
    } else {
        return false;
    }
}

function wpg_card_type_from_account_number($account_number) {
    $types = array(
        'visa' => '/^4/',
        'mastercard' => '/^5[1-5]/',
        'amex' => '/^3[47]/',
        'discover' => '/^(6011|65|64[4-9]|622)/',
        'diners' => '/^(36|38|30[0-5])/',
        'jcb' => '/^35/',
        'maestro' => '/^(5018|5020|5038|6304|6759|676[1-3])/',
        'laser' => '/^(6706|6771|6709)/',
    );
    foreach ($types as $type => $pattern) {
        if (1 === preg_match($pattern, $account_number)) {
            return $type;
        }
    }
    return null;
}

function wpg_round($price, $order) {
    $precision = 2;
    if (!wpg_currency_has_decimals($order->get_currency())) {
        $precision = 0;
    }
    return round($price, $precision);
}

function wpg_currency_has_decimals($currency) {
    if (in_array($currency, array('HUF', 'JPY', 'TWD'))) {
        return false;
    }
    return true;
}

function wpg_set_session($key, $value) {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }
    $wpg_session = WC()->session->get('wpg_session');
    if (!is_array($wpg_session)) {
        $wpg_session = array();
    }
    $wpg_session[$key] = $value;
    WC()->session->set('wpg_session', $wpg_session);
}

function wpg_get_session($key) {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }
    $wpg_session = WC()->session->get('wpg_session');
    if (!empty($wpg_session[$key])) {
        return $wpg_session[$key];
    }
    return false;
}

function wpg_unset_session($key) {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }
    $wpg_session = WC()->session->get('wpg_session');
    if (!empty($wpg_session[$key])) {
        unset($wpg_session[$key]);
        WC()->session->set('wpg_session', $wpg_session);
    }
}

function is_wpg_express_checkout_ready_to_capture() {
    $TOKEN = wpg_get_session('TOKEN');
    $PAYERID = wpg_get_session('PAYERID');
    if (!empty($TOKEN) && !empty($PAYERID)) {
        return true;
    } else {
        return false;
    }
}

function is_payment_method_saved() {
    if (is_user_logged_in()) {
        $tokens = WC_Payment_Tokens::get_customer_tokens(get_current_user_id(), 'wpg_paypal_express');
        if (!empty($tokens)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function wpg_maybe_clear_session_data() {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }
    WC()->session->set('wpg_session', '');
}

function wpg_get_option($getway_name, $key, $default = false) {
    if (!empty($getway_name)) {
        $gateway_key = 'woocommerce_' . $getway_name . '_settings';
        $setting_value = get_option($gateway_key);
        if (!empty($setting_value)) {
            $value = !empty($setting_value[$key]) ? $setting_value[$key] : $default;
            return $value;
        }
    }
    return false;
}

function is_cart_contains_pre_order() {
    if (class_exists('WC_Pre_Orders_Cart')) {
        if (WC_Pre_Orders_Cart::cart_contains_pre_order()) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function is_pre_order_activated() {
    if (class_exists('WC_Pre_Orders_Order')) {
        return true;
    } else {
        return false;
    }
}

function is_cart_contains_subscription() {
    $cart_contains_subscription = false;
    if (class_exists('WC_Subscriptions_Order') && class_exists('WC_Subscriptions_Cart')) {
        $cart_contains_subscription = WC_Subscriptions_Cart::cart_contains_subscription();
    }
    return $cart_contains_subscription;
}

function is_subscription_activated() {
    if (class_exists('WC_Subscriptions_Order') && function_exists('wcs_create_renewal_order')) {
        return true;
    } else {
        return false;
    }
}

function wpg_is_token_exist($gateway_id, $user_id, $token) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_payment_tokens WHERE gateway_id = %s AND user_id = %s AND token = %s", $gateway_id, $user_id, $token));
}

function wpg_has_active_session() {
    if (!WC()->session) {
        return false;
    }
    $wpg_order_id = WC()->session->get('wpg_order_id');
    if (isset($wpg_order_id) && !empty($wpg_order_id)) {
        return true;
    }
}

function wpg_clear_session_data() {
    if (!WC()->session) {
        return false;
    }
    WC()->session->set('wpg_order_details', null);
    WC()->session->set('wpg_order_id', null);
    unset(WC()->session->wpg_order_id);
    unset(WC()->session->wpg_order_details);
}

function wpg_number_format($price) {
    $decimals = 2;

    if (!wpg_currency_has_decimals(get_woocommerce_currency())) {
        $decimals = 0;
    }

    return number_format($price, $decimals, '.', '');
}

function wpg_remove_empty_key($data) {
    $original = $data;
    $data = array_filter($data);
    $data = array_map(function ($e) {
        return is_array($e) ? wpg_remove_empty_key($e) : $e;
    }, $data);
    return $original === $data ? $data : wpg_remove_empty_key($data);
}

function wpg_limit_length($string, $limit = 127) {
    $str_limit = $limit - 3;
    if (function_exists('mb_strimwidth')) {
        if (mb_strlen($string) > $limit) {
            $string = mb_strimwidth($string, 0, $str_limit) . '...';
        }
    } else {
        if (strlen($string) > $limit) {
            $string = substr($string, 0, $str_limit) . '...';
        }
    }
    return $string;
}
