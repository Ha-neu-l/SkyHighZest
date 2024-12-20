<?php

defined('ABSPATH') || exit;

class PPCP_Paypal_Checkout_For_Woocommerce_Seller_Onboarding {

    public $dcc_applies;
    public $on_board_host;
    public $sandbox;
    public $settings;
    public $host;
    public $partner_merchant_id;
    public $sandbox_partner_merchant_id;
    public $api_request;
    public $result;
    protected static $_instance = null;
    public $api_log;
    public $is_sandbox;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        try {
            $this->wpg_load_class();
            $this->sandbox_partner_merchant_id = WPG_SANDBOX_PARTNER_MERCHANT_ID;
            $this->partner_merchant_id = WPG_LIVE_PARTNER_MERCHANT_ID;
            $this->on_board_host = WPG_ONBOARDING_URL;
            add_action('wc_ajax_wpg_login_seller', array($this, 'wpg_login_seller'));
            add_action('admin_init', array($this, 'wpg_listen_for_merchant_id'));
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function wpg_load_class() {
        try {
            if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_DCC_Validate')) {
                include_once ( WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-dcc-validate.php');
            }
            if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_Settings')) {
                include_once WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-settings.php';
            }
            if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_Request')) {
                include_once WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-request.php';
            }
            if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_Log')) {
                include_once WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-log.php';
            }
            $this->api_log = PPCP_Paypal_Checkout_For_Woocommerce_Log::instance();
            $this->settings = PPCP_Paypal_Checkout_For_Woocommerce_Settings::instance();
            $this->dcc_applies = PPCP_Paypal_Checkout_For_Woocommerce_DCC_Validate::instance();
            $this->api_request = PPCP_Paypal_Checkout_For_Woocommerce_Request::instance();
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function nonce() {
        return 'a1233wtergfsdt4365tzrshgfbaewa36AGa1233wtergfsdt4365tzrshgfbaewa36AG';
    }

    public function data() {
        $data = $this->default_data();
        return $data;
    }

    public function wpg_generate_signup_link($sandbox) {
        $this->is_sandbox = ( $sandbox === 'yes' ) ? true : false;
        $host_url = $this->on_board_host;
        $args = array(
            'method' => 'POST',
            'body' => $this->data(),
            'headers' => array(),
        );
        return $this->api_request->request($host_url, $args, 'generate_signup_link');
    }

    private function default_data() {
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        return array(
            'email' => $user_email,
            'sandbox' => ($this->is_sandbox) ? 'yes' : 'no',
            'return_url' => admin_url(
                    'admin.php?page=wc-settings&tab=checkout&section=wpg_paypal_checkout&sandbox=' . ($this->is_sandbox ? 'yes' : 'no')
            ),
            'return_url_description' => __(
                    'Return to your shop.', 'woo-paypal-gateway'
            ),
            'products' => array(
                $this->dcc_applies->for_country_currency() ? 'PPCP' : 'EXPRESS_CHECKOUT',
        ));
    }

    public function wpg_login_seller() {
        try {
            $posted_raw = wpg_get_raw_data();
            if (empty($posted_raw)) {
                return false;
            }
            $data = json_decode($posted_raw, true);
            $this->wpg_get_credentials($data);
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function wpg_get_credentials($data) {
        try {
            $this->is_sandbox = isset($data['env']) && 'sandbox' === $data['env'];
            $this->host = ($this->is_sandbox) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
            $this->settings->set('sandbox', ($this->is_sandbox) ? 'yes' : 'no');
            $this->settings->persist();
            delete_transient('ppcp_sandbox_access_token');
            delete_transient('ppcp_live_access_token');
            delete_transient('ppcp_sandbox_client_token');
            delete_transient('ppcp_live_client_token');
            delete_option('ppcp_sandbox_webhook_id');
            delete_option('ppcp_live_webhook_id');
            $token = $this->wpg_get_access_token($data);
            $credentials = $this->wpg_get_seller_rest_api_credentials($token);
            if (!empty($credentials['client_secret']) && !empty($credentials['client_id'])) {
                if ($this->is_sandbox) {
                    $this->settings->set('enabled', 'yes');
                    $this->settings->set('rest_secret_id_sandbox', $credentials['client_secret']);
                    $this->settings->set('rest_client_id_sandbox', $credentials['client_id']);
                } else {
                    $this->settings->set('enabled', 'yes');
                    $this->settings->set('rest_secret_id_live', $credentials['client_secret']);
                    $this->settings->set('rest_client_id_live', $credentials['client_id']);
                }
                $this->settings->persist();
                if ($this->is_sandbox) {
                    set_transient('wpg_sandbox_seller_onboarding_process_done', 'yes', 29000);
                } else {
                    set_transient('wpg_live_seller_onboarding_process_done', 'yes', 29000);
                }
            }
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function wpg_get_access_token($data) {
        try {
            if (empty($data['authCode'])) {
                return false;
            }
            $authCode = $data['authCode'];
            $sharedId = $data['sharedId'];
            $url = trailingslashit($this->host) . 'v1/oauth2/token/';
            $args = array(
                'method' => 'POST',
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($sharedId . ':'),
                ),
                'body' => array(
                    'grant_type' => 'authorization_code',
                    'code' => $authCode,
                    'code_verifier' => $this->nonce(),
                ),
            );
            $this->result = $this->api_request->request($url, $args, 'get_access_token');
            if (isset($this->result['access_token'])) {
                return $this->result['access_token'];
            }
        } catch (Exception $ex) {
            if ($this->is_sandbox) {
                set_transient('wpg_sandbox_seller_onboarding_process_failed', 'yes', 29000);
            } else {
                set_transient('wpg_live_seller_onboarding_process_failed', 'yes', 29000);
            }
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
            return false;
        }
    }

    public function wpg_get_seller_rest_api_credentials($token) {
        if ($this->is_sandbox) {
            $partner_merchant_id = $this->sandbox_partner_merchant_id;
        } else {
            $partner_merchant_id = $this->partner_merchant_id;
        }
        try {
            $url = trailingslashit($this->host) .
                    'v1/customer/partners/' . $partner_merchant_id .
                    '/merchant-integrations/credentials/';
            $args = array(
                'method' => 'GET',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ),
            );
            $this->result = $this->api_request->request($url, $args, 'get_credentials');
            if (!isset($this->result['client_id']) || !isset($this->result['client_secret'])) {
                return false;
            }
            return $this->result;
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
            return false;
        }
    }

    public function wpg_listen_for_merchant_id() {
        try {
            if (!$this->is_valid_site_request()) {
                return;
            }
            if (!isset($_GET['merchantIdInPayPal'])) {
                return;
            }
            $this->is_sandbox = (isset($_GET['sandbox']) && $_GET['sandbox'] === 'yes');
            $merchant_id = sanitize_text_field(wp_unslash($_GET['merchantIdInPayPal']));
            $this->host = ($this->is_sandbox) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
            $this->result = $this->wpg_track_seller_onboarding_status($merchant_id);
            if ($this->wpg_is_acdc_payments_enable($this->result)) {
                $this->settings->set('enable_advanced_card_payments', 'yes');
            } else {
                $this->settings->set('enable_advanced_card_payments', 'no');
            }
            if (isset($_GET['isEmailConfirmed']) && $_GET['isEmailConfirmed'] === false) {
                set_transient('wpg_primary_email_not_confirmed', 'yes', 29000);
            }
            if (isset($_GET['merchantId'])) {
                $merchant_email = sanitize_text_field(wp_unslash($_GET['merchantId']));
            } else {
                $merchant_email = '';
            }
            if ($this->is_sandbox) {
                $this->settings->set('sandbox_merchant_id', $merchant_id);
                $this->settings->set('sandbox_email_address', $merchant_email);
                $this->settings->set('enabled', 'yes');
            } else {
                $this->settings->set('live_merchant_id', $merchant_id);
                $this->settings->set('live_email_address', $merchant_email);
                $this->settings->set('enabled', 'yes');
            }
            $this->settings->persist();
            $redirect_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=wpg_paypal_checkout&wpg_section=wpg_api_settings');
            wp_safe_redirect($redirect_url, 302);
            exit;
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function wpg_track_seller_onboarding_status($merchant_id) {
        $this->is_sandbox = (isset($_GET['sandbox']) && $_GET['sandbox'] === 'yes');
        if ($this->is_sandbox) {
            $partner_merchant_id = $this->sandbox_partner_merchant_id;
        } else {
            $partner_merchant_id = $this->partner_merchant_id;
        }
        try {
            $access_token = $this->api_request->ppcp_get_access_token();
            $url = trailingslashit($this->host) .
                    'v1/customer/partners/' . $partner_merchant_id .
                    '/merchant-integrations/' . $merchant_id;
            $args = array(
                'method' => 'GET',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json',
                ),
            );
            $this->result = $this->api_request->request($url, $args, 'seller_onboarding_status');
            return $this->result;
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
            return false;
        }
    }

    public function is_valid_site_request() {
        if (!isset($_REQUEST['section']) || !in_array(sanitize_text_field(wp_unslash($_REQUEST['section'])), array('wpg_paypal_checkout'), true)) {
            return false;
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        return true;
    }

    public function wpg_is_acdc_payments_enable($result) {
        if (isset($result['products']) && isset($result['capabilities']) && !empty($result['products']) && !empty($result['products'])) {
            foreach ($result['products'] as $key => $product) {
                if (isset($product['vetting_status']) && ('SUBSCRIBED' === $product['vetting_status'] || 'APPROVED' === $product['vetting_status'] ) && isset($product['capabilities']) && is_array($product['capabilities']) && in_array('CUSTOM_CARD_PROCESSING', $product['capabilities'])) {
                    foreach ($result['capabilities'] as $key => $capabilities) {
                        if (isset($capabilities['name']) && 'CUSTOM_CARD_PROCESSING' === $capabilities['name'] && 'ACTIVE' === $capabilities['status']) {
                            return true;
                        }
                    }
                }
            }
        }
    }
}
