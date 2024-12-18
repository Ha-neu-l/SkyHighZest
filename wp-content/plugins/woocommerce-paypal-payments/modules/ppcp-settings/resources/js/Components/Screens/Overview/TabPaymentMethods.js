import SettingsCard from '../../ReusableComponents/SettingsCard';
import { __ } from '@wordpress/i18n';
import PaymentMethodItem from '../../ReusableComponents/PaymentMethodItem';
import ModalPayPal from './Modals/ModalPayPal';
import ModalFastlane from './Modals/ModalFastlane';
import ModalAcdc from './Modals/ModalAcdc';

const TabPaymentMethods = () => {
	const renderPaymentMethods = ( data ) => {
		return (
			<div className="ppcp-r-payment-method-item-list">
				{ data.map( ( paymentMethod ) => (
					<PaymentMethodItem
						key={ paymentMethod.id }
						{ ...paymentMethod }
					/>
				) ) }
			</div>
		);
	};

	return (
		<div className="ppcp-r-payment-methods">
			<SettingsCard
				title={ __( 'PayPal Checkout', 'woocommerce-paypal-payments' ) }
				description={ __(
					'Select your preferred checkout option with PayPal for easy payment processing.',
					'woocommerce-paypal-payments'
				) }
				icon="icon-checkout-standard.svg"
			>
				{ renderPaymentMethods( paymentMethodsPayPalCheckoutDefault ) }
			</SettingsCard>
			<SettingsCard
				title={ __(
					'Online Card Payments',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Select your preferred card payment options for efficient payment processing.',
					'woocommerce-paypal-payments'
				) }
				icon="icon-checkout-online-methods.svg"
			>
				{ renderPaymentMethods(
					paymentMethodsOnlineCardPaymentsDefault
				) }
			</SettingsCard>
			<SettingsCard
				title={ __(
					'Alternative Payment Methods',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'With alternative payment methods, customers across the globe can pay with their bank accounts and other local payment methods.',
					'woocommerce-paypal-payments'
				) }
				icon="icon-checkout-alternative-methods.svg"
			>
				{ renderPaymentMethods( paymentMethodsAlternativeDefault ) }
			</SettingsCard>
		</div>
	);
};

const paymentMethodsPayPalCheckoutDefault = [
	{
		id: 'paypal',
		title: __( 'PayPal', 'woocommerce-paypal-payments' ),
		description: __(
			'Our all-in-one checkout solution lets you offer PayPal, Venmo, Pay Later options, and more to help maximize conversion.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-paypal',
		modal: ModalPayPal,
	},
	{
		id: 'venmo',
		title: __( 'Venmo', 'woocommerce-paypal-payments' ),
		description: __(
			'Offer Venmo at checkout to millions of active users.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-venmo',
	},
	{
		id: 'paypal_credit',
		title: __( 'PayPal Credit', 'woocommerce-paypal-payments' ),
		description: __(
			'Get paid in full at checkout while giving your customers the option to pay interest free if paid within 6 months on orders over $99.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-paypal',
	},
	{
		id: 'credit_and_debit_card_payments',
		title: __(
			'Credit and debit card payments',
			'woocommerce-paypal-payments'
		),
		description: __(
			"Accept all major credit and debit cards - even if your customer doesn't have a PayPal account.",
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-cards',
	},
];

const paymentMethodsOnlineCardPaymentsDefault = [
	{
		id: 'advanced_credit_and_debit_card_payments',
		title: __(
			'Advanced Credit and Debit Card Payments',
			'woocommerce-paypal-payments'
		),
		description: __(
			"Present custom credit and debit card fields to your payers so they can pay with credit and debit cards using your site's branding.",
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-advanced-cards',
		modal: ModalAcdc,
	},
	{
		id: 'fastlane',
		title: __( 'Fastlane by PayPal', 'woocommerce-paypal-payments' ),
		description: __(
			'Tap into the scale and trust of PayPal’s customer network to recognize shoppers and make guest checkout more seamless than ever.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-fastlane',
		modal: ModalFastlane,
	},
	{
		id: 'apply_pay',
		title: __( 'Apple Pay', 'woocommerce-paypal-payments' ),
		description: __(
			'Allow customers to pay via their Apple Pay digital wallet.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-apple-pay',
	},
	{
		id: 'google_pay',
		title: __( 'Google Pay', 'woocommerce-paypal-payments' ),
		description: __(
			'Allow customers to pay via their Google Pay digital wallet.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-google-pay',
	},
];

const paymentMethodsAlternativeDefault = [
	{
		id: 'bancontact',
		title: __( 'Bancontact', 'woocommerce-paypal-payments' ),
		description: __(
			'Bancontact is the most widely used, accepted and trusted electronic payment method in Belgium. Bancontact makes it possible to pay directly through the online payment systems of all major Belgian banks.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-bancontact',
	},
	{
		id: 'ideal',
		title: __( 'iDEAL', 'woocommerce-paypal-payments' ),
		description: __(
			'iDEAL is a payment method in the Netherlands that allows buyers to select their issuing bank from a list of options.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-ideal',
	},
	{
		id: 'eps',
		title: __( 'eps', 'woocommerce-paypal-payments' ),
		description: __(
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum porttitor massa ex, eget luctus lacus iaculis at.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-eps',
	},
	{
		id: 'blik',
		title: __( 'BLIK', 'woocommerce-paypal-payments' ),
		description: __(
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum porttitor massa ex, eget luctus lacus iaculis at.',
			'woocommerce-paypal-payments'
		),
		icon: 'payment-method-blik',
	},
];

export default TabPaymentMethods;
