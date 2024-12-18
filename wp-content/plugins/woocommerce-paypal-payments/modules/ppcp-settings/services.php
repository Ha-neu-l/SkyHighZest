<?php
/**
 * The Settings module services.
 *
 * @package WooCommerce\PayPalCommerce\Settings
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Settings;

use WooCommerce\PayPalCommerce\ApiClient\Helper\Cache;
use WooCommerce\PayPalCommerce\Settings\Data\CommonSettings;
use WooCommerce\PayPalCommerce\Settings\Data\GeneralSettings;
use WooCommerce\PayPalCommerce\Settings\Data\OnboardingProfile;
use WooCommerce\PayPalCommerce\Settings\Endpoint\CommonRestEndpoint;
use WooCommerce\PayPalCommerce\Settings\Endpoint\ConnectManualRestEndpoint;
use WooCommerce\PayPalCommerce\Settings\Endpoint\LoginLinkRestEndpoint;
use WooCommerce\PayPalCommerce\Settings\Endpoint\OnboardingRestEndpoint;
use WooCommerce\PayPalCommerce\Settings\Endpoint\SwitchSettingsUiEndpoint;
use WooCommerce\PayPalCommerce\Settings\Service\ConnectionUrlGenerator;
use WooCommerce\PayPalCommerce\Vendor\Psr\Container\ContainerInterface;

return array(
	'settings.url'                                => static function ( ContainerInterface $container ) : string {
		/**
		 * The path cannot be false.
		 *
		 * @psalm-suppress PossiblyFalseArgument
		 */
		return plugins_url(
			'/modules/ppcp-settings/',
			dirname( realpath( __FILE__ ), 3 ) . '/woocommerce-paypal-payments.php'
		);
	},
	'settings.data.onboarding'                    => static function ( ContainerInterface $container ) : OnboardingProfile {
		$can_use_casual_selling = $container->get( 'settings.casual-selling.eligible' );
		$can_use_vaulting       = $container->has( 'save-payment-methods.eligible' ) && $container->get( 'save-payment-methods.eligible' );
		$can_use_card_payments  = $container->has( 'card-fields.eligible' ) && $container->get( 'card-fields.eligible' );

		// Card payments are disabled for this plugin when WooPayments is active.
		// TODO: Move this condition to the card-fields.eligible service?
		if ( class_exists( '\WC_Payments' ) ) {
			$can_use_card_payments = false;
		}

		return new OnboardingProfile(
			$can_use_casual_selling,
			$can_use_vaulting,
			$can_use_card_payments
		);
	},
	'settings.data.general'                       => static function ( ContainerInterface $container ) : GeneralSettings {
		return new GeneralSettings();
	},
	'settings.data.common'                        => static function ( ContainerInterface $container ) : CommonSettings {
		return new CommonSettings();
	},
	'settings.rest.onboarding'                    => static function ( ContainerInterface $container ) : OnboardingRestEndpoint {
		return new OnboardingRestEndpoint( $container->get( 'settings.data.onboarding' ) );
	},
	'settings.rest.common'                        => static function ( ContainerInterface $container ) : CommonRestEndpoint {
		return new CommonRestEndpoint( $container->get( 'settings.data.common' ) );
	},
	'settings.rest.connect_manual'                => static function ( ContainerInterface $container ) : ConnectManualRestEndpoint {
		return new ConnectManualRestEndpoint(
			$container->get( 'api.paypal-host-production' ),
			$container->get( 'api.paypal-host-sandbox' ),
			$container->get( 'woocommerce.logger.woocommerce' ),
			$container->get( 'settings.data.general' )
		);
	},
	'settings.rest.login_link'                    => static function ( ContainerInterface $container ) : LoginLinkRestEndpoint {
		return new LoginLinkRestEndpoint(
			$container->get( 'settings.service.connection-url-generators' ),
		);
	},
	'settings.casual-selling.supported-countries' => static function ( ContainerInterface $container ) : array {
		return array(
			'AR',
			'AU',
			'AT',
			'BE',
			'BR',
			'CA',
			'CL',
			'CN',
			'CY',
			'CZ',
			'DK',
			'EE',
			'FI',
			'FR',
			'GR',
			'HU',
			'ID',
			'IE',
			'IT',
			'JP',
			'LV',
			'LI',
			'LU',
			'MY',
			'MT',
			'NL',
			'NZ',
			'NO',
			'PH',
			'PL',
			'PT',
			'RO',
			'RU',
			'SM',
			'SA',
			'SG',
			'SK',
			'SI',
			'ZA',
			'KR',
			'ES',
			'SE',
			'TW',
			'GB',
			'US',
			'VN',
		);
	},
	'settings.casual-selling.eligible'            => static function ( ContainerInterface $container ) : bool {
		$country            = $container->get( 'api.shop.country' );
		$eligible_countries = $container->get( 'settings.casual-selling.supported-countries' );

		return in_array( $country, $eligible_countries, true );
	},
	'settings.service.signup-link-cache'          => static function ( ContainerInterface $container ) : Cache {
		return new Cache( 'ppcp-paypal-signup-link' );
	},
	'settings.service.connection-url-generators'  => static function ( ContainerInterface $container ) : array {
		// Define available environments.
		$environments = array(
			'production' => array(
				'partner_referrals' => $container->get( 'api.endpoint.partner-referrals-production' ),
			),
			'sandbox'    => array(
				'partner_referrals' => $container->get( 'api.endpoint.partner-referrals-sandbox' ),
			),
		);

		$generators = array();

		// Instantiate URL generators for each environment.
		foreach ( $environments as $environment => $config ) {
			$generators[ $environment ] = new ConnectionUrlGenerator(
				$config['partner_referrals'],
				$container->get( 'api.repository.partner-referrals-data' ),
				$container->get( 'settings.service.signup-link-cache' ),
				$environment,
				$container->get( 'woocommerce.logger.woocommerce' )
			);
		}

		return $generators;
	},
	'settings.switch-ui.endpoint'                 => static function ( ContainerInterface $container ) : SwitchSettingsUiEndpoint {
		return new SwitchSettingsUiEndpoint(
			$container->get( 'woocommerce.logger.woocommerce' ),
			$container->get( 'button.request-data' ),
		);
	},
);
