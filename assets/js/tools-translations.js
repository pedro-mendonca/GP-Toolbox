/* global document */

jQuery( document ).ready( function( $ ) {
	// Set table.
	var gpToolboxTable = $( 'table.gp-table.gp-toolbox.tools-translations' );

	// Set tables rows.
	var rows = $( gpToolboxTable ).find( 'tbody tr' );

	// Configure Tablesorter.
	$( gpToolboxTable ).tablesorter( {
		theme: 'glotpress',
		sortList: [
			[ 0, 0 ], // Sort by Translation Set, ascending.
			[ 2, 1 ], // Sort by Active Originals count, descending.
			[ 3, 1 ], // Sort by Obsolete Originals count, descending.
			[ 4, 1 ], // Sort by Unknown Originals count, descending.
		],
		headers: {
			0: {
				sorter: 'text',
			},
		},
	} );

	// Table search.
	$( '#translations-filter' ).bind( 'change keyup input', function() {
		var words = this.value.toLowerCase().split( ' ' );

		if ( '' === this.value.trim() ) {
			rows.show();
		} else {
			rows.hide();
			rows.filter( function() {
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
	$( '#translations-filters a' ).click( function() {
		// Get the original status.
		var originalsStatus = $( this ).prop( 'id' );

		// Get the item class.
		var itemClass = $( this ).prop( 'class' );

		// Clear the text input filter.
		$( 'input#translations-filter' ).val( '' );

		if ( itemClass === 'translations' ) {
			if ( originalsStatus === 'translations-all' ) {
				// Show all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).show();
			} else if ( originalsStatus === 'translations-active-original' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.stats.originals-active:not([data-text="0"])' ).parent().show();
			} else if ( originalsStatus === 'translations-obsolete-original' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.stats.originals-obsolete:not([data-text="0"])' ).parent().show();
			} else if ( originalsStatus === 'translations-unknown-original' ) {
				// Hide all rows.
				$( gpToolboxTable ).find( 'tbody tr' ).hide();
				// Show the specified status rows.
				$( gpToolboxTable ).find( 'tbody tr td.stats.originals-unknown:not([data-text="0"])' ).parent().show();
			}
		}
	} );

	// Clear table filter.
	$( 'button#translations-filter-clear' ).click( function() {
		// Clear the text input filter.
		$( 'input#translations-filter' ).val( '' );
		// Show all rows.
		$( gpToolboxTable ).find( 'tbody tr' ).show();
	} );
} );
