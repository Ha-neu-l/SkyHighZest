<?php

if (!defined('ABSPATH')) {
    exit;
}

class Woo_Paypal_Gateway_Express_Checkout_API_Handler_NVP {

    public $username;
    public $password;
    public $signature;
    public $sandbox = false;
    public $order_item;
    public $order_cart_data;
    public $gateway_calculation;
    public $payment_method;
    public $invoice_prefix;
    public $gateway;
    public $request;
    public $mask_request;
    public $response;
    public $logoimg;
    public $hdrimg;
    public $pagestyle;
    public $brandname;
    public $result;
    public $paypal_account_optional;
    public $landing_page;
    public $token;
    public $request_name;
    public $payerid;
    public $order;
    public $order_id;
    public $invoice_number;
    public $invoice_id_prefix;
    public $order_status;
    public $refund_amount;
    public $refund_reason;
    public $is_in_content;
    public $paymentaction;

    public function __construct($gateway) {
        $this->gateway = $gateway;
        if (!class_exists('Woo_Paypal_Gateway_Calculations')) {
            require_once( WPG_PLUGIN_DIR . '/includes/class-woo-paypal-gateway-calculations.php' );
        }
        $this->logoimg = $this->gateway->get_option('logoimg', '');
        $this->hdrimg = $this->gateway->get_option('hdrimg', '');
        $this->pagestyle = $this->gateway->get_option('pagestyle', '');
        $this->brandname = $this->gateway->get_option('brandname', '');
        $this->paypal_account_optional = $this->gateway->get_option('paypal_account_optional', 'no');
        $this->landing_page = $this->gateway->get_option('landing_page', 'Login');
        $this->sandbox = 'yes' === $this->gateway->get_option('sandbox', 'yes');
        $this->paymentaction = $this->gateway->get_option('paymentaction', 'Sale');
        $this->seller_protection = $this->gateway->get_option('seller_protection', 'disabled');
        if ($this->sandbox == true) {
            $this->username = $this->gateway->get_option('sandbox_api_username', false);
            $this->password = $this->gateway->get_option('sandbox_api_password', false);
            $this->signature = $this->gateway->get_option('sandbox_api_signature', false);
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
        } else {
            $this->username = $this->gateway->get_option('api_username', false);
            $this->password = $this->gateway->get_option('api_password', false);
            $this->signature = $this->gateway->get_option('api_signature', false);
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }
        $this->invoice_prefix = $this->gateway->get_option('invoice_id_prefix', false);
        $this->instant_payments = $this->gateway->get_option('instant_payments', 'no');
        $this->gateway_calculation = new Woo_Paypal_Gateway_Calculations($this->gateway);
    }

    public function wpg_set_express_checkout($is_in_content = null) {
        try {
            $this->is_in_content = $is_in_content;
            $this->request = $this->wpg_set_express_checkout_param();
            $this->request_name = 'set_express_checkout';
            Woo_PayPal_Gateway_Express_Checkout_NVP::log(sprintf('%s: %s', 'WooCoomerce version', WC_VERSION));
            Woo_PayPal_Gateway_Express_Checkout_NVP::log(sprintf('%s: %s', 'WPG Express Checkout version', WPG_PLUGIN_VERSION));
            $this->wpg_request();
            $this->wpg_response();
            $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_add_payment_method() {
        try {
            $this->token = wpg_get_session('TOKEN');
            if (!empty($this->token) && $this->token == true) {
                $this->request = $this->wpg_get_express_checkout_param();
                $this->wpg_request();
                $this->wpg_response();
                if (!empty($this->result['PAYERID'])) {
                    $this->request = array(
                        'METHOD' => 'CreateBillingAgreement',
                        'VERSION' => '120.0',
                        'USER' => $this->username,
                        'PWD' => $this->password,
                        'SIGNATURE' => $this->signature,
                        'TOKEN' => $this->token
                    );
                    $this->wpg_request();
                    $this->wpg_response();
                    $billing_agreement_id = !empty($this->result['BILLINGAGREEMENTID']) ? $this->result['BILLINGAGREEMENTID'] : '';
                    if (!empty($billing_agreement_id)) {
                        $customer_id = get_current_user_id();
                        update_user_meta($customer_id, '_billing_agreement_id', $billing_agreement_id);
                        $result = wpg_is_token_exist($this->gateway->id, $customer_id, $billing_agreement_id);
                        if (is_null($result)) {
                            $token = new WC_Payment_Token_CC();
                            $token->set_user_id($customer_id);
                            $token->set_token($billing_agreement_id);
                            $token->set_gateway_id($this->gateway->id);
                            $token->set_card_type('PayPal Billing Agreement');
                            $token->set_last4(substr($billing_agreement_id, -4));
                            $token->set_expiry_month(date('m'));
                            $token->set_expiry_year(date('Y', strtotime('+20 years')));
                            $token->save();
                        } else {
                            if (!empty($result->token_id)) {
                                $token = WC_Payment_Tokens::get($result->token_id);
                                $order->add_payment_token($token);
                            }
                        }
                        wpg_maybe_clear_session_data();
                        wc_add_notice(__('Payment method successfully added.', 'woo-paypal-gateway'));
                        wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                        exit();
                    } else {
                        wc_add_notice(__('Payment method successfully added.', 'woo-paypal-gateway'));
                        wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                        exit();
                    }
                } else {
                    wc_add_notice(__('Payment method successfully added.', 'woo-paypal-gateway'));
                    wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                    exit();
                }
            } else {
                wc_add_notice(__('Payment method successfully added.', 'woo-paypal-gateway'));
                wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                exit();
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_get_express_checkout_details() {
        try {
            $this->token = wpg_get_session('TOKEN');
            if (!empty($this->token) && $this->token == true) {
                $this->request = $this->wpg_get_express_checkout_param();
                $this->request_name = 'get_express_checkout_details';
                $this->wpg_request();
                $this->wpg_response();
                $this->wpg_response_handler();
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_set_express_checkout_for_add_payment_method() {
        try {
            $this->request = $this->wpg_set_express_checkout_for_add_payment_method_param();
            $this->request_name = 'set_express_checkout_for_add_payment_method';
            $this->wpg_request();
            $this->wpg_response();
            $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_set_express_checkout_for_add_payment_method_param() {
        $express_checkout_param = array(
            'METHOD' => 'SetExpressCheckout',
            'VERSION' => '120.0',
            'USER' => $this->username,
            'PWD' => $this->password,
            'SIGNATURE' => $this->signature,
            'LOGOIMG' => $this->logoimg,
            'HDRIMG' => $this->hdrimg,
            'PAGESTYLE' => $this->pagestyle,
            'BRANDNAME' => $this->brandname,
            'LOCALCODE' => Woo_Paypal_Gateway_Express_Checkout_Helper_NVP::get_button_locale_code(),
            'RETURNURL' => esc_url(esc_url(add_query_arg('wpg_express_checkout_action', 'wpg_add_payment_method', WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP')))),
            'CANCELURL' => esc_url(wc_get_account_endpoint_url('add-payment-method')),
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'AUTHORIZATION',
            'PAYMENTREQUEST_0_INSURANCEAMT' => '0.00',
            'PAYMENTREQUEST_0_HANDLINGAMT' => '0.00',
            'PAYMENTREQUEST_0_CUSTOM' => '',
            'PAYMENTREQUEST_0_INVNUM' => '',
            'PAYMENTREQUEST_0_CURRENCYCODE' => get_woocommerce_currency(),
            'PAYMENTREQUEST_0_AMT' => '0.00',
            'PAYMENTREQUEST_0_SHIPDISCAMT' => '0.00',
            'NOSHIPPING' => 1,
        );
        $express_checkout_param['L_BILLINGTYPE0'] = 'MerchantInitiatedBillingSingleAgreement';
        $express_checkout_param['L_BILLINGAGREEMENTDESCRIPTION0'] = $this->wpg_get_billing_agreement_description();
        $express_checkout_param['L_BILLINGAGREEMENTCUSTOM0'] = '';
        return $express_checkout_param;
    }

    public function wpg_do_express_checkout_payment($order) {
        try {
            $this->order = $order;
            $this->request = $this->wpg_do_express_checkout_payment_param();
            $this->request_name = 'do_express_checkout_payment';
            $this->wpg_request();
            $this->wpg_response();
            $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_create_billing_agreement($order) {
        try {
            $this->order = $order;
            $this->order_id = $this->order->get_id();
            $this->token = wpg_get_session('TOKEN');
            $this->payerid = wpg_get_session('PAYERID');
            $this->request = array(
                'METHOD' => 'CreateBillingAgreement',
                'VERSION' => '120.0',
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'TOKEN' => $this->token
            );
            $this->request_name = 'create_billing_agreement';
            $this->wpg_request();
            $this->wpg_response();
            $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_do_reference_transaction($order) {
        try {
            $this->order = $order;
            $this->request = $this->wpg_do_reference_transaction_param();
            $this->request_name = 'do_reference_transaction';
            $this->wpg_request();
            $this->wpg_response();
            $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_refund_transaction($order_id, $refund_amount, $refund_reason = '') {
        try {
            $this->order_id = $order_id;
            $this->order = wc_get_order($this->order_id);
            $this->refund_amount = $refund_amount;
            $this->refund_reason = $refund_reason;
            $this->request = $this->wpg_refund_transaction_param();
            $this->request_name = 'refund_transaction';
            $this->wpg_request();
            $this->wpg_response();
            return $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_refund_transaction_param() {
        try {
            $express_checkout_param = array(
                'METHOD' => 'RefundTransaction',
                'VERSION' => '120.0',
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'TRANSACTIONID' => $this->order->get_transaction_id(),
                'REFUNDTYPE' => $this->order->get_total() == $this->refund_amount ? 'Full' : 'Partial',
                'AMT' => wpg_number_format($this->refund_amount),
                'CURRENCYCODE' => $this->order->get_currency(),
                'NOTE' => $this->refund_reason,
            );
            return $express_checkout_param;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_do_express_checkout_payment_param() {
        try {
            $this->order_id = $this->order->get_id();
            $this->token = wpg_get_session('TOKEN');
            $this->payerid = wpg_get_session('PAYERID');
            $this->invoice_number = preg_replace("/[^a-zA-Z0-9]/", "", $this->order->get_order_number());
            $this->order_cart_data = $this->gateway_calculation->order_calculation($this->order_id);
            $express_checkout_param = array(
                'METHOD' => 'DoExpressCheckoutPayment',
                'VERSION' => '120.0',
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'BUTTONSOURCE' => 'mbjtechnolabs_SP',
                'TOKEN' => $this->token,
                'PAYERID' => $this->payerid,
                'LANDINGPAGE' => '',
                'SOLUTIONTYPE' => '',
                'PAYMENTREQUEST_0_PAYMENTACTION' => $this->paymentaction,
                'PAYMENTREQUEST_0_INSURANCEAMT' => '0.00',
                'PAYMENTREQUEST_0_HANDLINGAMT' => '0.00',
                'PAYMENTREQUEST_0_INVNUM' => $this->invoice_prefix . $this->invoice_number,
                'PAYMENTREQUEST_0_NOTIFYURL' => apply_filters('wpg_paypal_express_checkout_notify_url', add_query_arg('wpg_ipn_action', 'ipn', WC()->api_request_url('Woo_Paypal_Gateway_IPN_Handler'))),
                'PAYMENTREQUEST_0_CUSTOM' => apply_filters('wpg_ppec_custom_parameter', json_encode(array('order_id' => $this->order_id, 'order_key' => $this->order->get_order_key()))),
                'PAYMENTREQUEST_0_CURRENCYCODE' => $this->order->get_currency(),
                'PAYMENTREQUEST_0_AMT' => wpg_number_format($this->order->get_total()),
                'PAYMENTREQUEST_0_SHIPDISCAMT' => '0.00',
                'NOSHIPPING' => 0
            );
            $express_checkout_param['PAYMENTREQUEST_0_ITEMAMT'] = $this->order_cart_data['itemamt'];
            $express_checkout_param['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->order_cart_data['shippingamt'];
            $express_checkout_param['PAYMENTREQUEST_0_TAXAMT'] = $this->order_cart_data['taxamt'];
            if (!empty($this->order_cart_data['order_items'])) {
                foreach ($this->order_cart_data['order_items'] as $key => $values) {
                    $line_item_params = array(
                        'L_PAYMENTREQUEST_0_NAME' . $key => $values['name'],
                        'L_PAYMENTREQUEST_0_DESC' . $key => !empty($values['desc']) ? strip_tags($values['desc']) : '',
                        'L_PAYMENTREQUEST_0_QTY' . $key => $values['qty'],
                        'L_PAYMENTREQUEST_0_AMT' . $key => $values['amt'],
                        'L_PAYMENTREQUEST_0_NUMBER' . $key => $values['number']
                    );
                    $express_checkout_param = array_merge($express_checkout_param, $line_item_params);
                }
            }
            return $express_checkout_param;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_do_reference_transaction_param() {
        try {
            $this->order_id = $this->order->get_id();
            if (!empty($_POST['wc-wpg_paypal_express-payment-token']) && $_POST['wc-wpg_paypal_express-payment-token'] !== 'new') {
                $token_id = $_POST['wc-wpg_paypal_express-payment-token'];
                $token = WC_Payment_Tokens::get($token_id);
                $referenceid = $token->get_token();
            } else {
                $referenceid = $this->order->get_meta('_payment_tokens_id');
            }
            $this->invoice_number = preg_replace("/[^a-zA-Z0-9]/", "", $this->order->get_order_number());
            $this->order_cart_data = $this->gateway_calculation->order_calculation($this->order_id);
            $express_checkout_param = array(
                'METHOD' => 'DoReferenceTransaction',
                'VERSION' => '120.0',
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'REFERENCEID' => $referenceid,
                'PAYMENTACTION' => $this->paymentaction,
                'INSURANCEAMT' => '0.00',
                'HANDLINGAMT' => '0.00',
                'INVNUM' => $this->invoice_prefix . $this->invoice_number,
                'NOTIFYURL' => apply_filters('wpg_paypal_express_checkout_notify_url', add_query_arg('wpg_ipn_action', 'ipn', WC()->api_request_url('Woo_Paypal_Gateway_IPN_Handler'))),
                'CUSTOM' => apply_filters('wpg_ppec_custom_parameter', json_encode(array('order_id' => $this->order_id, 'order_key' => $this->order->get_order_key()))),
                'CURRENCYCODE' => $this->order->get_currency(),
                'AMT' => wpg_number_format($this->order->get_total()),
                'SHIPDISCAMT' => '0.00',
                'NOSHIPPING' => 0
            );
            $express_checkout_param['ITEMAMT'] = $this->order_cart_data['itemamt'];
            $express_checkout_param['SHIPPINGAMT'] = $this->order_cart_data['shippingamt'];
            $express_checkout_param['TAXAMT'] = $this->order_cart_data['taxamt'];
            if (!empty($this->order_cart_data['order_items'])) {
                foreach ($this->order_cart_data['order_items'] as $key => $values) {
                    $line_item_params = array(
                        'L_NAME' . $key => $values['name'],
                        'L_DESC' . $key => !empty($values['desc']) ? strip_tags($values['desc']) : '',
                        'L_QTY' . $key => $values['qty'],
                        'L_AMT' . $key => $values['amt'],
                        'L_NUMBER' . $key => $values['number']
                    );
                    $express_checkout_param = array_merge($express_checkout_param, $line_item_params);
                }
            }
            return $express_checkout_param;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_get_express_checkout_param() {
        try {
            $express_checkout_param = array(
                'METHOD' => 'GetExpressCheckoutDetails',
                'VERSION' => '120.0',
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'TOKEN' => $this->token
            );
            return $express_checkout_param;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_set_express_checkout_param() {
        try {
            $this->order_cart_data = $this->gateway_calculation->cart_calculation();
            $express_checkout_param = array(
                'METHOD' => 'SetExpressCheckout',
                'VERSION' => '120.0',
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'LOGOIMG' => $this->logoimg,
                'HDRIMG' => $this->hdrimg,
                'PAGESTYLE' => $this->pagestyle,
                'BRANDNAME' => $this->brandname,
                'LOCALCODE' => Woo_Paypal_Gateway_Express_Checkout_Helper_NVP::get_button_locale_code(),
                'RETURNURL' => esc_url(add_query_arg('wpg_express_checkout_action', 'wpg_get_express_checkout_details', WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP'))),
                'CANCELURL' => esc_url(add_query_arg('wpg_express_checkout_action', 'wpg_cancel_url', WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP'))),
                'LANDINGPAGE' => '',
                'SOLUTIONTYPE' => '',
                'PAYMENTREQUEST_0_PAYMENTACTION' => $this->paymentaction,
                'PAYMENTREQUEST_0_INSURANCEAMT' => '0.00',
                'PAYMENTREQUEST_0_HANDLINGAMT' => '0.00',
                'PAYMENTREQUEST_0_CUSTOM' => '',
                'PAYMENTREQUEST_0_INVNUM' => '',
                'PAYMENTREQUEST_0_CURRENCYCODE' => get_woocommerce_currency(),
                'PAYMENTREQUEST_0_SHIPDISCAMT' => '0.00',
                'NOSHIPPING' => 0,
            );

            if ('yes' === $this->instant_payments && 'Sale' === $this->paymentaction) {
                $express_checkout_param['PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD'] = 'InstantPaymentOnly';
            }
            $usePayPalCredit = (!empty($_GET['is_wpg_cc']) && $_GET['is_wpg_cc'] == 'yes') ? true : false;
            if ($usePayPalCredit) {
                $express_checkout_param['USERSELECTEDFUNDINGSOURCE'] = 'Finance';
            }
            if (!empty($this->gateway->landing_page)) {
                $express_checkout_param['LANDINGPAGE'] = $this->gateway->landing_page;
            }
            if (strtolower($this->paypal_account_optional) == 'yes') {
                $express_checkout_param['SOLUTIONTYPE'] = 'Sole';
            }
            $start_from_checkout_page = (!empty($_GET['start_from']) && $_GET['start_from'] == 'checkout_page') ? true : false;
            if ($start_from_checkout_page == true) {
                // $express_checkout_param['ADDROVERRIDE'] = '1';
            }
            if (is_cart_contains_pre_order() == true) {
                $express_checkout_param['PAYMENTREQUEST_0_ITEMAMT'] = '0.00';
                $express_checkout_param['PAYMENTREQUEST_0_SHIPPINGAMT'] = '0.00';
                $express_checkout_param['PAYMENTREQUEST_0_TAXAMT'] = '0.00';
                $express_checkout_param['PAYMENTREQUEST_0_AMT'] = '0.00';
            } else {
                if (!empty($this->order_cart_data['order_items'])) {
                    foreach ($this->order_cart_data['order_items'] as $key => $values) {
                        $line_item_params = array(
                            'L_PAYMENTREQUEST_0_NAME' . $key => $values['name'],
                            'L_PAYMENTREQUEST_0_DESC' . $key => !empty($values['desc']) ? strip_tags($values['desc']) : '',
                            'L_PAYMENTREQUEST_0_QTY' . $key => $values['qty'],
                            'L_PAYMENTREQUEST_0_AMT' . $key => $values['amt'],
                            'L_PAYMENTREQUEST_0_NUMBER' . $key => $values['number']
                        );
                        $express_checkout_param = array_merge($express_checkout_param, $line_item_params);
                    }
                }
                $express_checkout_param['PAYMENTREQUEST_0_ITEMAMT'] = $this->order_cart_data['itemamt'];
                $express_checkout_param['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->order_cart_data['shippingamt'];
                $express_checkout_param['PAYMENTREQUEST_0_TAXAMT'] = $this->order_cart_data['taxamt'];
                $express_checkout_param['PAYMENTREQUEST_0_AMT'] = wpg_number_format(WC()->cart->total);
            }
            $post_data = wpg_get_session('post_data');
            $is_save_payment_method = false;
            if (!empty($post_data['wc-wpg_paypal_express-new-payment-method']) && $post_data['wc-wpg_paypal_express-new-payment-method'] == true) {
                $is_save_payment_method = true;
            }
            if (is_cart_contains_subscription() == true || is_cart_contains_pre_order() == true || $is_save_payment_method == true) {
                $express_checkout_param['L_BILLINGTYPE0'] = 'MerchantInitiatedBillingSingleAgreement';
                $express_checkout_param['L_BILLINGAGREEMENTDESCRIPTION0'] = $this->wpg_get_billing_agreement_description();
                $express_checkout_param['L_BILLINGAGREEMENTCUSTOM0'] = '';
            }
            return $express_checkout_param;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_response_handler() {
        try {
            if ($this->wpg_is_response_success_or_successwithwarning() == true) {
                switch ($this->request_name) {
                    case 'set_express_checkout':
                        if (!empty($this->result['TOKEN'])) {
                            wpg_set_session('TOKEN', $this->result['TOKEN']);
                            $this->wpg_redirect_action($this->PAYPAL_URL . $this->result['TOKEN']);
                        }
                        break;
                    case 'set_express_checkout_for_add_payment_method':
                        if (!empty($this->result['TOKEN'])) {
                            wpg_set_session('TOKEN', $this->result['TOKEN']);
                            $this->wpg_redirect_action($this->PAYPAL_URL . $this->result['TOKEN']);
                        }
                        break;
                    case 'get_express_checkout_details':
                        if (!empty($this->result['PAYERID'])) {
                            wpg_set_session('PAYERID', $this->result['PAYERID']);
                            wpg_set_session('GetExpressCheckoutDetails', $this->result);
                            $this->wpg_get_shipping_address();
                            $this->wpg_redirect_action(wc_get_checkout_url());
                        }
                        break;
                    case 'do_express_checkout_payment':
                        if (!empty($this->result['PAYMENTINFO_0_TRANSACTIONID'])) {
                            $this->order->add_order_note('Transaction ID: ' . $this->result['PAYMENTINFO_0_TRANSACTIONID']);
                            $this->wpg_seller_protection_handling($this->order_id);
                            $this->wpg_save_billing_agreement($this->order_id);
                            $this->wpg_add_order_note($this->order);
                            $this->wpg_update_payment_status_by_paypal_responce($this->order_id, $this->result);
                            WC()->cart->empty_cart();
                            wpg_maybe_clear_session_data();
                            $this->wpg_redirect_action($this->gateway->get_return_url($this->order));
                        }
                        break;
                    case 'do_reference_transaction':
                        if (!empty($this->result['TRANSACTIONID'])) {
                            $this->order->add_order_note('Transaction ID: ' . $this->result['TRANSACTIONID']);
                            $this->wpg_seller_protection_handling($this->order_id);
                            $this->wpg_save_billing_agreement($this->order_id);
                            $this->wpg_update_payment_status_by_paypal_responce($this->order_id, $this->result);
                            if (isset(WC()->cart) && sizeof(WC()->cart->get_cart()) > 0) {
                                WC()->cart->empty_cart();
                                wpg_maybe_clear_session_data();
                                $this->wpg_redirect_action($this->gateway->get_return_url($this->order));
                            }
                        }
                        break;
                    case 'create_billing_agreement':
                        if (!empty($this->result['BILLINGAGREEMENTID'])) {
                            $this->order->add_order_note('BILLINGAGREEMENTID ID: ' . $this->result['BILLINGAGREEMENTID']);
                            $this->wpg_save_billing_agreement($this->order_id);
                            $this->wpg_add_order_note($this->order);
                            if (is_cart_contains_pre_order() == false) {
                                $this->order->payment_complete($this->result['BILLINGAGREEMENTID']);
                                WC()->cart->empty_cart();
                                wpg_maybe_clear_session_data();
                                $this->wpg_redirect_action($this->gateway->get_return_url($this->order));
                            }
                        }
                        break;

                    case 'refund_transaction':
                        if (!empty($this->result['REFUNDTRANSACTIONID'])) {
                            $this->order->add_order_note('Refund Transaction ID: ' . $this->result['REFUNDTRANSACTIONID']);
                            if (!empty($this->refund_reason)) {
                                $this->order->add_order_note('Refund reason: ' . $this->refund_reason);
                            }
                            return true;
                        }
                        break;
                }
            } else {
                if (function_exists('wc_add_notice')) {
                    wpg_maybe_clear_session_data();
                    $ERRORCODE = !empty($this->result['L_ERRORCODE0']) ? $this->result['L_ERRORCODE0'] : '';
                    if (!empty($this->result['L_LONGMESSAGE0'])) {
                        $error = $this->result['L_LONGMESSAGE0'];
                    } elseif (!empty($this->result['L_SHORTMESSAGE0'])) {
                        $error = $this->result['L_SHORTMESSAGE0'];
                    } else {
                        $error = '';
                    }
                    wc_add_notice('Error:' . $ERRORCODE . '  ' . $MESSAGE, 'error');
                }
                $this->wpg_redirect_action(wc_get_cart_url());
            }
        } catch (Exception $ex) {
            
        }
    }

    public function wpg_request() {
        try {
            $body = apply_filters('wpg_request_param', $this->request);
            $args = array(
                'method' => 'POST',
                'body' => $body,
                'user-agent' => 'wpg_gateway',
                'httpversion' => '1.1',
                'timeout' => 60,
            );

            Woo_PayPal_Gateway_Express_Checkout_NVP::log(sprintf('%s request: %s', $this->request_name, print_r($this->wpg_mask_request_param(), true)));
            $this->response = wp_safe_remote_post($this->API_Endpoint, $args);
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_get_shipping_address() {
        try {
            $GetExpressCheckoutDetails = wpg_get_session('GetExpressCheckoutDetails');
            $shipping_address = array();
            if (!empty($GetExpressCheckoutDetails) && $GetExpressCheckoutDetails == true) {
                $shipping_address['first_name'] = !empty($GetExpressCheckoutDetails['FIRSTNAME']) ? $GetExpressCheckoutDetails['FIRSTNAME'] : '';
                $shipping_address['last_name'] = !empty($GetExpressCheckoutDetails['LASTNAME']) ? $GetExpressCheckoutDetails['LASTNAME'] : '';
                $shipping_address['email'] = !empty($GetExpressCheckoutDetails['EMAIL']) ? $GetExpressCheckoutDetails['EMAIL'] : '';
                $shipping_address['address_1'] = !empty($GetExpressCheckoutDetails['SHIPTOSTREET']) ? $GetExpressCheckoutDetails['SHIPTOSTREET'] : '';
                $shipping_address['city'] = !empty($GetExpressCheckoutDetails['SHIPTOCITY']) ? $GetExpressCheckoutDetails['SHIPTOCITY'] : '';
                $shipping_address['postcode'] = !empty($GetExpressCheckoutDetails['SHIPTOZIP']) ? $GetExpressCheckoutDetails['SHIPTOZIP'] : '';
                $shipping_address['country'] = !empty($GetExpressCheckoutDetails['SHIPTOCOUNTRYCODE']) ? $GetExpressCheckoutDetails['SHIPTOCOUNTRYCODE'] : '';
                $state = !empty($GetExpressCheckoutDetails['SHIPTOSTATE']) ? $GetExpressCheckoutDetails['SHIPTOSTATE'] : '';
                if (!empty($shipping_address['country']) && !empty($state)) {
                    $shipping_address['state'] = $this->wpg_get_state_code($shipping_address['country'], $state);
                }
                wpg_set_session('wpg_express_checkout_shipping_address', $shipping_address);
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_get_state_code($country, $state) {
        try {
            $valid_states = WC()->countries->get_states($country);
            if (!empty($valid_states) && is_array($valid_states)) {
                $valid_state_values = array_flip(array_map('strtolower', $valid_states));
                if (isset($valid_state_values[strtolower($state)])) {
                    $state_value = $valid_state_values[strtolower($state)];
                    return $state_value;
                }
            } else {
                return $state;
            }
            if (!empty($valid_states) && is_array($valid_states) && sizeof($valid_states) > 0) {
                if (!in_array(strtoupper($state), array_keys($valid_states))) {
                    return false;
                } else {
                    return strtoupper($state);
                }
            }
            return $state;
        } catch (Exception $ex) {
            
        }
    }

    public function wpg_redirect_action($url) {
        try {
            if (!empty($url)) {
                if (!is_ajax()) {
                    wp_redirect($url);
                    exit;
                } else {
                    if ($this->request_name == 'do_express_checkout_payment' || $this->is_in_content == false) {
                        wp_send_json(array(
                            'result' => 'success',
                            'redirect' => add_query_arg('utm_nooverride', '1', $url)
                        ));
                        exit();
                    } else {
                        wp_send_json(array('url' => $url));
                        exit();
                    }
                }
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_mask_request_param() {
        try {
            $this->mask_request = $this->request;
            $this->mask_request['USER'] = '****';
            $this->mask_request['PWD'] = '****';
            $this->mask_request['SIGNATURE'] = '****';
            return $this->mask_request;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_response() {
        try {
            if (is_wp_error($this->response)) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice(sprintf(__('An error occurred while trying to connect to PayPal: %s', 'woo-paypal-gateway'), $this->response->get_error_message()), 'error');
                }
                Woo_PayPal_Gateway_Express_Checkout_NVP::log(sprintf(__('An error occurred while trying to connect to PayPal: %s', 'woo-paypal-gateway'), $this->response->get_error_message()));
                throw new Exception(sprintf(__('An error occurred while trying to connect to PayPal: %s', 'woo-paypal-gateway'), $this->response->get_error_message()), 3);
            }
            if (empty($this->response['body'])) {
                Woo_PayPal_Gateway_Express_Checkout_NVP::log('Empty response!');
                throw new Exception(__('Empty Paypal response.', 'woo-paypal-gateway'));
            }
            parse_str(wp_remote_retrieve_body($this->response), $this->result);
            if (!array_key_exists('ACK', $this->result)) {
                Woo_PayPal_Gateway_Express_Checkout_NVP::log(sprintf('%s response: %s', $this->request_name, print_r($this->result, true)));
                throw new Exception(__('Malformed response received from PayPal', 'woo-paypal-gateway'), 3);
            } else {
                Woo_PayPal_Gateway_Express_Checkout_NVP::log(sprintf('%s response: %s', $this->request_name, print_r($this->result, true)));
            }
            return $this->result;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
            $this->wpg_redirect_action(wc_get_cart_url());
        }
    }

    public function wpg_is_response_success_or_successwithwarning() {
        try {
            if (!empty($this->result['ACK']) && strtoupper($this->result['ACK']) == 'SUCCESS' || strtoupper($this->result['ACK']) == "SUCCESSWITHWARNING") {
                return true;
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_update_payment_status_by_paypal_responce($orderid, $result) {
        try {
            $order = wc_get_order($orderid);
            if (!empty($result['PAYMENTINFO_0_PAYMENTSTATUS'])) {
                $payment_status = $result['PAYMENTINFO_0_PAYMENTSTATUS'];
            } elseif (!empty($result['PAYMENTSTATUS'])) {
                $payment_status = $result['PAYMENTSTATUS'];
            }
            if (!empty($result['PAYMENTINFO_0_TRANSACTIONTYPE'])) {
                $transaction_type = $result['PAYMENTINFO_0_TRANSACTIONTYPE'];
            } elseif (!empty($result['TRANSACTIONTYPE'])) {
                $transaction_type = $result['TRANSACTIONTYPE'];
            }
            if (!empty($result['PAYMENTINFO_0_TRANSACTIONID'])) {
                $transaction_id = $result['PAYMENTINFO_0_TRANSACTIONID'];
            } elseif (!empty($result['TRANSACTIONID'])) {
                $transaction_id = $result['TRANSACTIONID'];
            } elseif (!empty($result['BILLINGAGREEMENTID'])) {
                $transaction_id = $result['BILLINGAGREEMENTID'];
            }
            if (!empty($result['PAYMENTINFO_0_PENDINGREASON'])) {
                $pending_reason = $result['PAYMENTINFO_0_PENDINGREASON'];
            } elseif (!empty($result['PENDINGREASON'])) {
                $pending_reason = $result['PENDINGREASON'];
            }
            switch (strtolower($payment_status)) :
                case 'completed' :
                    $this->order_status = $this->order->get_status();
                    if ($this->order_status == 'completed') {
                        break;
                    }
                    if (!in_array(strtolower($transaction_type), array('merchtpmt', 'cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money'))) {
                        break;
                    }
                    $order->add_order_note(__('Payment Status Completed via Express Checkout', 'woo-paypal-gateway'));
                    $order->payment_complete($transaction_id);
                    break;
                case 'pending' :
                    if (!in_array(strtolower($transaction_type), array('merchtpmt', 'cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money', 'expresscheckout'))) {
                        break;
                    }
                    switch (strtolower($pending_reason)) {
                        case 'address':
                            $pending_reason = __('Address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.', 'woo-paypal-gateway');
                            break;
                        case 'authorization':
                            $pending_reason = __('Authorization: The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'woo-paypal-gateway');
                            break;
                        case 'echeck':
                            $pending_reason = __('eCheck: The payment is pending because it was made by an eCheck that has not yet cleared.', 'woo-paypal-gateway');
                            break;
                        case 'intl':
                            $pending_reason = __('intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'woo-paypal-gateway');
                            break;
                        case 'multicurrency':
                        case 'multi-currency':
                            $pending_reason = __('Multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'woo-paypal-gateway');
                            break;
                        case 'order':
                            $pending_reason = __('Order: The payment is pending because it is part of an order that has been authorized but not settled.', 'woo-paypal-gateway');
                            break;
                        case 'paymentreview':
                            $pending_reason = __('Payment Review: The payment is pending while it is being reviewed by PayPal for risk.', 'woo-paypal-gateway');
                            break;
                        case 'unilateral':
                            $pending_reason = __('Unilateral: The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'woo-paypal-gateway');
                            break;
                        case 'verify':
                            $pending_reason = __('Verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'woo-paypal-gateway');
                            break;
                        case 'other':
                            $pending_reason = __('Other: For more information, contact PayPal customer service.', 'woo-paypal-gateway');
                            break;
                        case 'none':
                        default:
                            $pending_reason = __('No pending reason provided.', 'woo-paypal-gateway');
                            break;
                    }
                    $order->add_order_note(sprintf(__('Payment via Express Checkout Pending. PayPal reason: %s.', 'woo-paypal-gateway'), $pending_reason));
                    if (!empty($result['L_LONGMESSAGE0'])) {
                        $error = $result['L_LONGMESSAGE0'];
                    } elseif (!empty($result['L_SHORTMESSAGE0'])) {
                        $error = $result['L_SHORTMESSAGE0'];
                    } else {
                        $error = '';
                    }
                    if (!empty($error)) {
                        $order->add_order_note(sprintf(__('Express Checkout Error: %s.', 'woo-paypal-gateway'), $error));
                    }
                    $order->update_status('on-hold');
                    wc_maybe_reduce_stock_levels($order->get_id());
                    break;
                case 'denied' :
                case 'expired' :
                case 'failed' :
                case 'voided' :
                    $order->update_status('failed', sprintf(__('Payment %s via Express Checkout.', 'woo-paypal-gateway'), strtolower($payment_status)));
                    break;
                default:
                    break;
            endswitch;
            return;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_add_order_note($order) {
        try {
            $paypal_express_checkout = wpg_get_session('GetExpressCheckoutDetails');
            if (!empty($paypal_express_checkout['PAYERSTATUS'])) {
                $order->add_order_note(sprintf(__('Payer Status: %s', 'woo-paypal-gateway'), '<strong>' . $paypal_express_checkout['PAYERSTATUS'] . '</strong>'));
            }
            if (!empty($paypal_express_checkout['ADDRESSSTATUS'])) {
                $order->add_order_note(sprintf(__('Address Status: %s', 'woo-paypal-gateway'), '<strong>' . $paypal_express_checkout['ADDRESSSTATUS'] . '</strong>'));
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log($ex->getMessage());
        }
    }

    public function wpg_get_billing_agreement_description() {
        /* translators: placeholder is blogname */
        $description = sprintf(_x('Orders with %s', 'data sent to PayPal', 'woocommerce-subscriptions'), get_bloginfo('name'));

        if (strlen($description) > 127) {
            $description = substr($description, 0, 124) . '...';
        }

        return html_entity_decode($description, ENT_NOQUOTES, 'UTF-8');
    }

    public function wpg_sellerprotection_should_cancel_order() {
        $protection_eligibility = isset($this->result['PAYMENTINFO_0_PROTECTIONELIGIBILITY']) ? $this->result['PAYMENTINFO_0_PROTECTIONELIGIBILITY'] : 'ERROR!';
        $txn_id = isset($this->result['PAYMENTINFO_0_TRANSACTIONID']) ? $this->result['PAYMENTINFO_0_TRANSACTIONID'] : '';
        if (!empty($txn_id)) {
            if (!empty($this->result['TRANSACTIONID'])) {
                $txn_id = $this->result['TRANSACTIONID'];
            }
        }
        switch ($this->seller_protection) {
            case 'no_seller_protection':
                if ($protection_eligibility != 'Eligible' && $protection_eligibility != 'PartiallyEligible') {
                    Woo_PayPal_Gateway_Express_Checkout_NVP::log('Transaction ' . $txn_id . ' is BAD. Setting: no_seller_protection, Response: ' . $protection_eligibility);
                    return true;
                }
                Woo_PayPal_Gateway_Express_Checkout_NVP::log('Transaction ' . $txn_id . ' is OK. Setting: no_seller_protection, Response: ' . $protection_eligibility);
                return false;
            case 'no_unauthorized_payment_protection':
                if ($protection_eligibility != 'Eligible') {
                    Woo_PayPal_Gateway_Express_Checkout_NVP::log('Transaction ' . $txn_id . ' is BAD. Setting: no_unauthorized_payment_protection, Response: ' . $protection_eligibility);
                    return true;
                }
                Woo_PayPal_Gateway_Express_Checkout_NVP::log('Transaction ' . $txn_id . ' is OK. Setting: no_unauthorized_payment_protection, Response: ' . $protection_eligibility);
                return false;
            case 'disabled':
                Woo_PayPal_Gateway_Express_Checkout_NVP::log('Transaction ' . $txn_id . ' is OK. Setting: disabled, Response: ' . $protection_eligibility);
                return false;
            default:
                Woo_PayPal_Gateway_Express_Checkout_NVP::log('ERROR! seller_protection setting for ' . $this->gateway->method_title . ' is not valid!');
                return true;
        }
    }

    public function wpg_seller_protection_handling($order_id) {
        $order = wc_get_order($order_id);
        if ($this->wpg_sellerprotection_should_cancel_order()) {
            Woo_PayPal_Gateway_Express_Checkout_NVP::log('Order ' . $order_id . ' (' . $this->result['PAYMENTINFO_0_TRANSACTIONID'] . ') did not meet our Seller Protection requirements. Cancelling and refunding order.');
            $order->add_order_note(__('Transaction did not meet our Seller Protection requirements. Cancelling and refunding order.', 'woo-paypal-gateway'));
            $this->gateway->process_refund($order_id, $order->get_total(), __('There was a problem processing your order. Please contact customer support.', 'woo-paypal-gateway'));
            $order->update_status('cancelled');
            $this->wpg_redirect_action(wc_get_cart_url());
        }
    }

    public function wpg_save_billing_agreement($order_id) {
        $order = wc_get_order($order_id);
        $billing_agreement_id = !empty($this->result['BILLINGAGREEMENTID']) ? $this->result['BILLINGAGREEMENTID'] : '';
        if (!empty($billing_agreement_id)) {
            $order->update_meta_data('BILLINGAGREEMENTID', $billing_agreement_id);
            $order->save_meta_data();
            if (0 != $order->get_user_id()) {
                $customer_id = $order->get_user_id();
            } else {
                $customer_id = get_current_user_id();
            }
            update_user_meta($customer_id, '_billing_agreement_id', $billing_agreement_id);
            $result = wpg_is_token_exist($this->gateway->id, $customer_id, $billing_agreement_id);
            if (is_null($result)) {
                $token = new WC_Payment_Token_CC();
                $token->set_user_id($customer_id);
                $token->set_token($billing_agreement_id);
                $token->set_gateway_id($this->gateway->id);
                $token->set_card_type('PayPal Billing Agreement');
                $token->set_last4(substr($billing_agreement_id, -4));
                $token->set_expiry_month(date('m'));
                $token->set_expiry_year(date('Y', strtotime('+20 years')));
                $save_result = $token->save();
                if ($save_result) {
                    $order->add_payment_token($token);
                }
            } else {
                if (!empty($result->token_id)) {
                    $token = WC_Payment_Tokens::get($result->token_id);
                    $order->add_payment_token($token);
                }
            }
            $this->save_payment_token($order, $billing_agreement_id);
        }
    }

    public function save_payment_token($order, $payment_tokens_id) {
        $order_id = $order->get_id();
        if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order_id)) {
            $subscriptions = wcs_get_subscriptions_for_order($order_id);
        } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order_id)) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);
        } else {
            $subscriptions = array();
        }
        if (!empty($subscriptions)) {
            foreach ($subscriptions as $subscription) {
                $subscription->update_meta_data('_payment_tokens_id', $payment_tokens_id);
                $subscription->save_meta_data();
            }
        } else {
            $order->update_meta_data('_payment_tokens_id', $payment_tokens_id);
            $order->save_meta_data();
        }
    }

    public function wpg_cancel_url() {
        wpg_maybe_clear_session_data();
        $this->wpg_redirect_action(wc_get_cart_url());
    }
}
