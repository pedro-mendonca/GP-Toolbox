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
use GP_Locales;

// Set the page breadcrumbs.
$breadcrumbs = array(
	'/tools/'                  => esc_html__( 'Tools', 'gp-toolbox' ),
	'/tools/translation-sets/' => esc_html__( 'Translation Sets', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Translation Sets', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p class="gptoolbox-description">
	<?php esc_html_e( 'Overview of all Translation Sets.', 'gp-toolbox' ); ?>
</p>

<p class="gptoolbox-description">
	<?php
	echo wp_kses_post( __( 'Each Translation Set has a parent <code>project_id</code>. If there is no parent Project in the database with the same ID, then the Translation Set is orphaned.', 'gp-toolbox' ) );
	?>
</p>

<?php

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

$orphaned_translation_sets = array();
foreach ( $gp_translation_sets as $translation_set ) {
	if ( ! isset( $gp_projects[ $translation_set->project_id ] ) ) {
		$orphaned_translation_sets[ $translation_set->project_id ][ $translation_set->id ] = $translation_set;
	}
}

// TODO: Allow delete Translation Sets entries.

?>
<section class="gp-toolbox translation-sets">
	<?php

	// Check for translation sets.
	if ( empty( $gp_translation_sets ) ) {
		?>
		<p id="translation-sets-filters"><?php esc_html_e( 'No translation sets found.', 'gp-toolbox' ); ?></p>
		<?php
	}

	// Check for translation sets and orphaned translations.
	if ( ! empty( $gp_translation_sets ) || ! empty( $orphaned_translations ) ) {

		?>
		<div class="translation-sets-filter">
			<label for="translation-sets-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="translation-sets-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="translation-sets-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox tools-translation-sets">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-name"><?php esc_html_e( 'Name', 'gp-toolbox' ); ?></th>
					<th class="gp-column-slug"><?php esc_html_e( 'Slug', 'gp-toolbox' ); ?></th>
					<th class="gp-column-project"><?php esc_html_e( 'Project', 'gp-toolbox' ); ?></th>
					<th class="gp-column-set-locale"><?php esc_html_e( 'Locale', 'gp-toolbox' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $gp_translation_sets as $translation_set ) {

					if ( $translation_set->project_id === 0 ) {
						// Don't show Locale Glossary virtual projects with ID '0'.
						continue;
					}

					?>
					<tr gptoolboxdata-translation-set="<?php echo esc_attr( strval( $translation_set->id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $translation_set->id ) ); ?></td>
						<td class="name"><?php echo esc_html( strval( $translation_set->name ) ); ?></td>
						<td class="slug"><?php echo esc_html( strval( $translation_set->slug ) ); ?></td>
						<?php

						// Get translation set project.
						$project = $gp_projects[ $translation_set->project_id ] ?? false;

						// Check if project is known. Double check for GP_Project object.
						if ( ! $project ) {
							// Unknown project.

							?>
							<td class="project unknown" data-text="">
								<span class="unknown">
									<?php
									printf(
										/* translators: Known identifier data. */
										esc_html__( 'Unknown project (%s)', 'gp-toolbox' ),
										sprintf(
											/* translators: %d ID number. */
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											esc_html( $translation_set->project_id )
										)
									);
									?>
								</span>
							</td>
							<?php
						} else {
							// Known project.

							?>
							<td class="project" data-text="<?php echo esc_attr( $project->name ); ?>">
								<?php
								gp_link_project( $project, esc_html( $project->name ) );
								?>
							</td>
							<?php
						}
						?>
						<td class="set-locale">
							<?php
							// Get translation set locale.
							$translation_set_locale = GP_Locales::by_slug( $translation_set->locale );

							gp_link( gp_url_join( gp_url( '/languages' ), $translation_set_locale->slug ), $translation_set_locale->slug );
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
<?php

// Load GlotPress Footer template.
gp_tmpl_footer();
