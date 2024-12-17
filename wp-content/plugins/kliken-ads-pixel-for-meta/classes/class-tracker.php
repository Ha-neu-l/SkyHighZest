<?php
/**
 * Class to deal with tracker stuff.
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Tracker class to deal with tracking script stuff.
 */
class Tracker {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $_plugin_settings;

	/**
	 * User data to be sent with events.
	 * Only being populated if Automatic Advanced Matching is enabled on the associated Pixel.
	 * Also respect AAM settings for the fields being available.
	 *
	 * @var null|array
	 */
	private $_user_data;

	/**
	 * Client data to be sent with back-end events.
	 * Contains: client_ip_address, client_user_agent, click_id, and browser_id.
	 *
	 * @var null|array
	 */
	private $_client_data;

	/**
	 * WooCommerce store currency code.
	 *
	 * @var string
	 */
	private $_wc_currency;

	/**
	 * Pixel settings. For now, only contain AAM-related settings.
	 *
	 * @var null|array
	 */
	private $_pixel_settings;

	/**
	 * Basic pieces of information for an Event's custom data.
	 * Like source, version, etc.
	 *
	 * @var array
	 */
	private $_base_custom_data;

	/**
	 * FB plugin state, whether or not it is active, configured, with Pixel ID.
	 *
	 * @var array
	 */
	private $_fb_plugin_state;

	/**
	 * Tracker constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->_plugin_settings = Helper::get_plugin_settings();
		$this->_wc_currency     = get_woocommerce_currency();

		// If the plugin is not configured, we won't do any tracking,
		// so we don't need those pieces of data either.
		if ( Helper::is_plugin_configured( $this->_plugin_settings ) ) {
			$this->_pixel_settings   = Helper::get_pixel_data();
			$this->_base_custom_data = $this->get_base_event_custom_data();

			if ( ! empty( $this->_pixel_settings ) ) {
				$this->_client_data = [
					'client_ip_address' => \WC_Geolocation::get_ip_address(),
					'client_user_agent' => $this->get_client_user_agent(),
					'click_id'          => $this->get_click_id(),
					'browser_id'        => $this->get_browser_id(),
				];

				if ( true === $this->_pixel_settings['aamEnabled'] ) {
					$this->_user_data = $this->get_current_user_info();
				}
			}

			// Only get Pixel ID for admin, for the different pixel message.
			$this->_fb_plugin_state = Helper::get_fb_plugin_state( is_admin() );

			$this->register_hooks();
		}
	}

	/**
	 * Register various hooks needed for putting tracking code onto the page/action.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_head', [ $this, 'add_meta_verification_tokens' ] );

		if ( Helper::is_plugin_configured( $this->_plugin_settings ) ) {
			// Handle various events for: product page, category page, search result page, order received page.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// To track add to cart action.
			add_action( 'woocommerce_add_to_cart', [ $this, 'woocommerce_add_to_cart' ] );

			// Hook AJAX call to the AJAX action to get cart data. nopriv means that this will run with non-logged in users.
			add_action( 'wp_ajax_' . KK_FB_WC_ACTION_FETCH_CART_ITEMS, [ $this, 'ajax_fetch_cart_items' ] );
			add_action( 'wp_ajax_nopriv_' . KK_FB_WC_ACTION_FETCH_CART_ITEMS, [ $this, 'ajax_fetch_cart_items' ] );

			// Show a warning message about different pixels configuration situation, if needed.
			add_action( 'admin_notices', [ $this, 'show_different_pixel_message' ] );
		}
	}

	/**
	 * Show a warning message about different pixel configuration situation, if needed.
	 *
	 * @return void
	 */
	public function show_different_pixel_message() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! empty( $this->_fb_plugin_state['pixel_id'] )
			&& ! empty( $this->_pixel_settings['pixelId'] )
			&& floatval( $this->_fb_plugin_state['pixel_id'] ) !== floatval( $this->_pixel_settings['pixelId'] )
			&& false === get_option( KK_FB_WC_DUPLICATE_PIXEL_MESSAGE . Plugin::DISMISS_POSTFIX, false ) ) {

			$message = sprintf(
				wp_kses(
					/* translators: %1$s: Plugin name. %2$s, %3$s: Hyperlinks. Meta, Pixel are brand names/products. Do not translate. */
					__( 'You currently have %1$s installed which is using a different Meta Pixel. This may result in under reporting of conversions. Please consider updating the account settings to use the same Pixel in each app, or <a href="%2$s">click here</a> to disable the %1$s app. <a href="%3$s" target="_blank" rel="noopener noreferrer">Learn more</a>.', 'kliken-ads-pixel-for-meta' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				'Facebook for WooCommerce',
				esc_url( get_admin_url() . 'plugins.php' ),
				esc_url( 'https://intercom.help/kliken/en/articles/9691843-troubleshooting-facebook-ads-data-detecting-multiple-pixels' )
			);

			Message::show_warning( $message, 'duppixel' );
		}
	}

	/**
	 * Add Verification Tokens to site's header if available.
	 */
	public function add_meta_verification_tokens() {
		// Inject Facebook domain verification token.
		// Sanitize the saved string again just in case.
		$fb_token = sanitize_text_field( $this->_plugin_settings['facebook_token'] );

		if ( $fb_token ) {
			printf( '<!-- Kliken Facebook Domain Verification Token Tag -->' );
			printf( '<meta name="facebook-domain-verification" content="%s" />', esc_attr( $fb_token ) );
		}

		// Inject Google site verification token.
		// Sanitize the saved string again just in case.
		$google_token = sanitize_text_field( $this->_plugin_settings['google_token'] );

		if ( $google_token ) {
			printf( '<!-- Kliken Google Site Verification Token Tag -->' );
			printf( '<meta name="google-site-verification" content="%s" />', esc_attr( $google_token ) );
		}
	}

	/**
	 * Add Tracking Scripts, depending on the current page type.
	 */
	public function enqueue_scripts() {
		// Basic script.
		wp_enqueue_script(
			'kk-fb-script',
			KK_FB_WC_PLUGIN_URL . 'assets/kk-fb-script.js',
			[ 'jquery' ],
			KK_FB_WC_PLUGIN_VERSION,
			false
		);

		wp_localize_script(
			'kk-fb-script',
			'kkfbFetchCartItems',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'action'   => KK_FB_WC_ACTION_FETCH_CART_ITEMS,
				'nonce'    => wp_create_nonce( KK_FB_WC_ACTION_FETCH_CART_ITEMS ),
				'currency' => get_woocommerce_currency(),
			]
		);

		$optional_settings  = '';
		$other_plugin_state = Helper::get_other_plugin_state( true );

		if ( ! $other_plugin_state['active'] ) {
			$optional_settings = '/nosw';
		}

		// Analytic main script.
		wp_enqueue_script(
			KK_FB_WC_MAIN_SCRIPT_NAME,
			'https://analytics.sitewit.com/v3/' . $this->_plugin_settings['account_id'] . $optional_settings . '/sw.js',
			[], // No dependencies.
			KK_FB_WC_PLUGIN_VERSION,
			[ 'strategy' => 'defer' ]
		);

		if ( ! empty( $this->_user_data ) ) {
			wp_add_inline_script(
				KK_FB_WC_MAIN_SCRIPT_NAME,
				'var kkInitUserData = ' . wp_json_encode( $this->_user_data ) . ';',
				'before'
			);
		}

		if ( ! empty( $this->_base_custom_data ) ) {
			$agent = sprintf(
				'%s-v%s-v%s',
				$this->_base_custom_data['source'],
				$this->_base_custom_data['version'],
				$this->_base_custom_data['kkPluginVersion']
			);

			wp_add_inline_script(
				KK_FB_WC_MAIN_SCRIPT_NAME,
				'var kkInitAgentData = ' . wp_json_encode( [ 'agent' => $agent ] ) . ';',
				'before'
			);
		}

		$scripts = $this->handle_tracking_events();

		$tracking_script = 'function ' . esc_js( KK_FB_WC_REGEVENT_FUNC_NAME ) . "() {\n";

		foreach ( $scripts as $script ) {
			if ( empty( $script ) ) {
				continue;
			}

			$tracking_script .= $script . "\n";
		}

		$tracking_script .= "}\n";

		wp_add_inline_script( KK_FB_WC_MAIN_SCRIPT_NAME, $tracking_script . $this->get_wrapper_script_code() );
	}

	/**
	 * Handle various types of events.
	 *
	 * @return array An array of tracking script codes that can be put on the page for front-end tracking purposes.
	 */
	private function handle_tracking_events() {
		$script_code = [];

		if ( ! empty( $this->_fb_plugin_state['configured'] ) && true === $this->_fb_plugin_state['configured'] ) {
			$script_code[] = 'sw.use_fbq = false;';
		}

		// Page View.
		$script_code[] = $this->get_generic_event_script_code(
			[
				'event_name'  => 'PageView',
				'custom_data' => $this->_base_custom_data,
			],
			false
		);

		// Checkout page.
		if ( is_checkout() ) {
			$script_code[] = $this->handle_event( 'InitiateCheckout', $this->build_cart_data() );
		}

		// Order received/confirmation page.
		if ( is_order_received_page() ) {
			$order = $this->build_order_data();

			if ( null !== $order ) {
				$script_code[] = $this->handle_event( 'Purchase', $order );

				// Check to send 'Subscribe' event if needed.
				if ( function_exists( 'wcs_get_subscriptions_for_order' ) ) {
					foreach ( wcs_get_subscriptions_for_order( $order['order_id'] ) as $subscription ) {
						$script_code[] = $this->handle_event(
							'Subscribe',
							[
								'total'       => $subscription->get_total(),
								'sign_up_fee' => $subscription->get_sign_up_fee(),
								'currency'    => $this->_wc_currency,
							]
						);
					}
				}
			}
		}

		// Product page.
		if ( is_product() ) {
			$script_code[] = $this->handle_event( 'ViewContent', $this->build_product_data() );
		}

		// Product category page.
		if ( is_product_category() ) {
			$script_code[] = $this->handle_event( 'ViewCategory', $this->build_category_data() );
		}

		// Product search result page.
		if ( ! is_admin()
			&& is_search()
			&& '' !== get_search_query()
			&& 'product' === get_query_var( 'post_type' ) ) {
			$script_code[] = $this->handle_event( 'Search', $this->build_search_data() );
		}

		return $script_code;
	}

	/**
	 * Handle when a product is added to cart via Submit action instead of AJAX.
	 * This will trigger a JS call to get cart info via AJAX.
	 */
	public function woocommerce_add_to_cart() {
		if ( wp_doing_ajax() ) {
			return;
		}

		wp_enqueue_script(
			'kk-fb-trigger-cart-script',
			KK_FB_WC_PLUGIN_URL . 'assets/kk-fb-trigger-cart-script.js',
			[],
			KK_FB_WC_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Action to handle AJAX request to get updated cart data.
	 */
	public function ajax_fetch_cart_items() {
		check_ajax_referer( KK_FB_WC_ACTION_FETCH_CART_ITEMS );

		$cart = $this->build_cart_data();

		if ( null !== $cart ) {
			$event = $this->build_event_data( 'AddToCart', $cart );

			$this->send_server_side_event( $event );

			// Front-end event will be handled after this AJAX response reaches the calling JS code.

			$response = [
				'cart'  => $cart,
				'event' => [
					'event_id'    => $event['event_id'],
					'custom_data' => $event['custom_data'],
				],
			];

			wp_send_json( $response );
		}

		wp_die();
	}

	/**
	 * Handle event, including building event data, sending server side event, and injecting event code to page (if needed).
	 *
	 * @param string $event_name Name of the event.
	 * @param array  $data Data for the event.
	 * @return string|null Script code to be used for front-end event tracking.
	 */
	protected function handle_event( $event_name, $data ) {
		if ( empty( $data ) ) {
			return null;
		}

		$event = $this->build_event_data( $event_name, $data );

		// Only send server-side event if the FB plugin is NOT configured.
		if ( ! empty( $this->_fb_plugin_state['configured'] ) && true === $this->_fb_plugin_state['configured'] ) {
			$this->send_server_side_event( $event );
		}

		$inject_code = null;

		switch ( $event_name ) {
			case 'InitiateCheckout':
			case 'Search':
			case 'Subscribe':
			case 'ViewCategory':
				$inject_code = $this->get_generic_event_script_code( $event );
				break;
			case 'Purchase':
				$inject_code = $this->get_purchase_confirmation_script_code( $event, $data );
				break;
			case 'ViewContent':
				$inject_code = $this->get_product_view_script_code( $event, $data );
				break;
		}

		return $inject_code;
	}

	/**
	 * Build event data.
	 *
	 * @param string $event_name Name of the event.
	 * @param array  $data Data for the event.
	 * @return array
	 */
	protected function build_event_data( $event_name, $data ) {
		$event = [
			'action_source'    => 'website',
			'event_name'       => $event_name,
			'event_time'       => time(),
			'event_id'         => $this->generate_event_id(),
			'event_source_url' => $this->get_current_url(),
			'user_data'        => $this->_user_data,
		];

		$custom_data = [];

		switch ( $event_name ) {
			case 'AddToCart':
				$custom_data = [
					'value'        => $data['total'],
					'currency'     => $data['currency'],
					'content_name' => 'AddToCart',
					'content_ids'  => array_map( fn( $item ) => $item['id'], $data['items'] ),
					'content_type' => 'product',
					'contents'     => array_map(
						function ( $item ) {
							return [
								'id'       => $item['id'],
								'quantity' => $item['quantity'],
							];
						},
						$data['items']
					),
				];

				break;
			case 'InitiateCheckout':
				$custom_data = [
					'num_items'    => $data['num_items'],
					'value'        => $data['total'],
					'currency'     => $data['currency'],
					'content_name' => 'InitiateCheckout',
					'content_ids'  => array_map( fn( $item ) => $item['id'], $data['items'] ),
					'content_type' => 'product',
					'contents'     => array_map(
						function ( $item ) {
							return [
								'id'       => $item['id'],
								'quantity' => $item['quantity'],
							];
						},
						$data['items']
					),
				];

				if ( ! empty( $data['category'] ) ) {
					$custom_data['content_category'] = $data['category'];
				}
				break;
			case 'Purchase':
				// Use order id as EventID. This should help with dedupping those events.
				$event['event_id']  = 'order-' . $data['order_id'];
				$event['user_data'] = $this->get_user_info_from_order( $data['customer'] );

				$custom_data = [
					'value'        => $data['total'],
					'currency'     => $data['currency'],
					'content_name' => 'Purchase',
					'content_ids'  => array_map( fn( $item ) => $item['id'], $data['items'] ),
					'content_type' => $data['content_type'],
					'contents'     => array_map(
						function ( $item ) {
							return [
								'id'       => $item['id'],
								'quantity' => $item['quantity'],
							];
						},
						$data['items']
					),
				];
				break;
			case 'Search':
				$custom_data = [
					'value'         => $data['value'],
					'currency'      => $data['currency'],
					'search_string' => $data['query'],
					'content_ids'   => $data['product_ids'],
					'content_type'  => $data['content_type'],
					'contents'      => array_map(
						function ( $product_id ) {
							return [
								'id'       => $product_id,
								'quantity' => 1,
							];
						},
						$data['product_ids']
					),
				];
				break;
			case 'Subscribe':
				$custom_data = [
					'sign_up_fee' => $data['sign_up_fee'],
					'value'       => $data['total'],
					'currency'    => $data['currency'],
				];
				break;
			case 'ViewCategory':
				$custom_data = [
					'content_name'     => $data['name'],
					'content_ids'      => $data['product_ids'],
					'content_type'     => $data['content_type'],
					'content_category' => $data['name'],
					'contents'         => array_map(
						function ( $product_id ) {
							return [
								'id'       => $product_id,
								'quantity' => 1,
							];
						},
						$data['product_ids']
					),
				];
				break;
			case 'ViewContent':
				$custom_data = [
					'value'        => $data['price'],
					'currency'     => $data['currency'],
					'content_name' => $data['name'],
					'content_ids'  => [ $data['id'] ],
					'content_type' => $data['content_type'],
					'contents'     => [
						[
							'id'       => $data['id'],
							'quantity' => 1,
						],
					],
				];

				if ( ! empty( $data['categories'] ) ) {
					$custom_data['content_category'] = array_values( $data['categories'] )[0];
				}
				break;
		}

		$event['custom_data'] = array_merge( $custom_data, $this->_base_custom_data );

		return $event;
	}

	/**
	 * Send the Pixel event server-side, to our proxy API, so it forwards to FB CAPI.
	 *
	 * @param array $event Event to send.
	 */
	protected function send_server_side_event( $event ) {
		if ( empty( $this->_pixel_settings ) ) {
			return;
		}

		if ( ! empty( $event['user_data'] ) ) {
			// Hash user data before sending.
			$keys_to_hash = [ 'em', 'fn', 'ln', 'ph', 'ct', 'st', 'zp', 'country', 'external_id' ];
			foreach ( $keys_to_hash as $key ) {
				if ( array_key_exists( $key, $event['user_data'] ) ) {
					$event['user_data'][ $key ] = hash( 'sha256', $event['user_data'][ $key ] );
				}
			}

			// We need those extra pieces for server-side.
			$event['user_data'] = array_merge( $event['user_data'], $this->_client_data );
		} else {
			$event['user_data'] = $this->_client_data;
		}

		// Hit server for event.
		try {
			wp_remote_post(
				KK_FB_WC_WEBEVENT_API_URL . 'Events',
				[
					'blocking' => false,
					'headers'  => [
						'Authorization' => 'Basic ' . base64_encode( $this->_plugin_settings['account_id'] . ':' . $this->_plugin_settings['app_token'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						'Accept'        => 'application/json',
						'Content-Type'  => 'application/json',
					],
					'body'     => wp_json_encode(
						[
							'EventType' => $event['event_name'],
							'EventData' => wp_json_encode( $event ),
						]
					),
				]
			);
		} catch ( \Exception $ex ) {
			Helper::wc_log( 'error', 'Faild to make request to send offline event. Error: ' . $ex->getMessage() );
		}
	}

	/**
	 * Generates a UUIDv4 unique ID for the event.
	 * Code from link below, but with `wp_rand` method, and styling as recommended by WordPress.
	 * Even though the fallback part is not quite necessary as we support only PHP 7.4 and above now.
	 *
	 * @see https://stackoverflow.com/a/15875555
	 *
	 * @return string
	 */
	protected function generate_event_id() {
		try {
			$data    = random_bytes( 16 );
			$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );  // set version to 0100.
			$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );  // set bits 6-7 to 10.
			return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
		} catch ( \Exception $e ) {
			// Fall back to mt_rand if random_bytes is unavailable.
			return sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low".
				wp_rand( 0, 0xffff ),
				wp_rand( 0, 0xffff ),
				// 16 bits for "time_mid".
				wp_rand( 0, 0xffff ),
				// 16 bits for "time_hi_and_version".
				// four most significant bits holds version number 4.
				wp_rand( 0, 0x0fff ) | 0x4000,
				// 16 bits, 8 bits for "clk_seq_hi_res".
				// 8 bits for "clk_seq_low".
				// two most significant bits holds zero and one for variant DCE1.1.
				wp_rand( 0, 0x3fff ) | 0x8000,
				// 48 bits for "node".
				wp_rand( 0, 0xffff ),
				wp_rand( 0, 0xffff ),
				wp_rand( 0, 0xffff )
			);
		}
	}

	/**
	 * Gets the browser ID from the cookie.
	 *
	 * @return string
	 */
	protected function get_browser_id() {
		return ! empty( $_COOKIE['_fbp'] ) ? wc_clean( wp_unslash( $_COOKIE['_fbp'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Get current URL.
	 *
	 * @return string
	 */
	protected function get_current_url() {
		if ( wp_doing_ajax() ) {
			$url = wp_get_raw_referer();
		} else {
			global $wp;
			$url = home_url( $wp->request );

			/**
			 * Instead of relying on the HTTP_HOST server var, we use home_url(),
			 * so that we get the host configured in site options.
			 * Additionally, this automatically uses the correct domain when
			 * using Forward with the WooCommerce Dev Helper plugin.
			 */

			/*
			$url = home_url();

			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$url .= wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			}
			*/
		}
		return $url;
	}

	/**
	 * Gets the click ID from the cookie or the query parameter.
	 *
	 * @see https://developers.facebook.com/docs/marketing-api/server-side-api/parameters/fbp-and-fbc#fbp-and-fbc-parameters
	 *
	 * @return string
	 */
	protected function get_click_id() {
		$click_id = '';

		if ( ! empty( $_COOKIE['_fbc'] ) ) {
			$click_id = wc_clean( wp_unslash( $_COOKIE['_fbc'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		} elseif ( ! empty( $_REQUEST['fbclid'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Generate the click ID based on the query parameter.
			$version         = 'fb';
			$subdomain_index = 1;
			$creation_time   = time();
			$fbclid          = wc_clean( wp_unslash( $_REQUEST['fbclid'] ) ); // phpcs:ignore
			$click_id        = "{$version}.{$subdomain_index}.{$creation_time}.{$fbclid}";
		}

		return $click_id;
	}

	/**
	 * Get client user agent.
	 *
	 * @return string
	 */
	protected function get_client_user_agent() {
		return ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? wc_clean( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Get currently logged-in user info.
	 * This respects AAM settings.
	 *
	 * @return null|array
	 */
	protected function get_current_user_info() {
		if ( empty( $this->_pixel_settings['aamEnabled'] )
			|| true !== $this->_pixel_settings['aamEnabled']
			|| empty( $this->_pixel_settings['availableFields'] ) ) {
			return null;
		}

		$current_user = wp_get_current_user();

		if ( 0 === $current_user->ID ) {
			// User not logged in.
			return null;
		} else {
			// See https://developers.facebook.com/docs/facebook-pixel/advanced/advanced-matching.
			$user_info = [];
			$user_id   = $current_user->ID;

			if ( in_array( 'Email', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['em'] = $current_user->user_email;
			}

			if ( in_array( 'FirstName', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['fn'] = $current_user->user_firstname;
			}

			if ( in_array( 'LastName', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['ln'] = $current_user->user_lastname;
			}

			if ( in_array( 'ExternalId', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['external_id'] = strval( $user_id );
			}

			if ( in_array( 'City', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['ct'] = get_user_meta( $user_id, 'billing_city', true );
			}

			if ( in_array( 'State', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['st'] = get_user_meta( $user_id, 'billing_state', true );
			}

			if ( in_array( 'ZipCode', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['zp'] = get_user_meta( $user_id, 'billing_postcode', true );
			}

			if ( in_array( 'Country', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['country'] = get_user_meta( $user_id, 'billing_country', true );
			}

			if ( in_array( 'PhoneNumber', $this->_pixel_settings['availableFields'], true ) ) {
				$user_info['ph'] = get_user_meta( $user_id, 'billing_phone', true );
			}

			return $user_info;
		}
	}

	/**
	 * Get user info from billing data.
	 * This respects AAM settings.
	 *
	 * @param array $order_customer Customer data from order billing data.
	 * @return null|array
	 */
	private function get_user_info_from_order( $order_customer ) {
		if ( empty( $this->_pixel_settings['aamEnabled'] )
			|| true !== $this->_pixel_settings['aamEnabled']
			|| empty( $this->_pixel_settings['availableFields'] ) ) {
			return null;
		}

		$user_info = [];

		if ( in_array( 'Email', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['em'] = $order_customer['email'];
		}

		if ( in_array( 'FirstName', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['fn'] = $order_customer['first_name'];
		}

		if ( in_array( 'LastName', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['ln'] = $order_customer['last_name'];
		}

		if ( in_array( 'PhoneNumber', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['ph'] = $order_customer['phone'];
		}

		if ( in_array( 'City', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['ct'] = $order_customer['city'];
		}

		if ( in_array( 'State', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['st'] = $order_customer['state'];
		}

		if ( in_array( 'ZipCode', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['zp'] = $order_customer['postcode'];
		}

		if ( in_array( 'Country', $this->_pixel_settings['availableFields'], true ) ) {
			$user_info['country'] = $order_customer['country'];
		}

		return $user_info;
	}

	/**
	 * Build shopping cart data preparing to be recorded by our tracking script.
	 *
	 * @return null|array
	 */
	private function build_cart_data() {
		$cart = WC()->cart;

		if ( null === $cart || $cart->is_empty() ) {
			return null;
		}

		$cart_info = [
			'currency'  => $this->_wc_currency,
			'num_items' => $cart->get_cart_contents_count(),
			'total'     => $cart->total,
			'category'  => null,
			'items'     => [],
		];

		$cart_items = array_values( $cart->get_cart() );
		$num_items  = count( $cart_items );

		for ( $i = 0; $i < $num_items; $i++ ) {
			$product = $cart_items[ $i ]['data'];

			if ( ! $product instanceof \WC_Product ) {
				continue;
			}

			$cart_info['items'][] = [
				'id'       => $this->get_product_id_for_fb( $product ),
				'name'     => $product->get_name(),
				'quantity' => $cart_items[ $i ]['quantity'],
				'price'    => $product->get_price(),
			];

			// Only get category name if cart only has 1 item/product.
			if ( $i > 0 ) {
				$cart_info['category'] = null; // Reset it.
				continue;
			}

			$product_categories = $this->get_product_categories( $product );

			// A product should always have at least the `Uncategorized` category, but just in case.
			if ( ! empty( $product_categories ) ) {
				$cart_info['category'] = array_values( $product_categories )[0];
			}
		}

		return $cart_info;
	}

	/**
	 * Build category data preparing to be recorded by our tracking script.
	 *
	 * @return array|null
	 */
	private function build_category_data() {
		global $wp_query;

		$content_type = 'product';
		$product_ids  = array();

		foreach ( $wp_query->posts as $post ) {
			$product = wc_get_product( $post );

			if ( ! $product instanceof \WC_Product ) {
				continue;
			}

			if ( count( $product_ids ) >= 10 ) {
				break;
			}

			$product_ids[] = $this->get_product_id_for_fb( $product );

			if ( $product->is_type( array( 'variable', 'variable-subscription' ) ) ) {
				$content_type = 'product_group';
			}
		}

		$category = get_queried_object();

		$category_info = [
			'name'         => $category->name,
			'content_type' => $content_type,
			'product_ids'  => $product_ids,
		];

		return $category_info;
	}

	/**
	 * Build transaction/order data preparing to be recorded by our tracking script.
	 *
	 * @return array|null
	 */
	private function build_order_data() {
		$order_id = get_query_var( 'order-received', 0 );

		if ( empty( $order_id ) ) {
			return null;
		}

		// Get the order detail.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return null;
		}

		// We don't care about these statuses.
		$status = $order->get_status();
		if ( 'cancelled' === $status || 'refunded' === $status || 'failed' === $status ) {
			return null;
		}

		$order_info = [
			'order_id'     => $order_id,
			'currency'     => $order->get_currency(),
			'affiliate'    => null,
			'sub_total'    => $order->get_subtotal(),
			'total'        => $order->get_total(),
			'tax'          => $order->get_total_tax(),
			'content_type' => 'product',
			'items'        => [],
			'customer'     => [
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'email'      => $order->get_billing_email(),
				'phone'      => $order->get_billing_phone(),
				'city'       => $order->get_billing_city(),
				'state'      => $order->get_billing_state(),
				'postcode'   => $order->get_billing_postcode(),
				'country'    => $order->get_billing_country(),
				'user_id'    => $order->get_user_id() === 0 ? null : strval( $order->get_user_id() ),
			],
		];

		$order_items = $order->get_items();

		// Cache category info, because in the order, there might be multiple items under same category.
		$category_cache = [];

		foreach ( $order_items as $item ) {
			$product = $item->get_product();

			if ( ! $product instanceof \WC_Product ) {
				continue;
			}

			if ( $product->is_type( 'variable' ) ) {
				$order_info['content_type'] = 'product_group';
			}

			$product_categories = $product->get_category_ids();
			$category_name      = '';

			foreach ( $product_categories as $id ) {
				if ( array_key_exists( $id, $category_cache ) ) {
					$category_name = $category_cache[ $id ];
				} else {
					$term = get_term_by( 'id', $id, 'product_cat' );
					if ( $term ) {
						$category_name         = $term->name;
						$category_cache[ $id ] = $category_name;
					}
				}
			}

			array_push(
				$order_info['items'],
				[
					'id'       => $this->get_product_id_for_fb( $product ),
					'name'     => $product->get_name(),
					'category' => $category_name,
					'price'    => $product->get_price(),
					'quantity' => $item->get_quantity(),
				]
			);
		}

		return $order_info;
	}

	/**
	 * Build product data preparing to be recorded by our tracking script.
	 *
	 * @return array|null
	 */
	private function build_product_data() {
		global $post;

		if ( ! isset( $post, $post->ID ) ) {
			return null;
		}

		$product = wc_get_product( $post->ID );

		if ( ! $product instanceof \WC_Product ) {
			return null;
		}

		$product_price = $product->is_type( [ 'variable', 'variable-subscription' ] )
			? $product->get_variation_price( 'min' )
			: $product->get_price();

		$content_type = $product->is_type( [ 'variable', 'grouped' ] ) ? 'product_group' : 'product';

		$product_info = [
			'id'           => $this->get_product_id_for_fb( $product ),
			'content_type' => $content_type,
			'name'         => $product->get_title(),
			'price'        => $product_price,
			'categories'   => $this->get_product_categories( $product ),
			'currency'     => $this->_wc_currency,
		];

		return $product_info;
	}

	/**
	 * Build search data preparing to be recorded by our tracking script.
	 *
	 * @return array|null
	 */
	private function build_search_data() {
		global $wp_query;

		if ( ! $wp_query->is_main_query()
			|| '' === get_search_query() ) {
			return null;
		}

		$content_type = 'product';
		$product_ids  = array();
		$total_value  = 0.00;

		foreach ( $wp_query->posts as $post ) {
			$product = wc_get_product( $post );

			if ( ! $product instanceof \WC_Product ) {
				continue;
			}

			if ( count( $product_ids ) >= 10 ) {
				break;
			}

			$product_ids[] = $this->get_product_id_for_fb( $product );

			if ( $product->is_type( [ 'variable', 'variable-subscription' ] ) ) {
				$content_type = 'product_group';
			}

			$total_value += (float) $product->get_price();
		}

		$search_info = [
			'query'        => get_search_query(),
			'content_type' => $content_type,
			'product_ids'  => $product_ids,
			'value'        => number_format( (float) $total_value, 2, '.', '' ),
			'currency'     => $this->_wc_currency,
		];

		return $search_info;
	}

	/**
	 * Get the wrapper script code.
	 *
	 * @return string
	 */
	private function get_wrapper_script_code() {
		return 'if (typeof sw !== "object") { '
			. 'var swPostRegister = function() { ' . esc_js( KK_FB_WC_REGEVENT_FUNC_NAME ) . '(); } '
			. '} else { ' . esc_js( KK_FB_WC_REGEVENT_FUNC_NAME ) . '(); }';
	}

	/**
	 * Get script code for a generic event.
	 *
	 * @param array $event Event data.
	 * @param bool  $include_event_id Whether to include Event ID or not. Default to true.
	 * @return string
	 */
	private function get_generic_event_script_code( $event, $include_event_id = true ) {
		$func_code = 'ViewCategory' === $event['event_name'] ? 'sw.trackCustom' : 'sw.track';
		$params    = [
			wp_json_encode( $event['event_name'] ),
			wp_json_encode( $event['custom_data'] ),
		];

		if ( $include_event_id ) {
			$params[] = wp_json_encode( $event['event_id'] );
		}

		return "\t" . $func_code . '(' . implode( ',', $params ) . ');';
	}

	/**
	 * Get script code for Product View event.
	 *
	 * @param array $event   Event data.
	 * @param array $product Raw Product data.
	 * @return string
	 */
	private function get_product_view_script_code( $event, $product ) {
		ob_start();

		?>
	sw.track(
		"ViewContent",
		<?php echo wp_json_encode( $event['custom_data'] ); ?>,
		<?php echo wp_json_encode( $event['event_id'] ); ?>
	);

	sw.gEvent(
		"view_item",
		{
			items: [
				{
					"id": "<?php echo esc_js( $product['id'] ); ?>",
					"name": "<?php echo esc_js( $product['name'] ); ?>",
					"category": "<?php echo esc_js( implode( ',', array_keys( $product['categories'] ) ) ); ?>",
					"google_business_vertical": "retail"
				}
			]
		}
	);

	sw.gEvent(
		"page_view",
		{
			"ecomm_prodid": "<?php echo esc_js( $product['id'] ); ?>"
		}
	);

	sw.register_product_view(
		{
			"id": "<?php echo esc_js( $product['id'] ); ?>",
			"category": "<?php echo esc_js( implode( ',', array_keys( $product['categories'] ) ) ); ?>"
		}
	);
		<?php

		return ob_get_clean();
	}

	/**
	 * Get script code for Purchase event.
	 *
	 * @param array $event Event data.
	 * @param array $order Raw Order data.
	 * @return string
	 */
	private function get_purchase_confirmation_script_code( $event, $order ) {
		ob_start();

		?>
	sw.track(
		"Purchase",
		<?php echo wp_json_encode( $event['custom_data'] ); ?>,
		<?php echo wp_json_encode( $event['event_id'] ); ?>
	);

	sw.gawCurrency = "<?php echo esc_js( $order['currency'] ); ?>";

	var trans = sw.create_transaction(
		"<?php echo esc_js( $order['order_id'] ); ?>",
		"<?php echo esc_js( $order['affiliate'] ); ?>",
		"<?php echo esc_js( $order['sub_total'] ); ?>",
		"<?php echo esc_js( $order['tax'] ); ?>",
		"<?php echo esc_js( $order['customer']['city'] ); ?>",
		"<?php echo esc_js( $order['customer']['state'] ); ?>",
		"<?php echo esc_js( $order['customer']['country'] ); ?>",
		"<?php echo esc_js( $order['total'] ); ?>",
	);

	trans.ignoreFbq = true;

		<?php foreach ( $order['items'] as $item ) : ?>
			trans.add_item(
				"<?php echo esc_js( $item['id'] ); ?>",
				"<?php echo esc_js( $item['name'] ); ?>",
				"<?php echo esc_js( $item['category'] ); ?>",
				"<?php echo esc_js( $item['price'] ); ?>",
				"<?php echo esc_js( $item['quantity'] ); ?>",
			);
		<?php endforeach; ?>

	sw.register_page_view("ORDER_CONFIRMATION");
		<?php

		return ob_get_clean();
	}

	/**
	 * Get product ID for event purposes.
	 *
	 * @param \WC_Product $wc_product WooCommerce product.
	 * @return string
	 */
	private function get_product_id_for_fb( \WC_Product $wc_product ) {
		return strval( $wc_product->get_id() );
	}

	/**
	 * Get a product's category as an associative array of id and name.
	 *
	 * @param \WC_Product $product Product to get categories for.
	 * @return array
	 */
	private function get_product_categories( \WC_Product $product ) {
		$product_id = $product->get_id();

		if ( $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
		}

		return wp_get_post_terms(
			$product_id,
			'product_cat',
			[ 'fields' => 'id=>name' ]
		);
	}

	/**
	 * Get basic pieces for an event's custom data.
	 *
	 * @return array
	 */
	private function get_base_event_custom_data() {
		return [
			'source'          => 'woocommerce',
			'version'         => wc()->version,
			'kkPluginVersion' => KK_FB_WC_PLUGIN_VERSION,
			'wpVersion'       => get_bloginfo( 'version' ),
		];
	}
}
