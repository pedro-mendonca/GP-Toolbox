/* global document */

jQuery( document ).ready( function( $ ) {
	// Set table.
	var gpToolboxTable = $( 'table.gp-table.gp-toolbox.tools-glossaries' );

	// Set tables rows.
	var glossariesRows = $( gpToolboxTable ).find( 'tbody tr' );

	// Configure Tablesorter.
	$( gpToolboxTable ).tablesorter( {
		theme: 'glotpress',
		sortList: [
			[ 2, 0 ], // Sort by Locale.
			[ 1, 0 ], // Sort by Type.
			[ 3, 0 ], // Sort by Project.
		],
		headers: {
			0: {
				sorter: 'text',
			},
		},
	} );

	// Table search.
	$( '#glossaries-filter' ).bind( 'change keyup input', function() {
		var words = this.value.toLowerCase().split( ' ' );

		if ( '' === this.value.trim() ) {
			glossariesRows.show();
		} else {
			glossariesRows.hide();
			glossariesRows.filter( function() {
				var t = $( this );
				var d;
				for ( d = 0; d < words.length; ++d ) {
					if ( t.text().toLowerCase().indexOf( words[d] ) !== -1 ) {
						return true;
					}
				}
				return false;
			} ).show();
		}
	} );

	// Filter table.
	$( '#glossaries-type-filters a' ).click( function() {
		// Get the original status.
		var glossaryType = $( this ).prop( 'id' );

		// Get the item class.
		var itemClass = $( this ).prop( 'class' );

		// Clear the text input filter.
		$( 'input#glossaries-filter' ).val( '' );

		console.log( glossaryType );

		if ( itemClass === 'glossaries-type' ) {
			if ( glossaryType === 'glossaries-type-all' ) {
				// Show all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).show();
				// Shrink Locale header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-locale' ).attr( 'colspan', 1 );
				// Show Project header column.
				$( gpToolboxTable ).find( 'thead th' ).show();
			} else if ( glossaryType === 'glossaries-type-global' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Enlarge Locale header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-locale' ).attr( 'colspan', 2 );
				// Hide Project header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-translation-set' ).hide();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.type.global' ).parent().show();
			} else if ( glossaryType === 'glossaries-type-project' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Shrink Locale header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-locale' ).attr( 'colspan', 1 );
				// Show Project header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-translation-set' ).show();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.type.project' ).parent().show();
			} else if ( glossaryType === 'glossaries-set-unknown' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Shrink Locale header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-locale' ).attr( 'colspan', 1 );
				// Show Project header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-translation-set' ).show();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.translation-set.unknown' ).parent().show();
			} else if ( glossaryType === 'glossaries-unknown-orphaned-entries' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Shrink Locale header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-locale' ).attr( 'colspan', 1 );
				// Show Project header column.
				$( gpToolboxTable ).find( 'thead th.gp-column-translation-set' ).show();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.id.unknown' ).parent().show();
			}
		}
	} );

	// Clear table filter.
	$( 'button#glossaries-filter-clear' ).click( function() {
		// Clear the text input filter.
		$( 'input#glossaries-filter' ).val( '' );
		// Show all rows.
		$( gpToolboxTable ).find( 'tbody tr' ).show();
	} );
} );
