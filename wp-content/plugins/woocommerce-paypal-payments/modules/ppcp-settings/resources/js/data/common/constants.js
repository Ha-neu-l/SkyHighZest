/**
 * Name of the module-store in the main Redux store.
 *
 * Helps to isolate data, used by reducer and selectors.
 *
 * @type {string}
 */
export const STORE_NAME = 'wc/paypal/common';

/**
 * REST path to hydrate data of this module by loading data from the WP DB..
 *
 * Used by resolvers.
 *
 * @type {string}
 */
export const REST_HYDRATE_PATH = '/wc/v3/wc_paypal/common';

/**
 * REST path to persist data of this module to the WP DB.
 *
 * Used by controls.
 *
 * @type {string}
 */
export const REST_PERSIST_PATH = '/wc/v3/wc_paypal/common';

/**
 * REST path to perform the manual connection check, using client ID and secret,
 *
 * Used by: Controls
 * See: ConnectManualRestEndpoint.php
 *
 * @type {string}
 */
export const REST_MANUAL_CONNECTION_PATH = '/wc/v3/wc_paypal/connect_manual';

/**
 * REST path to generate an ISU URL for the sandbox-login.
 *
 * Used by: Controls
 * See: LoginLinkRestEndpoint.php
 *
 * @type {string}
 */
export const REST_SANDBOX_CONNECTION_PATH = '/wc/v3/wc_paypal/login_link';
