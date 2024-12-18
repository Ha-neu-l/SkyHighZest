<?php

if (!defined('ABSPATH')) {
    exit;
}

class Woo_PayPal_Gateway_Express_Checkout_Subscriptions_NVP extends Woo_PayPal_Gateway_Express_Checkout_NVP {

    public $rest_api_handler;

    public function __construct() {
        parent::__construct();
        if (class_exists('WC_Subscriptions_Order')) {
            add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'scheduled_subscription_payment'), 10, 2);
            add_action('wcs_resubscribe_order_created', array($this, 'delete_resubscribe_meta'), 10);
            add_filter('woocommerce_my_subscriptions_payment_method', array($this, 'maybe_render_subscription_payment_method'), 10, 2);
            add_filter('woocommerce_subscription_payment_meta', array($this, 'add_subscription_payment_meta'), 10, 2);
            add_filter('woocommerce_subscription_validate_payment_meta', array($this, 'validate_subscription_payment_meta'), 10, 2);
            add_action('woocommerce_subscription_failing_payment_method_updated_' . $this->id, array($this, 'update_failing_payment_method'), 10, 2);
        }
        if (class_exists('WC_Pre_Orders_Order')) {
            add_action('wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array($this, 'process_pre_order_release_payment'));
        }
    }

    public function is_subscription($order_id) {
        return ( function_exists('wcs_order_contains_subscription') && ( wcs_order_contains_subscription($order_id) || wcs_is_subscription($order_id) || wcs_order_contains_renewal($order_id) ) );
    }

    protected function is_pre_order($order_id) {
        return ( class_exists('WC_Pre_Orders_Order') && WC_Pre_Orders_Order::order_contains_pre_order($order_id) );
    }

    public function process_pre_order($order_id) {
        if (WC_Pre_Orders_Order::order_requires_payment_tokenization($order_id)) {
            $order = new WC_Order($order_id);
            try {
                parent::init_api();
                $this->rest_api_handler->wpg_create_billing_agreement($order);
                WC()->cart->empty_cart();
                WC_Pre_Orders_Order::mark_order_as_pre_ordered($order);
                wpg_maybe_clear_session_data();
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            } catch (Exception $e) {
                wc_add_notice($e->getMessage(), 'error');
                return;
            }
        } else {
            return parent::process_payment($order_id);
        }
    }

    public function process_payment($order_id) {
        if ($this->is_subscription($order_id)) {
            return parent::process_payment($order_id);
        } elseif ($this->is_pre_order($order_id)) {
            return $this->process_pre_order($order_id);
        } else {
            return parent::process_payment($order_id);
        }
    }

    public function process_pre_order_release_payment($order) {
        parent::process_subscription_payment($order->get_id());
    }

    public function scheduled_subscription_payment($amount_to_charge, $renewal_order) {
        if ($renewal_order->get_total() > 0) {
            parent::process_subscription_payment($renewal_order->get_id());
        } else {
            parent::process_subscription_payment($renewal_order->get_id());
        }
    }

    public function add_subscription_payment_meta($payment_meta, $subscription) {
        $payment_meta[$this->id] = array(
            'post_meta' => array(
                '_payment_tokens_id' => array(
                    'value' => $subscription->get_meta('_payment_tokens_id'), 
                    'label' => 'Payment Tokens ID',
                )
            )
        );
        return $payment_meta;
    }

    public function validate_subscription_payment_meta($payment_method_id, $payment_meta) {
        if ($this->id === $payment_method_id) {
            if (empty($payment_meta['post_meta']['_payment_tokens_id']['value'])) {
                throw new Exception('A "_payment_tokens_id" value is required.');
            }
        }
    }

    public function delete_resubscribe_meta($resubscribe_order) {
        $resubscribe_order->delete_meta('_payment_tokens_id');
    }

    public function update_failing_payment_method($subscription, $renewal_order) {
        $subscription->update_meta_data('_payment_tokens_id', $renewal_order->payment_tokens_id);
        $subscription->save_meta_data();
    }

    public function free_signup_with_token_payment_tokenization($order_id) {
        if (!empty($_POST['wc-wpg_paypal_express-payment-token']) && $_POST['wc-wpg_paypal_express-payment-token'] != 'new') {
            $order = new WC_Order($order_id);
            if ($order->get_total() == 0) {
                return true;
            }
        }
        return false;
    }

    public function maybe_render_subscription_payment_method($payment_method_to_display, $subscription) {
        if ($this->id !== $subscription->get_payment_method() || !$subscription->get_customer_id()) {
            return $payment_method_to_display;
        }
        if (!empty($subscription->get_id())) {
            $payment_tokens_id = $subscription->get_meta('_payment_tokens_id');
            $payment_method_to_display = sprintf(__('Via %1$s %2$s', 'woocommerce-gateway-stripe'), 'PayPal Billing Agreement ', $payment_tokens_id);
        }
        return $payment_method_to_display;
    }

}
