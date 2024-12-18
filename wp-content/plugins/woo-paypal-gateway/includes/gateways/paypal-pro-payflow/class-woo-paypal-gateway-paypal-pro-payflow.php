<?php

/**
 * Woo_Paypal_Gateway_PayPal_Pro_Payflow class.
 *
 * @extends WC_Payment_Gateway_CC
 */
class Woo_Paypal_Gateway_PayPal_Pro_Payflow extends WC_Payment_Gateway_CC {

    public $api_request_handler;
    public $liveurl;
    public $testurl;
    public $allowed_currencies;
    public $testmode;
    public $paypal_vendor;
    public $paypal_partner;
    public $paypal_password;
    public $paypal_user;
    public $debug;
    public $soft_descriptor;
    public $paymentaction;
    public $invoice_prefix;
    public static $log_enabled = false;
    public static $log = false;

    public function __construct() {
        try {
            $this->id = 'wpg_paypal_pro_payflow';
            $this->method_title = __('PayPal Pro PayFlow', 'woo-paypal-gateway');
            $this->method_description = __('PayPal Pro PayFlow Edition works by adding credit card fields on the checkout and then sending the details to PayPal for verification.', 'woo-paypal-gateway');
            $this->has_fields = true;
            $this->supports = array(
                'products',
                'refunds'
            );
            $this->liveurl = 'https://payflowpro.paypal.com';
            $this->testurl = 'https://pilot-payflowpro.paypal.com';
            $this->allowed_currencies = apply_filters('woocommerce_wpg_paypal_pro_payflow_allowed_currencies', array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD'));
            $this->init_form_fields();
            $this->init_settings();
            $this->icon = $this->get_option('card_icon', WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png');
            if (is_ssl()) {
                $this->icon = preg_replace("/^http:/i", "https:", $this->icon);
            }
            $this->icon = apply_filters('woocommerce_wpg_paypal_pro_payflow_icon', $this->icon);
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->testmode = $this->get_option('testmode') === "yes" ? true : false;
            if ($this->testmode) {
                $this->paypal_vendor = $this->get_option('sandbox_paypal_vendor');
                $this->paypal_partner = $this->get_option('sandbox_paypal_partner', 'PayPal');
                $this->paypal_password = trim($this->get_option('sandbox_paypal_password'));
                $this->paypal_user = $this->get_option('sandbox_paypal_user', $this->paypal_vendor);
            } else {
                $this->paypal_vendor = $this->get_option('paypal_vendor');
                $this->paypal_partner = $this->get_option('paypal_partner', 'PayPal');
                $this->paypal_password = trim($this->get_option('paypal_password'));
                $this->paypal_user = $this->get_option('paypal_user', $this->paypal_vendor);
            }
            $this->debug = 'yes' === $this->get_option('debug', 'no');
            self::$log_enabled = $this->debug;
            $this->soft_descriptor = str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\-\.]/', '', $this->get_option('soft_descriptor', "")));
            $this->paymentaction = strtoupper($this->get_option('paymentaction', 'S'));
            $this->invoice_prefix = $this->get_option('invoice_prefix');
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } catch (Exception $ex) {
            
        }
    }

    public function init_form_fields() {
        try {
            $this->form_fields = include( 'settings-paypal-pro-payflow.php' );
        } catch (Exception $ex) {
            
        }
    }

    public function is_available() {
        try {
            if ($this->enabled === "yes") {
                if (!is_ssl() && !$this->testmode) {
                    return false;
                }
                if (!in_array(get_option('woocommerce_currency'), $this->allowed_currencies)) {
                    return false;
                }
                if (!$this->paypal_vendor || !$this->paypal_password) {
                    return false;
                }
                return true;
            }
            return false;
        } catch (Exception $ex) {
            
        }
    }

    private function get_posted_card() {
        try {
            $card_number = isset($_POST['wpg_paypal_pro_payflow-card-number']) ? wc_clean($_POST['wpg_paypal_pro_payflow-card-number']) : '';
            $card_cvc = isset($_POST['wpg_paypal_pro_payflow-card-cvc']) ? wc_clean($_POST['wpg_paypal_pro_payflow-card-cvc']) : '';
            $card_expiry = isset($_POST['wpg_paypal_pro_payflow-card-expiry']) ? wc_clean($_POST['wpg_paypal_pro_payflow-card-expiry']) : '';
            $card_number = str_replace(array(' ', '-'), '', $card_number);
            $card_expiry = array_map('trim', explode('/', $card_expiry));
            $card_exp_month = str_pad($card_expiry[0], 2, "0", STR_PAD_LEFT);
            $card_exp_year = isset($card_expiry[1]) ? $card_expiry[1] : '';
            if (strlen($card_exp_year) == 4) {
                $card_exp_year = $card_exp_year - 2000;
            }
            return (object) array(
                        'number' => $card_number,
                        'type' => '',
                        'cvc' => $card_cvc,
                        'exp_month' => $card_exp_month,
                        'exp_year' => $card_exp_year
            );
        } catch (Exception $ex) {
            
        }
    }

    public function validate_fields() {
        try {
            $card = $this->get_posted_card();
            if (empty($card->exp_month) || empty($card->exp_year)) {
                throw new Exception(__('Card expiration date is invalid', 'woo-paypal-gateway'));
            }
            if (!ctype_digit($card->cvc)) {
                throw new Exception(__('Card security code is invalid (only digits are allowed)', 'woo-paypal-gateway'));
            }
            if (!ctype_digit($card->exp_month) || !ctype_digit($card->exp_year) || $card->exp_month > 12 || $card->exp_month < 1 || $card->exp_year < date('y')) {
                throw new Exception(__('Card expiration date is invalid', 'woo-paypal-gateway'));
            }
            if (empty($card->number) || !ctype_digit($card->number)) {
                throw new Exception(__('Card number is invalid', 'woo-paypal-gateway'));
            }
            return true;
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    public function init_request_api() {
        try {
            include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-pro-payflow/class-woo-paypal-gateway-paypal-pro-payflow-api-handler.php' );
            $this->api_request_handler = new Woo_Paypal_Gateway_PayPal_Pro_Payflow_API_Handler();
            $this->api_request_handler->gateway_settings = $this;
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public function process_payment($order_id) {
        try {
            $this->init_request_api();
            $order = wc_get_order($order_id);
            $card = $this->get_posted_card();
            self::log('Processing order #' . $order_id);
            return $this->api_request_handler->request_do_payment($order, $card);
        } catch (Exception $ex) {
            
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        try {
            $this->init_request_api();
            return $this->api_request_handler->request_process_refund($order_id, $amount, $reason);
        } catch (Exception $ex) {
            
        }
    }

    public function payment_fields() {
        if (!empty($this->description)) {
            echo '<p>' . wp_kses_post($this->description);
        }
        if ($this->testmode == true) {
            echo '<p>';
            _e('NOTICE: SANDBOX (TEST) MODE ENABLED.', 'woo-paypal-gateway');
            echo '<br />';
            _e('For testing purposes you can use the card number 4111111111111111 with any CVC and a valid expiration date.', 'woo-paypal-gateway');
            echo '</p>';
        }
        parent::payment_fields();
    }

    public static function log($message, $level = 'info') {
        try {
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                    self::$log = wc_get_logger();
                }
                self::$log->log($level, $message, array('source' => 'wpg_paypal_pro_payflow'));
            }
        } catch (Exception $ex) {
            
        }
    }
}
