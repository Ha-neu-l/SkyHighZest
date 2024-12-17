<?php
/**
 * Plugin Name: Kliken: Ads + Pixel for Meta
 * Plugin URI: https://woo.kliken.com/
 * Description: Kliken's all-in-one marketing platform helps business owners reach high-intent customers, surpass your competition and realize significant growth in sales, while decreasing conversion costs.
 * Author: Kliken
 * Author URI: https://kliken.com/
 * Developer: Kliken
 * Developer URI: https://kliken.com/
 * Text Domain: kliken-ads-pixel-for-meta
 * Domain path: /languages
 *
 * Version: 1.0.2
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Tested up to: 6.7
 * Requires Plugins: woocommerce
 * WC requires at least: 6.0
 * WC tested up to: 9.3
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Kliken: Ads + Pixel for Meta
 */

defined( 'ABSPATH' ) || exit;

define( 'KK_FB_WC_PLUGIN_VERSION', '1.0.0' );
define( 'KK_FB_WC_PLUGIN_FILE', __FILE__ );
define( 'KK_FB_WC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KK_FB_WC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KK_FB_WC_PLUGIN_BASE_PATH', plugin_basename( __FILE__ ) );
define( 'KK_FB_WC_PLUGIN_REL_PATH', dirname( KK_FB_WC_PLUGIN_BASE_PATH ) );
define( 'KK_FB_WC_AFFILIATE_ID', '82E7B644-DB42-40E9-9EDF-6FD10A4BAFB3' );
define( 'KK_FB_WC_WOOKLIKEN_BASE_URL', 'https://woo.kliken.com/' );
define( 'KK_FB_WC_AUTH_CALLBACK_URL', 'https://auth.kliken.com/WebHooks/WooCommerceMetaAuth/' );
define( 'KK_FB_WC_WEBEVENT_API_URL', 'https://weapi.kliken.com/' );
define( 'KK_FB_WC_OTHER_PLUGIN_REL_PATH', 'kliken-marketing-for-google/kliken-marketing-for-google.php' );

define( 'KK_FB_WC_REQUIRES_AT_LEAST_VERSION', '6.0' ); // Must be the same version as the "WC requires at least" at the top.
define( 'KK_FB_WC_SLUG', 'kliken-ads-pixel-for-meta-plugin' );
define( 'KK_FB_WC_MAIN_PAGE_SLUG', 'kliken-ads-pixel-for-meta' );
define( 'KK_FB_WC_SETTINGS_OPTION_KEY', 'kk_fb_wc_settings' );
define( 'KK_FB_WC_SETTINGS_PIXEL_KEY', 'kk_fb_wc_pixel_settings' );

define( 'KK_FB_WC_ACTION_SAVE_SETTINGS', 'kk_fb_wc_savesettings' );
define( 'KK_FB_WC_ACTION_DISMISS_NOTICE', 'kk_fb_wc_dismissnotice' );
define( 'KK_FB_WC_ACTION_FETCH_CART_ITEMS', 'kk_fb_wc_fetchcartitems' );
define( 'KK_FB_WC_ACTION_SAVE_ACCOUNT', 'kk_fb_wc_saveaccount' );

define( 'KK_FB_WC_WELCOME_MESSAGE', 'kk_fb_wc_welcome_message' );
define( 'KK_FB_WC_BOOTSTRAP_MESSAGE', 'kk_fb_wc_bootstrap_message' );
define( 'KK_FB_WC_DUPLICATE_PIXEL_MESSAGE', 'kk_fb_wc_duplicate_pixel_message' );

define( 'KK_FB_WC_MAIN_SCRIPT_NAME', 'kk-main-analytics-script' );
define( 'KK_FB_WC_REGEVENT_FUNC_NAME', 'kkFbWcRegisterEvents' );

define( 'KK_FB_WC_SCHEDULE_ACTION_GET_PIXEL_NAME', 'kk_fb_wc_schedule_get_pixel' );

require KK_FB_WC_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Starting point. Try to initiate the main instance of the plugin.
 */
function kk_fb_wc_plugin() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		$plugin = new \Kliken\FbWcPlugin\Plugin();
	}

	return $plugin;
}

kk_fb_wc_plugin()->maybe_run();
