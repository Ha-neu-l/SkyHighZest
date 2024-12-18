<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Payment Gateway for PayPal on WooCommerce
 * Plugin URI:        https://profiles.wordpress.org/easypayment
 * Description:       Advanced Credit Cards, Pay Later, Venmo, SEPA, iDEAL, Mercado Pago, Sofort, Bancontact, Przelewy24, eps & more - by an official PayPal Partner
 * Version:           9.0.16
 * Author:            easypayment
 * Author URI:        https://profiles.wordpress.org/easypayment/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       woo-paypal-gateway
 * Domain Path:       /languages
 * Requires at least: 4.7
 * Requires PHP: 7.1
 * Requires Plugins: woocommerce
 * Tested up to: 6.7.1
 * WC requires at least: 3.4
 * WC tested up to: 9.4.3
 */
if (!defined('WPINC')) {
    die;
}

define('WPG_PLUGIN_VERSION', '9.0.16');

if (!defined('WPG_PLUGIN_PATH')) {
    define('WPG_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
}
if (!defined('WPG_PLUGIN_DIR')) {
    define('WPG_PLUGIN_DIR', dirname(__FILE__));
}
if (!defined('WPG_PLUGIN_BASENAME')) {
    define('WPG_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
if (!defined('WPG_PLUGIN_ASSET_URL')) {
    define('WPG_PLUGIN_ASSET_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPG_PLUGIN_FILE')) {
    define('WPG_PLUGIN_FILE',  __FILE__);
}
if (!defined('WPG_SANDBOX_PARTNER_MERCHANT_ID')) {
    define('WPG_SANDBOX_PARTNER_MERCHANT_ID', 'K6QLN2LPGQRHL');
}
if (!defined('WPG_LIVE_PARTNER_MERCHANT_ID')) {
    define('WPG_LIVE_PARTNER_MERCHANT_ID', 'GT5R877JNBPLL');
}
if (!defined('WPG_ONBOARDING_URL')) {
    define('WPG_ONBOARDING_URL', 'https://mbjtechnolabs.com/ppcp-seller-onboarding/seller-onboarding.php');
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-paypal-gateway-activator.php
 */
function activate_woo_paypal_gateway() {
    set_transient('woo_paypal_gateway_redirect', true, 30);
    require_once plugin_dir_path(__FILE__) . 'includes/class-woo-paypal-gateway-activator.php';
    Woo_Paypal_Gateway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-paypal-gateway-deactivator.php
 */
function deactivate_woo_paypal_gateway() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-woo-paypal-gateway-deactivator.php';
    Woo_Paypal_Gateway_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woo_paypal_gateway');
register_deactivation_hook(__FILE__, 'deactivate_woo_paypal_gateway');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce.php';
require plugin_dir_path(__FILE__) . 'includes/class-woo-paypal-gateway.php';

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */
function run_woo_paypal_gateway() {
    $plugin = new Woo_Paypal_Gateway();
    $plugin->run();
}

function init_wpg_woo_paypal_gateway_class() {
    if (class_exists('WC_Payment_Gateway')) {
        run_ppcp_paypal_checkout_for_woocommerce();
    }
    run_woo_paypal_gateway();
}

add_action('plugins_loaded', 'init_wpg_woo_paypal_gateway_class', 11);

if (!function_exists('run_ppcp_paypal_checkout_for_woocommerce')) {
    run_ppcp_paypal_checkout_for_woocommerce();
}

function run_ppcp_paypal_checkout_for_woocommerce() {
    if(!class_exists('PPCP_Paypal_Checkout_For_Woocommerce')) {
        require plugin_dir_path(__FILE__) . '/ppcp/includes/class-ppcp-paypal-checkout-for-woocommerce.php';
    }
    $plugin = new PPCP_Paypal_Checkout_For_Woocommerce();
    $plugin->run();
}

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('woocommerce_blocks_loaded', function () {
    try {
        if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            return;
        }
        require_once( WPG_PLUGIN_DIR . '/ppcp/checkout-block/ppcp-checkout-block.php' );
        require_once( WPG_PLUGIN_DIR . '/ppcp/checkout-block/ppcp-cc-block.php' );
        //require_once( WPG_PLUGIN_DIR . '/checkout-block/braintree-block.php' );
        add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new PPCP_Checkout_Block);
                    $payment_method_registry->register(new PPCP_Checkout_CC_Block);
                    //$payment_method_registry->register(new Braintree_Block);
                }
        );
    } catch (Exception $ex) {
        
    }
});

add_action('admin_init', 'woo_paypal_gateway_redirect_to_settings');

function woo_paypal_gateway_redirect_to_settings() {
    // Check if the transient is set and user has access to the admin panel
    if (get_transient('woo_paypal_gateway_redirect')) {
        // Remove the transient so it only redirects once
        delete_transient('woo_paypal_gateway_redirect');

        // Make sure the redirect only happens for administrators
        if (is_admin() && current_user_can('manage_options')) {
            wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=wpg_paypal_checkout'));
            exit;
        }
    }
}


