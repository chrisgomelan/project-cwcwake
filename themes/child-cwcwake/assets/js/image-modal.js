/**
 * CWC Custom Image Modal
 * 
 * A slim, dependency-free modal slider built from scratch.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Create Modal HTML Structure
    const modal = document.createElement('div');
    modal.id = 'cwc-image-modal';
    modal.className = 'cwc-modal';
    modal.innerHTML = `
        <div class="cwc-modal__overlay"></div>
        <button class="cwc-modal__close" aria-label="Close">&times;</button>
        <button class="cwc-modal__prev" aria-label="Previous image">&#10094;</button>
        <button class="cwc-modal__next" aria-label="Next image">&#10095;</button>
        <div class="cwc-modal__content">
            <img class="cwc-modal__image" src="" alt="">
            <div class="cwc-modal__caption"></div>
        </div>
        <div class="cwc-modal__counter"></div>
    `;
    document.body.appendChild(modal);

    const modalImg = modal.querySelector('.cwc-modal__image');
    const modalCaption = modal.querySelector('.cwc-modal__caption');
    const modalCounter = modal.querySelector('.cwc-modal__counter');
    let currentSet = [];
    let currentIndex = 0;

    const openModal = (index, set) => {
        currentSet = set;
        currentIndex = index;
        updateModal();
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden'; // Prevent scroll
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        // Only reset overflow if no other modal (like the gallery grid) is still open
        if (!document.querySelector('.cwc-gallery-modal.is-open')) {
            document.body.style.overflow = '';
        }
    };

    const updateModal = () => {
        const item = currentSet[currentIndex];
        modalImg.src = item.src;
        modalCaption.textContent = item.alt || '';
        modalCounter.textContent = `${currentIndex + 1} / ${currentSet.length}`;

        // Preload next image
        if (currentIndex < currentSet.length - 1) {
            const nextImg = new Image();
            nextImg.src = currentSet[currentIndex + 1].src;
        }
    };

    const nextImage = () => {
        currentIndex = (currentIndex + 1) % currentSet.length;
        updateModal();
    };

    const prevImage = () => {
        currentIndex = (currentIndex - 1 + currentSet.length) % currentSet.length;
        updateModal();
    };

    // 2. Click Handler
    document.body.addEventListener('click', (e) => {
        // Target images inside typical WP gallery/image structures
        const clickedImg = e.target.closest('.wp-block-gallery img, .wp-block-image img, .cwc-albums__img, .cwc-room-gallery__image, .cwc-gallery-modal__img');

        if (!clickedImg) return;

        // EXCLUSION: Don't trigger modal for images inside album cards (navigation links)
        // This effectively disables the modal on the single-cwc_album-category.html template
        // as well as the "More Albums" section of individual album pages.
        if (clickedImg.closest('.cwc-albums__card')) {
            return;
        }

        e.preventDefault();

        const container = clickedImg.closest('.wp-block-gallery, .cwc-albums__grid, .cwc-room-gallery__grid, .cwc-gallery-modal__grid, .wp-block-post-content') || document.body;
        const allImgs = Array.from(container.querySelectorAll('img')).filter(img => img.offsetWidth > 100 && !img.closest('.cwc-header, .cwc-footer'));

        const set = allImgs.map(img => ({ src: img.src, alt: img.alt }));
        const index = allImgs.indexOf(clickedImg);

        openModal(index, set);
    });

    // 3. UI Events
    modal.querySelector('.cwc-modal__close').addEventListener('click', closeModal);
    modal.querySelector('.cwc-modal__overlay').addEventListener('click', closeModal);
    modal.querySelector('.cwc-modal__next').addEventListener('click', (e) => { e.stopPropagation(); nextImage(); });
    modal.querySelector('.cwc-modal__prev').addEventListener('click', (e) => { e.stopPropagation(); prevImage(); });

    // 4. Keyboard Navigation
    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
    });

    // 5. Swipe Support (Basic)
    let touchStartX = 0;
    modal.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    modal.addEventListener('touchend', (e) => {
        const touchEndX = e.changedTouches[0].screenX;
        if (touchStartX - touchEndX > 50) nextImage();
        if (touchEndX - touchStartX > 50) prevImage();
    }, { passive: true });
});
