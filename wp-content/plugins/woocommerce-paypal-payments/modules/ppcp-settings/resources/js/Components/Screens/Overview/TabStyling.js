import { __, sprintf } from '@wordpress/i18n';
import { SelectControl, RadioControl } from '@wordpress/components';
import { PayPalCheckboxGroup } from '../../ReusableComponents/Fields';
import { useState, useMemo, useEffect } from '@wordpress/element';
import { PayPalScriptProvider, PayPalButtons } from '@paypal/react-paypal-js';

import {
	defaultLocationSettings,
	paymentMethodOptions,
	colorOptions,
	shapeOptions,
	buttonLayoutOptions,
	buttonLabelOptions,
} from '../../../data/settings/tab-styling-data';

const TabStyling = () => {
	const [ location, setLocation ] = useState( 'cart' );
	const [ canRender, setCanRender ] = useState( false );
	const [ locationSettings, setLocationSettings ] = useState( {
		...defaultLocationSettings,
	} );

	// Sometimes buttons won't render. This fixes the timing problem.
	useEffect( () => {
		const handleDOMContentLoaded = () => setCanRender( true );
		if (
			document.readyState === 'interactive' ||
			document.readyState === 'complete'
		) {
			handleDOMContentLoaded();
		} else {
			document.addEventListener(
				'DOMContentLoaded',
				handleDOMContentLoaded
			);
		}
	}, [] );

	const currentLocationSettings = useMemo( () => {
		return locationSettings[ location ];
	}, [ location, locationSettings ] );

	const locationOptions = useMemo( () => {
		return Object.keys( locationSettings ).reduce(
			( locationOptionsData, key ) => {
				locationOptionsData.push( {
					value: locationSettings[ key ].value,
					label: locationSettings[ key ].label,
				} );

				return locationOptionsData;
			},
			[]
		);
	}, [] );

	const updateButtonSettings = ( key, value ) => {
		setLocationSettings( {
			...locationSettings,
			[ location ]: {
				...currentLocationSettings,
				settings: {
					...currentLocationSettings.settings,
					[ key ]: value,
				},
			},
		} );
	};

	const updateButtonStyle = ( key, value ) => {
		setLocationSettings( {
			...locationSettings,
			[ location ]: {
				...currentLocationSettings,
				settings: {
					...currentLocationSettings.settings,
					style: {
						...currentLocationSettings.settings.style,
						[ key ]: value,
					},
				},
			},
		} );
	};

	if ( ! canRender ) {
		return <></>;
	}

	return (
		<div className="ppcp-r-styling">
			<div className="ppcp-r-styling__settings">
				<SectionIntro />
				<SectionLocations
					locationOptions={ locationOptions }
					location={ location }
					setLocation={ setLocation }
				/>
				<SectionPaymentMethods
					locationSettings={ currentLocationSettings }
					updateButtonSettings={ updateButtonSettings }
				/>

				<SectionButtonLayout
					locationSettings={ currentLocationSettings }
					updateButtonStyle={ updateButtonStyle }
				/>

				<SectionButtonShape
					locationSettings={ currentLocationSettings }
					updateButtonStyle={ updateButtonStyle }
				/>
				<SectionButtonLabel
					locationSettings={ currentLocationSettings }
					updateButtonStyle={ updateButtonStyle }
				/>
				<SectionButtonColor
					locationSettings={ currentLocationSettings }
					updateButtonStyle={ updateButtonStyle }
				/>
				<SectionButtonTagline
					locationSettings={ currentLocationSettings }
					updateButtonStyle={ updateButtonStyle }
				/>
			</div>
			<div className="ppcp-preview ppcp-r-button-preview ppcp-r-styling__preview">
				<div className="ppcp-r-styling__preview-inner">
					<SectionButtonPreview
						locationSettings={ currentLocationSettings }
					/>
				</div>
			</div>
		</div>
	);
};

const TabStylingSection = ( props ) => {
	let sectionTitleClassName = 'ppcp-r-styling__section';

	if ( props?.className ) {
		sectionTitleClassName += ` ${ props.className }`;
	}

	return (
		<div className={ sectionTitleClassName }>
			<span className="ppcp-r-styling__title">{ props.title }</span>
			{ props?.description && (
				<p
					dangerouslySetInnerHTML={ {
						__html: props.description,
					} }
					className="ppcp-r-styling__description"
				/>
			) }
			{ props.children }
		</div>
	);
};

const SectionIntro = () => {
	const buttonStyleDescription = sprintf(
		// translators: %s: Link to Classic checkout page
		__(
			'Customize the appearance of the PayPal smart buttons on the <a href="%s">[MISSING LINK]Classic Checkout page</a>. Checkout Buttons must be enabled to display the PayPal gateway on the Checkout page.'
		),
		'#'
	);
	return (
		<TabStylingSection
			className="ppcp-r-styling__section--rc ppcp-r-styling__section--empty"
			title={ __( 'Button Styling', 'wooocommerce-paypal-payments' ) }
			description={ buttonStyleDescription }
		></TabStylingSection>
	);
};

const SectionLocations = ( { locationOptions, location, setLocation } ) => {
	return (
		<TabStylingSection className="ppcp-r-styling__section--rc">
			<SelectControl
				className="ppcp-r-styling__select"
				value={ location }
				onChange={ ( newLocation ) => setLocation( newLocation ) }
				label={ __( 'Locations', 'woocommerce-paypal-payments' ) }
				options={ locationOptions }
			/>
		</TabStylingSection>
	);
};

const SectionPaymentMethods = ( {
	locationSettings,
	updateButtonSettings,
} ) => {
	return (
		<TabStylingSection
			title={ __( 'Payment Methods', 'woocommerce-paypal-payments' ) }
			className="ppcp-r-styling__section--rc"
		>
			<div className="ppcp-r-styling__payment-method-checkboxes">
				<PayPalCheckboxGroup
					value={ paymentMethodOptions }
					changeCallback={ ( newValue ) =>
						updateButtonSettings( 'paymentMethods', newValue )
					}
					currentValue={ locationSettings.settings.paymentMethods }
				/>
			</div>
		</TabStylingSection>
	);
};

const SectionButtonLayout = ( { locationSettings, updateButtonStyle } ) => {
	const buttonLayoutIsAllowed =
		locationSettings.settings.style?.layout &&
		locationSettings.settings.style?.tagline === false;
	return (
		buttonLayoutIsAllowed && (
			<TabStylingSection
				className="ppcp-r-styling__section--rc"
				title={ __( 'Button Layout', 'woocommerce-paypal-payments' ) }
			>
				<RadioControl
					className="ppcp-r__horizontal-control"
					onChange={ ( newValue ) =>
						updateButtonStyle( 'layout', newValue )
					}
					selected={ locationSettings.settings.style.layout }
					options={ buttonLayoutOptions }
				/>
			</TabStylingSection>
		)
	);
};

const SectionButtonShape = ( { locationSettings, updateButtonStyle } ) => {
	return (
		<TabStylingSection
			title={ __( 'Shape', 'woocommerce-paypal-payments' ) }
			className="ppcp-r-styling__section--rc"
		>
			<RadioControl
				className="ppcp-r__horizontal-control"
				onChange={ ( newValue ) =>
					updateButtonStyle( 'shape', newValue )
				}
				selected={ locationSettings.settings.style.shape }
				options={ shapeOptions }
			/>
		</TabStylingSection>
	);
};

const SectionButtonLabel = ( { locationSettings, updateButtonStyle } ) => {
	return (
		<TabStylingSection>
			<SelectControl
				className="ppcp-r-styling__select"
				onChange={ ( newValue ) =>
					updateButtonStyle( 'label', newValue )
				}
				value={ locationSettings.settings.style.label }
				label={ __( 'Button Label', 'woocommerce-paypal-payments' ) }
				options={ buttonLabelOptions }
			/>
		</TabStylingSection>
	);
};

const SectionButtonColor = ( { locationSettings, updateButtonStyle } ) => {
	return (
		<TabStylingSection>
			<SelectControl
				className=" ppcp-r-styling__select"
				label={ __( 'Button Color', 'woocommerce-paypal-payments' ) }
				onChange={ ( newValue ) =>
					updateButtonStyle( 'color', newValue )
				}
				value={ locationSettings.settings.style.color }
				options={ colorOptions }
			/>
		</TabStylingSection>
	);
};

const SectionButtonTagline = ( { locationSettings, updateButtonStyle } ) => {
	const taglineIsAllowed =
		locationSettings.settings.style.hasOwnProperty( 'tagline' ) &&
		locationSettings.settings.style?.layout === 'horizontal';

	return (
		taglineIsAllowed && (
			<TabStylingSection
				title={ __( 'Tagline', 'woocommerce-paypal-payments' ) }
				className="ppcp-r-styling__section--rc"
			>
				<PayPalCheckboxGroup
					value={ [
						{
							value: 'tagline',
							label: __(
								'Enable Tagline',
								'woocommerce-paypal-payments'
							),
						},
					] }
					changeCallback={ ( newValue ) => {
						updateButtonStyle( 'tagline', newValue );
					} }
					currentValue={ locationSettings.settings.style.tagline }
				/>
			</TabStylingSection>
		)
	);
};

const SectionButtonPreview = ( { locationSettings } ) => {
	return (
		<PayPalScriptProvider
			options={ {
				clientId: 'test',
				merchantId: 'QTQX5NP6N9WZU',
				components: 'buttons,googlepay',
				'buyer-country': 'US',
				currency: 'USD',
			} }
		>
			<PayPalButtons
				style={ locationSettings.settings.style }
				forceReRender={ [ locationSettings.settings.style ] }
			>
				Error
			</PayPalButtons>
		</PayPalScriptProvider>
	);
};

export default TabStyling;
