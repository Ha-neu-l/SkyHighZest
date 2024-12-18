/**
 * Action Creators: Define functions to create action objects.
 *
 * These functions update state or trigger side effects (e.g., async operations).
 * Actions are categorized as Transient, Persistent, or Side effect.
 *
 * @file
 */

import { select } from '@wordpress/data';

import ACTION_TYPES from './action-types';
import { STORE_NAME } from './constants';

/**
 * @typedef {Object} Action An action object that is handled by a reducer or control.
 * @property {string}  type    - The action type.
 * @property {Object?} payload - Optional payload for the action.
 */

/**
 * Persistent. Set the full onboarding details, usually during app initialization.
 *
 * @param {{data: {}, flags?: {}}} payload
 * @return {Action} The action.
 */
export const hydrate = ( payload ) => ( {
	type: ACTION_TYPES.HYDRATE,
	payload,
} );

/**
 * Transient. Marks the onboarding details as "ready", i.e., fully initialized.
 *
 * @param {boolean} isReady
 * @return {Action} The action.
 */
export const setIsReady = ( isReady ) => ( {
	type: ACTION_TYPES.SET_TRANSIENT,
	payload: { isReady },
} );

/**
 * Transient. Changes the "saving" flag.
 *
 * @param {boolean} isSaving
 * @return {Action} The action.
 */
export const setIsSaving = ( isSaving ) => ( {
	type: ACTION_TYPES.SET_TRANSIENT,
	payload: { isSaving },
} );

/**
 * Transient. Changes the "manual connection is busy" flag.
 *
 * @param {boolean} isBusy
 * @return {Action} The action.
 */
export const setIsBusy = ( isBusy ) => ( {
	type: ACTION_TYPES.SET_TRANSIENT,
	payload: { isBusy },
} );

/**
 * Persistent. Sets the sandbox mode on or off.
 *
 * @param {boolean} useSandbox
 * @return {Action} The action.
 */
export const setSandboxMode = ( useSandbox ) => ( {
	type: ACTION_TYPES.SET_PERSISTENT,
	payload: { useSandbox },
} );

/**
 * Persistent. Toggles the "Manual Connection" mode on or off.
 *
 * @param {boolean} useManualConnection
 * @return {Action} The action.
 */
export const setManualConnectionMode = ( useManualConnection ) => ( {
	type: ACTION_TYPES.SET_PERSISTENT,
	payload: { useManualConnection },
} );

/**
 * Persistent. Changes the "client ID" value.
 *
 * @param {string} clientId
 * @return {Action} The action.
 */
export const setClientId = ( clientId ) => ( {
	type: ACTION_TYPES.SET_PERSISTENT,
	payload: { clientId },
} );

/**
 * Persistent. Changes the "client secret" value.
 *
 * @param {string} clientSecret
 * @return {Action} The action.
 */
export const setClientSecret = ( clientSecret ) => ( {
	type: ACTION_TYPES.SET_PERSISTENT,
	payload: { clientSecret },
} );

/**
 * Side effect. Saves the persistent details to the WP database.
 *
 * @return {Action} The action.
 */
export const persist = function* () {
	const data = yield select( STORE_NAME ).persistentData();

	yield { type: ACTION_TYPES.DO_PERSIST_DATA, data };
};

/**
 * Side effect. Initiates the sandbox login ISU.
 *
 * @return {Action} The action.
 */
export const connectViaSandbox = function* () {
	yield setIsBusy( true );

	const result = yield { type: ACTION_TYPES.DO_SANDBOX_LOGIN };
	yield setIsBusy( false );

	return result;
};

/**
 * Side effect. Initiates a manual connection attempt using the provided client ID and secret.
 *
 * @return {Action} The action.
 */
export const connectViaIdAndSecret = function* () {
	const { clientId, clientSecret, useSandbox } =
		yield select( STORE_NAME ).persistentData();

	yield setIsBusy( true );

	const result = yield {
		type: ACTION_TYPES.DO_MANUAL_CONNECTION,
		clientId,
		clientSecret,
		useSandbox,
	};
	yield setIsBusy( false );

	return result;
};
