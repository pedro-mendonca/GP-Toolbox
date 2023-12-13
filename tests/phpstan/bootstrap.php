<?php
/**
 * PHPStan bootstrap file
 *
 * @package GP_Toolbox
 */

// Set plugin version.
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' ); // phpcs:ignore.
}

// Set script debug.
if ( ! defined( 'SCRIPT_DEBUG' ) ) {
	define( 'SCRIPT_DEBUG', true ); // phpcs:ignore.
}

// Set plugin version.
if ( ! defined( 'GP_TOOLBOX_VERSION' ) ) {
	define( 'GP_TOOLBOX_VERSION', '1.2.5' );
}

// Set plugin required PHP version. Needed for PHP compatibility check for WordPress < 7.2.
if ( ! defined( 'GP_TOOLBOX_REQUIRED_PHP' ) ) {
	define( 'GP_TOOLBOX_REQUIRED_PHP', '7.4' );
}


// Require plugin main file.
require_once 'gp-toolbox.php';
