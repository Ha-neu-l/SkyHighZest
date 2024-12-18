import AcdcOptionalPaymentMethods from './AcdcOptionalPaymentMethods';
import BcdcOptionalPaymentMethods from './BcdcOptionalPaymentMethods';

const OptionalPaymentMethods = ( {
	useAcdc,
	isFastlane,
	isPayLater,
	storeCountry,
	storeCurrency,
} ) => {
	return (
		<div className="ppcp-r-optional-payment-methods">
			{ useAcdc ? (
				<AcdcOptionalPaymentMethods
					isFastlane={ isFastlane }
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
					storeCurrency={ storeCurrency }
				/>
			) : (
				<BcdcOptionalPaymentMethods
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
					storeCurrency={ storeCurrency }
				/>
			) }
		</div>
	);
};

export default OptionalPaymentMethods;
