import SettingsCard from '../../ReusableComponents/SettingsCard';
import { __ } from '@wordpress/i18n';
import {
	PayPalCheckbox,
} from '../../ReusableComponents/Fields';
import { useState } from '@wordpress/element';
import data from '../../../utils/data';
import { Button } from '@wordpress/components';
import TitleBadge, {
	TITLE_BADGE_NEGATIVE,
	TITLE_BADGE_POSITIVE,
} from '../../ReusableComponents/TitleBadge';
import ConnectionInfo, {
	connectionStatusDataDefault,
} from '../../ReusableComponents/ConnectionInfo';

const TabOverview = () => {
	const [ todos, setTodos ] = useState( [] );
	const [ todosData, setTodosData ] = useState( todosDataDefault );

	return (
		<div className="ppcp-r-tab-overview">
			{ todosData.length > 0 && (
				<SettingsCard
					className="ppcp-r-tab-overview-todo"
					title={ __(
						'Things to do next',
						'woocommerce-paypal-payments'
					) }
					description={ __(
						'Complete these tasks to keep your store updated with the latest products and services.',
						'woocommerce-paypal-payments'
					) }
				>
					<div className="ppcp-r-todo-items">
						{ todosData.map( ( todo ) => (
							<TodoItem
								name="todo_items"
								key={ todo.value }
								value={ todo.value }
								currentValue={ todos }
								changeCallback={ setTodos }
								label={ todo.description }
								changeTodos={ setTodosData }
								todosData={ todosData }
							/>
						) ) }
					</div>
				</SettingsCard>
			) }
			<SettingsCard
				className="ppcp-r-tab-overview-support"
				title={ __( 'Status', 'woocommerce-paypal-payments' ) }
				description={ __(
					'Your PayPal account connection details, along with available products and features.',
					'woocommerce-paypal-payments'
				) }
			>
				<ConnectionStatus
					connectionData={ connectionStatusDataDefault }
				/>
				<FeaturesRefresh />
				{ featuresDefault.map( ( feature ) => {
					return (
						<FeatureItem key={ feature.id } feature={ feature } />
					);
				} ) }
			</SettingsCard>
		</div>
	);
};

const ConnectionStatus = ( { connectionData } ) => {
	return (
		<div className="ppcp-r-connection-status">
			<div className="ppcp-r-connection-status__status">
				<div className="ppcp-r-connection-status__status-status">
					<strong>
						{ __( 'Connection', 'woocommerce-paypal-payments' ) }
					</strong>
					{ connectionData.connectionStatus ? (
						<TitleBadge
							type={ TITLE_BADGE_POSITIVE }
							text={ __(
								'Activated',
								'woocommerce-paypal-payments'
							) }
						/>
					) : (
						<TitleBadge
							type={ TITLE_BADGE_NEGATIVE }
							text={ __(
								'Not Activated',
								'woocommerce-paypal-payments'
							) }
						/>
					) }
				</div>
				<div className="ppcp-r-connection-status__status-label">
					<span>
						{ __(
							'PayPal Account Details',
							'woocommerce-paypal-payments'
						) }
					</span>
				</div>
			</div>
			{ connectionData.connectionStatus && (
				<ConnectionInfo
					connectionStatusDataDefault={ connectionStatusDataDefault }
				/>
			) }
		</div>
	);
};

const FeaturesRefresh = () => {
	return (
		<div className="ppcp-r-feature-refresh">
			<div className="ppcp-r-feature-refresh__content">
				<span className="ppcp-r-feature-refresh__content-title">
					{ __( 'Features', 'woocommerce-paypal-payments' ) }
				</span>
				<p>
					{ __(
						'After making changes to your PayPal account, click Refresh to update your store features.',
						'woocommerce-paypal-payments'
					) }
				</p>
			</div>
			<Button variant="tertiary">
				{ data().getImage( 'icon-refresh.svg' ) }
				{ __( 'Refresh', 'woocommerce-paypal-payments' ) }
			</Button>
		</div>
	);
};

const TodoItem = ( props ) => {
	return (
		<div className="ppcp-r-todo-item">
			<div className="ppcp-r-todo-item__inner">
				<PayPalCheckbox
					{ ...{
						...props,
					} }
				/>{ ' ' }
			</div>
			<div
				className="ppcp-r-todo-item__close"
				onClick={ () =>
					removeTodo(
						props.value,
						props.todosData,
						props.changeTodos
					)
				}
			>
				{ data().getImage( 'icon-close.svg' ) }
			</div>
		</div>
	);
};

const FeatureItem = ( { feature } ) => {
	const printNotes = () => {
		if ( ! feature?.notes ) {
			return null;
		}

		if ( Array.isArray( feature.notes ) && feature.notes.length === 0 ) {
			return null;
		}

		return (
			<>
				<br />
				<br />
				<span className="ppcp-r-feature-item__notes">
					{ feature.notes.map( ( note, index ) => {
						return <span key={ index }>{ note }</span>;
					} ) }
				</span>
			</>
		);
	};

	return (
		<div className="ppcp-r-feature-item">
			<span className="ppcp-r-feature-item__title">
				{ feature.title }
				{ feature?.featureStatus && (
					<TitleBadge
						text={ __(
							'Activated',
							'woocommerce-paypal-payments'
						) }
						type={ TITLE_BADGE_POSITIVE }
					/>
				) }
			</span>
			<p className="ppcp-r-feature-item__description">
				{ feature.description }
				{ printNotes() }
			</p>
			<div className="ppcp-r-feature-item__buttons">
				{ feature.buttons.map( ( button ) => {
					return (
						<Button
							href={ button.url }
							key={ button.text }
							variant={ button.type }
						>
							{ button.text }
						</Button>
					);
				} ) }
			</div>
		</div>
	);
};

const removeTodo = ( todoValue, todosData, changeTodos ) => {
	changeTodos( todosData.filter( ( todo ) => todo.value !== todoValue ) );
};

const todosDataDefault = [
	{
		value: 'paypal_later_messaging',
		description: __(
			'Enable Pay Later messaging',
			'woocommerce-paypal-payments'
		),
	},
	{
		value: 'capture_authorized_payments',
		description: __(
			'Capture authorized payments',
			'woocommerce-paypal-payments'
		),
	},
	{
		value: 'enable_google_pay',
		description: __( 'Enable Google Pay', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'paypal_shortcut',
		description: __(
			'Add PayPal shortcut to the Cart page',
			'woocommerce-paypal-payments'
		),
	},
	{
		value: 'advanced_cards',
		description: __(
			'Add Advanced Cards to Blocks Checkout',
			'woocommerce-paypal-payments'
		),
	},
];

const featuresDefault = [
	{
		id: 'save_paypal_and_venmo',
		title: __( 'Save PayPal and Venmo', 'woocommerce-paypal-payments' ),
		description: __(
			'Securely save PayPal and Venmo payment methods for subscriptions or return buyers.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'primary',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'secondary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'advanced_credit_and_debit_cards',
		title: __(
			'Advanced Credit and Debit Cards',
			'woocommerce-paypal-payments'
		),
		featureStatus: true,
		description: __(
			'Process major credit and debit cards including Visa, Mastercard, American Express and Discover.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'primary',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'secondary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'alternative_payment_methods',
		title: __(
			'Alternative Payment Methods',
			'woocommerce-paypal-payments'
		),
		description: __(
			'Offer global, country-specific payment options for your customers.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'primary',
				text: __( 'Apply', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'secondary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'google_pay',
		title: __( 'Google Pay', 'woocommerce-paypal-payments' ),
		description: __(
			'Let customers pay using their Google Pay wallet.',
			'woocommerce-paypal-payments'
		),
		featureStatus: true,
		buttons: [
			{
				type: 'primary',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'secondary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
		notes: [
			__( '¹PayPal Q2 Earnings-2021.', 'woocommerce-paypal-payments' ),
		],
	},
	{
		id: 'apple_pay',
		title: __( 'Apple Pay', 'woocommerce-paypal-payments' ),
		description: __(
			'Let customers pay using their Apple Pay wallet.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'primary',
				text: __(
					'Domain registration',
					'woocommerce-paypal-payments'
				),
				url: '#',
			},
			{
				type: 'secondary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'pay_later_messaging',
		title: __( 'Pay Later Messaging', 'woocommerce-paypal-payments' ),
		description: __(
			'Let customers know they can buy now and pay later with PayPal. Adding this messaging can boost conversion rates and increase cart sizes by 39%¹, with no extra cost to you—plus, you get paid up front.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'primary',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'secondary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
];
export default TabOverview;
