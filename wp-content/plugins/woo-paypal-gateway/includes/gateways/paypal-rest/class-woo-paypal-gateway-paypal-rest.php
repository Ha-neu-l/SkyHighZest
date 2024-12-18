<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Woo_PayPal_Gateway_PayPal_Rest class.
 *
 * @extends WC_Payment_Gateway
 */
class Woo_PayPal_Gateway_PayPal_Rest extends WC_Payment_Gateway_CC {

    public static $log_enabled = false;
    public static $log = false;
    public $card_data;
    public $rest_api_handler;
    public $woocommerce_paypal_supported_currencies;
    public $enable_tokenized_payments;
    public $invoice_prefix;
    public $testmode;
    public $debug;
    public $rest_client_id;
    public $rest_secret_id;

    public function __construct() {
        $this->id = 'wpg_paypal_rest';
        $this->has_fields = true;
        $this->method_title = 'PayPal Credit Card Payments (deprecated)';
        $this->woocommerce_paypal_supported_currencies = array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP');
        $this->method_description = __('PayPal direct credit card payments using the REST API.  This allows you to accept credit cards directly on the website.', 'woo-paypal-gateway');
        $this->supports = array(
            'products',
            'refunds'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->icon = $this->get_option('card_icon', WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png');
        if (is_ssl()) {
            $this->icon = preg_replace("/^http:/i", "https:", $this->icon);
        }
        $this->icon = apply_filters('woocommerce_wpg_paypal_rest_icon', $this->icon);
        $this->enable_tokenized_payments = $this->get_option('enable_tokenized_payments', 'no');
        $this->supports = array(
            'subscriptions',
            'products',
            'pre-orders',
            'refunds',
            'subscription_cancellation',
            'subscription_reactivation',
            'subscription_suspension',
            'subscription_amount_changes',
            'subscription_payment_method_change', // Subs 1.n compatibility.
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'subscription_date_changes',
            'multiple_subscriptions',
            'add_payment_method',
            'tokenization'
        );
        $this->title = $this->get_option('title');
        $this->invoice_prefix = $this->get_option('invoice_prefix');
        $this->description = $this->get_option('description');
        $this->testmode = 'yes' === $this->get_option('sandbox', 'no');
        $this->debug = 'yes' === $this->get_option('debug', 'no');
        self::$log_enabled = $this->debug;
        if ($this->testmode) {
            $this->rest_client_id = $this->get_option('rest_client_id_sandbox', false);
            $this->rest_secret_id = $this->get_option('rest_secret_id_sandbox', false);
        } else {
            $this->rest_client_id = $this->get_option('rest_client_id_live', false);
            $this->rest_secret_id = $this->get_option('rest_secret_id_live', false);
        }
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function field_name($name) {
        return $this->supports('tokenizations') ? '' : ' name="' . esc_attr($this->id . '-' . $name) . '" ';
    }

    public function init_form_fields() {
        $this->form_fields = include( 'settings-paypal-rest.php' );
    }

    public function payment_fields() {
        if (!empty($this->description)) {
            echo '<p>' . wp_kses_post($this->description);
        }
        if ($this->testmode == true) {
            echo '<p>';
            _e('NOTICE: SANDBOX (TEST) MODE ENABLED.', 'woo-paypal-gateway');
            echo '<br />';
            _e('For testing purposes you can use the card number 4916311462114485 with any CVC and a valid expiration date.', 'woo-paypal-gateway');
            echo '</p>';
        }
        parent::payment_fields();
    }

    public function validate_fields() {
        try {
            if (isset($_POST['wc-wpg_paypal_rest-payment-token']) && 'new' !== $_POST['wc-wpg_paypal_rest-payment-token']) {
                $token_id = wc_clean($_POST['wc-wpg_paypal_rest-payment-token']);
                $token = WC_Payment_Tokens::get($token_id);
                if ($token->get_user_id() !== get_current_user_id()) {
                    throw new Exception(__('Error processing checkout. Please try again.', 'woo-paypal-gateway'));
                } else {
                    return true;
                }
            }
            $this->card_data = wpg_get_posted_card($this->id);
            if (empty($this->card_data->exp_month) || empty($this->card_data->exp_year)) {
                throw new Exception(__('Card expiration date is invalid', 'woo-paypal-gateway'));
            }
            if (!ctype_digit($this->card_data->cvc) && empty($this->card_data->cvc)) {
                throw new Exception(__('Card security code is invalid (only digits are allowed)', 'woo-paypal-gateway'));
            }
            if (!ctype_digit($this->card_data->exp_month) || !ctype_digit($this->card_data->exp_year) || $this->card_data->exp_month > 12 || $this->card_data->exp_month < 1 || $this->card_data->exp_year < date('y')) {
                throw new Exception(__('Card expiration date is invalid', 'woo-paypal-gateway'));
            }
            if (empty($this->card_data->number) || !ctype_digit($this->card_data->number)) {
                throw new Exception(__('Card number is invalid', 'woo-paypal-gateway'));
            }
            return true;
        } catch (Exception $e) {
            self::log($e->getMessage());
            wc_add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    public function is_available() {
        if ($this->enabled === "yes") {
            if (!$this->rest_client_id || !$this->rest_secret_id) {
                return false;
            }
            if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_paypal_rest_api_supported_currencies', $this->woocommerce_paypal_supported_currencies))) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function is_valid_for_use() {
        return in_array(get_woocommerce_currency(), apply_filters('woocommerce_paypal_rest_api_supported_currencies', $this->woocommerce_paypal_supported_currencies));
    }

    public function process_payment($order_id, $used_payment_token = false) {
        try {
            $this->init_api();
            $this->card_data = wpg_get_posted_card($this->id);
            $response = $this->rest_api_handler->create_payment_request($this->card_data, $order_id, $used_payment_token);
            return $response;
        } catch (Exception $ex) {
            self::log($ex->getMessage());
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
    }

    public function init_api() {
        try {
            if (!class_exists('ComposerAutoloaderInited5cff5a8f8574b72d4f6a04d4c34a6e')) {
                include_once( WPG_PLUGIN_DIR . '/includes/php-library/paypal-rest/vendor/autoload.php' );
            }
            include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-rest/class-woo-paypal-gateway-paypal-rest-api-handler.php' );
            $this->rest_api_handler = new Woo_PayPal_Gateway_PayPal_Rest_API_Handler();
            $this->rest_api_handler->rest_client_id = $this->rest_client_id;
            $this->rest_api_handler->rest_secret_id = $this->rest_secret_id;
            $this->rest_api_handler->sandbox = $this->testmode;
            $this->rest_api_handler->payment_method = $this->id;
            $this->rest_api_handler->rest_settings = $this;
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        try {
            $this->init_api();
            $response = $this->rest_api_handler->create_refund_request($order_id, $amount, $reason = '');
            return $response;
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public static function log($message, $level = 'info') {
        if (self::$log_enabled) {
            if (empty(self::$log)) {
                self::$log = wc_get_logger();
            }
            self::$log->log($level, $message, array('source' => 'wpg_paypal_rest'));
        }
    }

    public function add_payment_method() {
        $this->init_api();
        $this->card_data = wpg_get_posted_card($this->id);
        $response = $this->rest_api_handler->wpg_add_payment_method($this->card_data);
        return $response;
    }

    public function process_pre_order($order_id, $used_payment_token) {
        if (WC_Pre_Orders_Order::order_requires_payment_tokenization($order_id)) {
            try {
                $order = wc_get_order($order_id);
                $this->init_api();
                if ($this->rest_api_handler->is_request_using_save_card_data($order_id) == true) {
                    $this->rest_api_handler->wpg_set_card_token($order_id);
                } else {
                    $this->rest_api_handler->wpg_set_card_data($card_data);
                    if ($this->rest_api_handler->is_save_card_data() == true) {
                        $this->rest_api_handler->wpg_save_card_data_in_vault();
                    }
                }
                $this->rest_api_handler->save_payment_token($order, $this->rest_api_handler->restcreditcardid);
                $order->add_payment_token($this->rest_api_handler->restcreditcardid);
                WC()->cart->empty_cart();
                WC_Pre_Orders_Order::mark_order_as_pre_ordered($order);
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            } catch (Exception $e) {
                wc_add_notice($e->getMessage(), 'error');
                return;
            }
        } else {
            return parent::process_payment($order_id, $used_payment_token = true);
        }
    }
}
