import { useEffect } from '@wordpress/element';
import { Icon } from '@wordpress/components';
import { chevronDown, chevronUp } from '@wordpress/icons';

import { useState } from 'react';

const Accordion = ( {
	title,
	initiallyOpen = null,
	className = '',
	id = '',
	children,
} ) => {
	const determineInitialState = () => {
		if ( id && initiallyOpen === null ) {
			return window.location.hash === `#${ id }`;
		}
		return !! initiallyOpen;
	};

	const [ isOpen, setIsOpen ] = useState( determineInitialState );

	useEffect( () => {
		const handleHashChange = () => {
			if ( id && window.location.hash === `#${ id }` ) {
				setIsOpen( true );
			}
		};

		window.addEventListener( 'hashchange', handleHashChange );

		return () => {
			window.removeEventListener( 'hashchange', handleHashChange );
		};
	}, [ id ] );

	const toggleOpen = ( ev ) => {
		setIsOpen( ! isOpen );
		ev?.preventDefault();
		return false;
	};

	const wrapperClasses = [ 'ppcp-r-accordion' ];
	if ( className ) {
		wrapperClasses.push( className );
	}
	if ( isOpen ) {
		wrapperClasses.push( 'ppcp--is-open' );
	}

	return (
		<div className={ wrapperClasses.join( ' ' ) } id={ id }>
			<button
				onClick={ toggleOpen }
				className="ppcp-r-accordion--title"
				type="button"
			>
				<span>{ title }</span>
				<Icon icon={ isOpen ? chevronUp : chevronDown } />
			</button>
			{ isOpen && (
				<div className="ppcp-r-accordion--content">{ children }</div>
			) }
		</div>
	);
};

export default Accordion;
