import ApplepayButton from './Block/components/ApplePayButton';

const ApplePayManagerBlockEditor = ( {
	namespace,
	buttonConfig,
	ppcpConfig,
	buttonAttributes,
} ) => (
	<ApplepayButton
		namespace={ namespace }
		buttonConfig={ buttonConfig }
		ppcpConfig={ ppcpConfig }
		buttonAttributes={ buttonAttributes }
	/>
);

export default ApplePayManagerBlockEditor;
