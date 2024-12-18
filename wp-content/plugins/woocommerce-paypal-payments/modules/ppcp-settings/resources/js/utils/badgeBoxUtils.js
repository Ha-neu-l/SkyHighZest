import { __ } from '@wordpress/i18n';

const generatePriceText = ( type, selectedCountryPrice, storeCurrency ) => {
	if ( ! selectedCountryPrice || ! selectedCountryPrice[ type ] ) {
		console.warn( `Invalid type or price data for: ${ type }` );
		return '';
	}

	const percentage = selectedCountryPrice[ type ].toFixed( 2 );
	const fixedFee = `${ selectedCountryPrice.currencySymbol }${ selectedCountryPrice.fixedFee }`;

	return __(
		`from ${ percentage }% + ${ fixedFee } ${ storeCurrency }<sup>1</sup>`,
		'woocommerce-paypal-payments'
	);
};

export default generatePriceText;
