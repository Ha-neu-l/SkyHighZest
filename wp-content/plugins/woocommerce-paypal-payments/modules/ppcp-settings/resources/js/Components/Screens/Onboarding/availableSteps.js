import StepWelcome from './StepWelcome';
import StepBusiness from './StepBusiness';
import StepProducts from './StepProducts';
import StepPaymentMethods from './StepPaymentMethods';
import StepCompleteSetup from './StepCompleteSetup';

export const getSteps = ( flags ) => {
	const allSteps = [
		StepWelcome,
		StepBusiness,
		StepProducts,
        StepPaymentMethods,
		StepCompleteSetup,
	];

	if ( ! flags.canUseCasualSelling ) {
		return allSteps.filter( ( step ) => step !== StepBusiness );
	}

	return allSteps;
};
