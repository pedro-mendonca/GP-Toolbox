/* global document */

jQuery( document ).ready( function( $ ) {
	// Set tables rows.
	var rows = $( '.translation-sets tbody' ).find( 'tr' );

	// Configure Tablesorter.
	$( '.translation-sets' ).tablesorter( {
		theme: 'glotpress',
		sortList: [
			[ 1, 0 ],
			[ 3, 0 ],
		],
		headers: {
			0: {
				sorter: 'text',
			},
		},
	} );

	// Table search.
	$( '#translation-sets-filter' ).bind( 'change keyup input', function() {
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

	// Clear table filter.
	$( 'button#translation-sets-filter-clear' ).click( function() {
		// Clear the text input filter.
		$( 'input#translation-sets-filter' ).val( '' );
		// Show all rows.
		$( '.translation-sets tbody' ).find( 'tr' ).show();
	} );
} );
