<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

?>
<p class="gptoolbox-description">
	<?php esc_html_e( 'Overview of all Administrators and Validators for each Project and Translation Set.', 'gp-toolbox' ); ?>
</p>
<ul class="gptoolbox-description">
	<li><?php esc_html_e( 'Find and delete obsolete Permissions for unknown users, Projects or Translation Sets. (e.g. Related user, Project or Translation Set were deleted)', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find and delete Permission duplicates.', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find and delete Permissions of unknown types.', 'gp-toolbox' ); ?></li>
</ul>

<?php
// TODO: One click clean Permissions for unknown users/projects/translation sets, and delete duplicates.
