<?php
/**
 * Class file for the route Meta.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.6
 */

namespace GP_Toolbox\Routes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Meta' ) ) {

	/**
	 * Class Meta.
	 */
	class Meta extends Main {


		/**
		 * Route.
		 *
		 * @since 1.0.6
		 *
		 * @var string
		 */
		protected $route = '/tools/meta/';

		/**
		 * Template.
		 *
		 * @since 1.0.6
		 *
		 * @var string
		 */
		protected $template = 'gptoolbox-meta';
	}
}
