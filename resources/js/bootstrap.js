import '../css/header-user-menu.css';
import '../css/header-user-menu-tuning.css';
import '../css/header-layout-polish.css';
import '../css/header-logo-fix.css';
import '../css/post-create-settings-polish.css';
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

/*
 * Header yerlesimi artik tamamen CSS tarafindan yonetiliyor.
 * Burada width/height/top/transform gibi degerleri inline !important ile
 * zorlamiyoruz. Boylece desktop, tablet ve mobil breakpoint'leri birbirinin
 * stilini tasimiyor ve Iconify/SVG baseline farklari normal flex merkezlemesini
 * bozamiyor.
 */

const syncHeaderUserMenuLinks = async () => {
    const userMenu = document.querySelector('[data-user-menu-panel]');

    if (!userMenu) {
        return;
    }

    userMenu.removeAttribute('style');

    const menuLinks = Array.from(userMenu.querySelectorAll('a.site-user-menu-link'));
    const getPathname = (link) => {
        try {
            return new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
        } catch {
            return '';
        }
    };

    const panelLink = menuLinks.find((link) => getPathname(link) === '/dashboard') || null;
    const settingsLink = menuLinks.find((link) => getPathname(link) === '/dashboard/profile') || null;

    if (settingsLink) {
        settingsLink.href = '/dashboard';
    }

    if (!panelLink) {
        return;
    }

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
        panelLink.remove();
    }
};

const polishPostCreateSettings = () => {
    const form = document.getElementById('post-create-form');
    const settingsModal = document.getElementById('settings-modal');
    const cover = form?.querySelector('[data-cover-field]');
    const settingsList = settingsModal?.querySelector('.settings-panel > .flex-1 > .space-y-3');

    if (!form || !settingsModal || !cover || !settingsList || settingsList.querySelector('[data-create-cover-settings]')) {
        return;
    }

    const oldCoverContainer = cover.parentElement;
    const section = document.createElement('section');
    section.setAttribute('data-create-cover-settings', '');
    section.className = 'rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm';
    section.innerHTML = `
        <div class="mb-3 flex items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <iconify-icon icon="lucide:image" class="text-[17px]"></iconify-icon>
            </span>
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-950">Öne çıkan görsel</div>
                <div class="mt-0.5 text-xs text-slate-500">Kartlarda ve paylaşım ön izlemesinde kullanılacak görsel.</div>
            </div>
        </div>
        <div data-cover-slot></div>
    `;

    const slot = section.querySelector('[data-cover-slot]');
    slot?.appendChild(cover);
    settingsList.prepend(section);

    if (oldCoverContainer && oldCoverContainer.children.length === 1) {
        oldCoverContainer.classList.remove('py-4', 'sm:py-6');
    }

    const title = document.getElementById('title');
    if (title) {
        title.classList.remove('mt-5');
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncHeaderUserMenuLinks, { once: true });
    document.addEventListener('DOMContentLoaded', polishPostCreateSettings, { once: true });
} else {
    syncHeaderUserMenuLinks();
    polishPostCreateSettings();
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