import { __, sprintf } from '@wordpress/i18n';
import SettingsBlock, {
	SETTINGS_BLOCK_STYLING_TYPE_PRIMARY,
	SETTINGS_BLOCK_STYLING_TYPE_SECONDARY,
	SETTINGS_BLOCK_STYLING_TYPE_TERTIARY,
	SETTINGS_BLOCK_TYPE_EMPTY,
	SETTINGS_BLOCK_TYPE_TOGGLE,
	SETTINGS_BLOCK_TYPE_TOGGLE_CONTENT,
} from '../../../../ReusableComponents/SettingsBlock';
import TitleBadge, {
	TITLE_BADGE_POSITIVE,
} from '../../../../ReusableComponents/TitleBadge';
import ConnectionInfo, {
	connectionStatusDataDefault,
} from '../../../../ReusableComponents/ConnectionInfo';
import { Button, TextControl } from '@wordpress/components';
import { PayPalRdbWithContent } from '../../../../ReusableComponents/Fields';

const Sandbox = ( { settings, updateFormValue } ) => {
	const className = settings.sandboxConnected
		? 'ppcp-r-settings-block--sandbox-connected'
		: 'ppcp-r-settings-block--sandbox-disconnected';
	return (
		<SettingsBlock
			title={ __( 'Sandbox', 'woocommerce-paypal-payments' ) }
			className={ className }
			description={ __(
				"Test your site in PayPal's Sandbox environment.<br /><strong>Note</strong>: No real payments/money movement occur in Sandbox mode. Do not ship orders made in this mode.",
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
			{ settings.sandboxConnected && (
				<SettingsBlock
					title={ __(
						'Sandbox account credentials',
						'woocommerce-paypal-payments'
					) }
					description={ __(
						'Your account is connected to sandbox, no real charging takes place. To accept live payments, turn off sandbox mode and connect your live PayPal account.',
						'woocommerce-paypal-payments'
					) }
					tag={
						<TitleBadge
							type={ TITLE_BADGE_POSITIVE }
							text={ __(
								'Connected',
								'woocommerce-paypal-payments'
							) }
						/>
					}
					style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
					actionProps={ {
						type: SETTINGS_BLOCK_TYPE_EMPTY,
						callback: updateFormValue,
						key: 'sandboxAccountCredentials',
						value: settings.sandboxAccountCredentials,
					} }
				>
					<div className="ppcp-r-settings-block--sandbox">
						<SettingsBlock
							title={ __(
								'Enable sandbox mode',
								'woocommerce-paypal-payments'
							) }
							style={ SETTINGS_BLOCK_STYLING_TYPE_TERTIARY }
							actionProps={ {
								type: SETTINGS_BLOCK_TYPE_TOGGLE,
								callback: updateFormValue,
								key: 'sandboxEnabled',
								value: settings.sandboxEnabled,
							} }
						/>
						<ConnectionInfo
							connectionStatusDataDefault={
								connectionStatusDataDefault
							}
						/>
						<Button
							variant="secondary"
							onClick={ () =>
								updateFormValue( 'sandboxConnected', false )
							}
						>
							{ __(
								'Disconnect Sandbox',
								'woocommerce-paypal-payments'
							) }
						</Button>
					</div>
				</SettingsBlock>
			) }
			{ ! settings.sandboxConnected && (
				<SettingsBlock
					title={ __(
						'Connect Sandbox Account',
						'woocommerce-paypal-payments'
					) }
					description={ __(
						'Connect a PayPal Sandbox account in order to test your website. Transactions made will not result in actual money movement. Do not fulfil orders completed in Sandbox mode.',
						'woocommerce-paypal-payments'
					) }
					style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
					actionProps={ {
						type: SETTINGS_BLOCK_TYPE_EMPTY,
						callback: updateFormValue,
						key: 'sandboxAccountCredentials',
						value: settings.sandboxAccountCredentials,
					} }
				>
					<div className="ppcp-r-settings-block--connect-sandbox ppcp-r-settings-block--expert-rdb">
						<PayPalRdbWithContent
							id="sandbox_mode"
							name="paypal_connect_sandbox"
							value="sandbox_mode"
							currentValue={ settings.sandboxMode }
							toggleAdditionalContent={ true }
							handleRdbState={ ( newValue ) =>
								updateFormValue( 'sandboxMode', newValue )
							}
							label={ __(
								'Sandbox Mode',
								'woocommerce-paypal-payments'
							) }
							description={ __(
								'Activate Sandbox mode to safely test PayPal with sample data. Once your store is ready to go live, you can easily switch to your production account.',
								'woocommerce-paypal-payments'
							) }
						>
							<Button
								variant="primary"
								onClick={ () =>
									updateFormValue( 'sandboxConnected', true )
								}
							>
								{ __(
									'Connect Sandbox Account',
									'woocommerce-paypal-payments'
								) }
							</Button>
						</PayPalRdbWithContent>
						<PayPalRdbWithContent
							id="manual_connect"
							name="paypal_connect_sandbox"
							value="manual_connect"
							currentValue={ settings.sandboxMode }
							toggleAdditionalContent={ true }
							handleRdbState={ ( newValue ) =>
								updateFormValue( 'sandboxMode', newValue )
							}
							label={ __(
								'Manual Connect',
								'woocommerce-paypal-payments'
							) }
							description={ sprintf(
								// translators: %s: Link to creating PayPal REST application
								__(
									'For advanced users: Connect a custom PayPal REST app for full control over your integration. For more information on creating a PayPal REST application, <a target="_blank" href="%s">click here</a>.',
									'woocommerce-paypal-payments'
								),
								'#'
							) }
						>
							<TextControl
								className="ppcp-r-vertical-text-control"
								label={ __(
									'Sandbox Client ID',
									'woocommerce-paypal-payments'
								) }
							/>
							<TextControl
								className="ppcp-r-vertical-text-control"
								label={ __(
									'Sandbox Secrey Key',
									'woocommerce-paypal-payments'
								) }
							/>
							<Button variant="primary">
								{ __(
									'Connect Account',
									'woocommerce-paypal-payments'
								) }
							</Button>
						</PayPalRdbWithContent>
					</div>
				</SettingsBlock>
			) }
		</SettingsBlock>
	);
};
export default Sandbox;
