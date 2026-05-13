/**
 * CWC floating chat — same-origin REST proxy (Groq via PHP).
 * Modern ES2020+ syntax (const/let, optional chaining, globalThis).
 */
(() => {
	'use strict';

	const cfg = globalThis.cwcChat;
	if (!cfg?.restUrl || !cfg?.nonce) {
		return;
	}

	const i18n = cfg.i18n ?? {};
	const root = document.getElementById('cwc-chat-root');
	if (!root) {
		return;
	}

	root.removeAttribute('hidden');

	/** @type {{ role: string, content: string }[]} */
	const messages = [];

	const allowedOrigin = window.location.origin;

	const esc = (s) => {
		const d = document.createElement('div');
		d.textContent = s;
		return d.innerHTML;
	};

	const bubble = (text, role) => {
		const el = document.createElement('div');
		const kind = role === 'user' ? 'user' : 'assistant';
		el.className = `cwc-chat__bubble cwc-chat__bubble--${kind}`;
		el.innerHTML = esc(text);
		return el;
	};

	/**
	 * Turn assistant text into safe HTML: **bold**, ## headings, [label](url), bare same-origin URLs, line breaks.
	 * Raw HTML from the model is escaped first.
	 */
	const assistantMarkdownToHtml = (raw) => {
		let s = esc(String(raw ?? ''));

		s = s.replace(/^###\s+(.+)$/gm, '<span class="cwc-chat__md-h cwc-chat__md-h--sub">$1</span>');
		s = s.replace(/^##\s+(.+)$/gm, '<span class="cwc-chat__md-h">$1</span>');

		s = s.replace(/^- (.+)$/gm, '<span class="cwc-chat__md-li">$1</span>');

		s = s.replace(/\*\*((?:[^*]|\*(?!\*))+?)\*\*/g, '<strong>$1</strong>');
		s = s.replace(/__([^_]+)__/g, '<strong>$1</strong>');

		s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g, (full, label, href) => {
			const cleanHref = href.replace(/&amp;/g, '&');
			let u;
			try {
				u = new URL(cleanHref, allowedOrigin);
			} catch {
				return full;
			}
			if (u.origin !== allowedOrigin) {
				return full;
			}
			const safeHref = u.href.replace(/"/g, '%22');
			return `<a class="cwc-chat__inline-link" href="${safeHref}" rel="noopener noreferrer">${label}</a>`;
		});

		const chunks = s.split(/(<a\b[^>]*>[\s\S]*?<\/a>)/gi);
		s = chunks
			.map((chunk, i) => {
				if (i % 2 === 1) {
					return chunk;
				}
				return chunk.replace(/https?:\/\/[^\s<&]+/gi, (tok) => {
					const m = tok.match(/([.,;:!?)]+)$/);
					const end = m ? m[1] : '';
					const core = end ? tok.slice(0, -end.length) : tok;
					const coreDecoded = core.replace(/&amp;/g, '&');
					try {
						const u = new URL(coreDecoded, allowedOrigin);
						if (u.origin !== allowedOrigin) {
							return tok;
						}
						const safeHref = u.href.replace(/"/g, '%22');
						const display =
							coreDecoded.length > 54 ? `${coreDecoded.slice(0, 51)}…` : coreDecoded;
						return `<a class="cwc-chat__inline-link" href="${safeHref}" rel="noopener noreferrer">${esc(display)}</a>${end}`;
					} catch {
						return tok;
					}
				});
			})
			.join('');

		s = s.replace(/\n/g, '<br>');
		return s;
	};

	const assistantBubble = (text) => {
		const wrap = document.createElement('div');
		wrap.className = 'cwc-chat__turn cwc-chat__turn--assistant';
		if (!cfg.logoUrl) {
			wrap.classList.add('cwc-chat__turn--no-avatar');
		} else {
			const av = document.createElement('img');
			av.className = 'cwc-chat__msg-avatar';
			av.src = cfg.logoUrl;
			av.alt = '';
			av.width = 32;
			av.height = 32;
			av.decoding = 'async';
			wrap.append(av);
		}

		const el = document.createElement('div');
		el.className = 'cwc-chat__bubble cwc-chat__bubble--assistant';
		const inner = document.createElement('div');
		inner.className = 'cwc-chat__md-body';
		inner.innerHTML = assistantMarkdownToHtml(text);
		el.append(inner);
		wrap.append(el);
		return wrap;
	};

	const meta = (text) => {
		const el = document.createElement('div');
		el.className = 'cwc-chat__bubble cwc-chat__bubble--meta';
		el.textContent = text;
		return el;
	};

	const suggestionRow = (items) => {
		if (!Array.isArray(items) || !items.length) {
			return null;
		}
		const row = document.createElement('div');
		row.className = 'cwc-chat__suggestions';
		row.setAttribute('role', 'group');
		row.setAttribute('aria-label', i18n.suggestionsLabel ?? 'Suggested pages');

		for (const item of items) {
			if (!item?.url || !item?.label) {
				continue;
			}
			let url;
			try {
				url = new URL(item.url, allowedOrigin);
			} catch {
				continue;
			}
			if (url.origin !== allowedOrigin) {
				continue;
			}
			const a = document.createElement('a');
			a.className = 'cwc-chat__suggestion cwc-chat__hyperlink';
			a.href = url.href;
			a.textContent = item.label;
			a.title = `${item.label} — ${url.href}`;
			a.rel = 'noopener noreferrer';
			row.append(a);
		}

		return row.childElementCount ? row : null;
	};

	const wrap = document.createElement('div');
	wrap.className = 'cwc-chat';
	wrap.setAttribute('lang', document.documentElement.lang || 'en');

	const launcher = document.createElement('button');
	launcher.type = 'button';
	launcher.className = 'cwc-chat__launcher';
	launcher.setAttribute('aria-label', i18n.open ?? 'Open assistant');
	launcher.setAttribute('aria-expanded', 'false');
	launcher.setAttribute('aria-controls', 'cwc-chat-panel');
	launcher.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm0 14H5.17L4 17.17V4h16v12Z"/><path d="M7 9h10v2H7zm0-3h10v2H7zm3 6H7v2h3z"/></svg>`;

	const panel = document.createElement('div');
	panel.id = 'cwc-chat-panel';
	panel.className = 'cwc-chat__panel';
	panel.setAttribute('role', 'dialog');
	panel.setAttribute('aria-modal', 'true');
	panel.setAttribute('aria-labelledby', 'cwc-chat-title');
	panel.hidden = true;

	const head = document.createElement('div');
	head.className = 'cwc-chat__head';

	const brand = document.createElement('div');
	brand.className = 'cwc-chat__head-brand';

	if (cfg.logoUrl) {
		const avatar = document.createElement('img');
		avatar.className = 'cwc-chat__avatar';
		avatar.src = cfg.logoUrl;
		avatar.alt = '';
		avatar.width = 36;
		avatar.height = 36;
		avatar.decoding = 'async';
		brand.append(avatar);
	}

	const title = document.createElement('p');
	title.id = 'cwc-chat-title';
	title.className = 'cwc-chat__title';
	title.textContent = i18n.title ?? 'Assistant';

	brand.append(title);

	const closeBtn = document.createElement('button');
	closeBtn.type = 'button';
	closeBtn.className = 'cwc-chat__close';
	closeBtn.setAttribute('aria-label', i18n.close ?? 'Close');
	closeBtn.innerHTML =
		'<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';

	head.append(brand, closeBtn);

	const msgBox = document.createElement('div');
	msgBox.className = 'cwc-chat__messages';

	const startersWrap = document.createElement('div');
	startersWrap.className = 'cwc-chat__starters';
	const startersLabel = document.createElement('p');
	startersLabel.className = 'cwc-chat__starters-label';
	startersLabel.textContent = i18n.startersLabel ?? 'Quick questions';
	const startersRow = document.createElement('div');
	startersRow.className = 'cwc-chat__starters-row';
	startersWrap.append(startersLabel, startersRow);

	const prompts = Array.isArray(cfg.starterPrompts) ? cfg.starterPrompts : [];
	for (const p of prompts) {
		if (!p?.text) {
			continue;
		}
		const btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'cwc-chat__starter';
		btn.textContent = p.label ?? p.text;
		btn.title = p.text;
		btn.addEventListener('click', () => {
			void send(String(p.text));
		});
		startersRow.append(btn);
	}
	if (!startersRow.childElementCount) {
		startersWrap.hidden = true;
	}

	const composer = document.createElement('div');
	composer.className = 'cwc-chat__composer';

	const input = document.createElement('textarea');
	input.className = 'cwc-chat__input';
	input.rows = 2;
	input.setAttribute('aria-label', i18n.placeholder ?? 'Message');
	input.placeholder = i18n.placeholder ?? '';

	const sendBtn = document.createElement('button');
	sendBtn.type = 'button';
	sendBtn.className = 'cwc-chat__send';
	sendBtn.textContent = i18n.send ?? 'Send';

	composer.append(input, sendBtn);

	const foot = document.createElement('div');
	foot.className = 'cwc-chat__footer';
	if (cfg.contactUrl) {
		const a = document.createElement('a');
		a.className = 'cwc-chat__hyperlink cwc-chat__footer-link';
		a.href = cfg.contactUrl;
		a.textContent = i18n.contactCta ?? 'Contact us';
		foot.append(a);
	}

	panel.append(head, msgBox, startersWrap, composer, foot);
	wrap.append(panel, launcher);
	root.append(wrap);

	const syncStarters = () => {
		const hasUser = messages.some((m) => m.role === 'user');
		startersWrap.hidden = hasUser || !startersRow.childElementCount;
		startersWrap.setAttribute('aria-hidden', startersWrap.hidden ? 'true' : 'false');
	};

	const setOpen = (open) => {
		panel.hidden = !open;
		panel.classList.toggle('is-open', open);
		launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (open) {
			queueMicrotask(() => input.focus());
		}
	};

	const scrollBottom = () => {
		msgBox.scrollTop = msgBox.scrollHeight;
	};

	const setBusy = (b) => {
		sendBtn.disabled = b;
		input.disabled = b;
	};

	/**
	 * @param {string} [preset] Optional full question (from quick chips).
	 */
	const send = async (preset) => {
		const raw = preset != null && preset !== '' ? String(preset) : input.value;
		const text = raw.trim();
		if (!text) {
			window.alert(i18n.empty ?? 'Please enter a message.');
			return;
		}

		input.value = '';
		messages.push({ role: 'user', content: text });
		msgBox.append(bubble(text, 'user'));
		syncStarters();
		const thinkingEl = meta(i18n.thinking ?? '…');
		msgBox.append(thinkingEl);
		scrollBottom();
		setBusy(true);

		try {
			const res = await fetch(cfg.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce,
				},
				body: JSON.stringify({ messages }),
			});

			thinkingEl.remove();

			let data = null;
			try {
				data = await res.json();
			} catch {
				data = null;
			}

			if (!res.ok) {
				const errMsg = data?.message ?? i18n.error ?? 'Request failed.';
				msgBox.append(meta(errMsg));
				messages.pop();
				syncStarters();
				scrollBottom();
				setBusy(false);
				return;
			}

			if (data?.reply) {
				messages.push({ role: 'assistant', content: data.reply });
				const group = document.createElement('div');
				group.className = 'cwc-chat__assistant-group';
				if (!cfg.logoUrl) {
					group.classList.add('cwc-chat__assistant-group--no-avatar');
				}
				group.append(assistantBubble(data.reply));
				const sug = suggestionRow(data.suggestions);
				if (sug) {
					group.append(sug);
				}
				msgBox.append(group);
			} else {
				msgBox.append(meta(i18n.error ?? 'Error'));
				messages.pop();
				syncStarters();
			}
		} catch {
			thinkingEl.remove();
			msgBox.append(meta(i18n.error ?? 'Error'));
			messages.pop();
			syncStarters();
		}

		scrollBottom();
		setBusy(false);
	};

	syncStarters();

	launcher.addEventListener('click', () => {
		setOpen(!panel.classList.contains('is-open'));
	});

	closeBtn.addEventListener('click', () => setOpen(false));

	sendBtn.addEventListener('click', () => {
		void send();
	});

	input.addEventListener('keydown', (e) => {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			void send();
		}
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && panel.classList.contains('is-open')) {
			setOpen(false);
		}
	});
})();
