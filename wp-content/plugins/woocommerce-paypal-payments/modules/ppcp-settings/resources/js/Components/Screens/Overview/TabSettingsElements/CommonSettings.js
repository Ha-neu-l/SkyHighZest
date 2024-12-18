import { __ } from '@wordpress/i18n';
import SettingsBlock, {
	SETTINGS_BLOCK_STYLING_TYPE_PRIMARY,
	SETTINGS_BLOCK_STYLING_TYPE_SECONDARY,
	SETTINGS_BLOCK_TYPE_INPUT,
	SETTINGS_BLOCK_TYPE_TOGGLE,
} from '../../../ReusableComponents/SettingsBlock';
import SettingsCard from '../../../ReusableComponents/SettingsCard';
import OrderIntent from './Blocks/OrderIntent';
import SavePaymentMethods from './Blocks/SavePaymentMethods';

const CommonSettings = ( { updateFormValue, settings } ) => {
	return (
		<SettingsCard
			icon="icon-settings-common.svg"
			title={ __( 'Common Settings', 'woocommerce-paypal-payments' ) }
			className="ppcp-r-settings-card ppcp-r-settings-card--common-settings"
			description={ __(
				'Customize key features to tailor your PayPal experience.',
				'woocommerce-paypal-payments'
			) }
		>
			<SettingsBlock
				title="Invoice Prefix"
				description="Add a unique prefix to invoice numbers for site-specific tracking (recommended)."
				style={ SETTINGS_BLOCK_STYLING_TYPE_PRIMARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_INPUT,
					callback: updateFormValue,
					key: 'invoicePrefix',
					value: settings.invoicePrefix,
					placeholder: __(
						'Input prefix',
						'woocommerce-paypal-payments'
					),
				} }
			/>
			<OrderIntent
				settings={ settings }
				updateFormValue={ updateFormValue }
			/>
			<SavePaymentMethods
				updateFormValue={ updateFormValue }
				settings={ settings }
			/>
			<SettingsBlock
				title={ __(
					'Pay Now Experience',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Let PayPal customers skip the Order Review page by selecting shipping options directly within PayPal.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_TOGGLE,
					callback: updateFormValue,
					key: 'payNowExperience',
					value: settings.payNowExperience,
				} }
			/>
		</SettingsCard>
	);
};

export default CommonSettings;
