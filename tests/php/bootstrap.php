<?php

if ( ! defined( 'TRAVIS' ) || ! TRAVIS ) {
	$cwd = explode( 'wp-content', dirname( __FILE__ ) );
	define( 'WP_CONTENT_DIR', $cwd[0] . '/wp-content' );
}

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

/**
 * Because the plugin bootstraps itself inside of an 'init' hook,
 * and the 'init' hook relies on `is_admin()` being true, we will
 * bypass the hook and invoke the plugin's functionality manually.
 */
function _manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../../clone-replace.php';
	require_once dirname( __FILE__ ) . '/../../class-cr-clone.php';
	require_once dirname( __FILE__ ) . '/../../class-cr-replace.php';
	CR_Clone();
	CR_Replace();
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
