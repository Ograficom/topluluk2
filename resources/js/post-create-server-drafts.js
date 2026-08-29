const loadPostCreateEditorPolish = () => {
    if (document.querySelector('link[data-editorjs-create-polish]')) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/css/editorjs-create-polish.css?v=20260830d';
    link.setAttribute('data-editorjs-create-polish', '1');
    document.head.appendChild(link);
};

loadPostCreateEditorPolish();

const initPostCreateServerDrafts = () => {
    const form = document.getElementById('post-create-form');
    if (!form || form.dataset.serverDraftBound === '1') return;

    form.dataset.serverDraftBound = '1';

    const wrapper = form.querySelector('[data-editorjs-wrapper]');
    const contentJson = form.querySelector('[data-editor-json]');
    const contentFallback = form.querySelector('[data-editor-content]');
    const publishedInput = form.querySelector('#is_published');
    const serverKey = 'ografi:blog-create:server-draft:v1';
    const localDraftKey = 'ografi:blog-create:auto-draft:v2';
    const status = document.querySelector('[data-autosave-status]');
    const statusIcon = status?.querySelector('iconify-icon');

    let timer = null;
    let busy = false;
    let queued = false;
    let publishing = false;

    const readServerState = () => {
        try {
            const raw = localStorage.getItem(serverKey);
            if (!raw) return {};
            const parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch {
            return {};
        }
    };

    const writeServerState = (state) => {
        try {
            localStorage.setItem(serverKey, JSON.stringify(state));
        } catch {
        }
    };

    const clearDraftState = () => {
        try {
            localStorage.removeItem(serverKey);
            localStorage.removeItem(localDraftKey);
        } catch {
        }
    };

    const setStatusIcon = (state) => {
        if (!statusIcon) return;
        const icon = state === 'saving'
            ? 'lucide:cloud-upload'
            : state === 'error'
                ? 'lucide:cloud-alert'
                : 'lucide:cloud-check';
        statusIcon.setAttribute('icon', icon);
        status?.setAttribute('data-state', state);
        status?.setAttribute('aria-label', state === 'saving' ? 'Taslak kaydediliyor' : state === 'error' ? 'Taslak kaydedilemedi' : 'Taslak kaydedildi');
        status?.setAttribute('title', state === 'saving' ? 'Taslak kaydediliyor' : state === 'error' ? 'Taslak kaydedilemedi' : 'Taslak kaydedildi');
    };

    const syncEditorPayload = async () => {
        const editor = wrapper?.__editorInstance;
        if (!editor?.save) return;

        try {
            const output = await editor.save();
            if (contentJson) contentJson.value = JSON.stringify(output);
            if (window.filamentEditorBlocksToHtml && contentFallback) {
                contentFallback.value = window.filamentEditorBlocksToHtml(output.blocks || []);
            }
        } catch {
        }
    };

    const extractSlug = (url) => {
        try {
            const parsed = new URL(url, window.location.origin);
            const match = parsed.pathname.match(/\/blog\/posts\/([^/?#]+)/i);
            return match ? decodeURIComponent(match[1]) : '';
        } catch {
            return '';
        }
    };

    const saveToServer = async () => {
        if (publishing) return;
        if (busy) {
            queued = true;
            return;
        }

        await syncEditorPayload();

        const title = String(form.querySelector('#title')?.value || '').trim();
        const content = String(contentFallback?.value || '').trim();
        if (!title || !content) return;

        busy = true;
        setStatusIcon('saving');

        const state = readServerState();
        const currentSlug = String(state.slug || '').trim();
        const data = new FormData(form);

        data.delete('featured_image');
        data.set('is_published', '0');

        let endpoint = form.action;
        if (currentSlug) {
            endpoint = `/blog/posts/${encodeURIComponent(currentSlug)}`;
            data.set('_method', 'PUT');
        } else {
            data.delete('_method');
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                redirect: 'follow',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Autosave failed: ${response.status}`);
            }

            const slug = extractSlug(response.url) || currentSlug;
            if (slug) {
                writeServerState({ slug, savedAt: Date.now() });
                form.dataset.serverDraftSlug = slug;
            }

            setStatusIcon('saved');
        } catch {
            setStatusIcon('error');
        } finally {
            busy = false;
            if (queued) {
                queued = false;
                window.setTimeout(saveToServer, 350);
            }
        }
    };

    const scheduleServerSave = () => {
        if (publishing) return;
        if (timer) clearTimeout(timer);
        timer = window.setTimeout(saveToServer, 1800);
    };

    form.addEventListener('input', scheduleServerSave, true);
    form.addEventListener('change', scheduleServerSave, true);
    wrapper?.addEventListener('input', scheduleServerSave, true);
    wrapper?.addEventListener('keyup', scheduleServerSave, true);

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-submit-intent="publish"]') : null;
        if (!target || !form.contains(target)) return;

        const state = readServerState();
        const slug = String(state.slug || form.dataset.serverDraftSlug || '').trim();
        publishing = true;
        if (publishedInput) publishedInput.value = '1';

        if (slug) {
            form.action = `/blog/posts/${encodeURIComponent(slug)}`;
            let method = form.querySelector('input[name="_method"]');
            if (!method) {
                method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                form.appendChild(method);
            }
            method.value = 'PUT';
        }
    }, true);

    document.addEventListener('click', async (event) => {
        const saveButton = event.target instanceof Element ? event.target.closest('[data-settings-save]') : null;
        if (!saveButton || !form.contains(saveButton)) return;
        saveButton.disabled = true;
        saveButton.setAttribute('aria-busy', 'true');
        const oldText = saveButton.textContent;
        saveButton.textContent = 'Kaydediliyor…';
        await saveToServer();
        saveButton.textContent = 'Kaydedildi';
        window.setTimeout(() => {
            saveButton.textContent = oldText || 'Kaydet';
            saveButton.disabled = false;
            saveButton.removeAttribute('aria-busy');
        }, 900);
    }, true);

    window.addEventListener('pagehide', () => {
        if (publishing || String(publishedInput?.value || '') === '1') {
            clearDraftState();
        }
    });

    const initialState = readServerState();
    if (initialState.slug) {
        form.dataset.serverDraftSlug = String(initialState.slug);
        setStatusIcon('saved');
    }
};

const movePostCreateCategoryToSettings = () => {
    const categoryMenu = document.querySelector('[data-category-menu]');
    const settingsList = document.querySelector('#settings-modal .settings-panel > .flex-1 > .space-y-3');

    if (!categoryMenu || !settingsList || settingsList.querySelector('[data-create-category-settings]')) return;

    const oldHeaderWrap = categoryMenu.parentElement;
    const section = document.createElement('section');
    section.setAttribute('data-create-category-settings', '');
    section.className = 'rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm';
    section.innerHTML = `
        <div class="mb-3 flex items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <iconify-icon icon="lucide:tag" class="text-[17px]"></iconify-icon>
            </span>
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-950">Kategori</div>
                <div class="mt-0.5 text-xs text-slate-500">Gönderinin yayınlanacağı kategoriyi seç.</div>
            </div>
        </div>
        <div data-category-settings-slot></div>
    `;

    const slot = section.querySelector('[data-category-settings-slot]');
    slot?.appendChild(categoryMenu);

    const summary = categoryMenu.querySelector('summary');
    if (summary) {
        summary.className = 'flex min-h-11 w-full cursor-pointer list-none items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-800 [&::-webkit-details-marker]:hidden';
    }

    const menuPanel = categoryMenu.querySelector(':scope > div');
    if (menuPanel) {
        menuPanel.className = 'mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white';
        menuPanel.style.position = 'static';
        menuPanel.style.maxWidth = 'none';
        menuPanel.style.boxShadow = 'none';
    }

    const coverSection = settingsList.querySelector('[data-create-cover-settings]');
    if (coverSection) {
        coverSection.insertAdjacentElement('afterend', section);
    } else {
        settingsList.prepend(section);
    }

    if (oldHeaderWrap && !oldHeaderWrap.querySelector('[data-category-menu]')) {
        oldHeaderWrap.remove();
    }
};

const polishSettingsDrawer = () => {
    const modal = document.getElementById('settings-modal');
    const panel = modal?.querySelector('.settings-panel');
    if (!modal || !panel) return;

    const footer = panel.querySelector(':scope > .border-t');
    const footerButtons = footer ? Array.from(footer.querySelectorAll('button')) : [];
    const publishButton = footerButtons.find((button) => button.matches('[data-submit-intent="publish"]'));

    if (publishButton) {
        publishButton.type = 'button';
        publishButton.removeAttribute('data-submit-intent');
        publishButton.setAttribute('data-settings-save', '');
        publishButton.textContent = 'Kaydet';
    }

    if (!document.getElementById('ografi-settings-drawer-polish')) {
        const style = document.createElement('style');
        style.id = 'ografi-settings-drawer-polish';
        style.textContent = `
            #settings-modal .settings-panel {
                background: #f8f9fb !important;
                border: 1px solid #d8dee8 !important;
                box-shadow: 0 26px 70px -28px rgba(15,23,42,.32) !important;
                border-radius: 20px !important;
            }
            #settings-modal .settings-panel > div:nth-child(2) {
                min-height: 64px !important;
                background: #ffffff !important;
                border-bottom: 1px solid #dbe2ec !important;
                padding: 13px 18px !important;
            }
            #settings-modal .settings-panel > div:nth-child(2)::after {
                content: '';
                position: absolute;
                left: 18px;
                right: 18px;
                bottom: -1px;
                height: 2px;
                background: linear-gradient(90deg,#2563eb 0 64px,#dbe2ec 64px 100%);
            }
            #settings-modal .settings-panel > div:nth-child(2) { position: relative !important; }
            #settings-modal .settings-panel > .flex-1 {
                background: #f3f5f8 !important;
                padding: 10px !important;
            }
            #settings-modal .settings-panel > .flex-1 > .space-y-3 {
                gap: 8px !important;
            }
            #settings-modal .settings-panel section {
                position: relative !important;
                border: 1px solid #dce2ea !important;
                border-radius: 14px !important;
                background: #fff !important;
                box-shadow: none !important;
                overflow: hidden !important;
            }
            #settings-modal .settings-panel section::before {
                content: '';
                position: absolute;
                inset: 0 auto 0 0;
                width: 3px;
                background: #2563eb;
                opacity: .86;
            }
            #settings-modal .settings-panel section > .mb-3,
            #settings-modal .settings-panel section > details > summary,
            #settings-modal .settings-panel section > summary {
                padding-left: 3px !important;
            }
            #settings-modal .settings-panel summary {
                border-radius: 10px !important;
            }
            #settings-modal .settings-panel [data-create-cover-settings] .create-cover-drop {
                min-height: 132px !important;
                border-radius: 12px !important;
                background: #f5f8ff !important;
            }
            #settings-modal .settings-panel [data-create-category-settings] summary {
                border-radius: 10px !important;
                background: #f6f7f9 !important;
            }
            #settings-modal .settings-panel > .border-t {
                background: #fff !important;
                border-top: 1px solid #dbe2ec !important;
                padding: 12px 14px !important;
            }
            #settings-modal .settings-panel > .border-t .grid {
                gap: 10px !important;
            }
            #settings-modal .settings-panel > .border-t button {
                height: 44px !important;
                border-radius: 11px !important;
                box-shadow: none !important;
            }
            #settings-modal .settings-panel > .border-t button[data-settings-close] {
                background: #eef1f5 !important;
                color: #374151 !important;
                border: 1px solid #d8dee8 !important;
            }
            #settings-modal .settings-panel > .border-t button[data-settings-save] {
                background: #2563eb !important;
                color: #fff !important;
                border: 1px solid #2563eb !important;
                font-weight: 600 !important;
            }
            #settings-modal .settings-panel > .border-t button[data-settings-save]:hover {
                background: #1d4ed8 !important;
            }
            @media (min-width:768px) {
                #settings-modal .settings-panel {
                    width: 440px !important;
                    right: 16px !important;
                    top: 16px !important;
                    height: calc(100dvh - 32px) !important;
                    max-height: calc(100dvh - 32px) !important;
                    border-radius: 20px !important;
                }
            }
            @media (max-width:767px) {
                #settings-modal .settings-panel {
                    height: min(90dvh,820px) !important;
                    max-height: min(90dvh,820px) !important;
                    border-radius: 20px 20px 0 0 !important;
                    border-bottom: 0 !important;
                }
                #settings-modal .settings-panel > .mx-auto.mt-2 {
                    display: block !important;
                    width: 46px !important;
                    height: 5px !important;
                    margin-top: 8px !important;
                    margin-bottom: 4px !important;
                    border-radius: 999px !important;
                    background: #9ca3af !important;
                }
                #settings-modal .settings-panel > div:nth-child(2) {
                    min-height: 58px !important;
                    padding: 10px 14px 12px !important;
                }
                #settings-modal .settings-panel > .flex-1 {
                    padding: 8px 8px 12px !important;
                }
                #settings-modal .settings-panel section {
                    border-radius: 12px !important;
                }
                #settings-modal .settings-panel > .border-t {
                    padding: 10px 10px calc(10px + env(safe-area-inset-bottom)) !important;
                }
            }
        `;
        document.head.appendChild(style);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPostCreateServerDrafts, { once: true });
    document.addEventListener('DOMContentLoaded', movePostCreateCategoryToSettings, { once: true });
    document.addEventListener('DOMContentLoaded', polishSettingsDrawer, { once: true });
} else {
    initPostCreateServerDrafts();
    movePostCreateCategoryToSettings();
    polishSettingsDrawer();
}
