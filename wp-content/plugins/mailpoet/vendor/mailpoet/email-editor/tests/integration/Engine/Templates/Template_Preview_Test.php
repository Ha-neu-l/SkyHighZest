<?php
declare(strict_types = 1);
namespace MailPoet\EmailEditor\Engine\Templates;
if (!defined('ABSPATH')) exit;
class Template_Preview_Test extends \MailPoetTest {
 private Template_Preview $template_preview;
 public function _before() {
 parent::_before();
 $this->template_preview = $this->di_container->get( Template_Preview::class );
 }
 public function testItCanGetThemePreviewCss(): void {
 $template_id = 'mailpoet/mailpoet//simple-light';
 $result = $this->template_preview->get_email_theme_preview_css(
 array(
 'id' => $template_id,
 'wp_id' => null,
 )
 );
 verify( $result )->stringContainsString( 'Styles for the email editor.' );
 verify( $result )->stringContainsString( 'is-layout-email-flex' );
 verify( $result )->stringContainsString( '.is-root-container { width: 660px; margin: 0 auto; }' ); // Styles for both the email editor and renderer.
 }
}
