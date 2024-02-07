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

			// Add GlotPress and Toolbox items do admin menu.
			add_action( 'admin_menu', array( self::class, 'admin_menu' ), 10 );

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
		 * Add GlotPress and Tools items do admin menu.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function admin_menu() {

			// GlotPress icon.
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
				<rect x="0" width="20" height="20"/>
				<g fill="none">
					<path d="M10,0.6c1.269,0,2.5,0.249,3.658,0.738c0.557,0.235,1.094,0.528,1.596,0.867c0.498,0.336,0.966,0.723,1.392,1.149
						c0.424,0.426,0.811,0.894,1.147,1.392c0.339,0.502,0.63,1.04,0.867,1.596c0.49,1.158,0.738,2.389,0.738,3.658
						c0,1.269-0.249,2.5-0.738,3.658c-0.235,0.558-0.528,1.094-0.867,1.596c-0.336,0.498-0.723,0.966-1.147,1.392
						c-0.426,0.426-0.894,0.812-1.392,1.147c-0.502,0.34-1.04,0.631-1.596,0.867C12.5,19.15,11.269,19.398,10,19.398
						c-1.269,0-2.5-0.249-3.658-0.738c-0.557-0.235-1.094-0.528-1.596-0.867c-0.498-0.336-0.966-0.722-1.392-1.147
						c-0.426-0.426-0.812-0.894-1.147-1.392c-0.341-0.502-0.632-1.04-0.867-1.596C0.849,12.498,0.6,11.268,0.6,9.999
						c0-1.269,0.249-2.5,0.739-3.66c0.235-0.556,0.528-1.093,0.867-1.596c0.336-0.498,0.721-0.966,1.147-1.392
						C3.78,2.926,4.248,2.539,4.746,2.203c0.502-0.339,1.04-0.63,1.596-0.867C7.5,0.849,8.731,0.6,10,0.6 M10,0c-5.522,0-10,4.476-10,10
						c0,5.524,4.477,9.999,10,9.999c5.523,0,10-4.476,10-10C20,4.474,15.522,0,10,0"/>
					<path d="M9.986,1.638c-4.611,0-8.348,3.737-8.348,8.348c0,4.612,3.737,8.35,8.348,8.35c4.61,0,8.35-3.738,8.35-8.35
						C18.336,5.375,14.597,1.638,9.986,1.638z M15.56,9.99c-0.428,0.336-1.025,0.504-1.792,0.504h-0.74v0.648
						c0,0.636,0.069,1.048,0.207,1.234c0.138,0.188,0.46,0.28,0.965,0.28v0.342h-3.585v-0.342c0.492,0,0.812-0.09,0.956-0.27
						c0.144-0.18,0.216-0.594,0.216-1.244v-0.675h-0.615c-0.504,0-0.825,0.072-0.963,0.248C10.07,10.892,10,11.504,10,12.152v0.54
						c-0.25,0-0.715,0.09-1.387,0.27c-0.673,0.18-1.135,0.27-1.389,0.27c-0.938,0-1.742-0.318-2.414-0.956
						c-0.673-0.636-1.009-1.399-1.009-2.289s0.333-1.651,1-2.281C5.467,7.078,6.275,6.76,7.224,6.76c0.781,0,1.423,0.278,1.929,0.842
						c0.036-0.06,0.081-0.177,0.135-0.327c0.054-0.15,0.087-0.087,0.099-0.321h0.379v2.578H9.35C9.207,8.828,8.951,8.286,8.587,7.915
						C8.221,7.541,7.765,7.323,7.214,7.323c-0.729,0-1.254,0.264-1.57,0.793c-0.316,0.53-0.477,1.154-0.477,1.875
						c0,0.709,0.158,1.331,0.477,1.864c0.318,0.534,0.84,0.803,1.572,0.803c0.518,0,0.918-0.09,1.22-0.27
						c0.301-0.18,0.438-0.474,0.438-0.883c0-0.312-0.108-0.849-0.27-0.975c-0.162-0.126-0.477-0.189-0.947-0.189V9.999h4.128V8.837
						c0-0.649-0.069-1.061-0.207-1.234c-0.138-0.174-0.459-0.261-0.964-0.261V7h3.153c0.756,0,1.352,0.165,1.783,0.497
						c0.434,0.33,0.649,0.747,0.649,1.251C16.199,9.239,15.986,9.655,15.56,9.99z M14.631,7.882c0.192,0.228,0.288,0.518,0.288,0.866
						c0,0.324-0.099,0.603-0.297,0.838c-0.198,0.234-0.483,0.351-0.855,0.351h-0.74V7.54h0.74C14.151,7.54,14.439,7.654,14.631,7.882z"
						/>
				</g>
			</svg>';

			$menu_icon = 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

			// Add the new menu item to the admin menu.
			add_menu_page(
				esc_html__( 'GlotPress', 'gp-toolbox' ), // Page title.
				esc_html__( 'GlotPress', 'gp-toolbox' ), // Menu title.
				'read',                                  // Capability required to access this menu item.
				gp_url_public_root(),                    // URL.
				'',                                      // Callback. @phpstan-ignore-line.
				$menu_icon,                              // Menu icon.
				3                                        // Menu position.
			);

			if ( self::current_user_is_glotpress_admin() ) {
				add_submenu_page(
					gp_url_public_root(),                           // Parent slug.
					esc_html__( 'Tools', 'gp-toolbox' ),            // Page title.
					esc_html__( 'Tools', 'gp-toolbox' ),            // Menu title.
					'manage_options',                               // Capability required to access this menu item.
					gp_url_join( gp_url_public_root() . '/tools/' ) // URL.
				);
			}
		}


		/**
		 * Add Tools and Dashboard items do side menu.
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

					// Check if exist a link to Dashboard from another plugin.
					if ( ! isset( $new_item[ admin_url() ] ) ) {
						// Add link to WP Dashboard.
						$new_item[ admin_url() ] = esc_html__( 'Dashboard', 'gp-toolbox' );
					}
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
			GP::$router->prepend( '/tools/glossaries', array( __NAMESPACE__ . '\Routes\Glossaries', 'get_route' ) );
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
						'<b>' . esc_html_x( 'GP Toolbox', 'Plugin name', 'gp-toolbox' ) . '</b>',
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

			if ( $template === 'gptoolbox-glossaries' ) {

				$template_args = null;

				// Register and enqueue GP-Toolbox Glossaries template scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $template_args ) {
						self::register_plugin_scripts(
							'tools-glossaries',
							$template_args,
							array(
								'tablesorter',
								'wp-i18n',
								'wp-api',
							)
						);
					}
				);
			}

			if ( $template === 'gptoolbox-permissions' ) {

				$template_args = null;

				// Register and enqueue GP-Toolbox Permissions template scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $template_args ) {
						self::register_plugin_scripts(
							'tools-permissions',
							$template_args,
							array(
								'tablesorter',
								'wp-i18n',
								'wp-api',
							)
						);
					}
				);
			}

			if ( $template === 'gptoolbox-originals' ) {

				$template_args = null;

				// Register and enqueue GP-Toolbox Permissions template scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $template_args ) {
						self::register_plugin_scripts(
							'tools-originals',
							$template_args,
							array(
								'tablesorter',
								'wp-i18n',
								'wp-api',
							)
						);
					}
				);
			}

			if ( $template === 'gptoolbox-translation-sets' ) {

				$template_args = null;

				// Register and enqueue GP-Toolbox Translation Sets template scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $template_args ) {
						self::register_plugin_scripts(
							'tools-translation-sets',
							$template_args,
							array(
								'tablesorter',
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

			gp_enqueue_styles(
				array(
					'gp-toolbox',
					'dashicons',
				)
			);
		}


		/**
		 * Register and enqueue scripts.
		 *
		 * @since 1.0.0
		 *
		 * @param string             $template       GlotPress template name.
		 * @param array<string>|null $args           GlotPress template arguments.
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
				'gpToolbox',
				array(
					'admin'              => self::current_user_is_glotpress_admin(),                                   // GlotPress Admin with manage options capability.
					'gp_url'             => gp_url(),                                                                  // GlotPress base URL. Defaults to /glotpress/.
					'gp_url_project'     => gp_url_project(),                                                          // GlotPress projects base URL. Defaults to /glotpress/projects/.
					'nonce'              => wp_create_nonce( 'wp_rest' ),                                              // Authenticate in the Rest API.
					'args'               => ! is_null( $args ) ? self::{'template_args_' . $template}( $args ) : null, // Template arguments.
					'supported_statuses' => self::supported_translation_statuses(),                                    // Supported translation statuses.
					'user_locale'        => GP_locales::by_field( 'wp_locale', get_user_locale() ),                    // Current user Locale.
					'user_login'         => wp_get_current_user()->user_login,                                         // Current user login (username).
					/**
					 * Filters wether to color highlight or not the translation stats counts of the translation sets on the project page.
					 *
					 * @since 1.0.0
					 *
					 * @param bool $highlight_counts  True to highlight, false to don't highlight. Defaults to true.
					 */
					'highlight_counts'   => apply_filters( 'gp_toolbox_highlight_counts', $highlight_counts = true ),  // Wether or not to highlight the translation sets table.
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
				// GP Permissions tools.
				'tools_permissions'      => array(
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
				*/
				// GP Originals tools.
				'tools_originals'        => array(
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
				'tools_translations'     => array(
					'url'           => '/tools/translations/',
					'title'         => esc_html__( 'Translations', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-translations',
				),
				// GP Glossaries tools.
				'tools_glossaries'       => array(
					'url'           => '/tools/glossaries/',
					'title'         => esc_html__( 'Glossaries', 'gp-toolbox' ),
					'tools_section' => 'gptoolbox-tools-glossaries',
				),
				/* phpcs:ignore
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
				'tools_about'            => array(
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


		/**
		 * Get the page title.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $breadcrumbs  Array breadcrumbs to produce the page title.
		 * @param string                $separator    Separator of title items. Defaults to < as in GlotPress core.
		 *
		 * @return string   Page title, filterable by gp_title().
		 */
		public static function page_title( $breadcrumbs, $separator = ' &lt; ' ) {

			// Reverse items order.
			$breadcrumbs = array_reverse( $breadcrumbs );

			// Add main GlotPress item to breadcrumbs ending.
			array_push(
				$breadcrumbs,
				esc_html__( 'GlotPress', 'gp-toolbox' )
			);

			return gp_title(
				esc_html(
					implode( $separator, $breadcrumbs )
				)
			);
		}


		/**
		 * Get the page breadcrumbs.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $breadcrumbs  Array of breadcrumbs to produce the page breadcrumbs.
		 *
		 * @return string   Page breadcrumbs, filterable by gp_breadcrumb().
		 */
		public static function page_breadcrumbs( $breadcrumbs ) {

			$result = array();

			foreach ( $breadcrumbs as $url => $title ) {
				$result[] = gp_link_get( gp_url( $url ), esc_html( $title ) );
			}

			return gp_breadcrumb( $result );
		}
	}
}
