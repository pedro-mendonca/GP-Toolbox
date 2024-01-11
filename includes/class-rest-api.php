<?php
/**
 * Class file for registering Rest API encpoints.
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 *
 * @package PAC_FPB_Scrapper
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

use GP;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Rest_API' ) ) {

	/**
	 * Class Rest_API.
	 */
	class Rest_API {


		/**
		 * Constructor.
		 */
		public function __construct() {

			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}


		/**
		 * Register routes.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function register_routes() {

			$base         = 'translations';           // Base for translations routes.
			$project_path = '(?P<project_path>.+)';  // Project path.
			$locale       = '(?P<locale>.+)';        // Locale.
			$slug         = '(?P<slug>.+)';          // Locale slug.
			$status       = '(?P<status>.+)';        // Translations status.

			// Set the main route for translations by Translation Set, with a specified status.
			$translations_by_set_and_status = $base . '/' . $project_path . '/' . $locale . '/' . $slug . '/' . $status;

			// Route to bulk delete translations from a translation set, with a specific status.
			register_rest_route(
				GP_TOOLBOX_REST_NAMESPACE,
				"/$translations_by_set_and_status/-delete",
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'translations_bulk_delete' ),
					'permission_callback' => function () {
						return Toolbox::current_user_is_glotpress_admin();
					}
				)
			);
		}


		/**
		 * Bulk delete translations from a Translation Set, with a specified status.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request   Request data.
		 *
		 * @return mixed   Array of stats and number of deleted translations. Can also be a string with error message.
		 */
		public function translations_bulk_delete( WP_REST_Request $request ) {

			$project_path = $request->get_param( 'project_path' );
			$locale       = $request->get_param( 'locale' );
			$slug         = $request->get_param( 'slug' );
			$status       = $request->get_param( 'status' );

			// Get the GP_Project.
			$project = GP::$project->by_path( $project_path );

			if ( ! $project ) {
				// Return error.
				return rest_ensure_response( 'Project not found.' );
			}

			// Get the GP_Translation_Set.
			$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $slug, $locale );

			if ( ! $translation_set ) {
				// Return error.
				return rest_ensure_response( 'Translation Set not found.' );
			}

			// Get the translations.
			$translations = GP::$translation->for_translation( $project, $translation_set, 'no-limit', gp_get( 'filters', array( 'status' => $status ) ) );

			if ( ! $translations ) {
				// Return error.
				return rest_ensure_response( 'Translations not found.' );
			}

			// Delete specified translations.
			$deleted = GP::$translation->delete_many(
				array(
					'translation_set_id' => $translation_set->id,
					'status'             => $status,
				)
			);

			gp_clean_translation_set_cache( $translation_set->id );

			return rest_ensure_response(
				array(
					'deleted' => $deleted,
					'stats'   => array(
						'percent'      => $translation_set->percent_translated(),
						'current'      => $translation_set->current_count(),
						'fuzzy'        => $translation_set->fuzzy_count(),
						'untranslated' => $translation_set->untranslated_count(),
						'waiting'      => $translation_set->waiting_count(),
						'old'          => $translation_set->old_count,
						'rejected'     => $translation_set->rejected_count,
					),
				)
			);
		}
	}
}
