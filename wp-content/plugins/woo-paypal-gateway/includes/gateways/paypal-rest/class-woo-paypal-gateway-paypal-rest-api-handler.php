<?php

if (!defined('ABSPATH')) {
    exit;
}

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Transaction;
use PayPal\Api\Refund;
use PayPal\Api\Sale;

class Woo_PayPal_Gateway_PayPal_Rest_API_Handler {

    public $rest_client_id;
    public $rest_secret_id;
    public $sandbox = false;
    public $payment_card;
    public $fundinginstrument;
    public $payer;
    public $order_item;
    public $order_cart_data;
    public $itemlist;
    public $details;
    public $amount;
    public $transaction;
    public $payment;
    public $card_id;
    public $token;
    public $card;
    public $gateway_calculation;
    public $payment_method;
    public $creditcardtoken;
    public $restcreditcardid;
    public $used_payment_token;
    public $rest_settings;

    public function getAuth() {
        try {
            if (!class_exists('Woo_Paypal_Gateway_Calculations')) {
                require_once( WPG_PLUGIN_DIR . '/includes/class-woo-paypal-gateway-calculations.php' );
            }
            $this->gateway_calculation = new Woo_Paypal_Gateway_Calculations();
            $this->mode = $this->sandbox == true ? 'SANDBOX' : 'LIVE';

            $auth = new ApiContext(new OAuthTokenCredential($this->rest_client_id, $this->rest_secret_id));
            $auth->setConfig(array('mode' => $this->mode, 'http.headers.PayPal-Partner-Attribution-Id' => 'mbjtechnolabs_SP', 'log.LogEnabled' => true, 'log.LogLevel' => 'DEBUG', 'log.FileName' => wc_get_log_file_path('wpg_paypal_rest'), 'cache.enabled' => true, 'cache.FileName' => wc_get_log_file_path('wpg_paypal_rest_cache')));
            return $auth;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_card_data($card_data, $is_save = false) {
        try {
            $customer_id = get_current_user_id();
            $billtofirstname = (get_user_meta($customer_id, 'billing_first_name', true)) ? get_user_meta($customer_id, 'billing_first_name', true) : get_user_meta($customer_id, 'shipping_first_name', true);
            $billtolastname = (get_user_meta($customer_id, 'billing_last_name', true)) ? get_user_meta($customer_id, 'billing_last_name', true) : get_user_meta($customer_id, 'shipping_last_name', true);
            $this->card = new CreditCard();
            $this->card->setType($card_data->type)
                    ->setNumber($card_data->number)
                    ->setExpireMonth($card_data->exp_month)
                    ->setExpireYear($card_data->exp_year)
                    ->setCvv2($card_data->cvc)
                    ->setFirstName($billtofirstname)
                    ->setLastName($billtolastname);
            if ($this->is_save_card_data() == true || $is_save == true) {
                $this->card->setMerchantId(get_bloginfo('name') . '_' . $customer_id . '_' . uniqid())
                        ->setExternalCardId($card_data->number . '_' . uniqid())
                        ->setExternalCustomerId($card_data->number . '_' . $customer_id . '_' . uniqid());
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_fundinginstrument($order_id) {
        try {
            $this->fundinginstrument = new FundingInstrument();
            if (!empty($this->creditcardtoken)) {
                $this->fundinginstrument->setCreditCardToken($this->creditcardtoken);
            } elseif (!empty($this->card_id) || $this->is_renewal($order_id)) {
                if ($this->is_renewal($order_id)) {
                    $order = wc_get_order($order_id);
                    $this->card_id = $order->get_meta(_payment_tokens_id);
                }
                $this->creditcardtoken = new CreditCardToken();
                $this->creditcardtoken->setCreditCardId($this->card_id);
                $this->fundinginstrument->setCreditCardToken($this->creditcardtoken);
            } else {
                $this->fundinginstrument->setCreditCard($this->card);
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_payer() {
        try {
            $this->payer = new Payer();
            $this->payer->setPaymentMethod("credit_card")
                    ->setFundingInstruments(array($this->fundinginstrument));
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_item_list($order_id) {
        try {
            $order = wc_get_order($order_id);
            $this->order_cart_data = $this->gateway_calculation->order_calculation($order_id);
            $this->itemlist = new ItemList();
            foreach ($this->order_cart_data['order_items'] as $item) {
                $this->item = new Item();
                $this->item->setName($item['name']);
                $this->item->setCurrency($order->get_currency());
                $this->item->setQuantity($item['qty']);
                $this->item->setPrice($item['amt']);
                $this->itemlist->addItem($this->item);
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_detail_amount($order) {
        try {
            $this->details = new Details();
            if (!empty($this->order_cart_data['shippingamt'])) {
                $this->details->setShipping($this->order_cart_data['shippingamt']);
            }
            if (!empty($this->order_cart_data['taxamt'])) {
                $this->details->setTax($this->order_cart_data['taxamt']);
            }
            if (!empty($this->order_cart_data['itemamt'])) {
                $this->details->setSubtotal($this->order_cart_data['itemamt']);
            }
            $this->amount = new Amount();
            $this->amount->setCurrency($order->get_currency());
            $this->amount->setTotal(wpg_number_format($order->get_total(), $order));
            $this->amount->setDetails($this->details);
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_shipping_address($order) {
        if ($order->needs_shipping_address()) {
            $shipping_first_name = $order->get_shipping_first_name();
            $shipping_last_name = $order->get_shipping_last_name();
            $shipping_address_1 = $order->get_shipping_address_1();
            $shipping_address_2 = $order->get_shipping_address_2();
            $shipping_city = $order->get_shipping_city();
            $shipping_state = $order->get_shipping_state();
            $shipping_postcode = $order->get_shipping_postcode();
            $shipping_country = $order->get_shipping_country();
            $shipping_address_array = array('recipient_name' => $shipping_first_name . $shipping_last_name,
                'line1' => $shipping_address_1,
                'line2' => $shipping_address_2,
                'city' => $shipping_city,
                'state' => $shipping_state,
                'postal_code' => $shipping_postcode,
                'country_code' => $shipping_country
            );
            $this->itemlist->setShippingAddress($shipping_address_array);
        }
    }

    public function wpg_set_transaction($order) {
        try {
            $InvoiceNumber = $this->rest_settings->invoice_prefix . preg_replace("/[^a-zA-Z0-9]/", "", $order->get_order_number());
            $this->transaction = new Transaction();
            $this->transaction->setAmount($this->amount)
                    ->setItemList($this->itemlist)
                    ->setDescription("Payment description")
                    ->setInvoiceNumber($InvoiceNumber)
                    ->setNotifyUrl(apply_filters('wpg_paypal_rest_notify_url', add_query_arg('wpg_ipn_action', 'ipn', WC()->api_request_url('Woo_Paypal_Gateway_IPN_Handler'))))
                    ->setCustom(json_encode(array('order_id' => $order->get_id(), 'order_key' => $order->get_order_key())));
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_payment() {
        try {
            $this->payment = new Payment();
            $this->payment->setIntent("sale")
                    ->setPayer($this->payer)
                    ->setTransactions(array($this->transaction));
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function is_request_using_save_card_data($order_id) {
        try {
            $bool = false;
            try {
                if (!empty($_POST['wc-wpg_paypal_rest-payment-token']) && $_POST['wc-wpg_paypal_rest-payment-token'] != 'new') {
                    $bool = true;
                }
                if ($this->is_renewal($order_id)) {
                    $bool = true;
                }
            } catch (Exception $ex) {
                
            }
            return $bool;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function is_save_card_data() {
        try {
            $bool = false;
            try {
                if (!empty($_POST['wc-wpg_paypal_rest-new-payment-method']) && $_POST['wc-wpg_paypal_rest-new-payment-method'] == true) {
                    $bool = true;
                }
            } catch (Exception $ex) {
                
            }
            return $bool;
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function create_payment_request($card_data, $order_id, $used_payment_token) {
        $this->used_payment_token = $used_payment_token;
        $this->getAuth();
        $order = wc_get_order($order_id);
        try {
            if ($this->is_request_using_save_card_data($order_id) == true) {
                $this->wpg_set_card_token($order_id);
            } else {
                $this->wpg_set_card_data($card_data);
                if ($this->is_save_card_data() == true) {
                    $this->wpg_save_card_data_in_vault();
                }
            }
            if ($this->is_subscription($order_id)) {
                $this->save_payment_token($order, $this->restcreditcardid);
            }
            if ($order->get_total() > 0) {
                $this->wpg_set_fundinginstrument($order_id);
                $this->wpg_set_payer();
                $this->wpg_set_item_list($order_id);
                $this->wpg_set_shipping_address($order);
                $this->wpg_set_detail_amount($order);
                $this->wpg_set_transaction($order);
                $this->wpg_set_payment();
                $this->payment->create($this->getAuth());
                if ($this->payment->state == "approved") {
                    $transactions = $this->payment->getTransactions();
                    $relatedResources = $transactions[0]->getRelatedResources();
                    $sale = $relatedResources[0]->getSale();
                    $saleId = $sale->getId();
                    do_action('before_save_payment_token', $order_id);
                    $order->add_order_note(__('PayPal Credit Card Payments (REST) payment completed', 'woo-paypal-gateway'));
                    if (!empty($this->token)) {
                        $order->add_payment_token($this->token);
                    }
                    wc_reduce_stock_levels($order_id);
                    $order->payment_complete($saleId);
                    return $this->wpg_return_checkout_order_received_url($order);
                }
            } else {
                $order->add_order_note(__('PayPal Credit Card Payments (REST) payment completed', 'woo-paypal-gateway'));
                if (!empty($this->token)) {
                    $order->add_payment_token($this->token);
                }
                wc_reduce_stock_levels($order_id);
                $order->payment_complete($this->card_id);
                return $this->wpg_return_checkout_order_received_url($order);
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_set_card_token($order_id) {
        try {
            if ($this->is_renewal($order_id)) {
                $order = wc_get_order($order_id);
                $this->restcreditcardid = $order->get_meta('_payment_tokens_id');
            } else {
                $token_id = wc_clean($_POST['wc-wpg_paypal_rest-payment-token']);
                $token = WC_Payment_Tokens::get($token_id);
                $this->restcreditcardid = $token->get_token();
            }
            $this->creditcardtoken = new CreditCardToken();
            $this->creditcardtoken->setCreditCardId($this->restcreditcardid);
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_save_card_data_in_vault() {
        try {
            $this->card->create($this->getAuth());
            if ($this->card->getState() == 'ok') {
                $this->card_id = $this->card->getId();
                $this->restcreditcardid = $this->card->getId();
                $this->wpg_save_card_data_in_woo();
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function wpg_save_card_data_in_woo() {
        try {
            if (!empty($this->card_id)) {
                $customer_id = get_current_user_id();
                $creditcard_id = $this->card_id;
                $this->token = new WC_Payment_Token_CC();
                $this->token->set_user_id($customer_id);
                $this->token->set_token($creditcard_id);
                $this->token->set_gateway_id($this->payment_method);
                $this->token->set_card_type($this->card->type);
                $this->token->set_last4(substr($this->card->number, -4));
                $this->token->set_expiry_month($this->card->expire_month);
                $this->token->set_expiry_year($this->card->expire_year);
                $save_result = $this->token->save();
                return $save_result;
            }
        } catch (Exception $ex) {
            Woo_PayPal_Gateway_PayPal_Rest::log($ex->getMessage());
        }
    }

    public function create_refund_request($order_id, $amount, $reason = '') {
        $this->getAuth();
        $order = wc_get_order($order_id);
        $sale = Sale::get($order->get_transaction_id(), $this->getAuth());
        $this->amount = new Amount();
        $this->amount->setCurrency($order->get_currency());
        $this->amount->setTotal(wpg_number_format($amount, $order));
        $refund = new Refund();
        $refund->setAmount($this->amount);
        try {
            $refundedSale = $sale->refund($refund, $this->getAuth());
            if ($refundedSale->state == 'completed') {
                $order->add_order_note('Refund Transaction ID:' . $refundedSale->getId());
                if (isset($reason) && !empty($reason)) {
                    $order->add_order_note('Reason for Refund :' . $reason);
                }
                $max_remaining_refund = wc_format_decimal($order->get_total() - $order->get_total_refunded());
                if (!$max_remaining_refund > 0) {
                    $order->update_status('refunded');
                }
                return true;
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            $error_data = json_decode($ex->getData());
            if (is_object($error_data) && !empty($error_data)) {
                $error_message = ($error_data->message) ? $error_data->message : $error_data->information_link;
                return new WP_Error('paypal_credit_card_rest_refund-error', $error_message);
            } else {
                return new WP_Error('paypal_credit_card_rest_refund-error', $ex->getData());
            }
        } catch (Exception $ex) {
            return new WP_Error('paypal_credit_card_rest_refund-error', $ex->getMessage());
        }
    }

    public function wpg_add_payment_method($card_data) {
        $this->wpg_set_card_data($card_data, $is_save = true);
        $this->wpg_save_card_data_in_vault();
        if ($this->card->getState() == 'ok') {
            $result = 'success';
        } else {
            $result = 'fail';
        }
        return array(
            'result' => $result,
            'redirect' => wc_get_account_endpoint_url('payment-methods')
        );
    }

    public function is_subscription($order_id) {
        return ( function_exists('wcs_order_contains_subscription') && ( wcs_order_contains_subscription($order_id) || wcs_is_subscription($order_id) || wcs_order_contains_renewal($order_id) ) );
    }

    public function is_renewal($order_id) {
        return ( (function_exists('wcs_order_contains_subscription') && ( wcs_order_contains_renewal($order_id) )) || $this->used_payment_token );
    }

    public function save_payment_token($order, $payment_tokens_id) {
        if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order->get_id())) {
            $subscriptions = wcs_get_subscriptions_for_order($order->get_id());
        } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order->get_id())) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order($order->get_id());
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

    public function wpg_return_checkout_order_received_url($order) {
        if ($this->is_renewal($order->get_id())) {
            return;
        }
        WC()->cart->empty_cart();
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_order_received_url(),
        );
    }

}
