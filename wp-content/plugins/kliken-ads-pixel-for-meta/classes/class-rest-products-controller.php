<?php
/**
 * REST controller for getting products, extending of what WooCommerce
 * does not provide at the moment.
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * REST API Products controller class.
 *
 * @extends \WC_REST_Products_Controller
 */
class REST_Products_Controller extends \WC_REST_Products_Controller {
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
			'/products/modified',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/deleted',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_deleted_products' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Get deleted/trashed products.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_deleted_products( $request ) {
		$page     = intval( $request['page'] );
		$per_page = intval( $request['per_page'] );
		$use_gmt  = $request['dates_are_gmt'];

		$query_args = [
			'fields'              => 'ids', // We only need post ids for this.
			'post_type'           => $this->post_type,
			'post_status'         => 'trash',
			'ignore_sticky_posts' => true,
			'paged'               => 0 === $page ? 1 : $page,
			'posts_per_page'      => 0 === $per_page ? 100 : $per_page,
			'orderby'             => 'date ID',
			'order'               => 'ASC',
		];

		$query_args['date_query'] = [];

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$query_args['date_query'][] = [
				'column' => $use_gmt ? 'post_modified_gmt' : 'post_modified',
				'before' => $request['before'],
			];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$query_args['date_query'][] = [
				'column' => $use_gmt ? 'post_modified_gmt' : 'post_modified',
				'after'  => $request['after'],
			];
		}

		$query  = new \WP_Query();
		$result = $query->query( $query_args );

		$total_posts = $query->found_posts;
		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );
			$count_query = new \WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$response = rest_ensure_response( $result );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) ceil( $total_posts / (int) $query_args['posts_per_page'] ) );

		return $response;
	}

	/**
	 * Prepare objects query to get modified products.
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
