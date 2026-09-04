import '../css/header-user-menu.css';
import '../css/header-user-menu-tuning.css';
import '../css/header-layout-polish.css';
import '../css/header-logo-fix.css';
import '../css/post-create-settings-polish.css';
import '../css/post-create-settings-tabs.css';
import '../css/post-create-mobile-fix.css';
import '../css/post-card-footer-polish.css';
import '../css/video-mobile-header-fade.css';
import '../css/editorjs-create-polish.css';
import './post-create-server-drafts.js';
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

const setupPostCreateAutosave = () => {
    const form = document.getElementById('post-create-form');
    if (!form || form.dataset.autosaveBound === '1') return;
    form.dataset.autosaveBound = '1';

    const storageKey = 'ografi:blog-create:auto-draft:v2';
    const wrapper = form.querySelector('[data-editorjs-wrapper]');
    const contentJson = form.querySelector('[data-editor-json]');
    const contentFallback = form.querySelector('[data-editor-content]');
    const publishedInput = form.querySelector('#is_published');
    let saveTimer = null;
    let restoring = false;

    const headerActions = document.querySelector('.create-page-fixed header .flex.shrink-0');
    let status = headerActions?.querySelector('[data-autosave-status]') || null;

    if (headerActions && !status) {
        status = document.createElement('span');
        status.setAttribute('data-autosave-status', '');
        status.className = 'post-create-autosave-status';
        status.innerHTML = '<iconify-icon icon="lucide:cloud-check"></iconify-icon><span data-autosave-label>Otomatik taslak</span>';
        headerActions.prepend(status);
    }

    const statusLabel = status?.querySelector('[data-autosave-label]');
    const statusIcon = status?.querySelector('iconify-icon');

    const setStatus = (state, label) => {
        if (!status) return;
        status.dataset.state = state;
        if (statusLabel) statusLabel.textContent = label;
        if (statusIcon) {
            statusIcon.setAttribute('icon', state === 'saving' ? 'lucide:cloud-upload' : state === 'restored' ? 'lucide:history' : 'lucide:cloud-check');
        }
    };

    const readStoredDraft = () => {
        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) return null;
            const draft = JSON.parse(raw);
            return draft && typeof draft === 'object' ? draft : null;
        } catch {
            return null;
        }
    };

    const serializableFields = () => Array.from(form.elements).filter((field) => {
        if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) return false;
        if (!field.name || field.name === '_token' || field.type === 'file' || field.type === 'submit' || field.type === 'button') return false;
        return true;
    });

    const checkboxNames = () => new Set(
        serializableFields()
            .filter((field) => field instanceof HTMLInputElement && (field.type === 'checkbox' || field.type === 'radio'))
            .map((field) => field.name)
    );

    const collectFields = () => {
        const values = {};
        const groupedNames = checkboxNames();

        serializableFields().forEach((field) => {
            if (field instanceof HTMLInputElement && field.type === 'hidden' && groupedNames.has(field.name)) {
                return;
            }

            if (field instanceof HTMLInputElement && (field.type === 'checkbox' || field.type === 'radio')) {
                if (!Array.isArray(values[field.name])) values[field.name] = [];
                if (field.checked) values[field.name].push(field.value);
                return;
            }

            values[field.name] = field.value;
        });
        return values;
    };

    const syncEditorPayload = async () => {
        if (!wrapper?.__editorInstance?.save) return;
        try {
            const output = await wrapper.__editorInstance.save();
            if (contentJson) contentJson.value = JSON.stringify(output);
            if (window.filamentEditorBlocksToHtml && contentFallback) {
                contentFallback.value = window.filamentEditorBlocksToHtml(output.blocks || []);
            }
        } catch {
            // Editor hazır değilse mevcut hidden/textarea değerlerini koru.
        }
    };

    const saveDraft = async () => {
        if (restoring) return;
        setStatus('saving', 'Kaydediliyor');
        await syncEditorPayload();

        const payload = {
            version: 2,
            savedAt: Date.now(),
            fields: collectFields(),
            contentJson: contentJson?.value || '',
            content: contentFallback?.value || '',
        };

        try {
            localStorage.setItem(storageKey, JSON.stringify(payload));
            setStatus('saved', 'Taslak kaydedildi');
        } catch {
            setStatus('saved', 'Otomatik taslak');
        }
    };

    const scheduleSave = () => {
        if (restoring) return;
        setStatus('saving', 'Kaydediliyor');
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = window.setTimeout(saveDraft, 900);
    };

    const restoreDraft = async () => {
        const draft = readStoredDraft();
        if (!draft?.fields) {
            setStatus('saved', 'Otomatik taslak');
            return;
        }

        restoring = true;
        const fields = serializableFields();
        const groupedNames = checkboxNames();

        fields.forEach((field) => {
            if (field instanceof HTMLInputElement && field.type === 'hidden' && groupedNames.has(field.name)) {
                return;
            }

            const stored = draft.fields[field.name];
            if (stored === undefined || stored === null) return;

            if (field instanceof HTMLInputElement && (field.type === 'checkbox' || field.type === 'radio')) {
                const selected = Array.isArray(stored) ? stored.map(String) : [String(stored)];
                field.checked = selected.includes(String(field.value));
                return;
            }

            field.value = String(stored);
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (contentJson && draft.contentJson) contentJson.value = String(draft.contentJson);
        if (contentFallback && draft.content) contentFallback.value = String(draft.content);

        const title = form.querySelector('#title');
        title?.dispatchEvent(new Event('input', { bubbles: true }));

        window.setTimeout(async () => {
            const editor = wrapper?.__editorInstance;
            if (editor?.render && draft.contentJson) {
                try {
                    const parsed = JSON.parse(draft.contentJson);
                    if (Array.isArray(parsed?.blocks)) await editor.render(parsed);
                } catch {
                    // Geçersiz eski JSON varsa fallback içerik kalır.
                }
            }
        }, 1200);

        restoring = false;
        setStatus('restored', 'Taslak geri yüklendi');
        window.setTimeout(() => setStatus('saved', 'Taslak kaydedildi'), 2200);
    };

    form.addEventListener('input', scheduleSave, true);
    form.addEventListener('change', scheduleSave, true);
    wrapper?.addEventListener('input', scheduleSave, true);
    wrapper?.addEventListener('keyup', scheduleSave, true);

    form.addEventListener('submit', () => {
        if (saveTimer) clearTimeout(saveTimer);
        if (String(publishedInput?.value || '') === '1') {
            try { localStorage.removeItem(storageKey); } catch {}
        } else {
            void saveDraft();
        }
    });

    window.addEventListener('pagehide', () => {
        if (String(publishedInput?.value || '') !== '1') void saveDraft();
    });

    void restoreDraft();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncHeaderUserMenuLinks, { once: true });
    document.addEventListener('DOMContentLoaded', polishPostCreateSettings, { once: true });
    document.addEventListener('DOMContentLoaded', setupPostCreateAutosave, { once: true });
} else {
    syncHeaderUserMenuLinks();
    polishPostCreateSettings();
    setupPostCreateAutosave();
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