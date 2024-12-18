import { __ } from '@wordpress/i18n';
import SettingsBlock, {
	SETTINGS_BLOCK_STYLING_TYPE_PRIMARY,
	SETTINGS_BLOCK_STYLING_TYPE_SECONDARY,
	SETTINGS_BLOCK_TYPE_EMPTY,
	SETTINGS_BLOCK_TYPE_INPUT,
	SETTINGS_BLOCK_TYPE_SELECT,
	SETTINGS_BLOCK_TYPE_TOGGLE,
	SETTINGS_BLOCK_TYPE_TOGGLE_CONTENT,
} from '../../../../ReusableComponents/SettingsBlock';
import { PayPalRdbWithContent } from '../../../../ReusableComponents/Fields';

const PaypalSettings = ( { updateFormValue, settings } ) => {
	return (
		<SettingsBlock
			className="ppcp-r-settings-block--settings"
			title={ __( 'PayPal Settings', 'woocommerce-paypal-payments' ) }
			description={ __(
				'Modify the PayPal checkout experience',
				'woocommerce-paypal-payments'
			) }
			style={ SETTINGS_BLOCK_STYLING_TYPE_PRIMARY }
			actionProps={ {
				callback: updateFormValue,
				type: SETTINGS_BLOCK_TYPE_TOGGLE_CONTENT,
				key: 'payNowExperience',
				value: settings.payNowExperience,
			} }
		>
			<SettingsBlock
				title={ __(
					'Subtotal mismatch fallback',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Due to differences in how WooCommerce and PayPal calculates taxes, some transactions may fail due to a rounding error. This settings determines the fallback behavior.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_EMPTY,
				} }
			>
				<div className="ppcp-r-settings-block--mismatch-wrapper ppcp-r-settings-block--expert-rdb">
					<PayPalRdbWithContent
						id="add_a_correction"
						name="paypal_settings_mismatch"
						value="add_a_correction"
						currentValue={ settings.subtotalMismatchFallback }
						handleRdbState={ ( newValue ) =>
							updateFormValue(
								'subtotalMismatchFallback',
								newValue
							)
						}
						label={ __(
							'Add a correction',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Adds an additional line item with the missing amount.',
							'woocommerce-paypal-payments'
						) }
					/>
					<PayPalRdbWithContent
						id="do_not_send_line_items"
						name="paypal_settings_mismatch"
						value="do_not_send_line_items"
						currentValue={ settings.subtotalMismatchFallback }
						handleRdbState={ ( newValue ) =>
							updateFormValue(
								'subtotalMismatchFallback',
								newValue
							)
						}
						label={ __(
							'Do not send line items',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Resubmit the transaction without line item details',
							'woocommerce-paypal-payments'
						) }
					/>
				</div>
			</SettingsBlock>

			<SettingsBlock
				title={ __(
					'Instant payments only',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'If enabled, PayPal will not allow buyers to use funding sources that take additional time to complete, such as eChecks.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_TOGGLE,
					value: settings.savePaypalAndVenmo,
					callback: updateFormValue,
					key: 'savePaypalAndVenmo',
				} }
			/>
			<SettingsBlock
				title={ __( 'Brand name', 'woocommerce-paypal-payments' ) }
				description={ __(
					'What business name to show to your buyers during checkout and on receipts.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_INPUT,
					value: settings.brandName,
					callback: updateFormValue,
					key: 'brandName',
					placeholder: __(
						'Brand name',
						'woocommerce-paypal-payments'
					),
				} }
			/>
			<SettingsBlock
				title={ __( 'Soft Descriptor', 'woocommerce-paypal-payments' ) }
				description={ __(
					"The dynamic text used to construct the statement descriptor that appears on a payer's card statement. Applies to PayPal and Credit Card transactions. Max value of 22 characters.",
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_INPUT,
					value: settings.softDescriptor,
					callback: updateFormValue,
					key: 'softDescriptor',
					placeholder: __(
						'Soft Descriptor',
						'woocommerce-paypal-payments'
					),
				} }
			/>
			<SettingsBlock
				title={ __(
					'PayPal landing page',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Determine which experience a buyer sees when they click the PayPal button.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_EMPTY,
				} }
			>
				<div className="ppcp-r-settings-block--landing ppcp-r-settings-block--expert-rdb">
					<PayPalRdbWithContent
						id="no_perference"
						name="paypal_settings_landing"
						value="no_reference"
						currentValue={ settings.paypalLandingPage }
						handleRdbState={ ( newValue ) =>
							updateFormValue( 'paypalLandingPage', newValue )
						}
						label={ __(
							'No preference',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Shows the buyer the PayPal login for a recognized PayPal buyer.',
							'woocommerce-paypal-payments'
						) }
					/>
					<PayPalRdbWithContent
						id="login_page"
						name="paypal_settings_landing"
						value="login_page"
						currentValue={ settings.paypalLandingPage }
						handleRdbState={ ( newValue ) =>
							updateFormValue( 'paypalLandingPage', newValue )
						}
						label={ __(
							'Login page',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Always show the buyer the PayPal login screen.',
							'woocommerce-paypal-payments'
						) }
					/>
					<PayPalRdbWithContent
						id="guest_checkout_page"
						name="paypal_settings_landing"
						value="guest_checkout_page"
						currentValue={ settings.paypalLandingPage }
						handleRdbState={ ( newValue ) =>
							updateFormValue( 'paypalLandingPage', newValue )
						}
						label={ __(
							'Guest checkout page',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Always show the buyer the guest checkout fields first.',
							'woocommerce-paypal-payments'
						) }
					/>
				</div>
			</SettingsBlock>
			<SettingsBlock
				title={ __( 'Button Language', 'woocommerce-paypal-payments' ) }
				description={ __(
					'If left blank, PayPal and other buttons will present in the user’s detected language. Enter a language here to force all buttons to display in that language.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_SELECT,
					value: settings.buttonLanguage,
					callback: updateFormValue,
					options: languagesExample,
					key: 'buttonLanguage',
					placeholder: __(
						'Browser language',
						'woocommerce-paypal-payments'
					),
				} }
			/>
		</SettingsBlock>
	);
};

const languagesExample = [
	{ value: 'en', label: 'English' },
	{ value: 'de', label: 'German' },
	{ value: 'es', label: 'Spanish' },
	{ value: 'it', label: 'Italian' },
];
export default PaypalSettings;
