<?php

/**
 * Define the internationalization functionality.
 * @since      1.0.0
 * @package    Woo_Paypal_Gateway
 * @subpackage Woo_Paypal_Gateway/includes
 * @author     easypayment <wpeasypayment@gmail.com>
 */
class Woo_Paypal_Gateway_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
                'woo-paypal-gateway', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}
