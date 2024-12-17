<?php
/**
 * Uninstall script.
 *
 * @package Kliken: Ads + Pixel for Meta
 */

defined( 'ABSPATH' ) || exit;

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'kk_fb_wc_settings' );
delete_option( 'woocommerce_kk_fb_wc_integration_settings' );

delete_site_transient( 'kk_fb_wc_activation_redirect' );

delete_site_transient( 'kk_fb_wc_welcome_message' );
delete_option( 'kk_fb_wc_welcome_message_dismissed' );
delete_site_transient( 'kk_fb_wc_bootstrap_message' );
delete_option( 'kk_fb_wc_bootstrap_message_dismissed' );
