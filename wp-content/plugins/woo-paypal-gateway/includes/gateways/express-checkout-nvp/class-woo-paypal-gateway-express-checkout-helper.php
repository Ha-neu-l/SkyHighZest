<?php
if (!defined('ABSPATH')) {
    exit;
}

class Woo_Paypal_Gateway_Express_Checkout_Helper_NVP {

    public $version;
    public $gateway_obj;
    public $gateway_settings;
    public $show_on_checkout_page;
    public $show_on_product_page;
    public $checkout_skip_text;
    public $button_position;
    public $show_on_cart;
    public $credit_enabled;
    public $enable_in_context_checkout_flow;

    public function __construct($version) {
        try {
            $this->version = $version;
            $this->gateway_settings = get_option('woocommerce_wpg_paypal_express_settings');
            $this->show_on_checkout_page = !empty($this->gateway_settings['show_on_checkout_page']) ? $this->gateway_settings['show_on_checkout_page'] : 'no';
            $this->show_on_product_page = !empty($this->gateway_settings['show_on_product_page']) ? $this->gateway_settings['show_on_product_page'] : 'no';
            $this->checkout_skip_text = !empty($this->gateway_settings['checkout_skip_text']) ? $this->gateway_settings['checkout_skip_text'] : '';
            $this->button_position = !empty($this->gateway_settings['button_position']) ? $this->gateway_settings['button_position'] : 'bottom';
            $this->show_on_cart = !empty($this->gateway_settings['show_on_cart']) ? $this->gateway_settings['show_on_cart'] : 'yes';
            $this->credit_enabled = !empty($this->gateway_settings['credit_enabled']) ? $this->gateway_settings['credit_enabled'] : 'yes';
            $this->enable_in_context_checkout_flow = !empty($this->gateway_settings['enable_in_context_checkout_flow']) ? $this->gateway_settings['enable_in_context_checkout_flow'] : 'yes';
            add_action('woocommerce_after_add_to_cart_button', array($this, 'wpg_checkout_button_on_product_details_page'), 10);
            add_action('woocommerce_proceed_to_checkout', array($this, 'wpg_checkout_on_bottom_cart_page'), 999);
            add_action('woocommerce_before_cart_table', array($this, 'wpg_checkout_on_top_cart_page'));
            add_action('woocommerce_before_checkout_form', array($this, 'wpg_display_button_on_checkout_page'), 999);
            add_action('wp_enqueue_scripts', array($this, 'ec_enqueue_scripts_product_page'), 0);
            add_action('woocommerce_cart_emptied', array($this, 'wpg_maybe_clear_session_data'));
            add_action('woocommerce_available_payment_gateways', array($this, 'wpg_checkout_page_disable_gateways'));
            add_action('woocommerce_checkout_billing', array($this, 'wpg_express_checkout_auto_fillup_shipping_address'));
            add_action('wp_head', array($this, 'wpg_add_header_meta'), 0);
            add_filter('the_title', array($this, 'wpg_woocommerce_page_title'), 99, 1);
            add_filter('body_class', array($this, 'wpg_add_body_class'));
            add_action('wc_ajax_wpg_ajax_generate_cart', array($this, 'wpg_product_add_to_cart'));
            add_action('template_redirect', array($this, 'wpg_redirect_to_checkout_page'));
        } catch (Exception $ex) {

        }
    }

    public function wpg_express_checkout_auto_fillup_shipping_address() {
        try {
            $post_data = wpg_get_session('post_data');
            if (empty($post_data)) {
                $post_data = wpg_get_session('wpg_express_checkout_shipping_address');
            }
            if (!empty($post_data)) {
                foreach ($post_data as $key => $value) {
                    $_POST['billing_' . $key] = wc_clean($value);
                    $_POST['shipping_' . $key] = wc_clean($value);
                }
            }
        } catch (Exception $ex) {

        }
    }
    
    

    public function wpg_add_header_meta() {
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    }

    public function wpg_display_button_on_checkout_page() {
        if ($this->show_on_checkout_page == 'yes' && $this->wpg_is_express_checkout_enable() && is_wpg_express_checkout_ready_to_capture() === false) {
            echo '<div class="woocommerce-info">';
            echo '<span>' . esc_attr($this->checkout_skip_text) . '</span>';
            $this->buy_now_button();
            echo '</div>';
        }
    }

    public function wpg_checkout_button_on_product_details_page() {
        global $product;
        if (is_object($product)) {
            if ($product->is_type('simple') || $product->is_type('variable')) {
                if ($this->show_on_product_page == 'yes' && $this->wpg_is_express_checkout_enable()) {
                    $this->buy_now_button();
                    if ($product->is_type('simple')) {
                        ?>
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
                        <?php
                    }
                }
            }
        }
    }

    public function wpg_checkout_on_bottom_cart_page() {
        if ($this->show_on_cart == 'yes' && $this->wpg_is_express_checkout_enable() && ( $this->button_position == 'both' || $this->button_position == 'bottom')) {
            $this->buy_now_button();
        }
    }

    public function wpg_checkout_on_top_cart_page() {
        if ($this->show_on_cart == 'yes' && $this->wpg_is_express_checkout_enable() && ( $this->button_position == 'both' || $this->button_position == 'top')) {
            $this->buy_now_button();
        }
    }

    public function buy_now_button() {
        try {
            if (is_wpg_express_checkout_ready_to_capture() === false) {
                wp_enqueue_script('wpg-in-context-checkout-js');
                wp_enqueue_script('wpg-in-context-checkout-js-frontend');
                wp_enqueue_style("woo-paypal-gateway-public");
                echo '<div class="single_add_to_cart_button wpg_express_checkout_paypal_button button alt "></div>';
                if (is_wpg_credit_supported() == true && $this->credit_enabled == 'yes') {
                    echo '<div class="single_add_to_cart_button wpg_express_checkout_paypal_cc_button button alt "></div>';
                }
            }
        } catch (Exception $ex) {

        }
    }

    public function ec_enqueue_scripts_product_page() {
        global $post;
        try {
            if (is_wpg_express_checkout_ready_to_capture() == false) {
                $ENV_value = wpg_get_option('wpg_paypal_express', 'sandbox', 'yes');
                $ENV = ($ENV_value != 'yes') ? 'production' : 'sandbox';
                wp_register_script('wpg-in-context-checkout-js', 'https://www.paypalobjects.com/api/checkout.min.js', array(), null, true);
                wp_register_script('wpg-in-context-checkout-js-frontend', WPG_PLUGIN_ASSET_URL . '/public/js/woo-paypal-gateway-in-context-checkout.js', array('jquery'), $this->version, true);
                wp_localize_script('wpg-in-context-checkout-js-frontend', 'wpg_in_content_param', array(
                    'CREATE_PAYMENT_URL' => $this->wpg_get_create_payment_url(),
                    'CC_CREATE_PAYMENT_URL' => esc_url(add_query_arg(array('wpg_express_checkout_action' => 'wpg_set_express_checkout', 'is_wpg_cc' => 'yes'), WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP'))),
                    'EXECUTE_PAYMENT_URL' => esc_url(add_query_arg('wpg_express_checkout_action', 'checkout_payment_url', WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP'))),
                    'LOCALE' => self::get_button_locale_code(),
                    'GENERATE_NONCE' => wp_create_nonce('_wpg_nonce_'),
                    'IS_PRODUCT' => is_product() ? "yes" : "no",
                    'POST_ID' => isset($post->ID) ? $post->ID : '',
                    'CANCEL_URL' => esc_url(add_query_arg('wpg_express_checkout_action', 'cancel_url', WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP'))),
                    'SIZE' => wpg_get_option('wpg_paypal_express', 'button_size', 'small'),
                    'SHAPE' => wpg_get_option('wpg_paypal_express', 'button_shape', 'pill'),
                    'COLOR' => wpg_get_option('wpg_paypal_express', 'button_color', 'gold'),
                    'ENV' => $ENV,
                    'MerchantID' => $this->wpg_get_merchant_id(),
                    'add_to_cart_ajaxurl' => WC_AJAX::get_endpoint('wpg_ajax_generate_cart'),
                    'enable_in_context_checkout_flow' => $this->enable_in_context_checkout_flow
                ));
            }
        } catch (Exception $ex) {

        }
    }

    public static function get_button_locale_code() {
        try {
            $_supportedLocale = array(
                'en_US', 'fr_XC', 'es_XC', 'zh_XC', 'en_AU', 'de_DE', 'nl_NL',
                'fr_FR', 'pt_BR', 'fr_CA', 'zh_CN', 'ru_RU', 'en_GB', 'zh_HK',
                'he_IL', 'it_IT', 'ja_JP', 'pl_PL', 'pt_PT', 'es_ES', 'sv_SE', 'zh_TW', 'tr_TR'
            );
            $wpml_locale = self::wpg_ec_get_wpml_locale();
            if ($wpml_locale) {
                if (in_array($wpml_locale, $_supportedLocale)) {
                    return $wpml_locale;
                }
            }
            $locale = get_locale();
            if (!in_array($locale, $_supportedLocale)) {
                $locale = 'en_US';
            }
            return $locale;
        } catch (Exception $ex) {

        }
    }

    public static function wpg_ec_get_wpml_locale() {
        try {
            $locale = false;
            if (defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id')) {
                global $sitepress;
                if (isset($sitepress)) {
                    $locale = $sitepress->get_current_language();
                } else if (function_exists('pll_current_language')) {
                    $locale = pll_current_language('locale');
                } else if (function_exists('pll_default_language')) {
                    $locale = pll_default_language('locale');
                }
            }
            return $locale;
        } catch (Exception $ex) {

        }
    }

    public function wpg_checkout_page_disable_gateways($gateways) {
        try {
            if (is_wpg_express_checkout_ready_to_capture()) {
                foreach ($gateways as $id => $gateway) {
                    if ($id !== 'wpg_paypal_express') {
                        unset($gateways[$id]);
                    }
                }
            }
            return $gateways;
        } catch (Exception $ex) {

        }
    }

    public function wpg_maybe_clear_session_data() {
        try {
            wpg_maybe_clear_session_data();
        } catch (Exception $ex) {

        }
    }

    public function wpg_woocommerce_page_title($title) {
        try {
            if (!is_admin() && is_main_query() && in_the_loop() && is_page() && is_checkout() && is_wpg_express_checkout_ready_to_capture()) {
                $title = __('Confirm your PayPal order', 'woo-paypal-gateway');
                remove_filter('the_title', array($this, 'wpg_woocommerce_page_title'));
            }
            return $title;
        } catch (Exception $ex) {

        }
    }

    public function wpg_is_express_checkout_enable() {
        try {
            if (!class_exists('Woo_PayPal_Gateway_Express_Checkout_NVP')) {
                include_once( WPG_PLUGIN_DIR . '/includes/gateways/express-checkout-nvp/class-woo-paypal-gateway-express-checkout.php' );
                $this->gateway_obj = new Woo_PayPal_Gateway_Express_Checkout_NVP();
            } else {
                $this->gateway_obj = new Woo_PayPal_Gateway_Express_Checkout_NVP();
            }
            if ($this->gateway_obj->is_available()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {

        }
    }

    public function wpg_get_merchant_id() {
        try {
            if (!class_exists('Woo_Paypal_Gateway_Express_Checkout_NVP')) {
                include_once 'Woo_Paypal_Gateway_Express_Checkout_NVP';
            }
            $wpg_express_checkout_obj = new Woo_PayPal_Gateway_Express_Checkout_NVP();
            return $wpg_express_checkout_obj->username;
        } catch (Exception $ex) {

        }
    }

    public function wpg_add_body_class($classes) {
        try {
            if ( ! class_exists( 'WooCommerce' ) || WC()->session == null ) {
                return $classes;
            }
            if (is_wpg_express_checkout_ready_to_capture()) {
                $classes[] = 'wpg-express-checkout';
            }
            return $classes;
        } catch (Exception $ex) {

        }
    }

    public function wpg_product_add_to_cart() {
        try {
            if (!wp_verify_nonce($_POST['nonce'], '_wpg_nonce_')) {
                wp_die(__('Cheatin&#8217; huh?', 'woo-paypal-gateway'));
            }
            if (!isset($_POST['is_add_to_cart']) || $_POST['is_add_to_cart'] == 'no') {
                return false;
            }
            if (!isset($_POST['product_id']) && empty($_POST['product_id'])) {
                return false;
            }
            if (!defined('WOOCOMMERCE_CART')) {
                define('WOOCOMMERCE_CART', true);
            }
            if (!defined('WOOCOMMERCE_CHECKOUT')) {
                define('WOOCOMMERCE_CHECKOUT', true);
            }
            WC()->shipping->reset_shipping();
            $product = wc_get_product($_POST['product_id']);
            $qty = !isset($_POST['qty']) ? 1 : absint($_POST['qty']);
            if ($product->is_type('variable')) {
                $attributes = array_map('wc_clean', $_POST['attributes']);
                $data_store = WC_Data_Store::load('product');
                $variation_id = $data_store->find_matching_product_variation($product, $attributes);
                WC()->cart->add_to_cart($product->get_id(), $qty, $variation_id, $attributes);
            } elseif ($product->is_type('simple')) {
                WC()->cart->add_to_cart($product->get_id(), $qty);
            }
            WC()->cart->calculate_totals();
        } catch (Exception $ex) {

        }
    }

    public function wpg_redirect_to_checkout_page() {
        if (is_wpg_express_checkout_ready_to_capture() && is_cart()) {
            wp_redirect(wc_get_page_permalink('checkout'));
            exit;
        }
    }

    public function wpg_get_create_payment_url() {
        $create_payment_url = add_query_arg('wpg_express_checkout_action', 'wpg_set_express_checkout', WC()->api_request_url('Woo_PayPal_Gateway_Express_Checkout_NVP'));
        if (is_product()) {
            $create_payment_url = add_query_arg('start_from', 'product_page', $create_payment_url);
        } elseif (is_cart()) {
            $create_payment_url = add_query_arg('start_from', 'cart_page', $create_payment_url);
        } elseif (is_checkout()) {
            $create_payment_url = add_query_arg('start_from', 'checkout_page', $create_payment_url);
        }
        return esc_url($create_payment_url);
    }

}
