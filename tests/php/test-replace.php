<?php

/**
 * Tests for replacement functionality.
 */

class Test_Replace extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		// Set the current user to an editor so we can check permissions correctly.
		$user = $this->factory->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $user );

		add_filter( 'wp_redirect', '__return_false' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'wp_redirect', '__return_false' );
	}

	function test_replace_on_publish() {
		$post_id_a = $this->factory->post->create([
			'post_title' => 'Original Post Title',
			'post_content' => 'Original Post Content',
			'post_status' => 'publish',
		]);

		$post_id_b = $this->factory->post->create([
			'post_title' => 'Updated Post Title',
			'post_content' => 'Updated Post Content',
			'post_status' => 'draft',
			'meta_input' => [
				'_cr_replace_post_id' => $post_id_a,
			],
		]);

		$post = get_post( $post_id_a );
		$this->assertSame( 'Original Post Title', $post->post_title );
		$this->assertSame( 'Original Post Content', $post->post_content );
		$this->assertSame( 'original-post-title', $post->post_name );

		wp_publish_post( $post_id_b );

		$post = get_post( $post_id_b );
		$this->assertNull( $post );

		$post = get_post( $post_id_a );
		$this->assertSame( 'Updated Post Title', $post->post_title );
		$this->assertSame( 'Updated Post Content', $post->post_content );
		$this->assertSame( 'original-post-title', $post->post_name );
	}

	function test_replace_on_scheduled_publish() {
		$post_id_a = $this->factory->post->create([
			'post_title' => 'Original Post Title',
			'post_content' => 'Original Post Content',
			'post_status' => 'publish',
		]);

		$post_id_b = $this->factory->post->create([
			'post_title' => 'Updated Post Title',
			'post_content' => 'Updated Post Content',
			'post_status' => 'draft',
			'post_date' => date( 'Y-m-d h:i:s' ),
			'meta_input' => [
				'_cr_replace_post_id' => $post_id_a,
			],
		]);

		// WP will just publish a post if you try to set "future" with a current/past date.
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'future' ), array( 'ID' => $post_id_b ) );
		clean_post_cache( $post_id_b );

		$post = get_post( $post_id_a );
		$this->assertSame( 'Original Post Title', $post->post_title );
		$this->assertSame( 'Original Post Content', $post->post_content );
		$this->assertSame( 'original-post-title', $post->post_name );

		check_and_publish_future_post( $post_id_b );

		$post = get_post( $post_id_b );
		$this->assertNull( $post );

		$post = get_post( $post_id_a );
		$this->assertSame( 'Updated Post Title', $post->post_title );
		$this->assertSame( 'Updated Post Content', $post->post_content );
		$this->assertSame( 'original-post-title', $post->post_name );
	}
}
