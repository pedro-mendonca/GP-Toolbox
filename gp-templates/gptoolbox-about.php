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
gp_title( __( 'About &lt; Tools &lt; GlotPress', 'gp-toolbox' ) );

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
		gp_link_get( gp_url( '/tools/about/' ), esc_html__( 'About', 'gp-toolbox' ) ),
	)
);

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'About GP-Toolbox', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p>
	<?php esc_html_e( 'This set of tools extends the functionality of GlotPress, bringing to light any potential problems hidden under the hood, keeping it clean, fast and trouble-free.', 'gp-toolbox' ); ?>
</p>

<section class="gp-toolbox support">
	<h3>
		<?php esc_html_e( 'Support', 'gp-toolbox' ); ?>
		</h3>
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: 1: Link opening <a> tag. 2: Link closing </a> tag. */
				esc_html__( 'For Support, please use the %1$sSupport Forum%2$s on WordPress.org.', 'gp-toolbox' ),
				'<a href="https://wordpress.org/support/plugin/gp-toolbox/" target="_blank">',
				'</a>'
			)
		);
		?>
	</p>
</section>

<section class="gp-toolbox issues-requests">
	<h3>
		<?php esc_html_e( 'Feature requests and bug reports', 'gp-toolbox' ); ?>
	</h3>
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: 1: Link opening <a> tag. 2: Link closing </a> tag. */
				esc_html__( 'You are welcome to report any issues or add feature suggestions on the %1$sGitHub repository%2$s.', 'gp-toolbox' ),
				'<a href="https://github.com/pedro-mendonca/GP-Toolbox" target="_blank">',
				'</a>'
			)
		);
		?>
	</p>
</section>

<section class="gp-toolbox sponsor">
	<h3>
		<?php esc_html_e( 'Sponsor', 'gp-toolbox' ); ?>
		</h3>
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: 1: Link opening <a> tag. 2: Link closing </a> tag. */
				esc_html__( 'Do you like %1$s? Support its development by becoming a %2$sSponsor%3$s!', 'gp-toolbox' ),
				'<a href="https://github.com/pedro-mendonca/GP-Toolbox" target="_blank">' . esc_html_x( 'GP Toolbox', 'Plugin name', 'gp-toolbox' ) . '</a>',
				'<a href="https://github.com/sponsors/pedro-mendonca" target="_blank">',
				'</a>'
			)
		);
		?>
	</p>
</section>

<?php
gp_tmpl_footer();