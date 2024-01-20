<?php

$gp_toolbox_tools_pages = GP_Toolbox\Toolbox::tools_pages();

?>
<div class="gp-heading">
	<h2>
		<?php
		// Page title.
		echo esc_html( $args['title'] );
		?>
	</h2>

	<div class="gp-toolbox-tools">
		<?php
		$i = 0;
		foreach ( $gp_toolbox_tools_pages as $key => $gp_toolbox_tools_page ) {

			if ( $i > 0 ) {
				?>
				<strong class="separator">•</strong>
				<?php
			}

			?>
			<a class="gp-toolbox-tool-link" href="<?php echo esc_url( gp_url( $gp_toolbox_tools_page['url'] ) ); ?>"><?php echo esc_html( $gp_toolbox_tools_page['title'] ); ?></a>
			<?php

			$i++;
		}

		?>
	</div>

</div>

<div class="notice notice-info">
	<?php
	/* translators: 1: Link opening <a> tag. 2: Link closing </a> tag. */
	echo wp_kses_post(
		sprintf(
			__( '<strong>Attention:</strong> Before cleaning any data, please make sure you %1$sbackup your database%2$s first, as any deletion of data will be irreversible.', 'gp-toolbox' ),
			'<a href="https://developer.wordpress.org/advanced-administration/security/backup/" target="_blank">',
			'</a>'
		)
	);
	?>
</div>
