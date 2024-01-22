<?php
/**
 * Class file for the Toolbox.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

use GP;
use GP_Locale;
use GP_Locales;
use GP_Project;
use GP_Translation;
use GP_Translation_Set;
use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Toolbox' ) ) {

	/**
	 * Class Toolbox.
	 */
	class Toolbox {


		/**
		 * Registers actions.
		 *
		 * @return void
		 */
		public function __construct() {

			/**
			 * Check if GlotPress is activated.
			 */
			if ( ! self::check_gp() ) {
				return;
			}

			// Register and enqueue plugin style sheet.
			add_action( 'wp_enqueue_scripts', array( self::class, 'register_plugin_styles' ) );

			// Load things before templates.
			add_action( 'gp_pre_tmpl_load', array( self::class, 'pre_template_load' ), 10, 2 );

			// Load things after templates.
			add_action( 'gp_post_tmpl_load', array( self::class, 'post_template_load' ), 10, 2 );

			// Add Tools menu item.
			add_filter( 'gp_nav_menu_items', array( self::class, 'nav_menu_items' ), 10, 2 );

			// Register extra GlotPress routes.
			add_action( 'template_redirect', array( $this, 'register_gp_routes' ), 5 );

			// Set template locations.
			add_filter( 'gp_tmpl_load_locations', array( $this, 'template_load_locations' ), 10, 4 );

			// Instantiate Rest API.
			new Rest_API();
		}


		/**
		 * Add Tools menu item.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $items      Menu items.
		 * @param string                $location   Menu location.
		 *
		 * @return array<string, string>   Menu items.
		 */
		public static function nav_menu_items( $items, $location ) {

			$new_item = array();

			// Check for 'side' menu location.
			if ( $location === 'side' ) {

				// Check if user is logged in and has GlotPress admin previleges.
				if ( self::current_user_is_glotpress_admin() ) {
					// Add Tools item to admin bar side menu.
					$new_item[ strval( gp_url( '/tools/' ) ) ] = esc_html__( 'Tools', 'gp-toolbox' );
				}
			}

			return array_merge( $new_item, $items );
		}


		/**
		 * Register custom routes for GP-Toolbox.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function register_gp_routes() {

			GP::$router->prepend( '/tools', array( __NAMESPACE__ . '\Routes\Tools', 'get_route' ) );
			GP::$router->prepend( '/tools/originals', array( __NAMESPACE__ . '\Routes\Originals', 'get_route' ) );
			GP::$router->prepend( '/tools/permissions', array( __NAMESPACE__ . '\Routes\Permissions', 'get_route' ) );
			GP::$router->prepend( '/tools/translations', array( __NAMESPACE__ . '\Routes\Translations', 'get_route' ) );
			GP::$router->prepend( '/tools/translation-sets', array( __NAMESPACE__ . '\Routes\Translation_Sets', 'get_route' ) );
			GP::$router->prepend( '/tools/about', array( __NAMESPACE__ . '\Routes\About', 'get_route' ) );
		}


		/**
		 * Get GP-Toolbox templates.
		 *
		 * @since 1.0.0
		 *
		 * @param array<int, string> $locations     File paths of template locations.
		 * @param string             $template      The template name.
		 * @param array<mixed>       $args          Arguments passed to the template.
		 * @param string|null        $template_path Priority template location, if any.
		 *
		 * @return array<int, string>   Template locations.
		 */
		public function template_load_locations( $locations, $template, $args, $template_path ) {

			unset( $args, $template_path );

			// Register and enqueue scripts for Tools.
			$template_prefix = 'gptoolbox-';

			// Check GP Toolbox templates prefix.
			if ( substr( $template, 0, strlen( $template_prefix ) ) === $template_prefix ) {
				$locations = array(
					GP_TOOLBOX_DIR_PATH . 'gp-templates/',
				);
			}

			return $locations;
		}


		/**
		 * Check if GlotPress is activated.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public static function check_gp() {

			if ( ! class_exists( 'GP' ) ) {
				add_action( 'admin_notices', array( self::class, 'notice_gp_not_found' ) );
				return false;
			}

			return true;
		}


		/**
		 * Render GlotPress not found admin notice.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function notice_gp_not_found() {

			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: Plugin name. 2: Error message. */
						esc_html__( '%1$s: %2$s', 'gp-toolbox' ),
						'<b>' . esc_html_x( 'Toolbox for GlotPress', 'Plugin name', 'gp-toolbox' ) . '</b>',
						esc_html__( 'GlotPress not found. Please install and activate it.', 'gp-toolbox' )
					);
					?>
				</p>
			</div>
			<?php
		}


		/**
		 * Load things after templates.
		 *
		 * @since 1.0.0
		 *
		 * @param string               $template   The template name.
		 * @param array<string,string> $args       Arguments passed to the template.
		 *
		 * @return void
		 */
		public static function pre_template_load( $template, &$args ) {

			if ( $template === 'project' ) {

				// Register and enqueue GlotPress project template scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $template, $args ) {
						self::register_plugin_scripts(
							$template,
							$args,
							array(
								'wp-i18n',
								'wp-api',
							)
						);
					}
				);

			}

			// Register and enqueue scripts for Tools.
			$template_prefix = 'gptoolbox-';

			if ( substr( $template, 0, strlen( $template_prefix ) ) === $template_prefix ) {

				// Register and enqueue plugin scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $args ) {
						self::register_plugin_scripts(
							'tools', // Override generic template ID.
							$args,
							array(
								'wp-i18n',
								'wp-api',
							)
						);
					}
				);
			}
		}


		/**
		 * Load things after templates.
		 *
		 * @since 1.0.0
		 *
		 * @param string               $template   The template name.
		 * @param array<string,string> $args       Arguments passed to the template.
		 *
		 * @return void
		 */
		public static function post_template_load( $template, &$args ) {

			// Currently unused.

			// Unset unused variables.
			unset( $template, $args );
		}


		/**
		 * Register and enqueue style sheet.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function register_plugin_styles() {

			// Check if SCRIPT_DEBUG is true.
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_register_style(
				'gp-toolbox',
				GP_TOOLBOX_DIR_URL . 'assets/css/style' . $suffix . '.css',
				array(
					'buttons',
				),
				GP_TOOLBOX_VERSION
			);

			gp_enqueue_styles( array( 'gp-toolbox', 'dashicons' ) );
		}


		/**
		 * Register and enqueue scripts.
		 *
		 * @since 1.0.0
		 *
		 * @param string             $template       GlotPress template name.
		 * @param array<string>      $args           GlotPress template arguments.
		 * @param array<int, string> $dependencies   Array of script dependencies.
		 *
		 * @return void
		 */
		public static function register_plugin_scripts( $template, &$args, $dependencies = array() ) {

			// Check if SCRIPT_DEBUG is true.
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			// Set custom script ID.
			$script_id = sprintf(
				'gp-toolbox-%s',
				$template
			);

			wp_register_script(
				$script_id,
				GP_TOOLBOX_DIR_URL . 'assets/js/' . $template . $suffix . '.js',
				$dependencies,
				GP_TOOLBOX_VERSION,
				false
			);

			gp_enqueue_scripts( $script_id );

			wp_set_script_translations(
				$script_id,
				'gp-toolbox'
			);

			wp_localize_script(
				$script_id,
				'gpToolbox' . ucfirst( $template ), // Eg. 'gpToolboxProject'.
				array(
					'admin'              => self::current_user_is_glotpress_admin(),                // GlotPress Admin with manage options capability.
					'gp_url'             => gp_url(),                                               // GlotPress base URL. Defaults to /glotpress/.
					'gp_url_project'     => gp_url_project(),                                       // GlotPress projects base URL. Defaults to /glotpress/projects/.
					'nonce'              => wp_create_nonce( 'wp_rest' ),                           // Authenticate in the Rest API.
					'args'               => self::{'template_args_' . $template}( $args ),          // Template arguments.
					'supported_statuses' => self::supported_translation_statuses(),                 // Supported translation statuses.
					'user_locale'        => GP_locales::by_field( 'wp_locale', get_user_locale() ), // Current user Locale.
					'user_login'         => wp_get_current_user()->user_login,                      // Current user login (username).
					/**
					 * Filters wether to color highlight or not the translation stats counts of the translation sets on the project page.
					 *
					 * @since 1.0.0
					 *
					 * @param bool $highlight_counts  True to highlight, false to don't highlight. Defaults to true.
					 */
					'highlight_counts'   => apply_filters( 'gp_toolbox_highlight_counts', $highlight_counts = true ),   // Wether or not to highlight the translation sets table.
				)
			);
		}


		/**
		 * Project template arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $args   GlotPress template arguments.
		 *
		 * @return array<string, mixed>   Array of template arguments.
		 */
		public static function template_args_project( array $args ) {

			$result = array();

			$result['project'] = $args['project'];

			if ( is_array( $args['translation_sets'] ) ) {
				foreach ( $args['translation_sets'] as $translation_set ) {
					$result['translation_sets'][ $translation_set->locale ] = $translation_set;
				}
			}

			// Return Project and Translation Sets.
			return $result;
		}


		/**
		 * Tools template arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $args   GlotPress template arguments.
		 *
		 * @return array<string, mixed>   Array of template arguments.
		 */
		public static function template_args_tools( array $args ) {

			$result = $args;

			return $result;
		}


		/**
		 * Get the translation statuses to manage.
		 * Currently GlotPress project tables only show the 'current', 'fuzzy' and 'waiting' strings.
		 * This enables all the statuses, adding the columns 'old', 'rejected' and 'changesrequested' to the project tables.
		 * The list is filterable by 'gp_toolbox_supported_translation_statuses' below.
		 *
		 * @since 1.0.0
		 *
		 * @return array<string, string>   Translations statuses to enable management.
		 */
		public static function supported_translation_statuses() {

			$glotpress_statuses = array(
				'current'  => esc_html__( 'Current', 'gp-toolbox' ),
				'fuzzy'    => esc_html__( 'Fuzzy', 'gp-toolbox' ),
				'waiting'  => esc_html__( 'Waiting', 'gp-toolbox' ),
				'old'      => esc_html__( 'Old', 'gp-toolbox' ),
				'rejected' => esc_html__( 'Rejected', 'gp-toolbox' ),
				// TODO: Uncomment when the gp-translation-helpers is merged in GlotPress.
				/**
				 * 'changesrequested' => esc_html__( 'Changes requested', 'gp-toolbox' ), // phpcs:ignore
				 */
			);

			$supported_statuses = array_keys( $glotpress_statuses );

			/**
			 * Filter to set the translation statuses to manage with GP Toolbox.
			 *
			 * @since 1.0.0
			 *
			 * @param array $supported_statuses   The array of the supported statuses to enable management, check and cleanup.
			 */
			$filtered_statuses = apply_filters( 'gp_toolbox_supported_translation_statuses', $supported_statuses );

			// Sanitize the filtered statuses.
			$statuses = array();
			foreach ( $filtered_statuses as $filtered_status ) {
				if ( array_key_exists( $filtered_status, $glotpress_statuses ) ) {
					$statuses[ $filtered_status ] = $glotpress_statuses[ $filtered_status ];
				}
			}

			return $statuses;
		}


		/**
		 * Check if the current user is logged in, can manage options and has GlotPress admin previleges.
		 *
		 * @since 1.0.0
		 *
		 * @return bool   Return true or false.
		 */
		public static function current_user_is_glotpress_admin() {

			// Check if user is logged in.
			if ( ! is_user_logged_in() ) {
				return false;
			}

			// Check if user can manage options.
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			// Check if user has GlotPress admin previleges.
			if ( ! GP::$permission->current_user_can( 'admin' ) ) {
				return false;
			}

			return true;
		}


		/**
		 * Get the available Tools pages.
		 *
		 * @since 1.0.0
		 *
		 * @return array<string, array<string, string>>   Array of available Tools pages.
		 */
		public static function tools_pages() {

			$tools_pages = array(
				// Main page.
				/* phpcs:ignore.
				'tools' => array(
					'url'           => '/tools/',
					'title'         => esc_html__( 'Tools', 'gp-toolbox' ),
				),
				*/
				// GP Permissions tools.
				'tools_permissions' => array(
					'url'           => '/tools/permissions/',
					'title'         => esc_html__( 'Permissions', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-permissions',
				),
				/* phpcs:ignore.
				// GP Projects tools.
				'tools_projects' => array(
					'url'           => '/tools/projects/',
					'title'         => esc_html__( 'Projects', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-projects',
				),
				// GP Originals tools.
				'tools_originals' => array(
					'url'           => '/tools/originals/',
					'title'         => esc_html__( 'Originals', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-originals',
				),
				// GP Translation Sets tools.
				'tools_translation-sets' => array(
					'url'           => '/tools/translation-sets/',
					'title'         => esc_html__( 'Translation Sets', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-translation-sets',
				),
				// GP Translations tools.
				'tools_translations' => array(
					'url'           => '/tools/translations/',
					'title'         => esc_html__( 'Translations', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-translations',
				),
				// GP Glossaries tools.
				'tools_glossaries' => array(
					'url'           => '/tools/glossaries/',
					'title'         => esc_html__( 'Glossaries', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-glossaries',
				),
				// GP Glossary Entries tools.
				'tools_glossary-entries' => array(
					'url'           => '/tools/glossary-entries/',
					'title'         => esc_html__( 'Glossary Entries', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-glossary-entries',
				),
				// GP Meta tools.
				'tools_meta' => array(
					'url'           => '/tools/meta/',
					'title'         => esc_html__( 'Meta', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-meta',
				),
				// GP Locales tools.
				'tools_locales' => array(
					'url'           => '/tools/locales/',
					'title'         => esc_html__( 'Locales', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-locales',
				),
				*/
				// GP About tools.
				'tools_about'       => array(
					'url'   => '/tools/about/',
					'title' => esc_html__( 'About', 'gp-toolbox' ),
				),
			);

			/**
			 * Filters the gp_toolbox actual Tools pages.
			 *
			 * @since 1.0.0
			 *
			 * @param array $tools_pages   Array of the Tools pages.
			 */
			return apply_filters( 'gp_toolbox_tools_pages', $tools_pages );
		}
	}
}
