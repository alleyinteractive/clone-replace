<?php
/**
 * REST API: WP_REST_Clone_Replace_Search_Handler class
 *
 * @package Clone_Replace
 * @subpackage REST_API
 */

/**
 * Clone and Replace Search Handler.
 *
 * @see WP_REST_Post_Search_Handler
 */
class WP_REST_Clone_Replace_Search_Handler extends WP_REST_Post_Search_Handler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->type = 'clone_replace';
	}

	/**
	 * Searches the object type content for a given search request.
	 *
	 * @param WP_REST_Request $request Full REST request.
	 * @return array Associative array containing an `WP_REST_Search_Handler::RESULT_IDS` containing
	 *               an array of found IDs and `WP_REST_Search_Handler::RESULT_TOTAL` containing the
	 *               total count for the matching search results.
	 */
	public function search_items( WP_REST_Request $request ) {
		$current_post_ud = absint( $request->get_param( 'current_post_id' ) );

		$query_args = [
			'post__not_in'        => [ $current_post_ud ],
			'post_type'           => get_post_type( $current_post_ud ),
			'post_status'         => 'publish',
			'paged'               => absint( $request->get_param( 'page' ) ),
			'posts_per_page'      => absint( $request->get_param( 'per_page' ) ),
			'orderby'             => 'post_date',
			'order'               => 'DESC',
			'suppress_filters'    => false,
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'fields'              => 'ids',
		];

		if ( ! empty( $request['search'] ) ) {
			$query_args['s'] = $request->get_param( 'search' );
		}

		/**
		 * Filters the query arguments for a search request.
		 *
		 * Enables adding extra arguments or setting defaults for a post search request.
		 *
		 * @param array           $query_args Key value array of query var to query value.
		 * @param WP_REST_Request $request    The request used.
		 */
		$query_args = apply_filters( 'CR_Replace_rest_post_search_query', $query_args, $request );

		$query     = new WP_Query();
		$found_ids = $query->query( $query_args );
		$total     = $query->found_posts;

		return [
			self::RESULT_IDS   => $found_ids,
			self::RESULT_TOTAL => $total,
		];
	}
}
