/**
 * Hooks: Provide the main API for components to interact with the store.
 *
 * These encapsulate store interactions, offering a consistent interface.
 * Hooks simplify data access and manipulation for components.
 *
 * @file
 */

import { useSelect, useDispatch } from '@wordpress/data';

import { PRODUCT_TYPES } from '../constants';
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
		setStep,
		setCompleted,
		setIsCasualSeller,
		setAreOptionalPaymentMethodsEnabled,
		setProducts,
	} = useDispatch( STORE_NAME );

	// Read-only flags.
	const flags = useSelect( ( select ) => select( STORE_NAME ).flags(), [] );

	// Transient accessors.
	const isReady = useTransient( 'isReady' );

	// Persistent accessors.
	const step = usePersistent( 'step' );
	const completed = usePersistent( 'completed' );
	const isCasualSeller = usePersistent( 'isCasualSeller' );
	const areOptionalPaymentMethodsEnabled = usePersistent(
		'areOptionalPaymentMethodsEnabled'
	);
	const products = usePersistent( 'products' );

	const savePersistent = async ( setter, value ) => {
		setter( value );
		await persist();
	};

	return {
		flags,
		isReady,
		step,
		setStep: ( value ) => {
			return savePersistent( setStep, value );
		},
		completed,
		setCompleted: ( state ) => {
			return savePersistent( setCompleted, state );
		},
		isCasualSeller,
		setIsCasualSeller: ( value ) => {
			return savePersistent( setIsCasualSeller, value );
		},
		areOptionalPaymentMethodsEnabled,
		setAreOptionalPaymentMethodsEnabled: ( value ) => {
			return savePersistent( setAreOptionalPaymentMethodsEnabled, value );
		},
		products,
		setProducts: ( activeProducts ) => {
			const validProducts = activeProducts.filter( ( item ) =>
				Object.values( PRODUCT_TYPES ).includes( item )
			);
			return savePersistent( setProducts, validProducts );
		},
	};
};

export const useBusiness = () => {
	const { isCasualSeller, setIsCasualSeller } = useHooks();

	return { isCasualSeller, setIsCasualSeller };
};

export const useProducts = () => {
	const { products, setProducts } = useHooks();

	return { products, setProducts };
};

export const useOptionalPaymentMethods = () => {
	const {
		areOptionalPaymentMethodsEnabled,
		setAreOptionalPaymentMethodsEnabled,
	} = useHooks();

	return {
		areOptionalPaymentMethodsEnabled,
		setAreOptionalPaymentMethodsEnabled,
	};
};

export const useSteps = () => {
	const { flags, isReady, step, setStep, completed, setCompleted } =
		useHooks();

	return { flags, isReady, step, setStep, completed, setCompleted };
};
