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

// Get page title.
gp_title( __( 'Translation Sets &lt; Tools &lt; GlotPress', 'gp-toolbox' ) );

// Load GlotPress breadcrumbs.
gp_breadcrumb(
	array(
		gp_link_get( gp_url( '/tools/' ), esc_html__( 'Tools', 'gp-toolbox' ) ),
		gp_link_get( gp_url( '/tools/translation-sets/' ), esc_html__( 'Translation Sets', 'gp-toolbox' ) ),
	)
);

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Translation Sets', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p>
	<?php esc_html_e( 'Overview of all Translation Sets.', 'gp-toolbox' ); ?>
</p>

<?php

// Get GlotPress translation sets.
$gp_translation_sets = GP::$translation_set->all();

$orphaned_translation_sets = array();
foreach ( $gp_translation_sets as $gp_translation_set ) {
	$project_exist = GP::$project->get( $gp_translation_set->project_id );
	if ( ! $project_exist ) {
		$orphaned_translation_sets[ $gp_translation_set->project_id ][ $gp_translation_set->id ] = $gp_translation_set;
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

		<table class="gp-table gp-toolbox translation-sets">
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

				foreach ( $gp_translation_sets as $gp_translation_set ) {

					if ( $gp_translation_set->project_id === 0 ) {
						// Don't show Locale Glossary virtual projects with ID '0'.
						continue;
					}

					?>
					<tr gptoolboxdata-translation-set="<?php echo esc_attr( strval( $gp_translation_set->id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $gp_translation_set->id ) ); ?></td>
						<td class="name"><?php echo esc_html( strval( $gp_translation_set->name ) ); ?></td>
						<td class="slug"><?php echo esc_html( strval( $gp_translation_set->slug ) ); ?></td>
						<?php

						// Get translation set project.
						$project = GP::$project->get( $gp_translation_set->project_id );

						// Check if project is known. Double check for GP_Project object.
						if ( ! $project || ! is_a( $project, 'GP_Project' ) ) {
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
											esc_html( $gp_translation_set->project_id )
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
							$translation_set_locale = GP_Locales::by_slug( $gp_translation_set->locale );

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
