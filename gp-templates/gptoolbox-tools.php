<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

// Set the page breadcrumbs.
$breadcrumbs = array(
	'/tools/' => esc_html__( 'Tools', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

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
