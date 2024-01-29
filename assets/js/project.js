/* global document, Intl, gpToolbox, wp, wpApiSettings */

jQuery( document ).ready( function( $ ) {
	// Get User Locale.
	var userLocale = gpToolbox.user_locale;

	// Get the supported translation statuses.
	var supportedTranslationStatuses = gpToolbox.supported_statuses;

	// Get the Translation Sets table.
	var tableTranslationSets = $( 'table.gp-table.translation-sets' );

	// Check if user is has GlotPress Admin previleges.
	var glotpressAdmin = gpToolbox.admin;

	// Get the Base URL for GlotPress Projects.
	var gpUrlProject = gpToolbox.gp_url_project;

	// Get the Project.
	var project = gpToolbox.args.project;

	// Set the data attrib prefix.
	var dataPrefix = 'gptoolboxdata-';

	// Get the highlight_counts setting.
	var highlightCounts = false;
	if ( gpToolbox.highlight_counts === '1' ) {
		highlightCounts = true;
	}

	console.log( 'Supported translation statuses', supportedTranslationStatuses );

	// Check if the Translation Sets table exist.
	if ( tableTranslationSets.length ) {
		// Check if the 'old' status is supported.
		if ( supportedTranslationStatuses.hasOwnProperty( 'old' ) ) {
			// Add column header to header row.
			$( tableTranslationSets ).children( 'thead' ).children( 'tr' ).find( 'th:last' ).after( '<th class="gptoolbox-column-old">' + wp.i18n.__( 'Old', 'gp-toolbox' ) + '</td>' );
		}

		// Check if the 'rejected' status is supported.
		if ( supportedTranslationStatuses.hasOwnProperty( 'rejected' ) ) {
			// Add column header to header row.
			$( tableTranslationSets ).children( 'thead' ).children( 'tr' ).find( 'th:last' ).after( '<th class="gptoolbox-column-rejected">' + wp.i18n.__( 'Rejected', 'gp-toolbox' ) + '</td>' );
		}

		// Check if the 'changesrequested' status is supported.
		if ( supportedTranslationStatuses.hasOwnProperty( 'changesrequested' ) ) {
			// Add column header to header row.
			$( tableTranslationSets ).children( 'thead' ).children( 'tr' ).find( 'th:last' ).after( '<th class="gptoolbox-column-changesrequested">' + wp.i18n.__( 'Changes requested', 'gp-toolbox' ) + '</td>' );
		}

		// Customize translation sets rows.
		$( tableTranslationSets ).children( 'tbody' ).children( 'tr' ).each(
			function() {
				// Check if the 'old' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'old' ) ) {
					// Add cell to row.
					$( this ).find( 'td:last' ).after( '<td class="stats old"></td>' );
				}
				// Check if the 'rejected' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'rejected' ) ) {
					// Add cell to row.
					$( this ).find( 'td:last' ).after( '<td class="stats rejected"></td>' );
				}
				// Check if the 'changesrequested' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'changesrequested' ) ) {
					// Add cell to row.
					$( this ).find( 'td:last' ).after( '<td class="stats changesrequested"></td>' );
				}

				// Add attributes 'gptoolboxdata-' to each row.
				$( this ).children( 'td:first-child' ).find( 'a' ).each( function() {
					// Create a regular expression pattern to find the Locale and Slug of the Translation Set row.
					var regexPattern = new RegExp( '^' + gpUrlProject + '.*' + project.path + '/(.*)/(.*)/$' );

					/**
					 * Check for Locale and Slug in the link.
					 * Example: ../glotpress/projects/plugins/hello-dolly/pt/default/
					 */
					var match = $( this ).attr( 'href' ).match( regexPattern );
					var locale = match[1]; // 'pt'.
					var slug = match[2]; // 'default'.

					$( this ).closest( 'tr' ).attr( dataPrefix + 'locale', locale );
					$( this ).closest( 'tr' ).attr( dataPrefix + 'slug', slug );
					$( this ).closest( 'tr' ).attr( dataPrefix + 'projectpath', project.path );
				} );
			}
		);

		// Add Old, Rejected and Changes requested translations to translation set.
		$( tableTranslationSets ).children( 'tbody' ).children( 'tr' ).each(
			function() {
				var locale = $( this ).attr( 'gptoolboxdata-locale' );

				// Get Translation Set.
				var translationSet = gpToolbox.args.translation_sets[locale];

				// Get translation set link.
				var url = $( this ).children( 'td:first-child' ).find( 'a' ).attr( 'href' );

				// Set old and rejected elements.
				var old = $( this ).children( 'td.stats.old' );
				var rejected = $( this ).children( 'td.stats.rejected' );

				// Check if the 'old' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'old' ) ) {
					// Add value to cell.
					$( this ).find( 'td.stats.old' ).attr( 'data-text', translationSet.old_count ).html( '<div class="progress-notice" style="display: none;"></div><a class="count" href="' + url + '?filters[status]=old">' + new Intl.NumberFormat( userLocale.slug ).format( translationSet.old_count ) + '</a>' );
				}
				// Check if the 'rejected' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'rejected' ) ) {
					// Add value to cell.
					$( this ).find( 'td.stats.rejected' ).attr( 'data-text', translationSet.rejected_count ).html( '<div class="progress-notice" style="display: none;"></div><a class="count" href="' + url + '?filters[status]=rejected">' + new Intl.NumberFormat( userLocale.slug ).format( translationSet.rejected_count ) + '</a>' );
				}
				// Check if the 'changesrequested' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'changesrequested' ) ) {
					// Add value to cell.
					$( this ).find( 'td.stats.changesrequested' ).attr( 'data-text', translationSet.changesrequested_count ).html( '<a class="count" href="' + url + '?filters[status]=changesrequested">' + new Intl.NumberFormat( userLocale.slug ).format( translationSet.changesrequested_count ) + '</a>' );
				}

				// Check if user has GlotPress administrator previleges.
				if ( glotpressAdmin ) {
					// Add buttons to delete Old and Rejected translations.
					$( old ).find( 'a.count' ).after( '<button class="delete hidden" disabled><span class="dashicons dashicons-trash"></span></button>' );
					$( rejected ).find( 'a.count' ).after( '<button class="delete hidden" disabled><span class="dashicons dashicons-trash"></span></button>' );

					// Enable Old and Rejected delete buttons for non-zero counts.
					if ( $( old ).find( 'a.count' ).text().trim() !== '0' ) {
						$( old ).find( 'button.delete' ).attr( 'disabled', false ).removeClass( 'hidden' );
					}
					if ( $( rejected ).find( 'a.count' ).text().trim() !== '0' ) {
						$( rejected ).find( 'button.delete' ).attr( 'disabled', false ).removeClass( 'hidden' );
					}

					// Delete Old and Rejected translations.
					$( old ).find( 'button.delete' ).on( 'click', function() {
						translationsBulkDelete( translationSet.locale, translationSet.slug, 'old' );
					} );
					$( rejected ).find( 'button.delete' ).on( 'click', function() {
						translationsBulkDelete( translationSet.locale, translationSet.slug, 'rejected' );
					} );
				}
			}
		);

		updateHighlight();
	}

	/**
	 * Update count highlight status.
	 *
	 * @param {Object} element : HTML element to update.
	 */
	function updateHighlight( element ) {
		var count = null;

		// Check highlightCounts setting and don't highlight if not set to true.
		if ( highlightCounts === false ) {
			return;
		}

		if ( element ) {
			// Get stats count.
			count = $( element ).find( 'a' ).text();

			if ( count === '0' ) {
				// Remove highlight.
				$( element ).removeClass( 'highlight' );
			} else {
				// Add highlight.
				$( element ).addClass( 'highlight' );
			}
		} else {
			// Update all.
			$( tableTranslationSets ).find( 'td.stats' ).each(
				function() {
					// Get stats count.
					count = $( this ).find( 'a' ).text();

					if ( count === '0' ) {
						// Remove highlight.
						$( this ).removeClass( 'highlight' );
					} else {
						// Add highlight.
						$( this ).addClass( 'highlight' );
					}
				}
			);
		}
	}

	/**
	 * Bulk delete Translations from a Translation Set with a specific status.
	 *
	 * @param {string} locale : Locale of the GP_Translation_Set.
	 * @param {string} slug   : Slug of the GP_Translation_Set.
	 * @param {string} status : Status of the GP_Translation.
	 */
	function translationsBulkDelete( locale, slug, status ) {
		// Find the table cell.
		var td = $( tableTranslationSets ).find( 'tbody tr[' + dataPrefix + 'locale="' + locale + '"][' + dataPrefix + 'slug="' + slug + '"] td.stats.' + status );

		var notice = $( td ).find( 'div.progress-notice' );
		var stats = $( td ).find( 'a.count' );
		var button = $( td ).find( 'button.delete' );

		console.log( 'Clicked to delete translations on project "' + project.path + '" locale "' + locale + '/' + slug + '"' + ' and status "' + status + '"' );

		// Hide stats.
		$( stats ).hide();
		// Hide and disable button.
		$( button ).hide().attr( 'disabled', true );
		// Show progress notice.
		$( notice ).text( wp.i18n.__( 'Deleting...', 'gp-toolbox' ) ).fadeIn();

		$.ajax( {

			url: wpApiSettings.root + 'gp-toolbox/v1/translations/' + project.path + '/' + locale + '/' + slug + '/' + status + '/-delete',
			type: 'POST',
			data: {
				_wpnonce: gpToolbox.nonce,
			},

			success: function( response ) {
				// Set translation set data.
				var count = null;

				if ( status === 'old' ) {
					count = response.stats.old;
				} else if ( status === 'rejected' ) {
					count = response.stats.rejected;
				}

				// Update stats count.
				$( stats ).text( new Intl.NumberFormat( userLocale.slug ).format( count ) );

				// Update stats sort attribute.
				$( td ).attr( 'data-text', count );

				// Remove background highlight.
				updateHighlight( td );

				console.log( 'Successfully deleted translations!' );
			},

			error: function( response ) {
				// Show the Error notice.
				console.log( 'Failed to delete translations.' );
				console.log( 'Error message:', response.responseJSON.message );
			},

			complete: function() {
				// Hide progress notice.
				$( notice ).hide().text( '' );

				// Show stats.
				$( stats ).fadeIn();
			},
		} );
	}
} );
