import data from '../../utils/data';
import { PayPalCheckbox, PayPalRdb } from './Fields';

const SelectBox = ( props ) => {
	let boxClassName = 'ppcp-r-select-box';

	if (
		props.value === props.currentValue ||
		( Array.isArray( props.currentValue ) &&
			props.currentValue.includes( props.value ) )
	) {
		boxClassName += ' selected';
	}

	return (
		<div className={ boxClassName }>
			{ props.type === 'radio' && (
				<PayPalRdb
					{ ...{
						...props,
						handleRdbState: props.changeCallback,
					} }
				/>
			) }
			{ props.type === 'checkbox' && (
				<PayPalCheckbox
					{ ...props }
				/>
			) }
			<div className="ppcp-r-select-box__content">
				<div className="ppcp-r-select-box__content-inner">
					<span className="ppcp-r-select-box__title">
						{ props.title }
					</span>
					<p className="ppcp-r-select-box__description">
						{ props.description }
					</p>
					{ props.children && (
						<div className="ppcp-r-select-box__additional-content">
							{ props.children }
						</div>
					) }
				</div>
			</div>
		</div>
	);
};

export default SelectBox;
