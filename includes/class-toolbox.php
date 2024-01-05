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

			// Delete translations with a specified status.
			add_action( 'wp_ajax_delete_translations', array( self::class, 'delete_translations' ) );

			// Get progress of an action.
			add_action( 'wp_ajax_get_progress', array( self::class, 'get_progress' ) );

			// Add Tools menu item.
			add_filter( 'gp_nav_menu_items', array( self::class, 'nav_menu_items' ), 10, 2 );

			// Register routes.
			add_action( 'template_redirect', array( $this, 'register_routes' ), 5 );

			// Set template locations.
			add_filter( 'gp_tmpl_load_locations', array( $this, 'template_load_locations' ), 10, 4 );
		}


		/**
		 * Add Tools menu item.
		 *
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public static function nav_menu_items( $items, $location ) {

			$new_item = array();

			// Check for 'side' menu location.
			if ( $location === 'side' ) {

				// Check if user is logged in and has GlotPress admin previleges.
				if ( is_user_logged_in() && current_user_can( 'manage_options' ) && GP::$permission->current_user_can( 'admin' ) ) {
					// Add Tools item to admin bar side menu.
					$new_item[ gp_url( '/tools/' ) ] = esc_html__( 'Tools', 'gp-toolbox' );
				}
			}

			return array_merge( $new_item, $items );
		}


		/**
		 * Register custom routes for GP-Toolbox.
		 *
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public function register_routes() {

			GP::$router->prepend( '/tools', array( __NAMESPACE__ . '\Routes\Tools', 'tools_get' ) );
			GP::$router->prepend( '/tools/originals', array( __NAMESPACE__ . '\Routes\Originals', 'originals_get' ) );
			GP::$router->prepend( '/tools/translations', array( __NAMESPACE__ . '\Routes\Translations', 'translations_get' ) );
			GP::$router->prepend( '/tools/translation-sets', array( __NAMESPACE__ . '\Routes\Translation_Sets', 'translation_sets_get' ) );
		}


		/**
		 * Get GP-Toolbox templates.
		 *
		 * @since 1.0.0
		 *
		 * @return array   Template location.
		 */
		public function template_load_locations( $locations, $template, $args, $template_path ) {

			$gp_toolbox_templates = array(
				'gptoolbox-tools',
				'gptoolbox-originals',
				'gptoolbox-translations',
				'gptoolbox-translation-sets',
			);

			if ( in_array( $template, $gp_toolbox_templates, true ) ) {
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

				// Register and enqueue plugin scripts.
				add_action(
					'wp_enqueue_scripts',
					function () use ( $template, $args ) {
						self::register_plugin_scripts(
							$template,
							$args,
							array(
								'wp-i18n',
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
					'admin'              => GP::$permission->current_user_can( 'admin' ),
					'gp_url'             => gp_url(),         // GlotPress base URL. Defaults to /glotpress/.
					'gp_url_project'     => gp_url_project(), // GlotPress projects base URL. Defaults to /glotpress/projects/.
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'nonce'              => wp_create_nonce( 'gp-toolbox-nonce' ),
					'args'               => self::{'template_args_' . $template}( $args ),
					'supported_statuses' => self::supported_translation_statuses(), // Supported translation statuses.
					'user_locale'        => GP_locales::by_field( 'wp_locale', get_user_locale() ),
					/**
					 * Filters wether to color highlight or not the translation stats counts of the translation sets on the project page.
					 *
					 * @since 1.0.1
					 *
					 * @param bool   True to highlight, false to don't highlight. Defaults to true.
					 */
					'highlight_counts'   => apply_filters( 'gp_toolbox_highlight_counts', true ),
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
		 * Delete translations with a specified status.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public static function delete_translations() {

			// Check nonce.
			check_ajax_referer( 'gp-toolbox-nonce', 'nonce' );

			// Initialize variables.
			$project_path = '';
			$locale       = '';
			$slug         = '';
			$status       = '';

			if ( isset( $_POST['projectPath'] ) ) {
				$project_path = sanitize_key( $_POST['projectPath'] );
			} else {
				wp_die();
			}

			if ( isset( $_POST['locale'] ) ) {
				$locale = sanitize_key( $_POST['locale'] );
			} else {
				wp_die();
			}

			if ( isset( $_POST['slug'] ) ) {
				$slug = sanitize_key( $_POST['slug'] );
			} else {
				wp_die();
			}

			if ( isset( $_POST['status'] ) ) {
				$status = sanitize_key( $_POST['status'] );
			} else {
				wp_die();
			}

			// Get the GP_Project.
			$project = GP::$project->by_path( $project_path );

			// Get the GP_Translation_Set.
			$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $slug, $locale );

			// Get the translations.
			$translations = GP::$translation->for_translation( $project, $translation_set, 'no-limit', gp_get( 'filters', array( 'status' => $status ) ) );

			$total = count( $translations );
			//var_dump( $total );

			// Check if a progress transient exists.
			//$progress = get_transient( 'gp_toolbox_ajax_progress' );

			// If the transient doesn't exist, initialize the progress.
			//if ( $progress === false ) {
			//	$progress = 0;
			//}

			// Delete all translations.
			foreach ( $translations as $key => $translation ) {
				//var_dump( $key );

				// Delay for debug purposes.
				//sleep( 0.1 );

				$progress = ( $key + 1 ) * 100 / $total;

				// Update the transient with the current progress.
				set_transient( 'gp_toolbox_ajax_progress', $progress, MINUTE_IN_SECONDS );

				// Send JSON response with progress
				/*
				wp_send_json(
					array(
						'progress' => $progress,
					)
				);
				*/

				// Store JSON-encoded data in a buffer
				/*
        ob_start();
        echo json_encode(array('progress' => $progress));
        $output = ob_get_clean();

        // Send the buffered output
        echo $output;

        // Flush the output buffer to send data immediately to the client
        ob_flush();
        flush();
		*/


				$translation = GP::$translation->get( $translation );
				if ( ! $translation ) {
					continue;
				}

				// $translation->delete();

			}

			// Remove the transient when the task is complete.
			// delete_transient( 'gp_toolbox_ajax_progress' );

			gp_clean_translation_set_cache( $translation_set->id );

			// Send JSON response and die.
			wp_send_json_success(
				array(
					'percent'      => $translation_set->percent_translated(),
					'current'      => $translation_set->current_count(),
					'fuzzy'        => $translation_set->fuzzy_count(),
					'untranslated' => $translation_set->untranslated_count(),
					'waiting'      => $translation_set->waiting_count(),
					'old'          => $translation_set->old_count,
					'rejected'     => $translation_set->rejected_count,
					'progress'     => 100,
				)
			);
		}

		/**
		 * Get progress of an action.
		 *
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public static function get_progress() {

			// Check nonce.
			check_ajax_referer( 'gp-toolbox-nonce', 'nonce' );
/*
			// Initialize variables.
			$progress = null;

			if ( isset( $_POST['progress'] ) ) {
				$progress = sanitize_key( $_POST['progress'] );
			} else {
				wp_die();
			}
			*/

			// Check if a progress transient exists.
			$progress = get_transient( 'gp_toolbox_ajax_progress' );

			// If the transient doesn't exist, initialize the progress.
			if ( $progress === false ) {
				$progress = 0;
			}

			if ( $progress === 100 ) {
				delete_transient( 'gp_toolbox_ajax_progress' );
			}

			// Send JSON response and die.
			wp_send_json_success(
				array(
					'progress' => $progress,
				)
			);
		}


		/**
		 * Get the translation statuses to manage.
		 * Currently GlotPress project tables only show the 'current', 'fuzzy' and 'waiting' strings.
		 * This enables all the statuses, adding the columns 'old', 'rejected' and 'changesrequested' to the project tables.
		 * The list is filterable by 'gp_toolbox_supported_translation_statuses' below.
		 *
		 * @since 1.0.1
		 *
		 * @return array<int, string>   Translations statuses to enable management.
		 */
		public static function supported_translation_statuses() {

			$glotpress_statuses = array(
				'current'  => esc_html__( 'Current', 'gp-toolbox' ),
				'fuzzy'    => esc_html__( 'Fuzzy', 'gp-toolbox' ),
				'waiting'  => esc_html__( 'Waiting', 'gp-toolbox' ),
				'old'      => esc_html__( 'Old', 'gp-toolbox' ),
				'rejected' => esc_html__( 'Rejected', 'gp-toolbox' ),
				// TODO: Uncomment when the gp-translation-helpers is merged in GlotPress.
				// 'changesrequested' => esc_html__( 'Changes requested', 'gp-toolbox' ), // phpcs:ignore
			);

			$supported_statuses = array_keys( $glotpress_statuses );

			/**
			 * Filter to set the translation statuses to manage with GP Toolbox.
			 *
			 * @since 1.0.1
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
	}
}
