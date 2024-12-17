<?php
/**
 * REST controller for getting orders, extending of what WooCommerce
 * does not provide at the moment.
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * REST API Orders controller class.
 *
 * @extends \WC_REST_Orders_Controller
 */
class REST_Orders_Controller extends \WC_REST_Orders_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-kliken/v1';

	/**
	 * Register the routes for products.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/orders/modified',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params(),
				],
			]
		);
	}

	/**
	 * Prepare objects query to get modified orders.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = parent::prepare_objects_query( $request );

		// Reset the date query to look up post_modified column instead.
		if ( ! empty( $args['date_query'] ) ) {
			foreach ( $args['date_query'] as $key => $query ) {
				if ( ! isset( $args['date_query'][ $key ]['column'] ) ) {
					continue;
				}

				$args['date_query'][ $key ]['column'] = $request['dates_are_gmt']
					? 'post_modified_gmt'
					: 'post_modified';
			}
		}

		return $args;
	}
}
