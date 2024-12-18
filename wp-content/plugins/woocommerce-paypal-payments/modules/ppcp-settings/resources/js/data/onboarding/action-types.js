/**
 * Action Types: Define unique identifiers for actions across all store modules.
 *
 * @file
 */

export default {
	// Transient data.
	SET_TRANSIENT: 'ONBOARDING:SET_TRANSIENT',

	// Persistent data.
	SET_PERSISTENT: 'ONBOARDING:SET_PERSISTENT',
	RESET: 'ONBOARDING:RESET',
	HYDRATE: 'ONBOARDING:HYDRATE',

	// Controls - always start with "DO_".
	DO_PERSIST_DATA: 'ONBOARDING:DO_PERSIST_DATA',
};
