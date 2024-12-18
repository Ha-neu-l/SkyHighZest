/**
 * Controls: Implement side effects, typically asynchronous operations.
 *
 * Controls use ACTION_TYPES keys as identifiers.
 * They are triggered by corresponding actions and handle external interactions.
 *
 * @file
 */

import apiFetch from '@wordpress/api-fetch';

import {
	REST_PERSIST_PATH,
	REST_MANUAL_CONNECTION_PATH,
	REST_SANDBOX_CONNECTION_PATH,
} from './constants';
import ACTION_TYPES from './action-types';

export const controls = {
	async [ ACTION_TYPES.DO_PERSIST_DATA ]( { data } ) {
		try {
			return await apiFetch( {
				path: REST_PERSIST_PATH,
				method: 'POST',
				data,
			} );
		} catch ( error ) {
			console.error( 'Error saving data.', error );
		}
	},

	async [ ACTION_TYPES.DO_SANDBOX_LOGIN ]() {
		let result = null;

		try {
			result = await apiFetch( {
				path: REST_SANDBOX_CONNECTION_PATH,
				method: 'POST',
				data: {
					environment: 'sandbox',
					products: [ 'EXPRESS_CHECKOUT' ],
				},
			} );
		} catch ( e ) {
			result = {
				success: false,
				error: e,
			};
		}

		return result;
	},

	async [ ACTION_TYPES.DO_MANUAL_CONNECTION ]( {
		clientId,
		clientSecret,
		useSandbox,
	} ) {
		let result = null;

		try {
			result = await apiFetch( {
				path: REST_MANUAL_CONNECTION_PATH,
				method: 'POST',
				data: {
					clientId,
					clientSecret,
					useSandbox,
				},
			} );
		} catch ( e ) {
			result = {
				success: false,
				error: e,
			};
		}

		return result;
	},
};
