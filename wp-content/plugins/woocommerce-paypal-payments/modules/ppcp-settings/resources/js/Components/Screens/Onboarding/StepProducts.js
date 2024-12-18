import { __ } from '@wordpress/i18n';

import OnboardingHeader from '../../ReusableComponents/OnboardingHeader';
import SelectBox from '../../ReusableComponents/SelectBox';
import SelectBoxWrapper from '../../ReusableComponents/SelectBoxWrapper';
import { OnboardingHooks, PRODUCT_TYPES } from '../../../data';

const PRODUCTS_CHECKBOX_GROUP_NAME = 'products';

const StepProducts = () => {
	const { products, setProducts } = OnboardingHooks.useProducts();

	return (
		<div className="ppcp-r-page-products">
			<OnboardingHeader
				title={ __(
					'Tell us about the products you sell',
					'woocommerce-paypal-payments'
				) }
			/>
			<div className="ppcp-r-inner-container">
				<SelectBoxWrapper>
					<SelectBox
						title={ __( 'Virtual', 'woocommerce-paypal-payments' ) }
						description={ __(
							'Items do not require shipping.',
							'woocommerce-paypal-payments'
						) }
						name={ PRODUCTS_CHECKBOX_GROUP_NAME }
						value={ PRODUCT_TYPES.VIRTUAL }
						changeCallback={ setProducts }
						currentValue={ products }
						type="checkbox"
					>
						<ul className="ppcp-r-services">
							<li>
								{ __(
									'Services',
									'woocommerce-paypal-payments'
								) }
							</li>
							<li>
								{ __(
									'Downloadable',
									'woocommerce-paypal-payments'
								) }
							</li>
							<li>
								{ __(
									'Bookings',
									'woocommerce-paypal-payments'
								) }
							</li>
							<li>
								{ __(
									'Deposits',
									'woocommerce-paypal-payments'
								) }
							</li>
						</ul>
					</SelectBox>
					<SelectBox
						title={ __(
							'Physical Goods',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Items require shipping.',
							'woocommerce-paypal-payments'
						) }
						name={ PRODUCTS_CHECKBOX_GROUP_NAME }
						value={ PRODUCT_TYPES.PHYSICAL }
						changeCallback={ setProducts }
						currentValue={ products }
						type="checkbox"
					>
						<ul className="ppcp-r-services">
							<li>
								{ __( 'Goods', 'woocommerce-paypal-payments' ) }
							</li>
							<li>
								{ __(
									'Deliveries',
									'woocommerce-paypal-payments'
								) }
							</li>
						</ul>
					</SelectBox>
					<SelectBox
						title={ __(
							'Subscriptions',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Recurring payments for either physical goods or services.',
							'woocommerce-paypal-payments'
						) }
						name={ PRODUCTS_CHECKBOX_GROUP_NAME }
						value={ PRODUCT_TYPES.SUBSCRIPTIONS }
						changeCallback={ setProducts }
						currentValue={ products }
						type="checkbox"
					>
						<a
							target="__blank"
							href="https://woocommerce.com/document/woocommerce-paypal-payments/#subscriptions-faq"
						>
							{ __(
								'WooCommerce Subscriptions',
								'woocommerce-paypal-payments'
							) }
						</a>
					</SelectBox>
				</SelectBoxWrapper>
			</div>
		</div>
	);
};

export default StepProducts;
