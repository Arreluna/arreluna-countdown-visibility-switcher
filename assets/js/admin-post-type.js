(function () {
	'use strict';

	function toggle() {
		var mode   = document.getElementById( 'acvs_mode' ).value;
		var action = document.getElementById( 'acvs_action' ).value;
		document.querySelectorAll( '.acvs-field-evergreen' ).forEach( function ( el ) { el.style.display = mode === 'evergreen' ? 'table-row' : 'none'; } );
		document.querySelectorAll( '.acvs-field-fixed' ).forEach( function ( el ) { el.style.display = mode === 'fixed' ? 'table-row' : 'none'; } );
		document.querySelectorAll( '.acvs-field-redirect' ).forEach( function ( el ) { el.style.display = action === 'redirect' ? 'table-row' : 'none'; } );
		document.querySelectorAll( '.acvs-field-visibility' ).forEach( function ( el ) { el.style.display = action === 'visibility' ? 'table-row' : 'none'; } );
		document.querySelectorAll( '.acvs-mode-help-evergreen' ).forEach( function ( el ) { el.style.display = mode === 'evergreen' ? 'block' : 'none'; } );
		document.querySelectorAll( '.acvs-mode-help-fixed' ).forEach( function ( el ) { el.style.display = mode === 'fixed' ? 'block' : 'none'; } );
		document.querySelectorAll( '.acvs-usage-visibility' ).forEach( function ( el ) { el.style.display = action === 'visibility' ? 'block' : 'none'; } );
		document.querySelectorAll( '.acvs-usage-redirect' ).forEach( function ( el ) { el.style.display = action === 'redirect' ? 'block' : 'none'; } );
		document.querySelectorAll( '.acvs-usage-evergreen-reset' ).forEach( function ( el ) { el.style.display = mode === 'evergreen' ? 'block' : 'none'; } );
	}

	document.getElementById( 'acvs_mode' ).addEventListener( 'change', toggle );
	document.getElementById( 'acvs_action' ).addEventListener( 'change', toggle );
	toggle();
}());
