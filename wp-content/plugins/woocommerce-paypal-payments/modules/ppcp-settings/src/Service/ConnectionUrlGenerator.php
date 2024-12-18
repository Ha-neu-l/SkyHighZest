<?php
/**
 * Generator service to build URLs to sign in to a PayPal account.
 *
 * @package WooCommerce\PayPalCommerce\Settings\Service
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Settings\Service;

use Exception;
use Psr\Log\LoggerInterface;
use WooCommerce\PayPalCommerce\ApiClient\Endpoint\PartnerReferrals;
use WooCommerce\PayPalCommerce\ApiClient\Helper\Cache;
use WooCommerce\PayPalCommerce\ApiClient\Repository\PartnerReferralsData;
use WooCommerce\PayPalCommerce\Onboarding\Helper\OnboardingUrl;
use WooCommerce\WooCommerce\Logging\Logger\NullLogger;

/**
 * Generator that builds the ISU connection URL.
 */
class ConnectionUrlGenerator {
	/**
	 * The partner referrals endpoint.
	 *
	 * @var PartnerReferrals
	 */
	protected PartnerReferrals $partner_referrals;

	/**
	 * The default partner referrals data.
	 *
	 * @var PartnerReferralsData
	 */
	protected PartnerReferralsData $referrals_data;

	/**
	 * The cache
	 *
	 * @var Cache
	 */
	protected Cache $cache;

	/**
	 * Which environment is used for the connection URL.
	 *
	 * @var string
	 */
	protected string $environment = '';

	/**
	 * The logger
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * Constructor for the ConnectionUrlGenerator class.
	 *
	 * Initializes the cache and logger properties of the class.
	 *
	 * @param PartnerReferrals     $partner_referrals PartnerReferrals for URL generation.
	 * @param PartnerReferralsData $referrals_data    Default partner referrals data.
	 * @param Cache                $cache             The cache object used for storing and
	 *                                                retrieving data.
	 * @param string               $environment       Environment that is used to generate the URL.
	 *                                                ['production'|'sandbox'].
	 * @param ?LoggerInterface     $logger            The logger object for logging messages.
	 */
	public function __construct(
		PartnerReferrals $partner_referrals,
		PartnerReferralsData $referrals_data,
		Cache $cache,
		string $environment,
		?LoggerInterface $logger = null
	) {
		$this->partner_referrals = $partner_referrals;
		$this->referrals_data    = $referrals_data;
		$this->cache             = $cache;
		$this->environment       = $environment;
		$this->logger            = $logger ?: new NullLogger();
	}

	/**
	 * Returns the environment for which the URL is being generated.
	 *
	 * @return string
	 */
	public function environment() : string {
		return $this->environment;
	}

	/**
	 * Generates a PayPal onboarding URL for merchant sign-up.
	 *
	 * This function creates a URL for merchants to sign up for PayPal services.
	 * It handles caching of the URL, generation of new URLs when necessary,
	 * and works for both production and sandbox environments.
	 *
	 * @param array $products An array of product identifiers to include in the sign-up process.
	 *                        These determine the PayPal onboarding experience.
	 *
	 * @return string The generated PayPal onboarding URL.
	 */
	public function generate( array $products = array() ) : string {
		$cache_key      = $this->cache_key( $products );
		$user_id        = get_current_user_id();
		$onboarding_url = new OnboardingUrl( $this->cache, $cache_key, $user_id );
		$cached_url     = $this->try_get_from_cache( $onboarding_url, $cache_key );

		if ( $cached_url ) {
			$this->logger->info( 'Using cached onboarding URL for: ' . $cache_key );

			return $cached_url;
		}

		$this->logger->info( 'Generating onboarding URL for: ' . $cache_key );

		$url = $this->generate_new_url( $products, $onboarding_url, $cache_key );

		if ( $url ) {
			$this->persist_url( $onboarding_url, $url );
		}

		return $url;
	}

	/**
	 * Generates a cache key from the environment and sorted product array.
	 *
	 * @param array $products Product identifiers that are part of the cache key.
	 *
	 * @return string The cache key, defining the product list and environment.
	 */
	protected function cache_key( array $products = array() ) : string {
		// Sort products alphabetically, to improve cache implementation.
		sort( $products );

		return $this->environment() . '-' . implode( '-', $products );
	}

	/**
	 * Attempts to load the URL from cache.
	 *
	 * @param OnboardingUrl $onboarding_url The OnboardingUrl object.
	 * @param string        $cache_key      The cache key.
	 *
	 * @return string The cached URL, or an empty string if no URL is found.
	 */
	protected function try_get_from_cache( OnboardingUrl $onboarding_url, string $cache_key ) : string {
		try {
			if ( $onboarding_url->load() ) {
				$this->logger->debug( 'Loaded onboarding URL from cache: ' . $cache_key );

				return $onboarding_url->get();
			}
		} catch ( Exception $e ) {
			// No problem, return an empty string to generate a new URL.
			$this->logger->warning( 'Failed to load onboarding URL from cache: ' . $cache_key );
		}

		return '';
	}

	/**
	 * Generates a new URL.
	 *
	 * @param array         $products       The products array.
	 * @param OnboardingUrl $onboarding_url The OnboardingUrl object.
	 * @param string        $cache_key      The cache key.
	 *
	 * @return string The generated URL or an empty string on failure.
	 */
	protected function generate_new_url( array $products, OnboardingUrl $onboarding_url, string $cache_key ) : string {
		$query_args = array( 'displayMode' => 'minibrowser' );
		$onboarding_url->init();

		try {
			$onboarding_token = $onboarding_url->token();
		} catch ( Exception $e ) {
			$this->logger->warning( 'Could not generate an onboarding token for: ' . $cache_key );

			return '';
		}

		$data = $this->prepare_referral_data( $products, $onboarding_token );

		try {
			$url = $this->partner_referrals->signup_link( $data );
		} catch ( Exception $e ) {
			$this->logger->warning( 'Could not generate an onboarding URL for: ' . $cache_key );

			return '';
		}

		return add_query_arg( $query_args, $url );
	}

	/**
	 * Prepares the referral data.
	 *
	 * @param array  $products         The products array.
	 * @param string $onboarding_token The onboarding token.
	 *
	 * @return array The prepared referral data.
	 */
	protected function prepare_referral_data( array $products, string $onboarding_token ) : array {
		$data = $this->referrals_data
			->with_products( $products )
			->data();

		return $this->referrals_data->append_onboarding_token( $data, $onboarding_token );
	}

	/**
	 * Persists the generated URL.
	 *
	 * @param OnboardingUrl $onboarding_url The OnboardingUrl object.
	 * @param string        $url            The URL to persist.
	 */
	protected function persist_url( OnboardingUrl $onboarding_url, string $url ) : void {
		$onboarding_url->set( $url );
		$onboarding_url->persist();
	}
}
