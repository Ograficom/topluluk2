import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');

if (reverbKey && reverbHost) {
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    });
}

const notificationButton = document.getElementById('notificationMenuButton');

if (notificationButton && window.Echo) {
    const userId = notificationButton.dataset.notificationUserId;
    const openTemplate = notificationButton.dataset.notificationOpenTemplate;

    if (userId && openTemplate) {
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

            if (!notificationButton.querySelector('.notification-trigger__dot')) {
                const dot = document.createElement('span');
                dot.className = 'notification-trigger__dot';
                dot.setAttribute('aria-hidden', 'true');
                notificationButton.append(dot);
            }

            notificationButton.setAttribute('aria-label', `Bildirimler, ${next} okunmamış`);
        };

        window.Echo.private(`App.Models.User.${userId}`).notification((notification) => {
            if (!notification || !notification.id) return;

            const list = document.querySelector('#notificationMenu .notification-menu__list');
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
    }
}
