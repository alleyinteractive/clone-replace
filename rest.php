<?php
/**
 * REST APIs for ajax responder for the search system.
 *
 * @package Clone_Replace
 */

namespace Clone_Replace;

use WP_Query;

/**
 * A function to register custom REST routes on REST API init.
 */
function action_rest_api_init() {
	register_rest_route(
		'clone-replace/v1',
		'/search',
		[
			'callback'            => __NAMESPACE__ . '\rest_route_search',
			'methods'             => 'GET',
			'permission_callback' => function () {
				return is_user_logged_in();
			},
		]
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\action_rest_api_init' );

/**
 * A callback for /search/
 *
 * @param array $request Request variables.
 *
 * @return array An array of post objects.
 */
function rest_route_search( $request ) {
	$params    = $request->get_params();
	$search    = $params['search'];
	$post_type = $params['subtype'];

	$query = new WP_Query(
		[
			's'         => $search,
			'post_type' => $post_type,
		]
	);

	/**
	 * Filter out posts the user can't edit.
	 * While we are at it, format the keys for gutenberg and the PostSelector component specifically.
	 */
	$response = [];
	foreach ( $query->posts as $post ) {
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$result             = [];
			$result['id']       = $post->ID;
			$result['title']    = $post->post_title;
			$result['postType'] = $post->post_type;
			$response[]         = $result;
		}
	}

	return $response;
}
