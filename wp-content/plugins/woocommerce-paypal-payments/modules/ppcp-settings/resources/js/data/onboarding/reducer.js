/**
 * Reducer: Defines store structure and state updates for this module.
 *
 * Manages both transient (temporary) and persistent (saved) state.
 * The initial state must define all properties, as dynamic additions are not supported.
 *
 * @file
 */

import { createReducer, createSetters } from '../utils';
import ACTION_TYPES from './action-types';

// Store structure.

const defaultTransient = {
	isReady: false,

	// Read only values, provided by the server.
	flags: {
		canUseCasualSelling: false,
		canUseVaulting: false,
		canUseCardPayments: false,
	},
};

const defaultPersistent = {
	completed: false,
	step: 0,
	isCasualSeller: null, // null value will uncheck both options in the UI.
	areOptionalPaymentMethodsEnabled: true,
	products: [],
};

// Reducer logic.

const [ setTransient, setPersistent ] = createSetters(
	defaultTransient,
	defaultPersistent
);

const onboardingReducer = createReducer( defaultTransient, defaultPersistent, {
	[ ACTION_TYPES.SET_TRANSIENT ]: ( state, payload ) =>
		setTransient( state, payload ),

	[ ACTION_TYPES.SET_PERSISTENT ]: ( state, payload ) =>
		setPersistent( state, payload ),

	[ ACTION_TYPES.RESET ]: ( state ) =>
		setPersistent( state, defaultPersistent ),

	[ ACTION_TYPES.HYDRATE ]: ( state, payload ) => {
		const newState = setPersistent( state, payload.data );

		// Flags are not updated by `setPersistent()`.
		if ( payload.flags ) {
			newState.flags = { ...newState.flags, ...payload.flags };
		}

		return newState;
	},
} );

export default onboardingReducer;
