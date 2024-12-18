import { __ } from '@wordpress/i18n';
import SettingsBlock, {
	SETTINGS_BLOCK_STYLING_TYPE_PRIMARY,
	SETTINGS_BLOCK_STYLING_TYPE_SECONDARY,
	SETTINGS_BLOCK_TYPE_EMPTY,
	SETTINGS_BLOCK_TYPE_TOGGLE,
} from '../../../../ReusableComponents/SettingsBlock';

const OrderIntent = ( { updateFormValue, settings } ) => {
	return (
		<SettingsBlock
			title={ __( 'Order Intent', 'woocommerce-paypal-payments' ) }
			description={ __(
				'Choose between immediate capture or authorization-only, with manual capture in the Orders section.',
				'woocommerce-paypal-payments'
			) }
			style={ SETTINGS_BLOCK_STYLING_TYPE_PRIMARY }
			className="ppcp-r-settings-block--order-intent"
			actionProps={ {
				type: SETTINGS_BLOCK_TYPE_EMPTY,
			} }
		>
			<SettingsBlock
				title={ __( 'Authorize Only', 'woocommerce-paypal-payments' ) }
				description={ __(
					'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc pellentesque libero vitae mattis tempor.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_TOGGLE,
					callback: updateFormValue,
					key: 'authorizeOnly',
					value: settings.authorizeOnly,
				} }
			/>

			<SettingsBlock
				title={ __(
					'Capture Virtual-Only Orders',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc pellentesque libero vitae mattis tempor.',
					'woocommerce-paypal-payments'
				) }
				style={ SETTINGS_BLOCK_STYLING_TYPE_SECONDARY }
				actionProps={ {
					type: SETTINGS_BLOCK_TYPE_TOGGLE,
					callback: updateFormValue,
					key: 'captureVirtualOnlyOrders',
					value: settings.captureVirtualOnlyOrders,
				} }
			/>
		</SettingsBlock>
	);
};

export default OrderIntent;
