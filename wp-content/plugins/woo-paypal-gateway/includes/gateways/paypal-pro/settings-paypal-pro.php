<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for PayPal Pro Gateway.
 */
return $this->form_fields = array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'woo-paypal-gateway'),
        'label' => __('Enable PayPal Pro', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => '',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __('Credit card (PayPal)', 'woo-paypal-gateway'),
        'desc_tip' => true
    ),
    'description' => array(
        'title' => __('Description', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls the description which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __('Pay with your credit card via PayPal Website Payments Pro.', 'woo-paypal-gateway'),
        'desc_tip' => true
    ),
    'testmode' => array(
        'title' => __('Test Mode', 'woo-paypal-gateway'),
        'label' => __('Enable PayPal Sandbox/Test Mode', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => __('Place the payment gateway in development mode.', 'woo-paypal-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'sandbox_api_username' => array(
        'title' => __('Sandbox API Username', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Get your API credentials from PayPal.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_api_password' => array(
        'title' => __('Sandbox API Password', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API credentials from PayPal.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_api_signature' => array(
        'title' => __('Sandbox API Signature', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API credentials from PayPal.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'api_username' => array(
        'title' => __('API Username', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Get your API credentials from PayPal.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'api_password' => array(
        'title' => __('API Password', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API credentials from PayPal.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'api_signature' => array(
        'title' => __('API Signature', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('Get your API credentials from PayPal.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'paymentaction' => array(
        'title' => __('Payment Action', 'woo-paypal-gateway'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'woo-paypal-gateway'),
        'default' => 'sale',
        'desc_tip' => true,
        'options' => array(
            'sale' => __('Capture', 'woo-paypal-gateway'),
            'authorization' => __('Authorize', 'woo-paypal-gateway')
        )
    ),
    'invoice_prefix' => array(
        'title' => __('Invoice Prefix', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'woo-paypal-gateway'),
        'default' => 'WC-PayPal_Pro',
        'desc_tip' => true,
    ),
    'card_icon' => array(
        'title' => __('Card Icon', 'woo-paypal-gateway'),
        'type' => 'text',
        'default' => WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png',
        'class' => 'button_upload',
    ),
    'soft_descriptor' => array(
        'title' => __('Soft Descriptor', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('(Optional) Information that is usually displayed in the account holder\'s statement, for example your website name. Only 23 alphanumeric characters can be included, including the special characters dash (-) and dot (.) . Asterisks (*) and spaces ( ) are NOT permitted.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true,
        'custom_attributes' => array(
            'maxlength' => 23,
            'pattern' => '[a-zA-Z0-9.-]+'
        )
    ),
    'debug' => array(
        'title' => __('Debug Log', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Enable logging', 'woo-paypal-gateway'),
        'default' => 'no',
        'desc_tip' => true,
        'description' => __('Log PayPal Pro events inside <code>woocommerce/logs/paypal-pro.txt</code>', 'woo-paypal-gateway'),
    )
);
