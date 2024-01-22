/* global document, gpToolboxTools, Intl, wp, sprintf, wpApiSettings */

jQuery( document ).ready( function( $ ) {
	// Get User login (username).
	var userLogin = gpToolboxTools.user_login;

	// Get User Locale.
	var userLocale = gpToolboxTools.user_locale;

	// Get the Admin permissions table.
	var tablePermissionAdmin = $( 'table.gp-table.permission-admin' );

	// Get the Validator permissions table.
	var tablePermissionValidator = $( 'table.gp-table.permission-validator' );

	// Check if user is has GlotPress Admin previleges.
	var glotpressAdmin = gpToolboxTools.admin;

	// Set the data attrib prefix.
	var dataPrefix = 'gptoolboxdata-';

	// Check if the Permission Validators table exist.
	if ( tablePermissionAdmin.length ) {
		// Capture button click.
		$( tablePermissionAdmin ).children( 'tbody' ).children( 'tr' ).each(
			function() {
				var permissionID = $( this ).attr( 'gptoolboxdata-permission' );

				// Check if user has GlotPress administrator previleges.
				if ( glotpressAdmin ) {
					// Check if current user matches the row user to avoid remove self admin previleges.
					if ( $( this ).find( 'td.user' ).text().trim() === userLogin ) {
						// Disable delete button.
						$( this ).find( 'td.action button.delete' ).attr( 'disabled', true );
					} else {
						// Delete permission.
						$( this ).find( 'td.action button.delete' ).on( 'click', function() {
							permissionDelete( 'admin', permissionID );
						} );
					}
				}
			}
		);
	}

	// Check if the Permission Validators table exist.
	if ( tablePermissionValidator.length ) {
		// Capture button click.
		$( tablePermissionValidator ).children( 'tbody' ).children( 'tr' ).each(
			function() {
				var permissionID = $( this ).attr( 'gptoolboxdata-permission' );

				// Check if user has GlotPress administrator previleges.
				if ( glotpressAdmin ) {
					// Delete permission.
					$( this ).find( 'td.action button.delete' ).on( 'click', function() {
						permissionDelete( 'validator', permissionID );
					} );
				}
			}
		);
	}

	console.log( 'Loaded Tools JS' );

	/**
	 * Delete permission with specified ID.
	 *
	 * @param {string} permissionType : Type of the GP_Permission.
	 * @param {string} permissionID   : ID of the GP_Permission.
	 */
	function permissionDelete( permissionType, permissionID ) {
		// Find the table cell from any permissions table.
		var tr = $( 'table.gp-toolbox.permission-' + permissionType ).find( 'tbody tr[' + dataPrefix + 'permission="' + permissionID + '"]' );
		var td = $( tr ).find( 'td.action' );

		var notice = $( td ).find( 'div.progress-notice' );
		var button = $( td ).find( 'button.delete' );
		var bubble = $( td ).find( 'span.duplicate' );

		console.log( 'Deleting:', permissionType, permissionID );

		// Hide and disable button.
		$( button ).hide().attr( 'disabled', true );
		// Hide duplicate bubble.
		$( bubble ).hide();
		// Show progress notice.
		$( notice ).text( wp.i18n.__( 'Deleting...', 'gp-toolbox' ) ).fadeIn();

		$.ajax( {

			url: wpApiSettings.root + 'gp-toolbox/v1/permissions/' + permissionID + '/-delete',
			type: 'POST',
			data: {
				_wpnonce: gpToolboxTools.nonce,
			},

			success: function( response ) {
				var permissionsCount = $( 'p#permission-' + permissionType + '-count span.count' ).text();

				// Check response.
				if ( response.deleted === true ) {
					// console.log( 'TR:', tr );
					$( tr ).fadeOut();
					permissionsCount = permissionsCount - 1;

					if ( permissionsCount > 0 ) {
						// Update permissions count.
						$( 'p#permission-' + permissionType + '-count' ).html(
							sprintf(
								/* translators: %s: Permissions count. */
								wp.i18n._n(
									'%s Permission found.',
									'%s Permissions found.',
									permissionsCount,
									'gp-toolbox'
								),
								'<span class="count">' + new Intl.NumberFormat( userLocale.slug ).format( permissionsCount ) + '</span>'
							)
						);
					} else {
						// Update permissions count to 0 and hide Filter and Table.
						$( 'p#permission-' + permissionType + '-count' ).text( wp.i18n.__( 'No permissions found.', 'gp-toolbox' ) );
						// Hide table filter.
						$( 'div.permission-' + permissionType + '-filter' ).fadeOut();
						// Hide table.
						$( 'table.gp-toolbox.permission-' + permissionType ).fadeOut();
					}

					console.log( response.message );
				} else {
					// Hide progress notice.
					$( notice ).hide().text( '' );
					// Show progress notice.
					$( notice ).text(
						wp.i18n.sprintf(
							/* translators: %s Error message. */
							wp.i18n.__( 'Error: %s', 'gp-toolbox' ),
							response.message
						)
					);
					$( notice ).fadeIn();
					console.log( response.message );
				}
			},

			error: function( response ) {
				// Show the Error notice.
				console.log( 'Failed to delete permission.' );
				console.log( 'Error message:', response.responseJSON.message );
			},

			complete: function() {
				// Hide progress notice.
				// $( notice ).hide().text( '' );
			},
		} );
	}
} );
