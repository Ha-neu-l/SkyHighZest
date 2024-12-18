import { useCallback, useEffect, useState } from '@wordpress/element';
import { TabPanel } from '@wordpress/components';
import { getQuery, updateQueryString } from '@woocommerce/navigation';

const TabNavigation = ( { tabs } ) => {
	const { panel } = getQuery();

	const isValidTab = ( tabsList, checkTab ) => {
		return tabsList.some( ( tab ) => tab.name === checkTab );
	};

	const getValidInitialPanel = () => {
		if ( ! panel || ! isValidTab( tabs, panel ) ) {
			return tabs[ 0 ].name;
		}
		return panel;
	};

	const [ activePanel, setActivePanel ] = useState( getValidInitialPanel );

	const updateActivePanel = useCallback(
		( tabName ) => {
			if ( isValidTab( tabs, tabName ) ) {
				setActivePanel( tabName );
			} else {
				console.warn( `Invalid tab name: ${ tabName }` );
			}
		},
		[ tabs ]
	);

	useEffect( () => {
		updateQueryString( { panel: activePanel }, '/', getQuery() );
	}, [ activePanel ] );

	return (
		<TabPanel
			className={ `ppcp-r-tabs ${ activePanel }` }
			initialTabName={ activePanel }
			onSelect={ updateActivePanel }
			tabs={ tabs }
		>
			{ ( tab ) => {
				return tab.component || <>{ tab.title ?? tab.name }</>;
			} }
		</TabPanel>
	);
};

export default TabNavigation;
