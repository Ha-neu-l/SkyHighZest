<?php

if (!defined('ABSPATH')) {
    exit;
}

class Woo_PayPal_Gateway_Express_Checkout_NVP extends WC_Payment_Gateway {

    public static $log_enabled = false;
    public static $log = false;
    public $card_data;
    public $rest_api_handler;
    public $woocommerce_paypal_supported_currencies;
    public $sandbox;
    public $debug;
    public $seller_protection;
    public $enable_in_context_checkout_flow;
    public $paypal_account_optional;
    public $credit_enabled;
    public $username;
    public $password;
    public $signature;
    public $API_Endpoint;
    public $PAYPAL_URL;
    public $invoice_prefix;

    public function __construct() {
        try {
            $this->id = 'wpg_paypal_express';
            $this->method_title = __('PayPal Express Checkout (deprecated)', 'woo-paypal-gateway');
            $this->woocommerce_paypal_supported_currencies = array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP');
            $this->method_description = __('Increase sales by using PayPal express checkout to accept payments.', 'woo-paypal-gateway');
            $this->has_fields = false;
            $this->supports = array(
                'products',
                'refunds',
                'subscriptions',
                'subscription_cancellation',
                'subscription_reactivation',
                'subscription_suspension',
                'subscription_amount_changes',
                'subscription_payment_method_change',
                'subscription_payment_method_change_customer',
                'subscription_payment_method_change_admin',
                'subscription_date_changes',
                'multiple_subscriptions',
                'add_payment_method',
                'tokenization',
                'pre-orders'
            );
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->sandbox = 'yes' === $this->get_option('sandbox', 'yes');
            $this->debug = 'yes' === $this->get_option('debug', 'yes');
            $this->seller_protection = $this->get_option('seller_protection', 'disabled');
            $this->enable_in_context_checkout_flow = $this->get_option('enable_in_context_checkout_flow', 'yes');
            $this->paypal_account_optional = $this->get_option('paypal_account_optional', 'no');
            $this->credit_enabled = $this->get_option('credit_enabled', 'yes');
            if ($this->sandbox == true) {
                $this->username = $this->get_option('sandbox_api_username', false);
                $this->password = $this->get_option('sandbox_api_password', false);
                $this->signature = $this->get_option('sandbox_api_signature', false);
                $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
                $this->PAYPAL_URL = "https://www.sandbox.paypal.com/checkoutnow?token=";
            } else {
                $this->username = $this->get_option('api_username', false);
                $this->password = $this->get_option('api_password', false);
                $this->signature = $this->get_option('api_signature', false);
                $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
                $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
            }
            $this->invoice_prefix = $this->get_option('invoice_id_prefix');
            self::$log_enabled = $this->debug;
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'handle_wc_api'));
        } catch (Exception $ex) {
            
        }
    }

    public function init_form_fields() {
        try {
            $this->form_fields = include( 'settings-express-checkout.php' );
        } catch (Exception $ex) {
            
        }
    }

    public function get_icon() {
        $image_path = WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_paypal.png';
        if ($this->paypal_account_optional == 'no' && ($this->credit_enabled == 'no' && is_wpg_credit_supported() == false)) {
            $image_path = WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_paypal.png';
        } elseif ($this->paypal_account_optional == 'yes' && ($this->credit_enabled == 'no' && is_wpg_credit_supported() == false)) {
            $image_path = WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_paypal-credit-card-logos.png';
        } elseif ($this->paypal_account_optional == 'yes' && ($this->credit_enabled == 'yes' && is_wpg_credit_supported() == true)) {
            $image_path = WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_paypal-paypal-credit-card-logos.png';
        }
        if ($this->paypal_account_optional == 'no' && ($this->credit_enabled == 'yes' && is_wpg_credit_supported() == true)) {
            $image_path = WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_paypal.png';
            if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
                $image_path = str_replace('http:', 'https:', $image_path);
            }
            $image_path_two = WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_pp_credit_logo.png';
            if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
                $image_path_two = str_replace('http:', 'https:', $image_path_two);
            }
            $icon = "<img src=\"$image_path\" alt='" . __('Pay with PayPal', 'woo-paypal-gateway') . "'/>";
            $icon_two = "<img src=\"$image_path_two\" alt='" . __('Pay with PayPal', 'woo-paypal-gateway') . "'/>";
            return apply_filters('woocommerce_wpg_paypal_express_icon', $icon . $icon_two, $this->id);
        } else {
            if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
                $image_path = str_replace('http:', 'https:', $image_path);
            }
            $icon = "<img src=\"$image_path\" alt='" . __('Pay with PayPal', 'woo-paypal-gateway') . "'/>";
            return apply_filters('woocommerce_wpg_paypal_express_icon', $icon, $this->id);
        }
    }

    public function payment_fields() {
        if ($description = $this->get_description()) {
            echo wpautop(wptexturize($description));
        }
        if (is_checkout()) {
            $tokens = $this->get_tokens();
            if (sizeof($tokens) == 0) {
                $this->save_payment_method_checkbox();
            } else {
                //$this->new_method_label = __('Create a new billing agreement', 'woo-paypal-gateway');
                $this->tokenization_script();
                $this->saved_payment_methods();
            }
            do_action('payment_fields_saved_payment_methods', $this);
        }
    }

    public function handle_wc_api() {
        try {
            if (!empty($_GET['wpg_express_checkout_action'])) {
                $request_name = $_GET['wpg_express_checkout_action'];
                switch ($request_name) {
                    case 'wpg_set_express_checkout': {
                            $this->init_api();
                            $this->rest_api_handler->wpg_set_express_checkout();
                            break;
                        }
                    case 'wpg_get_express_checkout_details': {
                            $this->init_api();
                            $this->rest_api_handler->wpg_get_express_checkout_details();
                            break;
                        }
                    case 'wpg_do_express_checkout_payment' : {
                            $this->init_api();
                            $this->rest_api_handler->wpg_do_express_checkout_payment();
                            break;
                        }
                    case 'wpg_cancel_url': {
                            $this->init_api();
                            $this->rest_api_handler->wpg_cancel_url();
                            break;
                        }
                    case 'wpg_add_payment_method' : {
                            $this->init_api();
                            $this->rest_api_handler->wpg_add_payment_method();
                            break;
                        }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function is_available() {
        try {
            if ($this->enabled === "yes") {
                if (!$this->username || !$this->password || !$this->signature) {
                    return false;
                }
                if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_paypal_rest_api_supported_currencies', $this->woocommerce_paypal_supported_currencies))) {
                    return false;
                }
                return true;
            }
            return false;
        } catch (Exception $ex) {
            
        }
    }

    public function is_valid_for_use() {
        try {
            return in_array(get_woocommerce_currency(), apply_filters('woocommerce_paypal_rest_api_supported_currencies', $this->woocommerce_paypal_supported_currencies));
        } catch (Exception $ex) {
            
        }
    }

    public function process_payment($order_id) {
        try {
            $order = new WC_Order($order_id);
            if (is_wpg_express_checkout_ready_to_capture()) {
                $this->init_api();
                if ($order->get_total() > 0) {
                    $this->rest_api_handler->wpg_do_express_checkout_payment($order);
                } else {
                    $this->rest_api_handler->wpg_create_billing_agreement($order);
                }
            } else {
                $this->init_api();
                wpg_set_session('post_data', $_POST);
                if (!empty($_POST['wc-wpg_paypal_express-payment-token']) && $_POST['wc-wpg_paypal_express-payment-token'] !== 'new') {
                    $this->rest_api_handler->wpg_do_reference_transaction($order, $is_cart_payment = true);
                } else {
                    $this->rest_api_handler->wpg_set_express_checkout($is_in_content = false);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function process_subscription_payment($renewal_order_id) {
        $order = wc_get_order($renewal_order_id);
        try {
            $this->init_api();
            $this->rest_api_handler->wpg_do_reference_transaction($order);
        } catch (Exception $ex) {
            
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        try {
            $this->init_api();
            $response = $this->rest_api_handler->wpg_refund_transaction($order_id, $amount, $reason);
            return $response;
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public function init_api() {
        try {
            include_once( WPG_PLUGIN_DIR . '/includes/gateways/express-checkout-nvp/class-woo-paypal-gateway-express-checkout-api-handler.php' );
            $this->rest_api_handler = new Woo_Paypal_Gateway_Express_Checkout_API_Handler_NVP($this);
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public static function log($message, $level = 'info') {
        try {
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                    self::$log = wc_get_logger();
                }
                self::$log->log($level, $message, array('source' => 'wpg_paypal_express'));
            }
        } catch (Exception $ex) {
            
        }
    }

    public function add_payment_method() {
        try {
            $this->init_api();
            $this->rest_api_handler->wpg_set_express_checkout_for_add_payment_method();
        } catch (Exception $ex) {
            
        }
    }
}
