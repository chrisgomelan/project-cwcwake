/**
 * CWC Wake — All Blogs block view script.
 *
 * Submits the category filter form whenever its `<select>` changes
 * so editors don't need to click a separate "Apply" button. Falls
 * back gracefully: when JS is unavailable, the `<noscript>` submit
 * button rendered by `render.php` is what shows up.
 *
 * @since 1.0.0
 */

( () => {
	const init = () => {
		const selects = document.querySelectorAll(
			'.cwc-all-blogs__filter-select'
		);
		if ( selects.length === 0 ) {
			return;
		}

		selects.forEach( ( select ) => {
			select.addEventListener( 'change', () => {
				select.form?.submit();
			} );
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
