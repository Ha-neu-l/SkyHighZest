<?php

/**
 * @since      1.0.0
 * @package    PPCP_Paypal_Checkout_For_Woocommerce_Gateway
 * @subpackage PPCP_Paypal_Checkout_For_Woocommerce_Gateway/includes
 * @author     PayPal <wpeasypayment@gmail.com>
 */
class PPCP_Paypal_Checkout_For_Woocommerce_Gateway_CC extends PPCP_Paypal_Checkout_For_Woocommerce_Gateway {

    public $dcc_applies;
    public $enable;

    public function __construct() {
        parent::__construct();
        $this->plugin_name = 'ppcp-paypal-checkout-cc';
        $this->title = $this->advanced_card_payments_title;
        $this->icon = apply_filters('woocommerce_ppcp_cc_icon', WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png');
        $this->id = 'wpg_paypal_checkout_cc';
        $this->method_title = __('Credit or Debit Card', 'woo-paypal-gateway');
        if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_DCC_Validate')) {
            include_once ( WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-dcc-validate.php');
        }

        $this->enable = $this->cc_enable;
        $this->dcc_applies = PPCP_Paypal_Checkout_For_Woocommerce_DCC_Validate::instance();
    }

    public function payment_fields() {
        if ($this->advanced_card_payments) {
            $this->form();
            echo '<div id="payments-sdk__contingency-lightbox"></div>';
        }
    }

    public function form() {
        wp_enqueue_script('ppcp-checkout-js');
        wp_enqueue_script('ppcp-paypal-checkout-for-woocommerce-public');
        wp_enqueue_style("ppcp-paypal-checkout-for-woocommerce-public");
        ?>
        <div id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
            <div id='wpg_paypal_checkout_cc-card-number'></div>
            <div id='wpg_paypal_checkout_cc-card-expiry'></div>
            <div id='wpg_paypal_checkout_cc-card-cvc'></div>
        </div>

        <?php
    }

    public function get_icon() {
        $title_options = $this->card_labels();
        $images = [];
        $totalIcons = 0;
        foreach ($title_options as $icon_key => $icon_value) {
            if (!in_array($icon_key, $this->disable_cards)) {
                if ($this->dcc_applies->can_process_card($icon_key)) {
                    $iconUrl = esc_url(WPG_PLUGIN_ASSET_URL) . 'assets/' . esc_attr($icon_key) . '.svg';
                    $iconTitle = esc_attr($icon_value);
                    $images[] = sprintf('<img title="%s" src="%s" class="ppcp-card-icon ae-icon-%s" /> ', $iconTitle, $iconUrl, $iconTitle);
                    $totalIcons++;
                }
            }
        }
        return implode('', $images) . '<div class="ppcp-clearfix"></div>';
    }

    public function get_block_icon() {
        $title_options = $this->card_labels();
        $images = [];
        foreach ($title_options as $icon_key => $icon_value) {
            if (!in_array($icon_key, $this->disable_cards)) {
                if ($this->dcc_applies->can_process_card($icon_key)) {
                    $iconUrl = esc_url(WPG_PLUGIN_ASSET_URL) . 'assets/' . esc_attr($icon_key) . '.svg';
                    $images[] = $iconUrl;
                }
            }
        }
        return $images;
    }

    private function card_labels(): array {
        return array(
            'visa' => _x(
                    'Visa',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'mastercard' => _x(
                    'Mastercard',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'maestro' => _x(
                    'Maestro',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'amex' => _x(
                    'American Express',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'discover' => _x(
                    'Discover',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'jcb' => _x(
                    'JCB',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'elo' => _x(
                    'Elo',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
            'hiper' => _x(
                    'Hiper',
                    'Name of credit card',
                    'woo-paypal-gateway'
            ),
        );
    }

    public function is_credentials_set() {
        if (!empty($this->client_id) && !empty($this->secret_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function process_payment($woo_order_id) {
        if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_Request')) {
            include_once WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-request.php';
        }
        $this->request = new PPCP_Paypal_Checkout_For_Woocommerce_Request($this);
        $is_success = false;
        if (isset($_GET['from']) && 'checkout' === $_GET['from']) {
            ppcp_set_session('ppcp_woo_order_id', $woo_order_id);
            $this->request->ppcp_create_order_request($woo_order_id);
            exit();
        } else {
            $ppcp_paypal_order_id = ppcp_get_session('ppcp_paypal_order_id');
            if (!empty($ppcp_paypal_order_id)) {
                include_once WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-request.php';
                $this->request = new PPCP_Paypal_Checkout_For_Woocommerce_Request();
                $order = wc_get_order($woo_order_id);
                if ($this->paymentaction === 'capture') {
                    $is_success = $this->request->ppcp_order_capture_request($woo_order_id);
                } else {
                    $is_success = $this->request->ppcp_order_auth_request($woo_order_id);
                }
                $order->update_meta_data('_payment_action', $this->paymentaction);
                $order->update_meta_data('enviorment', ($this->sandbox) ? 'sandbox' : 'live');
                $order->save_meta_data();
                if ($is_success) {
                    WC()->cart->empty_cart();
                    unset(WC()->session->ppcp_session);
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order),
                    );
                } else {
                    unset(WC()->session->ppcp_session);
                    return array(
                        'result' => 'failure',
                        'redirect' => wc_get_cart_url()
                    );
                }
            } else {
                $result = $this->request->ppcp_regular_create_order_request($woo_order_id);
                if (ob_get_length()) {
                    ob_end_clean();
                }
                return $result;
            }
        }
    }

    public function can_refund_order($order) {
        $has_api_creds = false;
        if (!empty($this->client_id) && !empty($this->secret_id)) {
            $has_api_creds = true;
        }
        return $order && $order->get_transaction_id() && $has_api_creds;
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = wc_get_order($order_id);
        if (!$this->can_refund_order($order)) {
            return new WP_Error('error', __('Refund failed.', 'woo-paypal-gateway'));
        }
        include_once WPG_PLUGIN_DIR . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce-request.php';
        $this->request = new PPCP_Paypal_Checkout_For_Woocommerce_Request();
        $transaction_id = $order->get_transaction_id();
        $bool = $this->request->ppcp_refund_order($order_id, $amount, $reason, $transaction_id);
        return $bool;
    }

    public function is_available() {
        if ($this->enable === 'yes') {
            $this->enabled = true;
            return true;
        }
        return false;
    }
}
