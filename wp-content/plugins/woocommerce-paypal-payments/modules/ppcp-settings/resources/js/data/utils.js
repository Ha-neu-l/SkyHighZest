/**
 * Updates an object with new values, filtering based on allowed keys.
 *
 * Helper method used by createSetters.
 *
 * @param {Object} oldObject   The original object to update.
 * @param {Object} newValues   The new values to apply.
 * @param {Object} allowedKeys An object whose keys define the allowed keys to update.
 * @return {Object} A new object with the allowed updates applied.
 */
const updateObject = ( oldObject, newValues, allowedKeys = {} ) => ( {
	...oldObject,
	...Object.keys( newValues ).reduce( ( acc, key ) => {
		if ( key in allowedKeys ) {
			acc[ key ] = newValues[ key ];
		}
		return acc;
	}, {} ),
} );

/**
 * Creates setter functions for updating state.
 *
 * Only properties that are present in the "defaultTransient" or "defaultPersistent"
 * arguments can be updated by the setters. Make sure that the default state defines
 * ALL possible properties.
 *
 * @param {Object} defaultTransient  Object defining initial transient values.
 * @param {Object} defaultPersistent Object defining initial persistent values.
 * @return {[Function, Function]} An array containing setTransient and setPersistent functions.
 */
export const createSetters = ( defaultTransient, defaultPersistent ) => {
	const setTransient = ( oldState, newValues = {} ) =>
		updateObject( oldState, newValues, defaultTransient );

	const setPersistent = ( oldState, newValues = {} ) => ( {
		...oldState,
		data: updateObject( oldState.data, newValues, defaultPersistent ),
	} );

	return [ setTransient, setPersistent ];
};

/**
 * Creates a reducer function with predefined action handlers.
 *
 * @param {Object} defaultTransient  Object defining initial transient values.
 * @param {Object} defaultPersistent Object defining initial persistent values.
 * @param {Object} handlers          An object mapping action types to handler functions.
 * @return {Function} A reducer function.
 */
export const createReducer = (
	defaultTransient,
	defaultPersistent,
	handlers
) => {
	if ( Object.hasOwnProperty.call( defaultTransient, 'data' ) ) {
		throw new Error(
			'The transient state cannot contain a "data" property.'
		);
	}

	const initialState = {
		...defaultTransient,
		data: defaultPersistent,
	};

	return function reducer( state = initialState, action ) {
		if ( Object.hasOwnProperty.call( handlers, action.type ) ) {
			return handlers[ action.type ]( state, action.payload ?? {} );
		}

		return state;
	};
};
