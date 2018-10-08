<?php

if ( ! defined( 'TRAVIS' ) || ! TRAVIS ) {
	$cwd = explode( 'wp-content', dirname( __FILE__ ) );
	define( 'WP_CONTENT_DIR', $cwd[0] . '/wp-content' );
}

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

/**
 * Mock being in admin so that CR will load properly.
 */
class Mock_Screen {
	public function in_admin() {
		return true;
	}
}

function _manually_load_plugin() {
	global $current_screen;

	$current_screen = new Mock_Screen;
	require_once dirname( __FILE__ ) . '/../../clone-replace.php';
	$current_screen = null;
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
