<?php

/*
	Plugin Name: Clone & Replace
	Plugin URI: http://www.alleyinteractive.com/
	Description: Gives you the ability to clone posts, and replace posts. Together, you have a very powerful tool for a fork/merge editing model.
	Version: 0.1
	Author: Matthew Boynes
	Author URI: http://www.alleyinteractive.com/
*/
/*  This program is free software; you can redistribute it and/or modify
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


if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
	require_once( __DIR__ . '/class-cr-clone.php' );
	add_action( 'init', 'CR_Clone' );

	require_once( __DIR__ . '/class-cr-replace.php' );
	add_action( 'init', 'CR_Replace' );
}

if ( is_admin() ) :

	function cr_post_actions() {
		global $post;
		?>
		<div id="clone-replace-actions" class="misc-pub-section">
			<span id="clone-replace-status"><?php cr_the_status( $post ) ?></span>
			<?php if ( 'publish' != $post->post_status ) : ?>
				<a href="#clone-replace-select" class="edit-clone-replace hide-if-no-js"><?php _e( 'Clone/Replace', 'clone-replace' ); ?></a>
				<div id="clone-replace-select" class="hide-if-js">
					<?php do_action( 'clone-replace-actions' ) ?>
					<p>
						<a href="#clone-replace-select" class="save-clone-replace hide-if-no-js button">OK</a>
						<a href="#clone-replace-select" class="cancel-clone-replace hide-if-no-js">Cancel</a>
					</p>
				</div>
			<?php else : ?>
				<?php do_action( 'clone-replace-actions' ) ?>
			<?php endif ?>
		</div>
		<?php
	}
	add_action( 'post_submitbox_misc_actions', 'cr_post_actions' );


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
		});
		</script>
		<?php
	}
	add_action( 'admin_footer-post.php', 'cr_print_js' );
	add_action( 'admin_footer-post-new.php', 'cr_print_js' );


	function cr_the_status( $post ) {
		if ( 'publish' != $post->post_status && 0 != ( $replace_id = intval( get_post_meta( $post->ID, '_cr_replace_post_id', true ) ) ) ) {
			printf( __( 'Set to replace: <strong>%s</strong>', 'clone-replace' ), get_the_title( $replace_id ) );
		}
	}

endif;