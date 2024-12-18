import { OnboardingStoreName } from './index';

export const addDebugTools = ( context, modules ) => {
	if ( ! context || ! context?.debug ) {
		return;
	}

	context.dumpStore = async () => {
		/* eslint-disable no-console */
		if ( ! console?.groupCollapsed ) {
			console.error( 'console.groupCollapsed is not supported.' );
			return;
		}

		modules.forEach( ( module ) => {
			const storeName = module.STORE_NAME;
			const storeSelector = `wp.data.select( '${ storeName }' )`;
			console.group( `[STORE] ${ storeSelector }` );

			const dumpStore = ( selector ) => {
				const contents = wp.data.select( storeName )[ selector ]();

				console.groupCollapsed( `.${ selector }()` );
				console.table( contents );
				console.groupEnd();
			};

			Object.keys( module.selectors ).forEach( dumpStore );

			console.groupEnd();
		} );
		/* eslint-enable no-console */
	};

	context.resetStore = () => {
		const onboarding = wp.data.dispatch( OnboardingStoreName );
		onboarding.reset();
		onboarding.persist();
	};

	context.startOnboarding = () => {
		const onboarding = wp.data.dispatch( OnboardingStoreName );
		onboarding.setCompleted( false );
		onboarding.setStep( 0 );
		onboarding.persist();
	};
};
