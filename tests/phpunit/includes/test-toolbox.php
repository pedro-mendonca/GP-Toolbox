<?php
/**
 * Class Toolbox Test.
 *
 * @package GP_Toolbox
 */

use GP_Toolbox\Toolbox;


 /**
  * Toolbox test case.
  */
 class Test_Toolbox extends GP_UnitTestCase {


 	/**
 	 * Test add Tools and Dashboard items do side menu.
 	 */
 	public function test_nav_menu_items() {

		$existent_items = array(
			'/glotpress/profile/username/' => 'Profile',
			'/glotpress/settings/'         => 'Settings',
  			'/wp-login.php?action=logout'  => 'Log out',
		);

		$new_nav_menu_items = Toolbox::nav_menu_items( $existent_items, 'main' );

		// Don't add any menu items for non-administrators.
		$this->assertSame(
			$existent_items,
			$new_nav_menu_items,
		);

		GP::$administrator_permission->create(
			array(
				'user_id'     => 1,
				'action'      => 'admin',
			)
		);

		wp_set_current_user( '1' );

		$new_nav_menu_items = Toolbox::nav_menu_items( $existent_items, 'side' );

		// Add menu items for GlotPress administrators.
		$this->assertSame(
			array(
				'/glotpress/tools' => 'Tools',
				admin_url() => 'Dashboard',
				'/glotpress/profile/username/' => 'Profile',
				'/glotpress/settings/'         => 'Settings',
	  			'/wp-login.php?action=logout'  => 'Log out',
			),
			$new_nav_menu_items,

		);

 	}

 }
