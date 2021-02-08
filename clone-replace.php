<?php
/**
 * Clone & Replace plugin main file.
 *
 * @package Clone_Replace
 * @author Matthew Boynes
 *
 * Plugin Name: Clone & Replace
 * Plugin URI: https://alley.co/
 * Description: Gives you the ability to clone posts, and replace posts. Together, you have a very powerful tool for a fork/merge editing model.
 * Version: 0.2
 * Author: Alley
 * Author URI: https://alley.co/
 */

/*
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_action(
	'init',
	function () {
		if ( is_admin()
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
			|| ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], rest_get_url_prefix() ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		) {
			require_once __DIR__ . '/rest.php';
			require_once __DIR__ . '/class-cr-clone.php';
			require_once __DIR__ . '/class-cr-replace.php';

			CR_Clone();
			CR_Replace();
		}
	},
	9999
);

if ( is_admin() ) :

	/**
	 * Adds HTML for Clone-Replace actions to the submit metabox.
	 */
	function cr_post_actions() {
		global $post;
		?>
		<div id="clone-replace-actions" class="misc-pub-section">
			<span id="clone-replace-status"><?php cr_the_status( $post ); ?></span>
			<?php if ( 'publish' !== $post->post_status ) : ?>
				<a href="#clone-replace-select" class="edit-clone-replace hide-if-no-js"><?php esc_html_e( 'Clone/Replace', 'clone-replace' ); ?></a>
				<div id="clone-replace-select" class="hide-if-js">
					<?php do_action( 'clone-replace-actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores ?>
					<p>
						<a href="#clone-replace-select" class="save-clone-replace hide-if-no-js button">OK</a>
						<a href="#clone-replace-select" class="cancel-clone-replace hide-if-no-js">Cancel</a>
					</p>
				</div>
			<?php else : ?>
				<?php do_action( 'clone-replace-actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores ?>
			<?php endif ?>
		</div>
		<?php
	}
	add_action( 'post_submitbox_misc_actions', 'cr_post_actions' );


	/**
	 * Add javascript to edit post footers for behavior of clone and replace links.
	 * Hide the clone link after click to prevent multiple clicks.
	 */
	function cr_print_js() {
		?>
		<script type="text/javascript">
		jQuery(function($){
			$('.edit-clone-replace').click(function(event) {
				event.preventDefault();
				$('#clone-replace-select').slideDown();
				$(this).hide();
			});
			$('.cancel-clone-replace').click(function(event) {
				event.preventDefault();
				$('#clone-replace-select').slideUp( 'normal', function(){
					$('.edit-clone-replace').show();
				});
			});
			$('.save-clone-replace').click(function(event) {
				event.preventDefault();
				$('#clone-replace-select').slideUp( 'normal', function(){
					$('.edit-clone-replace').show();
				});
			});
			$('#clone-action a').click(function() {
				$(this).hide();
			});
		});
		</script>
		<?php
	}
	add_action( 'admin_footer-post.php', 'cr_print_js' );
	add_action( 'admin_footer-post-new.php', 'cr_print_js' );


	/**
	 * Add javascript to the edit footer to prevent multiple clicks on a clone link.
	 */
	function cr_print_edit_js() {
		?>
		<script type="text/javascript">
		jQuery(function($){
			$('.row-actions .cr-clone').click(function() {
				$(this).hide();
			});
		});
		</script>
		<?php
	}
	add_action( 'admin_footer-edit.php', 'cr_print_edit_js' );

	/**
	 * A helper function to display the post that will be replaced, if set.
	 *
	 * @param WP_Post $post The post object for the current post.
	 */
	function cr_the_status( $post ) {
		if ( 'publish' !== $post->post_status ) {
			$replace_id = intval( get_post_meta( $post->ID, '_cr_replace_post_id', true ) );
			if ( 0 !== $replace_id ) {
				printf(
					// translators: Title of the post to be replaced.
					esc_html__( 'Set to replace: %s', 'clone-replace' ),
					'<strong>' . esc_html( get_the_title( $replace_id ) ) . '<strong>'
				);
			}
		}
	}

	function cr_action_enqueue_block_editor_assets() {
		wp_enqueue_script(
			'clone-replace',
			plugins_url( '/clone-replace/build/cloneReplace.js', __DIR__ ),
			[ 'wp-blocks', 'wp-i18n' ],
			'1.0.0',
			true
		);
		inline_locale_data( 'clone-replace' );
	}

	add_action( 'enqueue_block_editor_assets', 'cr_maybe_enqueue_gutenberg_script', 10 );

	/**
	 * Creates a new Jed instance with specified locale data configuration.
	 *
	 * @param string $to_handle The script handle to attach the inline script to.
	 */
	function inline_locale_data( string $to_handle ) {
		global $post;
		// Define locale data for Jed.
		$locale_data = [
			'' => [
				'domain' => 'clone-replace',
				'lang'    => is_admin() ? get_user_locale() : get_locale(),
			],
		];

		// Pass the Jed configuration to the admin to properly register i18n.
		wp_add_inline_script(
			$to_handle,
			'wp.i18n.setLocaleData( ' . wp_json_encode( $locale_data ) . ", 'clone-replace' ); "
		);

		wp_add_inline_script( $to_handle, 'var crNonce = "' . wp_create_nonce( 'clone_post_' . $post->ID ) . '"', 'before' );
	}

endif;

/**
 * Registers meta for plugin.
 */
function cr_register_meta() {
	$meta_args = [
		'type'              => 'string',
		'single'            => true,
		'default'           => '',
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_text',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	];

	register_post_meta( '', '_cr_original_post', $meta_args );
	register_post_meta( '', '_cr_replace_post_id', $meta_args );
	register_post_meta( '', '_cr_replacing_post_id', $meta_args );
}
add_action( 'init', 'cr_register_meta' );
