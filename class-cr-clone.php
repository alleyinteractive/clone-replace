<?php
/**
 * Clone posts, super simply.
 *
 * @package Clone_Replace
 */

if ( ! class_exists( 'CR_Clone' ) ) :

	/**
	 * Clone-Replace classes: CR_Clone class
	 */
	class CR_Clone {

		/**
		 * Contains a copy of the instance of this class, if initialized.
		 *
		 * @access private
		 * @var CR_Clone
		 */
		private static $instance;

		/**
		 * Constructor method. Prevents access of this class other than through ::instance().
		 */
		private function __construct() {
			/* Don't do anything, needs to be initialized via instance() method */
		}

		/**
		 * Magic clone method. Prevents access of this class other than through ::instance().
		 */
		public function __clone() {
			wp_die( "Please don't __clone CR_Clone" );
		}

		/**
		 * Magic wakeup method. Prevents access of this class other than through ::instance().
		 */
		public function __wakeup() {
			wp_die( "Please don't __wakeup CR_Clone" );
		}

		/**
		 * Returns the instance of this class. Initializes the instance if necessary.
		 *
		 * @return CR_Clone
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new CR_Clone();
				self::$instance->setup();
			}
			return self::$instance;
		}


		/**
		 * Setup our singleton: add hooks and set defaults
		 *
		 * @return void
		 */
		public function setup() {
			add_action( 'admin_post_clone_post', array( $this, 'action_admin_post' ) );

			add_filter( 'post_row_actions', array( $this, 'add_row_link' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'add_row_link' ), 10, 2 );

			add_action( 'clone-replace-actions', array( $this, 'add_editpage_link' ) );

			add_action( 'CR_Clone_inserted_post', array( $this, 'clone_terms' ), 10, 2 );
			add_action( 'CR_Clone_inserted_post', array( $this, 'clone_post_meta' ), 10, 2 );
			add_action( 'CR_Clone_inserted_post', array( $this, 'cleanup' ), 10, 2 );
		}


		/**
		 * Respond to the admin request to clone a post
		 *
		 * @return void
		 */
		public function action_admin_post() {
			if ( ! isset( $_GET['p'] ) ) {
				wp_die( esc_html__( 'You are trying to copy an invalid post', 'clone-replace' ) );
			}

			check_admin_referer( 'clone_post_' . intval( $_GET['p'] ) );

			$post_id = $this->clone_post( intval( $_GET['p'] ), apply_filters( 'CR_Clone_post_options', array() ) ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

			if ( ! $post_id ) {
				wp_die( esc_html__( 'There was an error copying this post', 'clone-replace' ) );
			}

			/**
			 * Filter Cloned Post Redirect URL.
			 *
			 * Modify the resulting redirect location after cloning a post.
			 *
			 * @since 0.2
			 *
			 * @param string $redirect_url URL string passed to wp_redirect
			 * @param int    $post_id      ID for newly cloned post.
			 */
			$redirect_url = apply_filters( // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
				'CR_Clone_redirect_url',
				admin_url( "post.php?post={$post_id}&action=edit" ),
				$post_id
			);

			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit();
		}


		/**
		 * Add a link to the actions row in post, page lists
		 *
		 * @param array   $actions An array of actions in the row.
		 * @param WP_Post $post    The post object for the current row.
		 * @return array
		 */
		public function add_row_link( $actions, $post ) {
			if ( current_user_can( get_post_type_object( get_post_type( $post ) )->cap->edit_post, $post->ID ) ) {
				$actions['cr-clone'] = '<a href="' . esc_url( $this->get_url( $post ) ) . '">' . esc_html__( 'Clone', 'clone-replace' ) . '</a>';
			}
			return $actions;
		}


		/**
		 * Add a link to the post edit page
		 *
		 * @return void
		 */
		public function add_editpage_link() {
			if ( isset( $_GET['post'] ) && intval( $_GET['post'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				global $post;
				?>
			<div id="clone-action">
				<?php if ( 'publish' !== $post->post_status ) : ?>
					<h4 style="margin-bottom:0.33em"><?php esc_html_e( 'Clone', 'clone-replace' ); ?></h4>
				<?php endif ?>
				<a href="<?php echo esc_url( $this->get_url( intval( $_GET['post'] ) ) ); ?>"><?php esc_html_e( 'Clone to a new draft', 'clone-replace' ); ?></a> <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			</div>
				<?php
		endif;
		}

		/**
		 * Get the URL for cloning a post
		 *
		 * @param object|int $post A post object or post ID.
		 * @return string The URL for replicating a post, properly nonced
		 */
		public function get_url( $post ) {
			if ( is_int( $post ) ) {
				$post_id = $post;
			} elseif ( is_object( $post ) ) {
				$post_id = $post->ID;
			} else {
				return;
			}

			return wp_nonce_url( admin_url( "admin-post.php?action=clone_post&p={$post_id}" ), 'clone_post_' . $post_id );
		}


		/**
		 * Copy an existing post to a new one
		 *
		 * @param int   $old_post_id The old post ID.
		 * @param array $args        Optional. Options for the new post.
		 * @return int the ID of the new post
		 */
		public function clone_post( $old_post_id, $args = array() ) {
			// Ensure that the user can create this post type.
			$post_type_object = get_post_type_object( get_post_type( $old_post_id ) );
			if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
				return;
			}

			if ( is_int( $old_post_id ) ) {
				$old_post = get_post( $old_post_id );
			}

			if ( ! is_object( $old_post ) ) {
				return false;
			}

			$args = wp_parse_args(
				$args,
				array(
					'post_status' => 'draft',
					'post_date'   => false,
				)
			);

			$post_args = array(
				'menu_order'     => $old_post->menu_order,
				'comment_status' => $old_post->comment_status,
				'ping_status'    => $old_post->ping_status,
				'post_author'    => get_current_user_id(),
				'post_content'   => wp_slash( $old_post->post_content ),
				'post_excerpt'   => $old_post->post_excerpt,
				'post_mime_type' => $old_post->post_mime_type,
				'post_parent'    => $old_post->post_parent,
				'post_password'  => $old_post->post_password,
				'post_status'    => $args['post_status'],
				'post_title'     => $old_post->post_title,
				'post_type'      => $old_post->post_type,
			);
			if ( $args['post_date'] ) {
				$post_args['post_date']     = $args['post_date'];
				$post_args['post_date_gmt'] = get_gmt_from_date( $args['post_date'] );
			}
			$post_args = apply_filters( 'CR_Clone_post_args', $post_args, $old_post, $args ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

			$post_id = wp_insert_post( $post_args );

			do_action( 'CR_Clone_inserted_post', $post_id, $old_post_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

			return $post_id;
		}


		/**
		 * Copy terms from one post to another
		 *
		 * @param int $to_post_id The ID of the post to copy to.
		 * @param int $from_post_id The ID of the post to copy from.
		 * @return void
		 */
		public function clone_terms( $to_post_id, $from_post_id ) {
			$post       = get_post( $to_post_id );
			$taxonomies = apply_filters( 'CR_Clone_taxonomies', get_object_taxonomies( $post->post_type ), $post ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

			foreach ( $taxonomies as $taxonomy ) {
				$terms = wp_get_object_terms(
					$from_post_id,
					$taxonomy,
					array(
						'orderby' => 'term_order',
						'fields'  => 'ids',
					)
				);
				if ( $terms && ! is_wp_error( $terms ) ) {
					$terms = array_map( 'intval', $terms );
					$terms = apply_filters( 'CR_Clone_terms', $terms, $to_post_id, $taxonomy ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
					wp_set_object_terms( $to_post_id, $terms, $taxonomy );
				}
			}

		}


		/**
		 * Copy post meta from one post to another
		 *
		 * @param int $to_post_id The ID of the post to copy to.
		 * @param int $from_post_id The ID of the post to copy from.
		 * @return void
		 */
		public function clone_post_meta( $to_post_id, $from_post_id ) {
			$post_meta = apply_filters( 'CR_Clone_post_meta', get_post_meta( $from_post_id ), $to_post_id, $from_post_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

			$ignored_meta = apply_filters( // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
				'CR_Clone_ignored_meta',
				array(
					'_edit_lock',
					'_edit_last',
					'_wp_old_slug',
					'_wp_trash_meta_time',
					'_wp_trash_meta_status',
					'_previous_revision',
					'_wpas_done_all',
					'_encloseme',
					'_cr_original_post',
					'_cr_replace_post_id',
					'_cr_replacing_post_id',
				)
			);

			if ( empty( $post_meta ) ) {
				return;
			}

			foreach ( $post_meta as $key => $value_array ) {
				if ( in_array( $key, $ignored_meta, true ) ) {
					continue;
				}

				foreach ( (array) $value_array as $value ) {
					add_post_meta( $to_post_id, $key, maybe_unserialize( $value ) );
				}
			}
		}


		/**
		 * Perform any cleanup operations following a post cloning
		 *
		 * @param int $post_id The ID of the post to copy to.
		 * @param int $old_post_id The ID of the post to copy from.
		 * @return void
		 */
		public function cleanup( $post_id, $old_post_id ) {
			// Record the original post ID so the clone can later replace the cloned.
			add_post_meta( $post_id, '_cr_original_post', $old_post_id );
		}

	}

	/**
	 * Helper function for returning an instance of CR_Clone.
	 *
	 * @return CR_Clone
	 */
	function CR_Clone() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return CR_Clone::instance();
	}

endif;
