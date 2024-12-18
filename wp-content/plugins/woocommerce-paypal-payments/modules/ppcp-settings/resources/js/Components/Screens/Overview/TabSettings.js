import { useState } from '@wordpress/element';
import CommonSettings from './TabSettingsElements/CommonSettings';
import ExpertSettings from './TabSettingsElements/ExpertSettings';

const TabSettings = () => {
	const [ settings, setSettings ] = useState( {
		invoicePrefix: '',
		authorizeOnly: false,
		captureVirtualOnlyOrders: false,
		savePaypalAndVenmo: false,
		saveCreditCardAndDebitCard: false,
		payNowExperience: false,
		sandboxAccountCredentials: false,
		sandboxMode: null,
		sandboxEnabled: false,
		sandboxClientId: '',
		sandboxSecretKey: '',
		sandboxConnected: false,
		logging: false,
		subtotalMismatchFallback: null,
		brandName: '',
		softDescriptor: '',
		paypalLandingPage: null,
		buttonLanguage: '',
	} );
	const updateFormValue = ( key, value ) => {
		setSettings( { ...settings, [ key ]: value } );
	};

	return (
		<>
			<div className="ppcp-r-settings">
				<CommonSettings
					settings={ settings }
					updateFormValue={ updateFormValue }
				/>
				<ExpertSettings
					settings={ settings }
					updateFormValue={ updateFormValue }
				/>
			</div>
		</>
	);
};

export default TabSettings;
