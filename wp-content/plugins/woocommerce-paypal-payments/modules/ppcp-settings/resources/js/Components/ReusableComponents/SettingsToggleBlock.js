import { ToggleControl } from '@wordpress/components';
import { useRef } from '@wordpress/element';

import SpinnerOverlay from './SpinnerOverlay';

const SettingsToggleBlock = ( {
	isToggled,
	setToggled,
	isLoading = false,
	...props
} ) => {
	const toggleRef = useRef( null );
	const blockClasses = [ 'ppcp-r-toggle-block' ];

	if ( isLoading ) {
		blockClasses.push( 'ppcp--is-loading' );
	}

	const handleLabelClick = () => {
		if ( ! toggleRef.current || isLoading ) {
			return;
		}

		toggleRef.current.click();
		toggleRef.current.focus();
	};

	return (
		<div className={ blockClasses.join( ' ' ) }>
			<div className="ppcp-r-toggle-block__wrapper">
				<div className="ppcp-r-toggle-block__content">
					{ props?.label && (
						// eslint-disable-next-line jsx-a11y/click-events-have-key-events,jsx-a11y/no-static-element-interactions -- keyboard element is ToggleControl
						<div
							className="ppcp-r-toggle-block__content-label"
							onClick={ handleLabelClick }
						>
							{ props.label }
						</div>
					) }
					{ props?.description && (
						<p
							className="ppcp-r-toggle-block__content-description"
							dangerouslySetInnerHTML={ {
								__html: props.description,
							} }
						></p>
					) }
				</div>
				<div className="ppcp-r-toggle-block__switch">
					<ToggleControl
						ref={ toggleRef }
						checked={ isToggled }
						onChange={ ( newState ) => setToggled( newState ) }
						disabled={ isLoading }
					/>
				</div>
			</div>
			{ props.children && isToggled && (
				<div className="ppcp-r-toggle-block__toggled-content">
					{ isLoading && <SpinnerOverlay /> }
					{ props.children }
				</div>
			) }
		</div>
	);
};

export default SettingsToggleBlock;
