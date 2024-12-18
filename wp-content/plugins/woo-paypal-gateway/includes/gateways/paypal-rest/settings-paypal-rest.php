<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for PayPal Rest Gateway.
 */
return $this->form_fields = array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Enable PayPal Credit Card (REST)', 'woo-paypal-gateway'),
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __('PayPal Credit Card (REST)', 'woo-paypal-gateway')
    ),
    'description' => array(
        'title' => __('Description', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls the description which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __('Pay with your credit card', 'woo-paypal-gateway')
    ),
    'invoice_prefix' => array(
        'title' => __('Invoice Prefix', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'woo-paypal-gateway'),
        'default' => 'WC-PCCR',
        'desc_tip' => true,
    ),
    'sandbox' => array(
        'title' => __('Sandbox Mode', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Enable PayPal Sandbox Mode', 'woo-paypal-gateway'),
        'default' => 'yes',
        'description' => sprintf(__('Place the payment gateway in development mode. Sign up for a developer account <a href="%s" target="_blank">here</a>', 'woo-paypal-gateway'), 'https://developer.paypal.com/'),
    ),
    'rest_client_id_sandbox' => array(
        'title' => __('Sandbox Client ID', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => 'Enter your Sandbox PayPal Rest API Client ID',
        'default' => ''
    ),
    'rest_secret_id_sandbox' => array(
        'title' => __('Sandbox Secret ID', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Enter your Sandbox PayPal Rest API Secret ID.', 'woo-paypal-gateway'),
        'default' => ''
    ),
    'rest_client_id_live' => array(
        'title' => __('Live Client ID', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => 'Enter your PayPal Rest API Client ID',
        'default' => ''
    ),
    'rest_secret_id_live' => array(
        'title' => __('Live Secret ID', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Enter your PayPal Rest API Secret ID.', 'woo-paypal-gateway'),
        'default' => ''
    ),
    'card_icon' => array(
        'title' => __('Card Icon', 'woo-paypal-gateway'),
        'type' => 'text',
        'default' => WPG_PLUGIN_ASSET_URL . '/assets/images/wpg_cards.png',
        'class' => 'button_upload'
    ),
    'debug' => array(
        'title' => __('Debug Log', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Enable logging', 'woo-paypal-gateway'),
        'default' => 'no',
        'description' => sprintf(__('Log PayPal events, such as Secured Token requests', 'woo-paypal-gateway')),
    ),
    'advanced' => array(
        'title' => __('Advanced options', 'woo-paypal-gateway'),
        'type' => 'title',
        'description' => '',
    ),
    'enable_tokenized_payments' => array(
        'title' => __('Enable Tokenized Payments', 'woo-paypal-gateway'),
        'label' => __('Enable Tokenized Payments', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => __('Allow buyers to securely save payment details to their account for quick checkout / auto-ship orders in the future.', 'woo-paypal-gateway'),
        'default' => 'no',
        'class' => ''
    ),
);
