<?php

if (!defined('ABSPATH')) {
    exit;
}

class Woo_PayPal_Gateway_PayPal_Pro_API_Handler {

    public $gateway;
    public $gateway_calculation;
    public $request;
    public $request_name;
    public $response;
    public $mask_request;
    public $order_item;
    public $result;
    public $order;
    public $order_id;
    public $invoice_number;
    public $invoice_id_prefix;
    public $order_status;
    public $refund_amount;
    public $refund_reason;
    public $is_in_content;
    public $paymentaction;
    public $card;
    public $API_Endpoint;
    public $transaction_id;

    public function __construct($gateway) {
        $this->gateway = $gateway;
        $this->API_Endpoint = $this->gateway->testmode ? $this->gateway->testurl : $this->gateway->liveurl;
        $this->seller_protection = $this->gateway->get_option('seller_protection', 'disabled');
        if (!class_exists('Woo_Paypal_Gateway_Calculations')) {
            require_once( WPG_PLUGIN_DIR . '/includes/class-woo-paypal-gateway-calculations.php' );
        }
        $this->gateway_calculation = new Woo_Paypal_Gateway_Calculations($this->gateway);
    }

    public function wpg_do_direct_payment_request_param() {
        try {
            $this->order_id = $this->order->get_id();
            $this->invoice_number = preg_replace("/[^a-zA-Z0-9]/", "", $this->order->get_order_number());
            $this->order_cart_data = $this->gateway_calculation->order_calculation($this->order_id);
            $post_data = array(
                'VERSION' => $this->gateway->api_version,
                'SIGNATURE' => $this->gateway->api_signature,
                'USER' => $this->gateway->api_username,
                'PWD' => $this->gateway->api_password,
                'METHOD' => 'DoDirectPayment',
                'PAYMENTACTION' => $this->gateway->paymentaction,
                'IPADDRESS' => $this->get_user_ip(),
                'AMT' => wpg_number_format($this->order->get_total()),
                'INVNUM' => $this->gateway->invoice_prefix . $this->invoice_number,
                'CURRENCYCODE' => $this->order->get_currency(),
                'CREDITCARDTYPE' => $this->card->type,
                'ACCT' => $this->card->number,
                'EXPDATE' => $this->card->exp_month . $this->card->exp_year,
                'CVV2' => $this->card->cvc,
                'EMAIL' => $this->order->get_billing_email(),
                'FIRSTNAME' => $this->order->get_billing_first_name(),
                'LASTNAME' => $this->order->get_billing_last_name(),
                'STREET' => $this->order->get_billing_address_1() . ' ' . $this->order->get_billing_address_2(),
                'CITY' => $this->order->get_billing_city(),
                'STATE' => $this->order->get_billing_state(),
                'ZIP' => $this->order->get_billing_postcode(),
                'COUNTRYCODE' => $this->order->get_billing_country(),
                'SHIPTONAME' => $this->order->get_shipping_first_name() . ' ' . $this->order->get_shipping_last_name(),
                'SHIPTOSTREET' => $this->order->get_shipping_address_1(),
                'SHIPTOSTREET2' => $this->order->get_shipping_address_2(),
                'SHIPTOCITY' => $this->order->get_shipping_city(),
                'SHIPTOSTATE' => $this->order->get_shipping_state(),
                'SHIPTOCOUNTRYCODE' => $this->order->get_shipping_country(),
                'SHIPTOZIP' => $this->order->get_shipping_postcode(),
                'CUSTOM' => apply_filters('wpg_paypal_pro_custom_parameter', json_encode(array('order_id' => $this->order->get_id(), 'order_key' => $this->order->get_order_key())), $this->order),
                'NOTIFYURL' => apply_filters('wpg_paypal_pro_notify_url', add_query_arg('wpg_ipn_action', 'ipn', WC()->api_request_url('Woo_Paypal_Gateway_IPN_Handler'))),
                'BUTTONSOURCE' => 'mbjtechnolabs_SP',
            );
            if ($this->gateway->soft_descriptor) {
                $post_data['SOFTDESCRIPTOR'] = $this->gateway->soft_descriptor;
            }
            $post_data['ITEMAMT'] = $this->order_cart_data['itemamt'];
            $post_data['SHIPPINGAMT'] = $this->order_cart_data['shippingamt'];
            $post_data['TAXAMT'] = $this->order_cart_data['taxamt'];
            if ($this->gateway->send_items) {
                if (!empty($this->order_cart_data['order_items'])) {
                    foreach ($this->order_cart_data['order_items'] as $key => $values) {
                        $line_item_params = array(
                            'L_NAME' . $key => $values['name'],
                            'L_DESC' . $key => !empty($values['desc']) ? strip_tags($values['desc']) : '',
                            'L_QTY' . $key => $values['qty'],
                            'L_AMT' . $key => $values['amt'],
                            'L_NUMBER' . $key => $values['number']
                        );
                        $post_data = array_merge($post_data, $line_item_params);
                    }
                }
            }
            return $post_data;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function wpg_request() {
        try {
            $body = apply_filters('wpg_request_param', $this->request);
            $args = array(
                'method' => 'POST',
                'body' => $body,
                'user-agent' => 'wpg_gateway',
                'headers' => array(
                    'PAYPAL-NVP' => 'Y',
                ),
                'httpversion' => '1.1',
                'timeout' => 70,
            );
            Woo_PayPal_Gateway_PayPal_Pro::log(sprintf('%s request: %s', $this->request_name, print_r($this->wpg_mask_request_param(), true)));
            $this->response = wp_safe_remote_post($this->API_Endpoint, $args);
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function wpg_mask_request_param() {
        try {
            $this->mask_request = $this->request;
            $this->mask_request['USER'] = str_repeat('*', strlen($this->request['USER']));
            $this->mask_request['PWD'] = str_repeat('*', strlen($this->request['PWD']));
            $this->mask_request['SIGNATURE'] = str_repeat('*', strlen($this->request['SIGNATURE']));
            if (!empty($this->request['ACCT'])) {
                $this->mask_request['ACCT'] = str_repeat('*', strlen($this->request['ACCT']));
                $this->mask_request['EXPDATE'] = str_repeat('*', strlen($this->request['EXPDATE']));
                $this->mask_request['CVV2'] = str_repeat('*', strlen($this->request['CVV2']));
            }
            return $this->mask_request;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function wpg_response() {
        try {
            if (is_wp_error($this->response)) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice(sprintf(__('An error occurred while trying to connect to PayPal: %s', 'woo-paypal-gateway'), $this->response->get_error_message()), 'error');
                }
                Woo_PayPal_Gateway_PayPal_Pro::log(sprintf(__('An error occurred while trying to connect to PayPal: %s', 'woo-paypal-gateway'), $this->response->get_error_message()));
                throw new Exception(sprintf(__('An error occurred while trying to connect to PayPal: %s', 'woo-paypal-gateway'), $this->response->get_error_message()), 3);
            }
            if (empty($this->response['body'])) {
                Woo_PayPal_Gateway_PayPal_Pro::log('Empty response!');
                throw new Exception(__('Empty Paypal response.', 'woo-paypal-gateway'));
            }
            parse_str(wp_remote_retrieve_body($this->response), $this->result);
            if (!array_key_exists('ACK', $this->result)) {
                Woo_PayPal_Gateway_PayPal_Pro::log(sprintf('%s response: %s', $this->request_name, print_r($this->result, true)));
                throw new Exception(__('Malformed response received from PayPal', 'woo-paypal-gateway'), 3);
            } else {
                Woo_PayPal_Gateway_PayPal_Pro::log(sprintf('%s response: %s', $this->request_name, print_r($this->result, true)));
            }
            return $this->result;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
            $this->wpg_redirect_action(wc_get_cart_url());
        }
    }

    public function wpg_response_handler() {
        try {
            if ($this->wpg_is_response_success_or_successwithwarning() == true) {
                switch ($this->request_name) {
                    case 'do_direct_payment':
                        if (!empty($this->result['TRANSACTIONID'])) {
                            $this->transaction_id = $this->result['TRANSACTIONID'];
                            $this->order->add_order_note('Transaction ID: ' . $this->result['TRANSACTIONID']);
                            $this->wpg_get_transaction_details();
                            $this->wpg_update_payment_status_by_paypal_responce($this->order_id, $this->result);
                            WC()->cart->empty_cart();
                            $this->wpg_redirect_action($this->gateway->get_return_url($this->order));
                        }
                        break;
                    case 'do_reference_transaction':
                        if (!empty($this->result['TRANSACTIONID'])) {
                            $this->order->add_order_note('Transaction ID: ' . $this->result['TRANSACTIONID']);
                            $this->wpg_update_payment_status_by_paypal_responce($this->order_id, $this->result);
                            if (isset(WC()->cart) && sizeof(WC()->cart->get_cart()) > 0) {
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
                    $MESSAGE = !empty($this->result['L_LONGMESSAGE0']) ? $this->result['L_LONGMESSAGE0'] : (!empty($this->result['L_SHORTMESSAGE0']) ? $this->result['L_SHORTMESSAGE0'] : '');
                    wc_add_notice('Error:' . $ERRORCODE . '  ' . $MESSAGE, 'error');
                }
                $this->wpg_redirect_action(wc_get_cart_url());
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function wpg_is_response_success_or_successwithwarning() {
        try {
            if (!empty($this->result['ACK']) && strtoupper($this->result['ACK']) == 'SUCCESS' || strtoupper($this->result['ACK']) == "SUCCESSWITHWARNING") {
                return true;
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function request_do_direct_payment($order, $card) {
        try {
            $this->order = $order;
            $this->card = $card;
            $this->request = $this->wpg_do_direct_payment_request_param();
            $this->request_name = 'do_direct_payment';
            $this->wpg_request();
            $this->wpg_response();
            $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function wpg_get_transaction_details() {
        if (!empty($this->transaction_id)) {
            $this->request = $this->wpg_get_transaction_details_param();
            $this->request_name = 'get_transaction_details';
            $this->wpg_request();
            $this->wpg_response();
        }
    }

    public function wpg_get_transaction_details_param() {
        $post_data = array(
            'VERSION' => $this->gateway->api_version,
            'SIGNATURE' => $this->gateway->api_signature,
            'USER' => $this->gateway->api_username,
            'PWD' => $this->gateway->api_password,
            'METHOD' => 'GetTransactionDetails',
            'TRANSACTIONID' => $this->transaction_id
        );
        return $post_data;
    }

    public function wpg_refund_transaction_param() {
        try {
            $express_checkout_param = array(
                'METHOD' => 'RefundTransaction',
                'VERSION' => $this->gateway->api_version,
                'USER' => $this->gateway->api_username,
                'PWD' => $this->gateway->api_password,
                'SIGNATURE' => $this->gateway->api_signature,
                'TRANSACTIONID' => $this->order->get_transaction_id(),
                'REFUNDTYPE' => $this->order->get_total() == $this->refund_amount ? 'Full' : 'Partial',
                'AMT' => wpg_number_format($this->refund_amount),
                'CURRENCYCODE' => $this->order->get_currency(),
                'NOTE' => $this->refund_reason,
            );
            return $express_checkout_param;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function request_process_refund($order_id, $amount = null, $reason = '') {
        try {
            $this->order_id = $order_id;
            $this->order = wc_get_order($this->order_id);
            $this->refund_amount = $amount;
            $this->refund_reason = $reason;
            $this->transaction_id = $this->order->get_transaction_id();
            if (!$this->order || !$this->transaction_id || !$this->gateway->api_username || !$this->gateway->api_password || !$this->gateway->api_signature) {
                return false;
            }
            $this->wpg_get_transaction_details();
            if ($this->result && strtolower($this->result['PENDINGREASON']) === 'authorization') {
                $this->order->add_order_note(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-gateway'));
                Woo_PayPal_Gateway_PayPal_Pro::log('Refund order # ' . absint($this->order_id) . ': authorized only transactions need to use cancel/void instead.');
                throw new Exception(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-gateway'));
            }
            $this->request = $this->wpg_refund_transaction_param();
            $this->request_name = 'refund_transaction';
            $this->wpg_request();
            $this->wpg_response();
            return $this->wpg_response_handler();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

    public function get_user_ip() {
        try {
            return WC_Geolocation::get_ip_address();
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
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
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
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
            if (!empty($result['PAYERSTATUS'])) {
                $order->add_order_note(sprintf(__('Payer Status: %s', 'woo-paypal-gateway'), '<strong>' . $result['PAYERSTATUS'] . '</strong>'));
            }
            if (!empty($result['ADDRESSSTATUS'])) {
                $order->add_order_note(sprintf(__('Address Status: %s', 'woo-paypal-gateway'), '<strong>' . $result['ADDRESSSTATUS'] . '</strong>'));
            }
            switch (strtolower($payment_status)) :
                case 'completed' :
                    $this->order_status = $this->order->get_status();
                    if ($this->order_status == 'completed') {
                        break;
                    }
                    if (!in_array(strtolower($transaction_type), array('merchtpmt', 'cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money', 'webaccept'))) {
                        break;
                    }
                    $order->add_order_note(sprintf(__('Payment Status Completed via %s', 'woo-paypal-gateway'), $this->gateway->title));
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
                    $order->add_order_note(sprintf(__('Payment via %s Pending. PayPal reason: %s.', 'woo-paypal-gateway'), $this->gateway->title, $pending_reason));
                    if (!empty($result['L_LONGMESSAGE0'])) {
                        $error = $result['L_LONGMESSAGE0'];
                    } elseif (!empty($result['L_SHORTMESSAGE0'])) {
                        $error = $result['L_SHORTMESSAGE0'];
                    }
                    if (!empty($error)) {
                        $order->add_order_note(sprintf(__('%s Error: %s.', 'woo-paypal-gateway'), $this->gateway->title, $error));
                    }
                    $order->update_status('on-hold');
                    wc_maybe_reduce_stock_levels($orderid);
                    break;
                case 'denied' :
                case 'expired' :
                case 'failed' :
                case 'voided' :
                    $order->update_status('failed', sprintf(__('Payment %s via %s.', 'woo-paypal-gateway'), strtolower($payment_status)), $this->gateway->title);
                    break;
                default:
                    break;
            endswitch;
            return;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Pro::log($ex->getMessage());
        }
    }

}
