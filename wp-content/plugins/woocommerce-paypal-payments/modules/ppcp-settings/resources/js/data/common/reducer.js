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
	isBusy: false,
};

const defaultPersistent = {
	useSandbox: false,
	useManualConnection: false,
	clientId: '',
	clientSecret: '',
};

// Reducer logic.

const [ setTransient, setPersistent ] = createSetters(
	defaultTransient,
	defaultPersistent
);

const commonReducer = createReducer( defaultTransient, defaultPersistent, {
	[ ACTION_TYPES.SET_TRANSIENT ]: ( state, action ) =>
		setTransient( state, action ),

	[ ACTION_TYPES.SET_PERSISTENT ]: ( state, action ) =>
		setPersistent( state, action ),

	[ ACTION_TYPES.HYDRATE ]: ( state, payload ) =>
		setPersistent( state, payload.data ),
} );

export default commonReducer;
