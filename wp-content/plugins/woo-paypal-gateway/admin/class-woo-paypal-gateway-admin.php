<?php

/**
 * @package    Woo_Paypal_Gateway
 * @subpackage Woo_Paypal_Gateway/admin
 * @author     easypayment <wpeasypayment@gmail.com>
 */
class Woo_Paypal_Gateway_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;
    public $subscription_support_enabled;
    public $pre_order_enabled;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->subscription_support_enabled = false;
        $this->pre_order_enabled = false;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('woo-paypal-gateway-admin', plugin_dir_url(__FILE__) . 'css/woo-paypal-gateway-admin.css', array(), $this->version, 'all');
        wp_enqueue_script('woo-paypal-checkout-admin', plugin_dir_url(__FILE__) . 'js/woo-paypal-checkout-admin.js', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        
    }

    public function init_wpg_paypal_payment() {
        if (class_exists('WC_Payment_Gateway')) {
            require_once( WPG_PLUGIN_DIR . '/includes/gateways/express-checkout-nvp/class-woo-paypal-gateway-express-checkout-helper.php' );
            new Woo_Paypal_Gateway_Express_Checkout_Helper_NVP($this->version);
            if (!class_exists('Woo_PayPal_Gateway_Express_Checkout_NVP')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/express-checkout-nvp/class-woo-paypal-gateway-express-checkout.php' );
            }
            if (!class_exists('Woo_PayPal_Gateway_PayPal_Pro')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-pro/class-woo-paypal-gateway-paypal-pro.php' );
            }
            if (!class_exists('Woo_PayPal_Gateway_Braintree')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/braintree/class-woo-paypal-gateway-braintree.php' );
            }
            if (!class_exists('Woo_PayPal_Gateway_PayPal_Advanced')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-advanced/class-woo-paypal-gateway-paypal-advanced.php' );
            }
            if (!class_exists('Woo_Paypal_Gateway_PayPal_Pro_Payflow')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-pro-payflow/class-woo-paypal-gateway-paypal-pro-payflow.php' );
            }
            if (!class_exists('Woo_PayPal_Gateway_PayPal_Rest')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/paypal-rest/class-woo-paypal-gateway-paypal-rest.php' );
            }
            if (is_subscription_activated() == true || is_pre_order_activated() == true) {
                if (!class_exists('Woo_PayPal_Gateway_Express_Checkout_Subscriptions_NVP')) {
                    include_once( WPG_PLUGIN_DIR . '/includes/gateways/express-checkout-nvp/class-woo-paypal-gateway-express-checkout-subscriptions.php' );
                }
            }
        }
    }

    public function wpg_pal_payment_for_woo_add_payment_method_class($methods) {
        $this->init_wpg_paypal_payment();
        if (is_subscription_activated() == true || is_pre_order_activated() == true) {
            if (class_exists('WC_Payment_Gateway')) {
                $methods[] = 'Woo_PayPal_Gateway_Express_Checkout_Subscriptions_NVP';
                return $methods;
            }
        } else {
            if (class_exists('WC_Payment_Gateway')) {
                $methods[] = 'Woo_Paypal_Gateway_Express_Checkout_NVP';
                $methods[] = 'Woo_PayPal_Gateway_Braintree';
                $methods[] = 'Woo_PayPal_Gateway_PayPal_Pro';
                $methods[] = 'Woo_PayPal_Gateway_PayPal_Advanced';
                $methods[] = 'Woo_PayPal_Gateway_PayPal_Pro_Payflow';
                $methods[] = 'Woo_PayPal_Gateway_PayPal_Rest';
                return $methods;
            }
        }
    }

    public function wpg_is_subscription_or_pre_order_enabled() {
        if (class_exists('WC_Subscriptions_Order') && function_exists('wcs_create_renewal_order')) {
            $this->subscription_support_enabled = true;
        }
        if (class_exists('WC_Pre_Orders_Order')) {
            $this->pre_order_enabled = true;
        }
        $load_addons = ( $this->subscription_support_enabled || $this->pre_order_enabled );
        if ($load_addons == false) {
            return false;
        } else {
            return true;
        }
    }
}
