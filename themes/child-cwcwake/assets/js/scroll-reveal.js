/**
 * CWC Wake — Scroll Reveal Observer
 *
 * Uses IntersectionObserver to add an `is-revealed` class to elements
 * carrying a `data-reveal` attribute when they scroll into view.
 *
 * Supports:
 *   data-reveal="fade-up|fade-down|fade-left|fade-right|zoom-in|zoom-out|flip-up|stagger"
 *   data-reveal-delay="<ms>"      — per-element entrance delay
 *   data-reveal-duration="<ms>"   — per-element transition duration
 *   data-reveal-threshold="<0-1>" — IntersectionObserver threshold (default 0.15)
 *   data-reveal-once="false"      — set to "false" to re-animate on every scroll-in
 */
( () => {
	const init = () => {
		const els = document.querySelectorAll( '[data-reveal]' );
		if ( ! els.length ) return;

		/**
		 * Apply CSS custom-property overrides from data attributes.
		 */
		const applyOverrides = ( el ) => {
			const delay = el.dataset.revealDelay;
			const duration = el.dataset.revealDuration;

			if ( delay ) {
				el.style.setProperty( '--cwc-reveal-delay', `${ delay }ms` );
			}
			if ( duration ) {
				el.style.setProperty( '--cwc-reveal-duration', `${ duration }ms` );
			}
		};

		/**
		 * IntersectionObserver callback — reveals or hides elements.
		 */
		const onIntersect = ( entries, observer ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) return;

				entry.target.classList.add( 'is-revealed' );

				const once = entry.target.dataset.revealOnce;
				if ( once !== 'false' ) {
					observer.unobserve( entry.target );
				}
			} );
		};

		/**
		 * Group elements by threshold so we can create fewer observers.
		 */
		const groups = new Map();

		els.forEach( ( el ) => {
			applyOverrides( el );

			/**
			 * Detect "overlay on the image" or static elements that should NOT animate.
			 * Absolute positioned elements or specifically marked overlays are skipped
			 * to prevent glitchy motion paths or hiding essential UI layers.
			 */
			const isSectionStatic = el.classList.contains( 'cwc-cards-section--static' );
			const revealVariant = el.dataset.reveal;

			// Target direct children, or grandchildren if using a single inner wrapper (standard for split).
			const itemTargets = ( 'split' === revealVariant && 1 === el.children.length )
				? el.children[ 0 ].children
				: el.children;

			Array.from( itemTargets ).forEach( ( child ) => {
				const style = window.getComputedStyle( child );
				// If the element is an overlay (absolute) or part of a static block, mark it.
				if ( isSectionStatic || 'absolute' === style.position || child.classList.contains( 'cwc-cards-section__card-content' ) ) {
					child.classList.add( 'no-reveal' );
				}
			} );

			const threshold = parseFloat( el.dataset.revealThreshold ) || 0.15;
			const key = threshold.toFixed( 2 );

			if ( ! groups.has( key ) ) {
				groups.set( key, { threshold, elements: [] } );
			}
			groups.get( key ).elements.push( el );
		} );

		groups.forEach( ( { threshold, elements } ) => {
			const observer = new IntersectionObserver( onIntersect, {
				rootMargin: '0px 0px -40px 0px',
				threshold,
			} );

			elements.forEach( ( el ) => observer.observe( el ) );
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
