/* global document, gpToolboxProject, setTimeout, wp */

jQuery( document ).ready( function( $ ) {
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

	// Check if the Translation Sets table exist.
	if ( tableTranslationSets.length ) {
		// Add Old and Rejected translations columns.
		$( tableTranslationSets ).children( 'thead' ).children( 'tr' ).find( 'th.gp-column-waiting' ).after( '<th class="gptoolbox-column-old">' + wp.i18n.__( 'Old', 'gp-toolbox' ) + '</td>' );
		$( tableTranslationSets ).children( 'thead' ).children( 'tr' ).find( 'th.gptoolbox-column-old' ).after( '<th class="gptoolbox-column-rejected">' + wp.i18n.__( 'Rejected', 'gp-toolbox' ) + '</td>' );

		// Customize translation sets rows.
		$( tableTranslationSets ).children( 'tbody' ).children( 'tr' ).each(
			function() {
				// Add Old and Rejected translations to row.
				$( this ).find( 'td.stats.waiting' ).after( '<td class="stats old"></td>' );
				$( this ).find( 'td.stats.old' ).after( '<td class="stats rejected"></td>' );

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

		// Add Old and Rejected translations to translation set.
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

				// Add Old and Rejected translations count.
				$( this ).find( 'td.stats.old' ).html( '<div><a class="count" href="' + url + '?filters[status]=old">' + translationSet.old_count + '</a></div>' );
				$( this ).find( 'td.stats.rejected' ).html( '<div><a class="count" href="' + url + '?filters[status]=rejected">' + translationSet.rejected_count + '</a></div>' );

				// Check if user has GLotPress administrator previleges.
				if ( glotpressAdmin ) {
					// Add buttons to delete Old and Rejected translations.
					$( old ).find( 'div a.count' ).after( '<button class="delete" disabled><span class="dashicons dashicons-trash"></span></button>' );
					$( rejected ).find( 'div a.count' ).after( '<button class="delete" disabled><span class="dashicons dashicons-trash"></span></button></div>' );

					// Enable delete buttons for non-zero counts.
					if ( $( old ).find( 'div a.count' ).text().trim() !== '0' ) {
						$( old ).find( 'div button.delete' ).attr( 'disabled', false );
					}
					if ( $( rejected ).find( 'div a.count' ).text().trim() !== '0' ) {
						$( rejected ).find( 'div button.delete' ).attr( 'disabled', false );
					}

					// Delete Old and Rejected translations.
					$( old ).find( 'div button.delete' ).on( 'click', function() {
						deleteTranslations( translationSet.locale, translationSet.slug, 'old' );
					} );
					$( rejected ).find( 'div button.delete' ).on( 'click', function() {
						deleteTranslations( translationSet.locale, translationSet.slug, 'rejected' );
					} );
				}
			}
		);
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
			beforeSend: function() {
				console.log( 'Ajax request is starting...' );

				// Disable button.
				button.attr( 'disabled', true );
			},

		} ).done( function( response, textStatus, jqXHR ) {
			// Set translation set data.
			var old = response.data.old;
			var rejected = response.data.rejected;

			// Update stats count.
			if ( $( button ).closest( 'td' ).hasClass( 'old' ) ) {
				button.closest( 'div' ).children( 'a' ).text( old );
			}
			if ( $( button ).closest( 'td' ).hasClass( 'rejected' ) ) {
				button.closest( 'div' ).children( 'a' ).text( rejected );
			}

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
} );
