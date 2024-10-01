<?php
/**
 * GP Toolbox
 *
 * @package           GP_Toolbox
 * @link              https://github.com/pedro-mendonca/GP-Toolbox
 * @author            Pedro Mendonça
 * @copyright         2023 Pedro Mendonça
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       GP Toolbox
 * Plugin URI:        https://wordpress.org/plugins/gp-toolbox/
 * GitHub Plugin URI: https://github.com/pedro-mendonca/GP-Toolbox
 * Primary Branch:    main
 * Description:       This set of tools extends the functionality of GlotPress, bringing to light any potential problems hidden under the hood, keeping it clean, fast and trouble-free.
 * Version:           1.0.5
 * Requires at least: 5.3
 * Tested up to:      6.6
 * Requires PHP:      7.4
 * Requires Plugins:  glotpress
 * Author:            Pedro Mendonça
 * Author URI:        https://profiles.wordpress.org/pedromendonca/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gp-toolbox
 * Domain Path:       /languages
 */

namespace GP_Toolbox;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Check if get_plugin_data() function exists.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Get the plugin headers data.
$gp_toolbox_data = get_plugin_data( __FILE__, false, false );


// Set the plugin version.
if ( ! defined( 'GP_TOOLBOX_VERSION' ) ) {
	define( 'GP_TOOLBOX_VERSION', $gp_toolbox_data['Version'] );
}

// Set the plugin required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
if ( ! defined( 'GP_TOOLBOX_REQUIRED_PHP' ) ) {
	define( 'GP_TOOLBOX_REQUIRED_PHP', $gp_toolbox_data['RequiresPHP'] );
}

// Set the plugin URL.
define( 'GP_TOOLBOX_DIR_URL', plugin_dir_url( __FILE__ ) );

// Set the plugin filesystem path.
define( 'GP_TOOLBOX_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Set the plugin file path.
define( 'GP_TOOLBOX_FILE', plugin_basename( __FILE__ ) );

// Set the plugin Rest API namespace.
define( 'GP_TOOLBOX_REST_NAMESPACE', 'gp-toolbox/v1' );


// Include Composer autoload.
require_once GP_TOOLBOX_DIR_PATH . 'vendor/autoload.php';

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function gp_toolbox_init() {
	new Toolbox();
}
add_action( 'gp_init', __NAMESPACE__ . '\gp_toolbox_init' );
