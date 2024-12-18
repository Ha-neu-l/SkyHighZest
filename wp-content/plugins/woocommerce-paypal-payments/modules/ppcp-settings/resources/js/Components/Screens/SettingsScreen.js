import { getSettingsTabs } from './tabs';
import SettingsNavigation from './SettingsNavigation';
import Container from '../ReusableComponents/Container';
import TabNavigation from '../ReusableComponents/TabNavigation';

const SettingsScreen = () => {
	const tabs = getSettingsTabs();

	return (
		<>
			<SettingsNavigation />
			<Container page="settings">
				<TabNavigation tabs={ tabs }></TabNavigation>
			</Container>
		</>
	);
};

export default SettingsScreen;
