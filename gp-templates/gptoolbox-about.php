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
	'/tools/'       => esc_html__( 'Tools', 'gp-toolbox' ),
	'/tools/about/' => esc_html__( 'About', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title'       => esc_html__( 'About GP Toolbox', 'gp-toolbox' ), // Page title.
	'description' => esc_html__( 'This set of tools extends the functionality of GlotPress, bringing to light any potential problems hidden under the hood, keeping it clean, fast and trouble-free.', 'gp-toolbox' ), // Page description.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
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

// Load GlotPress Footer template.
gp_tmpl_footer();
