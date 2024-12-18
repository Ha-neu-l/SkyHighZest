import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { CardFields } from './Components/card-fields';

const config = wc.wcSettings.getSetting( 'ppcp-credit-card-gateway_data' );
const isUserLoggedIn = config?.scriptData?.is_user_logged_in;
const axoConfig = wc.wcSettings.getSetting( 'ppcp-axo-gateway_data' );
const axoEnabled = axoConfig !== false;

const Label = ( { components } ) => {
	const { PaymentMethodIcons } = components;
	return (
		<>
			<span dangerouslySetInnerHTML={ { __html: config?.title } } />
			<PaymentMethodIcons icons={ config?.card_icons } align="right" />
		</>
	);
};

registerPaymentMethod( {
	name: config?.id,
	label: <Label />,
	content: <CardFields config={ config } />,
	edit: <CardFields config={ config } />,
	ariaLabel: config?.title,
	canMakePayment: ( cartData ) => {
		const cartItems = cartData?.cart?.cartItems || [];

		// Check if any item in the cart is a subscription
		const hasSubscription = cartItems.some(
			( item ) =>
				item?.type === 'subscription' ||
				item?.type === 'variable-subscription' ||
				cartData?.paymentRequirements?.includes( 'subscriptions' )
		);

		// Show payment method if:
		// 1. Axo is disabled, OR
		// 2. User is logged in, OR
		// 3. Axo is enabled AND cart has subscriptions
		return !! (
			! axoEnabled ||
			isUserLoggedIn ||
			( axoEnabled && hasSubscription )
		);
	},
	supports: {
		showSavedCards: true,
		features: config?.supports,
	},
} );
