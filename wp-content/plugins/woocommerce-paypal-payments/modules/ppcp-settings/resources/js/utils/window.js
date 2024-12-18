/**
 * Opens the provided URL, preferably in a popup window.
 *
 * Popups are usually only supported on desktop devices, when the browser is not in fullscreen mode.
 *
 * @param {string}  url
 * @param {Object}  options
 * @param {string}  options.name
 * @param {number}  options.width
 * @param {number}  options.height
 * @param {boolean} options.resizeable
 * @return {null|Window} Popup window instance, or null.
 */
export const openPopup = (
	url,
	{ name = '_blank', width = 450, height = 720, resizeable = false } = {}
) => {
	width = Math.max( 100, Math.min( window.screen.width - 40, width ) );
	height = Math.max( 100, Math.min( window.screen.height - 40, height ) );

	const left = ( window.screen.width - width ) / 2;
	const top = ( window.screen.height - height ) / 2;

	const features = [
		`width=${ width }`,
		`height=${ height }`,
		`left=${ left }`,
		`top=${ top }`,
		`resizable=${ resizeable ? 'yes' : 'no' }`,
		`scrollbars=yes`,
		`status=no`,
	];

	const popup = window.open( url, name, features.join( ',' ) );

	if ( popup && ! popup.closed ) {
		popup.focus();
		return popup;
	}

	return null;
};
