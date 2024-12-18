import { Button } from '@wordpress/components';
import PaymentMethodIcon from './PaymentMethodIcon';
import { PayPalCheckbox } from './Fields';
import { useState } from '@wordpress/element';
import { ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import data from '../../utils/data';

const PaymentMethodItem = ( props ) => {
	const [ paymentMethodState, setPaymentMethodState ] = useState();
	const [ modalIsVisible, setModalIsVisible ] = useState( false );
	let Modal = null;
	if ( props?.modal ) {
		Modal = props.modal;
	}
	const handleCheckboxState = ( checked ) => {
		if ( checked ) {
			setPaymentMethodState( props.payment_method_id );
		} else {
			setPaymentMethodState( null );
		}
	};
	return (
		<>
			<div className="ppcp-r-payment-method-item">
				<div className="ppcp-r-payment-method-item__wrap">
					<div className="ppcp-r-payment-method-item__title-wrap">
						<PaymentMethodIcon
							icons={ [ props.icon ] }
							type={ props.icon }
						/>
						<span className="ppcp-r-payment-method-item__title">
							{ props.title }
						</span>
					</div>
					<div className="ppcp-r-payment-method-item__content">
						<p>{ props.description }</p>
					</div>
					<div className="ppcp-r-payment-method-item__footer">
						<ToggleControl
							__nextHasNoMarginBottom={ true }
							checked={
								props.payment_method_id === paymentMethodState
							}
							onChange={ ( newValue ) =>
								handleCheckboxState( newValue )
							}
						/>
						<div
							className="ppcp-r-payment-method-item__settings-button"
							onClick={ () => setModalIsVisible( true ) }
						>
							{ Modal && data().getImage( 'icon-settings.svg' ) }
						</div>
					</div>
				</div>
			</div>
			{ Modal && modalIsVisible && (
				<Modal setModalIsVisible={ setModalIsVisible } />
			) }
		</>
	);
};

export default PaymentMethodItem;
