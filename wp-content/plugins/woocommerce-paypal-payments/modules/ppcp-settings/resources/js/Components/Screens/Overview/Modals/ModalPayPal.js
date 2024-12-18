import PaymentMethodModal from '../../../ReusableComponents/PaymentMethodModal';
import { __ } from '@wordpress/i18n';
import { ToggleControl, Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const ModalPayPal = ( { setModalIsVisible } ) => {
	const [ paypalSettings, setPaypalSettings ] = useState( {
		checkoutPageTitle: 'PayPal',
		checkoutPageDescription: 'Pay via PayPal',
		showLogo: false,
	} );

	const updateFormValue = ( key, value ) => {
		setPaypalSettings( { ...paypalSettings, [ key ]: value } );
	};

	return (
		<PaymentMethodModal
			setModalIsVisible={ setModalIsVisible }
			icon="payment-method-paypal-big"
			title={ __( 'PayPal', 'woocommerce-paypal-payments' ) }
		>
			<div className="ppcp-r-modal__field-rows">
				<div className="ppcp-r-modal__field-row">
					<TextControl
						className="ppcp-r-vertical-text-control"
						label={ __(
							'Checkout page title',
							'woocommerce-paypal-payments'
						) }
						value={ paypalSettings.checkoutPageTitle }
						onChange={ ( newValue ) =>
							updateFormValue( 'checkoutPageTitle', newValue )
						}
					/>
				</div>
				<div className="ppcp-r-modal__field-row">
					<TextControl
						className="ppcp-r-vertical-text-control"
						label={ __(
							'Checkout page description',
							'woocommerce-paypal-payments'
						) }
						value={ paypalSettings.checkoutPageDescription }
						onChange={ ( newValue ) =>
							updateFormValue(
								'checkoutPageDescription',
								newValue
							)
						}
					/>
				</div>
				<div className="ppcp-r-modal__field-row">
					<ToggleControl
						label={ __(
							'Show logo',
							'woocommerce-paypal-payments'
						) }
						id="ppcp-r-paypal-settings-show-logo"
						checked={ paypalSettings.showLogo }
						onChange={ ( newValue ) => {
							updateFormValue( 'showLogo', newValue );
						} }
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

export default ModalPayPal;
