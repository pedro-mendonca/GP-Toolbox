<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

// Get page title.
gp_title( __( 'Tools &lt; GlotPress', 'gp-toolbox' ) );

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
$tools_pages = Toolbox::tools_pages();

// Load Tools sections.
foreach ( $tools_pages as $tools_page ) {
	// Load Tools section navigation template.
	if ( isset( $tools_page['tools_section'] ) ) {
		?>
		<section class="gp-toolbox">
			<h3>
				<a class="gp-toolbox-tool-link" href="<?php echo esc_url( gp_url( $tools_page['url'] ) ); ?>">
					<?php echo esc_html( $tools_page['title'] ); ?>
				</a>
			</h3>
			<?php
			gp_tmpl_load( $tools_page['tools_section'] );
			?>
		</section>
		<?php
	}
}

// Load GlotPress Footer template.
gp_tmpl_footer();
