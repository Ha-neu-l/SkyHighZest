<?php
/**
 * Message class to provide shortcuts for display various types of WordPress admin notices
 *
 * @package Kliken: Ads + Pixel for Meta
 */

namespace Kliken\FbWcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Message class
 */
class Message {
	/**
	 * Build a message to HTML code to be displayed on a page
	 *
	 * @param string $type            Message type.
	 * @param string $message_escaped Message, escaped, translated, to show to user.
	 * @param string $message_name    The name of the message. This is only used for message that can be permanently dismissible.
	 */
	private static function show_message( $type = 'info', $message_escaped = '', $message_name = '' ) {
		// Do not escape the message here as it's already escaped.
		// Mark this as XSS ok for PHPCodesniffer because the message MUST be escaped beforehand.
		printf(
			'<div class="notice notice-%1$s is-dismissible" %2$s><p>%3$s</p></div>',
			esc_attr( $type ),
			$message_name ? 'data-kk-fb-message-name="' . esc_attr( $message_name ) . '"' : '',
			$message_escaped // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WPCS: XSS ok.
		);
	}

	/**
	 * Show an info message
	 *
	 * @param string $message     Message, translated, to be displayed.
	 * @param string $message_name The name of the message. This is only used for message that can be permanently dismissible.
	 */
	public static function show_info( $message = '', $message_name = '' ) {
		if ( $message ) {
			self::show_message( 'info', $message, $message_name );
		}
	}

	/**
	 * Show a success message
	 *
	 * @param string $message     Message, translated, to be displayed.
	 * @param string $message_name The name of the message. This is only used for message that can be permanently dismissible.
	 */
	public static function show_success( $message = '', $message_name = '' ) {
		if ( $message ) {
			self::show_message( 'success', $message, $message_name );
		}
	}

	/**
	 * Show a warning message
	 *
	 * @param string $message     Message, translated, to be displayed.
	 * @param string $message_name The name of the message. This is only used for message that can be permanently dismissible.
	 */
	public static function show_warning( $message = '', $message_name = '' ) {
		if ( $message ) {
			self::show_message( 'warning', $message, $message_name );
		}
	}

	/**
	 * Show an error message
	 *
	 * @param string $message     Message, translated, to be displayed.
	 * @param string $message_name The name of the message. This is only used for message that can be permanently dismissible.
	 */
	public static function show_error( $message = '', $message_name = '' ) {
		if ( $message ) {
			self::show_message( 'error', $message, $message_name );
		}
	}
}
