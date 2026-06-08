/**
 * Fragella "pick the match" — meta-box search on the fragrance edit screen.
 *
 * Queries the admin AJAX endpoint for candidate bottles and lets the editor
 * choose the exact record, writing its id into a hidden field that save-time
 * enrichment honours instead of the fuzzy top-1. Vanilla JS, no jQuery.
 */
( function () {
	'use strict';

	var cfg = window.TheAlphaFragella;
	if ( ! cfg ) {
		return;
	}

	var btn     = document.getElementById( 'the_alpha_fragella_search_btn' );
	var input   = document.getElementById( 'the_alpha_fragella_query' );
	var results = document.getElementById( 'the_alpha_fragella_results' );
	var pickId  = document.getElementById( 'the_alpha_fragella_pick_id' );
	var spinner = document.getElementById( 'the_alpha_fragella_spinner' );
	var title   = document.getElementById( 'title' ); // classic editor title field

	if ( ! btn || ! input || ! results || ! pickId ) {
		return;
	}

	function setBusy( busy ) {
		btn.disabled = busy;
		if ( spinner ) {
			spinner.classList.toggle( 'is-active', busy );
		}
	}

	function note( msg ) {
		results.innerHTML = '';
		var p = document.createElement( 'p' );
		p.className = 'description';
		p.textContent = msg;
		results.appendChild( p );
	}

	function render( candidates ) {
		results.innerHTML = '';
		if ( ! candidates.length ) {
			note( cfg.i18n.none );
			return;
		}
		var ul = document.createElement( 'ul' );
		ul.className = 'the-alpha-frag-candidates';

		candidates.forEach( function ( c ) {
			var li = document.createElement( 'li' );

			if ( c.image ) {
				var img = document.createElement( 'img' );
				img.src = c.image;
				img.alt = '';
				img.loading = 'lazy';
				li.appendChild( img );
			}

			var meta = document.createElement( 'span' );
			meta.className = 'cand-meta';
			var strong = document.createElement( 'strong' );
			strong.textContent = c.name || c.id;
			meta.appendChild( strong );
			var sub = document.createElement( 'small' );
			sub.textContent = [ c.brand, c.year ].filter( Boolean ).join( ' · ' );
			meta.appendChild( sub );
			li.appendChild( meta );

			var use = document.createElement( 'button' );
			use.type = 'button';
			use.className = 'button button-small';
			use.textContent = cfg.i18n.pick;
			use.addEventListener( 'click', function () {
				pickId.value = c.id;
				Array.prototype.forEach.call( ul.querySelectorAll( 'li' ), function ( n ) {
					n.classList.remove( 'is-picked' );
				} );
				li.classList.add( 'is-picked' );
				// Align the human-facing name field with the chosen record.
				if ( c.name ) {
					input.value = c.name;
				}
				note( cfg.i18n.picked );
			} );
			li.appendChild( use );

			ul.appendChild( li );
		} );

		results.appendChild( ul );
	}

	function search() {
		var q = ( input.value || ( title && title.value ) || '' ).trim();
		if ( ! q ) {
			note( cfg.i18n.empty );
			return;
		}
		setBusy( true );
		note( cfg.i18n.searching );

		var body = new URLSearchParams();
		body.set( 'action', 'the_alpha_fragella_search' );
		body.set( 'nonce', cfg.nonce );
		body.set( 'q', q );

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) {
				if ( res && res.success ) {
					render( res.data.candidates || [] );
				} else {
					note( ( res && res.data && res.data.message ) || cfg.i18n.error );
				}
			} )
			.catch( function () { note( cfg.i18n.error ); } )
			.finally( function () { setBusy( false ); } );
	}

	btn.addEventListener( 'click', search );
	input.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Enter' ) {
			e.preventDefault();
			search();
		}
	} );
}() );
