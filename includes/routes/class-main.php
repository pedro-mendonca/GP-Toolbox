<?php
/**
 * Class file for the route Main.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox\Routes;

use GP;
use GP_Route_Main;
use GP_Toolbox\Toolbox;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Main' ) ) {

	/**
	 * Class Main.
	 */
	class Main extends GP_Route_Main {


		/**
		 * Route.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $route;

		/**
		 * Template.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $template;


		/**
		 * Get the route template, requires a user to be logged in and have GlotPress admin previleges.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function get_route() {

			// Check if the user is logged in.
			if ( ! is_user_logged_in() ) {
				// Redirect to the Login page.
				$this->redirect( wp_login_url( gp_url( $this->route ) ) );
				exit;
			}

			// Check if the current user is logged in, can manage options and has GlotPress admin previleges.
			if ( ! Toolbox::current_user_is_glotpress_admin() ) {
				// Redirect to the GlotPress home.
				$this->redirect( gp_url() );
				exit;
			}

			$this->tmpl( $this->template );
		}
	}
}
