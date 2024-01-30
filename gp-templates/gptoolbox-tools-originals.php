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
<p class="descrtiption">
	<?php esc_html_e( 'Overview of all active and obsolete Originals for each Project.', 'gp-toolbox' ); ?>
</p>
<ul>
	<li><?php esc_html_e( 'Find Originals for unknown Projects. (e.g. Related Project was deleted)', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find obsolete Originals.', 'gp-toolbox' ); ?></li>
</ul>

<?php
// TODO: One click clean Originals for unknown projects, and delete obsoletes.
