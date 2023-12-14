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
		public static function init() {

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
						self::register_plugin_scripts( $template, $args );
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
					'admin'          => GP::$permission->current_user_can( 'admin' ),
					'gp_url'         => gp_url(),         // GlotPress base URL. Defaults to /glotpress/.
					'gp_url_project' => gp_url_project(), // GlotPress projects base URL. Defaults to /glotpress/projects/.
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'gp-toolbox-nonce' ),
					'args'           => self::{'template_args_' . $template}( $args ),
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

			// Delete all translations.
			foreach ( $translations as $translation ) {

				$translation = GP::$translation->get( $translation );
				if ( ! $translation ) {
					continue;
				}
				$translation->delete();
			}

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
				)
			);
		}
	}
}
