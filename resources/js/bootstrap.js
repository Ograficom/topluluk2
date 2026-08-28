import '../css/header-user-menu.css';
import '../css/header-user-menu-tuning.css';
import '../css/header-layout-polish.css';
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const enforceHeaderActionLayout = () => {
    const header = document.querySelector('header.site-header[data-site-header]');
    const actions = header?.querySelector('.site-header-actions');

    if (!header || !actions) {
        return;
    }

    const setImportant = (element, property, value) => {
        element?.style.setProperty(property, value, 'important');
    };

    const isDark = document.documentElement.classList.contains('dark');
    const isDesktop = window.matchMedia('(min-width: 1024px)').matches;

    // Header arkasindaki sayfa yazilarinin ikonlarin icinden gorunmesini engelle.
    setImportant(header, 'background', isDark ? '#0f172a' : '#ffffff');
    setImportant(header, 'background-color', isDark ? '#0f172a' : '#ffffff');
    setImportant(header, 'backdrop-filter', 'none');
    setImportant(header, '-webkit-backdrop-filter', 'none');

    // Mobilde masaustunden kalmis inline !important display degerleri Tailwind
    // hidden/lg:* kurallarini ezebiliyordu. Arama ve Yaz butonunu mobil/tablette
    // burada kesin olarak gizliyoruz; masaustune donuldugunde asagidaki blok
    // display degerlerini tekrar dogru sekilde kurar.
    if (!isDesktop) {
        const mobileSearchPanel = actions.querySelector('.site-search-panel');
        const mobileWriteButton = actions.querySelector('.site-header-write-btn');

        setImportant(mobileSearchPanel, 'display', 'none');
        setImportant(mobileWriteButton, 'display', 'none');
        return;
    }

    // Tek bir gercek yatay eksen: tum aksiyonlar ayni 40px kontrol kutusunda.
    setImportant(actions, 'display', 'flex');
    setImportant(actions, 'align-items', 'center');
    setImportant(actions, 'justify-content', 'flex-end');
    setImportant(actions, 'gap', '8px');
    setImportant(actions, 'height', '64px');
    setImportant(actions, 'min-height', '64px');
    setImportant(actions, 'margin', '0 0 0 auto');
    setImportant(actions, 'padding', '0');
    setImportant(actions, 'line-height', '1');

    const searchPanel = actions.querySelector('.site-search-panel');
    const notificationsRoot = actions.querySelector('[data-notifications-root]');
    const userMenuRoot = actions.querySelector('[data-user-menu]');

    [searchPanel, notificationsRoot, userMenuRoot].forEach((wrapper) => {
        if (!wrapper) return;
        setImportant(wrapper, 'position', 'relative');
        setImportant(wrapper, 'display', 'flex');
        setImportant(wrapper, 'align-items', 'center');
        setImportant(wrapper, 'justify-content', 'center');
        setImportant(wrapper, 'width', '40px');
        setImportant(wrapper, 'min-width', '40px');
        setImportant(wrapper, 'max-width', '40px');
        setImportant(wrapper, 'height', '40px');
        setImportant(wrapper, 'min-height', '40px');
        setImportant(wrapper, 'max-height', '40px');
        setImportant(wrapper, 'flex', '0 0 40px');
        setImportant(wrapper, 'margin', '0');
        setImportant(wrapper, 'padding', '0');
        setImportant(wrapper, 'top', '0');
        setImportant(wrapper, 'transform', 'none');
        setImportant(wrapper, 'overflow', 'visible');
    });

    const searchButton = actions.querySelector('.site-search-trigger');
    const notificationButton = actions.querySelector('button[data-notifications-btn]');
    const messagesButton = actions.querySelector('a.site-header-messages-link');
    const userButton = actions.querySelector('button[data-user-menu-btn]');

    [searchButton, notificationButton, messagesButton, userButton].forEach((button) => {
        if (!button) return;
        setImportant(button, 'position', 'relative');
        setImportant(button, 'display', 'inline-flex');
        setImportant(button, 'align-items', 'center');
        setImportant(button, 'justify-content', 'center');
        setImportant(button, 'width', '40px');
        setImportant(button, 'min-width', '40px');
        setImportant(button, 'max-width', '40px');
        setImportant(button, 'height', '40px');
        setImportant(button, 'min-height', '40px');
        setImportant(button, 'max-height', '40px');
        setImportant(button, 'flex', '0 0 40px');
        setImportant(button, 'margin', '0');
        setImportant(button, 'padding', '0');
        setImportant(button, 'top', '0');
        setImportant(button, 'bottom', 'auto');
        setImportant(button, 'transform', 'none');
        setImportant(button, 'translate', 'none');
        setImportant(button, 'line-height', '1');
        setImportant(button, 'vertical-align', 'middle');
        setImportant(button, 'overflow', 'visible');
    });

    const searchIcon = searchButton?.querySelector('iconify-icon');
    const bellIcon = notificationButton?.querySelector('iconify-icon');
    const messageIcon = messagesButton?.querySelector('svg');

    [searchIcon, bellIcon, messageIcon].forEach((icon) => {
        if (!icon) return;
        setImportant(icon, 'position', 'static');
        setImportant(icon, 'display', 'block');
        setImportant(icon, 'width', '20px');
        setImportant(icon, 'min-width', '20px');
        setImportant(icon, 'height', '20px');
        setImportant(icon, 'min-height', '20px');
        setImportant(icon, 'margin', '0');
        setImportant(icon, 'padding', '0');
        setImportant(icon, 'font-size', '20px');
        setImportant(icon, 'line-height', '20px');
        setImportant(icon, 'transform', 'none');
    });

    const avatar = userButton?.querySelector('img, .site-avatar-fallback');
    if (avatar) {
        setImportant(avatar, 'position', 'static');
        setImportant(avatar, 'display', 'flex');
        setImportant(avatar, 'align-items', 'center');
        setImportant(avatar, 'justify-content', 'center');
        setImportant(avatar, 'width', '36px');
        setImportant(avatar, 'min-width', '36px');
        setImportant(avatar, 'height', '36px');
        setImportant(avatar, 'min-height', '36px');
        setImportant(avatar, 'margin', '0');
        setImportant(avatar, 'transform', 'none');
    }

    const writeButton = actions.querySelector('.site-header-write-btn');
    if (writeButton) {
        setImportant(writeButton, 'display', 'inline-flex');
        setImportant(writeButton, 'align-items', 'center');
        setImportant(writeButton, 'justify-content', 'center');
        setImportant(writeButton, 'height', '40px');
        setImportant(writeButton, 'min-height', '40px');
        setImportant(writeButton, 'max-height', '40px');
        setImportant(writeButton, 'margin', '0');
        setImportant(writeButton, 'padding', '0 17px');
        setImportant(writeButton, 'top', '0');
        setImportant(writeButton, 'transform', 'none');
        setImportant(writeButton, 'line-height', '1');
    }

    // Masaustunde arama alani ikonun ALTINDA degil, header satirinin icinde acilir.
    // Search wrapper 40px yukseklikte ve header icinde ortali oldugu icin -2px,
    // 44px yukseklikteki inputu header'in tam dikey merkezine getirir. Sonuclar
    // inputun hemen altinda, header bittikten sonra acilir.
    const searchDropdown = searchPanel?.querySelector('[data-search-dropdown], .site-search-dropdown');
    if (searchDropdown) {
        setImportant(searchDropdown, 'position', 'absolute');
        setImportant(searchDropdown, 'top', '-2px');
        setImportant(searchDropdown, 'right', '0');
        setImportant(searchDropdown, 'left', 'auto');
        setImportant(searchDropdown, 'width', '400px');
        setImportant(searchDropdown, 'max-width', 'calc(100vw - 24px)');
        setImportant(searchDropdown, 'margin', '0');
        setImportant(searchDropdown, 'z-index', '100020');
        setImportant(searchDropdown, 'transform', 'none');
    }

    const searchDropdownTop = searchDropdown?.querySelector('.site-search-dropdown-top');
    if (searchDropdownTop) {
        setImportant(searchDropdownTop, 'display', 'flex');
        setImportant(searchDropdownTop, 'align-items', 'center');
        setImportant(searchDropdownTop, 'gap', '10px');
        setImportant(searchDropdownTop, 'width', '100%');
    }

    const searchField = searchDropdown?.querySelector('label.site-search-field, .site-search-field');
    if (searchField) {
        setImportant(searchField, 'flex', '1 1 auto');
        setImportant(searchField, 'width', 'auto');
        setImportant(searchField, 'min-width', '0');
        setImportant(searchField, 'margin', '0');
    }

    // Noktalar akis hizasini degistirmez; ait olduklari 40px kutuya sabitlenir.
    actions.querySelectorAll('.site-status-dot').forEach((dot) => {
        setImportant(dot, 'position', 'absolute');
        setImportant(dot, 'top', '6px');
        setImportant(dot, 'right', '6px');
        setImportant(dot, 'margin', '0');
        setImportant(dot, 'transform', 'none');
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enforceHeaderActionLayout, { once: true });
} else {
    enforceHeaderActionLayout();
}

window.addEventListener('pageshow', enforceHeaderActionLayout);
window.addEventListener('themechange', enforceHeaderActionLayout);
window.addEventListener('resize', enforceHeaderActionLayout);

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