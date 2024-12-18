/**
 * Hooks: Provide the main API for components to interact with the store.
 *
 * These encapsulate store interactions, offering a consistent interface.
 * Hooks simplify data access and manipulation for components.
 *
 * @file
 */

import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

import { STORE_NAME } from './constants';

const useTransient = ( key ) =>
	useSelect(
		( select ) => select( STORE_NAME ).transientData()?.[ key ],
		[ key ]
	);

const usePersistent = ( key ) =>
	useSelect(
		( select ) => select( STORE_NAME ).persistentData()?.[ key ],
		[ key ]
	);

const useHooks = () => {
	const {
		persist,
		setSandboxMode,
		setManualConnectionMode,
		setClientId,
		setClientSecret,
		connectViaSandbox,
		connectViaIdAndSecret,
	} = useDispatch( STORE_NAME );

	// Transient accessors.
	const isReady = useTransient( 'isReady' );

	// Persistent accessors.
	const clientId = usePersistent( 'clientId' );
	const clientSecret = usePersistent( 'clientSecret' );
	const isSandboxMode = usePersistent( 'useSandbox' );
	const isManualConnectionMode = usePersistent( 'useManualConnection' );

	const savePersistent = async ( setter, value ) => {
		setter( value );
		await persist();
	};

	return {
		isReady,
		isSandboxMode,
		setSandboxMode: ( state ) => {
			return savePersistent( setSandboxMode, state );
		},
		isManualConnectionMode,
		setManualConnectionMode: ( state ) => {
			return savePersistent( setManualConnectionMode, state );
		},
		clientId,
		setClientId: ( value ) => {
			return savePersistent( setClientId, value );
		},
		clientSecret,
		setClientSecret: ( value ) => {
			return savePersistent( setClientSecret, value );
		},
		connectViaSandbox,
		connectViaIdAndSecret,
	};
};

export const useBusyState = () => {
	const { setIsBusy } = useDispatch( STORE_NAME );
	const isBusy = useTransient( 'isBusy' );

	return {
		isBusy,
		setIsBusy: useCallback( ( busy ) => setIsBusy( busy ), [ setIsBusy ] ),
	};
};

export const useSandbox = () => {
	const { isSandboxMode, setSandboxMode, connectViaSandbox } = useHooks();

	return { isSandboxMode, setSandboxMode, connectViaSandbox };
};

export const useManualConnection = () => {
	const {
		isManualConnectionMode,
		setManualConnectionMode,
		clientId,
		setClientId,
		clientSecret,
		setClientSecret,
		connectViaIdAndSecret,
	} = useHooks();

	return {
		isManualConnectionMode,
		setManualConnectionMode,
		clientId,
		setClientId,
		clientSecret,
		setClientSecret,
		connectViaIdAndSecret,
	};
};
