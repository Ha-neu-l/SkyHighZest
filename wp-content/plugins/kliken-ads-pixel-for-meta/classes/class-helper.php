<?php
/**
 * Helper class to provide some common functionalities
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class
 */
class Helper {
	/**
	 * A wrapper around WC_Logger log method.
	 *
	 * @param string  $level Log level.
	 * @param string  $message Message to log.
	 * @param boolean $force Force logging without the need of WP_DEBUG mode.
	 */
	public static function wc_log( $level, $message, $force = false ) {
		if ( ( WP_DEBUG || $force )
			&& function_exists( 'wc_get_logger' )
			&& $message
			&& in_array( $level, [ 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ], true ) ) {

			if ( ! is_string( $message ) ) {
				$message = wp_json_encode( $message );
			}

			wc_get_logger()->log( $level, $message, [ 'source' => 'kliken-ads-pixel-for-meta' ] );
		}
	}

	/**
	 * Check if the plugin is configured or not. It is if we have account id and application token.
	 *
	 * @param array $settings Plugin settings.
	 */
	public static function is_plugin_configured( $settings = null ) {
		$settings ??= self::get_plugin_settings();

		if ( empty( $settings ) ) {
			return false;
		}

		if ( ! self::is_valid_account_id( $settings['account_id'] ) ) {
			return false;
		}

		if ( ! self::is_valid_app_token( $settings['app_token'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get plugin options, being an integration with WooCommerce Settings API.
	 *
	 * @return array
	 */
	public static function get_plugin_settings() {
		return get_option( KK_FB_WC_SETTINGS_OPTION_KEY, [] );
	}

	/**
	 * Check if provided id can be considered a valid account id.
	 * Should be an integer.
	 *
	 * @param mixed $id Account Id.
	 * @return boolean
	 */
	public static function is_valid_account_id( $id ) {
		return ! empty( $id ) && ctype_digit( strval( $id ) );
	}

	/**
	 * Check if the provided token can be considered a valid application token.
	 * Should be not empty, for now.
	 *
	 * @param string $token Application token.
	 * @return boolean
	 */
	public static function is_valid_app_token( $token ) {
		return ! empty( sanitize_text_field( $token ) );
	}

	/**
	 * Check if current page is a the plugin page.
	 *
	 * @return boolean
	 */
	public static function is_plugin_page() {
		global $pagenow;

		if ( 'admin.php' !== $pagenow ) {
			return false;
		}

		return isset( $_GET['page'] ) && KK_FB_WC_MAIN_PAGE_SLUG === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get plugin page URL.
	 */
	public static function get_plugin_page() {
		return menu_page_url( KK_FB_WC_MAIN_PAGE_SLUG, false );
	}

	/**
	 * Get URL-safe base64 encoded string.
	 *
	 * @param mixed $input Input string to encode.
	 * @return string
	 */
	public static function base64url_encode( $input ) {
		return str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $input ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Build the WooCommerce authorization URL
	 * Doc: https://woocommerce.github.io/woocommerce-rest-api-docs/#rest-api-keys
	 *
	 * @param int    $account_id Account Id.
	 * @param string $application_token Application Token.
	 * @return string|null
	 */
	public static function build_authorization_url( $account_id, $application_token ) {
		if ( empty( $account_id ) || empty( $application_token ) ) {
			return null;
		}

		$authorization_url = get_site_url() . '/wc-auth/v1/authorize'
			. '?app_name=' . rawurlencode( __( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' ) )
			. '&scope=read_write'
			. '&user_id=' . base64_encode( $account_id . ':' . $application_token ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			. '&return_url=' . rawurlencode( KK_FB_WC_WOOKLIKEN_BASE_URL . 'meta/?acct=' . $account_id )
			. '&callback_url=' . rawurlencode( KK_FB_WC_AUTH_CALLBACK_URL );

		return $authorization_url;
	}

	/**
	 * Build sign up link.
	 */
	public static function build_signup_link() {
		$current_user = wp_get_current_user();

		return sprintf(
			KK_FB_WC_WOOKLIKEN_BASE_URL . 'meta/auth/preauth.aspx?u=%s&n=%s&e=%s&ae=%s&sa=%s&t=%s',
			rawurlencode( get_site_url() ),
			rawurlencode( $current_user->display_name ),
			rawurlencode( $current_user->user_email ),
			rawurlencode( get_option( 'admin_email', '' ) ),
			rawurlencode( get_admin_url() . 'admin.php?action=' . KK_FB_WC_ACTION_SAVE_ACCOUNT ),
			wp_create_nonce( KK_FB_WC_ACTION_SAVE_ACCOUNT )
		);
	}

	/**
	 * Save plugin settings. Only non-null values will be saved.
	 *
	 * @param int    $account_id Account Id.
	 * @param string $app_token Application Token.
	 * @param string $facebook_token Facebook Domain Verification Token.
	 * @param string $google_token Google Site Verification Token.
	 * @param array  $saved_settings Settings currently saved to the database.
	 * @return array Returns the saved settings.
	 */
	public static function save_plugin_settings(
		$account_id = null,
		$app_token = null,
		$facebook_token = null,
		$google_token = null,
		$saved_settings = null
	) {
		$saved_settings ??= self::get_plugin_settings();

		$to_update = [
			'account_id'     => intval( $account_id ?? $saved_settings['account_id'] ?? 0 ),
			'app_token'      => sanitize_text_field( $app_token ?? $saved_settings['app_token'] ?? '' ),
			'facebook_token' => sanitize_text_field( $facebook_token ?? $saved_settings['facebook_token'] ?? '' ),
			'google_token'   => sanitize_text_field( $google_token ?? $saved_settings['google_token'] ?? '' ),
		];

		update_option( KK_FB_WC_SETTINGS_OPTION_KEY, $to_update );

		// Update data to the other plugin's settings if available.
		self::save_other_plugin_settings( $to_update );

		return $to_update;
	}

	/**
	 * Get various data of the other plugin, for interoperability checks.
	 *
	 * @param bool $active_state_only If only want to get active states. Less calls made.
	 * @return array
	 */
	public static function get_other_plugin_state( $active_state_only = false ) {
		$state = [];

		if ( defined( 'KK_WC_INTEGRATION_PAGE_ID' ) ) {
			$state['active'] = true;
		} else {
			$state['active'] = self::is_plugin_active( KK_FB_WC_OTHER_PLUGIN_REL_PATH );
		}

		if ( $active_state_only ) {
			return $state;
		}

		if ( method_exists( '\Kliken\WcPlugin\Helper', 'get_option_key' ) ) {
			$state['option_key'] = \Kliken\WcPlugin\Helper::get_option_key();
		} else {
			$state['option_key'] = 'woocommerce_kk_wcintegration_settings';
		}

		$state['settings'] = get_option( $state['option_key'], [] );

		$state['configured'] = ! empty( $state['settings'] )
			&& self::is_valid_account_id( $state['settings']['account_id'] )
			&& self::is_valid_app_token( $state['settings']['app_token'] );

		return $state;
	}

	/**
	 * Save settings to the other plugin of ours.
	 *
	 * @param array $settings Setings to be saved.
	 * @return void
	 */
	public static function save_other_plugin_settings( array $settings ) {
		$state = self::get_other_plugin_state();

		if ( ! $state['active'] && ! $state['configured'] && empty( $state['settings'] ) ) {
			// Plugin is probably not even installed.
			return;
		}

		update_option( $state['option_key'], array_merge( $state['settings'], $settings ) );
	}

	/**
	 * Get Pixel data as transient. Should be valid for 20 minutes, max.
	 *
	 * @return null|array
	 */
	public static function get_pixel_data() {
		$saved_value = get_transient( KK_FB_WC_SETTINGS_PIXEL_KEY );

		// If the saved values expired, the `get_transient` will return `false`.
		if ( false !== $saved_value ) {
			return $saved_value;
		}

		return null;
	}

	/**
	 * Get Pixel data from our API, then cache as transient for 20 minutes.
	 */
	public static function get_pixel_data_from_api() {
		try {
			$settings = self::get_plugin_settings();

			$res = wp_remote_get(
				KK_FB_WC_WEBEVENT_API_URL . 'Pixel',
				[
					'timeout' => 30,
					'headers' => [
						'Authorization' => 'Basic ' . base64_encode( $settings['account_id'] . ':' . $settings['app_token'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						'Accept'        => 'application/json',
					],
				]
			);

			if ( 200 !== wp_remote_retrieve_response_code( $res ) ) {
				return;
			}

			$pixel_data = json_decode( wp_remote_retrieve_body( $res ), true );

			// Cache the value for 20 minutes.
			set_transient( KK_FB_WC_SETTINGS_PIXEL_KEY, $pixel_data, MINUTE_IN_SECONDS * 20 );
		} catch ( \Exception $ex ) {
			self::wc_log( 'error', 'Failed to make request to get Pixel data. Error: ' . $ex->getMessage() );
		}
	}

	/**
	 * Get FB plugin state, including whether it is active, configured, and its Pixel ID.
	 *
	 * @param bool $get_pixel Whether or not we want to get Pixel ID. Sometimes we don't care.
	 * @return array
	 */
	public static function get_fb_plugin_state( $get_pixel = false ) {
		$state                       = [];
		$fb_access_token_option_name = '';
		$fb_config_option_name       = '';

		if ( defined( '\WooCommerce\Facebook\Handlers\Connection::OPTION_ACCESS_TOKEN' )
			&& defined( 'WooCommerce\Facebook\Events\Event\WC_Facebookcommerce_Pixel::SETTINGS_KEY' ) ) {
			$state['active']             = true;
			$fb_access_token_option_name = constant( '\WooCommerce\Facebook\Handlers\Connection::OPTION_ACCESS_TOKEN' );
			$fb_config_option_name       = constant( 'WooCommerce\Facebook\Events\Event\WC_Facebookcommerce_Pixel::SETTINGS_KEY' );
		} else {
			$state['active']             = self::is_plugin_active( 'facebook-for-woocommerce/facebook-for-woocommerce.php' );
			$fb_access_token_option_name = 'wc_facebook_access_token';
			$fb_config_option_name       = 'facebook_config';
		}

		if ( true !== $state['active'] ) {
			return $state;
		}

		$state['configured'] = ! empty( get_option( $fb_access_token_option_name, '' ) );

		if ( true !== $state['configured'] || true !== $get_pixel ) {
			return $state;
		}

		// Get FB options to get Pixel ID.
		$fb_options = get_option( $fb_config_option_name );
		if ( $fb_options ) {
			$state['pixel_id'] = $fb_options['pixel_id'];
		}

		return $state;
	}

	/**
	 * Try to check if a plugin is active. Will try to use built-in WordPress function first.
	 * Otherwise, it's using the copied code of that built-in WordPress function.
	 * We do this because sometimes, we need this check before the WordPress function is available to us.
	 *
	 * @param mixed $plugin Plugin path. Should be the folder name, and the main file name.
	 * @return bool
	 */
	private static function is_plugin_active( $plugin ) {
		// Use WordPress function if it exists at this point.
		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( $plugin );
		}

		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || self::is_plugin_active_for_network( $plugin );
	}

	/**
	 * A copy of the built-in WordPress method of the same name.
	 * We do this because sometimes, we need this check before the WordPress function is available to us.
	 *
	 * @param mixed $plugin Plugin path. Should be the folder name, and the main file name.
	 * @return bool
	 */
	private static function is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}
}
