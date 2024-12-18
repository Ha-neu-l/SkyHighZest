import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

import OnboardingHeader from '../../ReusableComponents/OnboardingHeader';
import PaymentMethodIcons from '../../ReusableComponents/PaymentMethodIcons';
import Separator from '../../ReusableComponents/Separator';
import WelcomeDocs from '../../ReusableComponents/WelcomeDocs/WelcomeDocs';
import AccordionSection from '../../ReusableComponents/AccordionSection';

import AdvancedOptionsForm from './Components/AdvancedOptionsForm';

const StepWelcome = ( { setStep, currentStep, setCompleted } ) => {
	return (
		<div className="ppcp-r-page-welcome">
			<OnboardingHeader
				title={ __(
					'Welcome to PayPal Payments',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Your all-in-one integration for PayPal checkout solutions that enable buyers to pay via PayPal, Pay Later, all major credit/debit cards, Apple Pay, Google Pay, and more.',
					'woocommerce-paypal-payments'
				) }
			/>
			<div className="ppcp-r-inner-container">
				<WelcomeFeatures />
				<PaymentMethodIcons icons="all" />
				<p className="ppcp-r-button__description">
					{ __(
						`Click the button below to be guided through connecting your existing PayPal account or creating a new one.You will be able to choose the payment options that are right for your store.`,
						'woocommerce-paypal-payments'
					) }
				</p>
				<Button
					className="ppcp-r-button-activate-paypal"
					variant="primary"
					onClick={ () => setStep( currentStep + 1 ) }
				>
					{ __(
						'Activate PayPal Payments',
						'woocommerce-paypal-payments'
					) }
				</Button>
			</div>
			<Separator className="ppcp-r-page-welcome-mode-separator" />
			<WelcomeDocs
				useAcdc={ true }
				isFastlane={ true }
				isPayLater={ true }
				storeCountry={ 'us' }
				storeCurrency={ 'USD' }
			/>
			<Separator text={ __( 'or', 'woocommerce-paypal-payments' ) } />
			<AccordionSection
				title={ __(
					'See advanced options',
					'woocommerce-paypal-payments'
				) }
				className="onboarding-advanced-options"
				id="advanced-options"
			>
				<AdvancedOptionsForm setCompleted={ setCompleted } />
			</AccordionSection>
		</div>
	);
};

const WelcomeFeatures = () => {
	return (
		<div className="ppcp-r-welcome-features">
			<div className="ppcp-r-welcome-features__col">
				<span>{ __( 'Deposits', 'woocommerce-paypal-payments' ) }</span>
				<p>{ __( 'Instant', 'woocommerce-paypal-payments' ) }</p>
			</div>
			<div className="ppcp-r-welcome-features__col">
				<span>
					{ __( 'Payment Capture', 'woocommerce-paypal-payments' ) }
				</span>
				<p>
					{ __(
						'Authorize only or Capture',
						'woocommerce-paypal-payments'
					) }
				</p>
			</div>
			<div className="ppcp-r-welcome-features__col">
				<span>
					{ __(
						'Recurring payments',
						'woocommerce-paypal-payments'
					) }
				</span>
				<p>{ __( 'Supported', 'woocommerce-paypal-payments' ) }</p>
			</div>
		</div>
	);
};
export default StepWelcome;
