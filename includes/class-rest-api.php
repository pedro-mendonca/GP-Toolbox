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
		function register_routes() {

			$base         = 'translations';           // Base for translations routes.
			$project_path = '(?P<project_path>.+)';  // Project path.
			$locale       = '(?P<locale>.+)';        // Locale.
			$slug         = '(?P<slug>.+)';          // Locale slug.
			$status       = '(?P<status>.+)';        // Translations status.

			// Set the main route for translations by Translation Set, with a specified status.
			$translations_by_set_and_status = $base . '/' . $project_path . '/' . $locale . '/' . $slug . '/' . $status;

			// Route to delete translations from a translation set, with a specific status.
			register_rest_route(
				GP_TOOLBOX_REST_NAMESPACE,
				"/$translations_by_set_and_status/-delete",
				array(
					'methods' => 'POST',
					// 'methods'             => WP_REST_Server::CREATABLE,
					//'methods'             => WP_REST_Server::READABLE,
					'callback' => array( $this, 'delete_translations' ),
					//'permission_callback' => array( $this, 'get_private_data_permissions_check' ),
					'permission_callback' => '__return_true',
				)
			);

			// Route to get the deletion progress of translations from a translation set, with a specific status.
			register_rest_route(
				GP_TOOLBOX_REST_NAMESPACE,
				"/$translations_by_set_and_status/-delete-progress",
				array(
					'methods' => 'GET',
					// 'methods'             => WP_REST_Server::CREATABLE,
					//'methods'             => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_delete_translations_progress' ),
					//'callback' => array( $this, 'progress' ),
					//'permission_callback' => array( $this, 'get_private_data_permissions_check' ),
					'permission_callback' => '__return_true',
				)
			);
		}


		/**
		 * Delete translations from a Translation Set, with a specified status.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request   Request data.
		 *
		 * @return void
		 */
		function delete_translations( WP_REST_Request $request ) {

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

			$transient = 'gp_toolbox_translations__' . $project_path . '__' . $locale . '__' . $slug . '__' . $status . '__progress';

			$total = count( $translations );

			// Delete all translations.
			foreach ( $translations as $key => $translation ) {

				// Delay for debug purposes.
				sleep( 0.5 );

				$progress = ( $key + 1 ) * 100 / $total;

				// Update the transient with the current progress.
				set_transient( $transient, $progress, MINUTE_IN_SECONDS );

				$translation = GP::$translation->get( $translation );
				if ( ! $translation ) {
					continue;
				}

				// $translation->delete();

			}

			// Remove the transient when the task is complete.
			// delete_transient( 'gp_toolbox_ajax_progress' );

			gp_clean_translation_set_cache( $translation_set->id );

			return rest_ensure_response(
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
		 * Get the current deletetion progress of the translations from a Translation Set, with a specified status.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request   Request data.
		 *
		 * @return void
		 */
		function get_delete_translations_progress( WP_REST_Request $request ) {

			$project_path = $request->get_param( 'project_path' );
			$locale       = $request->get_param( 'locale' );
			$slug         = $request->get_param( 'slug' );
			$status       = $request->get_param( 'status' );

			$parameters = $request->get_params();

			$transient = 'gp_toolbox_translations__' . $project_path . '__' . $locale . '__' . $slug . '__' . $status . '__progress';

			// Check if a progress transient exists.
			$progress = get_transient( $transient );

			// If the transient doesn't exist, initialize the progress.
			if ( $progress === false ) {
				$progress = 0;
			}

			if ( $progress === 100 ) {
				delete_transient( $transient );
			}

			return rest_ensure_response(
				array(
					// 'parameters' => $parameters,
					'progress' => $progress,
				)
			);




		}




	}
}
