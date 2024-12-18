<?php

if (!defined('ABSPATH')) {
    exit;
}

class Woo_Paypal_Gateway_PayPal_Pro_Payflow_API_Handler {

    public $gateway_settings;

    public function _get_post_data($order) {
        try {
            $post_data = array();
            $post_data['USER'] = $this->gateway_settings->paypal_user;
            $post_data['VENDOR'] = $this->gateway_settings->paypal_vendor;
            $post_data['PARTNER'] = $this->gateway_settings->paypal_partner;
            $post_data['PWD'] = $this->gateway_settings->paypal_password;
            $post_data['TENDER'] = 'C';
            $post_data['TRXTYPE'] = $this->gateway_settings->paymentaction;
            $post_data['AMT'] = $order->get_total();
            $post_data['CURRENCY'] = $order->get_currency();
            $post_data['CUSTIP'] = $this->get_user_ip();
            $post_data['EMAIL'] = $order->get_billing_email();
            $post_data['INVNUM'] = $this->gateway_settings->invoice_prefix . $order->get_order_number();
            $post_data['CUSTOM'] = apply_filters('wpg_paypal_custom_parameter', json_encode(array('order_id' => $order->get_id(), 'order_key' => $order->get_order_key())), $order);
            $post_data['NOTIFYURL'] = apply_filters('wpg_paypal_pro_payflow_notify_url', add_query_arg('wpg_ipn_action', 'ipn', WC()->api_request_url('Woo_Paypal_Gateway_IPN_Handler')));
            $post_data['BUTTONSOURCE'] = 'mbjtechnolabs_SP';
            if ($this->gateway_settings->soft_descriptor) {
                $post_data['MERCHDESCR'] = $this->gateway_settings->soft_descriptor;
            }
            $item_loop = 0;
            if (sizeof($order->get_items()) > 0) {
                $ITEMAMT = 0;
                foreach ($order->get_items() as $item) {
                    $_product = $item->get_product();
                    if ($item['qty']) {
                        $post_data['L_NAME' . $item_loop] = $item['name'];
                        $post_data['L_COST' . $item_loop] = $order->get_item_total($item, true);
                        $post_data['L_QTY' . $item_loop] = $item['qty'];
                        if ($_product->get_sku()) {
                            $post_data['L_SKU' . $item_loop] = $_product->get_sku();
                        }
                        $ITEMAMT += $order->get_item_total($item, true) * $item['qty'];
                        $item_loop++;
                    }
                }
                if (( $order->get_total_shipping() + $order->get_shipping_tax() ) > 0) {
                    $post_data['L_NAME' . $item_loop] = 'Shipping';
                    $post_data['L_DESC' . $item_loop] = 'Shipping and shipping taxes';
                    $post_data['L_COST' . $item_loop] = $order->get_total_shipping() + $order->get_shipping_tax();
                    $post_data['L_QTY' . $item_loop] = 1;
                    $ITEMAMT += $order->get_total_shipping() + $order->get_shipping_tax();
                    $item_loop++;
                }
                if ($order->get_total_discount(false) > 0) {
                    $post_data['L_NAME' . $item_loop] = 'Order Discount';
                    $post_data['L_DESC' . $item_loop] = 'Discounts including tax';
                    $post_data['L_COST' . $item_loop] = '-' . $order->get_total_discount(false);
                    $post_data['L_QTY' . $item_loop] = 1;
                    $item_loop++;
                }
                $ITEMAMT = round($ITEMAMT, 2);
                if (absint($order->get_total() * 100) !== absint($ITEMAMT * 100)) {
                    $post_data['L_NAME' . $item_loop] = 'Rounding amendment';
                    $post_data['L_DESC' . $item_loop] = 'Correction if rounding is off (this can happen with tax inclusive prices)';
                    $post_data['L_COST' . $item_loop] = ( absint($order->get_total() * 100) - absint($ITEMAMT * 100) ) / 100;
                    $post_data['L_QTY' . $item_loop] = 1;
                }
                $post_data['ITEMAMT'] = $order->get_total();
            }
            $post_data['ORDERDESC'] = 'Order ' . $order->get_order_number() . ' on ' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
            $post_data['FIRSTNAME'] = $order->get_billing_first_name();
            $post_data['LASTNAME'] = $order->get_billing_last_name();
            $post_data['STREET'] = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
            $post_data['CITY'] = $order->get_billing_city();
            $post_data['STATE'] = $order->get_billing_state();
            $post_data['COUNTRY'] = $order->get_billing_country();
            $post_data['ZIP'] = $order->get_billing_postcode();
            if ($order->get_shipping_address_1()) {
                $post_data['SHIPTOFIRSTNAME'] = $order->get_shipping_first_name();
                $post_data['SHIPTOLASTNAME'] = $order->get_shipping_last_name();
                $post_data['SHIPTOSTREET'] = $order->get_shipping_address_1();
                $post_data['SHIPTOCITY'] = $order->get_shipping_city();
                $post_data['SHIPTOSTATE'] = $order->get_shipping_state();
                $post_data['SHIPTOCOUNTRY'] = $order->get_shipping_country();
                $post_data['SHIPTOZIP'] = $order->get_shipping_postcode();
            }
            return $post_data;
        } catch (Exception $ex) {
            
        }
    }

    public function request_do_payment($order, $card) {
        try {
            $url = $this->gateway_settings->testmode ? $this->gateway_settings->testurl : $this->gateway_settings->liveurl;
            $post_data = $this->_get_post_data($order);
            $post_data['ACCT'] = $card->number;
            $post_data['EXPDATE'] = $card->exp_month . $card->exp_year;
            $post_data['CVV2'] = $card->cvc;
            if ($this->gateway_settings->debug) {
                $log = $post_data;
                $log['ACCT'] = '****';
                $log['CVV2'] = '****';
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Do payment request ' . print_r($log, true));
            }
            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'body' => urldecode(http_build_query(apply_filters('woo-paypal-gateway_payflow_request', $post_data, $order), null, '&')),
                'timeout' => 70,
                'user-agent' => 'WooCommerce',
                'httpversion' => '1.1',
            ));
            if (is_wp_error($response)) {
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Error ' . print_r($response->get_error_message(), true));
                throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-gateway'));
            }
            if (empty($response['body'])) {
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Empty response!');
                throw new Exception(__('Empty PayPal response.', 'woo-paypal-gateway'));
            }
            parse_str($response['body'], $parsed_response);
            Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Parsed Response ' . print_r($parsed_response, true));
            if (isset($parsed_response['RESULT']) && in_array($parsed_response['RESULT'], array(0, 126, 127))) {
                switch ($parsed_response['RESULT']) {
                    case 0 :
                    case 127 :
                        $txn_id = (!empty($parsed_response['PNREF']) ) ? wc_clean($parsed_response['PNREF']) : '';
                        $details = $this->get_transaction_details($txn_id);
                        if ($details && strtolower($details['TRANSSTATE']) === '3') {
                            $order->update_meta_data('_paypalpro_charge_captured', 'no');
                            $order->save_meta_data();
                            $order->set_transaction_id($txn_id);
                            $order->update_status('on-hold', sprintf(__('PayPal Pro (PayFlow) charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'woo-paypal-gateway'), $txn_id));
                            wc_reduce_stock_levels($order->get_id());
                        } else {
                            $order->add_order_note(sprintf(__('PayPal Pro (Payflow) payment completed (PNREF: %s)', 'woo-paypal-gateway'), $parsed_response['PNREF']));
                            $order->payment_complete($txn_id);
                        }
                        WC()->cart->empty_cart();
                        break;
                    case 126 :
                        $order->add_order_note($parsed_response['RESPMSG']);
                        $order->add_order_note($parsed_response['PREFPSMSG']);
                        $order->update_status('on-hold', __('The payment was flagged by a fraud filter. Please check your PayPal Manager account to review and accept or deny the payment and then mark this order "processing" or "cancelled".', 'woo-paypal-gateway'));
                        break;
                }
                $redirect = $order->get_checkout_order_received_url();
                return array(
                    'result' => 'success',
                    'redirect' => $redirect,
                );
            } else {
                $order->update_status('failed', __('PayPal Pro (Payflow) payment failed. Payment was rejected due to an error: ', 'woo-paypal-gateway') . '(' . $parsed_response['RESULT'] . ') ' . '"' . $parsed_response['RESPMSG'] . '"');
                wc_add_notice(__('Payment error:', 'woo-paypal-gateway') . ' ' . $parsed_response['RESPMSG'], 'error');
                return;
            }
        } catch (Exception $e) {
            wc_add_notice(__('Connection error:', 'woo-paypal-gateway') . ': "' . $e->getMessage() . '"', 'error');
            return;
        }
    }

    public function get_transaction_details($transaction_id = 0) {
        try {
            $url = $this->gateway_settings->testmode ? $this->gateway_settings->testurl : $this->gateway_settings->liveurl;
            $post_data = array();
            $post_data['USER'] = $this->gateway_settings->paypal_user;
            $post_data['VENDOR'] = $this->gateway_settings->paypal_vendor;
            $post_data['PARTNER'] = $this->gateway_settings->paypal_partner;
            $post_data['PWD'] = $this->gateway_settings->paypal_password;
            $post_data['TRXTYPE'] = 'I';
            $post_data['ORIGID'] = $transaction_id;
            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'body' => urldecode(http_build_query(apply_filters('woo-paypal-gateway_payflow_transaction_details_request', $post_data, null, '&'))),
                'timeout' => 70,
                'user-agent' => 'WooCommerce',
                'httpversion' => '1.1',
            ));
            if (is_wp_error($response)) {
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Error ' . print_r($response->get_error_message(), true));
                throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-gateway'));
            }
            parse_str($response['body'], $parsed_response);
            if (isset($parsed_response['RESULT']) && '0' === $parsed_response['RESULT']) {
                return $parsed_response;
            }
            return false;
        } catch (Exception $ex) {
            
        }
    }

    public function request_process_refund($order_id, $amount = null, $reason = '') {
        try {
            $order = wc_get_order($order_id);
            $url = $this->gateway_settings->testmode ? $this->gateway_settings->testurl : $this->gateway_settings->liveurl;
            if (!$order || !$order->get_transaction_id() || !$this->gateway_settings->paypal_user || !$this->gateway_settings->paypal_vendor || !$this->gateway_settings->paypal_password) {
                return false;
            }
            $details = $this->get_transaction_details($order->get_transaction_id());
            if ($details && strtolower($details['TRANSSTATE']) === '3') {
                $order->add_order_note(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-gateway'));
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Refund order # ' . $order_id . ': authorized only transactions need to use cancel/void instead.');
                throw new Exception(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-gateway'));
            }
            $post_data = array();
            $post_data['USER'] = $this->gateway_settings->paypal_user;
            $post_data['VENDOR'] = $this->gateway_settings->paypal_vendor;
            $post_data['PARTNER'] = $this->gateway_settings->paypal_partner;
            $post_data['PWD'] = $this->gateway_settings->paypal_password;
            $post_data['TRXTYPE'] = 'C';
            $post_data['ORIGID'] = $order->get_transaction_id();
            if (!is_null($amount)) {
                $post_data['AMT'] = number_format($amount, 2, '.', '');
                $post_data['CURRENCY'] = $order->get_currency();
            }
            if ($reason) {
                if (255 < strlen($reason)) {
                    $reason = substr($reason, 0, 252) . '...';
                }
                $post_data['COMMENT1'] = html_entity_decode($reason, ENT_NOQUOTES, 'UTF-8');
            }
            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'body' => urldecode(http_build_query(apply_filters('woo-paypal-gateway_payflow_refund_request', $post_data, null, '&'))),
                'timeout' => 70,
                'user-agent' => 'WooCommerce',
                'httpversion' => '1.1',
            ));
            parse_str($response['body'], $parsed_response);
            if (is_wp_error($response)) {
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Error ' . print_r($response->get_error_message(), true));
                throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-gateway'));
            }
            if (!isset($parsed_response['RESULT'])) {
                throw new Exception(__('Unexpected response from PayPal.', 'woo-paypal-gateway'));
            }
            if ('0' !== $parsed_response['RESULT']) {
                Woo_Paypal_Gateway_PayPal_Pro_Payflow::log('Parsed Response (refund) ' . print_r($parsed_response, true));
            } else {
                $order->add_order_note(sprintf(__('Refunded %1$s - PNREF: %2$s', 'woo-paypal-gateway'), wc_price(number_format($amount, 2, '.', '')), $parsed_response['PNREF']));
                return true;
            }
            return false;
        } catch (Exception $ex) {
            
        }
    }

    public function get_user_ip() {
        try {
            return WC_Geolocation::get_ip_address();
        } catch (Exception $ex) {
            
        }
    }

}
