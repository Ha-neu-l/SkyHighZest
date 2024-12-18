<?php
/**
 * REST controller for connection via manual credentials input.
 *
 * @package WooCommerce\PayPalCommerce\Settings\Endpoint
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Settings\Endpoint;

use Exception;
use stdClass;
use RuntimeException;
use Psr\Log\LoggerInterface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WooCommerce\PayPalCommerce\ApiClient\Authentication\PayPalBearer;
use WooCommerce\PayPalCommerce\ApiClient\Endpoint\Orders;
use WooCommerce\PayPalCommerce\ApiClient\Helper\InMemoryCache;
use WooCommerce\PayPalCommerce\Settings\Data\GeneralSettings;

/**
 * REST controller for connection via manual credentials input.
 */
class ConnectManualRestEndpoint extends RestEndpoint {

	/**
	 * The API host for the live mode.
	 *
	 * @var string
	 */
	private string $live_host;

	/**
	 * The API host for the sandbox mode.
	 *
	 * @var string
	 */
	private string $sandbox_host;

	/**
	 * The logger.
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * The base path for this REST controller.
	 *
	 * @var string
	 */
	protected $rest_base = 'connect_manual';

	/**
	 * Settings instance.
	 *
	 * @var GeneralSettings
	 */
	private $settings = null;

	/**
	 * Field mapping for request.
	 *
	 * @var array
	 */
	private array $field_map = array(
		'client_id'     => array(
			'js_name'  => 'clientId',
			'sanitize' => 'sanitize_text_field',
		),
		'client_secret' => array(
			'js_name'  => 'clientSecret',
			'sanitize' => 'sanitize_text_field',
		),
		'use_sandbox'   => array(
			'js_name'  => 'useSandbox',
			'sanitize' => 'to_boolean',
		),
	);

	/**
	 * ConnectManualRestEndpoint constructor.
	 *
	 * @param string          $live_host    The API host for the live mode.
	 * @param string          $sandbox_host The API host for the sandbox mode.
	 * @param LoggerInterface $logger       The logger.
	 * @param GeneralSettings $settings     Settings instance.
	 */
	public function __construct(
		string $live_host,
		string $sandbox_host,
		LoggerInterface $logger,
		GeneralSettings $settings
	) {
		$this->live_host    = $live_host;
		$this->sandbox_host = $sandbox_host;
		$this->logger       = $logger;
		$this->settings     = $settings;
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
					'callback'            => array( $this, 'connect_manual' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Retrieves merchantId and email.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function connect_manual( WP_REST_Request $request ) : WP_REST_Response {
		$data = $this->sanitize_for_wordpress(
			$request->get_params(),
			$this->field_map
		);

		$client_id     = $data['client_id'] ?? '';
		$client_secret = $data['client_secret'] ?? '';
		$use_sandbox   = (bool) ( $data['use_sandbox'] ?? false );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return $this->return_error( 'No client ID or secret provided.' );
		}

		try {
			$payee = $this->request_payee( $client_id, $client_secret, $use_sandbox );
		} catch ( Exception $exception ) {
			return $this->return_error( $exception->getMessage() );
		}

		if ( $use_sandbox ) {
			$this->settings->set_is_sandbox( true );
			$this->settings->set_sandbox_client_id( $client_id );
			$this->settings->set_sandbox_client_secret( $client_secret );
			$this->settings->set_sandbox_merchant_id( $payee->merchant_id );
			$this->settings->set_sandbox_merchant_email( $payee->email_address );
		} else {
			$this->settings->set_is_sandbox( false );
			$this->settings->set_live_client_id( $client_id );
			$this->settings->set_live_client_secret( $client_secret );
			$this->settings->set_live_merchant_id( $payee->merchant_id );
			$this->settings->set_live_merchant_email( $payee->email_address );
		}
		$this->settings->save();

		return $this->return_success(
			array(
				'merchantId' => $payee->merchant_id,
				'email'      => $payee->email_address,
			)
		);
	}

	/**
	 * Retrieves the payee object with the merchant data
	 * by creating a minimal PayPal order.
	 *
	 * @throws Exception When failed to retrieve payee.
	 *
	 * phpcs:disable Squiz.Commenting
	 * phpcs:disable Generic.Commenting
	 *
	 * @param string $client_secret The client secret.
	 * @param bool   $use_sandbox   Whether to use the sandbox mode.
	 * @param string $client_id     The client ID.
	 *
	 * @return stdClass The payee object.
	 */
	private function request_payee(
		string $client_id,
		string $client_secret,
		bool $use_sandbox
	) : stdClass {

		$host = $use_sandbox ? $this->sandbox_host : $this->live_host;

		$bearer = new PayPalBearer(
			new InMemoryCache(),
			$host,
			$client_id,
			$client_secret,
			$this->logger,
			null
		);

		$orders = new Orders(
			$host,
			$bearer,
			$this->logger
		);

		$request_body = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => array(
				array(
					'amount' => array(
						'currency_code' => 'USD',
						'value'         => 1.0,
					),
				),
			),
		);

		$response = $orders->create( $request_body );
		$body     = json_decode( $response['body'] );

		$order_id = $body->id;

		$order_response = $orders->order( $order_id );
		$order_body     = json_decode( $order_response['body'] );

		$pu    = $order_body->purchase_units[0];
		$payee = $pu->payee;
		if ( ! is_object( $payee ) ) {
			throw new RuntimeException( 'Payee not found.' );
		}
		if ( ! isset( $payee->merchant_id ) || ! isset( $payee->email_address ) ) {
			throw new RuntimeException( 'Payee info not found.' );
		}

		return $payee;
	}
}
