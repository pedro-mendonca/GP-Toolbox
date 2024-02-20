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
	<?php esc_html_e( 'Overview of all Translations.', 'gp-toolbox' ); ?>
</p>
<ul class="gptoolbox-description">
	<li><?php esc_html_e( 'Find Translations with obsolete original. (e.g. Related Project originals were updated and set to obsolete)', 'gp-toolbox' ); ?></li>
	<li><?php esc_html_e( 'Find Translations with unknown original. (e.g. Related Original was deleted)', 'gp-toolbox' ); ?></li>
</ul>
<?php

// TODO: One click clean Translations with Onsolete or Unknown originals.
