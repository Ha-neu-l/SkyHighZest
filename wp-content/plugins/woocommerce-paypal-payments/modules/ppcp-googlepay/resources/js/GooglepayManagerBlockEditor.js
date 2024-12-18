import GooglepayButton from './Block/components/GooglepayButton';

const GooglepayManagerBlockEditor = ( {
	namespace,
	buttonConfig,
	ppcpConfig,
	buttonAttributes,
} ) => (
	<GooglepayButton
		namespace={ namespace }
		buttonConfig={ buttonConfig }
		ppcpConfig={ ppcpConfig }
		buttonAttributes={ buttonAttributes }
	/>
);

export default GooglepayManagerBlockEditor;
