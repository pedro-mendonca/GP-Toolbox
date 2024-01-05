<?php
/**
 * Primary class file for the Rest_Equipa.
 *
 * @package PAC_FPB_Scrapper
 *
 * @since 1.0.0
 */

namespace PAC_FPB_Scrapper;

use DOMDocument;
use DOMXPath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Rest_Equipa' ) ) {

	/**
	 * Class Rest_Equipa.
	 */
	class Rest_Equipa extends Equipa {


		/**
		 * Escalão da Equipa. Ex. Sub 16 Feminino.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $escalao;

		/**
		 * URL da Equipa no site da FPB.pt.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $link_fpb;

		/**
		 * Imagem da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $imagem;

		/**
		 * Nível da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $nivel;

		/**
		 * Género da Equipa. (Masculina, Feminina, null se for mista)
		 *
		 * @since 1.0.0
		 *
		 * @var string|null
		 */
		public $genero;

		/**
		 * Competição da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $competicao;

		/**
		 * Nome da Equipa. Ex. Paço de Arcos Clube - B.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $nome;

		/**
		 * Patrocinador da Equipa. Ex. Artwear.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $patrocinador;


		/**
		 * Constructor.
		 *
		 * @param DOMXPath   $xpath    DOM Path da Equipa obtida da FPB.pt.
		 * @param DOMElement $equipa   DOM Element com os dados da Equipa obtidos da FPB.pt.
		 * @param Clube $clube         Objecto do Clube no site da FPB.
		 */
		public function __construct( $xpath, $equipa, $clube, $epoca ) {

			// parent::__construct();

			$this->set_rest_properties( $xpath, $equipa, $clube, $epoca );
		}


		/**
		 * Define as propriedades do Jogo.
		 *
		 * @since 1.0.0
		 *
		 * @param DOMXPath   $xpath    DOM Path da Equipa obtida da FPB.pt.
		 * @param DOMElement $equipa   DOM Element com os dados da Equipa obtidos da FPB.pt.
		 * @param Clube $clube         Objecto do Clube no site da FPB.
		 *
		 * @return void
		 */
		public function set_rest_properties( $xpath, $equipa, $clube, $epoca ) {

			$this->escalao = self::get_escalao( $xpath, $equipa );

			$this->link_fpb = self::get_link_fpb( $equipa );

			$this->imagem = self::get_imagem( $xpath, $equipa );

			$this->get_detalhes( $this->id );

			$this->clube = $clube->id;

			$this->epoca = $epoca;

			$this->nivel = self::get_nivel( $this->id );

			$this->patrocinador = self::get_patrocinador( $this->id );

		}


		/**
		 * Obtém o Escalão da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @param DOMXPath   $xpath    DOM Path da Equipa obtida da FPB.pt.
		 * @param DOMElement $equipa   DOM Element com os dados da Equipa obtidos da FPB.pt.
		 *
		 * @return string   Escalão da Equipa.
		 */
		public static function get_escalao( $xpath, $equipa ) {

			$elemento     = $xpath->query( ".//div[contains(@class, 'equipa-name')]", $equipa );
			$nome_interno = trim( $elemento[0]->textContent );

			// Retirar espaços a mais. Ex.: "Mini 12                                                                                                Masculino".
			$nome_interno = preg_replace( '/\s+/', ' ', $nome_interno );

			return $nome_interno;
		}


		/**
		 * Obtém a Imagem da Equipa.
		 *
		 * @since 1.0.0
		 *
		 * @param DOMXPath   $xpath    DOM Path da Equipa obtida da FPB.pt.
		 * @param DOMElement $equipa   DOM Element com os dados da Equipa obtidos da FPB.pt.
		 *
		 * @return string|null   Imagem da Equipa.
		 */
		public static function get_imagem( $xpath, $equipa ) {

			$elemento     = $xpath->query( ".//div[contains(@class, 'equipa-photo')]", $equipa );

			$styles = trim( $elemento[0]->getAttribute( 'style' )  );

			$styles = explode( ';', $styles );

			$foto = null;

			foreach ( $styles as $style ) {

				// Extract the URL from the 'url()' function. O último parêntises nem sempre aparece no site da FPB.
				if ( preg_match( '/background-image: url\(([^)]+)\)?/', $style, $matches ) ) {
					$foto = trim( $matches[1], "'\"" );
				}

			}

			// Verificar se é a imagem default. Null se não existir imagem personalizada.
			// https://www.fpb.pt//wp-content/themes/fpbasquetebol/assets/images/ass_highlight_default.png
			// https://www.fpb.pt//wp-content/themes/fpbasquetebol/assets/images/ass_logo_default.png
			/**
			 * [dirname]   => https://www.fpb.pt//wp-content/themes/fpbasquetebol/assets/images
			 * [basename]  => ass_highlight_default.png
			 * [extension] => png
			 * [filename]  => ass_highlight_default
			 */
			$imagem = pathinfo( $foto );
			if ( $imagem['basename'] === 'ass_highlight_default.png' ) {
				return null;
			}

			return $foto;
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
		public static function get_nivel( $equipa ) {

			// Nível por omissão.
			$nivel = 'A';

			// TODO: Se existirem múltiplas equipas do mesmo escalão, procurar no sufixo do nome da equipa. Exemplo: Paço de Arcos Clube (-) B.

			$rest   = new Rest_API;
			$equipa = $rest->get_equipa( $equipa );

			$nome = $equipa->nome;

			$pattern = '/^(\b.*?)(?:(?:\s*\/\s*)(\b.*?\b))?(?:(?:\s*-\s*|\s+)([a-eg-ln-zA-EG-LN-Z]?))?$/';
			preg_match( $pattern, $nome, $matches );
			var_dump( $matches );

			$nome         = $matches[1];
			$patrocinador = $matches[2];
			$nivel        = $matches[3];

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
		public static function get_patrocinador( $equipa ) {

			// Nível por omissão.
			$nivel = 'A';

			// TODO: Se existirem múltiplas equipas do mesmo escalão, procurar no sufixo do nome da equipa. Exemplo: Paço de Arcos Clube (-) B.

			$rest   = new Rest_API;
			$equipa = $rest->get_equipa( $equipa );

			$nome = $equipa->nome;

			$pattern = '/^(\b.*?)(?:(?:\s*\/\s*)(\b.*?\b))?(?:(?:\s*-\s*|\s+)([a-eg-ln-zA-EG-LN-Z]?))?$/';
			preg_match( $pattern, $nome, $matches );
			// var_dump( $matches );

			$nome         = $matches[1];
			$patrocinador = $matches[2];
			$nivel        = $matches[3];

			return $patrocinador;

		}

	}

}
