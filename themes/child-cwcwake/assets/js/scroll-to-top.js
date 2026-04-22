/**
 * CWC Wake — Scroll to Top
 *
 * Handles the visibility and click interaction for the floating
 * scroll-to-top button.
 */
(function() {
    const init = () => {
        const btn = document.getElementById('cwc-scroll-top');
        if (!btn) return;

        /**
         * Toggle button visibility based on scroll depth.
         */
        const toggleVisibility = () => {
            if (window.pageYOffset > 300) {
                btn.classList.add('is-visible');
            } else {
                btn.classList.remove('is-visible');
            }
        };

        /**
         * Scroll the window to the top smoothly.
         */
        const scrollToTop = () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        window.addEventListener('scroll', toggleVisibility);
        btn.addEventListener('click', scrollToTop);

        // Initial check
        toggleVisibility();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
