import SettingsBlock, {
	SETTINGS_BLOCK_STYLING_TYPE_PRIMARY,
	SETTINGS_BLOCK_STYLING_TYPE_SECONDARY,
	SETTINGS_BLOCK_TYPE_BUTTON,
	SETTINGS_BLOCK_TYPE_EMPTY,
	SETTINGS_BLOCK_TYPE_TOGGLE,
	SETTINGS_BLOCK_TYPE_TOGGLE_CONTENT,
} from '../../../../ReusableComponents/SettingsBlock';
import { __ } from '@wordpress/i18n';

const Troubleshooting = ( { updateFormValue, settings } ) => {
	return (
		<SettingsBlock
			className="ppcp-r-settings-block--troubleshooting"
			title={ __( 'Troubleshooting', 'woocommerce-paypal-payments' ) }
			description={ __(
				'Access tools to help debug and resolve issues.',
				'woocommerce-paypal-payments'
			) }
			style={ SETTINGS_BLOCK_STYLING_TYPE_PRIMARY }
			actionProps={ {
				type: SETTINGS_BLOCK_TYPE_TOGGLE_CONTENT,
				callback: updateFormValue,
				key: 'payNowExperience',
				value: settings.payNowExperience,
			} }
		>
			<SettingsBlock
				title={ __( 'Logging', 'woocommerce-paypal-payments' ) }
				description={ __(
					'Log additional debugging information in the WooCommerce logs that can assist technical staff to determine issues.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_TOGGLE,
					callback: updateFormValue,
					key: 'logging',
					value: settings.logging,
				} }
			/>
			<SettingsBlock
				title={ __(
					'Subscribed PayPal webhooks',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'The following PayPal webhooks are subscribed. More information about the webhooks is available in the Webhook Status documentation.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_EMPTY,
					callback: updateFormValue,
					key: 'logging',
					value: settings.logging,
				} }
			>
				<HooksTable data={ hooksExampleData() } />
			</SettingsBlock>

			<SettingsBlock
				title={ __(
					'Resubscribe webhooks',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Click to remove the current webhook subscription and subscribe again, for example, if the website domain or URL structure changed.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_BUTTON,
					buttonType: 'secondary',
					callback: () =>
						console.log(
							'Resubscribe webhooks',
							'woocommerce-paypal-payments'
						),
					value: __(
						'Resubscribe webhooks',
						'woocommerce-paypal-payments'
					),
				} }
			/>
			<SettingsBlock
				title={ __(
					'Simulate webhooks',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_BUTTON,
					buttonType: 'secondary',
					callback: () =>
						console.log(
							'Simulate webhooks',
							'woocommerce-paypal-payments'
						),
					value: __(
						'Simulate webhooks',
						'woocommerce-paypal-payments'
					),
				} }
			/>
		</SettingsBlock>
	);
};

const hooksExampleData = () => {
	return {
		url: 'https://www.rt3.tech/wordpress/paypal-ux-testin/index.php?rest_route=/paypal/v1/incoming',
		hooks: [
			'billing plan pricing-change activated',
			'billing plan updated',
			'billing subscription cancelled',
			'catalog product updated',
			'checkout order approved',
			'checkout order completed',
			'checkout payment-approval reversed',
			'payment authorization voided',
			'payment capture completed',
			'payment capture denied',
			'payment capture pending',
			'payment capture refunded',
			'payment capture reversed',
			'payment order cancelled',
			'payment sale completed',
			'payment sale refunded',
			'vault payment-token created',
			'vault payment-token deleted',
		],
	};
};

const HooksTable = ( { data } ) => {
	return (
		<table className="ppcp-r-table">
			<thead>
				<tr>
					<th className="ppcp-r-table__hooks-url">
						{ __( 'URL', 'woocommerce-paypal-payments' ) }
					</th>
					<th className="ppcp-r-table__hooks-events">
						{ __(
							'Tracked events',
							'woocommerce-paypal-payments'
						) }
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td className="ppcp-r-table__hooks-url">{ data?.url }</td>
					<td className="ppcp-r-table__hooks-events">
						{ data.hooks.map( ( hook, index ) => (
							<span key={ hook }>
								{ hook }{ ' ' }
								{ index !== data.hooks.length - 1 && ',' }
							</span>
						) ) }
					</td>
				</tr>
			</tbody>
		</table>
	);
};

export default Troubleshooting;
