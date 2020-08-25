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
 * Version: 0.3
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

		register_post_meta(
			'post',
			'_cr_original_post',
			[
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'integer',
			]
		);

		register_post_meta(
			'post',
			'_cr_replace_post_id',
			[
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'integer',
			]
		);

		if ( is_admin()
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
			|| ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], rest_get_url_prefix() ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		) {
			require_once __DIR__ . '/class-cr-search.php';
			require_once __DIR__ . '/class-cr-clone.php';
			require_once __DIR__ . '/class-cr-replace.php';
			CR_Clone();
			CR_Replace();
		}
	},
	9999
);

/**
 * Make sure clone and replace meta keys are not protected.
 * 
 * @param bool   $is_protected True or False.
 * @param string $meta_key     Meta key.
 *
 * @return bool
 */
add_filter(
	'is_protected_meta',
	function( $is_protected, $meta_key ): bool {
		if ( in_array( $meta_key, [ '_cr_replace_post_id', '_cr_original_post' ], true ) ) {
			$is_protected = false;
		}

		return (bool) $is_protected;
	},
	10,
	2
);

if ( is_admin() ) :

	/**
	 * Decode the asset map at the given file path.
	 *
	 * @param string $path File path.
	 * @return array
	 */
	function cr_read_asset_map( $path ) {
		if ( file_exists( $path ) && 0 === validate_file( $path ) ) {
			ob_start();
			include $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.IncludingFile, WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			return json_decode( ob_get_clean(), true );
		}

		return [];
	}

	/**
	 * The main theme asset map.
	 *
	 * @var array
	 */
	define( 'CR_ASSET_MAP', cr_read_asset_map( __DIR__ . '/build/assetMap.json' ) );

	/**
	 * The main theme asset build mode.
	 *
	 * @var string
	 */
	define( 'CR_ASSET_MODE', CR_ASSET_MAP['mode'] ?? 'production' );

	/**
	 * Enqueue Clone and Replace assets.
	 */
	function cr_action_enqueue_block_editor_assets() {

		// Only load within the Gutenberg editor.
		$current_screen = get_current_screen();

		if (
			$current_screen instanceof \WP_Screen
			&& ! $current_screen->is_block_editor()
		) {
			return;
		}

		wp_enqueue_script(
			'clone-replace',
			get_asset_path( 'block.js' ),
			[],
			get_asset_hash( 'block.js' ),
			true
		);

		wp_localize_script(
			'clone-replace',
			'cloneReplaceSettings',
			[
				'nonce' => wp_create_nonce( 'clone_post_' . absint( get_the_ID() ) ),
			]
		);
	}
	add_action( 'admin_enqueue_scripts', 'cr_action_enqueue_block_editor_assets' );

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

	/**
	 * Get the path for a given asset.
	 *
	 * @param string $asset Entry point and asset type separated by a '.'.
	 * @return string The asset version.
	 */
	function get_asset_path( $asset ) {
		$asset_property = get_asset_property( $asset, 'path' );

		if ( $asset_property ) {
			// Create public path.
			$base_path = CR_ASSET_MODE === 'development' ?
				get_proxy_path() :
				plugins_url( 'build/', __FILE__ );

			return $base_path . $asset_property;
		}

		return null;
	}

	/**
	 * Get a property for a given asset.
	 *
	 * @param string $asset Entry point and asset type separated by a '.'.
	 * @param string $prop The property to get from the entry object.
	 * @return string|null The asset property based on entry and type.
	 */
	function get_asset_property( $asset, $prop ) {
		/*
		* Appending a '.' ensures the explode() doesn't generate a notice while
		* allowing the variable names to be more readable via list().
		*/
		list( $entrypoint, $type ) = explode( '.', "$asset." );

		$asset_property = CR_ASSET_MAP[ $entrypoint ][ $type ][ $prop ] ?? null;

		return $asset_property ? $asset_property : null;
	}

	/**
	 * Get the development mode proxy URL from .env
	 *
	 * @return string
	 */
	function get_proxy_path() {
		$proxy_url = 'https://0.0.0.0:8080';

		// Use the value in .env if available.
		if ( function_exists( 'getenv' ) && ! empty( getenv( 'PROXY_URL' ) ) ) {
			$proxy_url = getenv( 'PROXY_URL' );
		}

		return sprintf( '%s/build/', $proxy_url );
	}

	/**
	 * Get the contentHash for a given asset.
	 *
	 * @param string $asset Entry point and asset type separated by a '.'.
	 * @return string The asset's hash.
	 */
	function get_asset_hash( $asset ) {
		$asset_property = get_asset_property( $asset, 'hash' );

		return $asset_property ?? CR_ASSET_MAP['hash'] ?? '1.0.0';
	}

endif;
