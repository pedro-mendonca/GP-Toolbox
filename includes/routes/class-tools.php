<?php
/**
 * Class file for the route Tools.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox\Routes;

use GP;
use GP_Route_Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Tools' ) ) {

	/**
	 * Class Tools.
	 */
	class Tools extends GP_Route_Main {


		/**
		 * Displays the Tools page, requires a user to be logged in and have GlotPress admin previleges.
		 *
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public function tools_get() {

			// Check if the user is logged in.
			if ( ! is_user_logged_in() ) {
				// Redirect to the Login page.
				$this->redirect( wp_login_url( gp_url( '/tools/' ) ) );
				exit;
			}

			// Check if current user has GlotPress admin previleges.
			if ( ! GP::$permission->current_user_can( 'admin' ) ) {
				// Redirect to the GlotPress home.
				$this->redirect( gp_url() );
				exit;
			}

			$this->tmpl( 'tools' );
		}
	}
}
