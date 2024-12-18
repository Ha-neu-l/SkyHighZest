/**
 * Action Types: Define unique identifiers for actions across all store modules.
 *
 * @file
 */

export default {
	// Transient data.
	SET_TRANSIENT: 'COMMON:SET_TRANSIENT',

	// Persistent data.
	SET_PERSISTENT: 'COMMON:SET_PERSISTENT',
	HYDRATE: 'COMMON:HYDRATE',

	// Controls - always start with "DO_".
	DO_PERSIST_DATA: 'COMMON:DO_PERSIST_DATA',
	DO_MANUAL_CONNECTION: 'COMMON:DO_MANUAL_CONNECTION',
	DO_SANDBOX_LOGIN: 'COMMON:DO_SANDBOX_LOGIN',
};
