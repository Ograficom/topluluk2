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

let liveStatus = null;

if (notificationButton && notificationHeader) {
    liveStatus = document.createElement('span');
    liveStatus.className = 'notification-live-status';
    liveStatus.setAttribute('role', 'status');
    liveStatus.setAttribute('aria-live', 'polite');
    liveStatus.textContent = 'Canlı bağlantı hazırlanıyor…';
    liveStatus.style.cssText = 'display:block;margin-top:3px;font-size:11px;font-weight:500;color:#64748b;';
    notificationHeader.append(liveStatus);
}

const setLiveStatus = (message, state = 'pending') => {
    if (!liveStatus) return;

    liveStatus.textContent = message;
    liveStatus.dataset.state = state;
    liveStatus.style.color = state === 'connected'
        ? '#16a34a'
        : state === 'error'
            ? '#dc2626'
            : '#64748b';
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
            icon.textContent = '+';

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
