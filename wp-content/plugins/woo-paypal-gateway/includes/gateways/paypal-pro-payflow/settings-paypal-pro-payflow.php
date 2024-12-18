<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for PayPal Pro Payflow Gateway.
 */
return $this->form_fields = array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'woo-paypal-gateway'),
        'label' => __('Enable PayPal Pro Payflow Edition', 'woo-paypal-gateway'),
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
        'default' => __('Pay with your credit card.', 'woo-paypal-gateway'),
        'desc_tip' => true
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
    'invoice_prefix' => array(
        'title' => __('Invoice Prefix', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'woo-paypal-gateway'),
        'default' => 'WC-PayPal_PF',
        'desc_tip' => true,
    ),
    'testmode' => array(
        'title' => __('Test Mode', 'woo-paypal-gateway'),
        'label' => __('Enable PayPal Sandbox/Test Mode', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => __('Place the payment gateway in development mode.', 'woo-paypal-gateway'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'sandbox_paypal_partner' => array(
        'title' => __('Partner', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('The ID provided to you by the authorized PayPal Reseller who registered you
			for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'woo-paypal-gateway'),
        'default' => 'PayPal',
        'desc_tip' => true
    ),
    'sandbox_paypal_vendor' => array(
        'title' => __('Merchant Login', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Your merchant login ID that you created when you registered for the account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_paypal_user' => array(
        'title' => __('User (optional)', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('If you set up one or more additional users on the account, this value is the ID
			of the user authorized to process transactions. Otherwise, leave this field blank.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_paypal_password' => array(
        'title' => __('Password', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('The password that you defined while registering for the account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'paypal_partner' => array(
        'title' => __('Partner', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('The ID provided to you by the authorized PayPal Reseller who registered you
			for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'woo-paypal-gateway'),
        'default' => 'PayPal',
        'desc_tip' => true
    ),
    'paypal_vendor' => array(
        'title' => __('Merchant Login', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Your merchant login ID that you created when you registered for the account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'paypal_user' => array(
        'title' => __('User (optional)', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('If you set up one or more additional users on the account, this value is the ID
			of the user authorized to process transactions. Otherwise, leave this field blank.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'paypal_password' => array(
        'title' => __('Password', 'woo-paypal-gateway'),
        'type' => 'password',
        'description' => __('The password that you defined while registering for the account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'paymentaction' => array(
        'title' => __('Payment Action', 'woo-paypal-gateway'),
        'type' => 'select',
        'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'woo-paypal-gateway'),
        'default' => 'sale',
        'desc_tip' => true,
        'options' => array(
            'S' => __('Capture', 'woo-paypal-gateway'),
            'A' => __('Authorize', 'woo-paypal-gateway')
        )
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
        'default' => 'no',
        'desc_tip' => true,
        'description' => __('Log PayPal Pro (Payflow) events inside <code>woocommerce/logs/paypal-pro-payflow.txt</code>', 'woo-paypal-gateway'),
    )
);
