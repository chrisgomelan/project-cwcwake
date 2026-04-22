/**
 * CWC Wake — Rates Manager frontend logic
 */

( () => {
	'use strict';

	const initRatesManager = () => {
		const managers = document.querySelectorAll( '.cwc-rates-manager' );

		managers.forEach( ( manager ) => {
			const tabs   = manager.querySelectorAll( '.cwc-rates-manager__tab' );
			const panels = manager.querySelectorAll( '.cwc-rates-manager__panel' );

			if ( ! tabs.length || ! panels.length ) return;

			tabs.forEach( ( tab ) => {
				tab.addEventListener( 'click', () => {
					const targetId = tab.getAttribute( 'data-target' );
					const targetPanel = manager.querySelector( `#cat-${targetId}` );

					if ( ! targetPanel ) return;

					// Update Tabs
					tabs.forEach( t => t.classList.remove( 'is-active' ) );
					tab.classList.add( 'is-active' );

					// Update Panels
					panels.forEach( p => p.classList.remove( 'is-active' ) );
					targetPanel.classList.add( 'is-active' );
				} );
			} );
		} );
	};

	document.addEventListener( 'DOMContentLoaded', initRatesManager );
} )();
