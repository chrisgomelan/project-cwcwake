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

        let ticking = false;

        /**
         * Toggle button visibility based on scroll depth.
         */
        const toggleVisibility = () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    if (window.scrollY > 300) {
                        btn.classList.add('is-visible');
                    } else {
                        btn.classList.remove('is-visible');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        };

        /**
         * Create a water ripple effect on click.
         */
        const createRipple = (e) => {
            const spawnRipple = (delay = 0) => {
                setTimeout(() => {
                    const ripple = document.createElement('span');
                    ripple.classList.add('cwc-ripple');
                    
                    const rect = btn.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    
                    ripple.style.width = ripple.style.height = `${size}px`;
                    ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
                    ripple.style.top = `${e.clientY - rect.top - size / 2}px`;

                    btn.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 800);
                }, delay);
            };

            // Double ripple for "water effect"
            spawnRipple(0);
            spawnRipple(150);
        };

        /**
         * Scroll the window to the top smoothly.
         */
        const scrollToTop = (e) => {
            createRipple(e);
            
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
