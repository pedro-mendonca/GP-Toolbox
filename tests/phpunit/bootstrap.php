<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package GP_Toolbox
 */

$gp_toolbox_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $gp_toolbox_tests_dir ) {
	$gp_toolbox_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$gp_toolbox_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $gp_toolbox_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $gp_toolbox_phpunit_polyfills_path ); // phpcs:ignore
}

if ( ! file_exists( "{$gp_toolbox_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$gp_toolbox_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$gp_toolbox_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function gp_toolbox_manually_load_plugin() {
	require dirname( dirname( __DIR__ ) ) . '/gp-toolbox.php';
}

tests_add_filter( 'muplugins_loaded', 'gp_toolbox_manually_load_plugin' );

// Start up the WP testing environment.
require "{$gp_toolbox_tests_dir}/includes/bootstrap.php";
