/**
 * view.js for cwc/room-gallery
 * Handles the "See All Images" grid modal.
 */
document.addEventListener('DOMContentLoaded', () => {
    const openBtns = document.querySelectorAll('.js-cwc-open-gallery-modal');
    const modal = document.getElementById('cwc-gallery-grid-modal');
    
    if (!modal) return;

    const closeBtn = modal.querySelector('.cwc-gallery-modal__close');
    const overlay = modal.querySelector('.cwc-gallery-modal__overlay');

    const openModal = (e) => {
        if (e) e.preventDefault();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    openBtns.forEach(btn => {
        btn.addEventListener('click', openModal);
    });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', closeModal);

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
