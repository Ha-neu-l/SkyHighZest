<?php
/**
 * A helper for mapping the new/old settings.
 *
 * @package WooCommerce\PayPalCommerce\Compat
 */

declare(strict_types=1);

namespace WooCommerce\PayPalCommerce\Compat;

/**
 * A helper for mapping the new/old settings.
 */
class SettingsMapHelper {

	/**
	 * A list of mapped settings.
	 *
	 * @var SettingsMap[]
	 */
	protected array $settings_map;

	/**
	 * Constructor.
	 *
	 * @param SettingsMap[] $settings_map A list of mapped settings.
	 */
	public function __construct( array $settings_map ) {
		$this->settings_map = $settings_map;
	}

	/**
	 * Retrieves the mapped value from the new settings.
	 *
	 * @param string $key The key.
	 * @return ?mixed the mapped value or Null if it doesn't exist.
	 */
	public function mapped_value( string $key ) {
		if ( ! $this->has_mapped_key( $key ) ) {
			return null;
		}

		foreach ( $this->settings_map as $settings_map ) {
			$mapped_key   = array_search( $key, $settings_map->get_map(), true );
			$new_settings = $settings_map->get_model()->to_array();
			if ( ! empty( $new_settings[ $mapped_key ] ) ) {
				return $new_settings[ $mapped_key ];
			}
		}

		return null;
	}

	/**
	 * Checks if the given key exists in the new settings.
	 *
	 * @param string $key The key.
	 * @return bool true if the given key exists in the new settings, otherwise false.
	 */
	public function has_mapped_key( string $key ) : bool {
		foreach ( $this->settings_map as $settings_map ) {
			if ( in_array( $key, $settings_map->get_map(), true ) ) {
				return true;
			}
		}

		return false;
	}
}
