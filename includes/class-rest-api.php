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
			$project_path = '(?P<project_path>.+?)';  // Project path.
			$locale       = '(?P<locale>.+?)';        // Locale.
			$slug         = '(?P<slug>.+?)';          // Locale slug.
			$status       = '(?P<status>.+?)';        // Translations status.

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

			// Get the GP_Translation_Set.
			$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id, $slug, $locale );

			// Get the translations.
			$translations = GP::$translation->for_translation( $project, $translation_set, 'no-limit', gp_get( 'filters', array( 'status' => $status ) ) );

			$transient = 'gp_toolbox_translations__' . $project_path . '__' . $locale . '__' . $slug . '__' . $status;

			$total = count( $translations );

			// Delete all translations.
			foreach ( $translations as $key => $translation ) {

				// Delay for debug purposes.
				sleep( 0.1 );

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

			$transient = 'gp_toolbox_translations__' . $project_path . '__' . $locale . '__' . $slug . '__' . $status;

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


		/**
		 * Obter calendário de jogos.
		 *
		 * @param array $args   Argumentos do endpoint.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		function calendario( $args ) {

			$epoca = $args['epoca'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = 'calendario__' . $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			return $json;

		}


		/**
		 * Obter calendário de jogos.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		function get_associacoes() {

			// $epoca = '2023-2024';
			$epocas = FPB_Scrapper::epocas();
			$epoca  = str_replace( '/', '-', end( $epocas ) );

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			$json = $json->associacoes;

			return $json;

		}


		/**
		 * Obter Associação.
		 *
		 * @param array $args   Argumentos do endpoint.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		function associacao( $args ) {

			$epoca      = $args['epoca'];
			$associacao = $args['associacao'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $associacao . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/associacoes/' . $associacao . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			return $json;

		}


		/**
		 * Obter Associação.
		 *
		 * @param array $args   Argumentos do endpoint.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		function clube( $args ) {

			$epoca      = $args['epoca'];
			$associacao = $args['associacao'];
			$clube      = $args['clube'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $clube . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/associacoes/' . $associacao . '/' . $clube . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			return $json;

		}


		/**
		 * Obter Associação.
		 *
		 * @param array $args   Argumentos do endpoint.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		function clubes( $args ) {

			$associacao = $args['associacao'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $associacao . '.json';

			$upload_dir = Utils::upload_dir() . '/' . '2023-2024' . '/associacoes/' . $associacao . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			return $json;

		}


		/**
		 * Obter Clube.
		 *
		 * @param string $clube   ID do clube.
		 *
		 * @return object   Clube.
		 */
		function get_associacao( $args ) {

			// $epoca = '2023-2024'; // TODO: Variável.
			$epocas = FPB_Scrapper::epocas();
			$epoca  = str_replace( '/', '-', end( $epocas ) );


			$associacao_id = $args['associacao'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			$associacoes = $json->associacoes;

			if ( property_exists( $associacoes, $associacao_id ) ) {
				$result = $associacoes->{$associacao_id};
				$result->clubes = $this->get_clubes( $result );
				$result->_links['parent'] = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/associacoes' );
				$result->_links['self']   = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/associacao/' . $associacao_id );

			}

			return $result;

		}


		/**
		 * Obter Clube.
		 *
		 * @param string $clube   ID do clube.
		 *
		 * @return object   Clube.
		 */
		public function get_clube( $args ) {

			// $epoca = '2023-2024'; // TODO: Variável.
			$epocas = FPB_Scrapper::epocas();
			$epoca  = str_replace( '/', '-', end( $epocas ) );

			$clube_id = $args['clube'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			$associacoes = $json->associacoes;

			foreach ( $associacoes as $associacao_id => $associacao ) {

				if ( empty( $associacao->clubes ) ) {
					continue;
				}

				if ( property_exists( $associacao->clubes, $clube_id ) ) {
					$result = $associacao->clubes->{$clube_id};
					$result->sigla          = Clubes::get_personalizacao( 'sigla', $associacao_id, $clube_id );

					$epocas = FPB_Scrapper::epocas();
					$epoca_actual = end( $epocas );

					/*
					$caminho = str_replace( '/', '-', $epoca ) . '/associacoes/' . $associacao_id . '/' . $clube_id;
					$upload_dir = Utils::upload_url() . '/' . $caminho . '/';
					$logo_personalizado = Clubes::get_personalizacao( 'logo', $associacao_id, $clube_id );
					if ( ! is_null( $logo_personalizado ) ) {
						$logo_personalizado = $upload_dir . $logo_personalizado;
					}
					*/
					$result->logo_personalizado = Clubes::get_personalizacao( 'logo', $associacao_id, $clube_id );

					$result->cor            = Clubes::get_personalizacao( 'cor', $associacao_id, $clube_id );
					$result->links->YouTube = Clubes::get_personalizacao( 'link_youtube', $associacao_id, $clube_id );
					$result->_links['self']       = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/clube/' . $clube_id );
					$result->_links['associacao'] = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/associacao/' . $associacao_id );
					$result->_links['calendario'] = null;
					$result->_links['resultados'] = null;
					$result->equipas = $this->get_equipas( $result );
					break;
				}
			}
			// print_r( $result->logo_personalizado );

			return $result;

			/*
			$caminho = str_replace( '/', '-', $this->epoca ) . '/associacoes/' . $clube->associacao . '/' . $this->clube . '/' . str_replace( ' ', '-', $this->id );

			// JSON file path pattern: clube_1.json.
			$json_file = $this->id . '.json';

			// Create data file.
			$upload_dir = Utils::upload_dir() . '/' . $caminho . '/';
			Utils::upload_dir_prepare( $upload_dir );
			*/

		}


		/**
		 * Obter Clube.
		 *
		 * @param string $nome_completo   Nome completo do clube.
		 *
		 * @return object   Clube.
		 */
		public function get_clube_pelo_nome( $nome_completo = null ) {

			$epocas = FPB_Scrapper::epocas();
			$epoca  = str_replace( '/', '-', end( $epocas ) );

			if ( is_null( $nome_completo ) ) {
				return null;
			}

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			$associacoes = $json->associacoes;

			foreach ( $associacoes as $associacao ) {

				foreach ( $associacao->clubes as $clube_id => $clube ) {
					if ( $clube->nome_completo === $nome_completo ) {
						$args['clube'] = $clube_id;
						return $this->get_clube( $args );
					}
				}

			}

			return null;

		}


		/**
		 * Obter Clube.
		 *
		 * @param string $nome_completo   Nome completo do clube.
		 *
		 * @return object   Equipa.
		 */
		public function get_equipa_do_jogo( $escalao = null, $nome_equipa = null ) {

			if ( is_null( $escalao ) ) {
				return null;
			}

			if ( is_null( $nome_equipa ) ) {
				return null;
			}

			$epocas = FPB_Scrapper::epocas();
			$epoca  = str_replace( '/', '-', end( $epocas ) );

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			$associacoes = $json->associacoes;

			if ( empty( $associacoes ) ) {
				return null;
			}

			foreach ( $associacoes as $associacao_id => $associacao ) {

				if ( empty( $associacao->clubes ) ) {
					continue;
				}

				foreach ( $associacao->clubes as $clube_id => $clube ) {

					if ( empty( $clube->equipas ) ) {
						continue;
					}

					foreach ( $clube->equipas as $equipa_id => $equipa ) {

						if ( utf8_decode( $equipa->escalao ) !== $escalao ) {
							continue;
						}

						if ( $equipa->nome === $nome_equipa ) {
							$args = array(
								'associacao' => $associacao_id,
								'equipa'     => $equipa_id,
							);
							return $this->get_equipa( $args );
						}


					}



				}

			}

			return null;

		}


		/**
		 * Obtém a sigla da equipa do jogo.
		 *
		 * @param string $nome_completo   Nome completo do clube.
		 *
		 * @return object   Clube.
		 */
		public function get_sigla_equipa( $associacao_id, $clube_id, $equipa ) {

			// Definições.
			$mostrar_patrocinador = false;
			$mostrar_nivel        = true;

			$sigla_clube = Clubes::get_personalizacao( 'sigla', $associacao_id, $clube_id );

			if ( is_null( $sigla_clube ) ) {
				return null;
			}

			$sigla_equipa = $sigla_clube;

			if ( $mostrar_patrocinador && $equipa->patrocinador ) {
				$sigla_equipa .= '/' . $equipa->patrocinador;
			}

			if ( $mostrar_nivel && $equipa->nivel !== 'A' ) {
				$sigla_equipa .= '-' . $equipa->nivel;
			}

			return $sigla_equipa;

		}


		/**
		 * Obter Clube.
		 *
		 * @param object $associacao   Objecto da Associação.
		 *
		 * @return array   Clubes.
		 */
		function get_clubes( $associacao ) {

			$clubes = array();

			if ( empty( $associacao->clubes ) ) {
				return $clubes;
			}

			foreach ( $associacao->clubes as $clube_id => $clube ) {
				$args['clube'] = $clube_id;
				$clubes[ $clube_id ] = $this->get_clube( $args );
			}

			foreach ( $clubes as $clube_id => $clube ) {
				$clubes[ $clube_id ]->_links['self'] = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/clube/' . $clube_id );
			}

			return $clubes;

		}


		/**
		 * Obter Clube.
		 *
		 * @param object $clube   ID do clube.
		 *
		 * @return array   Equipas.
		 */
		function get_equipas( $clube ) {

			$equipas = array();

			if ( empty( $clube->equipas ) ) {
				return $equipas;
			}

			foreach ( $clube->equipas as $equipa_id => $equipa ) {
				$args = array(
					'associacao' => $clube->associacao,
					'equipa' => $equipa_id,
				);
				$equipas[ $equipa_id ] = $this->get_equipa( $args );
			}

			foreach ( $equipas as $equipa_id => $equipa ) {
				$equipas[ $equipa_id ]->_links['self'] = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/equipa/' . $equipa_id );
			}

			return $equipas;

		}


		/**
		 * Obter Equipa.
		 *
		 * @param array $args   Argumentos do endpoint.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		function equipa( $args ) {

			$epoca      = $args['epoca'];
			$associacao = $args['associacao'];
			$clube      = $args['clube'];
			$equipa     = $args['equipa'];

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $equipa . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/associacoes/' . $associacao . '/' . $clube . '/' . $equipa . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );
			$json->_links = array(
				'associacao' => get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/calendario/' . $epoca . '/associacoes/' . $associacao ),
				'equipa'     => get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/calendario/' . $epoca . '/associacoes/' . $associacao . '/' . $clube ),
			);

			return $json;

		}


		/**
		 * Obter Equipa.
		 *
		 * @param array $args   Argumentos do endpoint.
		 *
		 * @return object   Jogos organizados por dias.
		 */
		public function get_equipa( $args ) {

			$epocas = FPB_Scrapper::epocas();
			$epoca_actual  = str_replace( '/', '-', end( $epocas ) );

			// $epoca = '2023-2024'; // TODO: Variável.
			$epoca      = str_replace( '/', '-', ( $args['epoca'] ?? $epoca_actual ) );
			//$associacao = $args['associacao'];
			$equipa_id  = $args['equipa'];
			// TODO: Equipa tanto pode ser (string) ID como objecto Equipa.

			// Global JSON file path pattern: calendario__YYYY-YYYY.json
			$json_file = $epoca . '.json';

			$upload_dir = Utils::upload_dir() . '/' . $epoca . '/';

			$json = file_get_contents( $upload_dir . $json_file );

			$json = json_decode( $json );

			$associacoes = $json->associacoes;

			return $associacoes;

			if ( empty( $associacoes ) ) {
				return null;
			}

			foreach ( $associacoes as $associacao_id => $associacao ) {

				if ( empty( $associacao->clubes ) ) {
					continue;
				}

				foreach ( $associacao->clubes as $clube_id => $clube ) {

					if ( empty( $clube->equipas ) ) {
						continue;
					}


					if ( ! is_object( $clube->equipas ) ) {
						continue;
					}

					//print_r( $equipa_id );
					//print_r( $clube->equipas );

					if ( property_exists( $clube->equipas, $equipa_id ) ) {
						$equipa = $clube->equipas->{$equipa_id};
						$equipa->nivel = self::get_nivel_equipa( $equipa->nome );
						$equipa->patrocinador = self::get_patrocinador_equipa( $equipa->nome );
						$equipa->sigla = self::get_sigla_equipa( $associacao_id, $clube_id, $equipa );
						$equipa->escalao = utf8_decode( $equipa->escalao );
						$equipa->genero = self::get_genero_equipa( $equipa->escalao );
						//$equipa = $associacao->clubes->{$clube_id}->equipas->{$equipa_id};

						$equipa->_links['associacao'] = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/associacao/' . $associacao_id );
						$equipa->_links['clube']      = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/clube/' . $clube_id );
						$equipa->_links['self']       = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/equipa/' . $equipa_id );
						//$equipa->_links['epoca']      = get_rest_url( null, GP_TOOLBOX_REST_NAMESPACE . '/associacao/' . $associacao_id );
						break;
					}
				}


			}



/*
			$nova_equipa = new Equipa();

			foreach ( $equipa as $key => $value ) {
				// Assuming your properties have the same names as the keys in the JSON
				$nova_equipa->$key = $value;
			}
			$equipa = $nova_equipa;
			*/


			return $equipa;

		}


		/**
		 * Obtém o nível da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @param string $equipa    ID da Equipa da FPB.pt.
		 *
		 * @return void
		 */
		public static function get_nivel_equipa( $nome_equipa ) {

			// Nível por omissão.
			$nivel = 'A';

			// TODO: Se existirem múltiplas equipas do mesmo escalão, procurar no sufixo do nome da equipa. Exemplo: Paço de Arcos Clube (-) B.

			$pattern = '/^(\b.*?)(?:(?:\s*\/\s*)(\b.*?\b))?(?:(?:\s*-\s*|\s+)([a-eg-ln-zA-EG-LN-Z]?))?$/';
			$match = preg_match( $pattern, $nome_equipa, $matches, PREG_UNMATCHED_AS_NULL );

			if ( ! $match ) {
				return null;
			}

			$nome         = $matches[1];
			$patrocinador = $matches[2];
			$nivel        = $matches[3] ?? $nivel;

			return $nivel;

		}


		/**
		 * Obtém o nível da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @param string $equipa    ID da Equipa da FPB.pt.
		 *
		 * @return void
		 */
		public static function get_patrocinador_equipa( $nome_equipa ) {

			// Nível por omissão.
			$nivel = 'A';

			// TODO: Se existirem múltiplas equipas do mesmo escalão, procurar no sufixo do nome da equipa. Exemplo: Paço de Arcos Clube (-) B.

			$pattern = '/^(\b.*?)(?:(?:\s*\/\s*)(\b.*?\b))?(?:(?:\s*-\s*|\s+)([a-eg-ln-zA-EG-LN-Z]?))?$/';
			preg_match( $pattern, $nome_equipa, $matches, PREG_UNMATCHED_AS_NULL );

			$nome         = $matches[1];
			$patrocinador = $matches[2];
			$nivel        = $matches[3];

			return $patrocinador;

		}


		/**
		 * Obtém o nível da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @param string $equipa    ID da Equipa da FPB.pt.
		 *
		 * @return void
		 */
		public static function get_genero_equipa( $escalao ) {

			$pattern = '/^.*\s+([Ff]eminin[ao])|([Mm]asculin[ao])$/';
			preg_match( $pattern, $escalao, $matches, PREG_UNMATCHED_AS_NULL );
			// print_r( $matches );

			if ( $matches[1] ) {
				$genero = 'Feminino';
			} elseif ( $matches[2] ) {
				$genero = 'Masculino';
			} else {
				// Nível por omissão.
				$genero = null;
			}

			return $genero;

		}


		/**
		 * This is our callback function that embeds our resource in a WP_REST_Response
		 */
		function get_private_data_permissions_check() {
			// Restrict endpoint to only users who have the edit_posts capability.
			if ( ! current_user_can( 'edit_posts' ) ) {
				return new WP_Error(
					'rest_forbidden',
					esc_html__( 'You have no permission to view this data.', 'pac-fpb-scrapper' ),
					array(
						'status' => 401
					)
				);
			}

			// This is a black-listing approach. You could alternatively do this via white-listing, by returning false here and changing the permissions check.
			return true;
		}


		function add_custom_link_to_post( $data, $post, $context ) {
			// Add a custom link to the _links section
			$data['_links']['custom'] = [
				'href'  => 'https://example.com/custom',
				'title' => 'Custom Link',
			];

			return $data;
			}



	}
}
