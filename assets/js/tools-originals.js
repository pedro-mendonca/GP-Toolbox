/* global document */

jQuery( document ).ready( function( $ ) {
	// Set tables rows.
	var rows = $( '.originals tbody' ).find( 'tr' );

	// Configure Tablesorter.
	$( '.originals' ).tablesorter( {
		theme: 'glotpress',
		sortList: [
			[ 0, 0 ],
		],
		headers: {
			0: {
				sorter: 'text',
			},
		},
	} );

	// Table search.
	$( '#originals-filter' ).bind( 'change keyup input', function() {
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
	$( '#originals-status-filters a' ).click( function() {
		// Get the original status.
		var originalsStatus = $( this ).prop( 'id' );

		// Get the item class.
		var itemClass = $( this ).prop( 'class' );

		// Clear the text input filter.
		$( 'input#originals-filter' ).val( '' );

		if ( itemClass === 'originals-status' ) {
			if ( originalsStatus === 'originals-status-all' ) {
				// Show all rows.
				$( '.originals tbody' ).find( 'tr' ).show();
			} else if ( originalsStatus === 'originals-status-active' ) {
				// Hide all rows.
				$( '.originals tbody' ).find( 'tr' ).hide();
				// Show the specified status rows.
				$( '.originals tbody' ).find( 'tr td.stats.active:not([data-text="0"])' ).parent().show();
			} else if ( originalsStatus === 'originals-status-obsolete' ) {
				// Hide all rows.
				$( '.originals tbody' ).find( 'tr' ).hide();
				// Show the specified status rows.
				$( '.originals tbody' ).find( 'tr td.stats.obsolete:not([data-text="0"])' ).parent().show();
			}
		}
	} );

	// Clear table filter.
	$( 'button#originals-filter-clear' ).click( function() {
		// Clear the text input filter.
		$( 'input#originals-filter' ).val( '' );
		// Show all rows.
		$( '.originals tbody' ).find( 'tr' ).show();
	} );
} );
