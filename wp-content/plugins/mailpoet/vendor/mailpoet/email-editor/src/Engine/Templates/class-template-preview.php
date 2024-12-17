<?php
declare(strict_types = 1);
namespace MailPoet\EmailEditor\Engine\Templates;
if (!defined('ABSPATH')) exit;
use MailPoet\EmailEditor\Engine\Settings_Controller;
use MailPoet\EmailEditor\Engine\Theme_Controller;
use MailPoet\EmailEditor\Validator\Builder;
use WP_Theme_JSON;
class Template_Preview {
 private Theme_Controller $theme_controller;
 private Settings_Controller $settings_controller;
 private Templates $templates;
 public function __construct(
 Theme_Controller $theme_controller,
 Settings_Controller $settings_controller,
 Templates $templates
 ) {
 $this->theme_controller = $theme_controller;
 $this->settings_controller = $settings_controller;
 $this->templates = $templates;
 }
 public function initialize(): void {
 register_rest_field(
 'wp_template',
 'email_theme_css',
 array(
 'get_callback' => array( $this, 'get_email_theme_preview_css' ),
 'update_callback' => null,
 'schema' => Builder::string()->to_array(),
 )
 );
 }
 public function get_email_theme_preview_css( $template ): string {
 $editor_theme = clone $this->theme_controller->get_theme();
 $template_theme = $this->templates->get_block_template_theme( $template['id'], $template['wp_id'] );
 if ( is_array( $template_theme ) ) {
 $editor_theme->merge( new WP_Theme_JSON( $template_theme, 'custom' ) );
 }
 $editor_settings = $this->settings_controller->get_settings();
 $additional_css = '';
 foreach ( $editor_settings['styles'] as $style ) {
 $additional_css .= $style['css'];
 }
 // Set proper content width for previews.
 $layout_settings = $this->theme_controller->get_layout_settings();
 $additional_css .= ".is-root-container { width: {$layout_settings['contentSize']}; margin: 0 auto; }";
 return $editor_theme->get_stylesheet() . $additional_css;
 }
}
