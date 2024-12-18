import { useState } from '@wordpress/element';
import useGooglepayApiToGenerateButton from '../hooks/useGooglepayApiToGenerateButton';
import usePayPalScript from '../hooks/usePayPalScript';
import useGooglepayScript from '../hooks/useGooglepayScript';
import useGooglepayConfig from '../hooks/useGooglepayConfig';

const GooglepayButton = ( {
	namespace,
	buttonConfig,
	ppcpConfig,
	buttonAttributes,
} ) => {
	const [ componentFrame, setComponentFrame ] = useState( null );
	const isPayPalLoaded = usePayPalScript( namespace, ppcpConfig );

	const isGooglepayLoaded = useGooglepayScript(
		componentFrame,
		buttonConfig,
		isPayPalLoaded
	);

	const googlepayConfig = useGooglepayConfig( namespace, isGooglepayLoaded );

	const { button, containerStyles } = useGooglepayApiToGenerateButton(
		componentFrame,
		namespace,
		buttonConfig,
		ppcpConfig,
		googlepayConfig,
		buttonAttributes
	);

	return (
		<>
			<div
				id="express-payment-method-ppcp-googlepay"
				style={ containerStyles }
				ref={ ( node ) => {
					if ( ! node ) {
						return;
					}

					// Set component frame
					setComponentFrame( node.ownerDocument );

					// Handle button mounting
					while ( node.firstChild ) {
						node.removeChild( node.firstChild );
					}
					if ( button ) {
						node.appendChild( button );
					}
				} }
			/>
			{ button && (
				<style>
					{ `.block-editor-iframe__html .gpay-card-info-animated-progress-bar-container {
                        display: none !important
                    }` }
				</style>
			) }
		</>
	);
};

export default GooglepayButton;
