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

			// Register and enqueue plugin scripts.
			add_action( 'wp_enqueue_scripts', array( self::class, 'register_plugin_scripts' ) );

			// Load things before templates.
			add_action( 'gp_pre_tmpl_load', array( self::class, 'pre_template_load' ), 10, 2 );

			// Load things after templates.
			add_action( 'gp_post_tmpl_load', array( self::class, 'post_template_load' ), 10, 2 );
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

			// Unset unused variables.
			unset( $template, $args );
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

			// TODO: Enqueue based on template.

			// Project template.
			if ( $template === 'project' ) {
				?>
				<script type="text/javascript">
					jQuery( document ).ready( function( $ ) {
						var table = $( 'table.gp-table.translation-sets' );

						// Add table header column.
						$( table ).children( 'thead' ).children( 'tr' ).find( 'th.gp-column-waiting' ).after( '<th class="stats gp-toolbox"><?php echo esc_html( 'Info', 'gp-toolbox' ); ?></td>' );

						// Customize rows.
						$( table ).children( 'tbody' ).children( 'tr' ).each(
							function() {
								var gpUrlProject = '<?php echo gp_url_project(); ?>';

								// Add row column.
								$( this ).find( 'td.stats.waiting' ).after( '<td class="gp-toolbox"></td>' );

								// Add attributes 'gp-toolbox-data-' to each row.
								$( this ).children( 'td:first-child' ).find( 'a' ).each( function() {
									var dataPrefix = 'gptoolbox-data-';
									// Create a regular expression pattern with the variable
									var regexPattern = new RegExp( '^' + gpUrlProject + '(.*).*/(.+)/(.+)/$' );

									/**
									 * Check for Locale and Slug in the link.
									 * Example: ../glotpress/projects/plugins/hello-dolly/pt/default/
									 */
									var match = $( this ).attr( 'href' ).match( regexPattern );
									var projectPath = match[1]; // 'path/project-slug'
									var locale = match[2];      // 'pt'.
									var slug = match[3];        // 'default'.

									$( this ).closest( 'tr' ).attr( dataPrefix + 'project-path', projectPath );
									$( this ).closest( 'tr' ).attr( dataPrefix + 'locale', locale );
									$( this ).closest( 'tr' ).attr( dataPrefix + 'slug', slug );
								} );
							}
						);
					} );
				</script>
				<?php
			}

			// Unset unused variables.
			unset( $args );
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
		 * @return void
		 */
		public static function register_plugin_scripts() {

			// Check if SCRIPT_DEBUG is true.
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_register_script(
				'gp-toolbox',
				GP_TOOLBOX_DIR_URL . 'assets/js/scripts' . $suffix . '.js',
				array(),
				GP_TOOLBOX_VERSION,
				false
			);

			gp_enqueue_scripts( 'gp-toolbox' );

			wp_set_script_translations(
				'gp-toolbox',
				'gp-toolbox'
			);

			wp_localize_script(
				'gp-toolbox',
				'gpToolbox',
				array(
					'admin'          => GP::$permission->current_user_can( 'admin' ),
					'gp_url'         => gp_url(),         // /glotpress/.
					'gp_url_project' => gp_url_project(), // /glotpress/projects/.
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'gp-toolbox-nonce' ),
				)
			);
		}
	}
}
