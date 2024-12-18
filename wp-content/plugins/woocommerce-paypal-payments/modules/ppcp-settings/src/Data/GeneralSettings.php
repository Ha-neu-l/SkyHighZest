<?php
/**
 * GeneralSettings class
 *
 * @package WooCommerce\PayPalCommerce\Settings\Data
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Settings\Data;

use RuntimeException;

/**
 * This class serves as a container for managing the general plugin settings,
 * such as the connection credentials.
 */
class GeneralSettings extends AbstractDataModel {

	/**
	 * Option key where profile details are stored.
	 *
	 * @var string
	 */
	protected const OPTION_KEY = 'woocommerce-ppcp-data-general-settings';

	/**
	 * Get default values for the model.
	 *
	 * @return array
	 */
	protected function get_defaults() : array {
		return array(
			'is_sandbox'             => false,
			'live_client_id'         => '',
			'live_client_secret'     => '',
			'live_merchant_id'       => '',
			'live_merchant_email'    => '',
			'sandbox_client_id'      => '',
			'sandbox_client_secret'  => '',
			'sandbox_merchant_id'    => '',
			'sandbox_merchant_email' => '',
		);
	}

	// -----

	/**
	 * Gets the 'is_sandbox' flag.
	 */
	public function is_sandbox() : bool {
		return (bool) $this->data['is_sandbox'];
	}

	/**
	 * Sets the 'is_sandbox' flag.
	 *
	 * @param bool $value The value to set.
	 */
	public function set_is_sandbox( bool $value ) : void {
		$this->data['is_sandbox'] = $value;
	}

	/**
	 * Gets the live client ID.
	 */
	public function live_client_id() : string {
		return $this->data['live_client_id'];
	}

	/**
	 * Sets the live client ID.
	 *
	 * @param string $value The value to set.
	 */
	public function set_live_client_id( string $value ) : void {
		$this->data['live_client_id'] = sanitize_text_field( $value );
	}

	/**
	 * Gets the live client secret.
	 */
	public function live_client_secret() : string {
		return $this->data['live_client_secret'];
	}

	/**
	 * Sets the live client secret.
	 *
	 * @param string $value The value to set.
	 */
	public function set_live_client_secret( string $value ) : void {
		$this->data['live_client_secret'] = sanitize_text_field( $value );
	}

	/**
	 * Gets the live merchant ID.
	 */
	public function live_merchant_id() : string {
		return $this->data['live_merchant_id'];
	}

	/**
	 * Sets the live merchant ID.
	 *
	 * @param string $value The value to set.
	 */
	public function set_live_merchant_id( string $value ) : void {
		$this->data['live_merchant_id'] = sanitize_text_field( $value );
	}

	/**
	 * Gets the live merchant email.
	 */
	public function live_merchant_email() : string {
		return $this->data['live_merchant_email'];
	}

	/**
	 * Sets the live merchant email.
	 *
	 * @param string $value The value to set.
	 */
	public function set_live_merchant_email( string $value ) : void {
		$this->data['live_merchant_email'] = sanitize_email( $value );
	}

	/**
	 * Gets the sandbox client ID.
	 */
	public function sandbox_client_id() : string {
		return $this->data['sandbox_client_id'];
	}

	/**
	 * Sets the sandbox client ID.
	 *
	 * @param string $value The value to set.
	 */
	public function set_sandbox_client_id( string $value ) : void {
		$this->data['sandbox_client_id'] = sanitize_text_field( $value );
	}

	/**
	 * Gets the sandbox client secret.
	 */
	public function sandbox_client_secret() : string {
		return $this->data['sandbox_client_secret'];
	}

	/**
	 * Sets the sandbox client secret.
	 *
	 * @param string $value The value to set.
	 */
	public function set_sandbox_client_secret( string $value ) : void {
		$this->data['sandbox_client_secret'] = sanitize_text_field( $value );
	}

	/**
	 * Gets the sandbox merchant ID.
	 */
	public function sandbox_merchant_id() : string {
		return $this->data['sandbox_merchant_id'];
	}

	/**
	 * Sets the sandbox merchant ID.
	 *
	 * @param string $value The value to set.
	 */
	public function set_sandbox_merchant_id( string $value ) : void {
		$this->data['sandbox_merchant_id'] = sanitize_text_field( $value );
	}

	/**
	 * Gets the sandbox merchant email.
	 */
	public function sandbox_merchant_email() : string {
		return $this->data['sandbox_merchant_email'];
	}

	/**
	 * Sets the sandbox merchant email.
	 *
	 * @param string $value The value to set.
	 */
	public function set_sandbox_merchant_email( string $value ) : void {
		$this->data['sandbox_merchant_email'] = sanitize_email( $value );
	}
}
