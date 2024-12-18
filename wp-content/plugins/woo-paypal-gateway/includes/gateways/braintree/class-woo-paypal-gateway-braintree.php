<?php

/**
 * Woo_PayPal_Gateway_Braintree class.
 *
 * @extends WC_Payment_Gateway_CC
 */
class Woo_PayPal_Gateway_Braintree extends WC_Payment_Gateway_CC {

    /**
     * Constructor
     */
    public $result;
    public $order_id;
    public $current_user_id;
    public $redirect_endpoint;
    public $return_result;
    public $request;
    public $order;
    public $merchant_account_id;
    public $braintree_customer_id;
    public $sandbox;
    public $environment;
    public $merchant_id;
    public $private_key;
    public $public_key;
    public $debug;
    public $invoice_prefix;
    public $gateway;

    function __construct() {
        $this->id = 'wpg_braintree';
        $this->icon = $this->get_option('card_icon', WPG_PLUGIN_ASSET_URL . 'assets/images/wpg_cards.png');
        if (is_ssl()) {
            $this->icon = preg_replace("/^http:/i", "https:", $this->icon);
        }
        $this->icon = apply_filters('woocommerce_wpg_braintree_icon', $this->icon);
        $this->method_title = 'Braintree';
        $this->method_description = __('Credit Card payments Powered by PayPal / Braintree.', 'woo-paypal-gateway');
        $this->supports = array(
            'products',
            'refunds',
            'add_payment_method',
            'tokenization'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->merchant_account_id = NULL;
        $this->braintree_customer_id = NULL;
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->sandbox = 'yes' === $this->get_option('sandbox', 'yes');
        $this->environment = $this->sandbox == false ? 'production' : 'sandbox';
        $this->merchant_id = $this->sandbox == false ? $this->get_option('live_merchant_id') : $this->get_option('sandbox_merchant_id');
        $this->private_key = $this->sandbox == false ? $this->get_option('live_private_key') : $this->get_option('sandbox_private_key');
        $this->public_key = $this->sandbox == false ? $this->get_option('live_public_key') : $this->get_option('sandbox_public_key');
        $this->debug = 'yes' === $this->get_option('debug', 'yes');
        $this->invoice_prefix = $this->get_option('invoice_prefix');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wpg_braintree_before_process_payment', array($this, 'wpg_braintree_before_process_payment'), 10, 1);
        add_action('wpg_before_add_payment_method', array($this, 'wpg_before_add_payment_method'), 10);
        if ($this->is_available()) {
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'), 0);
        }
        $this->result;
        $this->order_id = NULL;
        $this->current_user_id;
        $this->redirect_endpoint = 'account';
        $this->return_result;
        $this->request;
        $this->order;
    }

    /**
     * Check if this gateway is enabled
     */
    public function is_available() {
        if ('yes' != $this->enabled) {
            return false;
        }
        if (!$this->merchant_id || !$this->public_key || !$this->private_key) {
            return false;
        }
        return true;
    }

    public function init_form_fields() {
        $this->form_fields = include( 'settings-braintree.php' );
    }

    public function payment_fields() {
        if (is_checkout() || is_checkout_pay_page()) {
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
            $clientToken = $this->wpg_braintree_get_client_token();
            ?>
            <div id="braintree-cc-form" class="wc-payment-form">
                <fieldset>
                    <div id="wpg_dropin-container"></div>
                </fieldset>
            </div>
            <?php
            if (is_ajax() || is_add_payment_method_page()) {
                $this->wpg_braintree_dropin_script($clientToken);
            }
        }
    }

    public function payment_fields_block() {
        if (is_checkout() || is_checkout_pay_page()) {
            $clientToken = $this->wpg_braintree_get_client_token();
            $this->wpg_braintree_dropin_script($clientToken);
        }
    }

    private function wpg_braintree_dropin_script($clientToken) {
        ?>
        <script type="text/javascript">
            var init_braintree = function () {
                var authorization = '<?php echo wc_clean($clientToken); ?>';
                var submitButton = document.querySelector('#place_order');
                var ccForm = jQuery('form.checkout, #order_review, #add_payment_method');
                var $form = jQuery('form.checkout, #order_review, #add_payment_method');
                var braintree_form = jQuery('#braintree-cc-form');
                braintree.dropin.create({
                    authorization: authorization,
                    container: '#wpg_dropin-container',
                    paypal: {
                        flow: 'vault'
                    },
                    paypalCredit: {
                        flow: 'vault'
                    }
                }, function (err, dropinInstance) {
                    if (err) {
                        console.log("configuration error " + err);
                        jQuery('.woocommerce-error, .wpg_braintree_token', ccForm).remove();
                        ccForm.prepend('<ul class="woocommerce-error"><li>' + err + '</li></ul>');
                        ccForm.unblock();
                        console.error(err);
                        return;
                    }
                    dropinInstance.on('paymentMethodRequestable', function () {
                        submitButton.removeAttribute('disabled');
                    });
                    dropinInstance.on('noPaymentMethodRequestable', function () {
                        submitButton.setAttribute('disabled', true);
                    });
                    if (jQuery('#payment_method_wpg_braintree').is(':checked')) {
                        submitButton.addEventListener('click', function (event) {
                            event.preventDefault();
                            dropinInstance.requestPaymentMethod(function (err, payload) {
                                console.log(payload);
                                if (err) {
                                    jQuery('.woocommerce-error, .braintree-token', ccForm).remove();
                                    ccForm.prepend('<ul class="woocommerce-error"><li>' + err + '</li></ul>');
                                    console.log("configuration error " + err);
                                    ccForm.unblock();
                                    return;
                                }
                                if (payload.nonce) {
                                    ccForm.find('input.wpg_braintree_token').remove();
                                    ccForm.append('<input type="hidden" class="wpg_braintree_token" name="wpg_braintree_token" value="' + payload.nonce + '"/>');
                                    $form.submit();
                                }
                            });
                        }, false);
                    }
                });
            };
            jQuery(window).ready(function () {
                init_braintree();
            });
            jQuery('form.checkout').on('checkout_place_order_wpg_braintree', function () {
                console.log('submit');
                return braintreeFormHandler();
            });
            function braintreeFormHandler() {
                if (jQuery('#payment_method_wpg_braintree').is(':checked')) {
                    if (jQuery('.wpg_braintree_token').length > 0) {
                        return true;
                    } else {
                        return false;
                    }
                }
                //return true;
            }
        </script>
        <?php
    }

    public function wpg_braintree_before_process_payment() {
        if ($this->wpg_braintree_is_user_logged_in()) {
            $this->wpg_braintree_create_customer();
            $this->wpg_before_add_payment_method($redirect_endpoint = 'checkout');
        }
    }

    public function wpg_braintree_create_customer() {
        $this->wpg_braintree_get_braintree_customer_id();
        if ($this->braintree_customer_id == NULL) {
            $create_customer_request = $this->wpg_braintree_create_customer_request();
            $this->wpg_braintree_request('create_customer', $create_customer_request);
        }
    }

    public function wpg_braintree_request($request_name = NULL, $request_param = NULL) {
        $this->wpg_init_braintree_lib();
        if ($request_name == NULL) {
            return false;
        }
        try {
            switch ($request_name) {
                case 'create_customer':
                    $this->add_log('Braintree\Customer::create request' . print_r($request_param, true));
                    $this->result = $this->gateway->customer()->create(apply_filters('wpg_braintree_create_customer_request_args', $request_param));
                    $this->add_log('Braintree\Customer::create response' . print_r($this->result, true));
                    $this->wpg_braintree_response('create_customer');
                    break;
                case "create_payment_method":
                    $this->add_log('Braintree\PaymentMethod::create request' . print_r($request_param, true));
                    $this->result = $this->gateway->paymentMethod()->create(apply_filters('wpg_braintree_create_payment_method_request_args', $request_param));
                    $this->add_log('Braintree\PaymentMethod::create response' . print_r($this->result, true));
                    $this->wpg_braintree_response('create_payment_method');
                    break;
                case "payment_request":
                    $this->add_log('Braintree\Transaction::sale request' . print_r($request_param, true));
                    $this->result = $this->gateway->transaction()->sale(apply_filters('wpg_braintree_create_payment_request_args', $request_param));
                    $this->add_log('Braintree\Transaction::sale response' . print_r($this->result, true));
                    $this->wpg_braintree_response('payment_request');
                    break;
                case 'create_client_token':
                    $this->add_log('Braintree\ClientToken::generate request' . print_r($request_param, true));
                    $clientToken = $this->gateway->clientToken()->generate($request_param);
                    $this->add_log('Braintree\ClientToken::generate response' . print_r($clientToken, true));
                    return $clientToken;
                case 'get_merchant_account_id':
                    $this->result = $this->gateway->merchantAccount()->all();
                    return $this->wpg_braintree_response('get_merchant_account_id');
                    break;
            }
        } catch (\Braintree\Exception\Authentication $e) {
            $this->add_log($request_name . "Braintree\Exception\Authentication: API keys are incorrect, Please double-check that you haven't accidentally tried to use your sandbox keys in production or vice-versa.");
            wc_add_notice(__($request_name . "Braintree\Exception\Authentication: API keys are incorrect, Please double-check that you haven't accidentally tried to use your sandbox keys in production or vice-versa.", 'woo-paypal-gateway'), 'error');
            wp_redirect(wc_get_cart_url());
            exit;
        } catch (\Braintree\Exception\Authorization $e) {
            $this->add_log($request_name . "Braintree\Exception\Authorization: The API key that you're using is not authorized to perform the attempted action according to the role assigned to the user who owns the API key.");
            wc_add_notice(__($request_name . "Braintree\Exception\Authorization: The API key that you're using is not authorized to perform the attempted action according to the role assigned to the user who owns the API key.", 'woo-paypal-gateway'), 'error');
            wp_redirect(wc_get_cart_url());
            exit;
        } catch (\Braintree\Exception\DownForMaintenance $e) {
            $this->add_log($request_name . "Braintree\Exception\DownForMaintenance: Request times out.");
            wc_add_notice(__($request_name . "Braintree\Exception\DownForMaintenance: Request times out.", 'woo-paypal-gateway'), 'error');
            wp_redirect(wc_get_cart_url());
            exit;
        } catch (\Braintree\Exception\ServerError $e) {
            $this->add_log($request_name . "Braintree\Exception\ServerError " . $e->getMessage());
            wc_add_notice(__($request_name . "Braintree\Exception\ServerError " . $e->getMessage(), 'woo-paypal-gateway'), 'error');
            wp_redirect(wc_get_cart_url());
            exit;
        } catch (\Braintree\Exception\SSLCertificate $e) {
            $this->add_log($request_name . "Braintree\Exception\SSLCertificate " . $e->getMessage());
            wc_add_notice(__($request_name . "Braintree\Exception\SSLCertificate " . $e->getMessage(), 'woo-paypal-gateway'), 'error');
            wp_redirect(wc_get_cart_url());
            exit;
        } catch (\Braintree\Exception\NotFound $e) {
            if ($request_name == 'create_client_token' && $e->getMessage() == 'Customer specified by customer_id does not exist') {
                if ($this->wpg_braintree_is_user_logged_in()) {
                    delete_user_meta($this->current_user_id, 'braintree_customer_id');
                    return $this->gateway->clientToken()->generate();
                }
            } else {
                $this->add_log($request_name . "Braintree\Exception\NotFound " . $e->getMessage());
                wc_add_notice(__($request_name . "Braintree\Exception\NotFound " . $e->getMessage(), 'woo-paypal-gateway'), 'error');
                wp_redirect(wc_get_cart_url());
                exit;
            }
        } catch (\Exception $e) {
            $this->add_log('Error: Unable to complete request. Reason: ' . $e->getMessage());
            wc_add_notice(__('Error: Unable to complete request. Reason: ' . $e->getMessage(), 'woo-paypal-gateway'), 'error');
            wp_redirect(wc_get_cart_url());
            exit;
        }
    }

    public function wpg_braintree_response($request_name = null) {
        if ($request_name === null) {
            return false;
        }

        // Check for a successful response
        if (!empty($this->result->success) && $this->result->success === true) {
            switch ($request_name) {
                case 'create_customer':
                    if (!empty($this->result->customer->id)) {
                        $this->wpg_braintree_save_braintree_customer_id($this->result->customer->id);
                    }
                    break;

                case 'create_payment_method':
                    if (!empty($this->result->paymentMethod)) {
                        $this->wpg_braintree_save_payment_method();
                    }
                    break;

                case 'payment_request':
                    $this->wpg_braintree_order_status_manage();
                    break;

                case 'get_merchant_account_id':
                    // Ensure the response includes merchant accounts
                    if (isset($this->result->merchantAccounts)) {
                        $currencyCode = is_null($this->order_id) ? get_woocommerce_currency() : $this->order->get_currency();

                        foreach ($this->result->merchantAccounts as $merchantAccount) {
                            if ($currencyCode === $merchantAccount->currencyIsoCode) {
                                $this->merchant_account_id = $merchantAccount->id;
                                return $this->merchant_account_id;
                            }
                        }
                    } else {
                        // Handle missing merchant accounts
                        $this->add_log(__('Error: No merchant accounts found in the Braintree response.', 'woo-paypal-gateway'));
                        return new WP_Error('merchant_accounts_not_found', __('No merchant accounts found.', 'woo-paypal-gateway'));
                    }
                    break;
            }
        } else {
            // Handle failed responses or errors
            if (!empty($this->result->errors)) {
                $notice = '';
                foreach ($this->result->errors->deepAll() as $error) {
                    $notice .= $error->code . ': ' . $error->message . "\n";
                }
                wc_add_notice($notice);
            } else {
                $this->add_log(__('Unknown error occurred in Braintree response.', 'woo-paypal-gateway'));
            }

            $this->return_result = array(
                'result' => 'failed',
                'redirect' => ''
            );
        }
    }

    public function wpg_braintree_order_status_manage() {
        $maybe_settled_later = array(
            'settling',
            'settlement_pending',
            'submitted_for_settlement',
        );

        if (in_array($this->result->transaction->status, $maybe_settled_later)) {
            $this->order->payment_complete($this->result->transaction->id);
            $this->order->add_order_note(sprintf(
                            __('%s payment approved! Transaction ID: %s', 'woo-paypal-gateway'),
                            $this->title,
                            $this->result->transaction->id
            ));
            WC()->cart->empty_cart();
        } else {
            $this->add_log(sprintf(
                            'Info: unhandled transaction id = %s, status = %s',
                            $this->result->transaction->id,
                            $this->result->transaction->status
            ));
            $this->order->update_status(
                    'on-hold',
                    sprintf(
                            __('Transaction was submitted to PayPal Braintree but not handled by WooCommerce order, transaction_id: %s, status: %s. Order was put on hold.', 'woo-paypal-gateway'),
                            $this->result->transaction->id,
                            $this->result->transaction->status
                    )
            );
        }

        $this->return_result = array(
            'result' => 'success',
            'redirect' => $this->get_return_url($this->order),
        );
    }

    public function wpg_braintree_create_customer_request() {
        $customer = new WC_Customer($this->current_user_id);
        return $create_customer_request = array('firstName' => $customer->get_billing_first_name(),
            'lastName' => $customer->get_billing_last_name(),
            'company' => $customer->get_billing_company(),
            'email' => $customer->get_billing_email(),
            'phone' => $customer->get_billing_phone(),
            'fax' => '',
            'website' => ''
        );
    }

    public function wpg_braintree_save_payment_token($wpg_braintree_payment_tokens) {
        if (!empty($wpg_braintree_payment_tokens)) {
            try {
                $this->order->update_meta_data('_wpg_braintree_payment_tokens', $wpg_braintree_payment_tokens);
                $this->order->save_meta_data();
            } catch (Exception $ex) {
                
            }
        }
    }

    public function wpg_braintree_save_payment_method() {
        if (!empty($this->result->paymentMethod->token)) {
            if (empty($this->result->paymentMethod->cardType) && !empty($this->result->paymentMethod->billingAgreementId)) {
                update_user_meta($this->current_user_id, 'billing_agreement_id', $this->result->paymentMethod->billingAgreementId);
            }
            if ($this->redirect_endpoint == 'checkout') {
                $this->wpg_braintree_save_payment_token($this->result->paymentMethod->token);
                $this->return_result = array(
                    'result' => 'success',
                    'redirect' => ''
                );
            } else {
                $this->return_result = array(
                    'result' => 'success',
                    'redirect' => wc_get_endpoint_url('add-payment-method')
                );
            }
        }
    }

    public function wpg_before_add_payment_method($redirect_endpoint = NULL) {
        if (!empty($redirect_endpoint)) {
            $this->redirect_endpoint = $redirect_endpoint;
        }

        $payment_method_nonce = self::get_posted_variable('wpg_braintree_token');
        if ($this->redirect_endpoint == 'account' && !empty($payment_method_nonce)) {
            $this->wpg_braintree_create_customer();
        }

        if (!empty($this->braintree_customer_id)) {
            $payment_method_request = array(
                'customerId' => $this->braintree_customer_id,
                'paymentMethodNonce' => $payment_method_nonce,
                'options' => array('failOnDuplicatePaymentMethod' => true),
            );

            if ($this->wpg_braintree_get_merchant_account_id() != NULL) {
                $this->merchant_account_id = $this->wpg_braintree_get_merchant_account_id();
                if (!empty($this->merchant_account_id)) {
                    $payment_method_request['options']['verificationMerchantAccountId'] = $this->merchant_account_id;
                }
            }

            $this->wpg_braintree_request('create_payment_method', $payment_method_request);
        }
    }

    public function add_payment_method() {
        do_action('wpg_before_add_payment_method');
        if (!empty($this->return_result['result']) && 'success' === $this->return_result['result']) {
            return $this->return_result;
        }
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id) {
        $this->order_id = $order_id;
        $this->order = new WC_Order($this->order_id);

        // Trigger action before processing the payment
        do_action('wpg_braintree_before_process_payment');

        // Make the payment request
        $this->wpg_braintree_payment_request();

        // Return the result of the payment processing
        return $this->return_result;
    }

    public function wpg_braintree_payment_request() {
        // Initialize the request array
        $this->request = array();

        // Set the billing details
        $this->wpg_braintree_payment_request_set_billing();

        // Set the shipping details
        $this->wpg_braintree_payment_request_set_shipping();

        // Set the payment method nonce
        $this->wpg_braintree_payment_request_set_payment_method_nonce();

        // Set the customer details
        $this->wpg_braintree_payment_request_set_customer();

        // Set other payment parameters
        $this->wpg_braintree_payment_request_set_other_params();

        // If the request array is not empty, initiate the payment request
        if (!empty($this->request)) {
            $this->wpg_braintree_request('payment_request', $this->request);
        }
    }

    public function wpg_braintree_payment_request_set_billing() {
        $billing_company = $this->order->get_billing_company();
        $billing_first_name = $this->order->get_billing_first_name();
        $billing_last_name = $this->order->get_billing_last_name();
        $billing_address_1 = $this->order->get_billing_address_1();
        $billing_address_2 = $this->order->get_billing_address_2();
        $billing_city = $this->order->get_billing_city();
        $billing_postcode = $this->order->get_billing_postcode();
        $billing_country = $this->order->get_billing_country();
        $billing_state = $this->order->get_billing_state();

        $this->request['billing'] = array(
            'firstName' => $billing_first_name,
            'lastName' => $billing_last_name,
            'company' => $billing_company,
            'streetAddress' => $billing_address_1,
            'extendedAddress' => $billing_address_2,
            'locality' => $billing_city,
            'region' => $billing_state,
            'postalCode' => $billing_postcode,
            'countryCodeAlpha2' => $billing_country,
        );
    }

    public function wpg_braintree_payment_request_set_shipping() {
        $this->request['shipping'] = array(
            'firstName' => $this->order->get_shipping_first_name(),
            'lastName' => $this->order->get_shipping_last_name(),
            'company' => $this->order->get_shipping_company(),
            'streetAddress' => $this->order->get_shipping_address_1(),
            'extendedAddress' => $this->order->get_shipping_address_2(),
            'locality' => $this->order->get_shipping_city(),
            'region' => $this->order->get_shipping_state(),
            'postalCode' => $this->order->get_shipping_postcode(),
            'countryCodeAlpha2' => $this->order->get_shipping_country(),
        );
    }

    public function wpg_braintree_payment_request_set_payment_method_nonce() {
        $wpg_braintree_payment_tokens = $this->order->get_meta('_wpg_braintree_payment_tokens');

        if (!empty($wpg_braintree_payment_tokens)) {
            $this->request['paymentMethodToken'] = $wpg_braintree_payment_tokens;
        } else {
            $this->request['paymentMethodNonce'] = self::get_posted_variable('wpg_braintree_token');
        }
    }

    public function wpg_braintree_payment_request_set_customer() {
        if (!empty($this->braintree_customer_id)) {
            $this->request['customerId'] = $this->braintree_customer_id;
        } else {
            $this->request['customer'] = array(
                'firstName' => $this->order->get_billing_first_name(),
                'lastName' => $this->order->get_billing_last_name(),
                'company' => $this->order->get_billing_company(),
                'phone' => $this->order->get_billing_phone(),
                'email' => $this->order->get_billing_email(),
            );
        }
    }

    public function wpg_braintree_payment_request_set_other_params() {
        $this->request['amount'] = number_format($this->order->get_total(), 2, '.', '');

        if (empty($this->merchant_account_id)) {
            if ($this->wpg_braintree_get_merchant_account_id() != NULL) {
                $this->merchant_account_id = $this->wpg_braintree_get_merchant_account_id();
            }
        }

        if (!empty($this->merchant_account_id)) {
            $this->request['merchantAccountId'] = $this->merchant_account_id;
        }

        $invoice_number = preg_replace("/[^a-zA-Z0-9]/", "", $this->order->get_order_number());
        $this->request['orderId'] = $this->invoice_prefix . $invoice_number;

        $this->request['options'] = array(
            'submitForSettlement' => true,
            'storeInVaultOnSuccess' => true,
        );

        $this->request['channel'] = 'mbjtechnolabs_SP';
    }

    public function wpg_init_braintree_lib() {
        try {
            // Ensure the Braintree PHP SDK is loaded
            if (!class_exists('\Braintree\Gateway')) {
                require_once(WPG_PLUGIN_DIR . '/includes/php-library/braintree/lib/Braintree.php');
            }

            // Initialize the Braintree Gateway instance
            $this->gateway = new \Braintree\Gateway([
                'environment' => $this->environment,
                'merchantId' => $this->merchant_id,
                'publicKey' => $this->public_key,
                'privateKey' => $this->private_key,
            ]);
        } catch (\Exception $ex) {
            // Log the error and return a WP_Error object
            $this->add_log('Error: Unable to Load Braintree. Reason: ' . $ex->getMessage());
            return new WP_Error(404, 'Error: Unable to Load Braintree. Reason: ' . $ex->getMessage());
        }
    }

    public function wpg_braintree_is_user_logged_in() {
        if (is_user_logged_in()) {
            $this->current_user_id = get_current_user_id();
            return $this->current_user_id;
        } else {
            return false;
        }
    }

    public function wpg_braintree_get_braintree_customer_id() {
        if ($this->wpg_braintree_is_user_logged_in()) {
            $this->braintree_customer_id = get_user_meta($this->current_user_id, 'braintree_customer_id', true);
        }
        return $this->braintree_customer_id;
    }

    public function wpg_braintree_save_braintree_customer_id($braintree_customer_id) {
        if ($this->wpg_braintree_is_user_logged_in()) {
            $is_success = update_user_meta($this->current_user_id, 'braintree_customer_id', $braintree_customer_id);
            if ($is_success) {
                $this->braintree_customer_id = $braintree_customer_id;
                return $this->braintree_customer_id;
            }
        }
        return $this->braintree_customer_id;
    }

    public function wpg_braintree_get_client_token() {
        try {
            if ($this->wpg_braintree_get_braintree_customer_id() != NULL) {
                $this->braintree_customer_id = $this->wpg_braintree_get_braintree_customer_id();
            }

            if ($this->wpg_braintree_get_merchant_account_id() != NULL) {
                $this->merchant_account_id = $this->wpg_braintree_get_merchant_account_id();
            }

            return $this->wpg_braintree_generate_client_token($this->braintree_customer_id, $this->merchant_account_id);
        } catch (Exception $ex) {
            $this->add_log('Error generating client token: ' . $ex->getMessage());
            return new WP_Error('braintree_client_token_error', __('Unable to generate client token.', 'woo-paypal-gateway'));
        }
    }

    public function wpg_braintree_generate_client_token($braintree_customer_id = NULL, $merchant_account_id = NULL) {
        $this->wpg_init_braintree_lib();

        try {
            if (!empty($braintree_customer_id) && !empty($merchant_account_id)) {
                $clientToken = $this->wpg_braintree_request('create_client_token', array(
                    'customerId' => $braintree_customer_id,
                    'merchantAccountId' => $merchant_account_id,
                ));
            } elseif (!empty($braintree_customer_id)) {
                $clientToken = $this->wpg_braintree_request('create_client_token', array(
                    'customerId' => $braintree_customer_id,
                ));
            } else {
                $clientToken = $this->wpg_braintree_request('create_client_token', array());
            }

            return $clientToken;
        } catch (Exception $ex) {
            $this->add_log('Error generating client token: ' . $ex->getMessage());
            return new WP_Error('braintree_client_token_error', __('Unable to generate client token.', 'woo-paypal-gateway'));
        }
    }

    public function wpg_braintree_get_merchant_account_id() {
        try {
            $this->merchant_account_id = $this->wpg_braintree_request('get_merchant_account_id', array());
            return $this->merchant_account_id;
        } catch (Exception $ex) {
            $this->add_log('Error fetching merchant account ID: ' . $ex->getMessage());
            return new WP_Error('braintree_merchant_account_error', __('Unable to fetch merchant account ID.', 'woo-paypal-gateway'));
        }
    }

    public function add_log($message) {
        if ($this->debug === 'yes') {
            // Initialize the logger if not already done
            if (empty($this->log)) {
                $this->log = new WC_Logger();
            }

            // Add the log message
            $this->log->add($this->id, $message);
        }
    }

    public function payment_scripts() {
        if (!$this->is_available()) {
            return;
        }

        // Enqueue the Braintree Drop-in JavaScript library
        wp_enqueue_script(
                $this->id . '_js',
                'https://js.braintreegateway.com/web/dropin/1.43.0/js/dropin.min.js',
                array(),
                WC_VERSION,
                false
        );
    }

    public static function get_posted_variable($variable, $default = '') {
        return isset($_POST[$variable]) ? sanitize_text_field($_POST[$variable]) : $default;
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = wc_get_order($order_id);

        if (!$order || !$order->get_transaction_id()) {
            return false;
        }

        $this->wpg_init_braintree_lib();

        try {
            $transaction = $this->gateway->transaction()->find($order->get_transaction_id());
        } catch (\Braintree\Exception\NotFound $e) {
            $this->add_log("Braintree\Transaction::find Exception: " . $e->getMessage());
            return new WP_Error(404, $e->getMessage());
        } catch (\Braintree\Exception\Authentication $e) {
            $this->add_log("Braintree\Transaction::find Authentication Exception: " . $e->getMessage());
            return new WP_Error(404, $e->getMessage());
        } catch (\Braintree\Exception\Authorization $e) {
            $this->add_log("Braintree\Transaction::find Authorization Exception: " . $e->getMessage());
            return new WP_Error(404, $e->getMessage());
        } catch (\Braintree\Exception\DownForMaintenance $e) {
            $this->add_log("Braintree\Transaction::find Maintenance Exception: " . $e->getMessage());
            return new WP_Error(404, $e->getMessage());
        } catch (Exception $e) {
            $this->add_log("Braintree\Transaction::find General Exception: " . $e->getMessage());
            return new WP_Error(404, $e->getMessage());
        }

        if (isset($transaction->status) && $transaction->status === 'submitted_for_settlement') {
            if ($amount == $order->get_total()) {
                try {
                    $result = $this->gateway->transaction()->void($order->get_transaction_id());
                    if ($result->success) {
                        $order->add_order_note(sprintf(__('Refunded %s - Transaction ID: %s', 'woo-paypal-gateway'), wc_price(number_format($amount, 2, '.', '')), $result->transaction->id));
                        return true;
                    } else {
                        $errors = implode(', ', array_map(function ($error) {
                                    return $error->message;
                                }, $result->errors->deepAll()));
                        return new WP_Error(404, 'ec_refund-error', $errors);
                    }
                } catch (\Braintree\Exception\NotFound $e) {
                    $this->add_log("Braintree\Transaction::void NotFound Exception: " . $e->getMessage());
                    return new WP_Error(404, $e->getMessage());
                } catch (Exception $e) {
                    $this->add_log("Braintree\Transaction::void General Exception: " . $e->getMessage());
                    return new WP_Error(404, $e->getMessage());
                }
            } else {
                return new WP_Error(404, 'braintree_refund-error', __('Partial void is not allowed. Please refund the full order amount.', 'woo-paypal-gateway'));
            }
        } elseif (isset($transaction->status) && in_array($transaction->status, ['settled', 'settling'])) {
            try {
                $result = $this->gateway->transaction()->refund($order->get_transaction_id(), $amount);
                if ($result->success) {
                    $order->add_order_note(sprintf(__('Refunded %s - Transaction ID: %s', 'woo-paypal-gateway'), wc_price(number_format($amount, 2, '.', '')), $result->transaction->id));
                    return true;
                } else {
                    $errors = implode(', ', array_map(function ($error) {
                                return $error->message;
                            }, $result->errors->deepAll()));
                    return new WP_Error(404, 'ec_refund-error', $errors);
                }
            } catch (\Braintree\Exception\NotFound $e) {
                $this->add_log("Braintree\Transaction::refund NotFound Exception: " . $e->getMessage());
                return new WP_Error(404, $e->getMessage());
            } catch (Exception $e) {
                $this->add_log("Braintree\Transaction::refund General Exception: " . $e->getMessage());
                return new WP_Error(404, $e->getMessage());
            }
        } else {
            $this->add_log("Error: The transaction cannot be voided nor refunded in its current state: state = {$transaction->status}");
            return new WP_Error(404, "Error: The transaction cannot be voided nor refunded in its current state: state = {$transaction->status}");
        }
    }
}
