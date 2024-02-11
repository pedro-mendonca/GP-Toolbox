<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

$defined_vars = get_defined_vars();

$page_title       = $defined_vars['args']['title'] ?? '';
$page_description = $defined_vars['args']['description'] ?? '';

$tools_pages = Toolbox::tools_pages();

?>
<div class="gp-heading">
	<h2>
		<?php
		// Page title.
		echo esc_html( $page_title );
		?>
	</h2>

	<div class="gp-toolbox-tools">
		<?php
		$current_uri = str_replace( untrailingslashit( gp_url_public_root() ), '', trailingslashit( gp_url_current() ) );

		$i = 0;
		foreach ( $tools_pages as $tools_page ) {

			if ( $i > 0 ) {
				?>
				<strong class="separator">•</strong>
				<?php
			}

			$class = 'gp-toolbox-tool-link';

			// Add class 'current' to current page link.
			$class .= gp_startswith( $current_uri, $tools_page['url'] ) ? ' current' : '';

			?>
			<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( gp_url( $tools_page['url'] ) ); ?>"><?php echo esc_html( $tools_page['title'] ); ?></a>
			<?php

			++$i;
		}

		?>
	</div>

</div>

<p>
	<?php
	// Page description.
	echo esc_html( $page_description );
	?>
</p>

<div class="notice notice-info">
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: 1: Link opening <a> tag. 2: Link closing </a> tag. */
			__( '<strong>Attention:</strong> Before cleaning any data, please make sure you %1$sbackup your database%2$s first, as any deletion of data will be irreversible.', 'gp-toolbox' ),
			'<a href="https://developer.wordpress.org/advanced-administration/security/backup/" target="_blank">',
			'</a>'
		)
	);
	?>
</div>

<div class="clear"></div>
