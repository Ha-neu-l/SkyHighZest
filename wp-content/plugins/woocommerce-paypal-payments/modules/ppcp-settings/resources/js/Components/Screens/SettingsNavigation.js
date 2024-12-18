import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import data from '../../utils/data';

const SettingsNavigation = ( {} ) => {
	return (
		<div className="ppcp-r-navigation-container">
			<div className="ppcp-r-navigation">
				<div className="ppcp-r-navigation--left">
					<span>{ data().getImage( 'icon-arrow-left.svg' ) }</span>
					<a
						className="ppcp-r-navigation--left__link"
						href={ global.ppcpSettings.wcPaymentsTabUrl }
						aria-label={ __(
							'PayPal Payments',
							'woocommerce-paypal-payments'
						) }
					>
						{ __(
							'PayPal Payments',
							'woocommerce-paypal-payments'
						) }
					</a>
				</div>
				{
					<div className="ppcp-r-navigation--right">
						<Button variant="primary" disabled={ false }>
							{ __( 'Save', 'woocommerce-paypal-payments' ) }
						</Button>
					</div>
				}
			</div>
		</div>
	);
};

export default SettingsNavigation;
