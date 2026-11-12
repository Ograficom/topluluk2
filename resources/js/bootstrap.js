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

const notificationOptions = notificationMenu?.querySelector('.notification-menu__options');
const notificationOptionsButton = notificationOptions?.querySelector('.pm-item__more');
const notificationOptionsMenu = notificationOptions?.querySelector('.dropdown-menu');

if (notificationOptionsButton && notificationOptionsMenu) {
    notificationOptionsButton.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const willOpen = notificationOptionsMenu.hidden;
        notificationOptionsMenu.hidden = !willOpen;
        notificationOptionsButton.setAttribute('aria-expanded', String(willOpen));
    });

    document.addEventListener('click', (event) => {
        if (!notificationOptions.contains(event.target)) {
            notificationOptionsMenu.hidden = true;
            notificationOptionsButton.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            notificationOptionsMenu.hidden = true;
            notificationOptionsButton.setAttribute('aria-expanded', 'false');
        }
    });
}

if (notificationButton && notificationMenu) {
    const backdrop = document.querySelector('.notification-sheet-backdrop');
    const mobileSheet = window.matchMedia('(max-width: 767px)');

    const syncNotificationSheet = () => {
        const isOpen = mobileSheet.matches && notificationMenu.classList.contains('show');
        document.body.classList.toggle('notification-sheet-open', isOpen);
        if (backdrop) backdrop.setAttribute('aria-hidden', String(!isOpen));
    };

    const observer = new MutationObserver(syncNotificationSheet);
    observer.observe(notificationMenu, { attributes: true, attributeFilter: ['class'] });
    mobileSheet.addEventListener?.('change', syncNotificationSheet);

    backdrop?.addEventListener('click', () => {
        if (notificationMenu.classList.contains('show')) notificationButton.click();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('notification-sheet-open')) {
            notificationButton.click();
        }
    });

    notificationButton.addEventListener('click', () => {
        requestAnimationFrame(syncNotificationSheet);
        requestAnimationFrame(() => requestAnimationFrame(syncNotificationSheet));
    });

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
    notificationHeader.append(liveStatus);
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

if (notificationButton && window.Echo) {
    const userId = notificationButton.dataset.notificationUserId;
    const openTemplate = notificationButton.dataset.notificationOpenTemplate;

    if (userId && openTemplate) {
        const channel = window.Echo.private(`App.Models.User.${userId}`);

        channel.subscribed(() => {
            setLiveStatus('Canlı bildirimler aktif', 'connected');
        });

        channel.error(() => {
            setLiveStatus('Bildirim kanalı yetkilendirilemedi', 'error');
        });

        const updateUnreadCount = () => {
            const current = Number(notificationButton.querySelector('.notification-trigger__count')?.textContent || 0);
            const next = current + 1;
            let count = notificationButton.querySelector('.notification-trigger__count');

            if (!count) {
                count = document.createElement('span');
                count.className = 'notification-trigger__count';
                notificationButton.append(count);
            }

            count.textContent = next > 99 ? '99+' : String(next);

            notificationButton.setAttribute('aria-label', `Bildirimler, ${next} okunmamış`);
        };

        channel.notification((notification) => {
            if (!notification || !notification.id) return;

            const list = notificationMenu?.querySelector('.notification-menu__list');
            if (!list) return;

            list.querySelector('.notification-empty')?.remove();

            const item = document.createElement('div');
            item.className = 'notification-item is-unread';

            const link = document.createElement('a');
            link.className = 'notification-item__main';
            link.href = openTemplate.replace('__NOTIFICATION__', encodeURIComponent(notification.id));

            const icon = document.createElement('span');
            icon.className = 'notification-item__icon';
            icon.setAttribute('aria-hidden', 'true');

            const iconPaths = {
                replies: ['M21 15a4 4 0 0 1-4 4H7l-4 2V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z', 'M8 10h8', 'M8 14h5'],
                likes: ['M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z'],
                posts: ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'M14 2v6h6', 'M8 13h8', 'M8 17h8'],
                announcements: ['M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9', 'M10 21h4', 'M12 2v2'],
                followers: ['M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2', 'M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M19 8v6', 'M16 11h6'],
                messages: ['M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z'],
                mentions: ['M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7', 'M14 11a5 5 0 0 0-7.1 0l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7'],
                security: ['M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z', 'm9 12 2 2 4-4'],
            };
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('width', '18');
            svg.setAttribute('height', '18');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('stroke-width', '1.8');
            svg.setAttribute('stroke-linecap', 'round');
            svg.setAttribute('stroke-linejoin', 'round');

            (iconPaths[notification.category] || iconPaths.announcements).forEach((pathData) => {
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                svg.append(path);
            });
            icon.append(svg);

            const content = document.createElement('span');
            content.className = 'notification-item__content';

            const title = document.createElement('strong');
            title.textContent = notification.title || 'Yeni bildirim';

            const body = document.createElement('span');
            body.textContent = notification.message || 'Hesabında yeni bir hareket var.';

            const time = document.createElement('time');
            time.textContent = 'Az önce';

            const unread = document.createElement('span');
            unread.className = 'notification-item__unread';
            unread.setAttribute('aria-label', 'Okunmadı');

            content.append(title, body, time);
            link.append(icon, content, unread);
            item.append(link);
            list.prepend(item);

            while (list.children.length > 8) {
                list.lastElementChild.remove();
            }

            updateUnreadCount();
        });
    } else {
        setLiveStatus('Kullanıcı bildirim bilgisi bulunamadı', 'error');
    }
}
