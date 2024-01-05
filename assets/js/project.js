/* global document, Intl, gpToolboxProject, wp */

jQuery( document ).ready( function( $ ) {
	// Get User Locale.
	var userLocale = gpToolboxProject.user_locale;

	// Get the supported translation statuses.
	var supportedTranslationStatuses = gpToolboxProject.supported_statuses;

	// Get the Translation Sets table.
	var tableTranslationSets = $( 'table.gp-table.translation-sets' );

	// Check if user is has GlotPress Admin previleges.
	var glotpressAdmin = gpToolboxProject.admin;

	// Get the Base URL for GlotPress Projects.
	var gpUrlProject = gpToolboxProject.gp_url_project;

	// Get the Project.
	var project = gpToolboxProject.args.project;

	// Set the data attrib prefix.
	var dataPrefix = 'gptoolboxdata-';

	// Get the highlight_counts setting.
	var highlightCounts = false;
	if ( gpToolboxProject.highlight_counts === '1' ) {
		highlightCounts = true;
	}

	console.log( supportedTranslationStatuses );

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
				var translationSet = gpToolboxProject.args.translation_sets[locale];

				// Get translation set link.
				var url = $( this ).children( 'td:first-child' ).find( 'a' ).attr( 'href' );

				// Set old and rejected elements.
				var old = $( this ).children( 'td.stats.old' );
				var rejected = $( this ).children( 'td.stats.rejected' );

				// Check if the 'old' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'old' ) ) {
					// Add value to cell.
					$( this ).find( 'td.stats.old' ).attr( 'data-text', translationSet.old_count ).html( '<div><a class="count" href="' + url + '?filters[status]=old">' + new Intl.NumberFormat( userLocale.slug ).format( translationSet.old_count ) + '</a></div>' );
				}
				// Check if the 'rejected' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'rejected' ) ) {
					// Add value to cell.
					$( this ).find( 'td.stats.rejected' ).attr( 'data-text', translationSet.rejected_count ).html( '<div><a class="count" href="' + url + '?filters[status]=rejected">' + new Intl.NumberFormat( userLocale.slug ).format( translationSet.rejected_count ) + '</a></div>' );
				}
				// Check if the 'changesrequested' status is supported.
				if ( supportedTranslationStatuses.hasOwnProperty( 'changesrequested' ) ) {
					// Add value to cell.
					$( this ).find( 'td.stats.changesrequested' ).attr( 'data-text', translationSet.changesrequested_count ).html( '<div><a class="count" href="' + url + '?filters[status]=changesrequested">' + new Intl.NumberFormat( userLocale.slug ).format( translationSet.changesrequested_count ) + '</a></div>' );
				}

				// Check if user has GLotPress administrator previleges.
				if ( glotpressAdmin ) {
					// Add buttons to delete Old and Rejected translations.
					$( old ).find( 'div a.count' ).after( '<button class="delete hidden" disabled><span class="dashicons dashicons-trash"></span></button>' );
					$( rejected ).find( 'div a.count' ).after( '<button class="delete hidden" disabled><span class="dashicons dashicons-trash"></span></button></div>' );

					// Enable Old and Rejected delete buttons for non-zero counts.
					if ( $( old ).find( 'div a.count' ).text().trim() !== '0' ) {
						$( old ).find( 'div button.delete' ).attr( 'disabled', false ).removeClass( 'hidden' );
					}
					if ( $( rejected ).find( 'div a.count' ).text().trim() !== '0' ) {
						$( rejected ).find( 'div button.delete' ).attr( 'disabled', false ).removeClass( 'hidden' );
					}

					// Delete Old and Rejected translations.
					$( old ).find( 'div button.delete' ).on( 'click', function() {
						deleteTranslations( translationSet.locale, translationSet.slug, 'old' );
						//getProgress( $( this ).closest( 'td' ), 1 );
					} );
					$( rejected ).find( 'div button.delete' ).on( 'click', function() {
						deleteTranslations( translationSet.locale, translationSet.slug, 'rejected' );
						//getProgress( $( this ).closest( 'td' ), 1 );
					} );
				}
			}
		);

		updateHighlight();

		// tableTranslationSets.addClass( 'ready' );
	}

	/**
	 * Update count highlight status.
	 *
	 * @param {Object} element : HTML element to update.
	 */
	function updateHighlight( element ) {
		var count = 0;

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
	 * Delete Translations from a Translation Set with a specific status.
	 *
	 * @param {string} locale : Locale of the GP_Translation_Set.
	 * @param {string} slug   : Slug of the GP_Translation_Set.
	 * @param {string} status : Status of the GP_Translation.
	 */
	function deleteTranslations( locale, slug, status ) {
		var button = $( tableTranslationSets ).find( 'tbody tr[' + dataPrefix + 'locale="' + locale + '"][' + dataPrefix + 'slug="' + slug + '"] td.stats.' + status + ' div button.delete' );
		console.log( 'Clicked to delete translations on project "' + project.path + '" locale "' + locale + '/' + slug + '"' + ' and status "' + status + '"' );

		$.ajax( {

			url: gpToolboxProject.ajaxurl,
			type: 'POST',
			data: {
				action: 'delete_translations',
				projectPath: project.path,
				locale: locale,
				slug: slug,
				status: status,
				nonce: gpToolboxProject.nonce,
			},
			//cache: false,
			beforeSend: function() {
				console.log( 'Ajax request is starting...' );

				// Disable button.
				button.attr( 'disabled', true );

				// Start the AJAX process with the initial step (1)
				//getProgress( 1 );
				getProgress( button.closest( 'td' ), 1 );
			},

		} ).done( function( response, textStatus, jqXHR ) {
			// Set translation set data.
			var old = response.data.old;
			var rejected = response.data.rejected;

			// Update Old and Rejected stats count after delete.
			if ( $( button ).closest( 'td' ).hasClass( 'old' ) ) {
				$( old ).closest( 'td' ).removeClass( 'highlight' );
				button.closest( 'div' ).children( 'a' ).text( new Intl.NumberFormat( userLocale.slug ).format( old ) );
				button.addClass( 'hidden' );
			}
			if ( $( button ).closest( 'td' ).hasClass( 'rejected' ) ) {
				$( rejected ).closest( 'td' ).removeClass( 'highlight' );
				button.closest( 'div' ).children( 'a' ).text( new Intl.NumberFormat( userLocale.slug ).format( rejected ) );
				button.addClass( 'hidden' );
			}

			updateHighlight( button.closest( 'td' ) );

			console.log( 'Ajax request has been completed (' + textStatus + '). Status: ' + jqXHR.status + ' ' + jqXHR.statusText );
			console.log( response );
			console.log( textStatus );
			console.log( jqXHR );
		} ).fail( function( jqXHR, textStatus ) {
			// Show the Error notice.
			console.log( 'Ajax request has failed (' + textStatus + '). Status: ' + jqXHR.status + ' ' + jqXHR.statusText );
		} ).always( function() {
			console.log( 'Ajax end.' );
		} );
	}



	function getProgress( element, step ) {
		console.log( 'Getting progress, step ' + step );
		// console.log( 'Element: ' + element );
		$.ajax( {

			url: gpToolboxProject.ajaxurl,
			type: 'POST',
			data: {
				action: 'get_progress',
				//step: step, // Pass the current step.
				nonce: gpToolboxProject.nonce,
			},

		} ).done( function( response, textStatus, jqXHR ) {
			if ( response.success ) {
				var progress = response.data.progress;
				console.log( 'Progress: ' + progress );
				// console.log( 'Response: ' + response.success );
				//$('#progress-bar').css('width', progress + '%');
				$( element ).css( 'background', 'linear-gradient(90deg, var(--gp-color-secondary-100) ' + progress + '%, var(--gp-color-status-rejected-subtle) ' + progress + '%)' );
				// background: linear-gradient(90deg, rgba(255,255,0,1) 60%, rgba(187,187,187,1) 60%);

				if ( progress < 100 ) {
					// If the progress is not 100%, continue the AJAX call with the next step
					// setTimeout( getProgress( element, step + 1 ), 0 );
					//getProgress( progress + 1 )
					//console.log( 'Get Progress again.' );
					//getProgress( element, step + 1 );
					setTimeout( function () {
						getProgress( element, step + 1 );
					}, 1000);
				} else {
					//$('#progress-container').hide(); // Hide progress bar when AJAX is complete
				}

			} else {
				// Handle error
			}
		} ).fail( function( jqXHR, textStatus ) {
			// Show the Error notice.
			console.log( 'Ajax request has failed (' + textStatus + '). Status: ' + jqXHR.status + ' ' + jqXHR.statusText );
		} ).always( function() {
			console.log( 'Ajax end.' );
		} );
	}
} );
