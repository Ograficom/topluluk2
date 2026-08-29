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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPostCreateServerDrafts, { once: true });
    document.addEventListener('DOMContentLoaded', movePostCreateCategoryToSettings, { once: true });
} else {
    initPostCreateServerDrafts();
    movePostCreateCategoryToSettings();
}
