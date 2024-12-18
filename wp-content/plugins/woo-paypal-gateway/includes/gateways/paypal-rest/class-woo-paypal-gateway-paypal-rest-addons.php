<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Woo_PayPal_Gateway_PayPal_Rest_Addons class.
 *
 * @extends Woo_PayPal_Gateway_PayPal_Rest
 */
class Woo_PayPal_Gateway_PayPal_Rest_Addons extends Woo_PayPal_Gateway_PayPal_Rest {

    public function __construct() {
        parent::__construct();
        if (class_exists('WC_Subscriptions_Order')) {
            add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'scheduled_subscription_payment'), 10, 2);
            add_filter('woocommerce_subscription_payment_meta', array($this, 'add_subscription_payment_meta'), 10, 2);
            add_filter('woocommerce_subscription_validate_payment_meta', array($this, 'validate_subscription_payment_meta'), 10, 2);
            add_action('wcs_resubscribe_order_created', array($this, 'delete_resubscribe_meta'), 10);
            add_action('woocommerce_subscription_failing_payment_method_updated_' . $this->id, array($this, 'update_failing_payment_method'), 10, 2);
        }
        if (class_exists('WC_Pre_Orders_Order')) {
            add_action('wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array($this, 'process_pre_order_release_payment'));
        }
    }

    public function is_subscription($order_id) {
        return ( function_exists('wcs_order_contains_subscription') && ( wcs_order_contains_subscription($order_id) || wcs_is_subscription($order_id) || wcs_order_contains_renewal($order_id) ) );
    }

    public function is_pre_order($order_id) {
        return ( class_exists('WC_Pre_Orders_Order') && WC_Pre_Orders_Order::order_contains_pre_order($order_id) );
    }

    public function process_payment($order_id, $used_payment_token = false) {
        if ($this->is_pre_order($order_id)) {
            return parent::process_pre_order($order_id, $used_payment_token);
        } else {
            return parent::process_payment($order_id, $used_payment_token);
        }
    }

    public function process_pre_order_release_payment($order) {
        return parent::process_payment($order->get_id(), $used_payment_token = true);
    }

    public function scheduled_subscription_payment($amount_to_charge, $renewal_order) {
        parent::process_payment($renewal_order->get_id(), $used_payment_token = true);
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
            if (!empty($payment_meta['post_meta']['_payment_tokens_id']['value']) && empty($payment_meta['post_meta']['_payment_tokens_id']['value'])) {
                throw new Exception('A "_payment_tokens_id" value is required.');
            }
        }
    }

    public function save_payment_token($order, $payment_tokens_id) {
        parent::save_payment_token($order, $payment_tokens_id);
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
        }
    }

    public function delete_resubscribe_meta($resubscribe_order) {
        $resubscribe_order->delete_meta('_payment_tokens_id');
    }

    public function update_failing_payment_method($subscription, $renewal_order) {
        $subscription->update_meta_data('_payment_tokens_id', $renewal_order->payment_tokens_id);
        $subscription->save_meta_data();
    }

}
