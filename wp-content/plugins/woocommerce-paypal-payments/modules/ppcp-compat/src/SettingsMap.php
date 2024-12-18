<?php
/**
 * A map of new to old settings.
 *
 * @package WooCommerce\PayPalCommerce\Compat
 */

declare(strict_types=1);

namespace WooCommerce\PayPalCommerce\Compat;

use WooCommerce\PayPalCommerce\Settings\Data\AbstractDataModel;

/**
 * A map of new to old settings.
 *
 * @psalm-type newSettingsKey = string
 * @psalm-type oldSettingsKey = string
 */
class SettingsMap {
	/**
	 * The new settings model.
	 *
	 * @var AbstractDataModel
	 */
	private AbstractDataModel $model;

	/**
	 * The map of the new setting key to the old setting keys.
	 *
	 * @var array<newSettingsKey, oldSettingsKey>
	 */
	private array $map;

	/**
	 * The constructor.
	 *
	 * @param AbstractDataModel                     $model The new settings model.
	 * @param array<newSettingsKey, oldSettingsKey> $map The map of the new setting key to the old setting keys.
	 */
	public function __construct( AbstractDataModel $model, array $map ) {
		$this->model = $model;
		$this->map   = $map;
	}

	/**
	 * The model.
	 *
	 * @return AbstractDataModel
	 */
	public function get_model(): AbstractDataModel {
		return $this->model;
	}

	/**
	 * The map of the new setting key to the old setting keys.
	 *
	 * @return array<newSettingsKey, oldSettingsKey>
	 */
	public function get_map(): array {
		return $this->map;
	}
}
