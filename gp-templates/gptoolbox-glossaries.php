<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.0
 */

namespace GP_Toolbox;

use GP;

// Get page title.
gp_title( __( 'Glossaries &lt; Tools &lt; GlotPress', 'gp-toolbox' ) );

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
		gp_link_get( gp_url( '/tools/glossaries/' ), esc_html__( 'Glossaries', 'gp-toolbox' ) ),
	)
);

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Glossaries', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p>
	<?php esc_html_e( 'Overview of all Global and Project Glossaries.', 'gp-toolbox' ); ?>
</p>

<?php

// Get GlotPress glossaries.
$gp_glossaries = GP::$glossary->all();

// TODO: Reponse if empty.

// Global glossaries.
$global_glossaries = array();
// Project glossaries.
$project_glossaries = array();

foreach ( $gp_glossaries as $gp_glossary ) {

	// Check if translation set is known.
	$translation_set = GP::$translation_set->get( $gp_glossary->translation_set_id );

	// Check if translation set is known. Double check for GP_Translation_Set object.
	if ( ! $translation_set || ! is_a( $translation_set, 'GP_Translation_Set' ) ) {
		// Unknown translation set.
		$project_glossaries[ $gp_glossary->id ] = $gp_glossary;
		continue;
	}

	// Check wether is a Global or Project glossary.
	if ( $translation_set->project_id === 0 ) {
		// Global glossaries.
		$global_glossaries[ $gp_glossary->id ] = $gp_glossary;
	} else {
		// Project glossaries.
		$project_glossaries[ $gp_glossary->id ] = $gp_glossary;
	}
}

?>
<section class="gp-toolbox glossaries global">
	<h3 style="margin-top: 2em;">
		<?php
		// Global glossaries heading.
		esc_html_e( 'Global glossaries', 'gp-toolbox' );
		?>
	</h3>
	<?php

	// Check for global glossaries.
	if ( empty( $global_glossaries ) ) {
		?>
		<p id="glossary-global-count"><?php esc_html_e( 'No glossaries found.', 'gp-toolbox' ); ?></p>
		<?php
	} else {
		?>
		<p id="glossary-global-count">
			<?php

			echo wp_kses_post(
				sprintf(
					/* translators: %s: Glossaries count. */
					_n(
						'%s Glossary found.',
						'%s Glossaries found.',
						count( $global_glossaries ),
						'gp-toolbox'
					),
					'<span class="count">' . esc_html( number_format_i18n( count( $global_glossaries ) ) ) . '</span>'
				)
			);
			?>
		</p>

		<div class="glossary-global-filter">
			<label for="glossary-global-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="glossary-global-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="glossary-global-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox glossary-global">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-locale"><?php esc_html_e( 'Locale', 'gp-toolbox' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $global_glossaries as $glossary_id => $global_glossary ) {

					// Get glossary Locale.
					$translation_set = GP::$translation_set->get( $global_glossary->translation_set_id );
					// Check if translation set is known. Double check for GP_Translation_Set object.
					if ( ! $translation_set || ! is_a( $translation_set, 'GP_Translation_Set' ) ) {
						continue;
					}
					?>
					<tr gptoolboxdata-glossary="<?php echo esc_attr( strval( $glossary_id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $glossary_id ) ); ?></td>
						<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->locale . '/' . $translation_set->slug ); ?>">
							<?php
							gp_link( gp_url_join( gp_url( '/languages' ), $translation_set->locale, $translation_set->slug, 'glossary' ), $translation_set->name_with_locale() );
							?>
						</td>
					</tr>
					<?php
				}

				?>
			</tbody>
		</table>
		<?php
	}
	?>
</section>

<section class="gp-toolbox glossaries project">
	<h3 style="margin-top: 2em;">
		<?php
		// Global glossaries heading.
		esc_html_e( 'Project glossaries', 'gp-toolbox' );
		?>
	</h3>
	<?php

	// Check for project glossaries.
	if ( empty( $project_glossaries ) ) {
		?>
		<p id="glossary-project-count"><?php esc_html_e( 'No glossaries found.', 'gp-toolbox' ); ?></p>
		<?php
	} else {
		?>
		<p id="glossary-project-count">
			<?php

			echo wp_kses_post(
				sprintf(
					/* translators: %s: Glossaries count. */
					_n(
						'%s Glossary found.',
						'%s Glossaries found.',
						count( $project_glossaries ),
						'gp-toolbox'
					),
					'<span class="count">' . esc_html( number_format_i18n( count( $project_glossaries ) ) ) . '</span>'
				)
			);
			?>
		</p>

		<div class="glossary-project-filter">
			<label for="glossary-project-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="glossary-project-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="glossary-project-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox glossary-project">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-locale"><?php esc_html_e( 'Locale', 'gp-toolbox' ); ?></th>
					<th class="gp-column-translation-set"><?php esc_html_e( 'Project', 'gp-toolbox' ); ?></th>

				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $project_glossaries as $glossary_id => $project_glossary ) {

					// Get glossary Locale.
					$translation_set = GP::$translation_set->get( $project_glossary->translation_set_id );

					?>
					<tr gptoolboxdata-glossary="<?php echo esc_attr( strval( $glossary_id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $glossary_id ) ); ?></td>

						<?php
						// Check if translation set is known. Double check for GP_Translation_Set object.
						if ( ! $translation_set || ! is_a( $translation_set, 'GP_Translation_Set' ) ) {
							?>

							<td class="translation set" data-text="" colspan="2">
								<span class="unknown">
									<?php
									printf(
										/* translators: Known identifier data. */
										esc_html__( 'Unknown translation set (%s)', 'gp-toolbox' ),
										sprintf(
											/* translators: %d ID number. */
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											esc_html( $project_glossary->translation_set_id )
										)
									);
									?>
								</span>
							</td>
							<?php
						} else {
							$project = GP::$project->get( intval( $translation_set->project_id ) );

							if ( ! $project ) {
								?>
								<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->locale . '/' . $translation_set->slug ); ?>">
									<?php
									echo esc_html( $translation_set->name_with_locale() );
									?>
								</td>
								<td class="project" data-text="">
									<span class="unknown">
										<?php
										printf(
											/* translators: Known identifier data. */
											esc_html__( 'Unknown project (%s)', 'gp-toolbox' ),
											sprintf(
												/* translators: %d ID number. */
												esc_html__( 'ID #%d', 'gp-toolbox' ),
												esc_html( strval( $translation_set->project_id ) )
											)
										);
										?>
									</span>
								</td>

								<?php
							} else {
								?>
								<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->locale . '/' . $translation_set->slug ); ?>">
									<?php
									gp_link( $project_glossary->path(), $translation_set->name_with_locale() );
									?>
								</td>
								<td class="project" data-text="<?php echo esc_attr( $project->path ); ?>">
									<?php
									gp_link_project( $project, esc_html( $project->name ) );
									?>
								</td>

								<?php
							}
						}

						?>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	?>
</section>

<script type="text/javascript" charset="utf-8">
	jQuery( document ).ready( function( $ ) {
		// Global glossaries.
		$( '.glossary-global' ).tablesorter( {
			theme: 'glotpress',
			sortList: [
				[1,0]
			],
			headers: {
				0: {
					sorter: 'text'
				}
			}
		} );
		var glossaryGlobalRows = $( '.glossary-global tbody' ).find( 'tr' );
		$( '#glossary-global-filter' ).bind( 'change keyup input', function() {
			var words = this.value.toLowerCase().split( ' ' );

			if ( '' === this.value.trim() ) {
				glossaryGlobalRows.show();
			} else {
				glossaryGlobalRows.hide();
				glossaryGlobalRows.filter( function ( i, v ) {
					var t = $( this );
					for ( var d = 0; d < words.length; ++d ) {
						if ( t.text().toLowerCase().indexOf( words[d] ) !== -1 ) {
							return true;
						}
					}
					return false;
				} ).show();
			}
		} );
		// Clear table filter.
		$( 'button#glossary-global-filter-clear' ).click( function() {
			// Clear the text input filter.
			$( 'input#glossary-global-filter' ).val( '' );
			// Show all rows.
			$( '.glossary-global tbody' ).find( 'tr' ).show();
		});

		// Project glossaries.
		$( '.glossary-project' ).tablesorter( {
			theme: 'glotpress',
			sortList: [
				[ 1, 0 ],
				[ 2, 0 ]
			],
			headers: {
				0: {
					sorter: 'text'
				}
			}
		} );
		var glossaryProjectRows = $( '.glossary-project tbody' ).find( 'tr' );
		$( '#glossary-project-filter' ).bind( 'change keyup input', function() {
			var words = this.value.toLowerCase().split( ' ' );

			if ( '' === this.value.trim() ) {
				glossaryProjectRows.show();
			} else {
				glossaryProjectRows.hide();
				glossaryProjectRows.filter( function ( i, v ) {
					var t = $( this );
					for ( var d = 0; d < words.length; ++d ) {
						if ( t.text().toLowerCase().indexOf( words[d] ) !== -1 ) {
							return true;
						}
					}
					return false;
				} ).show();
			}
		} );
		// Clear table filter.
		$( 'button#glossary-project-filter-clear' ).click( function() {
			// Clear the text input filter.
			$( 'input#glossary-project-filter' ).val( '' );
			// Show all rows.
			$( '.glossary-project tbody' ).find( 'tr' ).show();
		});
	} );
</script>

<?php
gp_tmpl_footer();
