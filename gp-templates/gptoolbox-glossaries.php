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

?>
<section class="gp-toolbox glossaries">
	<?php

	// Check for glossaries.
	if ( empty( $gp_glossaries ) ) {
		?>
		<p id="glossaries-type-filters"><?php esc_html_e( 'No glossaries found.', 'gp-toolbox' ); ?></p>
		<?php
	} else {
		?>
		<p id="glossaries-type-filters">
			<?php

			$global_glossaries_count  = 0;
			$project_glossaries_count = 0;

			foreach ( $gp_glossaries as $gp_glossary ) {
				// Try to get glossary translation set.
				$translation_set = GP::$translation_set->get( $gp_glossary->translation_set_id );
				// Set the glossary type.
				if ( $translation_set && is_a( $translation_set, 'GP_Translation_Set' ) && $translation_set->project_id === 0 ) {
					++$global_glossaries_count;
				} else {
					++$project_glossaries_count;
				}
			}

			// Glossaries: All {total} originals. {global} global glossaries. {project} project glossaries.
			echo wp_kses_post(
				sprintf(
					/* translators: 1: Glossaries total. 2: Global glossaries. 3: Project glossaries. */
					__( 'Glossaries: %1$s %2$s %3$s', 'gp-toolbox' ),
					'<a id="glossaries-type-all" class="glossaries-type" href="#glossaries">' . sprintf(
						/* translators: %s: Number of Glossaries. */
						_n( '%s glossary.', 'All %s glossaries.', $global_glossaries_count + $project_glossaries_count, 'gp-toolbox' ),
						'<strong class="glossaries-label glossaries-label-all">' . esc_html( number_format_i18n( $global_glossaries_count + $project_glossaries_count ) ) . '</strong>'
					) . '</a>',
					'<a id="glossaries-type-global" class="glossaries-type" href="#glossaries">' . sprintf(
						/* translators: %s: Number of Glossaries. */
						_n( '%s Global glossary original.', '%s Global glossaries.', $global_glossaries_count, 'gp-toolbox' ),
						'<strong class="glossaries-label glossaries-label-global">' . esc_html( number_format_i18n( $global_glossaries_count ) ) . '</strong>'
					) . '</a>',
					'<a id="glossaries-type-project" class="glossaries-type" href="#glossaries">' . sprintf(
						/* translators: %s: Number of Glossaries. */
						_n( '%s Project glossary.', '%s Project glossaries.', $project_glossaries_count, 'gp-toolbox' ),
						'<strong class="glossaries-label glossaries-label-project">' . esc_html( number_format_i18n( $project_glossaries_count ) ) . '</strong>'
					) . '</a>'
				)
			);
			?>
		</p>

		<div class="glossaries-filter">
			<label for="glossaries-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="glossaries-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="glossaries-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox glossaries">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-type"><?php esc_html_e( 'Type', 'gp-toolbox' ); ?></th>
					<th class="gp-column-locale"><?php esc_html_e( 'Locale', 'gp-toolbox' ); ?></th>
					<th class="gp-column-translation-set"><?php esc_html_e( 'Project', 'gp-toolbox' ); ?></th>
					<th class="gp-column-entries"><?php esc_html_e( 'Entries', 'gp-toolbox' ); ?></th>

				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $gp_glossaries as $gp_glossary ) {

					// Get glossary Locale.
					$translation_set = GP::$translation_set->get( $gp_glossary->translation_set_id );

					// Check wether is a Global or Project glossary.
					$glossary_type = ( $translation_set && is_a( $translation_set, 'GP_Translation_Set' ) && $translation_set->project_id === 0 ) ? 'global' : 'project';
					?>
					<tr gptoolboxdata-glossary="<?php echo esc_attr( strval( $gp_glossary->id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $gp_glossary->id ) ); ?></td>
						<?php

						// Check if translation set is known. Double check for GP_Translation_Set object.
						if ( ! $translation_set || ! is_a( $translation_set, 'GP_Translation_Set' ) ) {
							// Unknown translation set.

							?>
							<td class="type" data-text="">
								<span class="unknown">
									<?php
									// Unknown type glossary.
									esc_html_e( 'Unknown', 'gp-toolbox' );
									?>
								</span>
							</td>

							<td class="translation-set" data-text="" colspan="2">
								<span class="unknown">
									<?php
									printf(
										/* translators: Known identifier data. */
										esc_html__( 'Unknown translation set (%s)', 'gp-toolbox' ),
										sprintf(
											/* translators: %d ID number. */
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											esc_html( $gp_glossary->translation_set_id )
										)
									);
									?>
								</span>
							</td>

							<td class="entries">
								<?php
								echo esc_html( number_format_i18n( count( $gp_glossary->get_entries() ) ) );
								?>
							</td>
							<?php

						} elseif ( $glossary_type === 'global' ) {
							// Known translation set.
							// Global glossary.

							?>
							<td class="type global" data-text="global">
								<?php
								// Global glossary.
								esc_html_e( 'Global', 'gp-toolbox' );
								?>
							</td>

							<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->name_with_locale() ); ?>" colspan="2">
								<?php
								gp_link( $gp_glossary->path(), $translation_set->name_with_locale() );
								?>
							</td>

							<td class="entries">
								<?php
								gp_link( $gp_glossary->path(), number_format_i18n( count( $gp_glossary->get_entries() ) ) );
								?>
							</td>
							<?php

						} else {
							// Project glossary.

							?>
							<td class="type project" data-text="project">
								<?php
								// Project glossary.
								esc_html_e( 'Project', 'gp-toolbox' );
								?>
							</td>
							<?php

							$project = GP::$project->get( intval( $translation_set->project_id ) );

							if ( ! $project ) {
								// Unknown project.

								?>
								<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->name_with_locale() ); ?>">
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

								<td class="entries">
									<?php
									echo esc_html( number_format_i18n( count( $gp_glossary->get_entries() ) ) );
									?>
								</td>
								<?php

							} else {
								// Known project.

								?>
								<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->name_with_locale() ); ?>">
									<?php
									gp_link( $gp_glossary->path(), $translation_set->name_with_locale() );
									?>
								</td>

								<td class="project" data-text="<?php echo esc_attr( $project->path ); ?>">
									<?php
									gp_link_project( $project, esc_html( $project->name ) );
									?>
								</td>

								<td class="entries">
									<?php
									gp_link( $gp_glossary->path(), number_format_i18n( count( $gp_glossary->get_entries() ) ) );
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
		// Project glossaries.
		$( '.glossaries' ).tablesorter( {
			theme: 'glotpress',
			sortList: [
				[ 2, 0 ], // Sort by Locale.
				[ 1, 0 ], // Sort by Type.
				[ 3, 0 ]  // Sort by Project.
			],
			headers: {
				0: {
					sorter: 'text'
				}
			}
		} );

		var glossariesRows = $( '.glossaries tbody' ).find( 'tr' );

		$( '#glossaries-filter' ).bind( 'change keyup input', function() {
			var words = this.value.toLowerCase().split( ' ' );

			if ( '' === this.value.trim() ) {
				glossariesRows.show();
			} else {
				glossariesRows.hide();
				glossariesRows.filter( function ( i, v ) {
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

		// Filter table.
		$( '#glossaries-type-filters a' ).click( function() {

			// Clear the text input filter.
			$( 'input#glossaries-filter' ).val( '' );

			// Get the original status.
			var glossaryType = $( this ).prop( 'id' );
			console.log( glossaryType );
			// Get the item class.
			var itemClass = $( this ).prop( 'class' );

			if ( itemClass === 'glossaries-type' ) {

				if ( glossaryType === 'glossaries-type-all' ) {
					// Show all rows.
					$( '.glossaries tbody' ).find( 'tr' ).show();
					// Shrink Locale header column.
					$( '.glossaries thead' ).find( 'th.gp-column-locale' ).attr( 'colspan', 1 );
					// Show Project header column.
					$( '.glossaries thead' ).find( 'th' ).show();
				} else if ( glossaryType === 'glossaries-type-global' ) {
					// Hide all rows.
					$( '.glossaries tbody' ).find( 'tr' ).hide();
					// Enlarge Locale header column.
					$( '.glossaries thead' ).find( 'th.gp-column-locale' ).attr( 'colspan', 2 );
					// Hide Project header column.
					$( '.glossaries thead' ).find( 'th.gp-column-translation-set' ).hide();
					// Show the specified status rows.
					$( '.glossaries tbody' ).find( 'tr td.type.global' ).parent().show();
				} else if ( glossaryType === 'glossaries-type-project' ) {
					// Hide all rows.
					$( '.glossaries tbody' ).find( 'tr' ).hide();
					// Shrink Locale header column.
					$( '.glossaries thead' ).find( 'th.gp-column-locale' ).attr( 'colspan', 1 );
					// Show Project header column.
					$( '.glossaries thead' ).find( 'th.gp-column-translation-set' ).show();
					// Show the specified status rows.
					$( '.glossaries tbody' ).find( 'tr td.type.project' ).parent().show();

				}
			}
		});

		// Clear table filter.
		$( 'button#glossaries-filter-clear' ).click( function() {
			// Clear the text input filter.
			$( 'input#glossaries-filter' ).val( '' );
			// Show all rows.
			$( '.glossaries tbody' ).find( 'tr' ).show();
		});
	} );
</script>

<?php
gp_tmpl_footer();
