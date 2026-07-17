<style>
    .mention-link {
        color: #0e7c86;
        font-weight: 600;
        text-decoration: none;
    }

    .mention-link:hover {
        text-decoration: underline;
    }

    .mention-autocomplete-panel {
        position: absolute;
        z-index: 2200;
        width: min(320px, calc(100vw - 24px));
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.16);
    }

    .mention-autocomplete-panel[hidden] {
        display: none !important;
    }

    .mention-autocomplete-item {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 12px;
        border: 0;
        background: transparent;
        padding: 11px 14px;
        text-align: left;
        color: #0f172a;
        transition: background-color .15s ease;
    }

    .mention-autocomplete-item:hover,
    .mention-autocomplete-item.is-active {
        background: #f8fafc;
    }

    .mention-autocomplete-avatar {
        display: inline-flex;
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .mention-autocomplete-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .mention-autocomplete-copy {
        min-width: 0;
    }

    .mention-autocomplete-name,
    .mention-autocomplete-username {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mention-autocomplete-name {
        font-size: 14px;
        font-weight: 600;
    }

    .mention-autocomplete-username {
        margin-top: 2px;
        font-size: 12px;
        color: #64748b;
    }
</style>

<script>
    (() => {
        const endpoint = document.body?.dataset.mentionsEndpoint || '';
        if (!endpoint) {
            return;
        }

        const textareaSelector = 'textarea[data-mentionable="users"]';
        const panel = document.createElement('div');
        panel.className = 'mention-autocomplete-panel';
        panel.hidden = true;
        document.body.appendChild(panel);

        const state = {
            target: null,
            mode: null,
            query: '',
            start: 0,
            end: 0,
            items: [],
            activeIndex: 0,
            requestId: 0,
            anchorRect: null,
        };

        let searchTimer = null;

        const isEditorTarget = (element) => Boolean(element?.isContentEditable && element.closest('[data-editorjs-wrapper]'));
        const isTextareaTarget = (element) => Boolean(element?.matches?.(textareaSelector));
        const isMentionTarget = (element) => isTextareaTarget(element) || isEditorTarget(element);

        const hidePanel = () => {
            panel.hidden = true;
            panel.innerHTML = '';
            state.items = [];
            state.query = '';
        };

        const escapeHtml = (value = '') => String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const getInitials = (value = '') => {
            const first = String(value || 'U').trim().charAt(0).toUpperCase();
            return first || 'U';
        };

        const setCaretForContenteditable = (target, offset) => {
            const selection = window.getSelection();
            if (!selection) {
                return;
            }

            const range = document.createRange();
            const walker = document.createTreeWalker(target, NodeFilter.SHOW_TEXT);
            let remaining = offset;
            let current = null;

            while ((current = walker.nextNode())) {
                const length = current.textContent?.length ?? 0;
                if (remaining <= length) {
                    range.setStart(current, remaining);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                    target.focus();
                    return;
                }

                remaining -= length;
            }

            range.selectNodeContents(target);
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
            target.focus();
        };

        const getContenteditableState = (target) => {
            const selection = window.getSelection();
            if (!selection || !selection.rangeCount || !target.contains(selection.anchorNode)) {
                return null;
            }

            const range = selection.getRangeAt(0);
            const beforeRange = range.cloneRange();
            beforeRange.selectNodeContents(target);
            beforeRange.setEnd(range.endContainer, range.endOffset);

            const beforeText = beforeRange.toString();
            const match = beforeText.match(/(^|[\s([{"'.,!?;:>\/-])@([A-Za-z0-9._-]{1,50})$/);
            if (!match) {
                return null;
            }

            const query = match[2] || '';
            const end = beforeText.length;
            const start = end - query.length - 1;
            const rect = range.getBoundingClientRect();

            return {
                mode: 'contenteditable',
                query,
                start,
                end,
                rect: rect.width || rect.height ? rect : target.getBoundingClientRect(),
            };
        };

        const getTextareaState = (target) => {
            const end = target.selectionStart ?? 0;
            const beforeText = target.value.slice(0, end);
            const match = beforeText.match(/(^|[\s([{"'.,!?;:>\/-])@([A-Za-z0-9._-]{1,50})$/);
            if (!match) {
                return null;
            }

            return {
                mode: 'textarea',
                query: match[2] || '',
                start: end - (match[2] || '').length - 1,
                end,
                rect: target.getBoundingClientRect(),
            };
        };

        const readStateFromTarget = (target) => {
            if (!target || !isMentionTarget(target)) {
                return null;
            }

            return isTextareaTarget(target)
                ? getTextareaState(target)
                : getContenteditableState(target);
        };

        const positionPanel = () => {
            if (panel.hidden || !state.target) {
                return;
            }

            const rect = state.anchorRect || state.target.getBoundingClientRect();
            const panelWidth = panel.offsetWidth || 320;
            const viewportLeft = window.scrollX + 12;
            const viewportRight = window.scrollX + window.innerWidth - panelWidth - 12;
            const left = Math.max(viewportLeft, Math.min(window.scrollX + rect.left, viewportRight));
            const top = window.scrollY + rect.bottom + 8;

            panel.style.left = `${left}px`;
            panel.style.top = `${top}px`;
        };

        const renderPanel = () => {
            if (!state.items.length) {
                hidePanel();
                return;
            }

            panel.innerHTML = state.items.map((item, index) => `
                <button type="button" class="mention-autocomplete-item ${index === state.activeIndex ? 'is-active' : ''}" data-mention-index="${index}">
                    <span class="mention-autocomplete-avatar">
                        ${item.avatar ? `<img src="${escapeHtml(item.avatar)}" alt="${escapeHtml(item.name)}">` : escapeHtml(getInitials(item.name))}
                    </span>
                    <span class="mention-autocomplete-copy">
                        <span class="mention-autocomplete-name">${escapeHtml(item.name || item.username || 'Kullanici')}</span>
                        <span class="mention-autocomplete-username">@${escapeHtml(item.username || '')}</span>
                    </span>
                </button>
            `).join('');

            panel.hidden = false;
            positionPanel();
        };

        const applyMentionSelection = (item) => {
            if (!item || !state.target) {
                return;
            }

            const replacement = `@${item.username} `;

            if (state.mode === 'textarea' && isTextareaTarget(state.target)) {
                const target = state.target;
                const value = target.value || '';
                target.value = `${value.slice(0, state.start)}${replacement}${value.slice(state.end)}`;
                const caret = state.start + replacement.length;
                target.focus();
                target.setSelectionRange(caret, caret);
                target.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (state.mode === 'contenteditable' && isEditorTarget(state.target)) {
                const target = state.target;
                const value = target.textContent || '';
                target.textContent = `${value.slice(0, state.start)}${replacement}${value.slice(state.end)}`;
                setCaretForContenteditable(target, state.start + replacement.length);
                target.dispatchEvent(new InputEvent('input', {
                    bubbles: true,
                    inputType: 'insertText',
                    data: replacement,
                }));
            }

            hidePanel();
        };

        const loadSuggestions = (query) => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(async () => {
                const requestId = ++state.requestId;

                try {
                    const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    const payload = await response.json();
                    if (requestId !== state.requestId) {
                        return;
                    }

                    state.items = Array.isArray(payload?.data) ? payload.data.filter((item) => item?.username) : [];
                    state.activeIndex = 0;
                    renderPanel();
                } catch (error) {
                    hidePanel();
                }
            }, 120);
        };

        const syncFromTarget = (target) => {
            const next = readStateFromTarget(target);
            if (!next || !next.query) {
                hidePanel();
                return;
            }

            state.target = target;
            state.mode = next.mode;
            state.query = next.query;
            state.start = next.start;
            state.end = next.end;
            state.anchorRect = next.rect;
            loadSuggestions(next.query);
        };

        document.addEventListener('input', (event) => {
            const target = isMentionTarget(event.target)
                ? event.target
                : event.target?.closest?.(textareaSelector) || event.target?.closest?.('[contenteditable="true"]');

            if (!isMentionTarget(target)) {
                return;
            }

            syncFromTarget(target);
        }, true);

        document.addEventListener('click', (event) => {
            const item = event.target.closest('[data-mention-index]');
            if (item) {
                event.preventDefault();
                applyMentionSelection(state.items[Number(item.getAttribute('data-mention-index') || 0)]);
                return;
            }

            if (panel.contains(event.target)) {
                return;
            }

            const target = isMentionTarget(event.target)
                ? event.target
                : event.target?.closest?.(textareaSelector) || event.target?.closest?.('[contenteditable="true"]');

            if (isMentionTarget(target)) {
                syncFromTarget(target);
                return;
            }

            hidePanel();
        }, true);

        document.addEventListener('keydown', (event) => {
            if (panel.hidden || !state.items.length) {
                return;
            }

            const activeTarget = state.target;
            if (!activeTarget || (!isMentionTarget(event.target) && !panel.contains(event.target))) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                state.activeIndex = (state.activeIndex + 1) % state.items.length;
                renderPanel();
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                state.activeIndex = (state.activeIndex - 1 + state.items.length) % state.items.length;
                renderPanel();
                return;
            }

            if (event.key === 'Enter' || event.key === 'Tab') {
                event.preventDefault();
                applyMentionSelection(state.items[state.activeIndex]);
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                hidePanel();
            }
        }, true);

        document.addEventListener('selectionchange', () => {
            if (!state.target || !isEditorTarget(state.target)) {
                return;
            }

            syncFromTarget(state.target);
        });

        panel.addEventListener('mousedown', (event) => {
            event.preventDefault();
        });

        window.addEventListener('resize', positionPanel);
        window.addEventListener('scroll', positionPanel, true);
    })();
</script>
