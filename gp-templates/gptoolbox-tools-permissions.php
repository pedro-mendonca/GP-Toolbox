<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

?>
<p class="descrtiption">
	<?php esc_html_e( 'Overview of all GlotPress Administrators and Validators for each Project and Translation Set.', 'gp-toolbox' ); ?>
	<br>
	<?php esc_html_e( 'Manage all your installation\'s permissions in one place.', 'gp-toolbox' ); ?>
</p>
<h4 class="features"><?php esc_html_e( 'Features', 'gp-toolbox' ); ?></h4>
<ul>
	<li><?php esc_html_e( 'Find and delete obsolete Permissions for unknown users, Projects or Translation Sets. (e.g. Related user, Project or Translation Set was deleted)', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find and delete Permission duplicates.', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find and delete Permissions of unknown types.', 'gp-toolbox' ); ?></li>
</ul>

<?php
// TODO: One click clean Permissions for unknown users/projects/translation sets, and delete duplicates.
