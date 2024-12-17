<?php
/**
 * REST controller for miscellaneous endpoints.
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * REST API Misc controller class.
 *
 * @extends WC_REST_CRUD_Controller
 */
class REST_Misc_Controller extends \WC_REST_CRUD_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-kliken/v1';

	/**
	 * Register the routes we need.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/google-token',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_google_token' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_woocommerce' );
					},
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/facebook-token',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_facebook_token' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_woocommerce' );
					},
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/kk-account',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_account_info' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_woocommerce' );
					},
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/shipping/wc-services',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_wc_services_shipping_methods' ],
					'permission_callback' => [ $this, 'get_shipping_methods_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Save Facebook Verification Token to database so we can later on display it as a header's meta.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save_facebook_token( $request ) {
		// If we know more about Facebook's token specifications, we can have more strict rules
		// For now, just sanitize the token as a text string (no tags, no simple injections).
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		if ( ! $token ) {
			return new \WP_Error( 'rest_bad_request', __( 'Invalid Data.', 'kliken-ads-pixel-for-meta' ), [ 'status' => 400 ] );
		}

		// Save the token to database.
		Helper::save_plugin_settings( null, null, $token );

		return new \WP_REST_Response( null, 201 );
	}

	/**
	 * Save Google Verification Token to database so we can later on display it as a header's meta.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save_google_token( $request ) {
		// If we know more about Google's token specifications, we can have more strict rules
		// For now, just sanitize the token as a text string (no tags, no simple injections).
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		if ( ! $token ) {
			return new \WP_Error( 'rest_bad_request', __( 'Invalid Data.', 'kliken-ads-pixel-for-meta' ), [ 'status' => 400 ] );
		}

		// Save the token to database.
		Helper::save_plugin_settings( null, null, null, $token );

		return new \WP_REST_Response( null, 201 );
	}

	/**
	 * Get shipping services associated with a WooCommerce Services Carrier shipping method.
	 * Because for some reason, WooCommerce does not return that with their API endpoints.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_wc_services_shipping_methods( $request ) {
		$id           = (int) $request['id'];
		$carrier_name = $request['carrier'];

		// Get the option from database.
		$data = get_option( "woocommerce_wc_services_{$carrier_name}_{$id}_form_settings" );

		if ( ! $data || empty( $data ) || empty( $data->services ) ) {
			return new \WP_Error( 'no_wc_services', __( 'Invalid Data.', 'kliken-ads-pixel-for-meta' ), [ 'status' => 404 ] );
		}

		$carrier_services = [];

		foreach ( $data->services as $key => $service ) {
			if ( true !== $service['enabled'] ) {
				continue;
			}

			array_push(
				$carrier_services,
				[
					'service_name'    => $service['id'],
					'adjustment'      => $service['adjustment'],
					'adjustment_type' => $service['adjustment_type'],
				]
			);
		}

		$response = rest_ensure_response(
			[
				'carrier_name'     => $data->title,
				'carrier_services' => $carrier_services,
			]
		);

		return $response;
	}

	/**
	 * Check whether a given request has permission to view shipping methods.
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|boolean
	 */
	public function get_shipping_methods_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'shipping_methods', 'read' ) ) {
			return new \WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'kliken-ads-pixel-for-meta' ), [ 'status' => rest_authorization_required_code() ] );
		}
		return true;
	}

	/**
	 * Save Kliken account info.
	 *
	 * @param mixed $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save_account_info( $request ) {
		$account_id = intval( $request->get_param( 'maid' ) );

		if ( 0 >= $account_id ) {
			return new \WP_Error( 'rest_bad_request', __( 'Invalid Data.', 'kliken-ads-pixel-for-meta' ), [ 'status' => 400 ] );
		}

		$token = sanitize_text_field( $request->get_param( 'appt' ) );

		if ( ! $token ) {
			return new \WP_Error( 'rest_bad_request', __( 'Invalid Data.', 'kliken-ads-pixel-for-meta' ), [ 'status' => 400 ] );
		}

		Helper::save_plugin_settings( $account_id, $token );

		return new \WP_REST_Response( null, 201 );
	}
}
