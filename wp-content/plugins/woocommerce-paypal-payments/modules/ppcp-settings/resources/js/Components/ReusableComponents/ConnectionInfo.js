import { __ } from '@wordpress/i18n';
import data from '../../utils/data';
import { useState } from '@wordpress/element';

const ConnectionInfo = ( { connectionStatusDataDefault } ) => {
	const [ connectionData, setConnectionData ] = useState( {
		...connectionStatusDataDefault,
	} );

	const showAllData = () => {
		setConnectionData( {
			...connectionData,
			showAllData: ! connectionData.showAllData,
		} );
	};

	const toggleStatusClassName = [ 'ppcp-r-connection-status__status-toggle' ];

	if ( connectionData.showAllData ) {
		toggleStatusClassName.push(
			'ppcp-r-connection-status__status-toggle--toggled'
		);
	}

	return (
		<div className="ppcp-r-connection-status__data">
			<div
				className="ppcp-r-connection-status__status-row ppcp-r-connection-status__status-row--first"
				onClick={ () => showAllData() }
			>
				<strong>
					{ __( 'Email address:', 'woocommerce-paypal-payments' ) }
				</strong>
				<span>{ connectionData.email }</span>
				<span className={ toggleStatusClassName.join( ' ' ) }>
					{ data().getImage(
						'icon-arrow-down.svg',
						'ppcp-r-connection-status__show-all-data'
					) }
				</span>
			</div>
			{ connectionData.showAllData && (
				<>
					<div className="ppcp-r-connection-status__status-row">
						<strong>
							{ __(
								'Merchant ID:',
								'woocommerce-paypal-payments'
							) }
						</strong>
						<span>{ connectionData.merchantId }</span>
					</div>
					<div className="ppcp-r-connection-status__status-row">
						<strong>
							{ __(
								'Client ID:',
								'woocommerce-paypal-payments'
							) }
						</strong>
						<span>{ connectionData.clientId }</span>
					</div>
				</>
			) }
		</div>
	);
};
export default ConnectionInfo;

export const connectionStatusDataDefault = {
	connectionStatus: true,
	showAllData: false,
	email: 'bt_us@woocommerce.com',
	merchantId: 'AT45V2DGMKLRY',
	clientId: 'BAARTJLxtUNN4d2GMB6Eut3suMDYad72xQA-FntdIFuJ6FmFJITxAY8',
};
