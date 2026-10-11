<?php
/**
 * Smoke tests for Chiramise.
 *
 * @package chiramise
 */

/**
 * Check that the plugin is loaded and its main features work.
 */
class Chiramise_Plugin_Test extends WP_UnitTestCase {

	/**
	 * Enable Chiramise for posts.
	 */
	public function set_up() {
		parent::set_up();
		update_option( 'chiramise_support_post_type', [ 'post' ] );
	}

	/**
	 * Plugin bootstrap defines version and loads functions.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'CHIRAMISE_VERSION' ) );
		foreach ( [
			'chiramise_capability',
			'chiramise_supported',
			'chiramise_can_read',
			'chiramise_should_check',
			'chiramise_get_segments',
			'chiramise_filter_content',
			'chiramise_get_toc',
		] as $function ) {
			$this->assertTrue( function_exists( $function ), $function . ' should exist.' );
		}
	}

	/**
	 * Hooks are registered.
	 */
	public function test_hooks_registered() {
		$this->assertSame( 1, has_filter( 'the_content', 'chiramise_filter_content' ) );
		$this->assertTrue( shortcode_exists( 'Chiramise' ) );
		$this->assertArrayHasKey( '/chiramise/v1/content/(?P<post_id>\d+)/?', rest_get_server()->get_routes() );
	}

	/**
	 * Post type support follows the option.
	 */
	public function test_supported_post_type() {
		$this->assertTrue( chiramise_supported( 'post' ) );
		$this->assertFalse( chiramise_supported( 'page' ) );
	}

	/**
	 * Content is split by the more tag and restricted for guests.
	 */
	public function test_content_is_restricted_for_guest() {
		$post = self::factory()->post->create_and_get( [
			'post_content' => 'Public part<!--more-->Members only',
		] );
		$this->assertSame( [ 'Public part', 'Members only' ], chiramise_get_segments( $post ) );
		$this->assertTrue( chiramise_should_check( $post ) );

		wp_set_current_user( 0 );
		$this->assertFalse( chiramise_can_read( $post ) );

		$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );
		$this->assertTrue( chiramise_can_read( $post ) );
	}
}
