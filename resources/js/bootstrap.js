import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;

const getCookie = (name) => {
    const prefix = `${name}=`;
    const value = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith(prefix))
        ?.slice(prefix.length);

    return value ? decodeURIComponent(value) : '';
};

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content
    || getCookie('XSRF-TOKEN');

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');

const notificationButton = document.getElementById('notificationMenuButton');
const notificationMenu = document.getElementById('notificationMenu');
const notificationHeader = notificationMenu?.querySelector('.notification-menu__head');

// The mobile drawer uses the same notification DOM and dropdown state as desktop.
if (notificationButton && notificationMenu) {
    const root = notificationMenu.closest('.notification-dropdown');
    const dropdownToggle = document.getElementById('notificationDropdownToggle');
    const profileToggle = document.getElementById('desktopUserMenuButton');
    const backdrop = document.querySelector('.notification-sheet-backdrop');
    const closeButton = notificationMenu.querySelector('[data-notification-drawer-close]');
    const mobileSheet = window.matchMedia('(max-width: 767px)');
    let wasOpen = false;

    const menuIsOpen = () => root?.classList.contains('is-open')
        || root?.classList.contains('show')
        || notificationMenu.classList.contains('show');

    const syncNotificationSheet = () => {
        const isMobile = mobileSheet.matches;
        const isOpen = isMobile && menuIsOpen();
        document.body.classList.toggle('notification-sheet-open', isOpen);
        backdrop?.setAttribute('aria-hidden', String(!isOpen));

        if (isMobile) {
            notificationButton.setAttribute('aria-haspopup', 'dialog');
            notificationMenu.setAttribute('role', 'dialog');
            notificationMenu.setAttribute('aria-labelledby', 'notificationDrawerTitle');
            notificationMenu.setAttribute('aria-hidden', String(!isOpen));
            if (isOpen) notificationMenu.setAttribute('aria-modal', 'true');
            else notificationMenu.removeAttribute('aria-modal');
        } else {
            notificationButton.setAttribute('aria-haspopup', 'menu');
            notificationMenu.setAttribute('role', 'menu');
            notificationMenu.setAttribute('aria-labelledby', 'notificationDrawerTitle');
            notificationMenu.removeAttribute('aria-hidden');
            notificationMenu.removeAttribute('aria-modal');
        }

        const justOpened = isOpen && !wasOpen;
        const justClosed = !isOpen && wasOpen;
        wasOpen = isOpen;
        if (justOpened) requestAnimationFrame(() => {
            if (mobileSheet.matches && menuIsOpen()) closeButton?.focus({ preventScroll: true });
        });
        if (justClosed) {
            notificationMenu.querySelectorAll('details[open]').forEach(item => { item.open = false; });
            if (notificationMenu.contains(document.activeElement)) profileToggle?.focus({ preventScroll: true });
        }
    };

    const closeDrawer = () => {
        if (mobileSheet.matches && menuIsOpen()) dropdownToggle?.click();
        profileToggle?.focus({ preventScroll: true });
        syncNotificationSheet();
    };

    if (root) {
        const observer = new MutationObserver(syncNotificationSheet);
        observer.observe(root, { attributes: true, attributeFilter: ['class'] });
        observer.observe(notificationMenu, { attributes: true, attributeFilter: ['class'] });
        root.addEventListener('dropdown:open', syncNotificationSheet);
        root.addEventListener('dropdown:close', syncNotificationSheet);
    }

    mobileSheet.addEventListener?.('change', () => {
        if (menuIsOpen()) dropdownToggle?.click();
        syncNotificationSheet();
    });

    // Prevent the generic outside-click handler from closing before the backdrop click.
    backdrop?.addEventListener('pointerdown', event => {
        if (mobileSheet.matches && menuIsOpen()) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
    backdrop?.addEventListener('click', closeDrawer);
    closeButton?.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', event => {
        if (!mobileSheet.matches || !menuIsOpen()) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            closeDrawer();
            return;
        }
        if (event.key !== 'Tab') return;
        const focusable = Array.from(notificationMenu.querySelectorAll(
            'a[href], button:not([disabled]), summary, input:not([disabled]), [tabindex="0"]'
        )).filter(element => element.getClientRects().length && element.tabIndex >= 0);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || !notificationMenu.contains(document.activeElement))) {
            event.preventDefault();
            event.stopPropagation();
            last.focus();
        } else if (!event.shiftKey && (document.activeElement === last || !notificationMenu.contains(document.activeElement))) {
            event.preventDefault();
            event.stopPropagation();
            first.focus();
        }
    }, true);

    notificationButton.addEventListener('click', () => requestAnimationFrame(syncNotificationSheet));
    syncNotificationSheet();
}

let liveStatus = null;

if (notificationButton && notificationHeader) {
    liveStatus = document.createElement('span');
    liveStatus.className = 'notification-live-status';
    liveStatus.setAttribute('role', 'status');
    liveStatus.setAttribute('aria-live', 'polite');
    liveStatus.textContent = '';
    liveStatus.setAttribute('aria-label', 'Canlı bağlantı hazırlanıyor');
    liveStatus.title = 'Canlı bağlantı hazırlanıyor';
    liveStatus.style.cssText = 'display:inline-block;flex:none;width:7px;height:7px;margin-left:auto;border-radius:50%;background:#94a3b8;';
    const actions = notificationHeader.querySelector('.notification-menu__actions');
    if (actions) notificationHeader.insertBefore(liveStatus, actions);
    else notificationHeader.append(liveStatus);
}

const setLiveStatus = (message, state = 'pending') => {
    if (!liveStatus) return;

    liveStatus.textContent = '';
    liveStatus.setAttribute('aria-label', message);
    liveStatus.title = message;
    liveStatus.dataset.state = state;
    liveStatus.style.backgroundColor = state === 'connected'
        ? '#16a34a'
        : state === 'error'
            ? '#dc2626'
            : '#94a3b8';
};

if (!reverbKey || !reverbHost) {
    setLiveStatus('Canlı bildirim yapılandırması eksik', 'error');
} else {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 80),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        },
    });

    const connection = window.Echo.connector.pusher.connection;

    connection.bind('state_change', ({ current }) => {
        if (current === 'connected') {
            setLiveStatus('Canlı bağlantı kuruldu', 'connected');
        } else if (current === 'connecting' || current === 'initialized') {
            setLiveStatus('Canlı bağlantı kuruluyor…');
        } else if (current === 'unavailable' || current === 'failed' || current === 'disconnected') {
            setLiveStatus('Canlı bağlantı kurulamadı; yeniden deneniyor', 'error');
        }
    });

    connection.bind('error', () => {
        setLiveStatus('WebSocket bağlantı hatası', 'error');
    });
}

if (notificationButton && notificationMenu) {
    const list = notificationMenu.querySelector('.notification-menu__list');
    const errorBox = notificationMenu.querySelector('[data-notification-error]');
    const feedUrl = notificationButton.dataset.notificationFeed;
    const dot = document.querySelector('[data-notification-dot]');
    let refreshing = false;
    let pending = false;
    let mutating = false;
    let revision = 0;

    const refresh = async () => {
        if (!feedUrl || !list || mutating) return;
        if (refreshing) { pending = true; return; }
        refreshing = true;
        const startedRevision = revision;
        try {
            const response = await fetch(feedUrl, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Bildirimler güncellenemedi.');
            const data = await response.json();
            if (mutating || revision !== startedRevision) return;
            const unread = Number(data.unread) || 0;
            const count = notificationButton.querySelector('[data-notification-count]');
            notificationButton.dataset.unreadCount = String(unread);
            notificationButton.setAttribute('aria-label', unread ? 'Bildirimler, '+unread+' okunmamış' : 'Bildirimler');
            if (dot) dot.hidden = unread === 0;
            if (count) { count.hidden = unread === 0; count.textContent = unread > 99 ? '99+' : String(unread); }
            if (!list.querySelector('details[open]') && !list.contains(document.activeElement) && typeof data.html === 'string') {
                list.innerHTML = data.html;
            }
        } catch (error) {
            // Keep the last successful list if the connection drops.
        } finally {
            refreshing = false;
            if (pending) { pending = false; refresh(); }
        }
    };

    notificationMenu.addEventListener('submit', async event => {
        const form = event.target;
        if (event.defaultPrevented || !(form instanceof HTMLFormElement)) return;
        event.preventDefault();
        if (mutating) return;
        mutating = true;
        revision++;
        if (errorBox) errorBox.hidden = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: new FormData(form)
            });
            if (!response.ok) throw new Error('İşlem yapılamadı. Lütfen tekrar dene.');
            list.querySelectorAll('details[open]').forEach(item => { item.open = false; });
            if (list.contains(document.activeElement)) notificationMenu.querySelector('[data-notification-drawer-close]')?.focus();
        } catch (error) {
            if (errorBox) { errorBox.textContent = error.message; errorBox.hidden = false; }
        } finally {
            mutating = false;
            refresh();
        }
    });

    notificationButton.addEventListener('click', refresh);
    window.addEventListener('focus', refresh);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.setInterval(() => { if (!document.hidden) refresh(); }, 15000);
    if (window.Echo && notificationButton.dataset.notificationUserId) {
        const channel = window.Echo.private('App.Models.User.'+notificationButton.dataset.notificationUserId);
        channel.subscribed(() => { setLiveStatus('Canlı bildirimler aktif', 'connected'); refresh(); });
        channel.error(() => setLiveStatus('Canlı bağlantı yok; düzenli kontrol aktif', 'error'));
        channel.notification(refresh);
    }
    refresh();
}
