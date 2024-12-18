const Container = ( { isCard = true, page, children } ) => {
	let className = 'ppcp-r-container';

	if ( isCard ) {
		className += ' ppcp-r-container--card';
	}

	if ( page ) {
		className += ` ppcp-r-container--${ page }`;
	}

	return <div className={ className }>{ children }</div>;
};

export default Container;
