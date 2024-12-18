<?php

/**
 * WC_Gateway_Palmodule_PayPal_Pro class.
 *
 * @extends WC_Payment_Gateway_CC
 */
class Woo_PayPal_Gateway_PayPal_Pro extends WC_Payment_Gateway_CC {

    public $api_request_handler;
    public $api_version;
    public $liveurl;
    public $testurl;
    public $liveurl_3ds;
    public $testurl_3ds;
    public $available_card_types;
    public $iso4217;
    public $testmode;
    public $api_username;
    public $api_password;
    public $api_signature;
    public $enable_3dsecure;
    public $debug;
    public $send_items;
    public $soft_descriptor;
    public $paymentaction;
    public $invoice_prefix;
    public static $log_enabled = false;
    public static $log = false;

    public function __construct() {
        $this->id = 'wpg_paypal_pro';
        $this->api_version = '119';
        $this->method_title = __('PayPal Pro', 'woo-paypal-gateway');
        $this->method_description = __('PayPal Pro works by adding credit card fields on the checkout and then sending the details to PayPal for verification.', 'woo-paypal-gateway');
        $this->has_fields = true;
        $this->supports = array(
            'products',
            'refunds',
        );
        $this->liveurl = 'https://api-3t.paypal.com/nvp';
        $this->testurl = 'https://api-3t.sandbox.paypal.com/nvp';
        $this->liveurl_3ds = 'https://paypal.cardinalcommerce.com/maps/txns.asp';
        $this->testurl_3ds = 'https://centineltest.cardinalcommerce.com/maps/txns.asp';
        $this->available_card_types = apply_filters('woocommerce_wpg_paypal_pro_available_card_types', array(
            'GB' => array(
                'Visa' => 'Visa',
                'MasterCard' => 'MasterCard',
                'Solo' => 'Solo'
            ),
            'US' => array(
                'Visa' => 'Visa',
                'MasterCard' => 'MasterCard',
                'Discover' => 'Discover',
                'AmEx' => 'American Express'
            ),
            'CA' => array(
                'Visa' => 'Visa',
                'MasterCard' => 'MasterCard'
            ),
            'AU' => array(
                'Visa' => 'Visa',
                'MasterCard' => 'MasterCard'
            ),
            'JP' => array(
                'Visa' => 'Visa',
                'MasterCard' => 'MasterCard',
                'JCB' => 'JCB'
            )
        ));
        $this->available_card_types = apply_filters('woocommerce_wpg_paypal_pro_avaiable_card_types', $this->available_card_types);
        $this->iso4217 = apply_filters('woocommerce_wpg_paypal_pro_iso_currencies', array(
            'AUD' => '036',
            'CAD' => '124',
            'CZK' => '203',
            'DKK' => '208',
            'EUR' => '978',
            'HUF' => '348',
            'JPY' => '392',
            'NOK' => '578',
            'NZD' => '554',
            'PLN' => '985',
            'GBP' => '826',
            'SGD' => '702',
            'SEK' => '752',
            'CHF' => '756',
            'USD' => '840'
        ));
        $this->init_form_fields();
        $this->init_settings();
        $this->icon = $this->get_option('card_icon', WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png');
        if ( is_ssl() || get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) {
            $this->icon = preg_replace("/^http:/i", "https:", $this->icon);
        }
        $this->icon = apply_filters('woocommerce_wpg_paypal_pro_icon', $this->icon);
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode = $this->get_option('testmode', "no") === "yes" ? true : false;
        if( $this->testmode ) {
            $this->api_username = $this->get_option('sandbox_api_username');
            $this->api_password = $this->get_option('sandbox_api_password');
            $this->api_signature = $this->get_option('sandbox_api_signature');
        } else {
            $this->api_username = $this->get_option('api_username');
            $this->api_password = $this->get_option('api_password');
            $this->api_signature = $this->get_option('api_signature');
        }
        $this->enable_3dsecure = $this->get_option('enable_3dsecure', "no") === "yes" ? true : false;
        $this->debug = 'yes' === $this->get_option('debug', 'no');
        self::$log_enabled = $this->debug;
        $this->send_items = $this->get_option('send_items', "no") === "yes" ? true : false;
        $this->soft_descriptor = str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\-\.]/', '', $this->get_option('soft_descriptor', "")));
        $this->paymentaction = $this->get_option('paymentaction', 'sale');
        $this->invoice_prefix = $this->get_option('invoice_prefix');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        
    }

    public function init_form_fields() {
        try {
            $this->form_fields = include( 'settings-paypal-pro.php' );
        } catch (Exception $ex) {
            
        }
    }
    
    public function is_available() {
        if ($this->enabled === "yes") {
            if (!is_ssl() && !$this->testmode) {
                return false;
            }
            if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_paypal_pro_allowed_currencies', array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD')))) {
                return false;
            }
            if (!$this->api_username || !$this->api_password || !$this->api_signature) {
                return false;
            }
            return true;
        }
        return false;
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

    private function get_posted_card() {
        $card_number = isset($_POST['wpg_paypal_pro-card-number']) ? wc_clean($_POST['wpg_paypal_pro-card-number']) : '';
        $card_cvc = isset($_POST['wpg_paypal_pro-card-cvc']) ? wc_clean($_POST['wpg_paypal_pro-card-cvc']) : '';
        $card_expiry = isset($_POST['wpg_paypal_pro-card-expiry']) ? wc_clean($_POST['wpg_paypal_pro-card-expiry']) : '';
        $card_number = str_replace(array(' ', '-'), '', $card_number);
        $card_expiry = array_map('trim', explode('/', $card_expiry));
        $card_exp_month = str_pad($card_expiry[0], 2, "0", STR_PAD_LEFT);
        $card_exp_year = isset($card_expiry[1]) ? $card_expiry[1] : '';
        if (strlen($card_exp_year) == 2) {
            $card_exp_year += 2000;
        }
        return (object) array(
                    'number' => $card_number,
                    'type' => '',
                    'cvc' => $card_cvc,
                    'exp_month' => $card_exp_month,
                    'exp_year' => $card_exp_year
        );
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
            include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-pro/class-woo-paypal-gateway-paypal-pro-api-handler.php' );
            $this->api_request_handler = new Woo_PayPal_Gateway_PayPal_Pro_API_Handler($this);
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public function process_payment($order_id) {
        $this->init_request_api();
        $order = wc_get_order($order_id);
        $card = $this->get_posted_card();
        self::log('Processing order #' . $order_id);
        return $this->api_request_handler->request_do_direct_payment($order, $card);
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $this->init_request_api();
        self::log('Processing Refund order #' . $order_id);
        return $this->api_request_handler->request_process_refund($order_id, $amount, $reason);
    }

    public static function log($message, $level = 'info') {
        if (self::$log_enabled) {
            if (empty(self::$log)) {
                self::$log = wc_get_logger();
            }
            self::$log->log($level, $message, array('source' => 'wpg_paypal_pro'));
        }
    }

}
