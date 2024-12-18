<?php

if (class_exists('WC_Checkout')) {
    
    

    class PPCP_Paypal_Checkout_For_Woocommerce_Checkout extends WC_Checkout {
        
        protected static $_instance = null;

        public static function instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function process_checkout() {
            try {
                wc_maybe_define_constant('WOOCOMMERCE_CHECKOUT', true);
                wc_set_time_limit(0);
                do_action('woocommerce_before_checkout_process');
                if (WC()->cart->is_empty()) {
                    throw new Exception(sprintf(__('Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woo-paypal-gateway'), esc_url(wc_get_page_permalink('shop'))));
                }
                do_action('woocommerce_checkout_process');
                $errors = new WP_Error();
                //$posted_data = $this->get_posted_data();
                if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_Button_Manager')) {
                    include_once WPG_PLUGIN_DIR . '/public/class-ppcp-paypal-checkout-for-woocommerce-button-manager.php';
                }
                $smart_button = PPCP_Paypal_Checkout_For_Woocommerce_Button_Manager::instance();
                $posted_data = $smart_button->ppcp_prepare_order_data();
                $this->update_session($posted_data);
                $this->process_customer($posted_data);
                $order_id = $this->create_order($posted_data);
                $order = wc_get_order($order_id);
                if (is_wp_error($order_id)) {
                    throw new Exception($order_id->get_error_message());
                }
                if (!$order) {
                    throw new Exception(__('Unable to create order.', 'woo-paypal-gateway'));
                }
                do_action('woocommerce_checkout_order_processed', $order_id, $posted_data, $order);
                if (apply_filters('woocommerce_cart_needs_payment', $order->needs_payment(), WC()->cart)) {
                    $this->process_order_payment($order_id, 'wpg_paypal_checkout');
                } else {
                    $this->process_order_without_payment($order_id);
                }
            } catch (Exception $e) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($e->getMessage(), 'error');
                }
            }
            $this->send_ajax_failure_response();
        }

        public function ppcp_create_order() {
            try {
                wc_maybe_define_constant('WOOCOMMERCE_CHECKOUT', true);
                wc_set_time_limit(0);
                do_action('woocommerce_before_checkout_process');
                if (WC()->cart->is_empty()) {
                    throw new Exception(sprintf(__('Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woo-paypal-gateway'), esc_url(wc_get_page_permalink('shop'))));
                }
                do_action('woocommerce_checkout_process');
                $errors = new WP_Error();
                if (!class_exists('PPCP_Paypal_Checkout_For_Woocommerce_Button_Manager')) {
                    include_once WPG_PLUGIN_DIR . '/public/class-ppcp-paypal-checkout-for-woocommerce-button-manager.php';
                }
                $smart_button = PPCP_Paypal_Checkout_For_Woocommerce_Button_Manager::instance();
                $posted_data = $smart_button->ppcp_prepare_order_data();
                $this->update_session($posted_data);
                $this->process_customer($posted_data);
                $order_id = $this->create_order($posted_data);
                $order = wc_get_order($order_id);
                if (is_wp_error($order_id)) {
                    throw new Exception($order_id->get_error_message());
                }
                if (!$order) {
                    throw new Exception(__('Unable to create order.', 'woo-paypal-gateway'));
                }
                return $order_id;
            } catch (Exception $ex) {
                
            }
        }

    }

}


