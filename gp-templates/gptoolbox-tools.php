<?php

// Get page title.
gp_title( __( 'Tools &lt; GlotPress', 'gp-toolbox' ) );

// Enqueue scripts.
gp_enqueue_scripts(
	array(
		'tablesorter',
		'tools',
	)
);

// Load GlotPress breadcrumbs.
gp_breadcrumb(
	array(
		gp_link_get( gp_url( '/tools/' ), esc_html__( 'Tools', 'gp-toolbox' ) ),
	)
);

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Tools', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>

<div class="clear"></div>

<?php
$gp_toolbox_tools_pages = GP_Toolbox\Toolbox::tools_pages();

// Load Tools sections.
foreach ( $gp_toolbox_tools_pages as $key => $gp_toolbox_tools_page ) {
	// Load Tools section navigation template.
	if ( isset( $gp_toolbox_tools_page['tools_section'] ) && ! is_null( $gp_toolbox_tools_page['tools_section'] ) ) {
		?>
		<section class="gp-toolbox">
			<h3>
				<a class="gp-toolbox-tool-link" href="<?php echo esc_url( gp_url( $gp_toolbox_tools_page['url'] ) ); ?>">
					<?php echo esc_html( $gp_toolbox_tools_page['title'] ); ?>
				</a>
			</h3>
			<?php
			gp_tmpl_load( $gp_toolbox_tools_page['tools_section'] );
			?>
		</section>
		<?php
	}
}


gp_tmpl_footer();
