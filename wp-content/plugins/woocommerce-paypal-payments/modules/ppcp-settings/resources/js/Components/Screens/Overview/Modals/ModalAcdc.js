import PaymentMethodModal from '../../../ReusableComponents/PaymentMethodModal';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { RadioControl } from '@wordpress/components';

const ModalAcdc = ( { setModalIsVisible } ) => {
	const [ threeDSecure, setThreeDSecure ] = useState( 'no-3d-secure' );
	const acdcOptions = [
		{
			label: __( 'No 3D Secure', 'woocommerce-paypal-payments' ),
			value: 'no-3d-secure',
		},
		{
			label: __( 'Only when required', 'woocommerce-paypal-payments' ),
			value: 'only-required-3d-secure',
		},
		{
			label: __(
				'Always require 3D Secure',
				'woocommerce-paypal-payments'
			),
			value: 'always-3d-secure',
		},
	];

	return (
		<PaymentMethodModal
			setModalIsVisible={ setModalIsVisible }
			icon="payment-method-cards-big"
			title={ __(
				'Advanced Credit and Debit Card Payments',
				'woocommerce-paypal-payments'
			) }
		>
			<strong className="ppcp-r-modal__content-title">
				{ __( '3D Secure', 'woocommerce-paypal-payments' ) }
			</strong>
			<p className="ppcp-r-modal__description">
				{ __(
					'Authenticate cardholders through their card issuers to reduce fraud and improve transaction security. Successful 3D Secure authentication can shift liability for fraudulent chargebacks to the card issuer.',
					'woocommerce-paypal-payments'
				) }
			</p>
			<div className="ppcp-r-modal__field-rows ppcp-r-modal__field-rows--acdc">
				<RadioControl
					onChange={ setThreeDSecure }
					selected={ threeDSecure }
					options={ acdcOptions }
				/>

				<div className="ppcp-r-modal__field-row ppcp-r-modal__field-row--save">
					<Button variant="primary">
						{ __( 'Save changes', 'woocommerce-paypal-payments' ) }
					</Button>
				</div>
			</div>
		</PaymentMethodModal>
	);
};

export default ModalAcdc;
