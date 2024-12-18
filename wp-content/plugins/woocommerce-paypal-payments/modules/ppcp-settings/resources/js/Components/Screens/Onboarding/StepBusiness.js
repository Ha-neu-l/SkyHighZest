import { __ } from '@wordpress/i18n';

import OnboardingHeader from '../../ReusableComponents/OnboardingHeader';
import SelectBoxWrapper from '../../ReusableComponents/SelectBoxWrapper';
import SelectBox from '../../ReusableComponents/SelectBox';
import { OnboardingHooks, BUSINESS_TYPES } from '../../../data';

const BUSINESS_RADIO_GROUP_NAME = 'business';

const StepBusiness = ( {} ) => {
	const { isCasualSeller, setIsCasualSeller } = OnboardingHooks.useBusiness();

	const handleSellerTypeChange = ( value ) => {
		setIsCasualSeller( BUSINESS_TYPES.CASUAL_SELLER === value );
	};

	const getCurrentValue = () => {
		if ( isCasualSeller === null ) {
			return '';
		}

		return isCasualSeller
			? BUSINESS_TYPES.CASUAL_SELLER
			: BUSINESS_TYPES.BUSINESS;
	};

	return (
		<div className="ppcp-r-page-business">
			<OnboardingHeader
				title={ __(
					'Choose your account type',
					'woocommerce-paypal-payments'
				) }
			/>
			<div className="ppcp-r-inner-container">
				<SelectBoxWrapper>
					<SelectBox
						title={ __(
							'Business',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Recommended for individuals and organizations that primarily use PayPal to sell goods or services or receive donations, even if your business is not incorporated.',
							'woocommerce-paypal-payments'
						) }
						name={ BUSINESS_RADIO_GROUP_NAME }
						value={ BUSINESS_TYPES.BUSINESS }
						changeCallback={ handleSellerTypeChange }
						currentValue={ getCurrentValue() }
						checked={ isCasualSeller === false }
						type="radio"
					></SelectBox>
					<SelectBox
						title={ __(
							'Personal Account',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Ideal for those who primarily make purchases or send personal transactions to family and friends.',
							'woocommerce-paypal-payments'
						) }
						name={ BUSINESS_RADIO_GROUP_NAME }
						value={ BUSINESS_TYPES.CASUAL_SELLER }
						changeCallback={ handleSellerTypeChange }
						currentValue={ getCurrentValue() }
						checked={ isCasualSeller === true }
						type="radio"
					></SelectBox>
				</SelectBoxWrapper>
			</div>
		</div>
	);
};

export default StepBusiness;
