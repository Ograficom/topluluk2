import '../css/header-user-menu.css';
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const syncHeaderUserMenuLinks = async () => {
    const userMenu = document.querySelector('[data-user-menu-panel]');

    if (!userMenu) {
        return;
    }

    // header.blade.php icindeki eski inline !important beyaz yuzey, CSS'teki
    // backdrop-filter'i opaklastiriyordu. Menunun sunumsal inline stilini
    // kaldirip gorunumu tamamen header-user-menu.css'e birakiyoruz.
    userMenu.removeAttribute('style');

    const menuLinks = Array.from(userMenu.querySelectorAll('a.site-user-menu-link'));
    const getPathname = (link) => {
        try {
            return new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
        } catch {
            return '';
        }
    };

    // Mevcut Blade yapisinda Panel /dashboard, Ayarlar ise /dashboard/profile.
    // Once ikisini ayri ayri yakaliyoruz; Ayarlar linkini sonradan /dashboard'a
    // cevirdigimiz icin bu siralama iki butonun birbirine karismasini onler.
    const panelLink = menuLinks.find((link) => getPathname(link) === '/dashboard') || null;
    const settingsLink = menuLinks.find((link) => getPathname(link) === '/dashboard/profile') || null;

    if (settingsLink) {
        settingsLink.href = '/dashboard';
    }

    if (!panelLink) {
        return;
    }

    // Rol dogrulanana kadar Panel butonunu gostermeyerek normal kullanicilarda
    // kisa sureli de olsa admin baglantisinin gorunmesini engelliyoruz.
    panelLink.hidden = true;
    panelLink.setAttribute('aria-hidden', 'true');

    try {
        const response = await window.axios.get('/api/user', {
            headers: {
                Accept: 'application/json',
            },
        });

        const role = String(response?.data?.role ?? '').trim().toLowerCase();
        const isAdmin = role === 'admin' || role === 'super_admin';

        if (!isAdmin) {
            panelLink.remove();
            return;
        }

        panelLink.href = '/admin';
        panelLink.hidden = false;
        panelLink.removeAttribute('aria-hidden');
    } catch {
        // Rol teyit edilemiyorsa yetkiyi varsayma; butonu hic gostermemek daha guvenli.
        panelLink.remove();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncHeaderUserMenuLinks, { once: true });
} else {
    syncHeaderUserMenuLinks();
}

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const isProductionOrigin = ['ografi.com', 'www.ografi.com'].includes(window.location.hostname);
const reverbHost = isProductionOrigin
    ? 'ografi.com'
    : (import.meta.env.VITE_REVERB_HOST || window.location.hostname);
const reverbPort = isProductionOrigin
    ? 443
    : Number(import.meta.env.VITE_REVERB_PORT || (window.location.protocol === 'https:' ? 443 : 80));
const reverbUsesTls = isProductionOrigin
    || (import.meta.env.VITE_REVERB_SCHEME ?? window.location.protocol.replace(':', '')) === 'https';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbUsesTls,
    enabledTransports: reverbUsesTls ? ['wss'] : ['ws', 'wss'],
});
