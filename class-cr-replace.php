<?php
/**
 * Contains a class to handle replacing one post with another.
 *
 * @package Clone_Replace
 *
 * @todo restore post meta with revision
 * @todo display post meta with revision when browsing revisions
 * @todo add ability to clone a revision
 * @todo add GUI to replace one published post with another
 */

if ( ! class_exists( 'CR_Replace' ) ) :

	/**
	 * Clone-Replace classes: CR_Replace class
	 */
	class CR_Replace {

		/**
		 * Contains a copy of the instance of this class, if initialized.
		 *
		 * @access private
		 * @var CR_Replace
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
			wp_die( "Please don't __clone CR_Replace" );
		}

		/**
		 * Magic wakeup method. Prevents access of this class other than through ::instance().
		 */
		public function __wakeup() {
			wp_die( "Please don't __wakeup CR_Replace" );
		}

		/**
		 * Returns the instance of this class. Initializes the instance if necessary.
		 *
		 * @return CR_Replace
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new CR_Replace();
				self::$instance->setup();
			}
			return self::$instance;
		}


		/**
		 * Setup the singletons
		 *
		 * @return void
		 */
		public function setup() {
			add_action( 'load-post.php', [ $this, 'add_edit_page_hooks' ] );
			add_action( 'load-post-new.php', [ $this, 'add_edit_page_hooks' ] );
			add_action( 'wp_ajax_cr_search_posts', [ $this, 'ajax_search_posts' ] );
			add_action( 'wp_ajax_cr_save_post', [ $this, 'ajax_save_post' ] );

			add_action( 'save_post', [ $this, 'action_save_post' ] );
			add_action( 'before_delete_post', [ $this, 'action_before_delete_post' ] );
			add_action( 'trashed_post', [ $this, 'action_trashed_post' ] );
			add_action( 'transition_post_status', [ $this, 'action_publish_post' ], 1, 2 );

			// Used when adding row-action Replace.
			add_action( 'admin_enqueue_scripts', [ $this, 'action_admin_enqueue_scripts' ] );
			add_action( 'admin_footer', [ $this, 'row_action_replace_js' ] );
			add_filter( 'post_row_actions', [ $this, 'add_row_link' ], 10, 2 );
			add_filter( 'page_row_actions', [ $this, 'add_row_link' ], 10, 2 );

			// Handle gutenberg saving.
			add_action( 'wp_after_insert_post', [ $this, 'after_insert_post' ] );
		}

		/**
		 * Add hooks for just the new/edit post admin page
		 *
		 * @return void
		 */
		public function add_edit_page_hooks() {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			add_action( 'admin_footer', [ $this, 'js' ] );
			add_action( 'clone-replace-actions', [ $this, 'add_editpage_content' ] );
			add_action( 'admin_notices', [ $this, 'will_be_replaced_notice' ] );
		}

		/**
		 * Add a link to the actions row in post, page lists
		 *
		 * @param array   $actions Actions for the row.
		 * @param WP_Post $post    The post object for the current row.
		 * @return array
		 */
		public function add_row_link( $actions, $post ) {
			if ( 'publish' !== $post->post_status && current_user_can( get_post_type_object( get_post_type( $post ) )->cap->edit_post, $post->ID ) ) {
				$replace_id            = get_post_meta( $post->ID, '_cr_replace_post_id', true );
				$replace_name          = ( 0 !== intval( $replace_id ) ) ? get_the_title( intval( $replace_id ) ) : '';
				$actions['cr-replace'] = '<a href="#">' . esc_html__( 'Replace', 'clone-replace' ) . '</a>';
				$original_post_id      = intval( get_post_meta( $post->ID, '_cr_original_post', true ) );
				$cr_notice_text        = esc_html__( 'When this post is published, it will replace the selected post. The data from this post will be moved to the replaced one, the latest version of the replaced post will become a revision if revisions are enabled, or go to the trash if not, and this post will be deleted. There is no undo, per se.', 'clone-replace' );
				$cr_orig_post_anchor   = 0 !== $original_post_id ? '
					<p>
						<a
							href="#"
							class="cr_replace_original_post"
							data-post-id="' . esc_attr( $original_post_id ) . '"
							data-title="' . esc_attr( get_the_title( $original_post_id ) ) . '
						">
							' . esc_html__( 'Replace original post', 'clone-replace' ) . '
						</a>
					</p>
					' : '';

				$replace_interface = '
					<div class="replace-action" style="display: none">
						' . wp_nonce_field( 'clone_replace', 'replace_with_' . $post->ID ) . '
						<h4 style="margin-bottom:0.33em">' . esc_html__( 'Replace', 'clone-replace' ) . '</h4>
						<div class="cr-notice">
							<p>' . $cr_notice_text . '</p>
						</div>
						' . $cr_orig_post_anchor . '
						<div>
							<label for="cr_replace_post_title-' . intval( $post->ID ) . '">' . esc_html__( 'Find a post to replace', 'clone-replace' ) . '</label><br />
							<input type="text" class="cr_replace_post_title" id="cr_replace_post_title-' . intval( $post->ID ) . '" value="' . esc_attr( $replace_name ) . '" style="width:100%" />
							<input type="hidden" name="cr_replace_post_id" class="cr_replace_post_id" value="' . esc_attr( $replace_id ) . '" />
							<input type="hidden" name="current_post_id" class="current_post_id" value="' . esc_attr( $post->ID ) . '" />
						</div>
						<p>
							<a href="#" class="inline-save-clone-replace hide-if-no-js button">Replace Post</a>
							<a href="#" class="inline-cancel-clone-replace hide-if-no-js">Cancel</a>
						</p>
					</div>
				';

				$actions['cr-replace'] .= $replace_interface;
			}
			return $actions;
		}

		/**
		 * Add the replace GUI to edit pages
		 *
		 * @return void
		 */
		public function add_editpage_content() {
			global $post;

			if ( 'publish' !== $post->post_status ) {
				$replace_id   = get_post_meta( $post->ID, '_cr_replace_post_id', true );
				$replace_name = ( 0 !== intval( $replace_id ) ) ? get_the_title( intval( $replace_id ) ) : '';
				?>
			<div id="replace-action">
				<h4 style="margin-bottom:0.33em"><?php esc_html_e( 'Replace', 'clone-replace' ); ?></h4>

				<?php wp_nonce_field( 'clone_replace', 'replace_with_' . $post->ID ); ?>

				<div class="cr-notice">
					<p><?php esc_html_e( 'When this post is published, it will replace the selected post. The data from this post will be moved to the replaced one, the latest version of the replaced post will become a revision if revisions are enabled, or go to the trash if not, and this post will be deleted. There is no undo, per se.', 'clone-replace' ); ?></p>
				</div>

				<?php $original_post_id = intval( get_post_meta( $post->ID, '_cr_original_post', true ) ); ?>
				<?php if ( 0 !== $original_post_id ) : ?>
				<p><a href="#" id="cr_replace_original_post" data-post-id="<?php echo esc_attr( $original_post_id ); ?>" data-title="<?php echo esc_attr( get_the_title( $original_post_id ) ); ?>"><?php esc_html_e( 'Replace original post', 'clone-replace' ); ?></a></p>
				<?php endif ?>

				<div>
					<label for="cr_replace_post_title"><?php esc_html_e( 'Find a post to replace', 'clone-replace' ); ?></label><br />
					<input type="text" id="cr_replace_post_title" value="<?php echo esc_attr( $replace_name ); ?>" style="width:100%" />
					<input type="hidden" name="cr_replace_post_id" id="cr_replace_post_id" value="<?php echo esc_attr( $replace_id ); ?>" />
				</div>

				<div id="replace_preview"></div>
			</div>
				<?php
			}
		}


		/**
		 * Add a warning message to a post if it is set to be replaced by another post
		 *
		 * @return void
		 */
		public function will_be_replaced_notice() {
			global $post_ID;
			$replacing_post_id = intval( get_post_meta( $post_ID, '_cr_replacing_post_id', true ) );
			if ( 0 === $replacing_post_id ) {
				return;
			}

			if ( in_array( get_post_status( $replacing_post_id ), [ 'trash', 'publish', 'inherit' ], true ) ) {
				return;
			}

			echo '<div class="error"><p>';
			printf(
				// translators: A link to the post that will do the replacing.
				esc_html__( 'Warning: This post is set to be replaced by %s. Any edits will be lost when it is replaced.', 'clone-replace' ),
				'<strong><a href="' . esc_url( get_edit_post_link( $replacing_post_id ) ) . '">' . esc_html( get_the_title( $replacing_post_id ) ) . '</a></strong>'
			);
			echo '</p></div>';
		}

		/**
		 * Add JavaScript event handling for Replace in Row Actions
		 *
		 * @return void
		 */
		public function row_action_replace_js() {
			?>
			<script type="text/javascript">
			jQuery(function($) {
				// On Cancel Replace, reset state of Replace UI
				$('.inline-cancel-clone-replace').click(function() {
					$(this).parents('.replace-action').remove();
				});

				// Move replace-action into the row
				// Setup XHR request for post search
				$('.row-actions .cr-replace').click(function() {
					var $replace_clone;
					var $original_replace_actions = $(this).children('.replace-action');
					if ($(this).parents('.row-actions').next('.replace-action').length) {
						$replace_clone = $(this).parents('.row-actions').next('.replace-action');
					} else {
						$replace_clone = $original_replace_actions.clone(true);
						$replace_clone.insertAfter($(this).parents('.row-actions'));
					}
					// With the markup set, we need to store variables and setup an xhr call
					var $title = $('.cr_replace_post_title', $replace_clone);
					var $post_id = $('.cr_replace_post_id', $replace_clone);
					var $current_post = $('.current_post_id', $replace_clone);
					var $save_button = $('.inline-save-clone-replace', $replace_clone);
					var $replace_original = $('.cr_replace_original_post', $replace_clone);
					var $replace_with = $('[name="replace_with_' + $current_post.val() + '"]', $replace_clone);
					var cr_status = "<?php echo esc_js( __( 'Set to replace: {{title}}', 'clone-replace' ) ); ?>";
					var cr_ac_options = {};

					// jQueryUI AutoComplete options
					cr_ac_options.select = function( e, ui ) {
						e.preventDefault();
						$title.val( ui.item.label );
						$post_id.val( ui.item.value );
					};
					cr_ac_options.focus = function( e, ui ) {
						e.preventDefault();
						$title.val( ui.item.label );
					};
					cr_ac_options.source = function( request, response ) {
						$.post(
							ajaxurl,
							{
								action: 'cr_search_posts',
								cr_autocomplete_search: request.term,
								cr_current_post: $current_post.val(),
								cr_nonce: "<?php echo esc_js( wp_create_nonce( 'clone_replace_search' ) ); ?>"
							},
							response,
							'json'
						);
					};
					$title.autocomplete( cr_ac_options );

					// Finally, show the replace container
					$replace_clone.toggle();

					$save_button.click(function(event) {
						event.preventDefault();
						if (!$title.val() || !$post_id.val()) {
							alert('Please select a post to replace');
							return;
						}
						$(this).text('Saving...');

						var postOptions = {
							action: 'cr_save_post',
							cr_nonce: "<?php echo esc_js( wp_create_nonce( 'clone_replace_save' ) ); ?>",
							cr_replace_post_id: $post_id.val(), // Autocomplete hidden field
							cr_replace_with_id: $current_post.val(),
						};
						postOptions['replace_with_' + $current_post.val()] = $replace_with.val();

						$.post(
							ajaxurl,
							postOptions,
							function(data) {
								if ('success' === data.label) {
									$replace_clone.remove();
									// Update cloned-from markup to reflect change
									// A page refresh will populate this field automatically
									$('.cr_replace_post_title', $original_replace_actions)
										.val($title.val())
								}
							},
							'json'
						);
					});

					$replace_original.click(function(event) {
						$(this).text('Saving...')
						event.preventDefault();
						$title.val( $(this).data('title') );
						$post_id.val( $(this).data('post-id') );
						$save_button.click();
					});
				});
			});
			</script>
			<?php
		}

		/**
		 * Add javascript routines to the edit page footer
		 *
		 * @return void
		 */
		public function js() {
			?>
			<script type="text/javascript">
			jQuery(function($){
				if ( ! $('#replace-action').length ) {
					return;
				}

				var $title = $('#cr_replace_post_title');
				var $post_id = $('#cr_replace_post_id');
				var cr_status = "<?php echo esc_js( __( 'Set to replace: {{title}}', 'clone-replace' ) ); ?>";
				cr_ac_options = {};
				cr_ac_options.select = function( e, ui ) {
					e.preventDefault();
					$title.val( ui.item.label );
					$post_id.val( ui.item.value );
				};
				cr_ac_options.focus = function( e, ui ) {
					e.preventDefault();
					$title.val( ui.item.label );
				};
				cr_ac_options.source = function( request, response ) {
					$.post( ajaxurl, { action: 'cr_search_posts', cr_autocomplete_search: request.term, cr_current_post: $('#post_ID').val(), cr_nonce: "<?php echo esc_js( wp_create_nonce( 'clone_replace_search' ) ); ?>" }, response, 'json' );
				};
				$title.autocomplete( cr_ac_options );

				$('.save-clone-replace').click(function(){
					$('#clone-replace-status').html( cr_status.replace( '{{title}}', $title.val() ) );
				});
				$('.cancel-clone-replace').click(function(){
					$title.val('');
					$post_id.val('');
					$('#clone-replace-status').html('');
				});
				$('#cr_replace_original_post').click(function(event) {
					event.preventDefault();
					$title.val( $(this).data('title') );
					$post_id.val( $(this).data('post-id') );
					$('.save-clone-replace').click();
				});
			});
			</script>
			<?php
		}

		/**
		 * Ajax responder for saving a post via row-action replace
		 *
		 * @return void
		 */
		public function ajax_save_post() {
			// Check the nonce.
			$cr_nonce = isset( $_POST['cr_nonce'] ) ? sanitize_text_field( $_POST['cr_nonce'] ) : '';
			if ( ! wp_verify_nonce( $cr_nonce, 'clone_replace_save' ) ) {
				exit( '[{"label":"Error: You shall not pass","value":"0"}]' );
			}

			$post_id         = isset( $_POST['cr_replace_post_id'] ) ? intval( sanitize_text_field( $_POST['cr_replace_post_id'] ) ) : 0;
			$replace_with_id = isset( $_POST['cr_replace_with_id'] ) ? intval( sanitize_text_field( $_POST['cr_replace_with_id'] ) ) : 0;

			$this->action_save_post( intval( $replace_with_id ) );

			// Test to ensure post meta was set.
			if ( intval( get_post_meta( $replace_with_id, '_cr_replace_post_id', $post_id ) ) === $post_id ) {
				exit( '{"label": "success"}' );
			} else {
				exit();
			}
		}


		/**
		 * Ajax responder for the "find post" autocomplete box
		 *
		 * @return void
		 */
		public function ajax_search_posts() {
			// Extract and sanitize variables from $_POST.
			$cr_nonce               = isset( $_POST['cr_nonce'] ) ? sanitize_text_field( $_POST['cr_nonce'] ) : '';
			$cr_autocomplete_search = isset( $_POST['cr_autocomplete_search'] ) ? sanitize_text_field( $_POST['cr_autocomplete_search'] ) : '';
			$cr_current_post        = isset( $_POST['cr_current_post'] ) ? intval( $_POST['cr_current_post'] ) : 0;

			if ( ! wp_verify_nonce( $cr_nonce, 'clone_replace_search' ) ) {
				exit( '[{"label":"Error: You shall not pass","value":"0"}]' );
			}

			$args  = apply_filters(
				'CR_Replace_ajax_query_args', // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
				[
					's'                => $cr_autocomplete_search,
					'post__not_in'     => [ $cr_current_post ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn
					'posts_per_page'   => 10,
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'post_status'      => 'publish',
					'post_type'        => get_post_type( $cr_current_post ),
					'suppress_filters' => false,
				]
			);
			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				exit( '[]' );
			}

			$posts = [];

			foreach ( $query->posts as $post ) {
				$posts[] = [
					'label' => ! empty( $post->post_title ) ? $post->post_title : __( '(no title)', 'clone-replace' ),
					'value' => $post->ID,
				];
			}

			echo wp_json_encode( $posts );
			exit;
		}

		/**
		 * Enqueues scripts used in an admin context.
		 */
		public function action_admin_enqueue_scripts() {
			$screen = get_current_screen();
			if ( ! empty( $screen->base ) && 'edit' === $screen->base ) {
				wp_enqueue_script( 'jquery-ui-autocomplete' );
			}
		}

		/**
		 * On post publish, if this post is set to replace another, add a hook to do it.
		 * This is a two-hook process because we only want to run it when the post publishes.
		 *
		 * @param string $new_status The new post status.
		 * @param string $old_status The old post status.
		 */
		public function action_publish_post( $new_status, $old_status ) {
			if ( 'publish' === $new_status && 'publish' !== $old_status ) {
				add_action( 'save_post', [ $this, 'replacement_action' ], 10, 2 );
			}
		}


		/**
		 * Trigger the post replacement routine
		 *
		 * @param int     $post_id The ID of the post.
		 * @param WP_Post $post    The post object.
		 * @return void
		 */
		public function replacement_action( $post_id, $post ) {
			if ( 'publish' !== $post->post_status ) {
				return;
			}

			$replace_id = intval( get_post_meta( $post_id, '_cr_replace_post_id', true ) );
			if ( 0 !== $replace_id ) {
				$this->replace_post( $replace_id, $post_id );
				if ( ! defined( 'REST_REQUEST' ) || true !== REST_REQUEST ) {
					if ( wp_safe_redirect( get_edit_post_link( $replace_id, 'url' ) ) ) {
						exit;
					}
				}
			}
		}

		/**
		 * Sets meta for gutenberg posts after update.
		 *
		 * @param int $post The id of the post being saved/updated.
		 */
		public function after_insert_post( $post ) {
			$replace_post_id = (int) get_post_meta( $post, '_cr_replace_post_id', true );

			if ( ! $replace_post_id ) {
				return;
			}

			/**
			 * We only surface posts the user can edit in gutenberg (post selector),
			 * but there is nothing stoping them from passing a random post id. If they do,
			 * and the user can't edit the post, unset meta.
			 */
			if ( ! self::current_user_can_replace( $post, $replace_post_id ) ) {
				delete_post_meta( $post, '_cr_replace_post_id' );
				return;
			}

			update_post_meta( $replace_post_id, '_cr_replacing_post_id', $post );
		}

		/**
		 * Checks if a user can replace a post with the current post.
		 *
		 * @param int $with_post_id    The current post.
		 * @param int $replace_post_id The post to be replaced.
		 *
		 * @return bool True if the current user can replace, false if not.
		 */
		public function current_user_can_replace( $with_post_id, $replace_post_id ) {

			// If we don't have valid post IDs, bail out.
			if ( ! is_int( $replace_post_id ) || ! is_int( $with_post_id ) || empty( $replace_post_id ) || empty( $with_post_id ) ) {
				return false;
			}

			// The user needs to be able to edit the to-be-replaced post.
			$post_type        = get_post_type( $replace_post_id );
			$post_type_object = get_post_type_object( $post_type );
			if ( ! current_user_can( $post_type_object->cap->edit_post, $replace_post_id ) ) {
				return false;
			}

			// The user also needs to be able to delete the replacing post.
			if ( get_post_type( $with_post_id ) !== $post_type ) {
				$post_type_object = get_post_type_object( get_post_type( $with_post_id ) );
			}
			if ( ! current_user_can( $post_type_object->cap->delete_post, $with_post_id ) ) {
				return false;
			}

			// If we made it this far, we're good to go.
			return true;
		}

		/**
		 * Save post meta for replacement data on post save
		 *
		 * @param int $with_post_id The ID of the post being saved.
		 * @return void
		 */
		public function action_save_post( $with_post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}


			if ( isset( $_POST['cr_replace_post_id'] ) ) {
				if ( ! isset( $_POST[ "replace_with_{$with_post_id}" ] ) || ! wp_verify_nonce( sanitize_text_field( $_POST[ "replace_with_{$with_post_id}" ] ), 'clone_replace' ) ) {
					return;
				}

				$replace_post_id = intval( $_POST['cr_replace_post_id'] );

				// If nothing has changed, no sense in progressing.
				$old_replace_id = intval( get_post_meta( $with_post_id, '_cr_replace_post_id', true ) );
				if ( $old_replace_id === $replace_post_id ) {
					return;
				}


				if ( ! self::current_user_can_replace( $with_post_id, $replace_post_id ) ) {
					return;
				}

				// Check to see if the replacement post is set.
				if ( 0 === $replace_post_id ) {
					if ( $old_replace_id ) {
						// Replacement was removed.
						delete_post_meta( $with_post_id, '_cr_replace_post_id' );
						delete_post_meta( $old_replace_id, '_cr_replacing_post_id' );
					}
					return;
				}

				// Whew! That was a lot of validation, but you never can be too safe.
				update_post_meta( $with_post_id, '_cr_replace_post_id', $replace_post_id );
				update_post_meta( $replace_post_id, '_cr_replacing_post_id', $with_post_id );

				// If this post was set to replace another, and that changed, we need to update that posts's meta.
				if ( $old_replace_id ) {
					delete_post_meta( $old_replace_id, '_cr_replacing_post_id' );
				}
			}
		}


		/**
		 * When deleting a post, check to see if it was replacing another, and if so, delete the reciprocal post's relevant meta value
		 *
		 * @param int $post_id The ID of the post being deleted.
		 * @return void
		 */
		public function action_before_delete_post( $post_id ) {
			$replace_id = intval( get_post_meta( $post_id, '_cr_replace_post_id', true ) );
			if ( 0 !== $replace_id ) {
				delete_post_meta( $replace_id, '_cr_replacing_post_id' );
			}
		}


		/**
		 * When trashing a post, check to see if it was to be replaced by another, and if so, delete the reciprocal post's relevant meta value
		 *
		 * @param int $post_id The post ID for the post being trashed.
		 * @return void
		 */
		public function action_trashed_post( $post_id ) {
			$replacing_id = intval( get_post_meta( $post_id, '_cr_replacing_post_id', true ) );
			if ( 0 !== $replacing_id ) {
				delete_post_meta( $replacing_id, '_cr_replace_post_id' );
			}
		}


		/**
		 * Replace one post with another
		 *
		 * @param int $replace_post_id The ID of the post to be replaced.
		 * @param int $with_post_id    The ID of the post that will take its place.
		 * @return int the ID of the replaced post ($replace_post_id)
		 */
		public function replace_post( $replace_post_id, $with_post_id ) {
			if ( is_int( $replace_post_id ) ) {
				$replace_post = get_post( $replace_post_id );
			}
			if ( ! is_object( $replace_post ) || is_wp_error( $replace_post ) ) {
				return false;
			}

			if ( is_int( $with_post_id ) ) {
				$with_post = get_post( $with_post_id );
			}
			if ( ! is_object( $with_post ) || is_wp_error( $with_post ) ) {
				return false;
			}

			// Unset request params so plugins and themes don't think we're updating the current post, and re-save meta.
			$_POST    = [];
			$_REQUEST = [];
			$_GET     = [];

			// Fire an action so other plugins and themes know what's going on.
			do_action( 'CR_Replace_pre_replacement', $replace_post_id, $with_post_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

			// Make the to-be-replaced post and its meta a revision of itself.
			$revision_id = $this->store_post_revision( $replace_post_id );

			// Remove term relationships of to-be-replaced post.
			$this->truncate_post_terms( $replace_post_id );

			// Update fields we need to persist.
			$with_post->ID          = $replace_post->ID;
			$with_post->post_name   = $replace_post->post_name;
			$with_post->guid        = $replace_post->guid;
			$with_post->post_status = $replace_post->post_status;
			$with_post              = apply_filters( 'CR_Replace_with_post', $with_post, $replace_post_id, $with_post_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
			wp_update_post( $with_post );

			// Update post_meta and term relationships of replacing post to its new post id
			// The replace and with look backwards, but they aren't.
			$this->copy_post_terms( $with_post_id, $replace_post_id );
			$this->move_post_meta( $with_post_id, $replace_post_id );

			// This hook causes issues due to some interaction which I could not find.
			remove_action( 'delete_post', '_wp_delete_post_menu_item' );

			// Delete the replacing post.
			wp_delete_post( $with_post_id, true );

			// Perform cleanup actions.
			$this->cleanup( $replace_post_id, $revision_id );

			do_action( 'CR_Replace_after_replacement', $replace_post_id, $with_post_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
			return $replace_post_id;
		}


		/**
		 * Make a post a revision of itself and return the revision ID
		 *
		 * @param int $post_id The post ID for which to store a post revision.
		 * @return int The new revision ID
		 */
		private function store_post_revision( $post_id ) {
			if ( ! is_int( $post_id ) ) {
				return false;
			}

			$revision_id = wp_save_post_revision( $post_id );

			if ( ! $revision_id ) {
				$revision_id = $this->trash_revision( $post_id );
			}

			$this->move_post_meta( $post_id, $revision_id );

			return $revision_id;
		}


		/**
		 * Remove all the terms for a given post
		 *
		 * @param int $post_id The post ID for which to truncate terms.
		 * @return void
		 */
		private function truncate_post_terms( $post_id ) {
			$taxonomies = get_post_taxonomies( $post_id );
			foreach ( (array) $taxonomies as $taxonomy ) {
				wp_set_object_terms( $post_id, null, $taxonomy );
			}
		}


		/**
		 * If revisions are disabled, create a new version of the replaced post and trash it
		 *
		 * @param int $post_id The post ID to trash.
		 * @return int
		 */
		private function trash_revision( $post_id ) {
			$post            = get_post( $post_id );
			$post->post_name = wp_unique_post_slug( $post->post_name, 0, $post->post_status, $post->post_type, 0 );
			$post->ID        = 0;
			$new_id          = wp_insert_post( $post );
			wp_trash_post( $new_id );
			return $new_id;
		}


		/**
		 * Copy all taxonomy terms from one post to another
		 *
		 * @param int $from_post_id Source post ID for taxonomy migration.
		 * @param int $to_post_id   Destination post ID for taxonomy migration.
		 */
		private function copy_post_terms( $from_post_id, $to_post_id ) {
			if ( ! is_int( $from_post_id ) || ! is_int( $to_post_id ) ) {
				return;
			}

			// While it would be much more efficient to use SQL here, this
			// is much safer and more reliable to ensure proper term counts and
			// avoid collisions.
			CR_Clone()->clone_terms( $to_post_id, $from_post_id );
		}


		/**
		 * Move all post meta from one post to another
		 *
		 * @param int $from_post_id Source post ID for meta migration.
		 * @param int $to_post_id   Destination post ID for meta migration.
		 */
		private function move_post_meta( $from_post_id, $to_post_id = 0 ) {
			$from_post_id = intval( $from_post_id );
			$to_post_id   = intval( $to_post_id );
			if ( ! $from_post_id ) {
				return;
			}

			$ignored_meta = apply_filters(
				'CR_Replace_ignored_meta', // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
				[
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
				]
			);

			global $wpdb;

			$where = "`post_id` = {$from_post_id}";
			if ( count( $ignored_meta ) ) {
				$where .= " AND `meta_key` NOT IN ('" . implode( "', '", esc_sql( $ignored_meta ) ) . "')";
			}

			// We use SQL here because otherwise we'd run (2n + 1) queries deleting postmeta and re-adding it.
			if ( $to_post_id ) {
				$wpdb->query( "UPDATE {$wpdb->postmeta} SET `post_id` = {$to_post_id} WHERE $where" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			} else {
				// If we don't have a $to_post_id, delete the post meta.
				$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE $where" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			// Cleanup cache.
			wp_cache_delete( $from_post_id, 'post_meta' );
			wp_cache_delete( $to_post_id, 'post_meta' );
		}


		/**
		 * Cleanup after a post replacement. Specifically, remove unneeded post meta that were created in the process
		 *
		 * @param int $post_id The resulting post ID ($replace_post_id as referenced elsewhere).
		 * @param int $revision_id Optional. The ID of the newly-created revision, which *was* the replaced post.
		 * @return void
		 */
		private function cleanup( $post_id, $revision_id = false ) {
			delete_post_meta( $post_id, '_cr_replace_post_id' );
			delete_post_meta( $post_id, '_cr_original_post' );

			// Only run this in the event a post revision was created.
			if ( $revision_id ) {
				delete_post_meta( $revision_id, '_cr_replacing_post_id' );
			}
		}
	}

	/**
	 * A helper method to return the instance of CR_Replace.
	 *
	 * @return CR_Replace - The instance of CR_Replace.
	 */
	function CR_Replace() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return CR_Replace::instance();
	}

endif;
