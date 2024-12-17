<?php
/**
 * Main plugin class to bootstrap everything else
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin class
 */
class Plugin {

	const ALREADY_BOOTSTRAPED      = 1;
	const DEPENDENCIES_UNSATISFIED = 2;
	const DISMISS_POSTFIX          = '_dismissed';

	/**
	 * Flag to indicate the plugin has been boostrapped.
	 *
	 * @var bool
	 */
	private $_bootstrapped = false;

	/**
	 * Try to register important hooks, with main stuff inside "plugins_loaded"
	 * so we can have checks for requirements/dependencies.
	 */
	public function maybe_run() {
		register_activation_hook( KK_FB_WC_PLUGIN_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( KK_FB_WC_PLUGIN_FILE, [ $this, 'deactivate' ] );

		add_action( 'plugins_loaded', [ $this, 'bootstrap' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( KK_FB_WC_PLUGIN_FILE ), [ $this, 'add_plugin_action_links' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		if ( is_admin() ) {
			add_action( 'wp_ajax_' . KK_FB_WC_ACTION_DISMISS_NOTICE, [ $this, 'handle_ajax_dismiss_notice' ] );
		}
	}

	/**
	 * Run when the plugin is activated.
	 */
	public function activate() {
		$saved_settings = Helper::get_plugin_settings();
		$need_update    = false;

		if ( empty( $saved_settings ) ) {
			$saved_settings = [
				'account_id'     => 0,
				'app_token'      => '',
				'facebook_token' => '',
				'google_token'   => '',
			];

			$need_update = true;
		}

		// Check if the other plugin has settings to copy from.
		$other_plugin_state = Helper::get_other_plugin_state();

		if ( $other_plugin_state['configured'] ) {
			$saved_settings = [
				'account_id'     => $other_plugin_state['settings']['account_id'],
				'app_token'      => $other_plugin_state['settings']['app_token'],
				'facebook_token' => $other_plugin_state['settings']['facebook_token'],
				'google_token'   => $other_plugin_state['settings']['google_token'],
			];

			$need_update = true;
		}

		if ( $need_update ) {
			Helper::save_plugin_settings(
				$saved_settings['account_id'],
				$saved_settings['app_token'],
				$saved_settings['facebook_token'],
				$saved_settings['google_token'],
				[] // To overwrite all fields.
			);
		}

		if ( ! Helper::is_plugin_configured( $saved_settings ) ) {
			// Show onboarding message, for a month, at top.
			set_site_transient( KK_FB_WC_WELCOME_MESSAGE, true, MONTH_IN_SECONDS );
		}
	}

	/**
	 * Run when plugin is deactivated.
	 */
	public function deactivate() {
		$this->cancel_scheduled_actions();
	}

	/**
	 * Bootstrap the execution of the plugin.
	 *
	 * @throws \Exception Throw an exception if the plugin has been called before.
	 */
	public function bootstrap() {
		try {
			add_filter( 'load_textdomain_mofile', [ $this, 'load_my_own_textdomain' ], 10, 2 );

			if ( $this->_bootstrapped ) {
				throw new \Exception(
					sprintf(
						/* translators: %s: Plugin name. Do not translate. */
						__( '%s plugin can only be called once.', 'kliken-ads-pixel-for-meta' ),
						/* translators: Meta and Woo are brand names. Meta Pixel and Meta Ads are product names. Do not translate. */
						__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' )
					),
					self::ALREADY_BOOTSTRAPED
				);
			}

			// Declare compatibility with HPOS.
			// This has to be here so that WooCommerce won't complain about us in case
			// we fail on checks of dependencies, next, and has to stop ourselves from running.
			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', KK_FB_WC_PLUGIN_FILE, true );
					}
				}
			);

			$this->check_dependencies();
			$this->register_hooks();

			$this->_bootstrapped = true;
		} catch ( \Exception $e ) {
			if ( in_array( $e->getCode(), [ self::ALREADY_BOOTSTRAPED, self::DEPENDENCIES_UNSATISFIED ], true ) ) {
				set_site_transient( KK_FB_WC_BOOTSTRAP_MESSAGE, $e->getMessage(), MONTH_IN_SECONDS );
			}

			add_action( 'admin_notices', [ $this, 'show_bootstrap_message' ] );
		}
	}

	/**
	 * We want to use our own translation files.
	 * Reference: https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#plugins-on-wordpress-org.
	 *
	 * @param mixed $mofile .mo file to be loaded.
	 * @param mixed $domain text domain of the plugin.
	 * @return mixed
	 */
	public function load_my_own_textdomain( $mofile, $domain ) {
		if ( 'kliken-ads-pixel-for-meta' === $domain
			&& false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
			/**
			 * Get the current locale to load the appropriate .mo file for translations.
			 *
			 * @since 1.0.0
			 */
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
			$mofile = WP_PLUGIN_DIR . '/' . KK_FB_WC_PLUGIN_REL_PATH . '/languages/' . $domain . '-' . $locale . '.mo';
		}

		return $mofile;
	}

	/**
	 * Add more links to plugin's "row" in the WordPress' plugin list,
	 * for more actions that user can do.
	 *
	 * @param array $links A list of links already registered.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$plugin_links = [];

		if ( function_exists( 'wc' ) ) {
			$plugin_links[] = '<a href="' . esc_url( Helper::get_plugin_page() ) . '">' . __( 'Dashboard', 'kliken-ads-pixel-for-meta' ) . '</a>';
		}

		$plugin_links[] = '<a href="https://intercom.help/kliken/">' . __( 'Support', 'kliken-ads-pixel-for-meta' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Enqueue script(s) needed for admin pages.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'kk-fb-admin-script',
			KK_FB_WC_PLUGIN_URL . 'assets/kk-fb-admin-script.js',
			[ 'jquery' ],
			KK_FB_WC_PLUGIN_VERSION,
			false
		);

		wp_localize_script(
			'kk-fb-admin-script',
			'kkfbDimissibleNotice',
			[
				'action'         => KK_FB_WC_ACTION_DISMISS_NOTICE,
				'nonce'          => wp_create_nonce( KK_FB_WC_ACTION_DISMISS_NOTICE ),
				'confirmMessage' => __( 'This will dismiss the message permanently. Are you sure?', 'kliken-ads-pixel-for-meta' ),
			]
		);
	}

	/**
	 * Action to handle AJAX request to permanently dismiss a notice.
	 */
	public function handle_ajax_dismiss_notice() {
		check_ajax_referer( KK_FB_WC_ACTION_DISMISS_NOTICE );

		if ( isset( $_POST['message_name'] ) ) { // WPCS: input var ok.
			$message_name = sanitize_key( $_POST['message_name'] );  // WPCS: input var ok.

			switch ( $message_name ) {
				case 'welcome':
					update_option( KK_FB_WC_WELCOME_MESSAGE . self::DISMISS_POSTFIX, true );
					break;
				case 'bsmessage':
					update_option( KK_FB_WC_BOOTSTRAP_MESSAGE . self::DISMISS_POSTFIX, true );
					delete_site_transient( KK_FB_WC_BOOTSTRAP_MESSAGE );
					break;
				case 'duppixel':
					update_option( KK_FB_WC_DUPLICATE_PIXEL_MESSAGE . self::DISMISS_POSTFIX, true );
					break;
			}
		}

		wp_die();
	}

	/**
	 * Check if the dependencies of the plugin are satisfied.
	 * Here we need WooCommerce above the specified version, to be installed, and active.
	 *
	 * @throws \Exception Throw an exception if the dependency is not satisfied.
	 */
	private function check_dependencies() {
		if ( ! function_exists( 'wc' ) ) {
			throw new \Exception(
				sprintf(
					/* translators: %s: Plugin name. Do not translate. */
					esc_html__( '%s requires WooCommerce to be activated.', 'kliken-ads-pixel-for-meta' ),
					esc_html__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' )
				),
				self::DEPENDENCIES_UNSATISFIED // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			);
		}

		if ( version_compare( wc()->version, KK_FB_WC_REQUIRES_AT_LEAST_VERSION, '<' ) ) {
			throw new \Exception(
				sprintf(
					/* translators: %1$s: Plugin name. %2$s: Version number of WooCommerce required to run plugin. Do not translate. */
					esc_html__( '%1$s requires WooCommerce version %2$s or greater.', 'kliken-ads-pixel-for-meta' ),
					esc_html__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' ),
					esc_html( KK_FB_WC_REQUIRES_AT_LEAST_VERSION )
				),
				self::DEPENDENCIES_UNSATISFIED // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			);
		}

		// If we see this constant defined, it means the other plugin is at "good" version.
		if ( ! defined( 'KK_WC_PLUGIN_VERSION' ) ) {
			$state = Helper::get_other_plugin_state( true );

			if ( $state['active'] ) {
				$data = get_file_data(
					WP_PLUGIN_DIR . '/' . KK_FB_WC_OTHER_PLUGIN_REL_PATH,
					[
						'name'    => 'Plugin Name',
						'version' => 'Version',
					]
				);

				if ( version_compare( $data['version'], '1.5.1', '<' ) ) {
					throw new \Exception(
						sprintf(
							/* translators: %1$s: Plugin name. %2$s: Plugin name. Do not translate. */
							esc_html__( 'An older version of "%1$s" plugin is active. For "%2$s" to work, please deactivate it, or update to latest version.', 'kliken-ads-pixel-for-meta' ),
							esc_html( $data['name'] ),
							esc_html__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' )
						),
						self::DEPENDENCIES_UNSATISFIED // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					);
				}
			}
		}
	}

	/**
	 * Show bootstrap (error) message if needed.
	 */
	public function show_bootstrap_message() {
		$message = get_site_transient( KK_FB_WC_BOOTSTRAP_MESSAGE );

		if ( ! empty( $message )
			&& false === get_option( KK_FB_WC_BOOTSTRAP_MESSAGE . self::DISMISS_POSTFIX, false )
			&& current_user_can( 'manage_woocommerce' ) ) {
			Message::show_error( esc_html( $message ), 'bsmessage' );
		}
	}

	/**
	 * Hook into different parts of WordPress and WooCommerce.
	 */
	private function register_hooks() {
		// Hook to get Pixel data, to be called by scheduler.
		add_action( KK_FB_WC_SCHEDULE_ACTION_GET_PIXEL_NAME, [ Helper::class, 'get_pixel_data_from_api' ] );

		add_action( 'init', [ $this, 'handle_init_action' ] );

		add_action( 'admin_menu', [ $this, 'add_menu_item' ], 10, 0 );
		add_action( 'admin_notices', [ $this, 'show_onboarding_message' ] );

		if ( is_admin() ) {
			// Handle request to save account info coming back from Kliken sign up process.
			add_action( 'admin_action_' . KK_FB_WC_ACTION_SAVE_ACCOUNT, [ $this, 'save_account_info' ] );
		}

		add_action( 'rest_api_init', [ $this, 'register_rest_api_routes' ] );

		// Initialize tracker.
		new Tracker();
	}

	/**
	 * Handle the init action hook.
	 * Here we will try to schedule the action to get/update Pixel data.
	 */
	public function handle_init_action() {
		load_plugin_textdomain(
			'kliken-ads-pixel-for-meta',
			false,
			KK_FB_WC_PLUGIN_REL_PATH . '/languages'
		);

		// Only schedule the action to get Pixel data, if:
		// 1. The scheduler is available.
		// 2. The action has not been scheduled yet.
		// 3. The plugin is configured.
		if ( function_exists( 'as_schedule_recurring_action' )
			&& false === as_has_scheduled_action( KK_FB_WC_SCHEDULE_ACTION_GET_PIXEL_NAME )
			&& true === Helper::is_plugin_configured() ) {

			as_schedule_recurring_action(
				time(),
				MINUTE_IN_SECONDS * 15,
				KK_FB_WC_SCHEDULE_ACTION_GET_PIXEL_NAME,
				[],
				KK_FB_WC_SLUG,
				false,
				1 // High priority.
			);
		}
	}

	/**
	 * Add menu item, under WooCommerce's Marketing.
	 */
	public function add_menu_item() {
		$hookname = add_submenu_page(
			'woocommerce-marketing',
			__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' ),
			__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' ),
			'manage_woocommerce',
			KK_FB_WC_MAIN_PAGE_SLUG,
			function () {
				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					return;
				}

				wp_enqueue_style(
					'kk-fb-admin-plugin-page-style',
					KK_FB_WC_PLUGIN_URL . 'assets/kk-fb-admin-styles.css',
					[],
					KK_FB_WC_PLUGIN_VERSION
				);

				wp_enqueue_script(
					'kk-fb-admin-plugin-page-script',
					KK_FB_WC_PLUGIN_URL . 'assets/kk-fb-admin-plugin-page-script.js',
					[ 'jquery' ],
					KK_FB_WC_PLUGIN_VERSION,
					false
				);

				wp_localize_script(
					'kk-fb-admin-plugin-page-script',
					'kkfbPluginPage',
					[ 'signupLink' => Helper::build_signup_link() ]
				);

				// Settings will be used inside the pages.
				$settings = Helper::get_plugin_settings();

				if ( Helper::is_plugin_configured( $settings ) ) {
					include_once KK_FB_WC_PLUGIN_DIR . 'pages/dashboard.php';
				} else {
					// Show Get Started page.
					include_once KK_FB_WC_PLUGIN_DIR . 'pages/getstarted.php';
				}
			},
			1
		);

		add_action( 'load-' . $hookname, [ $this, 'handle_settings_form_submit' ] );
	}

	/**
	 * Handle submission of Settings form, with security checks.
	 */
	public function handle_settings_form_submit() {
		if ( ! isset( $_SERVER['REQUEST_METHOD'] )
			|| 'POST' !== $_SERVER['REQUEST_METHOD']
			|| ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		check_admin_referer( KK_FB_WC_ACTION_SAVE_SETTINGS );

		$saved_settings = Helper::get_plugin_settings();
		$account_id     = intval( $_POST['account_id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$app_token      = sanitize_text_field( wp_unslash( $_POST['app_token'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( 0 === $account_id || '' === $app_token ) {
			// No (valid) values were entered, we should show onboarding/welcome message.
			set_site_transient( KK_FB_WC_WELCOME_MESSAGE, true, MONTH_IN_SECONDS );

			$this->cancel_scheduled_actions();
		} elseif ( intval( $saved_settings['account_id'] !== $account_id )
			|| $app_token !== $saved_settings['app_token']
		) {
			// If the values changed, we want to redirect them to the WooCommerce Authorization page immediately after save.
			$authorization_url = Helper::build_authorization_url( $account_id, $app_token );
		}

		$facebook_token = sanitize_text_field( wp_unslash( $_POST['facebook_token'] ?? '' ) );

		$saved = Helper::save_plugin_settings(
			$account_id,
			$app_token,
			$facebook_token,
			null,
			$saved_settings
		);

		if ( $saved && ! empty( $authorization_url ) ) {
			wp_safe_redirect( $authorization_url );
			exit;
		}
	}

	/**
	 * Show onboarding (info) message if needed.
	 */
	public function show_onboarding_message() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Welcome message.
		if ( false === get_option( KK_FB_WC_WELCOME_MESSAGE . self::DISMISS_POSTFIX, false )
			&& ! Helper::is_plugin_page()
			&& ! Helper::is_plugin_configured() ) {

			$message = sprintf(
				wp_kses(
					/* translators: %s: Plugin name. %s: A hyperlink. Do not translate. */
					__( '<strong>%1$s plugin is almost ready.</strong> <a href="%2$s">Click here</a> to get started.', 'kliken-ads-pixel-for-meta' ),
					[
						'strong' => [],
						'a'      => [ 'href' => [] ],
					]
				),
				__( 'Kliken: Ads + Pixel for Meta', 'kliken-ads-pixel-for-meta' ),
				esc_url( Helper::get_plugin_page() )
			);

			Message::show_info( $message, 'welcome' );
		}
	}

	/**
	 * Register custom REST API endpoints for WooCommerce.
	 */
	public function register_rest_api_routes() {
		( new REST_Misc_Controller() )->register_routes();
		( new REST_Products_Controller() )->register_routes();
		( new REST_Orders_Controller() )->register_routes();
	}

	/**
	 * Cancel all the actions we scheduled.
	 */
	public function cancel_scheduled_actions() {
		if ( ! function_exists( 'as_unschedule_all_actions' )
			|| false === as_has_scheduled_action( KK_FB_WC_SCHEDULE_ACTION_GET_PIXEL_NAME ) ) {
			return;
		}

		as_unschedule_all_actions(
			KK_FB_WC_SCHEDULE_ACTION_GET_PIXEL_NAME,
			[],
			KK_FB_WC_SLUG
		);
	}

	/**
	 * Action to save account information after signing up.
	 */
	public function save_account_info() {
		if ( ! isset( $_GET['t'] )
			|| ! wp_verify_nonce( sanitize_key( $_GET['t'] ), KK_FB_WC_ACTION_SAVE_ACCOUNT ) ) { // WPCS: input var ok.
			wp_nonce_ays( KK_FB_WC_ACTION_SAVE_ACCOUNT );
		}

		if ( isset( $_GET['maid'], $_GET['appt'] ) ) { // WPCS: input var ok.
			$saved = Helper::save_plugin_settings(
				sanitize_text_field( wp_unslash( $_GET['maid'] ) ), // WPCS: input var ok.
				sanitize_text_field( wp_unslash( $_GET['appt'] ) ) // WPCS: input var ok.
			);

			add_filter(
				'allowed_redirect_hosts',
				function ( $hosts ) {
					$woo_host = wp_parse_url( KK_FB_WC_WOOKLIKEN_BASE_URL, PHP_URL_HOST );

					if ( ! $woo_host ) {
						return $hosts;
					} else {
						return array_merge( $hosts, [ $woo_host ] );
					}
				}
			);

			wp_safe_redirect( KK_FB_WC_WOOKLIKEN_BASE_URL . 'meta/?acct=' . $saved['account_id'] );
			exit;
		}
	}
}
