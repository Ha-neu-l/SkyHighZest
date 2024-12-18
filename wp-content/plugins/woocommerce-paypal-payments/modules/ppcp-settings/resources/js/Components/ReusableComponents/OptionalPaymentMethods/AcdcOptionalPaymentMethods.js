import BadgeBox, { BADGE_BOX_TITLE_BIG } from '../BadgeBox';
import { __, sprintf } from '@wordpress/i18n';
import Separator from '../Separator';
import generatePriceText from '../../../utils/badgeBoxUtils';
import { countryPriceInfo } from '../../../utils/countryPriceInfo';

const AcdcOptionalPaymentMethods = ( {
	isFastlane,
	isPayLater,
	storeCountry,
	storeCurrency,
} ) => {
	if ( isFastlane && isPayLater && storeCountry === 'us' ) {
		return (
			<div className="ppcp-r-optional-payment-methods__wrapper">
				<BadgeBox
					title={ __(
						'Custom Card Fields',
						'woocommerce-paypal-payments'
					) }
					imageBadge={ [
						'icon-button-visa.svg',
						'icon-button-mastercard.svg',
						'icon-button-amex.svg',
						'icon-button-discover.svg',
					] }
					textBadge={ generatePriceText(
						'ccf',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Style the credit card fields to match your own style. Includes advanced processing with risk management, 3D Secure, fraud protection options, and chargeback protection. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
				<Separator className="ppcp-r-optional-payment-methods__separator" />
				<BadgeBox
					title={ __(
						'Digital Wallets',
						'woocommerce-paypal-payments'
					) }
					imageBadge={ [
						'icon-button-apple-pay.svg',
						'icon-button-google-pay.svg',
					] }
					textBadge={ generatePriceText(
						'dw',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Accept Apple Pay on eligible devices and Google Pay through mobile and web. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
				<Separator className="ppcp-r-optional-payment-methods__separator" />
				<BadgeBox
					title={ __(
						'Alternative Payment Methods',
						'woocommerce-paypal-payments'
					) }
					imageBadge={ [
						'icon-button-sepa.svg',
						'icon-button-ideal.svg',
						'icon-button-blik.svg',
						'icon-button-bancontact.svg',
					] }
					textBadge={ generatePriceText(
						'apm',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Seamless payments for customers across the globe using their preferred payment methods. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
				<Separator className="ppcp-r-optional-payment-methods__separator" />
				<BadgeBox
					title={ __( '', 'woocommerce-paypal-payments' ) }
					imageBadge={ [ 'icon-payment-method-fastlane-small.svg' ] }
					textBadge={ generatePriceText(
						'fastlane',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Speed up guest checkout with Fatslane. Link a customer\'s email address to their payment details. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
			</div>
		);
	}

	if ( isPayLater && storeCountry === 'uk' ) {
		return (
			<div className="ppcp-r-optional-payment-methods__wrapper">
				<BadgeBox
					title={ __(
						'Custom Card Fields',
						'woocommerce-paypal-payments'
					) }
					imageBadge={ [
						'icon-button-visa.svg',
						'icon-button-mastercard.svg',
						'icon-button-amex.svg',
						'icon-button-discover.svg',
					] }
					textBadge={ generatePriceText(
						'ccf',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Style the credit card fields to match your own style. Includes advanced processing with risk management, 3D Secure, fraud protection options, and chargeback protection. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
				<Separator className="ppcp-r-optional-payment-methods__separator" />
				<BadgeBox
					title={ __(
						'Digital Wallets',
						'woocommerce-paypal-payments'
					) }
					imageBadge={ [
						'icon-button-apple-pay.svg',
						'icon-button-google-pay.svg',
					] }
					textBadge={ generatePriceText(
						'dw',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Accept Apple Pay on eligible devices and Google Pay through mobile and web. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
				<Separator className="ppcp-r-optional-payment-methods__separator" />
				<BadgeBox
					title={ __(
						'Alternative Payment Methods',
						'woocommerce-paypal-payments'
					) }
					imageBadge={ [
						'icon-button-sepa.svg',
						'icon-button-ideal.svg',
						'icon-button-blik.svg',
						'icon-button-bancontact.svg',
					] }
					textBadge={ generatePriceText(
						'apm',
						countryPriceInfo[ storeCountry ],
						storeCurrency
					) }
					description={ sprintf(
						// translators: %s: Link to PayPal business fees guide
						__(
							'Seamless payments for customers across the globe using their preferred payment methods. <a target="_blank" href="%s">Learn more</a>',
							'woocommerce-paypal-payments'
						),
						'https://www.paypal.com/us/business/paypal-business-fees'
					) }
				/>
			</div>
		);
	}

	return (
		<div className="ppcp-r-optional-payment-methods__wrapper">
			<BadgeBox
				title={ __(
					'Custom Card Fields',
					'woocommerce-paypal-payments'
				) }
				imageBadge={ [
					'icon-button-visa.svg',
					'icon-button-mastercard.svg',
					'icon-button-amex.svg',
					'icon-button-discover.svg',
				] }
				textBadge={ generatePriceText(
					'ccf',
					countryPriceInfo[ storeCountry ],
					storeCurrency
				) }
				description={ sprintf(
					// translators: %s: Link to PayPal business fees guide
					__(
						'Style the credit card fields to match your own style. Includes advanced processing with risk management, 3D Secure, fraud protection options, and chargeback protection. <a target="_blank" href="%s">Learn more</a>',
						'woocommerce-paypal-payments'
					),
					'https://www.paypal.com/us/business/paypal-business-fees'
				) }
			/>
			<Separator className="ppcp-r-optional-payment-methods__separator" />
			<BadgeBox
				title={ __( 'Digital Wallets', 'woocommerce-paypal-payments' ) }
				imageBadge={ [
					'icon-button-apple-pay.svg',
					'icon-button-google-pay.svg',
				] }
				textBadge={ generatePriceText(
					'dw',
					countryPriceInfo[ storeCountry ],
					storeCurrency
				) }
				description={ sprintf(
					// translators: %s: Link to PayPal business fees guide
					__(
						'Accept Apple Pay on eligible devices and Google Pay through mobile and web. <a target="_blank" href="%s">Learn more</a>',
						'woocommerce-paypal-payments'
					),
					'https://www.paypal.com/us/business/paypal-business-fees'
				) }
			/>
			<Separator className="ppcp-r-optional-payment-methods__separator" />
			<BadgeBox
				title={ __(
					'Alternative Payment Methods',
					'woocommerce-paypal-payments'
				) }
				imageBadge={ [
					'icon-button-sepa.svg',
					'icon-button-ideal.svg',
					'icon-button-blik.svg',
					'icon-button-bancontact.svg',
				] }
				textBadge={ generatePriceText(
					'apm',
					countryPriceInfo[ storeCountry ],
					storeCurrency
				) }
				description={ sprintf(
					// translators: %s: Link to PayPal business fees guide
					__(
						'Seamless payments for customers across the globe using their preferred payment methods. <a target="_blank" href="%s">Learn more</a>',
						'woocommerce-paypal-payments'
					),
					'https://www.paypal.com/us/business/paypal-business-fees'
				) }
			/>
		</div>
	);
};

export default AcdcOptionalPaymentMethods;
