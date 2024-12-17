<?php
declare(strict_types=1);
if (!defined('ABSPATH')) exit;
class IntegrationTester extends \Codeception\Actor {
 use _generated\IntegrationTesterActions;
 private $wp_term_ids = array();
 private $created_comment_ids = array();
 private $posts = array();
 public function create_post( array $params ): \WP_Post {
 $post_id = wp_insert_post( $params );
 if ( $post_id instanceof WP_Error ) {
 throw new \Exception( 'Failed to create post' );
 }
 $post = get_post( $post_id );
 if ( ! $post instanceof WP_Post ) {
 throw new \Exception( 'Failed to fetch the post' );
 }
 $this->posts[] = $post;
 return $post;
 }
 public function cleanup(): void {
 $this->delete_posts();
 }
 private function delete_posts(): void {
 foreach ( $this->posts as $post ) {
 wp_delete_post( $post->ID, true );
 }
 }
}
