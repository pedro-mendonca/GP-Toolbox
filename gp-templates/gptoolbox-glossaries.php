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

// Set the page breadcrumbs.
$breadcrumbs = array(
	'/tools/'            => esc_html__( 'Tools', 'gp-toolbox' ),
	'/tools/glossaries/' => esc_html__( 'Glossaries', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title'       => esc_html__( 'Glossaries', 'gp-toolbox' ), // Page title.
	'description' => esc_html__( 'Overview of all Global and Project Glossaries.', 'gp-toolbox' ), // Page description.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<p class="gptoolbox-description">
	<?php echo wp_kses_post( __( 'The type of Glossaries can be <code>global</code> or <code>project</code>.', 'gp-toolbox' ) ); ?>
	<br>
	<?php echo wp_kses_post( __( 'Each Glossary belongs to a Translation Set, identified by <code>translation_set_id</code>. If there is no Translation Set in the database with the same ID, then the Glossary is orphaned.', 'gp-toolbox' ) ); ?>
</p>

<?php

// Get GlotPress glossaries.
$gp_glossaries             = array();
$orphaned_glossary_entries = array();
foreach ( GP::$glossary->all() as $glossary ) {
	$gp_glossaries[ $glossary->id ] = $glossary;
}

// Get GlotPress glossary entries.
$gp_glossary_entries             = array();
$gp_glossary_entries_by_glossary = array();
foreach ( GP::$glossary_entry->all() as $glossary_entry ) {
	$gp_glossary_entries[ $glossary_entry->id ] = $glossary_entry;

	// Set orphaned glossary entries.
	if ( ! isset( $gp_glossaries[ $glossary_entry->glossary_id ] ) ) {
		$orphaned_glossary_entries[ $glossary_entry->glossary_id ][ $glossary_entry->id ] = $glossary_entry;
	}

	// Set glossary entries by glossary.
	$gp_glossary_entries_by_glossary[ $glossary_entry->glossary_id ][ $glossary_entry->id ] = $glossary_entry;
}

// Get GlotPress translation sets.
$gp_translation_sets = array();
foreach ( GP::$translation_set->all() as $translation_set ) {
	$gp_translation_sets[ $translation_set->id ] = $translation_set;
}

// Get GlotPress projects.
$gp_projects = array();
foreach ( GP::$project->all() as $project ) {
	$gp_projects[ $project->id ] = $project;
}

// TODO: Allow delete orphaned entries.

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

			$global_glossaries_count       = 0;
			$project_glossaries_count      = 0;
			$unknown_type_glossaries_count = 0;

			foreach ( $gp_glossaries as $glossary ) {
				// Try to get glossary translation set.
				$translation_set = $gp_translation_sets[ $glossary->translation_set_id ] ?? false;

				// Set the glossary type.
				if ( $translation_set && $translation_set->project_id === 0 ) {
					++$global_glossaries_count;
				} elseif ( $translation_set && $translation_set->project_id !== 0 ) {
					++$project_glossaries_count;
				} else {
					++$unknown_type_glossaries_count;
				}
			}

			// Glossaries: All {total} originals. {global} global glossaries. {project} project glossaries.
			echo wp_kses_post(
				sprintf(
					/* translators: %s: Links to filter the table. */
					__( 'Glossaries: %s', 'gp-toolbox' ),
					sprintf(
						'%1$s %2$s %3$s %4$s %5$s',
						'<a id="glossaries-type-all" class="glossaries-type" href="#glossaries">' . sprintf(
							/* translators: %s: Number of Glossaries. */
							_n( '%s glossary.', 'All %s glossaries.', $global_glossaries_count + $project_glossaries_count + $unknown_type_glossaries_count + count( $orphaned_glossary_entries ), 'gp-toolbox' ),
							'<strong class="glossaries-label glossaries-label-all">' . esc_html( number_format_i18n( $global_glossaries_count + $project_glossaries_count + $unknown_type_glossaries_count + count( $orphaned_glossary_entries ) ) ) . '</strong>'
						) . '</a>',
						$global_glossaries_count > 0 ? '<a id="glossaries-type-global" class="glossaries-type" href="#glossaries">' . sprintf(
							/* translators: %s: Number of Glossaries. */
							_n( '%s Global glossary.', '%s Global glossaries.', $global_glossaries_count, 'gp-toolbox' ),
							'<strong class="glossaries-label glossaries-label-global">' . esc_html( number_format_i18n( $global_glossaries_count ) ) . '</strong>'
						) . '</a>' : '',
						$project_glossaries_count > 0 ? '<a id="glossaries-type-project" class="glossaries-type" href="#glossaries">' . sprintf(
							/* translators: %s: Number of Glossaries. */
							_n( '%s Project glossary.', '%s Project glossaries.', $project_glossaries_count, 'gp-toolbox' ),
							'<strong class="glossaries-label glossaries-label-project">' . esc_html( number_format_i18n( $project_glossaries_count ) ) . '</strong>'
						) . '</a>' : '',
						$unknown_type_glossaries_count > 0 ? '<a id="glossaries-set-unknown" class="glossaries-type" href="#glossaries">' . sprintf(
							/* translators: %s: Number of Glossaries. */
							_n( '%s Glossary of unknown translation set.', '%s Glossaries of unknown translation set.', $unknown_type_glossaries_count, 'gp-toolbox' ),
							'<strong class="glossaries-label glossaries-label-project">' . esc_html( number_format_i18n( $unknown_type_glossaries_count ) ) . '</strong>'
						) . '</a>' : '',
						count( $orphaned_glossary_entries ) > 0 ? '<a id="glossaries-unknown-orphaned-entries" class="glossaries-type" href="#glossaries">' . sprintf(
							/* translators: %s: Number of Glossaries. */
							_n( 'Entries from %s unknown glossary.', 'Entries from %s unknown glossaries.', count( $orphaned_glossary_entries ), 'gp-toolbox' ),
							'<strong class="glossaries-label glossaries-label-project">' . esc_html( number_format_i18n( count( $orphaned_glossary_entries ) ) ) . '</strong>'
						) . '</a>' : ''
					)
				)
			);

			?>
		</p>
		<?php
	}

	// Check for glossaries and orphaned glossary entries.
	if ( ! empty( $gp_glossaries ) || ! empty( $orphaned_glossary_entries ) ) {

		?>
		<div class="glossaries-filter">
			<label for="glossaries-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="glossaries-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="glossaries-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox tools-glossaries">
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

				foreach ( $gp_glossaries as $glossary ) {

					// Try to get glossary translation set.
					$translation_set = $gp_translation_sets[ $glossary->translation_set_id ] ?? false;

					// Check wether is a Global or Project glossary.
					$glossary_type = ( $translation_set && $translation_set->project_id === 0 ) ? 'global' : 'project';

					?>
					<tr gptoolboxdata-glossary="<?php echo esc_attr( strval( $glossary->id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $glossary->id ) ); ?></td>
						<?php

						// Check if translation set is known. Double check for GP_Translation_Set object.
						if ( ! $translation_set ) {
							// Unknown translation set.

							?>
							<td class="translation-set unknown" data-text="" colspan="3">
								<span class="unknown">
									<?php
									printf(
										/* translators: Known identifier data. */
										esc_html__( 'Unknown translation set (%s)', 'gp-toolbox' ),
										sprintf(
											/* translators: %d ID number. */
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											esc_html( $glossary->translation_set_id )
										)
									);
									?>
								</span>
							</td>

							<td class="entries">
								<?php
								$count = isset( $gp_glossary_entries_by_glossary[ $glossary->id ] ) ? count( $gp_glossary_entries_by_glossary[ $glossary->id ] ) : 0;
								echo esc_html( number_format_i18n( $count ) );
								?>
							</td>
							<?php

						} elseif ( $glossary_type === 'global' ) {
							// Known translation set.
							// Global glossary.

							$global_glossary_path = gp_url_join( gp_url( '/languages' ), $translation_set->locale, $translation_set->slug, 'glossary' );

							?>
							<td class="type global" data-text="global">
								<?php
								// Global glossary.
								esc_html_e( 'Global', 'gp-toolbox' );
								?>
							</td>

							<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->name ); ?>">
								<?php
								gp_link( $global_glossary_path, $translation_set->name );
								?>
							</td>

							<td class="project" data-text=""></td>

							<td class="entries">
								<?php
								$count = isset( $gp_glossary_entries_by_glossary[ $glossary->id ] ) ? count( $gp_glossary_entries_by_glossary[ $glossary->id ] ) : 0;
								gp_link( $global_glossary_path, number_format_i18n( $count ) );
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

							$project = $gp_projects[ $translation_set->project_id ] ?? false;

							if ( ! $project ) {
								// Unknown project.

								?>
								<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->name ); ?>">
									<?php
									echo esc_html( $translation_set->name );
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
									$count = isset( $gp_glossary_entries_by_glossary[ $glossary->id ] ) ? count( $gp_glossary_entries_by_glossary[ $glossary->id ] ) : 0;
									echo esc_html( number_format_i18n( $count ) );
									?>
								</td>
								<?php

							} else {
								// Known project.

								$project_glossary_path = gp_url_join( gp_url_project_locale( $project->path, $translation_set->locale, $translation_set->slug ), 'glossary' );

								?>
								<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->name ); ?>">
									<?php
									gp_link( $project_glossary_path, $translation_set->name );
									?>
								</td>

								<td class="project" data-text="<?php echo esc_attr( $project->path ); ?>">
									<?php
									gp_link_project( $project, esc_html( $project->name ) );
									?>
								</td>

								<td class="entries">
									<?php
									$count = isset( $gp_glossary_entries_by_glossary[ $glossary->id ] ) ? count( $gp_glossary_entries_by_glossary[ $glossary->id ] ) : 0;
									gp_link( $project_glossary_path, number_format_i18n( $count ) );
									?>
								</td>
								<?php
							}
						}
						?>
					</tr>
					<?php
				}

				if ( ! empty( $orphaned_glossary_entries ) ) {
					// Add orphaned Glossary Entries by Glossary ID.

					foreach ( $orphaned_glossary_entries as $glossary_id => $orphaned_glossary_entries_by_glossary_id ) {
						?>
						<tr gptoolboxdata-glossary="<?php echo esc_attr( strval( $glossary_id ) ); ?>">

							<td class="id unknown" data-text="" colspan="4">
									<span class="unknown">
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: 1: Glossary entries count. 2: Known identifier data. */
											_n(
												'%1$d orphaned glossary entry from unknown glossary (%2$s)',
												'%1$d orphaned glossary entries from unknown glossary (%2$s)',
												count( $orphaned_glossary_entries_by_glossary_id ),
												'gp-toolbox'
											),
											esc_html( number_format_i18n( count( $orphaned_glossary_entries_by_glossary_id ) ) ),
											sprintf(
												/* translators: %d ID number. */
												esc_html__( 'ID #%d', 'gp-toolbox' ),
												esc_html( strval( $glossary_id ) )
											)
										)
									);
									?>
								</span>
							</td>

							<td class="entries">
								<?php
								echo esc_html( number_format_i18n( count( $orphaned_glossary_entries_by_glossary_id ) ) );
								?>
							</td>

						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}
	?>
</section>
<?php

// Load GlotPress Footer template.
gp_tmpl_footer();
