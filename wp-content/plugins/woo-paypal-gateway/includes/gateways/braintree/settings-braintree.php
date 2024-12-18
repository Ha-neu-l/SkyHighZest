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
        'label' => __('Enable Braintree Payment Gateway', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => '',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __('Credit / PayPal / PayPal Credit', 'woo-paypal-gateway'),
        'desc_tip' => true
    ),
    'description' => array(
        'title' => __('Description', 'woo-paypal-gateway'),
        'type' => 'textarea',
        'description' => __('This controls the description which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => 'Pay securely with your Credit Card/PayPal.',
        'desc_tip' => true
    ),
    'invoice_prefix' => array(
        'title' => __('Invoice Prefix', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'woo-paypal-gateway'),
        'default' => 'WC-BR',
        'desc_tip' => true,
    ),
    'sandbox' => array(
        'title' => __('Sandbox', 'woo-paypal-gateway'),
        'label' => __('Enable Sandbox Mode', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => __('Place the payment gateway in sandbox mode using sandbox API keys (real payments will not be taken).', 'woo-paypal-gateway'),
        'default' => 'yes'
    ),
    'sandbox_public_key' => array(
        'title' => __('Sandbox Public Key', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API keys from your Braintree account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_private_key' => array(
        'title' => __('Sandbox Private Key', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API keys from your Braintree account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_merchant_id' => array(
        'title' => __('Sandbox Merchant ID', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API keys from your Braintree account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'live_public_key' => array(
        'title' => __('Live Public Key', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API keys from your Braintree account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'live_private_key' => array(
        'title' => __('Live Private Key', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API keys from your Braintree account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'live_merchant_id' => array(
        'title' => __('Live Merchant ID', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API keys from your Braintree account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'card_icon' => array(
        'title' => __('Card Icon', 'woo-paypal-gateway'),
        'type' => 'text',
        'default' => WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png',
        'class' => 'button_upload'
    ),
    'debug' => array(
        'title' => __('Debug Log', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Enable logging', 'woo-paypal-gateway'),
        'default' => 'yes',
        'description' => sprintf(__('Log PayPal/Braintree events', 'woo-paypal-gateway'))
    )
);
