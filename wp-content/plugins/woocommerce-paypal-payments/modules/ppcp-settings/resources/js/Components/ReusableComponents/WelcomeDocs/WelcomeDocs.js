import { __, sprintf } from '@wordpress/i18n';
import AcdcFlow from './AcdcFlow';
import BcdcFlow from './BcdcFlow';
import { Button } from '@wordpress/components';

const WelcomeDocs = ( {
	useAcdc,
	isFastlane,
	isPayLater,
	storeCountry,
	storeCurrency,
} ) => {
	const pricesBasedDescription = sprintf(
		// translators: %s: Link to PayPal REST application guide
		__(
			'<sup>1</sup>Prices based on domestic transactions as of October 25th, 2024. <a target="_blank" href="%s">Click here</a> for full pricing details.',
			'woocommerce-paypal-payments'
		),
		'https://woocommerce.com/document/woocommerce-paypal-payments/#manual-credential-input '
	);

	return (
		<div className="ppcp-r-welcome-docs">
			<h2 className="ppcp-r-welcome-docs__title">
				{ __(
					`Want to know more about PayPal Payments?`,
					'woocommerce-paypal-payments'
				) }
			</h2>
			{ useAcdc ? (
				<AcdcFlow
					isFastlane={ isFastlane }
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
					storeCurrency={ storeCurrency }
				/>
			) : (
				<BcdcFlow
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
					storeCurrency={ storeCurrency }
				/>
			) }
			<p
				className="ppcp-r-optional-payment-methods__description"
				dangerouslySetInnerHTML={ { __html: pricesBasedDescription } }
			></p>
		</div>
	);
};

export default WelcomeDocs;
