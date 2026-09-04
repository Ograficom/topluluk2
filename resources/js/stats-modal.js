import '../css/stats-modal.css';

(() => {
    const MAX_VISIBLE = 5;
    const MAX_OVERFLOW = 99;
    const LABELS = [
        'Akışlardan Gösterim',
        'Görüntülenen',
        'Tepkiler',
        'Yorumlar',
        'Kaydedildi',
    ];

    const modalIsFeed = (modal) => modal?.matches?.('[data-post-card-stats-modal]') ?? false;
    const modalIsShow = (modal) => modal?.matches?.('[data-show-stats-modal]') ?? false;

    const panelFor = (modal) => {
        if (modalIsFeed(modal)) return modal.querySelector('.post-card__stats-panel');
        if (modalIsShow(modal)) return modal.querySelector('.ps-show-stats-panel');
        return null;
    };

    const gridFor = (modal) => {
        if (modalIsFeed(modal)) return modal.querySelector('.post-card__stats-grid');
        if (modalIsShow(modal)) return modal.querySelector('.ps-show-stats-grid');
        return null;
    };

    const itemsFor = (modal) => {
        const grid = gridFor(modal);
        if (!grid) return [];

        const selector = modalIsFeed(modal)
            ? ':scope > .post-card__stats-item'
            : ':scope > .ps-show-stats-item';

        return Array.from(grid.querySelectorAll(selector));
    };

    const reactionTileFor = (modal) => {
        const items = itemsFor(modal);
        return items.find((item) => /tepki/i.test(item.textContent || '')) || items[2] || null;
    };

    const countFor = (tile) => tile?.querySelector(':scope > strong') || tile?.querySelector('strong') || null;

    const numericText = (node) => {
        const text = String(node?.textContent || '').replace(/[^0-9]/g, '');
        return Number(text || 0);
    };

    const patchHeaderTitle = (modal) => {
        const title = modalIsFeed(modal)
            ? modal.querySelector('.post-card__stats-head > strong')
            : modal.querySelector('.ps-show-stats-title');

        if (!title) return;

        if (modalIsFeed(modal)) {
            title.childNodes.forEach((node) => {
                if (node.nodeType === Node.TEXT_NODE && /etkileşim/i.test(node.nodeValue || '')) {
                    node.nodeValue = (node.nodeValue || '').replace(/etkileşim/gi, 'Etkileşim');
                }
            });
            return;
        }

        title.textContent = String(title.textContent || '').replace(/etkileşim/gi, 'Etkileşim');
    };

    const labelNodeFor = (item) => {
        if (!item) return null;
        return item.querySelector('.og-reaction-summary-label')
            || Array.from(item.children).find((child) => child.tagName === 'SPAN')
            || item.querySelector('span');
    };

    const patchLabels = (modal) => {
        patchHeaderTitle(modal);
        itemsFor(modal).forEach((item, index) => {
            const label = labelNodeFor(item);
            if (label && LABELS[index]) label.textContent = LABELS[index];
        });
    };

    const guestSvg = () => {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '1.8');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'M20 21a8 8 0 0 0-16 0');
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', '12');
        circle.setAttribute('cy', '7');
        circle.setAttribute('r', '4');
        svg.append(path, circle);
        return svg;
    };

    const endpointFromAction = (root) => {
        const form = root?.querySelector?.('form[action*="/reactions"]');
        const action = form?.getAttribute('action');
        if (!action) return null;

        try {
            const url = new URL(action, window.location.origin);
            url.searchParams.set('details', '1');
            return `${url.pathname}${url.search}`;
        } catch (_) {
            return null;
        }
    };

    const endpointFor = (modal) => {
        if (!modal) return null;

        const explicit = modal.getAttribute('data-reaction-details-endpoint');
        if (explicit) return explicit;

        if (modalIsFeed(modal)) {
            const card = modal.closest('[data-post-card-shell]');
            const actionEndpoint = endpointFromAction(card);
            if (actionEndpoint) return actionEndpoint;

            const id = String(card?.id || '');
            if (id.startsWith('post-card-shell-')) {
                const slug = id.slice('post-card-shell-'.length);
                if (slug) return `/posts/${encodeURIComponent(slug)}/reactions?details=1`;
            }
        }

        if (modalIsShow(modal)) {
            const actionEndpoint = endpointFromAction(modal.closest('.post-show-shell'));
            if (actionEndpoint) return actionEndpoint;

            const path = window.location.pathname.replace(/\/+$/, '');
            const parts = path.split('/').filter(Boolean);
            const slug = parts.length ? decodeURIComponent(parts[parts.length - 1]) : '';
            if (slug) return `/posts/${encodeURIComponent(slug)}/reactions?details=1`;
        }

        return null;
    };

    const isOpen = (modal) => {
        if (modalIsFeed(modal)) return modal.hasAttribute('open') || modal.open === true;
        if (modalIsShow(modal)) return !modal.hasAttribute('hidden') && modal.getAttribute('aria-hidden') !== 'true';
        return false;
    };

    const createAvatar = (person) => {
        if (person?.avatar) {
            const image = document.createElement('img');
            image.className = 'og-reaction-avatar';
            image.src = person.avatar;
            image.alt = person?.name ? `${person.name} profil resmi` : 'Profil resmi';
            image.loading = 'lazy';
            image.decoding = 'async';
            image.title = person?.name || 'Kullanıcı';
            return image;
        }

        const fallback = document.createElement('span');
        fallback.className = 'og-reaction-avatar-fallback';
        fallback.textContent = person?.initials || '?';
        fallback.title = person?.name || 'Kullanıcı';
        return fallback;
    };

    const createGuestAvatar = () => {
        const guest = document.createElement('span');
        guest.className = 'og-reaction-avatar-guest';
        guest.title = 'Misafir tepki';
        guest.appendChild(guestSvg());
        return guest;
    };

    const normalizeSummary = (modal) => {
        patchLabels(modal);

        const tile = reactionTileFor(modal);
        if (!tile) return null;

        tile.classList.add('og-reaction-stats-tile');
        tile.setAttribute('role', 'button');
        tile.setAttribute('tabindex', '0');
        tile.setAttribute('aria-label', 'Tepkileri göster');

        const count = countFor(tile);
        let label = labelNodeFor(tile);
        if (!label) return null;

        label.textContent = 'Tepkiler';
        label.classList.add('og-reaction-summary-label');

        let row = tile.querySelector('.og-reaction-summary-row');
        if (!row) {
            row = document.createElement('div');
            row.className = 'og-reaction-summary-row';
            label.parentNode?.insertBefore(row, label);
            row.appendChild(label);
        } else if (label.parentElement !== row) {
            row.prepend(label);
        }

        let stack = row.querySelector('.og-reaction-avatar-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'og-reaction-avatar-stack';
            row.appendChild(stack);
        }

        return { tile, count, label, row, stack };
    };

    const renderSummary = (modal, payload = null) => {
        const refs = normalizeSummary(modal);
        if (!refs) return;

        const fallbackTotal = numericText(refs.count);
        const total = Math.max(0, Number(payload?.total ?? fallbackTotal) || 0);
        const preview = Array.isArray(payload?.preview) ? payload.preview.slice(0, MAX_VISIBLE) : [];
        const visibleTarget = Math.min(total, MAX_VISIBLE);
        const missing = Math.max(visibleTarget - preview.length, 0);

        if (refs.count) {
            refs.count.textContent = new Intl.NumberFormat('tr-TR').format(total);
        }

        refs.stack.replaceChildren();
        preview.forEach((person) => refs.stack.appendChild(createAvatar(person)));
        for (let index = 0; index < missing; index += 1) {
            refs.stack.appendChild(createGuestAvatar());
        }

        const overflow = Math.min(Math.max(total - MAX_VISIBLE, 0), MAX_OVERFLOW);
        if (overflow > 0) {
            const more = document.createElement('span');
            more.className = 'og-reaction-avatar-more';
            more.textContent = `+${overflow}`;
            more.title = `${overflow} tepki daha`;
            refs.stack.appendChild(more);
        }
    };

    const fetchPayload = async (modal) => {
        if (!modal) return null;
        if (modal.__ogReactionPromise) return modal.__ogReactionPromise;

        const endpoint = endpointFor(modal);
        if (!endpoint) return null;

        modal.__ogReactionPromise = fetch(endpoint, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
        })
            .then((response) => {
                if (!response.ok) throw new Error(`Reaction details failed: ${response.status}`);
                return response.json();
            })
            .catch(() => null)
            .finally(() => {
                delete modal.__ogReactionPromise;
            });

        return modal.__ogReactionPromise;
    };

    const createDetailAvatar = (person) => {
        if (person?.avatar) {
            const image = document.createElement('img');
            image.className = 'og-reaction-detail-avatar';
            image.src = person.avatar;
            image.alt = person?.name ? `${person.name} profil resmi` : 'Profil resmi';
            image.loading = 'lazy';
            return image;
        }

        const fallback = document.createElement('span');
        fallback.className = 'og-reaction-detail-avatar-fallback';
        fallback.textContent = person?.initials || '?';
        return fallback;
    };

    const createReactionValue = (reaction = {}) => {
        const value = document.createElement('span');
        value.className = 'og-reaction-detail-value';

        if (reaction.image_url) {
            const image = document.createElement('img');
            image.src = reaction.image_url;
            image.alt = reaction.label || 'Tepki';
            value.appendChild(image);
            return value;
        }

        if (reaction.emoji) {
            value.textContent = reaction.emoji;
            return value;
        }

        value.textContent = reaction.label || 'Tepki';
        value.style.fontSize = '13px';
        value.style.fontWeight = '600';
        return value;
    };

    const createRegisteredRow = (person) => {
        const row = person?.profile_url ? document.createElement('a') : document.createElement('div');
        row.className = 'og-reaction-detail-row';
        if (person?.profile_url) row.href = person.profile_url;

        const personWrap = document.createElement('span');
        personWrap.className = 'og-reaction-detail-person';
        personWrap.appendChild(createDetailAvatar(person));

        const name = document.createElement('span');
        name.className = 'og-reaction-detail-name';
        name.textContent = person?.name || 'Ografi kullanıcısı';
        personWrap.appendChild(name);

        row.append(personWrap, createReactionValue(person?.reaction || {}));
        return row;
    };

    const createGuestRow = (count) => {
        const row = document.createElement('div');
        row.className = 'og-reaction-detail-row';

        const personWrap = document.createElement('span');
        personWrap.className = 'og-reaction-detail-person';

        const avatar = document.createElement('span');
        avatar.className = 'og-reaction-detail-avatar-guest';
        avatar.appendChild(guestSvg());

        const name = document.createElement('span');
        name.className = 'og-reaction-detail-name';
        name.textContent = count > 1 ? `Misafir kullanıcılar (${count})` : 'Misafir kullanıcı';

        personWrap.append(avatar, name);

        const value = document.createElement('span');
        value.className = 'og-reaction-detail-value';
        value.textContent = 'Tepki';
        value.style.fontSize = '13px';
        value.style.fontWeight = '600';

        row.append(personWrap, value);
        return row;
    };

    const ensureDetails = (modal) => {
        const panel = panelFor(modal);
        if (!panel) return null;

        let details = panel.querySelector('.og-reaction-details');
        if (!details) {
            details = document.createElement('div');
            details.className = 'og-reaction-details';

            const head = document.createElement('div');
            head.className = 'og-reaction-details-head';

            const title = document.createElement('strong');
            title.className = 'og-reaction-details-title';
            title.textContent = 'Tepkiler';

            const back = document.createElement('button');
            back.type = 'button';
            back.className = 'og-reaction-details-back';
            back.textContent = 'Geri';
            back.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                modal.classList.remove('og-stats-reaction-details-open');
            });

            const list = document.createElement('div');
            list.className = 'og-reaction-details-list';

            head.append(title, back);
            details.append(head, list);
            panel.appendChild(details);
        }

        return { details, list: details.querySelector('.og-reaction-details-list') };
    };

    const renderDetails = (modal, payload) => {
        const refs = ensureDetails(modal);
        if (!refs?.list) return;

        const items = Array.isArray(payload?.items) ? payload.items : [];
        const total = Math.max(0, Number(payload?.total) || 0);
        const anonymousCount = Math.max(Number(payload?.anonymous_count ?? Math.max(total - items.length, 0)) || 0, 0);

        refs.list.replaceChildren();
        items.forEach((person) => refs.list.appendChild(createRegisteredRow(person)));
        if (anonymousCount > 0) refs.list.appendChild(createGuestRow(anonymousCount));

        if (items.length === 0 && anonymousCount === 0) {
            const empty = document.createElement('div');
            empty.className = 'og-reaction-details-empty';
            empty.textContent = 'Henüz tepki yok.';
            refs.list.appendChild(empty);
        }
    };

    const hydrate = async (modal) => {
        if (!modal) return null;

        /* Avatar alanını API cevabını beklemeden görünür yap. */
        renderSummary(modal, null);

        const payload = await fetchPayload(modal);
        if (payload) renderSummary(modal, payload);
        return payload;
    };

    const openDetails = async (modal) => {
        if (!modal) return;

        const payload = await hydrate(modal);
        if (!payload) {
            const fallbackTotal = numericText(countFor(reactionTileFor(modal)));
            renderDetails(modal, {
                total: fallbackTotal,
                anonymous_count: fallbackTotal,
                items: [],
            });
        } else {
            renderDetails(modal, payload);
        }

        modal.classList.add('og-stats-reaction-details-open');
    };

    const resetDetails = (modal) => {
        modal?.classList?.remove('og-stats-reaction-details-open');
    };

    const initModal = (modal) => {
        if (!(modal instanceof Element)) return;

        patchLabels(modal);
        renderSummary(modal, null);

        if (isOpen(modal)) {
            window.setTimeout(() => hydrate(modal), 0);
        }
    };

    const initAll = (root = document) => {
        if (root instanceof Element && root.matches('[data-post-card-stats-modal], [data-show-stats-modal]')) {
            initModal(root);
        }
        root.querySelectorAll?.('[data-post-card-stats-modal], [data-show-stats-modal]').forEach(initModal);
    };

    initAll();

    document.addEventListener('ografi:feed-appended', () => initAll());

    const observer = new MutationObserver((records) => {
        records.forEach((record) => {
            if (record.type === 'childList') {
                record.addedNodes.forEach((node) => {
                    if (node instanceof Element) initAll(node);
                });
                return;
            }

            const modal = record.target;
            if (!(modal instanceof Element)) return;
            if (!modal.matches('[data-post-card-stats-modal], [data-show-stats-modal]')) return;

            if (isOpen(modal)) {
                patchLabels(modal);
                window.setTimeout(() => hydrate(modal), 0);
            } else {
                resetDetails(modal);
            }
        });
    });

    observer.observe(document.documentElement, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['open', 'hidden', 'aria-hidden'],
    });

    document.addEventListener('click', (event) => {
        const close = event.target.closest?.('.post-card__stats-close, .ps-show-stats-close');
        if (close) {
            resetDetails(close.closest('[data-post-card-stats-modal], [data-show-stats-modal]'));
            return;
        }

        const tile = event.target.closest?.('.og-reaction-stats-tile');
        if (tile) {
            event.preventDefault();
            event.stopPropagation();
            openDetails(tile.closest('[data-post-card-stats-modal], [data-show-stats-modal]'));
            return;
        }

        const feedTrigger = event.target.closest?.('[data-post-card-stats-trigger]');
        if (feedTrigger) {
            const card = feedTrigger.closest('[data-post-card-shell]');
            const modal = card?.querySelector('[data-post-card-stats-modal]');
            if (modal) window.setTimeout(() => hydrate(modal), 0);
            return;
        }

        const showTrigger = event.target.closest?.('[data-show-stats-trigger]');
        if (showTrigger) {
            window.setTimeout(() => {
                const modal = document.querySelector('[data-show-stats-modal]:not([hidden])')
                    || document.querySelector('[data-show-stats-modal]');
                if (modal) hydrate(modal);
            }, 0);
        }
    }, true);

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const tile = event.target.closest?.('.og-reaction-stats-tile');
        if (!tile) return;

        event.preventDefault();
        openDetails(tile.closest('[data-post-card-stats-modal], [data-show-stats-modal]'));
    });
})();
