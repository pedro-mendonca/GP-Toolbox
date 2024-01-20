<?php

// Get page title.
gp_title( __( 'Permissions &lt; Tools &lt; GlotPress', 'gp-toolbox' ) );

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
		gp_link_get( gp_url( '/tools/permissions/' ), esc_html__( 'Permissions', 'gp-toolbox' ) ),
	)
);

// Load GlotPress Header template.
gp_tmpl_header();

$args = array(
	'title' => esc_html__( 'Permissions', 'gp-toolbox' ), // Page title.
);

// Load GP-Toolbox header template.
gp_tmpl_load( 'gptoolbox-header', $args );

?>
<div class="clear"></div>

<p>
	<?php esc_html_e( 'Overview of all GlotPress Administrators and Validators for each Project and Translation Set.', 'gp-toolbox' ); ?>
</p>

<?php
// Get GlotPress permissions.
$gp_permissions = GP::$permission->all();

// GlotPress core permissions.
$gp_permission_types = array(
	'admin'     => array(
		'singular' => esc_html__( 'Administrator', 'gp-toolbox' ),
		'plural'   => esc_html__( 'Administrators', 'gp-toolbox' ),
	),
	'approve'     => array(
		'singular' => esc_html__( 'Validator', 'gp-toolbox' ),
		'plural'   => esc_html__( 'Validators', 'gp-toolbox' ),
	),
);

// Organize permissions by type.
$gp_toolbox_permissions_by_type = array();

foreach ( $gp_permissions as $gp_permission ) {
	$gp_toolbox_permissions_by_type[ $gp_permission->action ][ $gp_permission->user_id ][] = $gp_permission;
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
			echo wp_kses_post( sprintf(
				/* translators: %s: Permissions count. */
				_n(
					'%s Permission found.',
					'%s Permissions found.',
					count( $gp_toolbox_permissions_by_type['admin'] ),
					'gp-toolbox'
				),
				'<span class="count">' . number_format_i18n( count( $gp_toolbox_permissions_by_type['admin'] ) ) . '</span>'
			) );
			?>
		</p>

		<div class="permission-admin-filter">
			<label for="permission-admin-filter"><?php _e( 'Filter:', 'gp-toolbox' ); ?> <input id="permission-admin-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
		</div>

		<table class="gp-table gp-toolbox permission-admin">
			<thead>
				<tr>
					<th class="gp-column-user"><?php _e( 'User', 'gp-toolbox' ); ?></th>
					<th class="gp-column-actions sorter-false">&mdash;</th>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $gp_toolbox_permissions_by_type['admin'] as $user_id => $permissions ) {

					$user = get_user_by( 'id', $user_id );

					foreach ( $permissions as $permission ) {
						?>
						<tr gptoolboxdata-permission="<?php echo esc_attr( $permission->id ); ?>">
							<td class="user">
								<?php
								if ( $user ) {
									?>
									<a href="<?php echo esc_url( gp_url_profile( $user->user_nicename ) ); ?>"><?php echo esc_html( $user->user_login ); ?></a>
									<?php
								} else {
									echo '<span class="unknown">'
									. sprintf(
										/* translators: Known identifier data. */
										esc_html__( 'Unknown (%s)', 'gp-toolbox' ),
										sprintf(
											/* translators: %d ID number. */
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											$user_id
										)
									) . '</span>';
								}
								?>
							</td>
							<td class="action">
								<div class="progress-notice" style="display: none;"></div>
								<button class="delete"><span class="dashicons dashicons-trash"></span></button>
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
			foreach ( $gp_toolbox_permissions_by_type['approve']  as $validator ) {
				foreach ( $validator as $permission ) {
					$gp_toolbox_validators_count++;
				}
			}

			echo wp_kses_post( sprintf(
				/* translators: %s: Permissions count. */
				_n(
					'%s Permission found.',
					'%s Permissions found.',
					$gp_toolbox_validators_count,
					'gp-toolbox'
				),
				'<span class="count">' . number_format_i18n( $gp_toolbox_validators_count ) . '</span>'
			) );
			?>
		</p>

		<div class="permission-validator-filter">
			<label for="permission-validator-filter"><?php _e( 'Filter:', 'gp-toolbox' ); ?> <input id="permission-validator-filter" type="text" placeholder="<?php esc_attr_e( 'Search', 'gp-toolbox' ); ?>" /> </label>
		</div>

		<table class="gp-table gp-toolbox permission-validator">
			<thead>
				<tr>
					<th class="gp-column-user"><?php _e( 'User', 'gp-toolbox' ); ?></th>
					<th class="gp-column-project"><?php _e( 'Project', 'gp-toolbox' ); ?></th>
					<th class="gp-column-translation-set"><?php _e( 'Translation Set', 'gp-toolbox' ); ?></th>
					<th class="gp-column-actions sorter-false">&mdash;</th>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ( $gp_toolbox_permissions_by_type['approve'] as $user_id => $permissions ) {

					$user = get_user_by( 'id', $user_id );

					$previous_permission = null;

					foreach ( $permissions as $permission ) {

						// Check duplicate.
						$duplicate = false;
						if ( ! is_null( $previous_permission ) && $permission->user_id === $previous_permission->user_id && $permission->object_type === $previous_permission->object_type && $permission->object_id === $previous_permission->object_id ) {
							$duplicate = true;
						}

						$class = $duplicate ? 'duplicate' : '';

						?>
						<tr class="<?php echo esc_attr( $class ); ?>" gptoolboxdata-permission="<?php echo esc_attr( $permission->id ); ?>">

							<td class="user">
								<?php
								if ( $user ) {
									?>
									<a href="<?php echo esc_url( gp_url_profile( $user->user_nicename ) ); ?>"><?php echo esc_html( $user->user_login ); ?></a>
									<?php
								} else {
									echo '<span class="unknown">'
									. sprintf(
										/* translators: Known identifier data. */
										esc_html__( 'Unknown (%s)', 'gp-toolbox' ),
										sprintf(
											/* translators: %d ID number. */
											esc_html__( 'ID #%d', 'gp-toolbox' ),
											$user_id
										)
									) . '</span>';
								}
								?>
							</td>

							<?php

							if ( $permission->object_type === 'project|locale|set-slug' ) {
								$data       = explode( '|', $permission->object_id );
								$project_id = $data[0];
								$locale     = $data[1];
								$slug       = $data[2];

								$project = GP::$project->get( $project_id );

								$translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project_id, $slug, $locale );

								?>
								<td class="project">
									<?php
									if ( $project ) {
										gp_link_project( $project, esc_html( $project->name ) );
									} else {
										echo '<span class="unknown">'
										. sprintf(
											/* translators: Known identifier data. */
											esc_html__( 'Unknown (%s)', 'gp-toolbox' ),
											sprintf(
												/* translators: %d ID number. */
												esc_html__( 'ID #%d', 'gp-toolbox' ),
												$project_id
											)
										) . '</span>';
									}
									?>
								</td>

								<?php
								if ( $project && $translation_set ) {
									?>
									<td class="translation-set unknown">
										<?php
										gp_link( gp_url_project( $project, gp_url_join( $translation_set->locale, $translation_set->slug ) ), $translation_set->name_with_locale() );
										?>
									</td>
									<?php
								} else {
									?>
									<td class="translation-set">
										<?php
										echo '<span class="unknown">'
										. sprintf(
											/* translators: Known identifier data. */
											esc_html__( 'Unknown (%s)', 'gp-toolbox' ),
											sprintf(
												'%s/%s',
												$locale,
												$slug
											)
										) . '</span>';
										?>
									</td>
									<?php
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
											'%s/%s',
											$permission->object_type,
											$permission->object_id
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

						$previous_permission = $permission;
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
?>

<script type="text/javascript" charset="utf-8">
	jQuery( document ).ready( function( $ ) {
		$( '.permission-admin' ).tablesorter( {
			theme: 'glotpress',
			sortList: [ [ 0, 0 ] ],
			headers: {
				0: {
					sorter: 'text',
				},
			},
		} );
		var permissionAdminsRows = $( '.permission-admin tbody' ).find( 'tr' );
		$( '#permission-admin-filter' ).bind( 'change keyup input', function() {
			var words = this.value.toLowerCase().split( ' ' );

			if ( '' === this.value.trim() ) {
				permissionAdminsRows.show();
			} else {
				permissionAdminsRows.hide();
				permissionAdminsRows.filter( function ( i, v ) {
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

		$( '.permission-validator' ).tablesorter( {
			theme: 'glotpress',
			sortList: [ [ 0, 0 ] ],
			headers: {
				0: {
					sorter: 'text',
				},
			},
		} );
		var permissionValidatorsRows = $( '.permission-validator tbody' ).find( 'tr' );
		$( '#permission-validator-filter' ).bind( 'change keyup input', function() {
			var words = this.value.toLowerCase().split( ' ' );

			if ( '' === this.value.trim() ) {
				permissionValidatorsRows.show();
			} else {
				permissionValidatorsRows.hide();
				permissionValidatorsRows.filter( function ( i, v ) {
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
gp_tmpl_footer();
