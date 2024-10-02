<?php
/**
 * Template file.
 *
 * @package GP_Toolbox
 *
 * @since 1.0.6
 */

namespace GP_Toolbox;

use GP;
use GP_Meta;

// Set the page breadcrumbs.
$breadcrumbs = array(
	'/tools/'                  => esc_html__( 'Tools', 'gp-toolbox' ),
	'/tools/meta/' => esc_html__( 'Meta', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title'       => esc_html__( 'Meta', 'gp-toolbox' ), // Page title.
	'description' => esc_html__( 'Overview of all Meta.', 'gp-toolbox' ), // Page description.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<p class="gptoolbox-description">
	<?php
	echo wp_kses_post( __( 'Each Meta has a parent <code>object_id</code>. If there is no parent Object in the database with the same ID, then the Meta is orphaned.', 'gp-toolbox' ) );
	?>
</p>
<?php

// Get GlotPress Meta entries.
$gp_meta = array();
foreach ( GP::$meta->all() as $meta ) {
	$gp_meta[ $meta->id ] = $meta;
}

// Get GlotPress glossaries.
$gp_glossaries = array();
foreach ( GP::$glossary->all() as $glossary ) {
	$gp_glossaries[ $glossary->id ] = $glossary;
}

// Get GlotPress glossary entries.
$gp_glossary_entries = array();
foreach ( GP::$glossary_entry->all() as $glossary_entry ) {
	$gp_glossary_entries[ $glossary_entry->id ] = $glossary_entry;
}

// Get GlotPress permissions.
$gp_permissions = array();
foreach ( GP::$permission->all() as $permission ) {
	$gp_permissions[ $permission->id ] = $permission;
}

// Get GlotPress translations.
$gp_translations = array();
foreach ( GP::$translation->all() as $translation ) {
	$gp_translations[ $translation->id ] = $translation;
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

// Get GlotPress originals.
$gp_originals = array();
foreach ( GP::$original->all() as $original ) {
	$gp_originals[ $original->id ] = $original;
}

/*
$orphaned_meta = array();
foreach ( $gp_meta as $meta ) {
	if ( ! isset( $gp_projects[ $meta->project_id ] ) ) {
		$orphaned_meta[ $meta->project_id ][ $meta->id ] = $meta;
	}
}
*/

/*
// GP::$meta->all()
// Test the speed of the exampleFunction with arguments 5 and 10 over 10000 iterations
$averageTime = Toolbox::test_function_speed(
	function() {
		return gp_get_meta( 'project', '41', 'project_icon' );
	},
	array(),
	1000
);
var_dump( gp_get_meta( 'project', '41', 'project_icon' ) );
echo "Old gp_get_meta() average execution time: " . $averageTime . " seconds<br>";

$averageTime = Toolbox::test_function_speed(
	function() {
		return GP::$meta->by_object_type_object_id_and_meta_key( 'project', '41', 'project_icon' );
	},
	array(),
	1000
);

var_dump( GP::$meta->by_object_type_object_id_and_meta_key( 'project', '41', 'project_icon' )->meta_value );
echo 'New GP::$meta->get() average execution time: ' . $averageTime . " seconds<br>";
*/

// TODO: Allow delete Meta entries.

?>
<section class="gp-toolbox meta">
	<?php

	// Check for meta.
	if ( empty( $gp_meta ) ) {
		?>
		<p id="meta-filters"><?php esc_html_e( 'No meta found.', 'gp-toolbox' ); ?></p>
		<?php
	}

	// Check for meta and orphaned translations.
	if ( ! empty( $gp_meta ) || ! empty( $orphaned_meta ) ) {

		?>
		<div class="meta-filter">
			<label for="meta-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="meta-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="meta-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox tools-meta">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-object-type"><?php esc_html_e( 'Object Type', 'gp-toolbox' ); ?></th>
					<th class="gp-column-object-id"><?php esc_html_e( 'Object ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-meta-key"><?php esc_html_e( 'Meta Key', 'gp-toolbox' ); ?></th>
					<th class="gp-column-meta-value"><?php esc_html_e( 'Meta Value', 'gp-toolbox' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $gp_meta as $meta ) {

					?>
					<tr gptoolboxdata-meta="<?php echo esc_attr( strval( $meta->id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $meta->id ) ); ?></td>
						<td class="object-type"><?php echo esc_html( $meta->object_types[ $meta->object_type ] ); ?></td>
						<td class="object-id"><?php echo esc_html( strval( $meta->object_id ) ); ?></td>
						<td class="object-meta-key"><?php echo esc_html( strval( $meta->meta_key ) ); ?></td>
						<td class="object-meta-value"><?php echo esc_html( strval( $meta->meta_value ) ); ?></td>
						<?php
						/*

						// Get meta project.
						$project = $gp_projects[ $meta->project_id ] ?? false;

						// Check if project is known. Double check for GP_Project object.
						if ( ! $project ) {
							// Unknown project.

							?>
							<td class="project unknown" data-text="">
								<span class="unknown">
									<?php
									printf(
										// translators: Known identifier data.
										esc_html__( 'Unknown project (%s)', 'gp-toolbox' ),
										sprintf(
											// translators: %d ID number.
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											esc_html( $meta->project_id )
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
						*/
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
<?php

// Load GlotPress Footer template.
gp_tmpl_footer();
