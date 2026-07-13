const darkQuery = window.matchMedia('(prefers-color-scheme: dark)');
const applyTheme = () => {
    document.documentElement.classList.toggle('dark', darkQuery.matches);
};

applyTheme();
if (typeof darkQuery.addEventListener === 'function') {
    darkQuery.addEventListener('change', applyTheme);
} else if (typeof darkQuery.addListener === 'function') {
    darkQuery.addListener(applyTheme);
}

const searchShell = document.querySelector('[data-search-shell]');
const searchTrigger = searchShell?.querySelector('[data-search-trigger]') || null;
const searchInput = searchShell?.querySelector('[data-search-input]') || null;
const searchDropdown = searchShell?.querySelector('[data-search-dropdown]') || null;
const searchResults = searchShell?.querySelector('[data-search-results]') || null;
const searchClear = searchShell?.querySelector('[data-search-clear]') || null;
const searchCloseButtons = searchShell
    ? Array.from(searchShell.querySelectorAll('[data-search-close]'))
    : [];
const searchViewAll = searchShell?.querySelector('[data-search-view-all]') || null;
const searchViewAllLabel = searchShell?.querySelector('[data-search-view-all-label]') || null;
const searchEndpoint = searchShell?.getAttribute('action') || '/search';
let searchAbortController = null;
let searchDebounceTimer = null;

const escapeHtml = (value = '') => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

const titleCase = (value = '') => {
    const text = value.toString().trim();
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
};

const setSearchViewAll = (query = '') => {
    if (!searchViewAll) return;
    const cleanQuery = query.trim();
    searchViewAll.href = cleanQuery
        ? `${searchEndpoint}?q=${encodeURIComponent(cleanQuery)}`
        : searchEndpoint;
    if (searchViewAllLabel) {
        searchViewAllLabel.textContent = cleanQuery
            ? `Show all results for: ${cleanQuery}`
            : 'Show all results';
    }
};

const showSearchDropdown = ({ focusInput = false } = {}) => {
    if (!searchDropdown) return;
    searchDropdown.classList.remove('hidden');
    searchShell?.classList.add('is-open');
    searchTrigger?.setAttribute('aria-expanded', 'true');
    if (focusInput) {
        window.requestAnimationFrame(() => {
            searchInput?.focus();
            searchInput?.select();
        });
    }
};

const hideSearchDropdown = () => {
    if (!searchDropdown) return;
    searchDropdown.classList.add('hidden');
    searchShell?.classList.remove('is-open');
    searchTrigger?.setAttribute('aria-expanded', 'false');
};

const renderSearchMessage = (message) => {
    if (!searchResults) return;
    searchResults.innerHTML = `<p class="site-search-empty">${escapeHtml(message)}</p>`;
};

const sectionWrapper = (label, itemsHtml) => `
    <section class="site-search-section">
        <div class="site-search-section-head">
            <span class="site-search-section-title">${escapeHtml(label)}</span>
            <span class="site-search-section-line"></span>
        </div>
        <div class="site-search-section-list">${itemsHtml}</div>
    </section>
`;

const resultRow = (innerHtml, url = '#', extraClass = '') => `
    <a href="${escapeHtml(url)}" class="site-search-row ${extraClass}">
        ${innerHtml}
    </a>
`;

const avatarBubble = (name = '', avatar = '') => {
    if (avatar) {
        return `<img src="${escapeHtml(avatar)}" alt="${escapeHtml(name)}" class="site-search-avatar" />`;
    }

    const initial = escapeHtml((name || 'U').trim().charAt(0).toUpperCase() || 'U');
    return `<span class="site-search-avatar site-search-avatar--fallback">${initial}</span>`;
};

const renderSearchPayload = (payload = {}) => {
    if (!searchResults) return;

    const posts = Array.isArray(payload.posts) ? payload.posts : [];
    const categories = Array.isArray(payload.categories) ? payload.categories : [];
    const tags = Array.isArray(payload.tags) ? payload.tags : [];
    const users = Array.isArray(payload.users) ? payload.users : [];
    const pages = Array.isArray(payload.pages) ? payload.pages : [];
    const sections = [];

    if (users.length) {
        const html = users.map((user) => resultRow(`
            <div class="site-search-row-stack">
                ${avatarBubble(user.title ?? '', user.avatar ?? '')}
                <div class="site-search-row-copy">
                    <div class="site-search-row-title">${escapeHtml(titleCase(user.title ?? ''))}</div>
                    <p class="site-search-row-meta">${escapeHtml(user.subtitle ?? user.username ?? '')}</p>
                </div>
            </div>
        `, user.url ?? '#')).join('');

        sections.push(sectionWrapper('Users', html));
    }

    if (posts.length) {
        const html = posts.map((post) => resultRow(`
            <div class="site-search-row-stack">
                <span class="site-search-glyph">
                    <iconify-icon icon="lucide:search"></iconify-icon>
                </span>
                <div class="site-search-row-copy">
                    <div class="site-search-row-title">${escapeHtml(titleCase(post.title ?? ''))}</div>
                    <p class="site-search-row-meta">${escapeHtml(post.snippet ?? post.category ?? '')}</p>
                </div>
            </div>
        `, post.url ?? '#')).join('');

        sections.push(sectionWrapper('Stories', html));
    }

    if (categories.length) {
        const html = categories.map((category) => resultRow(`
            <div class="site-search-row-stack">
                <span class="site-search-glyph">
                    <iconify-icon icon="lucide:folder-open"></iconify-icon>
                </span>
                <div class="site-search-row-copy">
                    <div class="site-search-row-title">${escapeHtml(titleCase(category.title ?? ''))}</div>
                    <p class="site-search-row-meta">Category</p>
                </div>
            </div>
        `, category.url ?? '#')).join('');

        sections.push(sectionWrapper('Categories', html));
    }

    if (tags.length) {
        const html = tags.map((tag) => resultRow(`
            <div class="site-search-row-stack">
                <span class="site-search-glyph">
                    <iconify-icon icon="lucide:hash"></iconify-icon>
                </span>
                <div class="site-search-row-copy">
                    <div class="site-search-row-title">#${escapeHtml(titleCase(tag.title ?? ''))}</div>
                    <p class="site-search-row-meta">Tag</p>
                </div>
            </div>
        `, tag.url ?? '#')).join('');

        sections.push(sectionWrapper('Tags', html));
    }

    if (pages.length) {
        const html = pages.map((page) => resultRow(`
            <div class="site-search-row-stack">
                <span class="site-search-glyph">
                    <iconify-icon icon="lucide:file-text"></iconify-icon>
                </span>
                <div class="site-search-row-copy">
                    <div class="site-search-row-title">${escapeHtml(titleCase(page.title ?? ''))}</div>
                    <p class="site-search-row-meta">${escapeHtml(page.snippet ?? '')}</p>
                </div>
            </div>
        `, page.url ?? '#')).join('');

        sections.push(sectionWrapper('Pages', html));
    }

    const total = posts.length + categories.length + tags.length + users.length + pages.length;
    if (total === 0) {
        renderSearchMessage('Sonuc bulunamadi.');
        return;
    }

    searchResults.innerHTML = sections.join('');
};

const runHeaderSearch = async (query) => {
    const cleanQuery = query.trim();
    setSearchViewAll(cleanQuery);

    if (!cleanQuery) {
        renderSearchMessage('Kullanici, kategori, tag, post veya sayfa aramak icin yaz.');
        return;
    }

    if (searchAbortController) {
        searchAbortController.abort();
    }

    searchAbortController = new AbortController();

    try {
        const response = await fetch(`${searchEndpoint}?q=${encodeURIComponent(cleanQuery)}`, {
            headers: { Accept: 'application/json' },
            signal: searchAbortController.signal,
        });

        if (!response.ok) {
            throw new Error('Search request failed');
        }

        const json = await response.json();
        if (json?.meta?.too_short) {
            renderSearchMessage(`Arama en az ${json.meta.min_length} karakter olmali.`);
            return;
        }

        if (json?.meta && !json.meta.enabled) {
            renderSearchMessage('Arama su anda kapali.');
            return;
        }

        renderSearchPayload(json?.data ?? {});
    } catch (error) {
        if (error?.name === 'AbortError') {
            return;
        }
        renderSearchMessage('Arama sonuclari yuklenemedi.');
    }
};

if (searchShell && searchInput && searchDropdown) {
    const syncClearButton = () => {
        const hasValue = searchInput.value.trim() !== '';
        searchClear?.classList.toggle('hidden', !hasValue);
    };

    searchTrigger?.addEventListener('click', (event) => {
        event.preventDefault();
        const isHidden = searchDropdown.classList.contains('hidden');
        if (isHidden) {
            syncClearButton();
            showSearchDropdown({ focusInput: true });
            runHeaderSearch(searchInput.value);
            return;
        }

        hideSearchDropdown();
    });

    searchInput.addEventListener('focus', () => {
        showSearchDropdown();
        syncClearButton();
        runHeaderSearch(searchInput.value);
    });

    searchInput.addEventListener('input', () => {
        syncClearButton();
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = window.setTimeout(() => {
            showSearchDropdown();
            runHeaderSearch(searchInput.value);
        }, 180);
    });

    searchClear?.addEventListener('click', (event) => {
        event.preventDefault();
        searchInput.value = '';
        syncClearButton();
        renderSearchMessage('Kullanici, kategori, tag, post veya sayfa aramak icin yaz.');
        showSearchDropdown();
        searchInput.focus();
    });

    searchCloseButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            hideSearchDropdown();
            searchTrigger?.focus();
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof Element && searchShell.contains(target)) {
            return;
        }
        hideSearchDropdown();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideSearchDropdown();
        }
    });

    syncClearButton();
    setSearchViewAll(searchInput.value);
}

const notificationsRoot = document.querySelector('[data-notifications-root]');
const notificationsBtn = notificationsRoot?.querySelector('[data-notifications-btn]') || null;
const notificationsDot = notificationsRoot?.querySelector('[data-notifications-dot]') || null;
const notificationsPanel = notificationsRoot?.querySelector('[data-notifications-panel]') || null;
const notificationsList = notificationsRoot?.querySelector('[data-notifications-list]') || null;
const notificationsActionsRoot = notificationsRoot?.querySelector('[data-notifications-actions]') || null;
const notificationsActionsBtn = notificationsRoot?.querySelector('[data-notifications-actions-btn]') || null;
const notificationsActionsMenu = notificationsRoot?.querySelector('[data-notifications-actions-menu]') || null;
const notificationsMarkAllBtn = notificationsRoot?.querySelector('[data-notifications-mark-all]') || null;
const notificationsDeleteAllBtn = notificationsRoot?.querySelector('[data-notifications-delete-all]') || null;
const notificationsEndpoint = notificationsRoot?.getAttribute('data-notifications-endpoint') || '';
const notificationsIndexUrl = notificationsRoot?.getAttribute('data-notifications-index-url') || '/notifications';
const notificationsMarkAllUrl = notificationsRoot?.getAttribute('data-notifications-mark-all-url') || '';
const notificationsDeleteAllUrl = notificationsRoot?.getAttribute('data-notifications-delete-all-url') || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let notificationsLoaded = false;

const setNotificationsDot = (count = 0) => {
    notificationsDot?.classList.toggle('hidden', Number(count) < 1);
};

const closeNotificationsActions = () => {
    if (!notificationsActionsMenu || !notificationsActionsBtn) return;
    notificationsActionsMenu.classList.add('hidden');
    notificationsActionsBtn.setAttribute('aria-expanded', 'false');
};

const openNotificationsActions = () => {
    if (!notificationsActionsMenu || !notificationsActionsBtn) return;
    notificationsActionsMenu.classList.remove('hidden');
    notificationsActionsBtn.setAttribute('aria-expanded', 'true');
};

const closeNotifications = () => {
    if (!notificationsPanel || !notificationsBtn) return;
    notificationsPanel.classList.add('hidden');
    notificationsBtn.setAttribute('aria-expanded', 'false');
    closeNotificationsActions();
};

const openNotifications = () => {
    if (!notificationsPanel || !notificationsBtn) return;
    notificationsPanel.classList.remove('hidden');
    notificationsBtn.setAttribute('aria-expanded', 'true');
};

const renderNotificationAvatar = (item = {}) => {
    if (item.avatar) {
        return `<img src="${escapeHtml(item.avatar)}" alt="${escapeHtml(item.name || 'User')}" class="site-notification-item-avatar" />`;
    }

    const initial = escapeHtml((item.name || item.title || 'U').trim().charAt(0).toUpperCase() || 'U');
    return `<span class="site-notification-item-avatar--fallback">${initial}</span>`;
};

const renderNotificationTitle = (item = {}) => {
    const actor = escapeHtml(item.name || 'Bir kullanici');
    const action = escapeHtml(item.action_text || '');
    const subject = escapeHtml(item.subject || '');
    const title = escapeHtml(item.title || 'Bildirim');

    if (action && subject) {
        return `<strong>${actor}</strong> ${action} <strong>${subject}</strong>`;
    }

    if (action) {
        return `<strong>${actor}</strong> ${action}`;
    }

    return title;
};

const syncNotificationActions = (items = [], unreadCount = 0) => {
    const hasItems = items.length > 0;
    const hasUnread = Number(unreadCount) > 0;
    const hasActions = hasItems || hasUnread;

    notificationsMarkAllBtn?.classList.toggle('hidden', !hasUnread);
    notificationsDeleteAllBtn?.classList.toggle('hidden', !hasItems);
    notificationsActionsBtn?.classList.toggle('hidden', !hasActions);

    if (!hasActions) {
        closeNotificationsActions();
    }
};

const renderNotificationsPayload = (payload = {}) => {
    if (!notificationsList) return;

    const items = Array.isArray(payload.items) ? payload.items : [];
    const unreadCount = Number(payload.unreadCount ?? 0);
    setNotificationsDot(unreadCount);
    syncNotificationActions(items, unreadCount);

    if (!items.length) {
        notificationsList.innerHTML = '<p class="site-notifications-empty">Henuz bildirim yok.</p>';
        return;
    }

    notificationsList.innerHTML = items.map((item) => {
        const preview = escapeHtml(item.preview || item.message || '');
        const url = escapeHtml(item.url || notificationsIndexUrl);
        const time = escapeHtml(item.time || 'Simdi');

        return `
            <a href="${url}" class="site-notification-item">
                ${renderNotificationAvatar(item)}
                <div class="site-notification-item-copy">
                    <div class="site-notification-item-meta">
                        <iconify-icon icon="lucide:clock-3"></iconify-icon>
                        <span>${time}</span>
                    </div>
                    <p class="site-notification-item-title">${renderNotificationTitle(item)}</p>
                    ${preview ? `<p class="site-notification-item-preview">${preview}</p>` : ''}
                </div>
            </a>
        `;
    }).join('');
};

const loadNotifications = async (force = false) => {
    if (!notificationsList || !notificationsEndpoint) return;
    if (notificationsLoaded && !force) return;

    notificationsList.innerHTML = '<p class="site-notifications-empty">Bildirimler yukleniyor...</p>';

    try {
        const response = await fetch(notificationsEndpoint, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Notifications request failed');
        }

        const json = await response.json();
        renderNotificationsPayload(json?.data ?? {});
        notificationsLoaded = true;
    } catch (error) {
        notificationsList.innerHTML = '<p class="site-notifications-empty">Bildirimler yuklenemedi.</p>';
    }
};

const postNotificationAction = async (url) => {
    if (!url) return null;

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error('Notification action failed');
    }

    return response.json().catch(() => ({}));
};

if (notificationsRoot && notificationsBtn && notificationsPanel) {
    notificationsBtn.addEventListener('click', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        const isHidden = notificationsPanel.classList.contains('hidden');
        if (!isHidden) {
            closeNotifications();
            return;
        }

        openNotifications();
        await loadNotifications(true);
    });

    notificationsActionsBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const isHidden = notificationsActionsMenu?.classList.contains('hidden') ?? true;
        if (isHidden) {
            openNotificationsActions();
            return;
        }

        closeNotificationsActions();
    });

    notificationsMarkAllBtn?.addEventListener('click', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        try {
            await postNotificationAction(notificationsMarkAllUrl);
            notificationsLoaded = false;
            await loadNotifications(true);
        } catch (error) {
            notificationsList.innerHTML = '<p class="site-notifications-empty">Bildirimler guncellenemedi.</p>';
        } finally {
            closeNotificationsActions();
        }
    });

    notificationsDeleteAllBtn?.addEventListener('click', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        try {
            await postNotificationAction(notificationsDeleteAllUrl);
            notificationsLoaded = false;
            await loadNotifications(true);
        } catch (error) {
            notificationsList.innerHTML = '<p class="site-notifications-empty">Bildirimler silinemedi.</p>';
        } finally {
            closeNotificationsActions();
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            closeNotifications();
            return;
        }

        if (notificationsActionsRoot && !notificationsActionsRoot.contains(target)) {
            closeNotificationsActions();
        }

        if (notificationsRoot.contains(target)) {
            return;
        }

        closeNotifications();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNotifications();
        }
    });
}

const userMenuRoot = document.querySelector('[data-user-menu]');
const userMenuBtn = userMenuRoot?.querySelector('[data-user-menu-btn]');
const userMenuPanel = userMenuRoot?.querySelector('[data-user-menu-panel]');

const closeUserMenu = () => {
    if (!userMenuPanel || !userMenuBtn) return;
    userMenuPanel.classList.add('hidden');
    userMenuBtn.setAttribute('aria-expanded', 'false');
};

const toggleUserMenu = () => {
    if (!userMenuPanel || !userMenuBtn) return;
    userMenuPanel.classList.toggle('hidden');
    const isOpen = !userMenuPanel.classList.contains('hidden');
    userMenuBtn.setAttribute('aria-expanded', String(isOpen));
};

if (userMenuBtn && userMenuPanel) {
    userMenuBtn.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        toggleUserMenu();
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof Element) {
            if (userMenuRoot?.contains(target)) return;
        }
        closeUserMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeUserMenu();
    });
}

const shouldHandleSlowImage = (img) => {
    if (!(img instanceof HTMLImageElement)) return false;
    if (img.getAttribute('data-no-slow-image') === '1') return false;
    if (img.hasAttribute('data-slow-image')) {
        return img.getAttribute('data-slow-image') !== '0';
    }
    if (img.loading === 'lazy') return true;
    if (img.closest('.prose')) return true;
    return false;
};

const initSlowImage = (img) => {
    if (!shouldHandleSlowImage(img)) return;
    if (img.getAttribute('data-slow-init') === '1') return;

    img.setAttribute('data-slow-init', '1');
    img.setAttribute('data-slow-image', '1');

    const markLoaded = () => {
        img.setAttribute('data-slow-loaded', '1');
    };

    const markError = () => {
        img.setAttribute('data-slow-error', '1');
        img.setAttribute('data-slow-loaded', '1');
    };

    if (img.complete && img.naturalWidth > 0) {
        markLoaded();
    } else {
        img.addEventListener('load', markLoaded, { once: true });
        img.addEventListener('error', markError, { once: true });
    }
};

const initSlowImages = (root = document) => {
    if (!(root instanceof Element || root instanceof Document)) return;
    const images = root.querySelectorAll('img');
    images.forEach(initSlowImage);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initSlowImages());
} else {
    initSlowImages();
}

if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) return;
                if (node.tagName === 'IMG') {
                    initSlowImage(node);
                    return;
                }
                const imgs = node.querySelectorAll?.('img');
                if (imgs?.length) imgs.forEach(initSlowImage);
            });
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
}
