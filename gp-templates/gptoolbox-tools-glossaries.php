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
	<?php esc_html_e( 'Overview of all Global and Project Glossaries.', 'gp-toolbox' ); ?>
</p>
<h4 class="features"><?php esc_html_e( 'Features', 'gp-toolbox' ); ?></h4>
<ul>
	<li><?php esc_html_e( 'Find Glossaries for unknown Translation Sets or Projects. (e.g. Related Translation Set or Project were deleted)', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find obsolete Glossaries.', 'gp-toolbox' ); ?></li>
</ul>

<?php
// TODO: One click clean Glossaries for unknown projects, and delete obsoletes.
