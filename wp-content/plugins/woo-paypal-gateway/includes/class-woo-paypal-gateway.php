<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Paypal_Gateway
 * @subpackage Woo_Paypal_Gateway/includes
 * @author     easypayment <wpeasypayment@gmail.com>
 */
class Woo_Paypal_Gateway {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woo_Paypal_Gateway_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WPG_PLUGIN_VERSION')) {
            $this->version = WPG_PLUGIN_VERSION;
        } else {
            $this->version = '9.0.16';
        }
        $this->plugin_name = 'woo-paypal-gateway';
        if (!defined('WPG_PLUGIN_NAME')) {
            define('WPG_PLUGIN_NAME', $this->plugin_name);
        }
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        add_action('init', array($this, 'add_endpoint'), 0);
        add_action('init', array($this, 'handle_api_requests'), 999999);
        add_action('wpg_paypal_payment_api_ipn', array($this, 'wpg_paypal_payment_api_ipn'));
        add_action('http_api_curl', array($this, 'wpg_http_api_curl_ec_add_curl_parameter'), 10, 3);
        $prefix = is_network_admin() ? 'network_admin_' : '';
        add_filter("{$prefix}plugin_action_links_" . WPG_PLUGIN_BASENAME, array($this, 'wpg_plugin_action_links'), 10, 4);
        add_filter('plugin_row_meta', array($this, 'add_wpg_plugin_meta_links'), 10, 2);
        add_action('woocommerce_cart_emptied', array($this, 'wpg_clear_session'), 1);
        add_action('woocommerce_cart_item_removed', array($this, 'wpg_clear_session'), 1);
        add_action('woocommerce_update_cart_action_cart_updated', array($this, 'wpg_clear_session'), 1);
        add_action('admin_notices', array($this, 'cpp_admin_notice'));
        add_action('wp_ajax_ppcp_admin_notice_action', array($this, 'ppcp_admin_notice_action'), 10);
        add_action('wp_ajax_ppcp_dismiss_notice', array($this, 'ppcp_dismiss_notice'), 10);
        add_action('admin_footer', array($this, 'wpg_add_deactivation_feedback_form'));
        add_action('admin_enqueue_scripts', array($this, 'wpg_add_deactivation_feedback_form_scripts'));
        add_action('wp_ajax_wpg_send_deactivation', array($this, 'wpg_handle_plugin_deactivation_request'));
        add_action('admin_notices', array($this, 'leaverev'));
        add_action('wp_ajax_wpg_handle_review_action', array($this, 'handle_review_action'));
        add_action('admin_enqueue_scripts', array($this, 'wpg_enqueue_scripts'));
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woo_Paypal_Gateway_Loader. Orchestrates the hooks of the plugin.
     * - Woo_Paypal_Gateway_i18n. Defines internationalization functionality.
     * - Woo_Paypal_Gateway_Admin. Defines all hooks for the admin area.
     * - Woo_Paypal_Gateway_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-gateway-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-gateway-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-paypal-gateway-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-woo-paypal-gateway-public.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-gateway-functions.php';

        if (!class_exists('Woo_Paypal_Gateway_Calculations')) {
            require_once plugin_dir_path(dirname(__FILE__)) . '/includes/class-woo-paypal-gateway-calculations.php';
        }

        $this->loader = new Woo_Paypal_Gateway_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woo_Paypal_Gateway_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Woo_Paypal_Gateway_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Woo_Paypal_Gateway_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 0);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('plugins_loaded', $plugin_admin, 'init_wpg_paypal_payment');
        $this->loader->add_filter('woocommerce_payment_gateways', $plugin_admin, 'wpg_pal_payment_for_woo_add_payment_method_class', 9999, 1);
    }

    private function define_public_hooks() {

        $plugin_public = new Woo_Paypal_Gateway_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 0);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woo_Paypal_Gateway_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    public function handle_api_requests() {
        global $wp;
        if (isset($_GET['wpg_ipn_action']) && $_GET['wpg_ipn_action'] == 'ipn') {
            $wp->query_vars['Woo_Paypal_Gateway'] = $_GET['wpg_ipn_action'];
        }
        if (!empty($wp->query_vars['Woo_Paypal_Gateway'])) {
            ob_start();
            $api = strtolower(esc_attr($wp->query_vars['Woo_Paypal_Gateway']));
            do_action('wpg_paypal_payment_api_' . $api);
            ob_end_clean();
            die('1');
        }
    }

    public function add_endpoint() {
        add_rewrite_endpoint('Woo_Paypal_Gateway', EP_ALL);
    }

    public function wpg_paypal_payment_api_ipn() {
        require_once( WPG_PLUGIN_DIR . '/includes/paypal-ipn/class-woo-paypal-gateway-ipn-handler.php' );
        $Woo_Paypal_Gateway_IPN_Handler_Object = new Woo_Paypal_Gateway_IPN_Handler();
        $Woo_Paypal_Gateway_IPN_Handler_Object->check_response();
    }

    public function wpg_http_api_curl_ec_add_curl_parameter($handle, $r, $url) {
        try {
            if ((strstr($url, 'https://') && strstr($url, '.paypal.com'))) {
                curl_setopt($handle, CURLOPT_VERBOSE, 1);
                curl_setopt($handle, CURLOPT_SSLVERSION, 6);
            }
        } catch (Exception $ex) {
            
        }
    }

    public function wpg_plugin_action_links($actions, $plugin_file, $plugin_data, $context) {
        $custom_actions = array(
            'configure' => sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=wc-settings&tab=checkout&section=wpg_paypal_checkout'), __('Settings', 'woo-paypal-gateway')),
        );
        if (array_key_exists('deactivate', $actions)) {
            $actions['deactivate'] = str_replace('<a', '<a class="woo-paypal-gateway-deactivate-link"', $actions['deactivate']);
        }
        return array_merge($custom_actions, $actions);
    }

    public function add_wpg_plugin_meta_links($meta, $file) {
        if (basename($file) === basename(WPG_PLUGIN_FILE)) {
            $meta[] = '<a href="https://wordpress.org/support/plugin/woo-paypal-gateway/">' . __('Community support', 'woo-paypal-gateway') . '</a>';
            $meta[] = '<a href="https://wordpress.org/support/plugin/woo-paypal-gateway/reviews/#new-post" target="_blank" rel="noopener noreferrer">' . __('Rate our plugin', 'woo-paypal-gateway') . '</a>';
        }
        return $meta;
    }

    public function wpg_clear_session() {
        wpg_clear_session_data();
    }

    public function cpp_admin_notice() {
        global $current_user;
        $user_id = $current_user->ID;
        if (false === ( $ppcp_wp_review_request = get_transient('ppcp_wp_review_request') ) && !get_user_meta($user_id, 'ppcp_wp_review_request')) :
            ?>
            <div class="ppcp_wp_review_request notice notice-info is-dismissible">
                <h3 style="margin: 10px 0;"><?php _e('Payment Gateway for PayPal on WooCommerce', 'woo-paypal-gateway'); ?></h3>
                <p><?php esc_html_e('Hey, We would like to thank you for using our plugin. We would appreciate it if you could take a moment to drop a quick review that will inspire us to keep going.', 'woo-paypal-gateway'); ?></p>
                <p>
                    <a class="button button-secondary" style="color:#333; border-color:#ccc; background:#efefef;" data-type="later">Remind me later</a>
                    <a class="button button-primary" data-type="add_review">Review now</a>
                </p>
            </div>
        <?php endif; ?>
        <script type="text/javascript">
            (function ($) {
                "use strict";
                var data_obj = {
                    action: 'ppcp_admin_notice_action',
                    ppcp_admin_notice_action_type: ''
                };
                $(document).on('click', '.ppcp_wp_review_request a.button', function (e) {
                    e.preventDefault();
                    var elm = $(this);
                    var btn_type = elm.attr('data-type');
                    if (btn_type == 'add_review') {
                        window.open('https://wordpress.org/support/plugin/woo-paypal-gateway/reviews/');
                    }
                    elm.parents('.ppcp_wp_review_request').hide();

                    data_obj['ppcp_admin_notice_action_type'] = btn_type;
                    $.ajax({
                        url: ajaxurl,
                        data: data_obj,
                        type: 'POST'
                    });

                }).on('click', '.ppcp_wp_review_request .notice-dismiss', function (e) {
                    e.preventDefault();
                    var elm = $(this);
                    elm.parents('.ppcp_wp_review_request').hide();
                    data_obj['ppcp_admin_notice_action_type'] = 'review_closed';
                    $.ajax({
                        url: ajaxurl,
                        data: data_obj,
                        type: 'POST'
                    });

                }).on('click', '.ppcp_wp_billing_phone_notice .notice-dismiss', function (e) {
                    e.preventDefault();
                    var elm = $(this);
                    elm.parents('.ppcp_wp_billing_phone_notice').hide();
                    data_obj['ppcp_admin_notice_action_type'] = 'ppcp_wp_billing_phone_notice';
                    $.ajax({
                        url: ajaxurl,
                        data: data_obj,
                        type: 'POST'
                    });

                });
            })(jQuery);
        </script>
        <?php
    }

    public function ppcp_admin_notice_action() {
        if (isset($_POST['ppcp_admin_notice_action_type']) && 'later' === $_POST['ppcp_admin_notice_action_type']) {
            set_transient('ppcp_wp_review_request', 'yes', MONTH_IN_SECONDS);
        } elseif (isset($_POST['ppcp_admin_notice_action_type']) && 'add_review' === $_POST['ppcp_admin_notice_action_type']) {
            global $current_user;
            $user_id = $current_user->ID;
            add_user_meta($user_id, 'ppcp_wp_review_request', 'true', true);
        } elseif (isset($_POST['ppcp_admin_notice_action_type']) && 'review_closed' === $_POST['ppcp_admin_notice_action_type']) {
            set_transient('ppcp_wp_review_request', 'yes', MONTH_IN_SECONDS);
        } elseif (isset($_POST['ppcp_admin_notice_action_type']) && 'ppcp_wp_billing_phone_notice' === $_POST['ppcp_admin_notice_action_type']) {
            global $current_user;
            $user_id = $current_user->ID;
            add_user_meta($user_id, 'ppcp_wp_billing_phone_notice', 'true', true);
        }
    }

    public function ppcp_dismiss_notice() {
        global $current_user;
        $user_id = $current_user->ID;
        if (!empty($_POST['action']) && $_POST['action'] == 'ppcp_dismiss_notice') {
            if (!empty($_POST['action']) && $_POST['action'] === 'ppcp_dismiss_notice') {
                add_user_meta($user_id, $_POST['data'], 'true', true);
                wp_send_json_success();
            }
        }
    }

    public function wpg_add_deactivation_feedback_form() {
        global $pagenow;
        if ('plugins.php' != $pagenow) {
            return;
        }
        include_once(WPG_PLUGIN_DIR . '/admin/feedback/deactivation-feedback-form.php');
    }

    public function wpg_add_deactivation_feedback_form_scripts() {
        global $pagenow;
        if ('plugins.php' != $pagenow) {
            return;
        }
        wp_enqueue_style('deactivation-feedback-modal', WPG_PLUGIN_ASSET_URL . 'admin/feedback/css/deactivation-feedback-modal.css', null, WPG_PLUGIN_VERSION);
        wp_enqueue_script('deactivation-feedback-modal', WPG_PLUGIN_ASSET_URL . 'admin/feedback/js/deactivation-feedback-modal.js', null, WPG_PLUGIN_VERSION, true);
        wp_localize_script('deactivation-feedback-modal', 'wpg_feedback_form_ajax_data', array('nonce' => wp_create_nonce('wpg-ajax')));
    }

    public function wpg_handle_plugin_deactivation_request() {
        $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
        $reason_details = isset($_POST['reason_details']) ? sanitize_text_field($_POST['reason_details']) : '';
        $url = 'https://api.airtable.com/v0/appxxiU87VQWG6rOO/Sheet1';
        $api_key = 'patgeqj8DJfPjqZbS.9223810d432db4efccf27354c08513a7725e4a08d11a85fba75de07a539c8aeb';
        $data = array(
            'reason' => $reason . ' : ' . $reason_details,
            'plugin' => 'woo-paypal-gateway',
            'php_version' => phpversion(),
            'wp_version' => get_bloginfo('version'),
            'wc_version' => (!defined('WC_VERSION') ) ? '' : WC_VERSION,
            'locale' => get_locale(),
            'theme' => wp_get_theme()->get('Name'),
            'theme_version' => wp_get_theme()->get('Version'),
            'multisite' => is_multisite() ? 'Yes' : 'No',
            'plugin_version' => WPG_PLUGIN_VERSION
        );
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'records' => array(
                    array(
                        'fields' => array(
                            'reason' => json_encode($data),
                            'date' => date('M d, Y h:i:s A')
                        ),
                    ),
                ),
            )),
            'method' => 'POST'
        );
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => 'Error communicating with Airtable',
                'error' => $response->get_error_message()
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'Deactivation feedback submitted successfully',
                'response' => json_decode(wp_remote_retrieve_body($response), true)
            ));
        }
    }

    public function leaverev() {
        $activation_time = get_option('wpg_activation_time');
        if ($activation_time == '') {
            $activation_time = time();
            update_option('wpg_activation_time', $activation_time);
        }
        $rev_notice_hide = get_option('wpg_rev_notice_hide');
        $next_show_time = get_option('wpg_next_show_time', time());
        $days_since_activation = (time() - $activation_time) / 86400;
        if ($rev_notice_hide != 'never' && $days_since_activation >= 10 && time() >= $next_show_time) {
            $class = 'notice notice-info';
            $notice = '<div class="wpg-review-notice">' .
                    '<p style="font-weight:normal;font-size:15px;">' .
                    '<strong>Hi there,</strong><br>' .
                    'We’re glad to see you’ve been using <b>PayPal Plugin</b>!<br>' .
                    'Could you share your experience by leaving a review on WordPress? Your feedback means a lot to us.<br>' .
                    '<br>Thank you!<br>Team EasyPayment' .
                    '</p>' .
                    '<p style="margin-bottom:10px;">' .
                    '<a href="https://wordpress.org/support/plugin/woo-paypal-gateway/reviews/#new-post" style="text-decoration:none;" target="_blank">' .
                    '<button class="button button-primary wpg-action-button" data-action="reviewed" style="margin-right:5px;" type="button">OK, you deserve it</button>' .
                    '</a>' .
                    '<button class="button button-secondary wpg-action-button" data-action="later" style="margin-right:5px;">Not now, maybe later</button>' .
                    '<button class="button button-secondary wpg-action-button" data-action="never" style="float:right;">Don’t remind me again</button>' .
                    '</p>' .
                    '</div>';

            printf('<div class="%1$s" style="position:fixed;bottom:50px;right:20px;padding-right:30px;z-index:2;margin-left:20px">%2$s</div>', esc_attr($class), $notice);
        }
    }

    public function handle_review_action() {
        check_ajax_referer('wpg_review_nonce', 'nonce');

        $action = isset($_POST['review_action']) ? sanitize_text_field($_POST['review_action']) : '';

        if ($action === 'later') {
            // Hide the notice for 1 week
            $next_show_time = time() + (86400 * 7); // 7 days from now
            update_option('wpg_next_show_time', $next_show_time);
            update_option('wpg_rev_notice_hide', 'later');
        } elseif ($action === 'never' || $action === 'reviewed') {
            // Hide the notice permanently
            update_option('wpg_rev_notice_hide', 'never');
        } else {
            wp_send_json_error('Invalid action');
        }

        wp_send_json_success();
    }

    public function wpg_enqueue_scripts() {
        wp_enqueue_script(
                'wpg-review-ajax',
                WPG_PLUGIN_ASSET_URL . '/admin/js/review-ajax.js',
                array('jquery'),
                '1.0',
                true
        );
        wp_localize_script('wpg-review-ajax', 'wpgAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpg_review_nonce')
        ));
    }
}
