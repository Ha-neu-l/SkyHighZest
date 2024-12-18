<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for PayPal Express Gateway.
 */
$require_ssl = '';
if (wc_checkout_is_https() == false) {
    $require_ssl = __('This image requires an SSL host.  Please upload your image to <a target="_blank" href="http://www.sslpic.com">www.sslpic.com</a> and enter the image URL here.', 'woo-paypal-gateway');
}
$credit_enabled_label = __('Enable PayPal Credit', 'woo-paypal-gateway');
if (is_wpg_credit_supported() == false) {
    $credit_enabled_label .= '<p><em>' . __('This option is disabled. Currently PayPal Credit only available for U.S. and U.K. merchants.', 'woo-paypal-gateway') . '</em></p>';
}
return $this->form_fields = array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'woo-paypal-gateway'),
        'label' => __('Enable PayPal Express', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'description' => '',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __('PayPal Express', 'woo-paypal-gateway'),
        'desc_tip' => true,
    ),
    'description' => array(
        'title' => __('Description', 'woo-paypal-gateway'),
        'type' => 'textarea',
        'description' => __('This controls the description which the user sees during checkout.', 'woo-paypal-gateway'),
        'default' => __("Pay via PayPal; you can pay with your credit card if you don't have a PayPal account", 'woo-paypal-gateway'),
        'desc_tip' => true,
    ),
    'account_settings' => array(
        'title' => __('Account Settings (Classic Sandbox API Credentials)', 'woo-paypal-gateway'),
        'type' => 'title',
        'description' => '',
    ),
    'sandbox' => array(
        'title' => __('Sandbox Mode', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Enable PayPal Sandbox Mode', 'woo-paypal-gateway'),
        'default' => 'yes',
        'description' => sprintf(__('Place the payment gateway in development mode. Sign up for a developer account <a href="%s" target="_blank">here</a>', 'woo-paypal-gateway'), 'https://developer.paypal.com/'),
    ),
    'sandbox_api_username' => array(
        'title' => __('Username', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Create sandbox accounts and obtain API credentials from within your PayPal developer account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true
    ),
    'sandbox_api_password' => array(
        'title' => __('Password', 'woo-paypal-gateway'),
        'type' => 'password',
        'default' => ''
    ),
    'sandbox_api_signature' => array(
        'title' => __('Signature', 'woo-paypal-gateway'),
        'type' => 'password',
        'default' => '',
        'css' => 'width: 485px;'
    ),
    'api_username' => array(
        'title' => __('Username', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Get your live account API credentials from your PayPal account profile under the API Access section <br />or by using <a target="_blank" href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_login-api-run">this tool</a>.', 'woo-paypal-gateway'),
        'default' => ''
    ),
    'api_password' => array(
        'title' => __('Password', 'woo-paypal-gateway'),
        'type' => 'password',
        'default' => ''
    ),
    'api_signature' => array(
        'title' => __('Signature', 'woo-paypal-gateway'),
        'type' => 'password',
        'default' => '',
        'css' => 'width: 485px;'
    ),
    'display_settings' => array(
        'title' => __('Display Settings (Optional)', 'woo-paypal-gateway'),
        'type' => 'title',
        'class' => '',
        'description' => __('Customize the appearance of Express Checkout in your store.', 'woo-paypal-gateway'),
    ),
    'pagestyle' => array(
        'title' => __('Page Style', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Optionally enter the name of the page style you wish to use. These are defined within your PayPal account.', 'woo-paypal-gateway'),
        'default' => '',
        'desc_tip' => true,
        'placeholder' => __('Optional', 'woo-paypal-gateway'),
    ),
    'brandname' => array(
        'title' => __('Brand Name', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls what users see as the brand / company name on PayPal review pages.', 'woo-paypal-gateway'),
        'default' => __(get_bloginfo('name'), 'woo-paypal-gateway'),
        'desc_tip' => true,
    ),
    'logoimg' => array(
        'title' => __('PayPal Checkout Logo Image(190x60)', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls what users see as the logo on PayPal review pages. ', 'woo-paypal-gateway') . $require_ssl,
        'default' => '',
        'desc_tip' => true,
        'placeholder' => __('Optional', 'woo-paypal-gateway'),
    ),
    'hdrimg' => array(
        'title' => __('Header Image (750x90)', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This controls what users see as the header banner on PayPal review pages. ', 'woo-paypal-gateway') . $require_ssl,
        'default' => '',
        'desc_tip' => true,
        'placeholder' => __('Optional', 'woo-paypal-gateway'),
    ),
    'paypal_account_optional' => array(
        'title' => __('PayPal Account Optional', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Allow customers to checkout without a PayPal account using their credit card.', 'woo-paypal-gateway'),
        'default' => 'no',
        'description' => __('PayPal Account Optional must be turned on in your PayPal account profile under Website Preferences.', 'woo-paypal-gateway'),
        'desc_tip' => true,
    ),
    'landing_page' => array(
        'title' => __('Landing Page', 'woo-paypal-gateway'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Type of PayPal page to display.', 'woo-paypal-gateway'),
        'default' => 'Login',
        'desc_tip' => true,
        'options' => array(
            'Billing' => _x('Billing (Non-PayPal account)', 'Type of PayPal page', 'woo-paypal-gateway'),
            'Login' => _x('Login (PayPal account login)', 'Type of PayPal page', 'woo-paypal-gateway'),
        ),
    ),
    'credit_enabled' => array(
        'title' => __('Enable PayPal Credit', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => $credit_enabled_label,
        'disabled' => is_wpg_credit_supported() == false,
        'default' => is_wpg_credit_supported() == false ? 'no' : 'yes',
        'desc_tip' => true,
        'description' => __('This enables PayPal Credit, which displays a PayPal Credit button next to the Express Checkout button. PayPal Express Checkout lets you give customers access to financing through PayPal Credit® - at no additional cost to you. You get paid up front, even though customers have more time to pay. A pre-integrated payment button shows up next to the PayPal Button, and lets customers pay quickly with PayPal Credit®.', 'woo-paypal-gateway'),
    ),
    'show_on_product_page' => array(
        'title' => __('Product Page', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Show the Express Checkout button on product detail pages.', 'woo-paypal-gateway'),
        'default' => 'no',
        'description' => sprintf(__('Allows customers to checkout using PayPal directly from a product page.')),
        'desc_tip' => false,
    ),
    'show_on_cart' => array(
        'title' => __('Cart Page', 'woo-paypal-gateway'),
        'label' => __('Show Express Checkout button on shopping cart page.', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'default' => 'yes'
    ),
    'button_position' => array(
        'title' => __('Cart Button Position', 'woo-paypal-gateway'),
        'label' => __('Where to display PayPal Express Checkout button(s).', 'woo-paypal-gateway'),
        'class' => 'wc-enhanced-select',
        'description' => __('Set where to display the PayPal Express Checkout button(s).'),
        'type' => 'select',
        'options' => array(
            'top' => 'At the top, above the shopping cart details.',
            'bottom' => 'At the bottom, below the shopping cart details.',
            'both' => 'Both at the top and bottom, above and below the shopping cart details.'
        ),
        'default' => 'bottom',
        'desc_tip' => true,
    ),
    'show_on_checkout_page' => array(
        'title' => __('Checkout Page', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Show the Express Checkout button on checkout page.', 'woo-paypal-gateway'),
        'default' => 'yes',
        'description' => sprintf(__('Allows customers to checkout using PayPal directly from a checkout page.')),
        'desc_tip' => false,
    ),
    'checkout_skip_text' => array(
        'title' => __('Express Checkout Message', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('This message will be displayed near to the PayPal Express Checkout button at the top of the checkout page.'),
        'default' => __('Skip the checkout form and pay faster with PayPal!', 'woo-paypal-gateway'),
        'desc_tip' => true,
    ),
    'button_styles' => array(
        'title' => __('Express Checkout Custom Button Styles (Optional)', 'woo-paypal-gateway'),
        'type' => 'title',
        'description' => 'Customize your PayPal button with colors, sizes and shapes.',
    ),
    'button_size' => array(
        'title' => __('Button Size', 'woo-paypal-gateway'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Type of PayPal Button Size (small | medium | responsive).', 'woo-paypal-gateway'),
        'default' => 'small',
        'desc_tip' => true,
        'options' => array(
            'small' => __('Small', 'woo-paypal-gateway'),
            'medium' => __('Medium', 'woo-paypal-gateway'),
            'responsive' => __('Responsive', 'woo-paypal-gateway'),
        ),
    ),
    'button_shape' => array(
        'title' => __('Button Shape', 'woo-paypal-gateway'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Type of PayPal Button Shape (pill | rect).', 'woo-paypal-gateway'),
        'default' => 'pill',
        'desc_tip' => true,
        'options' => array(
            'pill' => __('Pill', 'woo-paypal-gateway'),
            'rect' => __('Rect', 'woo-paypal-gateway')
        ),
    ),
    'button_color' => array(
        'title' => __('Button Color', 'woo-paypal-gateway'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Type of PayPal Button Color (gold | blue | silver).', 'woo-paypal-gateway'),
        'default' => 'gold',
        'desc_tip' => true,
        'options' => array(
            'gold' => __('Gold', 'woo-paypal-gateway'),
            'blue' => __('Blue', 'woo-paypal-gateway'),
            'silver' => __('Silver', 'woo-paypal-gateway')
        ),
    ),
    'advanced' => array(
        'title' => __('Advanced Settings (Optional)', 'woo-paypal-gateway'),
        'type' => 'title',
        'description' => '',
    ),
    'invoice_id_prefix' => array(
        'title' => __('Invoice ID Prefix', 'woo-paypal-gateway'),
        'type' => 'text',
        'description' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'woo-paypal-gateway'),
        'desc_tip' => true,
        'default' => 'WC-EC'
    ),
    'paymentaction' => array(
        'title' => __('Payment Action', 'woo-paypal-gateway'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'woo-paypal-gateway'),
        'default' => 'Sale',
        'desc_tip' => true,
        'options' => array(
            'Sale' => __('Sale', 'woo-paypal-gateway'),
            'Authorization' => __('Authorization', 'woo-paypal-gateway'),
            'Order' => __('Order', 'woo-paypal-gateway')
        ),
    ),
    'instant_payments' => array(
        'title' => __('Instant Payments', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('Require Instant Payment', 'woo-paypal-gateway'),
        'default' => 'no',
        'desc_tip' => true,
        'description' => __('If you enable this setting, PayPal will be instructed not to allow the buyer to use funding sources that take additional time to complete (for example, eChecks). Instead, the buyer will be required to use an instant funding source, such as an instant transfer, a credit/debit card, or PayPal Credit.', 'woo-paypal-gateway'),
    ),
    'enable_in_context_checkout_flow' => array(
        'title' => __('Enable In-Context Checkout flow', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => __('The enhanced PayPal Express Checkout with In-Context gives your customers a simplified checkout experience that keeps them local to your website throughout the payment authorization process and enables them to use their PayPal balance, bank account, or credit card to pay without sharing or entering any sensitive information on your site.', 'woo-paypal-gateway'),
        'default' => 'yes'
    ),
    'debug' => array(
        'title' => __('Debug', 'woo-paypal-gateway'),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable logging<code>%s</code>', 'woo-paypal-gateway'), WC_Log_Handler_File::get_log_file_path('paypal_express')),
        'default' => 'yes'
    )
);
