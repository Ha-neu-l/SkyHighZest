import { CheckboxControl } from '@wordpress/components';

export const PayPalCheckbox = ( props ) => {
	let isChecked = null;

	if ( Array.isArray( props.currentValue ) ) {
		isChecked = props.currentValue.includes( props.value );
	} else {
		isChecked = props.currentValue;
	}

	return (
		<div className="ppcp-r__checkbox">
			<CheckboxControl
				label={ props?.label ? props.label : '' }
				value={ props.value }
				checked={ isChecked }
				onChange={ ( checked ) =>
					handleCheckboxState( checked, props )
				}
			/>
		</div>
	);
};

export const PayPalCheckboxGroup = ( props ) => {
	const renderCheckboxGroup = () => {
		return props.value.map( ( checkbox ) => {
			return (
				<PayPalCheckbox
					label={ checkbox.label }
					value={ checkbox.value }
					key={ checkbox.value }
					currentValue={ props.currentValue }
					changeCallback={ props.changeCallback }
				/>
			);
		} );
	};

	return <>{ renderCheckboxGroup() }</>;
};

export const PayPalRdb = ( props ) => {
	return (
		<div className="ppcp-r__radio">
			<input
				id={ props?.id }
				className="ppcp-r__radio-value"
				type="radio"
				checked={ props.value === props.currentValue }
				name={ props.name }
				value={ props.value }
				onChange={ () => props.handleRdbState( props.value ) }
			/>
			<span className="ppcp-r__radio-presentation"></span>
		</div>
	);
};

export const PayPalRdbWithContent = ( props ) => {
	const className = [ 'ppcp-r__radio-wrapper' ];

	if ( props?.className ) {
		className.push( props.className );
	}

	return (
		<div className="ppcp-r__radio-outer-wrapper">
			<div className={ className }>
				<PayPalRdb { ...props } />
				<div className="ppcp-r__radio-content">
					<label htmlFor={ props?.id }>{ props.label }</label>
					{ props.description && (
						<p
							className="ppcp-r__radio-description"
							dangerouslySetInnerHTML={ {
								__html: props.description,
							} }
						/>
					) }
				</div>
			</div>
			{ props?.toggleAdditionalContent &&
				props.children &&
				props.value === props.currentValue && (
					<div className="ppcp-r__radio-content-additional">
						{ props.children }
					</div>
				) }
		</div>
	);
};

export const handleCheckboxState = ( checked, props ) => {
	let newValue = null;
	if ( ! Array.isArray( props.currentValue ) ) {
		newValue = checked;
	} else if ( checked ) {
		newValue = [ ...props.currentValue, props.value ];
	} else {
		newValue = props.currentValue.filter(
			( value ) => value !== props.value
		);
	}
	props.changeCallback( newValue );
};
