import { __, sprintf } from '@wordpress/i18n';

import OnboardingHeader from '../../ReusableComponents/OnboardingHeader';
import SelectBoxWrapper from '../../ReusableComponents/SelectBoxWrapper';
import SelectBox from '../../ReusableComponents/SelectBox';
import { OnboardingHooks } from '../../../data';
import OptionalPaymentMethods from '../../ReusableComponents/OptionalPaymentMethods/OptionalPaymentMethods';

const OPM_RADIO_GROUP_NAME = 'optional-payment-methods';

const StepPaymentMethods = ( {} ) => {
	const {
		areOptionalPaymentMethodsEnabled,
		setAreOptionalPaymentMethodsEnabled,
	} = OnboardingHooks.useOptionalPaymentMethods();
	const pricesBasedDescription = sprintf(
		// translators: %s: Link to PayPal REST application guide
		__(
			'<sup>1</sup>Prices based on domestic transactions as of October 25th, 2024. <a target="_blank" href="%s">Click here</a> for full pricing details.',
			'woocommerce-paypal-payments'
		),
		'https://woocommerce.com/document/woocommerce-paypal-payments/#manual-credential-input '
	);

	return (
		<div className="ppcp-r-page-optional-payment-methods">
			<OnboardingHeader
				title={ __(
					'Add optional payment methods to your Checkout',
					'woocommerce-paypal-payments'
				) }
			/>
			<div className="ppcp-r-inner-container">
				<SelectBoxWrapper>
					<SelectBox
						title={ __(
							'Available with additional application',
							'woocommerce-paypal-payments'
						) }
						description={
							<OptionalPaymentMethods
								useAcdc={ true }
								isFastlane={ true }
								isPayLater={ true }
								storeCountry={ 'us' }
								storeCurrency={ 'usd' }
							/>
						}
						name={ OPM_RADIO_GROUP_NAME }
						value={ true }
						changeCallback={ setAreOptionalPaymentMethodsEnabled }
						currentValue={ areOptionalPaymentMethodsEnabled }
						type="radio"
					></SelectBox>
					<SelectBox
						title={ __(
							'No thanks, I prefer to use a different provider for processing credit cards, digital wallets, and local payment methods',
							'woocommerce-paypal-payments'
						) }
						name={ OPM_RADIO_GROUP_NAME }
						value={ false }
						changeCallback={ setAreOptionalPaymentMethodsEnabled }
						currentValue={ areOptionalPaymentMethodsEnabled }
						type="radio"
					></SelectBox>
				</SelectBoxWrapper>
				<p
					className="ppcp-r-optional-payment-methods__description"
					dangerouslySetInnerHTML={ {
						__html: pricesBasedDescription,
					} }
				></p>
			</div>
		</div>
	);
};

export default StepPaymentMethods;
