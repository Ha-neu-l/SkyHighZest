<?php

/**
 * @since      1.0.0
 * @package    PPCP_Paypal_Checkout_For_Woocommerce_Request
 * @subpackage PPCP_Paypal_Checkout_For_Woocommerce_Request/includes
 * @author     PayPal <wpeasypayment@gmail.com>
 */
class PPCP_Paypal_Checkout_For_Woocommerce_Request extends WC_Payment_Gateway {

    /**
     * @since    1.0.0
     */
    public $log_enabled = false;
    public static $log = false;
    public $request;
    public $id;
    public $decimals;
    public $is_sandbox;
    public $debug;
    public $ppcp_currency;
    public $client_id;
    public $secret;
    public $token_url;
    public $access_token;
    public $order_url;
    public $paypal_oauth_api;
    public $paypal_order_api;
    public $paypal_refund_api;
    public $auth;
    public $webhook;
    public $basicAuth;
    public $webhook_id;
    public $webhook_url;
    public $generate_token_url;
    public $client_token;
    public $paymentaction;
    public $payee_preferred;
    public $invoice_id_prefix;
    public $soft_descriptor;
    public $brand_name;
    public $landing_page;
    public $advanced_card_payments;
    public $AVSCodes;
    public $CVV2Codes;
    public $logger;
    public $invoice_prefix;
    public $merchant_id;
    public $send_items;
    public $api_response;
    protected static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        try {
            $this->id = 'wpg_paypal_checkout';
            $this->is_sandbox = 'yes' === $this->get_option('sandbox', 'no');
            $this->debug = 'yes' === $this->get_option('debug', 'no');
            $this->ppcp_currency = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD');
            $this->log_enabled = $this->debug;
            if ($this->is_sandbox) {
                $this->client_id = $this->get_option('rest_client_id_sandbox');
                $this->secret = $this->get_option('rest_secret_id_sandbox');
                $this->token_url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
                $this->access_token = get_transient('ppcp_sandbox_access_token');
                $this->order_url = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
                $this->paypal_oauth_api = 'https://api.sandbox.paypal.com/v1/oauth2/token/';
                $this->paypal_order_api = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
                $this->paypal_refund_api = 'https://api.sandbox.paypal.com/v2/payments/captures/';
                $this->auth = 'https://api.sandbox.paypal.com/v2/payments/authorizations/';
                $this->webhook = 'https://api.sandbox.paypal.com/v1/notifications/webhooks';
                $this->basicAuth = base64_encode($this->client_id . ":" . $this->secret);
                $this->webhook_id = 'ppcp_sandbox_webhook_id';
                $this->webhook_url = 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature';
                $this->generate_token_url = 'https://api.sandbox.paypal.com/v1/identity/generate-token';
                $this->client_token = get_transient('ppcp_sandbox_client_token');
            } else {
                $this->client_token = get_transient('ppcp_client_token');
                $this->client_id = $this->get_option('rest_client_id_live');
                $this->secret = $this->get_option('rest_secret_id_live');
                $this->token_url = 'https://api.paypal.com/v1/oauth2/token';
                $this->access_token = get_transient('ppcp_access_token');
                $this->order_url = 'https://api.paypal.com/v2/checkout/orders/';
                $this->paypal_oauth_api = 'https://api.paypal.com/v1/oauth2/token/';
                $this->paypal_order_api = 'https://api.paypal.com/v2/checkout/orders/';
                $this->paypal_refund_api = 'https://api.paypal.com/v2/payments/captures/';
                $this->auth = 'https://api.paypal.com/v2/payments/authorizations/';
                $this->webhook = 'https://api.paypal.com/v1/notifications/webhooks';
                $this->basicAuth = base64_encode($this->client_id . ":" . $this->secret);
                $this->webhook_id = 'ppcp_live_webhook_id';
                $this->webhook_url = 'https://api.paypal.com/v1/notifications/verify-webhook-signature';
                $this->generate_token_url = 'https://api.paypal.com/v1/identity/generate-token';
            }
            $this->paymentaction = $this->get_option('paymentaction', 'capture');
            $this->payee_preferred = 'yes' === $this->get_option('payee_preferred', 'no');
            $this->invoice_id_prefix = $this->get_option('invoice_id_prefix', 'WC-PPCP');
            $this->soft_descriptor = $this->get_option('soft_descriptor', '');
            $this->brand_name = $this->get_option('brand_name', get_bloginfo('name'));
            $this->landing_page = $this->get_option('landing_page', 'NO_PREFERENCE');
            $this->advanced_card_payments = 'yes' === $this->get_option('enable_advanced_card_payments', 'yes');
            $this->decimals = $this->ppcp_get_number_of_decimal_digits();
            $this->send_items = 'yes' === $this->get_option('send_items', 'yes');
            $this->AVSCodes = array("A" => "Address Matches Only (No ZIP)",
                "B" => "Address Matches Only (No ZIP)",
                "C" => "This tranaction was declined.",
                "D" => "Address and Postal Code Match",
                "E" => "This transaction was declined.",
                "F" => "Address and Postal Code Match",
                "G" => "Global Unavailable - N/A",
                "I" => "International Unavailable - N/A",
                "N" => "None - Transaction was declined.",
                "P" => "Postal Code Match Only (No Address)",
                "R" => "Retry - N/A",
                "S" => "Service not supported - N/A",
                "U" => "Unavailable - N/A",
                "W" => "Nine-Digit ZIP Code Match (No Address)",
                "X" => "Exact Match - Address and Nine-Digit ZIP",
                "Y" => "Address and five-digit Zip match",
                "Z" => "Five-Digit ZIP Matches (No Address)");

            $this->CVV2Codes = array(
                "E" => "N/A",
                "M" => "Match",
                "N" => "No Match",
                "P" => "Not Processed - N/A",
                "S" => "Service Not Supported - N/A",
                "U" => "Service Unavailable - N/A",
                "X" => "No Response - N/A"
            );
            if (isset($_GET['from']) && 'cart' === $_GET['from']) {
                $this->order_button_text = __('Continue to payment', 'woo-paypal-gateway');
            }
        } catch (Exception $ex) {
            
        }
    }

    public function request($url, $args, $action_name = 'default') {
        try {
            $result = wp_remote_get($url, $args);
            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                $body = wp_remote_retrieve_body($result);
                $response = !empty($body) ? json_decode($body, true) : '';
                return $response;
            }
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function ppcp_application_context($return = false) {
        $application_context = array(
            'brand_name' => $this->brand_name,
            'locale' => 'en-US',
            'landing_page' => $this->landing_page,
            'shipping_preference' => $this->ppcp_shipping_preference(),
            'user_action' => is_checkout() ? 'PAY_NOW' : 'CONTINUE',
            'return_url' => 'https://www.google.com',
            'cancel_url' => 'https://www.google.com'
        );
        if ($return) {
            $application_context['return_url'] = add_query_arg(array('ppcp_action' => 'ppcp_regular_capture', 'utm_nooverride' => '1'), WC()->api_request_url('PPCP_Paypal_Checkout_For_Woocommerce_Button_Manager'));
        }
        return $application_context;
    }

    public function ppcp_shipping_preference() {
        $shipping_preference = 'GET_FROM_FILE';
        $is_cart = is_cart() && !WC()->cart->is_empty();
        $is_checkout = is_checkout();
        $page = $is_cart ? 'cart' : ( $is_checkout ? 'checkout' : null );
        switch ($page) {
            case 'cart':
                $shipping_preference = WC()->cart->needs_shipping_address() ? 'GET_FROM_FILE' : 'NO_SHIPPING';
                break;
            case 'checkout':
                $shipping_preference = WC()->cart->needs_shipping_address() ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING';
                break;
        }
        return $shipping_preference;
    }

    public function get_genrate_token() {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            if ($this->is_valid_for_use() === true && $this->access_token) {
                $response = wp_remote_post($this->generate_token_url, array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'httpversion' => '1.1',
                    'blocking' => true,
                    'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, 'Accept-Language' => 'en_US'),
                    'cookies' => array()
                        )
                );
                $this->ppcp_log('Get Genrate token Request' . $this->paypal_oauth_api);
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
                } else {
                    $api_response = json_decode(wp_remote_retrieve_body($response), true);
                    $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                    if (!empty($api_response['client_token'])) {
                        if ($this->is_sandbox) {
                            set_transient('ppcp_sandbox_client_token', $api_response['client_token'], 2500);
                        } else {
                            set_transient('ppcp_client_token', $api_response['client_token'], 2500);
                        }
                        $this->client_token = $api_response['client_token'];
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function is_valid_for_use() {
        try {
            if (empty($this->client_id) && empty($this->secret)) {
                return false;
            }
            return true;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'wpg_paypal_checkout'));
        }
    }

    public function ppcp_create_order_request($woo_order_id = null) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            if ($woo_order_id == null) {
                $cart = $this->ppcp_get_details_from_cart();
            } else {
                $cart = $this->ppcp_get_details_from_order($woo_order_id);
            }
            $reference_id = wc_generate_order_key();
            ppcp_set_session('ppcp_reference_id', $reference_id);
            $intent = ($this->paymentaction === 'capture') ? 'CAPTURE' : 'AUTHORIZE';
            $body_request = array(
                'intent' => $intent,
                'application_context' => $this->ppcp_application_context(),
                'payment_method' => array('payee_preferred' => ($this->payee_preferred) ? 'IMMEDIATE_PAYMENT_REQUIRED' : 'UNRESTRICTED'),
                'purchase_units' =>
                array(
                    0 =>
                    array(
                        'reference_id' => $reference_id,
                        'amount' =>
                        array(
                            'currency_code' => get_woocommerce_currency(),
                            'value' => ppcp_round($cart['order_total'], $this->decimals),
                            'breakdown' => array()
                        )
                    ),
                ),
            );
            if ($woo_order_id != null) {
                $order = wc_get_order($woo_order_id);
                $body_request['purchase_units'][0]['soft_descriptor'] = $this->soft_descriptor;
                $body_request['purchase_units'][0]['invoice_id'] = $this->invoice_id_prefix . str_replace("#", "", $order->get_order_number());
                $body_request['purchase_units'][0]['custom_id'] = wp_json_encode(
                        array(
                            'order_id' => $order->get_id(),
                            'order_key' => $order->get_order_key(),
                        )
                );
            } else {
                $body_request['purchase_units'][0]['invoice_id'] = $reference_id;
                $body_request['purchase_units'][0]['custom_id'] = wp_json_encode(
                        array(
                            'order_id' => $reference_id,
                            'order_key' => $reference_id,
                        )
                );
            }
            if ($this->send_items === true) {
                if (isset($cart['total_item_amount']) && $cart['total_item_amount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['item_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['total_item_amount'], $this->decimals)
                    );
                }
                if (isset($cart['shipping']) && $cart['shipping'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['shipping'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['shipping'], $this->decimals)
                    );
                }
                if (isset($cart['ship_discount_amount']) && $cart['ship_discount_amount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['shipping_discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['ship_discount_amount'], $this->decimals),
                    );
                }
                if (isset($cart['order_tax']) && $cart['order_tax'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['tax_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['order_tax'], $this->decimals)
                    );
                }
                if (isset($cart['discount']) && $cart['discount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['discount'], $this->decimals)
                    );
                }
                if (isset($cart['items']) && !empty($cart['items'])) {
                    foreach ($cart['items'] as $key => $order_items) {
                        $description = !empty($order_items['description']) ? $order_items['description'] : '';
                        if (strlen($description) > 127) {
                            $description = substr($description, 0, 124) . '...';
                        }
                        $name = $order_items['name'];
                        if (strlen($name) > 127) {
                            $name = substr($name, 0, 124) . '...';
                        }
                        $body_request['purchase_units'][0]['items'][$key] = array(
                            'name' => $name,
                            'description' => html_entity_decode($description, ENT_NOQUOTES, 'UTF-8'),
                            'sku' => $order_items['sku'],
                            'category' => $order_items['category'],
                            'quantity' => $order_items['quantity'],
                            'unit_amount' => array(
                                'currency_code' => get_woocommerce_currency(),
                                'value' => ppcp_round($order_items['amount'], $this->decimals)
                            ),
                        );
                    }
                }
            }
            if ($woo_order_id != null) {
                $order = wc_get_order($woo_order_id);
                if ($order->needs_shipping_address() || WC()->cart->needs_shipping_address()) {
                    if (($order->has_shipping_address())) {
                        $shipping_first_name = $order->get_shipping_first_name();
                        $shipping_last_name = $order->get_shipping_last_name();
                        $shipping_address_1 = $order->get_shipping_address_1();
                        $shipping_address_2 = $order->get_shipping_address_2();
                        $shipping_city = $order->get_shipping_city();
                        $shipping_state = $order->get_shipping_state();
                        $shipping_postcode = $order->get_shipping_postcode();
                        $shipping_country = $order->get_shipping_country();
                    } else {
                        $shipping_first_name = $order->get_billing_first_name();
                        $shipping_last_name = $order->get_billing_last_name();
                        $shipping_address_1 = $order->get_billing_address_1();
                        $shipping_address_2 = $order->get_billing_address_2();
                        $shipping_city = $order->get_billing_city();
                        $shipping_state = $order->get_billing_state();
                        $shipping_postcode = $order->get_billing_postcode();
                        $shipping_country = $order->get_billing_country();
                    }
                    if (!empty($shipping_first_name) && !empty($shipping_last_name)) {
                        $body_request['purchase_units'][0]['shipping']['name']['full_name'] = $shipping_first_name . ' ' . $shipping_last_name;
                    }
                    $body_request['purchase_units'][0]['shipping']['address'] = array(
                        'address_line_1' => $shipping_address_1,
                        'address_line_2' => $shipping_address_2,
                        'admin_area_2' => $shipping_city,
                        'admin_area_1' => $shipping_state,
                        'postal_code' => $shipping_postcode,
                        'country_code' => $shipping_country,
                    );
                }
            } else {
                if (true === WC()->cart->needs_shipping_address()) {
                    if (is_user_logged_in()) {
                        if (!empty($cart['shipping_address']['first_name']) && !empty($cart['shipping_address']['last_name'])) {
                            $body_request['purchase_units'][0]['shipping']['name']['full_name'] = $cart['shipping_address']['first_name'] . ' ' . $cart['shipping_address']['last_name'];
                        }
                        if (!empty($cart['shipping_address']['address_1']) && !empty($cart['shipping_address']['city']) && !empty($cart['shipping_address']['state']) && !empty($cart['shipping_address']['postcode']) && !empty($cart['shipping_address']['country'])) {
                            $body_request['purchase_units'][0]['shipping']['address'] = array(
                                'address_line_1' => $cart['shipping_address']['address_1'],
                                'address_line_2' => $cart['shipping_address']['address_2'],
                                'admin_area_2' => $cart['shipping_address']['city'],
                                'admin_area_1' => $cart['shipping_address']['state'],
                                'postal_code' => $cart['shipping_address']['postcode'],
                                'country_code' => $cart['shipping_address']['country'],
                            );
                        }
                    }
                }
            }
            $body_request = $this->ppcp_set_payer_details($woo_order_id, $body_request);
            $body_request = ppcp_remove_empty_key($body_request);
            $this->ppcp_add_log_details('Create order');
            $this->ppcp_log('Order Request : ' . wc_print_r($body_request, true));
            $body_request = json_encode($body_request);
            $response = wp_remote_post($this->paypal_order_api, array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                if (ob_get_length())
                    ob_end_clean();
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($api_response['status'])) {
                    $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                    $return_response['orderID'] = $api_response['id'];
                    if (!empty(isset($woo_order_id) && !empty($woo_order_id))) {
                        $order->update_meta_data('_paypal_order_id', $api_response['id']);
                        $order->save_meta_data();
                        ppcp_set_session('ppcp_paypal_transaction_details', $api_response);
                    }
                    wp_send_json($return_response, 200);
                    exit();
                } else {
                    $error_message = $this->ppcp_get_readable_message($api_response);
                    $this->ppcp_log('Error Message : ' . wc_print_r($api_response, true));
                    wp_send_json_error($error_message);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_extra_offset_line_item($amount) {
        try {
            return array(
                'name' => 'Line Item Amount Offset',
                'description' => 'Adjust cart calculation discrepancy',
                'quantity' => 1,
                'amount' => ppcp_round($amount, $this->decimals),
            );
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_number_of_decimal_digits() {
        try {
            return $this->ppcp_is_currency_supports_zero_decimal() ? 0 : 2;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_access_token() {
        try {
            $headers = array(
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $this->basicAuth,
                'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB'
            );
            $body = array('grant_type' => 'client_credentials');
            $response = wp_remote_post($this->paypal_oauth_api, array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => $headers,
                'body' => $body
            ));
            $this->ppcp_log('Get access token Request: ' . $this->paypal_oauth_api);
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message: ' . $error_message);
                return false;
            }
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
            $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
            $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
            if (!empty($api_response['access_token'])) {
                $transient_key = $this->is_sandbox ? 'ppcp_sandbox_access_token' : 'ppcp_access_token';
                set_transient($transient_key, $api_response['access_token'], 5000);
                $this->access_token = $api_response['access_token'];
                return $this->access_token;
            }
            $this->ppcp_log('Error: Access token not found in the response');
            return false;
        } catch (Exception $ex) {
            $this->ppcp_log('Exception caught: ' . $ex->getMessage());
            return false;
        }
    }

    public function ppcp_order_capture_request($woo_order_id, $need_to_update_order = true) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $order = wc_get_order($woo_order_id);
            if ($need_to_update_order && is_object($order)) {
                $this->ppcp_update_order($order);
            }
            $paypal_order_id = ppcp_get_session('ppcp_paypal_order_id');
            $this->ppcp_add_log_details('Capture payment for order');
            $this->ppcp_log('Request : ' . wc_print_r($this->paypal_order_api . $paypal_order_id . '/capture', true));
            $response = wp_remote_post($this->paypal_order_api . $paypal_order_id . '/capture', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($error_message, 'error');
                }
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->ppcp_log('Response : ' . wc_print_r($api_response, true));
                if (isset($api_response['id']) && !empty($api_response['id'])) {
                    $return_response['paypal_order_id'] = $api_response['id'];
                    $order->update_meta_data('_paypal_order_id', $api_response['id']);
                    $order->save_meta_data();
                    if ($api_response['status'] == 'COMPLETED') {
                        $payment_source = isset($api_response['payment_source']) ? $api_response['payment_source'] : '';
                        if (!empty($payment_source['card'])) {
                            $card_response_order_note = __('Card Details', 'woo-paypal-gateway');
                            $card_response_order_note .= "\n";
                            $card_response_order_note .= 'Last digits : ' . $payment_source['card']['last_digits'];
                            $card_response_order_note .= "\n";
                            $card_response_order_note .= 'Brand : ' . ppcp_readable($payment_source['card']['brand']);
                            $card_response_order_note .= "\n";
                            $card_response_order_note .= 'Card type : ' . ppcp_readable($payment_source['card']['type']);
                            $order->add_order_note($card_response_order_note);
                        }
                        $processor_response = isset($api_response['purchase_units']['0']['payments']['captures']['0']['processor_response']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['processor_response'] : '';
                        if (!empty($processor_response['avs_code'])) {
                            $avs_response_order_note = __('Address Verification Result', 'woo-paypal-gateway');
                            $avs_response_order_note .= "\n";
                            $avs_response_order_note .= $processor_response['avs_code'];
                            if (isset($this->AVSCodes[$processor_response['avs_code']])) {
                                $avs_response_order_note .= ' : ' . $this->AVSCodes[$processor_response['avs_code']];
                            }
                            $order->add_order_note($avs_response_order_note);
                        }
                        if (!empty($processor_response['cvv_code'])) {
                            $cvv2_response_code = __('Card Security Code Result', 'woo-paypal-gateway');
                            $cvv2_response_code .= "\n";
                            $cvv2_response_code .= $processor_response['cvv_code'];
                            if (isset($this->CVV2Codes[$processor_response['cvv_code']])) {
                                $cvv2_response_code .= ' : ' . $this->CVV2Codes[$processor_response['cvv_code']];
                            }
                            $order->add_order_note($cvv2_response_code);
                        }
                        $currency_code = isset($api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['currency_code']) ? $api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['currency_code'] : '';
                        $value = isset($api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value']) ? $api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value'] : '';
                        $order->update_meta_data('_paypal_fee', $value);
                        $order->update_meta_data('_paypal_fee_currency_code', $currency_code);
                        $order->save_meta_data();
                        $transaction_id = isset($api_response['purchase_units']['0']['payments']['captures']['0']['id']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['id'] : '';
                        $seller_protection = isset($api_response['purchase_units']['0']['payments']['captures']['0']['seller_protection']['status']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['seller_protection']['status'] : '';
                        $payment_status = isset($api_response['purchase_units']['0']['payments']['captures']['0']['status']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['status'] : '';
                        if ($payment_status == 'COMPLETED') {
                            $order->payment_complete($transaction_id);
                            $order->add_order_note(sprintf(__('Payment via %s : %s.', 'woo-paypal-gateway'), $order->get_payment_method_title(), ucfirst(strtolower($payment_status))));
                        } else {
                            $payment_status_reason = isset($api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason'] : '';
                            ppcp_update_woo_order_status($woo_order_id, $payment_status, $payment_status_reason);
                        }
                        $order->update_meta_data('_payment_status', $payment_status);
                        $order->save_meta_data();
                        $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'woo-paypal-gateway'), $order->get_payment_method_title(), $transaction_id));
                        $order->add_order_note('Seller Protection Status: ' . ppcp_readable($seller_protection));
                    }
                    return true;
                } else {
                    if (function_exists('wc_add_notice')) {
                        $error_message = $this->ppcp_get_readable_message($api_response);
                        wc_add_notice($error_message, 'error');
                    }
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_order_auth_request($woo_order_id) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $order = wc_get_order($woo_order_id);
            if (is_object($order)) {
                $this->ppcp_update_order($order);
            }
            $paypal_order_id = ppcp_get_session('ppcp_paypal_order_id');
            $this->ppcp_add_log_details('Authorize payment for order');
            $this->ppcp_log('Request : ' . wc_print_r($this->paypal_order_api . $paypal_order_id . '/authorize', true));
            $response = wp_remote_post($this->paypal_order_api . $paypal_order_id . '/authorize', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($error_message, 'error');
                }
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->ppcp_log('Response : ' . wc_print_r($api_response, true));
                if (!empty($api_response['id'])) {
                    $return_response['paypal_order_id'] = $api_response['id'];
                    if (isset($woo_order_id) && !empty($woo_order_id)) {
                        $order->update_meta_data('_paypal_order_id', $api_response['id']);
                        $order->save_meta_data();
                    }
                    if ($api_response['status'] == 'COMPLETED') {
                        $transaction_id = isset($api_response['purchase_units']['0']['payments']['authorizations']['0']['id']) ? $api_response['purchase_units']['0']['payments']['authorizations']['0']['id'] : '';
                        $seller_protection = isset($api_response['purchase_units']['0']['payments']['authorizations']['0']['seller_protection']['status']) ? $api_response['purchase_units']['0']['payments']['authorizations']['0']['seller_protection']['status'] : '';
                        $payment_status = isset($api_response['purchase_units']['0']['payments']['authorizations']['0']['status']) ? $api_response['purchase_units']['0']['payments']['authorizations']['0']['status'] : '';
                        $order->update_meta_data('_transaction_id', $transaction_id);
                        $order->update_meta_data('_payment_status', $payment_status);
                        $order->update_meta_data('_auth_transaction_id', $transaction_id);
                        $order->update_meta_data('_payment_action', $this->paymentaction);
                        $order->save_meta_data();
                        $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'woo-paypal-gateway'), $order->get_payment_method_title(), $transaction_id));
                        $order->add_order_note('Seller Protection Status: ' . ppcp_readable($seller_protection));
                        $order->update_status('on-hold');
                        $order->add_order_note(__('Payment authorized. Change payment status to processing or complete to capture funds.', 'woo-paypal-gateway'));
                    }
                    WC()->cart->empty_cart();
                    return true;
                } else {
                    if (function_exists('wc_add_notice')) {
                        $error_message = $this->ppcp_get_readable_message($api_response);
                        wc_add_notice($error_message, 'error');
                    }
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_checkout_details($paypal_order_id) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $this->ppcp_add_log_details('Get Order Details');
            $this->ppcp_log('Endpoint: ' . $this->paypal_order_api . $paypal_order_id);
            $response = wp_remote_get($this->paypal_order_api . $paypal_order_id, array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB'),
                'body' => array(),
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response));
                $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                ppcp_set_session('ppcp_paypal_order_id', $paypal_order_id);
                ppcp_set_session('ppcp_paypal_transaction_details', $api_response);
                return $api_response;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_details_from_cart() {
        try {
            $rounded_total = $this->ppcp_get_rounded_total_in_cart();
            $discounts = WC()->cart->get_cart_discount_total();
            $details = array(
                'total_item_amount' => ppcp_round(WC()->cart->cart_contents_total, $this->decimals) + $discounts,
                'order_tax' => ppcp_round(WC()->cart->tax_total + WC()->cart->shipping_tax_total, $this->decimals),
                'shipping' => ppcp_round(WC()->cart->shipping_total, $this->decimals),
                'items' => $this->ppcp_get_paypal_line_items_from_cart(),
                'shipping_address' => $this->ppcp_get_address_from_customer(),
                'email' => WC()->customer->get_billing_email(),
            );
            return $this->ppcp_get_details($details, $discounts, $rounded_total, WC()->cart->total);
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_is_currency_supports_zero_decimal() {
        try {
            return in_array(get_woocommerce_currency(), array('HUF', 'JPY', 'TWD'));
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_rounded_total_in_cart() {
        try {
            $rounded_total = 0;
            foreach (WC()->cart->cart_contents as $cart_item_key => $values) {
                $amount = ppcp_round($values['line_subtotal'] / $values['quantity'], $this->decimals);
                $rounded_total += ppcp_round($amount * $values['quantity'], $this->decimals);
            }
            return $rounded_total;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_paypal_line_items_from_cart() {
        try {
            $items = array();
            foreach (WC()->cart->cart_contents as $cart_item_key => $values) {
                $desc = '';
                $amount = ppcp_round($values['line_subtotal'] / $values['quantity'], $this->decimals);
                $product = $values['data'];
                $name = $product->get_name();
                $sku = $product->get_sku();
                $category = $product->needs_shipping() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS';
                if (is_object($product) && $product->is_type('variation')) {
                    if (!empty($values['variation']) && is_array($values['variation'])) {
                        foreach ($values['variation'] as $key => $value) {
                            $key = str_replace(array('attribute_pa_', 'attribute_', 'Pa_', 'pa_'), '', $key);
                            $desc .= ' ' . ucwords($key) . ': ' . $value;
                        }
                        $desc = trim($desc);
                    }
                }
                $name = wp_strip_all_tags($name);
                if (strlen($name) > 127) {
                    $name = substr($name, 0, 124) . '...';
                }
                $desc = !empty($desc) ? $desc : '';
                if (strlen($desc) > 127) {
                    $desc = substr($desc, 0, 124) . '...';
                }
                $desc = strip_shortcodes($desc);
                $desc = str_replace("\n", " ", $desc);
                $desc = preg_replace('/\s+/', ' ', $desc);
                $item = array(
                    'name' => $name,
                    'description' => $desc,
                    'sku' => $sku,
                    'category' => $category,
                    'quantity' => $values['quantity'],
                    'amount' => $amount,
                );
                $items[] = $item;
            }

            return $items;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_address_from_customer() {
        try {
            $customer = WC()->customer;
            if ($customer->get_shipping_address() || $customer->get_shipping_address_2()) {
                $shipping_first_name = $customer->get_shipping_first_name();
                $shipping_last_name = $customer->get_shipping_last_name();
                $shipping_address_1 = $customer->get_shipping_address();
                $shipping_address_2 = $customer->get_shipping_address_2();
                $shipping_city = $customer->get_shipping_city();
                $shipping_state = $customer->get_shipping_state();
                $shipping_postcode = $customer->get_shipping_postcode();
                $shipping_country = $customer->get_shipping_country();
            } else {
                $shipping_first_name = $customer->get_billing_first_name();
                $shipping_last_name = $customer->get_billing_last_name();
                $shipping_address_1 = $customer->get_billing_address_1();
                $shipping_address_2 = $customer->get_billing_address_2();
                $shipping_city = $customer->get_billing_city();
                $shipping_state = $customer->get_billing_state();
                $shipping_postcode = $customer->get_billing_postcode();
                $shipping_country = $customer->get_billing_country();
            }
            return array(
                'first_name' => $shipping_first_name,
                'last_name' => $shipping_last_name,
                'company' => '',
                'address_1' => $shipping_address_1,
                'address_2' => $shipping_address_2,
                'city' => $shipping_city,
                'state' => $shipping_state,
                'postcode' => $shipping_postcode,
                'country' => $shipping_country,
                'phone' => $customer->get_billing_phone(),
            );
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_details($details, $discounts, $rounded_total, $total) {
        try {
            $discounts = ppcp_round($discounts, $this->decimals);
            $details['order_total'] = ppcp_round(
                    $details['total_item_amount'] + $details['order_tax'] + $details['shipping'] - $discounts, $this->decimals
            );
            $diff = 0;
            if ($details['total_item_amount'] != $rounded_total) {
                $diff = round($details['total_item_amount'] + $discounts - $rounded_total, $this->decimals);
                if (abs($diff) > 0.000001 && 0.0 !== (float) $diff) {
                    $extra_line_item = $this->ppcp_get_extra_offset_line_item($diff);
                    $details['items'][] = $extra_line_item;
                    $details['total_item_amount'] += $extra_line_item['amount'];
                    $details['total_item_amount'] = ppcp_round($details['total_item_amount'], $this->decimals);
                    $details['order_total'] += $extra_line_item['amount'];
                    $details['order_total'] = ppcp_round($details['order_total'], $this->decimals);
                }
            }
            if (0 == $details['total_item_amount']) {
                unset($details['items']);
            }
            if ($details['total_item_amount'] != $rounded_total) {
                unset($details['items']);
            }
            if ($details['total_item_amount'] == $discounts) {
                unset($details['items']);
            } else if ($discounts > 0 && $discounts < $details['total_item_amount'] && !empty($details['items'])) {
                $details['discount'] = $discounts;
            }
            $details['discount'] = $discounts;
            $details['ship_discount_amount'] = 0;
            $wc_order_total = ppcp_round($total, $this->decimals);
            $discounted_total = ppcp_round($details['order_total'], $this->decimals);
            if ($wc_order_total != $discounted_total) {
                if ($discounted_total < $wc_order_total) {
                    $details['order_tax'] += $wc_order_total - $discounted_total;
                    $details['order_tax'] = ppcp_round($details['order_tax'], $this->decimals);
                } else {
                    $details['ship_discount_amount'] += $wc_order_total - $discounted_total;
                    $details['ship_discount_amount'] = ppcp_round($details['ship_discount_amount'], $this->decimals);
                    $details['ship_discount_amount'] = abs($details['ship_discount_amount']);
                }
                $details['order_total'] = $wc_order_total;
            }
            if (!is_numeric($details['shipping'])) {
                $details['shipping'] = 0;
            }
            $lisum = 0;
            if (!empty($details['items'])) {
                foreach ($details['items'] as $li => $values) {
                    $lisum += $values['quantity'] * $values['amount'];
                }
            }
            if (abs($lisum) > 0.000001 && 0.0 !== (float) $diff) {
                $details['items'][] = $this->ppcp_get_extra_offset_line_item($details['total_item_amount'] - $lisum);
            }
            return $details;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_details_from_order($order_id) {
        try {
            $order = wc_get_order($order_id);
            $rounded_total = $this->ppcp_get_rounded_total_in_order($order);
            $details = array(
                'total_item_amount' => ppcp_round($order->get_subtotal(), $this->decimals),
                'order_tax' => ppcp_round($order->get_total_tax(), $this->decimals),
                'shipping' => ppcp_round($order->get_shipping_total(), $this->decimals),
                'items' => $this->ppcp_get_paypal_line_items_from_order($order),
            );
            $details = $this->ppcp_get_details($details, $order->get_total_discount(), $rounded_total, $order->get_total());
            return $details;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_paypal_line_items_from_order($order) {
        try {
            $items = array();
            foreach ($order->get_items() as $cart_item_key => $values) {
                $desc = '';
                $amount = ppcp_round($values['line_subtotal'] / $values['qty'], $this->decimals);
                $product = $values->get_product();
                $name = $product->get_name();
                $sku = $product->get_sku();
                $category = $product->needs_shipping() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS';
                if (is_object($product)) {
                    if ($product->is_type('variation')) {
                        if (!empty($values['variation']) && is_array($values['variation'])) {
                            foreach ($values['variation'] as $key => $value) {
                                $key = str_replace(array('attribute_pa_', 'attribute_', 'Pa_', 'pa_'), '', $key);
                                $desc .= ' ' . ucwords($key) . ': ' . $value;
                            }
                            $desc = trim($desc);
                        }
                    }
                }
                $item = array(
                    'name' => $name,
                    'description' => $desc,
                    'sku' => $sku,
                    'category' => $category,
                    'quantity' => $values['quantity'],
                    'amount' => $amount,
                );
                $items[] = $item;
            }
            return $items;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_get_rounded_total_in_order($order) {
        try {

            $order = wc_get_order($order);
            $rounded_total = 0;
            foreach ($order->get_items() as $cart_item_key => $values) {
                $amount = ppcp_round($values['line_subtotal'] / $values['qty'], $this->decimals);
                $rounded_total += ppcp_round($amount * $values['qty'], $this->decimals);
            }
            return $rounded_total;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_refund_order($order_id, $amount, $reason, $transaction_id) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $this->ppcp_add_log_details('Refund Request');
            $this->ppcp_log('Endpoint: ' . $this->paypal_refund_api . $transaction_id . '/refund');
            $order = wc_get_order($order_id);
            $this->decimals = $this->ppcp_get_number_of_decimal_digits();
            $reason = !empty($reason) ? $reason : 'Refund';
            $body_request['note_to_payer'] = $reason;
            if (!empty($amount) && $amount > 0) {
                $body_request = array(
                    'amount' =>
                    array(
                        'value' => ppcp_round($amount, $this->decimals),
                        'currency_code' => $order->get_currency()
                    )
                );
            }
            $body_request = ppcp_remove_empty_key($body_request);
            $body_request = json_encode($body_request);
            $this->ppcp_log('Refund request: ' . $body_request);
            $response = wp_remote_post($this->paypal_refund_api . $transaction_id . '/refund', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
                $order->add_order_note('Error Failed Message : ' . wc_print_r($error_message, true));
                return new WP_Error('error', $$error_message);
            }
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
            $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
            $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
            if (isset($api_response['status']) && $api_response['status'] == "COMPLETED") {
                $gross_amount = isset($api_response['seller_payable_breakdown']['gross_amount']['value']) ? $api_response['seller_payable_breakdown']['gross_amount']['value'] : '';
                $refund_transaction_id = isset($api_response['id']) ? $api_response['id'] : '';
                $order->add_order_note(
                        sprintf(__('Refunded %1$s - Refund ID: %2$s', 'woo-paypal-gateway'), $gross_amount, $refund_transaction_id)
                );
            } else {
                if (!empty($api_response['details'][0]['description'])) {
                    $order->add_order_note('Error Message : ' . wc_print_r($api_response['details'][0]['description'], true));
                    throw new Exception($api_response['details'][0]['description']);
                }
                return false;
            }
            return true;
        } catch (Exception $ex) {
            return new WP_Error('error', $ex->getMessage());
        }
    }

    public function ppcp_update_order($order) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }

            $patch_request = array();
            $update_amount_request = array();
            $reference_id = ppcp_get_session('ppcp_reference_id');
            $order_id = $order->get_id();
            $cart = $this->ppcp_get_details_from_order($order_id);

            // Shipping or Billing Address
            if ($order->has_shipping_address()) {
                $shipping_address_1 = $order->get_shipping_address_1();
                $shipping_address_2 = $order->get_shipping_address_2();
                $shipping_city = $order->get_shipping_city();
                $shipping_state = $order->get_shipping_state();
                $shipping_postcode = $order->get_shipping_postcode();
                $shipping_country = $order->get_shipping_country();
            } else {
                $shipping_address_1 = $order->get_billing_address_1();
                $shipping_address_2 = $order->get_billing_address_2();
                $shipping_city = $order->get_billing_city();
                $shipping_state = $order->get_billing_state();
                $shipping_postcode = $order->get_billing_postcode();
                $shipping_country = $order->get_billing_country();
            }

            $shipping_address_request = array(
                'address_line_1' => $shipping_address_1,
                'address_line_2' => $shipping_address_2,
                'admin_area_2' => $shipping_city,
                'admin_area_1' => $shipping_state,
                'postal_code' => $shipping_postcode,
                'country_code' => $shipping_country,
            );

            // Always calculate item_total if items exist
            if (isset($cart['total_item_amount']) && $cart['total_item_amount'] > 0) {
                $update_amount_request['item_total'] = array(
                    'currency_code' => get_woocommerce_currency(),
                    'value' => ppcp_round($cart['total_item_amount'], $this->decimals)
                );
            }

            // Additional details if $this->send_items is true
            if ($this->send_items === true) {
                if (isset($cart['discount']) && $cart['discount'] > 0) {
                    $update_amount_request['discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['discount'], $this->decimals)
                    );
                }
                if (isset($cart['shipping']) && $cart['shipping'] > 0) {
                    $update_amount_request['shipping'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['shipping'], $this->decimals)
                    );
                }
                if (isset($cart['ship_discount_amount']) && $cart['ship_discount_amount'] > 0) {
                    $update_amount_request['shipping_discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['ship_discount_amount'], $this->decimals),
                    );
                }
                if (isset($cart['order_tax']) && $cart['order_tax'] > 0) {
                    $update_amount_request['tax_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => ppcp_round($cart['order_tax'], $this->decimals)
                    );
                }
            }

            // Patch request for updating the order amount and shipping
            $patch_request[] = array(
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='$reference_id'/amount",
                'value' => array(
                    'currency_code' => $order->get_currency(),
                    'value' => ppcp_round($cart['order_total'], $this->decimals),
                    'breakdown' => $update_amount_request // Make sure breakdown includes item_total and others
                ),
            );

            // Update shipping address
            $patch_request[] = array(
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='$reference_id'/shipping/address",
                'value' => $shipping_address_request
            );

            // Update invoice ID
            $patch_request[] = array(
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='$reference_id'/invoice_id",
                'value' => $this->invoice_id_prefix . str_replace("#", "", $order->get_order_number())
            );

            // Update custom ID
            $update_custom_id = wp_json_encode(
                    array(
                        'order_id' => $order->get_id(),
                        'order_key' => $order->get_order_key(),
                    )
            );
            $patch_request[] = array(
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='$reference_id'/custom_id",
                'value' => $update_custom_id
            );

            // Convert the patch request array to JSON
            $patch_request_json = json_encode($patch_request);

            // Retrieve the PayPal order ID and send the patch request to update the order
            $paypal_order_id = ppcp_get_session('ppcp_paypal_order_id');
            $this->ppcp_add_log_details('Update order');
            $this->ppcp_log('Endpoint: ' . $this->paypal_order_api . $paypal_order_id);
            $this->ppcp_log('Request: ' . print_r($patch_request_json, true));

            // Send the request to PayPal
            $response = wp_remote_request($this->paypal_order_api . $paypal_order_id, array(
                'timeout' => 60,
                'method' => 'PATCH',
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer " . $this->access_token,
                    "prefer" => "return=representation",
                    'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB',
                    'PayPal-Request-Id' => $this->generate_request_id()
                ),
                'body' => $patch_request_json,
                'cookies' => array()
            ));

            // Handle response errors or log response details
            if (is_wp_error($response)) {
                if (function_exists('wc_add_notice')) {
                    $error_message = $response->get_error_message();
                    $this->ppcp_log('Error Message : ' . wc_print_r($response, true));
                    wc_add_notice($error_message, 'error');
                }
                return false;
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
            }
        } catch (Exception $ex) {
            $this->ppcp_log('Exception: ' . $ex->getMessage());
            if (function_exists('wc_add_notice')) {
                wc_add_notice(__('An error occurred while updating the order.', 'woo-paypal-gateway'), 'error');
            }
        }
    }

    public function ppcp_show_details_authorized_payment($authorization_id) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $this->ppcp_add_log_details('Show details for authorized payment');
            $this->ppcp_log('Endpoint: ' . $this->auth . $authorization_id);
            $response = wp_remote_get($this->auth . $authorization_id, array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB'),
                'body' => array(),
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response));
                $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                ppcp_set_session('ppcp_paypal_transaction_details', $api_response);
                return $api_response;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_capture_authorized_payment($woo_order_id) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $order = wc_get_order($woo_order_id);
            if ($order === false) {
                return false;
            }
            $capture_arg = array(
                'amount' =>
                array(
                    'value' => ppcp_round($order->get_total(), $this->decimals),
                    'currency_code' => $order->get_currency(),
                ),
                'invoice_id' => $this->invoice_id_prefix . str_replace("#", "", $order->get_order_number()),
                'final_capture' => true,
            );
            $body_request = ppcp_remove_empty_key($capture_arg);
            $body_request = json_encode($body_request);
            $authorization_id = $order->get_meta('_auth_transaction_id');
            $this->ppcp_add_log_details('Capture authorized payment');
            $this->ppcp_log('Request : ' . wc_print_r($this->auth . $authorization_id . '/capture', true));
            $response = wp_remote_post($this->auth . $authorization_id . '/capture', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($error_message, 'error');
                }
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->ppcp_log('Response : ' . wc_print_r($api_response, true));
                if (!empty($api_response['id'])) {
                    $return_response['paypal_order_id'] = $api_response['id'];
                    $order->update_meta_data('_paypal_order_id', $api_response['id']);
                    $order->save_meta_data();
                    $payment_source = isset($api_response['payment_source']) ? $api_response['payment_source'] : '';
                    if (!empty($payment_source['card'])) {
                        $card_response_order_note = __('Card Details', 'woo-paypal-gateway');
                        $card_response_order_note .= "\n";
                        $card_response_order_note .= 'Last digits : ' . $payment_source['card']['last_digits'];
                        $card_response_order_note .= "\n";
                        $card_response_order_note .= 'Brand : ' . $payment_source['card']['brand'];
                        $card_response_order_note .= "\n";
                        $card_response_order_note .= 'Card type : ' . $payment_source['card']['type'];
                        $order->add_order_note($card_response_order_note);
                    }
                    $processor_response = isset($api_response['purchase_units']['0']['payments']['captures']['0']['processor_response']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['processor_response'] : '';
                    if (!empty($processor_response['avs_code'])) {
                        $avs_response_order_note = __('Address Verification Result', 'woo-paypal-gateway');
                        $avs_response_order_note .= "\n";
                        $avs_response_order_note .= $processor_response['avs_code'];
                        if (isset($this->AVSCodes[$processor_response['avs_code']])) {
                            $avs_response_order_note .= ' : ' . $this->AVSCodes[$processor_response['avs_code']];
                        }
                        $order->add_order_note($avs_response_order_note);
                    }
                    if (!empty($processor_response['cvv_code'])) {
                        $cvv2_response_code = __('Card Security Code Result', 'woo-paypal-gateway');
                        $cvv2_response_code .= "\n";
                        $cvv2_response_code .= $processor_response['cvv_code'];
                        if (isset($this->CVV2Codes[$processor_response['cvv_code']])) {
                            $cvv2_response_code .= ' : ' . $this->CVV2Codes[$processor_response['cvv_code']];
                        }
                        $order->add_order_note($cvv2_response_code);
                    }
                    $currency_code = isset($api_response['seller_receivable_breakdown']['paypal_fee']['currency_code']) ? $api_response['seller_receivable_breakdown']['paypal_fee']['currency_code'] : '';
                    $value = isset($api_response['seller_receivable_breakdown']['paypal_fee']['value']) ? $api_response['seller_receivable_breakdown']['paypal_fee']['value'] : '';
                    $order->update_meta_data('_paypal_fee', $value);
                    $order->update_meta_data('_paypal_fee_currency_code', $currency_code);
                    $order->save_meta_data();
                    $transaction_id = isset($api_response['id']) ? $api_response['id'] : '';
                    $seller_protection = isset($api_response['seller_protection']['status']) ? $api_response['seller_protection']['status'] : '';
                    $payment_status = isset($api_response['status']) ? $api_response['status'] : '';
                    $order->update_meta_data('_paypal_fee', $value);
                    $order->update_meta_data('_payment_status', $payment_status);
                    $order->save_meta_data();
                    $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'woo-paypal-gateway'), $order->get_payment_method_title(), $transaction_id));
                    $order->add_order_note('Seller Protection Status: ' . ppcp_readable($seller_protection));
                    if ($payment_status === 'COMPLETED') {
                        $order->payment_complete($transaction_id);
                        $order->add_order_note(sprintf(__('Payment via %s : %s.', 'woo-paypal-gateway'), $order->get_payment_method_title(), ucfirst(strtolower($payment_status))));
                    } else {
                        $payment_status_reason = isset($api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason'] : '';
                        ppcp_update_woo_order_status($woo_order_id, $payment_status, $payment_status_reason);
                    }
                    $order->set_transaction_id($transaction_id);
                    return true;
                } else {
                    if (function_exists('wc_add_notice')) {
                        $error_message = $this->ppcp_get_readable_message($api_response);
                        wc_add_notice($error_message, 'error');
                    }
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_add_log_details($action_name = null) {
        $this->ppcp_log(sprintf(__('Payment Gateway for PayPal on WooCommerce: %s', 'woo-paypal-gateway'), WPG_PLUGIN_VERSION));
        $this->ppcp_log(sprintf(__('WooCommerce Version: %s', 'woo-paypal-gateway'), WC_VERSION));
        $mode = $this->is_sandbox ? 'Yes' : 'No';
        $this->ppcp_log("Test Mode: " . $mode);
        $this->ppcp_log('Action Name : ' . $action_name);
    }

    public function ppcp_get_readable_message($error) {
        $message = '';
        if (isset($error['name'])) {
            switch ($error['name']) {
                case 'VALIDATION_ERROR':
                    foreach ($error['details'] as $e) {
                        $message .= "\t" . $e['field'] . "\n\t" . $e['issue'] . "\n\n";
                    }
                    break;
                case 'INVALID_REQUEST':
                    foreach ($error['details'] as $e) {
                        if (isset($e['field']) && isset($e['description'])) {
                            $message .= "\t" . $e['field'] . "\n\t" . $e['description'] . "\n\n";
                        } elseif (isset($e['issue'])) {
                            $message .= "\t" . $e['issue'] . "n\n";
                        }
                    }
                    break;
                case 'BUSINESS_ERROR':
                    $message .= $error['message'];
                    break;
                case 'UNPROCESSABLE_ENTITY' :
                    foreach ($error['details'] as $e) {
                        $message .= "\t" . $e['issue'] . ": " . $e['description'] . "\n\n";
                    }
                    break;
            }
        }
        if (!empty($message)) {
            
        } else if (!empty($error['message'])) {
            $message = $error['message'];
        } else if (!empty($error['error_description'])) {
            $message = $error['error_description'];
        } else {
            $message = $error;
        }
        return $message;
    }

    public function ppcp_create_webhooks_request() {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            if ($this->is_valid_for_use() === true && $this->access_token) {
                $webhook_request = array();
                $webhook_request['url'] = add_query_arg(array('ppcp_action' => 'webhook_handler', 'utm_nooverride' => '1'), WC()->api_request_url('PPCP_Paypal_Checkout_For_Woocommerce_Button_Manager'));
                $webhook_request['event_types'][] = array('name' => 'CHECKOUT.ORDER.APPROVED');
                $webhook_request['event_types'][] = array('name' => 'PAYMENT.AUTHORIZATION.CREATED');
                $webhook_request['event_types'][] = array('name' => 'PAYMENT.AUTHORIZATION.VOIDED');
                $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.COMPLETED');
                $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.DENIED');
                $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.PENDING');
                $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.REFUNDED');
                $webhook_request = ppcp_remove_empty_key($webhook_request);
                $webhook_request = json_encode($webhook_request);
                $response = wp_remote_post($this->webhook, array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'httpversion' => '1.1',
                    'blocking' => true,
                    'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    'body' => $webhook_request,
                    'cookies' => array()
                        )
                );
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
                } else {
                    ob_start();
                    $return_response = array();
                    $api_response = json_decode(wp_remote_retrieve_body($response), true);
                    $this->ppcp_log('function called: ppcp_create_webhooks_request');
                    if (!empty($api_response['id'])) {
                        $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                        $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                        $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                        update_option($this->webhook_id, $api_response['id']);
                    } else {
                        $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                        $error = $this->ppcp_get_readable_message($api_response);
                        $this->ppcp_log('Response Message: ' . wc_print_r($error, true));
                        if (isset($api_response['name']) && strpos($api_response['name'], 'WEBHOOK_NUMBER_LIMIT_EXCEEDED') !== false) {
                            $this->ppcp_delete_first_webhook();
                        } elseif ($api_response['name'] && strpos($api_response['name'], 'WEBHOOK_URL_ALREADY_EXISTS') !== false) {
                            $this->ppcp_delete_exiting_webhook();
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_delete_exiting_webhook() {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $response = wp_remote_get($this->webhook, array('headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB')));
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($api_response['webhooks'])) {
                foreach ($api_response['webhooks'] as $key => $webhooks) {
                    if (isset($webhooks['url']) && strpos($webhooks['url'], site_url()) !== false) {
                        $response = wp_remote_request($this->webhook . '/' . $webhooks['id'], array(
                            'timeout' => 60,
                            'method' => 'DELETE',
                            'redirection' => 5,
                            'httpversion' => '1.1',
                            'blocking' => true,
                            'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                            'cookies' => array()
                                )
                        );
                        $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                        $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                        $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_delete_first_webhook() {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $response = wp_remote_get($this->webhook, array('headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB')));
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($api_response['webhooks'])) {
                foreach ($api_response['webhooks'] as $key => $webhooks) {
                    $response = wp_remote_request($this->webhook . $webhooks['id'], array(
                        'timeout' => 60,
                        'method' => 'DELETE',
                        'redirection' => 5,
                        'httpversion' => '1.1',
                        'blocking' => true,
                        'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                        'cookies' => array()
                            )
                    );
                    $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_handle_webhook_request_handler() {
        try {
            $bool = false;
            if ($this->is_valid_for_use() === true && $this->access_token == false) {
                $this->ppcp_get_access_token();
            }
            if ($this->is_valid_for_use() === true && $this->access_token) {
                $posted_raw = ppcp_get_raw_data();
                if (empty($posted_raw)) {
                    return false;
                }
                $headers = $this->getallheaders_value();
                $headers = array_change_key_case($headers, CASE_UPPER);
                $posted = json_decode($posted_raw, true);
                $this->ppcp_log('Response Body: ' . wc_print_r($posted, true));
                $this->ppcp_log('Headers: ' . wc_print_r($headers, true));
                $bool = $this->ppcp_validate_webhook_event($headers, $posted);
                if ($bool) {
                    $this->ppcp_update_order_status($posted);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function getallheaders_value() {
        try {
            if (!function_exists('getallheaders')) {
                return $this->getallheaders_custome();
            } else {
                return getallheaders();
            }
        } catch (Exception $ex) {
            
        }
    }

    public function getallheaders_custome() {
        try {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_validate_webhook_event($headers, $body) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $this->ppcp_prepare_webhook_validate_request($headers, $body);
            if (!empty($this->request)) {
                $response = wp_remote_post($this->webhook_url, array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'httpversion' => '1.1',
                    'blocking' => true,
                    'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    'body' => json_encode($this->request),
                    'cookies' => array()
                        )
                );
            } else {
                return false;
            }
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->ppcp_log('Webhook Error Message : ' . wc_print_r($error_message, true));
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->ppcp_log('Response Body: ' . wc_print_r($api_response, true));
                if (!empty($api_response['verification_status']) && 'SUCCESS' === $api_response['verification_status']) {
                    $this->ppcp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->ppcp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    return true;
                } else {
                    return false;
                }
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    public function ppcp_prepare_webhook_validate_request($headers, $body) {
        try {
            $this->request = array();
            $webhook_id = get_option($this->webhook_id, false);
            $this->request['transmission_id'] = $headers['PAYPAL-TRANSMISSION-ID'];
            $this->request['transmission_time'] = $headers['PAYPAL-TRANSMISSION-TIME'];
            $this->request['cert_url'] = $headers['PAYPAL-CERT-URL'];
            $this->request['auth_algo'] = $headers['PAYPAL-AUTH-ALGO'];
            $this->request['transmission_sig'] = $headers['PAYPAL-TRANSMISSION-SIG'];
            $this->request['webhook_id'] = $webhook_id;
            $this->request['webhook_event'] = $body;
        } catch (Exception $ex) {
            
        }
    }

    public function ppcp_update_order_status($posted) {
        $order = false;
        if (!empty($posted['resource']['purchase_units'][0]['custom_id'])) {
            $order = $this->ppcp_get_paypal_order($posted['resource']['purchase_units'][0]['custom_id']);
        } elseif (!empty($posted['resource']['custom_id'])) {
            $order = $this->ppcp_get_paypal_order($posted['resource']['custom_id']);
        }
        if ($order && isset($posted['event_type']) && !empty($posted['event_type'])) {
            $order->add_order_note('Webhooks Update : ' . $posted['summary']);
            if (isset($posted['resource']['status']) && !empty($posted['resource']['status'])) {
                $this->ppcp_log('Payment status: ' . $posted['resource']['status']);
            }
            if (isset($posted['resource']['id']) && !empty($posted['resource']['id'])) {
                $this->ppcp_log('PayPal Transaction ID: ' . $posted['resource']['id']);
            }
            if (isset($posted['resource']['status']) && isset($posted['resource']['id'])) {
                switch ($posted['event_type']) {
                    case 'PAYMENT.AUTHORIZATION.CREATED' :
                        $this->payment_status_on_hold($order, $posted);
                        break;
                    case 'PAYMENT.AUTHORIZATION.VOIDED' :
                        $this->payment_status_voided($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.COMPLETED' :
                        $this->payment_status_completed($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.DENIED' :
                        $this->payment_status_denied($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.PENDING' :
                        $this->payment_status_on_hold($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.REFUNDED' :
                        $this->payment_status_refunded($order, $posted);
                        break;
                }
            }
        }
    }

    public function payment_status_completed($order, $posted) {
        if ($order->has_status(wc_get_is_paid_statuses())) {
            $this->ppcp_log('Aborting, Order #' . $order->get_id() . ' is already complete.');
            exit;
        }
        if ('COMPLETED' === $posted['resource']['status']) {
            $this->payment_complete($order);
        } else {
            if ('created' === $posted['resource']['status']) {
                $this->payment_on_hold($order, __('Payment authorized. Change payment status to processing or complete to capture funds.', 'woo-paypal-gateway'));
            } else {
                if (!empty($posted['pending_reason'])) {
                    $this->payment_on_hold($order, sprintf(__('Payment pending (%s).', 'woo-paypal-gateway'), $posted['pending_reason']));
                }
            }
        }
    }

    public function payment_complete($order, $txn_id = '', $note = '') {
        if (!$order->has_status(array('processing', 'completed'))) {
            $order->add_order_note($note);
            $order->payment_complete($txn_id);
            WC()->cart->empty_cart();
        }
    }

    public function payment_on_hold($order, $reason = '') {
        if (!$order->has_status(array('processing', 'completed', 'refunded'))) {
            $order->update_status('on-hold', $reason);
        }
    }

    public function payment_status_pending($order, $posted) {
        if (!$order->has_status(array('processing', 'completed', 'refunded'))) {
            $this->payment_status_completed($order, $posted);
        }
    }

    public function payment_status_failed($order) {
        if (!$order->has_status(array('failed'))) {
            $order->update_status('failed');
        }
    }

    public function payment_status_denied($order) {
        $this->payment_status_failed($order);
    }

    public function payment_status_expired($order) {
        $this->payment_status_failed($order);
    }

    public function payment_status_voided($order) {
        $this->payment_status_failed($order);
    }

    public function payment_status_refunded($order) {
        if (!$order->has_status(array('refunded'))) {
            $order->update_status('refunded');
        }
    }

    public function payment_status_on_hold($order) {
        if ($order->has_status(array('pending'))) {
            $order->update_status('on-hold');
        }
    }

    public function ppcp_get_paypal_order($raw_custom) {
        $custom = json_decode($raw_custom);
        if ($custom && is_object($custom)) {
            $order_id = $custom->order_id;
            $order_key = $custom->order_key;
        } else {
            $this->ppcp_log('Order ID and key were not found in "custom_id".');
            return false;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            $order_id = wc_get_order_id_by_order_key($order_key);
            $order = wc_get_order($order_id);
        }
        if (!$order || !hash_equals($order->get_order_key(), $order_key)) {
            $this->ppcp_log('Order Keys do not match.');
            return false;
        }
        $this->ppcp_log('Order  match : ' . $order_id);

        return $order;
    }

    public function generate_request_id() {
        static $pid = -1;
        static $addr = -1;

        if ($pid == -1) {
            $pid = getmypid();
        }

        if ($addr == -1) {
            if (array_key_exists('SERVER_ADDR', $_SERVER)) {
                $addr = ip2long($_SERVER['SERVER_ADDR']);
            } else {
                $addr = php_uname('n');
            }
        }

        return $addr . $pid . $_SERVER['REQUEST_TIME'] . mt_rand(0, 0xffff);
    }

    public function ppcp_set_payer_details($woo_order_id, $body_request) {
        if ($woo_order_id != null) {
            $order = wc_get_order($woo_order_id);
            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
            $billing_email = $order->get_billing_email();
            $billing_phone = $order->get_billing_phone();
            if (!empty($billing_email)) {
                $body_request['payer']['email_address'] = $billing_email;
            }
            if (!empty($billing_phone)) {
                $body_request['payer']['phone']['phone_number']['national_number'] = preg_replace('/[^0-9]/', '', $billing_phone);
            }
            if (!empty($first_name)) {
                $body_request['payer']['name']['given_name'] = $first_name;
            }
            if (!empty($last_name)) {
                $body_request['payer']['name']['surname'] = $last_name;
            }
            $address_1 = $order->get_billing_address_1();
            $address_2 = $order->get_billing_address_2();
            $city = $order->get_billing_city();
            $state = $order->get_billing_state();
            $postcode = $order->get_billing_postcode();
            $country = $order->get_billing_country();
            if (!empty($address_1) && !empty($city) && !empty($state) && !empty($postcode) && !empty($country)) {
                $body_request['payer']['address'] = array(
                    'address_line_1' => $address_1,
                    'address_line_2' => $address_2,
                    'admin_area_2' => $city,
                    'admin_area_1' => $state,
                    'postal_code' => $postcode,
                    'country_code' => $country,
                );
            }
        } else {
            if (is_user_logged_in()) {
                $customer = WC()->customer;
                $first_name = $customer->get_billing_first_name();
                $last_name = $customer->get_billing_last_name();
                $address_1 = $customer->get_billing_address_1();
                $address_2 = $customer->get_billing_address_2();
                $city = $customer->get_billing_city();
                $state = $customer->get_billing_state();
                $postcode = $customer->get_billing_postcode();
                $country = $customer->get_billing_country();
                $email_address = WC()->customer->get_billing_email();
                $billing_phone = $customer->get_billing_phone();
                if (!empty($first_name)) {
                    $body_request['payer']['name']['given_name'] = $first_name;
                }
                if (!empty($last_name)) {
                    $body_request['payer']['name']['surname'] = $last_name;
                }
                if (!empty($email_address)) {
                    $body_request['payer']['email_address'] = $email_address;
                }
                if (!empty($billing_phone)) {
                    $body_request['payer']['phone']['phone_number']['national_number'] = preg_replace('/[^0-9]/', '', $billing_phone);
                }
                if (!empty($address_1) && !empty($city) && !empty($state) && !empty($postcode) && !empty($country)) {
                    $body_request['payer']['address'] = array(
                        'address_line_1' => $address_1,
                        'address_line_2' => $address_2,
                        'admin_area_2' => $city,
                        'admin_area_1' => $state,
                        'postal_code' => $postcode,
                        'country_code' => $country,
                    );
                }
            }
        }
        return $body_request;
    }

    public function ppcp_regular_create_order_request($woo_order_id = null, $return_url = true) {
        try {
            if ($this->access_token === false) {
                $this->access_token = $this->ppcp_get_access_token();
            }
            $return_response = [];
            if ($this->ppcp_get_order_total($woo_order_id) === 0) {
                $wc_notice = __('Sorry, your session has expired.', 'woo-paypal-gateway');
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($wc_notice);
                }
                wp_send_json_error($wc_notice);
                exit();
            }
            if ($woo_order_id == null) {
                $cart = $this->ppcp_get_details_from_cart();
            } else {
                $cart = $this->ppcp_get_details_from_order($woo_order_id);
            }
            $decimals = $this->ppcp_get_number_of_decimal_digits();
            $reference_id = wc_generate_order_key();
            ppcp_set_session('reference_id', $reference_id);
            $intent = ($this->paymentaction === 'capture') ? 'CAPTURE' : 'AUTHORIZE';
            $body_request = array(
                'intent' => $intent,
                'application_context' => $this->ppcp_application_context($return_url = true),
                'payment_method' => array('payee_preferred' => ($this->payee_preferred) ? 'IMMEDIATE_PAYMENT_REQUIRED' : 'UNRESTRICTED'),
                'purchase_units' =>
                array(
                    0 =>
                    array(
                        'reference_id' => $reference_id,
                        'amount' =>
                        array(
                            'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), $cart['order_total']),
                            'value' => $cart['order_total'],
                            'breakdown' => array()
                        )
                    ),
                ),
            );
            if ($woo_order_id != null) {
                $order = wc_get_order($woo_order_id);
                $body_request['purchase_units'][0]['invoice_id'] = $this->invoice_prefix . str_replace("#", "", $order->get_order_number());
                $body_request['purchase_units'][0]['custom_id'] = apply_filters('ppcp_custom_id', $this->invoice_prefix . str_replace("#", "", $order->get_order_number()), $order);
            } else {
                $body_request['purchase_units'][0]['invoice_id'] = $reference_id;
                $body_request['purchase_units'][0]['custom_id'] = apply_filters('ppcp_custom_id', $reference_id, '');
            }
            $body_request['purchase_units'][0]['payee']['merchant_id'] = $this->merchant_id;
            if ($this->send_items === true) {
                if (isset($cart['total_item_amount']) && $cart['total_item_amount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['item_total'] = array(
                        'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), $cart['total_item_amount']),
                        'value' => $cart['total_item_amount'],
                    );
                }
                if (isset($cart['shipping']) && $cart['shipping'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['shipping'] = array(
                        'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), $cart['shipping']),
                        'value' => $cart['shipping'],
                    );
                }
                if (isset($cart['ship_discount_amount']) && $cart['ship_discount_amount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['shipping_discount'] = array(
                        'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), ppcp_round($cart['ship_discount_amount'], $decimals)),
                        'value' => ppcp_round($cart['ship_discount_amount'], $decimals),
                    );
                }
                if (isset($cart['order_tax']) && $cart['order_tax'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['tax_total'] = array(
                        'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), $cart['order_tax']),
                        'value' => $cart['order_tax'],
                    );
                }
                if (isset($cart['discount']) && $cart['discount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['discount'] = array(
                        'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), $cart['discount']),
                        'value' => $cart['discount'],
                    );
                }
                if (isset($cart['items']) && !empty($cart['items'])) {
                    foreach ($cart['items'] as $key => $order_items) {
                        $description = !empty($order_items['description']) ? strip_shortcodes($order_items['description']) : '';
                        $product_name = !empty($order_items['name']) ? wp_strip_all_tags($order_items['name']) : '';
                        if (strlen($description) > 127) {
                            $description = substr($description, 0, 124) . '...';
                        }
                        if (strlen($product_name) > 127) {
                            $product_name = substr($product_name, 0, 124) . '...';
                        }
                        $body_request['purchase_units'][0]['items'][$key] = array(
                            'name' => $product_name,
                            'description' => html_entity_decode($description, ENT_NOQUOTES, 'UTF-8'),
                            'sku' => !empty($order_items['sku']) ? $order_items['sku'] : '',
                            'category' => !empty($order_items['category']) ? $order_items['category'] : '',
                            'quantity' => $order_items['quantity'],
                            'unit_amount' => array(
                                'currency_code' => apply_filters('ppcp_woocommerce_currency', $this->ppcp_get_currency($woo_order_id), $order_items['amount']),
                                'value' => ppcp_round($order_items['amount'], $this->decimals)
                            ),
                        );
                    }
                }
            }
            if ($woo_order_id != null) {
                $order = wc_get_order($woo_order_id);
                if ($order->has_shipping_address()) {
                    $shipping_first_name = $order->get_shipping_first_name();
                    $shipping_last_name = $order->get_shipping_last_name();
                    $shipping_address_1 = $order->get_shipping_address_1();
                    $shipping_address_2 = $order->get_shipping_address_2();
                    $shipping_city = $order->get_shipping_city();
                    $shipping_state = $order->get_shipping_state();
                    $shipping_postcode = $order->get_shipping_postcode();
                    $shipping_country = $order->get_shipping_country();
                } else {
                    $shipping_first_name = $order->get_billing_first_name();
                    $shipping_last_name = $order->get_billing_last_name();
                    $shipping_address_1 = $order->get_billing_address_1();
                    $shipping_address_2 = $order->get_billing_address_2();
                    $shipping_city = $order->get_billing_city();
                    $shipping_state = $order->get_billing_state();
                    $shipping_postcode = $order->get_billing_postcode();
                    $shipping_country = $order->get_billing_country();
                }
                $shipping_country = strtoupper($shipping_country);
                if ($order->needs_shipping_address() || WC()->cart->needs_shipping()) {
                    if (!empty($shipping_first_name) && !empty($shipping_last_name)) {
                        $body_request['purchase_units'][0]['shipping']['name']['full_name'] = $shipping_first_name . ' ' . $shipping_last_name;
                    }
                    ppcp_set_session('is_shipping_added', 'yes');
                    $body_request['purchase_units'][0]['shipping']['address'] = array(
                        'address_line_1' => $shipping_address_1,
                        'address_line_2' => $shipping_address_2,
                        'admin_area_2' => $shipping_city,
                        'admin_area_1' => $shipping_state,
                        'postal_code' => $shipping_postcode,
                        'country_code' => $shipping_country,
                    );
                }
            } else {
                if (true === WC()->cart->needs_shipping()) {
                    if (is_user_logged_in()) {
                        if (!empty($cart['shipping_address']['first_name']) && !empty($cart['shipping_address']['last_name'])) {
                            $body_request['purchase_units'][0]['shipping']['name']['full_name'] = $cart['shipping_address']['first_name'] . ' ' . $cart['shipping_address']['last_name'];
                        }
                        if (!empty($cart['shipping_address']['address_1']) && !empty($cart['shipping_address']['city']) && !empty($cart['shipping_address']['country'])) {
                            $body_request['purchase_units'][0]['shipping']['address'] = array(
                                'address_line_1' => $cart['shipping_address']['address_1'],
                                'address_line_2' => $cart['shipping_address']['address_2'],
                                'admin_area_2' => $cart['shipping_address']['city'],
                                'admin_area_1' => $cart['shipping_address']['state'],
                                'postal_code' => $cart['shipping_address']['postcode'],
                                'country_code' => strtoupper($cart['shipping_address']['country']),
                            );
                            ppcp_set_session('is_shipping_added', 'yes');
                        }
                    }
                }
            }
            $body_request = $this->ppcp_set_payer_details($woo_order_id, $body_request);
            $body_request = ppcp_remove_empty_key($body_request);
            $body_request = json_encode($body_request);
            $this->api_response = wp_remote_post($this->paypal_order_api, array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Bearer " . $this->access_token, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($this->api_response)) {
                $error_message = $this->api_response->get_error_message();
                $this->ppcp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                if (ob_get_length()) {
                    ob_end_clean();
                }
                $this->api_response = json_decode(wp_remote_retrieve_body($this->api_response), true);
                if (!empty($this->api_response['status'])) {
                    if (!empty(isset($woo_order_id) && !empty($woo_order_id))) {
                        $order->update_meta_data('_paypal_order_id', $this->api_response['id']);
                        $order->save();
                    }
                    if (!empty($this->api_response['links'])) {
                        foreach ($this->api_response['links'] as $key => $link_result) {
                            if ('approve' === $link_result['rel'] || 'payer-action' === $link_result['rel']) {
                                return array(
                                    'result' => 'success',
                                    'redirect' => $link_result['href']
                                );
                            }
                        }
                    }
                    return array(
                        'result' => 'fail',
                        'redirect' => ''
                    );
                } else {
                    $error_email_notification_param = array(
                        'request' => 'create_order',
                        'order_id' => $woo_order_id
                    );
                    $error_message = $this->ppcp_get_readable_message($this->api_response, $error_email_notification_param);
                    if (!empty(isset($woo_order_id) && !empty($woo_order_id))) {
                        $order->add_order_note($error_message);
                    }
                    if (function_exists('wc_add_notice')) {
                        wc_add_notice(__('This payment was unable to be processed successfully. Please try again with another payment method.', 'woo-paypal-gateway'), 'error');
                    }
                    return array(
                        'result' => 'fail',
                        'redirect' => ''
                    );
                }
            }
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getFile() . ' ' . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function ppcp_get_order_total() {
        global $product;
        $total = 0;
        $order_id = absint(get_query_var('order-pay'));
        if (is_product()) {
            $total = $product->get_price();
        } elseif (0 < $order_id) {
            $order = wc_get_order($order_id);
            $total = (float) $order->get_total();
        } elseif (isset(WC()->cart) && 0 < WC()->cart->total) {
            $total = (float) WC()->cart->total;
        }
        return $total;
    }

    public function ppcp_get_currency($woo_order_id = null) {
        $currency_code = '';

        if ($woo_order_id != null) {
            $order = wc_get_order($woo_order_id);
            $currency_code = $order->get_currency();
        } else {
            $currency_code = get_woocommerce_currency();
        }

        return $currency_code;
    }

    public function ppcp_regular_capture() {
        if (isset($_GET['token']) && !empty($_GET['token'])) {
            ppcp_set_session('ppcp_paypal_order_id', wc_clean($_GET['token']));
        } else {
            wp_redirect(wc_get_checkout_url());
            exit();
        }
        $order_id = ppcp_get_awaiting_payment_order_id();
        if (ppcp_is_valid_order($order_id) === false || empty($order_id)) {
            wp_redirect(wc_get_checkout_url());
            exit();
        }
        $order = wc_get_order($order_id);
        if ($this->paymentaction === 'capture') {
            $is_success = $this->ppcp_order_capture_request($order_id, $need_to_update_order = false);
        } else {
            $is_success = $this->ppcp_order_auth_request($order_id);
        }
        $order->update_meta_data('_paymentaction', $this->paymentaction);
        $order->update_meta_data('_enviorment', ($this->is_sandbox) ? 'sandbox' : 'live');
        $order->save_meta_data();
        if ($is_success) {
            unset(WC()->session->ppcp_session);
            WC()->cart->empty_cart();
            wp_redirect(apply_filters('woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order));
        } else {
            unset(WC()->session->ppcp_session);
            WC()->session->set('reload_checkout', null);
            wp_redirect(wc_get_checkout_url());
        }
        exit();
    }
}
