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
	'/tools/'              => esc_html__( 'Tools', 'gp-toolbox' ),
	'/tools/translations/' => esc_html__( 'Translations', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Translations', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p class="gptoolbox-description">
	<?php esc_html_e( 'Overview of all Translations.', 'gp-toolbox' ); ?>
</p>

<p class="gptoolbox-description">
	<?php
	echo wp_kses_post( __( 'Each Translation has a parent <code>original_id</code>. If there is no parent Original in the database with the same ID, then the Translation is orphaned.', 'gp-toolbox' ) );
	?>
	<br>
	<?php
	echo wp_kses_post( __( 'Each Translation has a parent <code>translation_set_id</code>. If there is no parent Translation Set in the database with the same ID, then the Translation is orphaned.', 'gp-toolbox' ) );
	?>
</p>

<p class="gptoolbox-description">
	<?php

	$statuses_list = array();
	foreach ( Toolbox::supported_translation_statuses() as $key => $status ) {
		$statuses_list[] = '<span class="translation-status ' . esc_attr( $key ) . '">' . esc_html( $status ) . '</span>';
	}

	echo wp_kses_post(
		wp_sprintf(
			/* translators: %l: List of translation statuses. */
			esc_html__( 'The Translations can have one of the following statuses: %l.', 'gp-toolbox' ),
			$statuses_list
		)
	);
	?>
</p>

<?php

// Get GlotPress translations.
$gp_translations = array();
foreach ( GP::$translation->all() as $translation ) {
	$gp_translations[ $translation->id ] = $translation;
}

$translations_by_translation_set = array();
$translations_by_original        = array();
foreach ( $gp_translations as $translation_id => $translation ) {
	$translations_by_translation_set[ $translation->translation_set_id ][ $translation_id ] = $translation;
	$translations_by_translation_set[ $translation->original_id ][ $translation_id ] = $translation;
}

// TODO: Allow delete Translations.

?>
<section class="gp-toolbox translations">
	<?php

	// Check for translations.
	if ( empty( $gp_translations ) ) {
		?>
		<p id="translations-filters"><?php esc_html_e( 'No translations found.', 'gp-toolbox' ); ?></p>
		<?php
	}

	// Check for translations and orphaned translations.
	if ( ! empty( $gp_translations ) ) {

		?>
		<div class="translations-filter">
			<label for="translations-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="translations-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="translations-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox tools-translations">
			<thead>
				<tr>
					<th class="gp-column-translation-set"><?php _e( 'Translation Set', 'gp-toolbox' ); ?></th>
					<th class="gp-column-project"><?php esc_html_e( 'Project', 'gp-toolbox' ); ?></th>
					<th class="gp-column-originals-known"><?php _e( 'Known Originals', 'gp-toolbox' ); ?></th>
					<th class="gp-column-originals-unknown"><?php _e( 'Unknown Originals', 'gp-toolbox' ); ?></th>
					<th class="gp-column-translations"><?php _e( 'Translations', 'gp-toolbox' ); ?></th>
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

/*
All translations
+active original
-obsolete original
known translation_set
unknown translation_set
known project
unknown project
*/

$projects = array();
foreach ( GP::$project->all() as $project ) {
	$projects[ $project->id ] = $project;
}

$translation_sets = array();
foreach ( GP::$translation_set->all() as $translation_set ) {
	$translation_sets[ $translation_set->id ] = $translation_set;
}

$translations = array();
foreach ( GP::$translation->all() as $translation ) {
	$translations[ $translation->id ] = $translation;
}

$originals = array();
foreach ( GP::$original->all() as $original ) {
	$originals[ $original->id ] = $original;
}

echo 'All translations: ' . count( $translations ) . '<br>';
?>

<p>
	<?php
	printf(
		/* translators: 1: Active status; 2: Obsolete status. */
		esc_html__( 'Translations can be from a known Original (%1$s or %2$s), or from an Original that don\'t exist anymore in the database.', 'gp-toolbox' ),
		'<code>+active</code>',
		'<code>-obsolete</code>'
	); ?>
</p>


<div class="clear"></div>
<?php



if ( ! empty( $translations ) ) {

	$translations_by_original_status        = array();
	$translations_by_translation_set_status = array();

	foreach ( $translations as $translation ) {

		if ( isset( $originals[ $translation->original_id ] ) ) {
			$original = $originals[ $translation->original_id ];
			$translations_by_original_status[ $original->status ][ $translation->id ] = $translation;
		} else {
			$translations_by_original_status['unknown'][ $translation->id ] = $translation;
		}

		if ( isset( $translation_sets[ $translation->translation_set_id ] ) ) {
			$translation_set = $translation_sets[ $translation->translation_set_id ];
			$translations_by_translation_set_status[ $translation_set->id ][ $translation->id ] = $translation;
		} else {
			$translations_by_translation_set_status['unknown'][ $translation->id ] = $translation;
		}

	}

	echo '<p>Translations by Original status:</p>';
	echo '<ul>';
	foreach ( $translations_by_original_status as $status => $translation_by_original_status ) {
		echo '<li>' . $status . ' (' . count( $translation_by_original_status ) . ')';
	}
	echo '</ul>';



	$sets = array();

	foreach ( $translations as $translation ) {
		//var_dump( $translation );
		$sets[ $translation->translation_set_id ][] = $translation;

	}

	ksort( $sets );


	?>


	<div class="translations-filter">
		<label for="translations-filter"><?php _e( 'Filter:', 'gp-toolbox' ); ?> <input id="translations-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
	</div>

	<style>
		table.gp-table.translations {
			width: auto;
		}
		tr.tablesorter-hasChildRow {
			cursor: pointer;
		}
		.tablesorter-glotpress .tablesorter-header:not(.sorter-false) {
			padding-right: 2.5em;
			padding-left: 1em;
		}
		td[data-text="0"] {
			text-align: center;
			vertical-align: middle;
			color: var( --gp-color-fg-muted );
			font-style: italic;
		}
		td.stats {
			text-align: center;
		}
	</style>

	<table class="gp-table translations">
		<thead>
			<tr>
				<th class="gp-column-project"><?php _e( 'Translation Set', 'gp-toolbox' ); ?></th>
				<?php /*
				<th class="gp-column-locale"><?php _e( 'Project', 'gp-toolbox' ); ?></th>
				*/ ?>

				<th class="gp-column-translations"><?php _e( 'Original', 'gp-toolbox' ); ?></th>

				<th class="gp-column-translations"><?php _e( 'Translations', 'gp-toolbox' ); ?></th>

			</tr>
		</thead>
		<tbody>
			<?php

			$sets = [];

			foreach ( $translations as $translation ) {
				$sets[ $translation->translation_set_id ][] = $translation;
			}

			foreach ( $sets as $id => $set ) {

				?>
				<tr>
					<?php

					$translation_set = GP::$translation_set->get( $id );

					if ( $translation_set ) {
						$project = GP::$project->get( $translation_set->project_id );

						?>
						<td class="translation-set" data-text="<?php echo esc_attr( gp_url_join( $translation_set->locale, $translation_set->slug ) ); ?>">
							<?php gp_link( gp_url_project( $project, gp_url_join( $translation_set->locale, $translation_set->slug ) ), $translation_set->name_with_locale() . ' (ID #' . esc_html( $translation_set->id ) . ')' ); ?>
						</td>

						<td class="original" data-text="">

						</td>

						<?php
						/*
						<td class="project" data-text="<?php echo esc_attr( $project->path ); ?>">
							<?php gp_link_project( $project, esc_html( $project->name ) . ' (ID #' . esc_html( $project->id ) . ')' ); ?>
						</td>
						*/
						?>

						<td class="stats" data-text="<?php echo esc_attr( count( $set ) ); ?>">
							<?php

							//var_dump( $translation_set->all_count );
							gp_link(
								gp_url_project(
									$project,
									gp_url_join( $translation_set->locale, $translation_set->slug ),
									array(
										// 'filters[status]' => 'all',
										'filters[status]' => 'current_or_waiting_or_fuzzy_or_rejected_or_old',
									)
								),
								number_format_i18n( count( $set ) )
							);

							?>
						</td>
						<?php

					} else {

						?>
						<td class="translation-set" data-text="0">
							<?php printf(
								/* translators: Known identifier data. */
								esc_html__( 'Unknown (ID #%d)', 'gp-toolbox' ),
								esc_html( $id )
							); ?>
						</td>

						<td class="original" data-text="">

						</td>

						<?php
						/*
						<td class="project" data-text="0">
							<?php printf(
								esc_html__( 'Unknown (ID #%d)', 'gp-toolbox' ),
								esc_html( $id )
							); ?>
						</td>
						*/
						?>

						<td class="stats" data-text="<?php echo esc_attr( count( $set ) ); ?>"><?php echo number_format_i18n( count( $set ) ); ?></td>
						<?php

					}

					?>

				</tr>
				<?php

			}

			?>
		</tbody>
	</table>

	<script type="text/javascript" charset="utf-8">
		jQuery( document ).ready( function( $ ) {
			$( '.translations' ).tablesorter( {
				theme: 'glotpress',
				sortList: [[0,0]],
				headers: {
					0: {
						sorter: 'text'
					}
				}
			} );

			rows = $( '.translations tbody' ).find( 'tr' );

			$( '#translations-filter' ).bind( 'change keyup input', function() {
				var words = this.value.toLowerCase().split( ' ' );

				if ( '' === this.value.trim() ) {
					rows.show();
				} else {
					rows.hide();
					rows.filter( function ( i, v ) {
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
		} );
	</script>
	<?php

}

gp_tmpl_footer();
