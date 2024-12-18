<?php
/**
 * REST endpoint to manage the onboarding module.
 *
 * @package WooCommerce\PayPalCommerce\Settings\Endpoint
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Settings\Endpoint;

use WP_REST_Server;
use WP_REST_Response;
use WP_REST_Request;
use WooCommerce\PayPalCommerce\Settings\Service\ConnectionUrlGenerator;

/**
 * REST controller that generates merchant login URLs.
 */
class LoginLinkRestEndpoint extends RestEndpoint {
	/**
	 * The base path for this REST controller.
	 *
	 * @var string
	 */
	protected $rest_base = 'login_link';

	/**
	 * Link generator list, with environment name as array key.
	 *
	 * @var ConnectionUrlGenerator[]
	 */
	protected array $url_generators;

	/**
	 * Constructor.
	 *
	 * @param ConnectionUrlGenerator[] $url_generators Array of environment-specific URL generators.
	 */
	public function __construct( array $url_generators ) {
		$this->url_generators = $url_generators;
	}

	/**
	 * Configure REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'get_login_url' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'environment' => array(
							'required' => true,
							'type'     => 'string',
						),
						'products'    => array(
							'required'          => true,
							'type'              => 'array',
							'items'             => array(
								'type' => 'string',
							),
							'sanitize_callback' => function ( $products ) {
								return array_map( 'sanitize_text_field', $products );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Returns the full login URL for the requested environment and products.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The login URL or an error response.
	 */
	public function get_login_url( WP_REST_Request $request ) : WP_REST_Response {
		$environment = $request->get_param( 'environment' );
		$products    = $request->get_param( 'products' );

		if ( ! isset( $this->url_generators[ $environment ] ) ) {
			return new WP_REST_Response(
				array( 'error' => 'Invalid environment specified.' ),
				400
			);
		}

		$url_generator = $this->url_generators[ $environment ];

		try {
			$url = $url_generator->generate( $products );

			return $this->return_success( $url );
		} catch ( \Exception $e ) {
			return $this->return_error( $e->getMessage() );
		}
	}
}
