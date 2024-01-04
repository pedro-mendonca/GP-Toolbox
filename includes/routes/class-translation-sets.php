<?php
/**
 * Class file for the route Translation Sets.
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

if ( ! class_exists( __NAMESPACE__ . '\Translation_Sets' ) ) {

	/**
	 * Class Translation_Sets.
	 */
	class Translation_Sets extends GP_Route_Main {


		/**
		 * Displays the Translation Sets page, requires a user to be logged in and have GlotPress admin previleges.
		 *
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public function translation_sets_get() {

			// Check if the user is logged in.
			if ( ! is_user_logged_in() ) {
				// Redirect to the Login page.
				$this->redirect( wp_login_url( gp_url( '/tools/translation-sets/' ) ) );
				exit;
			}

			// Check if current user has GlotPress admin previleges.
			if ( ! GP::$permission->current_user_can( 'admin' ) ) {
				// Redirect to the GlotPress home.
				$this->redirect( gp_url() );
				exit;
			}

			$this->tmpl( 'gptoolbox-translation-sets' );
		}
	}
}
