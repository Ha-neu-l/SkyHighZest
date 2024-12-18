<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Braintree_Block extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'wpg_braintree';
    public $version;

    public function initialize() {
        $this->version = WPG_PLUGIN_VERSION;
        $this->settings = get_option('woocommerce_wpg_braintree_settings', []);
        if (!class_exists('Woo_PayPal_Gateway_Braintree')) {
            include_once ( WPG_PLUGIN_DIR . '/includes/gateways/braintree/class-woo-paypal-gateway-braintree.php');
        }
        $this->gateway = new Woo_PayPal_Gateway_Braintree();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        if (!$this->is_active()) {
            return;
        }
        $this->gateway->payment_fields_block();
        wp_enqueue_script('wpg_braintreejs', 'https://js.braintreegateway.com/web/dropin/1.43.0/js/dropin.min.js', array(), WC_VERSION, false);
        wp_register_script('wpg_braintree-blocks-integration', WPG_PLUGIN_ASSET_URL . 'checkout-block/braintree-block.js', array('jquery', 'react', 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-i18n', 'wp-polyfill', 'wp-element', 'wp-plugins'), WPG_PLUGIN_VERSION, true);
        wp_localize_script('wpg_braintree-blocks-integration', 'wpg_braintree_manager_block', array(
            'settins' => $this->settings,
        ));

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('wpg_braintree-blocks-integration', 'woo-paypal-gateway');
        }
       
        return ['wpg_braintree-blocks-integration'];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->gateway->title,
            'description' => $this->get_setting('description'),
            'supports' => $this->get_supported_features(),
            'icons' => $this->gateway->get_icon()
        ];
    }
}
