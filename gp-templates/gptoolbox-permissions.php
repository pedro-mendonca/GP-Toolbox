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
	'/tools/'             => esc_html__( 'Tools', 'gp-toolbox' ),
	'/tools/permissions/' => esc_html__( 'Permissions', 'gp-toolbox' ),
);

// Get GlotPress page title.
Toolbox::page_title( $breadcrumbs );

// Get GlotPress breadcrumbs.
Toolbox::page_breadcrumbs( $breadcrumbs );

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Permissions', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p class="gptoolbox-description">
	<?php esc_html_e( 'Overview of all Administrators and Validators for each Project and Translation Set.', 'gp-toolbox' ); ?>
	<br>
	<?php echo wp_kses_post( __( 'The Permissions can be either <code>Admin</code> or <code>Validator</code>. Admins can administer GlotPress, and Validators can create, edit and approve translations for the specified Translation Sets.', 'gp-toolbox' ) ); ?>
	<br>
	<?php echo wp_kses_post( __( 'Each Permission belongs to a User, identified by <code>user_id</code>. If there is no User in the WordPress database with the same ID, then the Permission is orphaned.', 'gp-toolbox' ) ); ?>
	<br>
	<?php echo wp_kses_post( __( 'Each Validator Permission has a specified <code>project|locale|set-slug</code>. If there is no Project or Translation Set in the database with the same IDs, then the Permission is orphaned.', 'gp-toolbox' ) ); ?>
</p>

<?php
// Get GlotPress permissions.
$gp_permissions = array();
foreach ( GP::$permission->all() as $permission ) {
	$gp_permissions[ $permission->id ] = $permission;
}

// Get GlotPress translation sets.
$gp_translation_sets                                   = array();
$gp_translation_sets_by_project_id_and_locale_and_slug = array();
foreach ( GP::$translation_set->all() as $translation_set ) {
	$gp_translation_sets[ $translation_set->id ] = $translation_set;

	// Translation Sets by Project ID, slug and locale, matching the Validator data values in the DB. This avoids querying the DB for every Project Permission.
	$gp_translation_sets_by_project_id_and_locale_and_slug[ $translation_set->project_id . '|' . $translation_set->locale . '|' . $translation_set->slug ] = $translation_set;
}

// Get GlotPress projects.
$gp_projects = array();
foreach ( GP::$project->all() as $project ) {
	$gp_projects[ $project->id ] = $project;
}

// GlotPress core permissions.
$gp_permission_types = array(
	'admin'   => array(
		'singular' => esc_html_x( 'Administrator', 'Singular noun', 'gp-toolbox' ),
		'plural'   => esc_html_x( 'Administrators', 'Plural noun', 'gp-toolbox' ),
	),
	'approve' => array(
		'singular' => esc_html_x( 'Validator', 'Singular noun', 'gp-toolbox' ),
		'plural'   => esc_html_x( 'Validators', 'Plural noun', 'gp-toolbox' ),
	),
);

// Organize permissions by type.
$gp_toolbox_permissions_by_type = array();

foreach ( $gp_permissions as $permission_id => $permission ) {

	if ( $permission->action === 'admin' ) {
		$gp_toolbox_permissions_by_type['admin'][ $permission_id ] = $permission->user_id;
	} else {
		$gp_toolbox_permissions_by_type[ $permission->action ][ $permission->user_id ][ $permission->object_type ][ $permission_id ] = $permission->object_id;
	}
}

?>
<section class="gp-toolbox permissions admins">
	<h3>
		<?php
		// Administrators heading.
		echo esc_html( $gp_permission_types['admin']['plural'] );
		?>
	</h3>
	<?php

	// Check for Administrators.
	if ( empty( $gp_toolbox_permissions_by_type['admin'] ) ) {
		?>
		<p id="permission-admin-count"><?php esc_html_e( 'No permissions found.', 'gp-toolbox' ); ?></p>
		<?php
	} else {
		?>
		<p id="permission-admin-count">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: Permissions count. */
					_n(
						'%s Permission found.',
						'%s Permissions found.',
						count( $gp_toolbox_permissions_by_type['admin'] ),
						'gp-toolbox'
					),
					'<span class="count">' . number_format_i18n( count( $gp_toolbox_permissions_by_type['admin'] ) ) . '</span>'
				)
			);
			?>
		</p>

		<div class="permission-admin-filter">
			<label for="permission-admin-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="permission-admin-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="permission-admin-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox tools-permission-admin">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-user"><?php esc_html_e( 'User', 'gp-toolbox' ); ?></th>
					<th class="gp-column-actions sorter-false">&mdash;</th>
				</tr>
			</thead>
			<tbody>
				<?php


				foreach ( $gp_toolbox_permissions_by_type['admin'] as $permission_id => $user_id ) {

					$user = get_user_by( 'id', $user_id );

					?>
					<tr gptoolboxdata-permission="<?php echo esc_attr( strval( $permission_id ) ); ?>">
						<td class="id"><?php echo esc_html( strval( $permission_id ) ); ?></td>

							<?php
							if ( $user ) {
								?>
								<td class="user" data-text="<?php echo esc_attr( $user->user_login ); ?>">
									<a href="<?php echo esc_url( gp_url_profile( $user->user_nicename ) ); ?>"><?php echo esc_html( $user->user_login ); ?></a>
								</td>
								<?php
							} else {
								?>
								<td class="user" data-text="">
									<span class="unknown" data-text="">
										<?php
										printf(
											/* translators: Known identifier data. */
											esc_html__( 'Unknown user (%s)', 'gp-toolbox' ),
											sprintf(
												/* translators: %d ID number. */
												esc_html__( 'ID #%d', 'gp-toolbox' ),
												esc_html( strval( $user_id ) )
											)
										);
										?>
									</span>
								</td>
								<?php
							}
							?>

						<td class="action">
							<div class="progress-notice" style="display: none;"></div>
							<button class="delete"><span class="dashicons dashicons-trash"></span></button>
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

<section class="gp-toolbox permissions validators">
	<h3 style="margin-top: 2em;">
		<?php
		// Validators heading.
		echo esc_html( $gp_permission_types['approve']['plural'] );
		?>
	</h3>
	<?php

	// Check for Validators.
	if ( empty( $gp_toolbox_permissions_by_type['approve'] ) ) {
		?>
		<p id="permission-validator-count"><?php esc_html_e( 'No permissions found.', 'gp-toolbox' ); ?></p>
		<?php
	} else {
		?>
		<p id="permission-validator-count">
			<?php

			$gp_toolbox_validators_count = 0;
			foreach ( $gp_toolbox_permissions_by_type['approve'] as $validator ) {
				foreach ( $validator as $permissions_by_type ) {
					$gp_toolbox_validators_count = $gp_toolbox_validators_count + count( $permissions_by_type );
				}
			}

			echo wp_kses_post(
				sprintf(
					/* translators: %s: Permissions count. */
					_n(
						'%s Permission found.',
						'%s Permissions found.',
						$gp_toolbox_validators_count,
						'gp-toolbox'
					),
					'<span class="count">' . number_format_i18n( $gp_toolbox_validators_count ) . '</span>'
				)
			);
			?>
		</p>

		<div class="permission-validator-filter">
			<label for="permission-validator-filter"><?php esc_html_e( 'Filter:', 'gp-toolbox' ); ?> <input id="permission-validator-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
			<button id="permission-validator-filter-clear" class="button" style="margin-bottom: 3px;" title="<?php esc_attr_e( 'Clear search filter.', 'gp-toolbox' ); ?>"><?php esc_html_e( 'Clear', 'gp-toolbox' ); ?></button>
		</div>

		<table class="gp-table gp-toolbox tools-permission-validator">
			<thead>
				<tr>
					<th class="gp-column-id"><?php esc_html_e( 'ID', 'gp-toolbox' ); ?></th>
					<th class="gp-column-user"><?php esc_html_e( 'User', 'gp-toolbox' ); ?></th>
					<th class="gp-column-project"><?php esc_html_e( 'Project', 'gp-toolbox' ); ?></th>
					<th class="gp-column-translation-set"><?php esc_html_e( 'Translation Set', 'gp-toolbox' ); ?></th>
					<th class="gp-column-actions sorter-false">&mdash;</th>
				</tr>
			</thead>
			<tbody>
				<?php


				foreach ( $gp_toolbox_permissions_by_type['approve'] as $user_id => $permissions_by_type ) {

					$user = get_user_by( 'id', $user_id );

					foreach ( $permissions_by_type as $permission_type => $permissions ) {

						asort( $permissions );

						$previous_permission_value = null;

						foreach ( $permissions as $permission_id => $current_permission_value ) {

							// Check duplicate.
							$duplicate = false;
							if ( $current_permission_value === $previous_permission_value ) {
								$duplicate = true;
							}

							$class = $duplicate ? 'duplicate' : '';

							?>
							<tr class="<?php echo esc_attr( $class ); ?>" gptoolboxdata-permission="<?php echo esc_attr( $permission_id ); ?>">

								<td class="id"><?php echo esc_html( $permission_id ); ?></td>
								<?php

								if ( $user ) {
									?>
									<td class="user" data-text="<?php echo esc_attr( $user->user_login ); ?>">
										<a href="<?php echo esc_url( gp_url_profile( $user->user_nicename ) ); ?>"><?php echo esc_html( $user->user_login ); ?></a>
									</td>
									<?php
								} else {
									?>
									<td class="user" data-text="">
										<span class="unknown" data-text="">
											<?php
											printf(
												/* translators: Known identifier data. */
												esc_html__( 'Unknown user (%s)', 'gp-toolbox' ),
												sprintf(
													/* translators: %d ID number. */
													esc_html__( 'ID #%d', 'gp-toolbox' ),
													esc_html( strval( $user_id ) )
												)
											);
											?>
										</span>
									</td>
									<?php
								}

								if ( $permission_type === 'project|locale|set-slug' ) {

									$data = explode( '|', $current_permission_value );

									$translation_set_project_id = $data[0];
									$translation_set_locale     = $data[1];
									$translation_set_slug       = $data[2];

									$project = $gp_projects[ $translation_set_project_id ] ?? false;

									$translation_set = $gp_translation_sets_by_project_id_and_locale_and_slug[ $current_permission_value ] ?? false;

									if ( ! $project ) {
										?>
										<td class="project" data-text="" colspan="2">
											<span class="unknown">
												<?php
												printf(
													/* translators: Known identifier data. */
													esc_html__( 'Unknown project (%s)', 'gp-toolbox' ),
													sprintf(
														/* translators: %d ID number. */
														esc_html__( 'ID #%d', 'gp-toolbox' ),
														esc_html( $translation_set_project_id )
													)
												);
												?>
											</span>
										</td>
										<?php
									} else {
										?>
										<td class="project" data-text="<?php echo esc_attr( $project->path ); ?>">
											<?php
											gp_link_project( $project, esc_html( $project->name ) );
											?>
										</td>
										<?php

										if ( $translation_set ) {
											?>
											<td class="translation-set" data-text="<?php echo esc_attr( $translation_set->locale . '/' . $translation_set->slug ); ?>">
												<?php
												gp_link( gp_url_project( $project, gp_url_join( $translation_set->locale, $translation_set->slug ) ), $translation_set->name_with_locale() );
												?>
											</td>
											<?php
										} else {
											?>
											<td class="translation-set" data-text="">
												<span class="unknown">
													<?php
													printf(
														/* translators: Known identifier data. */
														esc_html__( 'Unknown translation set (%s)', 'gp-toolbox' ),
														sprintf(
															'%s/%s',
															esc_html( $translation_set_locale ),
															esc_html( $translation_set_slug )
														)
													);
													?>
												</span>
											</td>
											<?php
										}
									}
								} else {
									// Unknown object type.
									?>
									<td class="unknown" colspan=2>
										<?php
										echo '<span class="unknown">'
										. sprintf(
											/* translators: Known identifier data. */
											esc_html__( 'Unknown type (%s)', 'gp-toolbox' ),
											sprintf(
												'"%s":"%s"',
												esc_html( $permission_type ),
												esc_html( $current_permission_value )
											)
										) . '</span>';
										?>
									</td>
									<?php
								}

								?>
								<td class="action">
									<div class="progress-notice" style="display: none;"></div>
									<button class="delete"><span class="dashicons dashicons-trash"></span></button>
									<?php
									if ( $duplicate ) {
										?>
										<span class="duplicate"><?php esc_html_e( 'Duplicate', 'gp-toolbox' ); ?></span>
										<?php
									}
									?>
								</td>
							</tr>
							<?php
							$previous_permission_value = $current_permission_value;
						}
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
// TODO: Check for Other Permissions.

/**
 * Fires after check default GlotPress permissions.
 *
 * @since 1.0.0
 *
 * @param array $gp_toolbox_permissions_by_type   Array of permissions by type.
 */
do_action( 'gp_toolbox_after_known_permissions', $gp_toolbox_permissions_by_type );

// Load GlotPress Footer template.
gp_tmpl_footer();
