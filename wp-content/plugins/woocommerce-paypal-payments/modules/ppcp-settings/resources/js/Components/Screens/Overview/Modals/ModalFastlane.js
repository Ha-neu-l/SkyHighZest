import PaymentMethodModal from '../../../ReusableComponents/PaymentMethodModal';
import { __ } from '@wordpress/i18n';
import { Button, ToggleControl } from '@wordpress/components';
import { PayPalRdb } from '../../../ReusableComponents/Fields';
import { useState } from '@wordpress/element';

const ModalFastlane = ( { setModalIsVisible } ) => {
	const [ fastlaneSettings, setFastlaneSettings ] = useState( {
		cardholderName: false,
		displayWatermark: false,
	} );

	const updateFormValue = ( key, value ) => {
		setFastlaneSettings( { ...fastlaneSettings, [ key ]: value } );
	};

	return (
		<PaymentMethodModal
			setModalIsVisible={ setModalIsVisible }
			icon="payment-method-fastlane-big"
			title={ __( 'Fastlane by PayPal', 'woocommerce-paypal-payments' ) }
			size="small"
		>
			<div className="ppcp-r-modal__field-rows ppcp-r-modal__field-rows--fastlane">
				<div className="ppcp-r-modal__field-row">
					<ToggleControl
						className="ppcp-r-modal__inverted-toggle-control"
						checked={ fastlaneSettings.cardholderName }
						onChange={ ( newValue ) =>
							updateFormValue( 'cardholderName', newValue )
						}
						label={ __(
							'Display cardholder name',
							'woocommerce-paypal-payments'
						) }
						id="ppcp-r-fastlane-settings-cardholder"
					/>
				</div>
				<div className="ppcp-r-modal__field-row">
					<ToggleControl
						className="ppcp-r-modal__inverted-toggle-control"
						checked={ fastlaneSettings.displayWatermark }
						onChange={ ( newValue ) =>
							updateFormValue( 'displayWatermark', newValue )
						}
						label={ __(
							'Display Fastlane Watermark',
							'woocommerce-paypal-payments'
						) }
						id="ppcp-r-fastlane-settings-watermark"
					/>
				</div>
				<div className="ppcp-r-modal__field-row ppcp-r-modal__field-row--save">
					<Button variant="primary">
						{ __( 'Save changes', 'woocommerce-paypal-payments' ) }
					</Button>
				</div>
			</div>
		</PaymentMethodModal>
	);
};

export default ModalFastlane;
