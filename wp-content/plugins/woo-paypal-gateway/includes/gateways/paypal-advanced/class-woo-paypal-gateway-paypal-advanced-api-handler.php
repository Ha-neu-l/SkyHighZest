<?php
if (!defined('ABSPATH')) {
    exit;
}

class Woo_Paypal_Gateway_PayPal_Advanced_API_Handler {

    public $gateway;   
    public $API_Endpoint;

    public function __construct($gateway) {
        $this->gateway = $gateway;
        $this->API_Endpoint = $this->gateway->testmode ? $this->gateway->testurl : $this->gateway->liveurl;
        $this->seller_protection = $this->gateway->get_option('seller_protection', 'disabled');
        if (!class_exists('Woo_Paypal_Gateway_Calculations')) {
            require_once( WPG_PLUGIN_DIR . '/includes/class-woo-paypal-gateway-calculations.php' );
        }
        $this->gateway_calculation = new Woo_Paypal_Gateway_Calculations($this->gateway);
    }
    
    public function get_token($order, $post_data, $force_new_token = false) {
        try {
            if (!$force_new_token && $order->get_meta('_SECURETOKENHASH') == md5(json_encode($post_data))) {
                return array(
                    'SECURETOKEN' => $order->get_meta('_SECURETOKEN'), 
                    'SECURETOKENID' => $order->get_meta('_SECURETOKENID'),
                );
            }
            $post_data['SECURETOKENID'] = uniqid() . md5($order->get_order_key());
            $post_data['CREATESECURETOKEN'] = 'Y';
            $post_data['SILENTTRAN'] = 'TRUE';
            $post_data['ERRORURL'] = WC()->api_request_url('Woo_Paypal_Gateway_PayPal_Advanced');
            $post_data['RETURNURL'] = WC()->api_request_url('Woo_Paypal_Gateway_PayPal_Advanced');
            $post_data['URLMETHOD'] = 'POST';
            $response = wp_remote_post($this->gateway->testmode ? $this->gateway->testurl : $this->gateway->liveurl, array(
                'method' => 'POST',
                'body' => urldecode(http_build_query(apply_filters('woo-paypal-gateway_payflow_request', $post_data, $order), null, '&')),
                'timeout' => 70,
                'user-agent' => 'WooCommerce',
                'httpversion' => '1.1'
            ));
            if (is_wp_error($response)) {
                wc_add_notice(__('There was a problem connecting to the payment gateway.', 'woo-paypal-gateway'));
                return false;
            }
            if (empty($response['body'])) {
                wc_add_notice(__('Empty Paypal response.', 'woo-paypal-gateway'));
                return false;
            }
            parse_str($response['body'], $parsed_response);
            if (isset($parsed_response['RESULT']) && in_array($parsed_response['RESULT'], array(160, 161, 162))) {
                return $this->get_token($order, $post_data, $force_new_token);
            } elseif (isset($parsed_response['RESULT']) && $parsed_response['RESULT'] == 0 && !empty($parsed_response['SECURETOKEN'])) {
                $order->update_meta_data('_SECURETOKEN', $parsed_response['SECURETOKEN']);
                $order->update_meta_data('_SECURETOKENID', $parsed_response['SECURETOKENID']);
                $order->update_meta_data('_SECURETOKENHASH', md5(json_encode($post_data)));
                $order->save_meta_data();
                return array(
                    'SECURETOKEN' => $parsed_response['SECURETOKEN'],
                    'SECURETOKENID' => $parsed_response['SECURETOKENID']
                );
            } else {
                $order->update_status('failed', __('PayPal Pro (Payflow) token generation failed: ', 'woo-paypal-gateway') . '(' . $parsed_response['RESULT'] . ') ' . '"' . $parsed_response['RESPMSG'] . '"');
                wc_add_notice(__('Payment error:', 'woo-paypal-gateway') . ' ' . $parsed_response['RESPMSG'], 'error');
                return false;
            }
        } catch (Exception $ex) {

        }
    }

    protected function _get_post_data($order) {
        try {
            $post_data = array();
            $post_data['USER'] = $this->gateway->paypal_user;
            $post_data['VENDOR'] = $this->gateway->paypal_vendor;
            $post_data['PARTNER'] = $this->gateway->paypal_partner;
            $post_data['PWD'] = $this->gateway->paypal_password;
            $post_data['TENDER'] = 'C';
            $post_data['TRXTYPE'] = $this->gateway->paymentaction;
            $post_data['AMT'] = $order->get_total();
            $post_data['CURRENCY'] = ( $order->get_currency() );
            $post_data['CUSTIP'] = $this->get_user_ip();
            $post_data['EMAIL'] = $order->get_billing_email();
            $post_data['INVNUM'] = $this->gateway->invoice_prefix . $order->get_order_number();
            $post_data['BUTTONSOURCE'] = 'mbjtechnolabs_SP';
            $post_data['CUSTOM'] = apply_filters('wpg_paypal_advanced_custom_parameter', json_encode(array('order_id' => $order->get_id(), 'order_key' => $order->get_order_key())), $order);
            $post_data['NOTIFYURL'] = apply_filters('wpg_paypal_advanced_notify_url', add_query_arg('wpg_ipn_action', 'ipn', WC()->api_request_url('Woo_Paypal_Gateway_IPN_Handler')));
            if ($this->gateway->soft_descriptor) {
                $post_data['MERCHDESCR'] = $this->gateway->soft_descriptor;
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

    public function get_transaction_details($transaction_id = 0) {
        try {
            $url = $this->gateway->testmode ? $this->gateway->testurl : $this->gateway->liveurl;
            $post_data = array();
            $post_data['USER'] = $this->gateway->paypal_user;
            $post_data['VENDOR'] = $this->gateway->paypal_vendor;
            $post_data['PARTNER'] = $this->gateway->paypal_partner;
            $post_data['PWD'] = $this->gateway->paypal_password;
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
                Woo_Paypal_Gateway_PayPal_Advanced::log('Error ' . print_r($response->get_error_message(), true));

                throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-gateway'));
            }
            parse_str($response['body'], $parsed_response);
            Woo_Paypal_Gateway_PayPal_Advanced::log('transaction_details ' . print_r($parsed_response, true));
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
            $url = $this->gateway->testmode ? $this->gateway->testurl : $this->gateway->liveurl;
            if (!$order || !$order->get_transaction_id() || !$this->gateway->paypal_user || !$this->gateway->paypal_vendor || !$this->gateway->paypal_password) {
                return false;
            }
            $details = $this->get_transaction_details($order->get_transaction_id());
            if ($details && strtolower($details['TRANSSTATE']) === '3') {
                $order->add_order_note(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-gateway'));
                Woo_Paypal_Gateway_PayPal_Advanced::log('Refund order # ' . $order_id . ': authorized only transactions need to use cancel/void instead.');
                throw new Exception(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-gateway'));
            }
            $post_data = array();
            $post_data['USER'] = $this->gateway->paypal_user;
            $post_data['VENDOR'] = $this->gateway->paypal_vendor;
            $post_data['PARTNER'] = $this->gateway->paypal_partner;
            $post_data['PWD'] = $this->gateway->paypal_password;
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
                Woo_Paypal_Gateway_PayPal_Advanced::log('Error ' . print_r($response->get_error_message(), true));
                throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-gateway'));
            }
            if (!isset($parsed_response['RESULT'])) {
                throw new Exception(__('Unexpected response from PayPal.', 'woo-paypal-gateway'));
            }
            if ('0' !== $parsed_response['RESULT']) {
                Woo_Paypal_Gateway_PayPal_Advanced::log('Parsed Response (refund) ' . print_r($parsed_response, true));
            } else {
                $order->add_order_note(sprintf(__('Refunded %1$s - PNREF: %2$s', 'woo-paypal-gateway'), wc_price(number_format($amount, 2, '.', '')), $parsed_response['PNREF']));
                return true;
            }
            return false;
        } catch (Exception $ex) {

        }
    }

    public function request_return_handler() {
        try {
            @ob_clean();
            header('HTTP/1.1 200 OK');
            $result = isset($_POST['RESULT']) ? absint($_POST['RESULT']) : null;
            $INVOICE = wc_clean($_POST['INVOICE']);
            $INVOICE = str_replace($this->gateway->invoice_prefix, '', $INVOICE);
            $order_id = isset($INVOICE) ? absint(ltrim($INVOICE, '#')) : 0;
            if (is_null($result) || empty($order_id)) {
                echo "Invalid request.";
                exit;
            }
            $order = new WC_Order($order_id);
            switch ($result) {
                case 0 :
                case 127 :
                    $txn_id = (!empty($_POST['PNREF']) ) ? wc_clean($_POST['PNREF']) : '';
                    $details = $this->get_transaction_details($txn_id);
                    if ($details && strtolower($details['TRANSSTATE']) === '3') {
                        $order->update_meta_data('_paypalpro_charge_captured', 'no');
                        $order->save_meta_data();
                        $order->set_transaction_id($txn_id);
                        $order->update_status('on-hold', sprintf(__('PayPal Pro (PayFlow) charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'woo-paypal-gateway'), $txn_id));
                        wc_reduce_stock_levels($order->get_id());
                    } else {
                        $order->add_order_note(sprintf(__('PayPal Pro (Payflow) payment completed (PNREF: %s)', 'woo-paypal-gateway'), $txn_id));
                        $order->payment_complete($txn_id);
                    }
                    WC()->cart->empty_cart();
                    $redirect = $order->get_checkout_order_received_url();
                    break;
                case 126 :
                    $order->add_order_note(wc_clean($_POST['RESPMSG']));
                    $order->add_order_note(wc_clean($_POST['PREFPSMSG']));
                    $order->update_status('on-hold', __('The payment was flagged by a fraud filter. Please check your PayPal Manager account to review and accept or deny the payment and then mark this order "processing" or "cancelled".', 'woo-paypal-gateway'));
                    WC()->cart->empty_cart();
                    $redirect = $order->get_checkout_order_received_url();
                    break;
                default :
                    $order->update_status('failed', $_POST['RESPMSG']);
                    $redirect = $order->get_checkout_payment_url(true);
                    $redirect = add_query_arg('wc_error', urlencode(wp_kses_post($_POST['RESPMSG'])), $redirect);
                    if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
                        $redirect = str_replace('http:', 'https:', $redirect);
                    }
                    break;
            }
            wp_redirect($redirect);
            exit;
        } catch (Exception $ex) {

        }
    }

    public function request_receipt_page($order_id) {
        try {
            wp_enqueue_script('wc-credit-card-form');
            $order = new WC_Order($order_id);
            $url = $this->gateway->testmode ? 'https://pilot-payflowlink.paypal.com' : 'https://payflowlink.paypal.com';
            $post_data = $this->_get_post_data($order);
            $token = $this->get_token($order, $post_data);
            if (!$token) {
                wc_print_notices();
                return;
            }
            echo wpautop(__('Enter your payment details below and click "Confirm and pay" to securely pay for your order.', 'woo-paypal-gateway'));
            ?>
            <form method="POST" action="<?php echo esc_url($url); ?>">
                <div id="payment">
                    <label style="padding:10px 0 0 10px;display:block;"><?php echo esc_attr($this->gateway->title) . ' ' . '<div style="vertical-align:middle;display:inline-block;margin:2px 0 0 .5em;">' . $this->gateway->get_icon() . '</div>'; ?></label>
                    <div class="payment_box">
                        <p><?php echo esc_attr($this->gateway->description) . ( $this->gateway->testmode ? ' ' . __('TEST/SANDBOX MODE ENABLED. In test mode, you can use the card number 4111111111111111 with any CVC and a valid expiration date.', 'woo-paypal-gateway') : '' ); ?></p>
                        <fieldset id="paypal_pro_payflow-cc-form">
                            <p class="form-row form-row-wide">
                                <label for="paypal_pro_payflow-card-number"><?php _e('Card Number ', 'woo-paypal-gateway'); ?><span class="required">*</span></label>
                                <input type="text" id="paypal_pro_payflow-card-number" class="input-text wc-credit-card-form-card-number" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="CARDNUM" />
                            </p>
                            <p class="form-row form-row-first">
                                <label for="paypal_pro_payflow-card-expiry"><?php _e('Expiry (MM/YY) ', 'woo-paypal-gateway'); ?><span class="required">*</span></label>
                                <input type="text" id="paypal_pro_payflow-card-expiry" class="input-text wc-credit-card-form-card-expiry" autocomplete="off" placeholder="MM / YY" name="EXPDATE" />
                            </p>
                            <p class="form-row form-row-last">
                                <label for="paypal_pro_payflow-card-cvc"><?php _e('Card Code ', 'woo-paypal-gateway'); ?><span class="required">*</span></label>
                                <input type="text" id="paypal_pro_payflow-card-cvc" class="input-text wc-credit-card-form-card-cvc" autocomplete="off" placeholder="CVC" name="CVV2" />
                            </p>
                            <input type="hidden" name="SECURETOKEN" value="<?php echo esc_attr($token['SECURETOKEN']); ?>" />
                            <input type="hidden" name="SECURETOKENID" value="<?php echo esc_attr($token['SECURETOKENID']); ?>" />
                            <input type="hidden" name="SILENTTRAN" value="TRUE" />
                        </fieldset>
                    </div>
                    <input type="submit" value="<?php _e('Confirm and pay', 'woo-paypal-gateway'); ?>" class="submit buy button" style="float:right;"/>
                </div>
                
            </form>
            <?php
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
