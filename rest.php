<?php
/**
 * REST APIs for ajax responder for the search system
 */

namespace Clone_Replace;

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
			'permission_callback' => '__return_true',
		],
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\action_rest_api_init' );

/**
 * A callback for /wp-json/kaiser-family-foundation/v1/csr-config.
 *
 * @return array An array containing information about Custom State Reports indicators.
 */
function rest_route_search( $request ) {
	$params = $request->get_params();
	$search = $params['s'];

	$query = new \WP_Query( [ 's' => $search ] );
	$query->posts = array_filter(
		$query->posts,
		function ( $post ) {
			return current_user_can( 'edit_post', $post->ID );
		}
	);

	return $query->posts;
}
