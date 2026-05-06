/**
 * Global Toast Notification System
 *
 * Provides a simple API to trigger non-blocking toast alerts globally.
 * Usage: window.cwcToast.show('Message', 'type', duration_ms)
 * Types: 'default', 'success', 'warning', 'error'
 */
window.cwcToast = (() => {
	let container;

	const init = () => {
		if (!document.getElementById('cwc-toast-container')) {
			container = document.createElement('div');
			container.id = 'cwc-toast-container';
			container.className = 'cwc-toast-container';
			document.body.appendChild(container);
		} else {
			container = document.getElementById('cwc-toast-container');
		}
	};

	const show = (message, type = 'default', duration = 3000) => {
		if (!container) init();

		const toast = document.createElement('div');
		toast.className = `cwc-toast cwc-toast--${type}`;
		
		let iconSvg = '';
		if (type === 'warning' || type === 'error') {
			iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
		} else if (type === 'success') {
			iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
		}

		toast.innerHTML = `${iconSvg ? `<span style="display:flex;align-items:center;">${iconSvg}</span>` : ''}<span>${message}</span>`;
		
		container.appendChild(toast);

		// Animate in
		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				toast.classList.add('is-visible');
			});
		});

		// Animate out and remove
		setTimeout(() => {
			toast.classList.remove('is-visible');
			toast.addEventListener('transitionend', () => {
				if (toast.parentNode) {
					toast.parentNode.removeChild(toast);
				}
			});
		}, duration);
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	return { show };
})();
