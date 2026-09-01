const selector = '#show-comment-form textarea.ogx3-textarea';
const replyTextareaSelector = '.ogx-reply-form textarea[name="content"], .ogx-edit-form textarea[name="content"]';
const minimumHeight = 72;
const maximumHeight = 360;

const resizeCommentComposer = (textarea) => {
    if (!(textarea instanceof HTMLTextAreaElement) || !textarea.matches(selector)) return;

    textarea.style.setProperty('height', '0px', 'important');
    textarea.style.setProperty('min-height', `${minimumHeight}px`, 'important');
    textarea.style.setProperty('max-height', `${maximumHeight}px`, 'important');

    const contentHeight = Math.max(textarea.scrollHeight + 2, minimumHeight);
    const height = Math.min(contentHeight, maximumHeight);

    textarea.style.setProperty('height', `${height}px`, 'important');
    textarea.style.setProperty('overflow-y', contentHeight > maximumHeight ? 'auto' : 'hidden', 'important');

    const field = textarea.closest('.ogx3-field');
    if (field) {
        field.style.setProperty('height', 'auto', 'important');
        field.style.setProperty('overflow', 'visible', 'important');
    }
};

const initializeCommentComposers = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;
    root.querySelectorAll(selector).forEach(resizeCommentComposer);
};

const ensureCommentActionStyles = () => {
    if (document.querySelector('style[data-ogx-comment-action-style="v2"]')) return;

    document.querySelector('style[data-ogx-comment-action-style="v1"]')?.remove();

    const style = document.createElement('style');
    style.setAttribute('data-ogx-comment-action-style', 'v2');
    style.textContent = `
        html body .ogx-comments-panel .ogx-votes {
            display: inline-flex !important;
            align-items: center !important;
            gap: 1px !important;
            min-height: 34px !important;
            margin: 0 !important;
            padding: 3px 5px !important;
            border-radius: 9999px !important;
            background: #f4f4f5 !important;
            box-shadow: none !important;
        }

        html body .ogx-comments-panel .ogx-votes form {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn {
            width: 26px !important;
            min-width: 26px !important;
            height: 26px !important;
            min-height: 26px !important;
            padding: 0 !important;
            border-radius: 9999px !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn[aria-label="Beğen"]:hover,
        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn[aria-label="Beğen"]:focus-visible {
            background: #dcfce7 !important;
            color: #16a34a !important;
        }

        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn[aria-label="Beğen"]:active {
            background: #bbf7d0 !important;
            color: #15803d !important;
        }

        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn[aria-label="Beğenme"]:hover,
        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn[aria-label="Beğenme"]:focus-visible {
            background: #fee2e2 !important;
            color: #ef4444 !important;
        }

        html body .ogx-comments-panel .ogx-votes .ogx-vote-btn[aria-label="Beğenme"]:active {
            background: #fecaca !important;
            color: #dc2626 !important;
        }

        html body .ogx-comments-panel .ogx-votes .ogx-vote-count {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 18px !important;
            height: 26px !important;
            margin: 0 !important;
            padding: 0 2px !important;
            color: #18181b !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        html body .ogx-comments-panel .ogx-replies-plus-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 2px !important;
            min-width: 30px !important;
            height: 30px !important;
            padding: 0 7px !important;
            border-radius: 9999px !important;
            background: transparent !important;
            color: #18181b !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        html body .ogx-comments-panel .ogx-replies-plus-btn:hover,
        html body .ogx-comments-panel .ogx-replies-plus-btn:focus-visible,
        html body .ogx-comments-panel .ogx-replies-plus-btn:active {
            background: #f4f4f5 !important;
        }

        html body .ogx-comments-panel .ogx-replies-count {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 10px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        html body .ogx-comments-panel .ografi-reply-editor {
            display: block !important;
            flex: 1 1 100% !important;
            width: 100% !important;
            min-width: 0 !important;
            min-height: 36px !important;
            max-height: 420px !important;
            margin: 0 !important;
            padding: 2px 0 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            box-sizing: border-box !important;
            border: 0 !important;
            outline: 0 !important;
            background: transparent !important;
            color: #111827 !important;
            box-shadow: none !important;
            text-shadow: none !important;
            font-family: "Segoe UI", Arial, sans-serif !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            font-style: normal !important;
            line-height: 20px !important;
            letter-spacing: 0 !important;
            word-spacing: 0 !important;
            white-space: pre-wrap !important;
            overflow-wrap: anywhere !important;
            word-break: normal !important;
            text-align: left !important;
            text-indent: 0 !important;
            direction: ltr !important;
            writing-mode: horizontal-tb !important;
            caret-color: #111827 !important;
            resize: none !important;
            transform: none !important;
            transition: none !important;
            animation: none !important;
            -webkit-user-modify: read-write-plaintext-only;
        }

        html body .ogx-comments-panel .ografi-reply-editor:empty::before {
            content: attr(data-placeholder);
            color: #71717a !important;
            pointer-events: none !important;
        }

        html body .ogx-comments-panel .ografi-reply-editor::selection,
        html body .ogx-comments-panel .ografi-reply-editor *::selection {
            background: #e5e7eb !important;
            color: #111827 !important;
        }

        html body .ogx-comments-panel textarea[data-ografi-hidden-reply-textarea] {
            display: none !important;
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            min-height: 0 !important;
            max-height: 1px !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        html.dark body .ogx-comments-panel .ogx-votes,
        body.dark .ogx-comments-panel .ogx-votes,
        [data-theme="dark"] body .ogx-comments-panel .ogx-votes {
            background: #27272a !important;
        }

        html.dark body .ogx-comments-panel .ogx-votes .ogx-vote-count,
        body.dark .ogx-comments-panel .ogx-votes .ogx-vote-count,
        [data-theme="dark"] body .ogx-comments-panel .ogx-votes .ogx-vote-count,
        html.dark body .ogx-comments-panel .ogx-replies-plus-btn,
        body.dark .ogx-comments-panel .ogx-replies-plus-btn,
        [data-theme="dark"] body .ogx-comments-panel .ogx-replies-plus-btn {
            color: #f4f4f5 !important;
        }

        html.dark body .ogx-comments-panel .ogx-replies-plus-btn:hover,
        html.dark body .ogx-comments-panel .ogx-replies-plus-btn:focus-visible,
        html.dark body .ogx-comments-panel .ogx-replies-plus-btn:active,
        body.dark .ogx-comments-panel .ogx-replies-plus-btn:hover,
        body.dark .ogx-comments-panel .ogx-replies-plus-btn:focus-visible,
        body.dark .ogx-comments-panel .ogx-replies-plus-btn:active,
        [data-theme="dark"] body .ogx-comments-panel .ogx-replies-plus-btn:hover,
        [data-theme="dark"] body .ogx-comments-panel .ogx-replies-plus-btn:focus-visible,
        [data-theme="dark"] body .ogx-comments-panel .ogx-replies-plus-btn:active {
            background: #27272a !important;
        }

        html.dark body .ogx-comments-panel .ografi-reply-editor,
        body.dark .ogx-comments-panel .ografi-reply-editor,
        [data-theme="dark"] body .ogx-comments-panel .ografi-reply-editor {
            color: #f4f4f5 !important;
            caret-color: #f4f4f5 !important;
        }

        html.dark body .ogx-comments-panel .ografi-reply-editor:empty::before,
        body.dark .ogx-comments-panel .ografi-reply-editor:empty::before,
        [data-theme="dark"] body .ogx-comments-panel .ografi-reply-editor:empty::before {
            color: #a1a1aa !important;
        }

        html.dark body .ogx-comments-panel .ografi-reply-editor::selection,
        html.dark body .ogx-comments-panel .ografi-reply-editor *::selection,
        body.dark .ogx-comments-panel .ografi-reply-editor::selection,
        body.dark .ogx-comments-panel .ografi-reply-editor *::selection,
        [data-theme="dark"] body .ogx-comments-panel .ografi-reply-editor::selection,
        [data-theme="dark"] body .ogx-comments-panel .ografi-reply-editor *::selection {
            background: #3f3f46 !important;
            color: #f4f4f5 !important;
        }
    `;

    document.head.appendChild(style);
};

const getDirectReplyCount = (target) => {
    if (!(target instanceof Element)) return 0;

    try {
        return target.querySelectorAll(':scope > [data-ogx-comment]').length;
    } catch {
        return Array.from(target.children).filter((child) => child.matches?.('[data-ogx-comment]')).length;
    }
};

const decorateReplyCounters = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;

    const buttons = [];
    if (root instanceof Element && root.matches('.ogx-replies-plus-btn[data-replies-target]')) {
        buttons.push(root);
    }
    root.querySelectorAll?.('.ogx-replies-plus-btn[data-replies-target]').forEach((button) => buttons.push(button));

    buttons.forEach((button) => {
        const selectorValue = button.getAttribute('data-replies-target');
        if (!selectorValue) return;

        const target = document.querySelector(selectorValue);
        const count = getDirectReplyCount(target);
        if (count < 1) return;

        let counter = button.querySelector('.ogx-replies-count');
        if (!counter) {
            counter = document.createElement('span');
            counter.className = 'ogx-replies-count';
            button.appendChild(counter);
        }

        counter.textContent = String(count);
        button.setAttribute('aria-label', `${count} yanıtı ${button.getAttribute('aria-expanded') === 'true' ? 'gizle' : 'göster'}`);
        button.setAttribute('title', `${count} yanıt`);
    });
};

const editorText = (editor) => {
    if (!(editor instanceof HTMLElement)) return '';
    return String(editor.innerText || '').replace(/\r\n?/g, '\n').replace(/\u00a0/g, ' ');
};

const syncReplyEditor = (editor) => {
    if (!(editor instanceof HTMLElement)) return;

    const form = editor.closest('.ogx-reply-form, .ogx-edit-form');
    const textarea = form?.querySelector('textarea[data-ografi-hidden-reply-textarea]');
    if (!(textarea instanceof HTMLTextAreaElement)) return;

    textarea.value = editorText(editor);
};

const insertPlainTextAtCaret = (text) => {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount < 1) return false;

    const range = selection.getRangeAt(0);
    range.deleteContents();

    const node = document.createTextNode(text);
    range.insertNode(node);
    range.setStartAfter(node);
    range.collapse(true);

    selection.removeAllRanges();
    selection.addRange(range);
    return true;
};

const upgradeReplyTextarea = (textarea) => {
    if (!(textarea instanceof HTMLTextAreaElement)) return;
    if (textarea.hasAttribute('data-ografi-hidden-reply-textarea')) return;

    const form = textarea.closest('.ogx-reply-form, .ogx-edit-form');
    const compose = textarea.closest('.ogx-reply-compose');
    if (!(form instanceof HTMLFormElement) || !(compose instanceof HTMLElement)) return;

    const editor = document.createElement('div');
    editor.className = 'ografi-reply-editor';
    editor.contentEditable = 'true';
    editor.setAttribute('role', 'textbox');
    editor.setAttribute('aria-multiline', 'true');
    editor.setAttribute('spellcheck', 'false');
    editor.setAttribute('autocorrect', 'off');
    editor.setAttribute('autocapitalize', 'off');
    editor.setAttribute('data-placeholder', textarea.getAttribute('placeholder') || 'Yanıt yaz...');
    editor.textContent = textarea.value || '';

    if (textarea.hasAttribute('required')) {
        textarea.removeAttribute('required');
        editor.setAttribute('aria-required', 'true');
        form.setAttribute('data-ografi-editor-required', '1');
    }

    textarea.removeAttribute('data-mentionable');
    textarea.removeAttribute('data-ogx-autogrow');
    textarea.removeAttribute('oninput');
    textarea.oninput = null;
    textarea.setAttribute('data-ografi-hidden-reply-textarea', '');
    textarea.setAttribute('aria-hidden', 'true');
    textarea.tabIndex = -1;
    textarea.style.setProperty('display', 'none', 'important');

    compose.insertBefore(editor, textarea);

    editor.addEventListener('input', () => {
        syncReplyEditor(editor);
    });

    editor.addEventListener('paste', (event) => {
        const text = event.clipboardData?.getData('text/plain');
        if (typeof text !== 'string') return;

        event.preventDefault();
        insertPlainTextAtCaret(text);
        syncReplyEditor(editor);
    });

    editor.addEventListener('blur', () => syncReplyEditor(editor));
};

const initializeReplyEditors = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;

    if (root instanceof HTMLTextAreaElement && root.matches(replyTextareaSelector)) {
        upgradeReplyTextarea(root);
    }

    root.querySelectorAll?.(replyTextareaSelector).forEach(upgradeReplyTextarea);
};

const focusVisibleReplyEditor = (toggle) => {
    if (!(toggle instanceof Element)) return;

    const selectorValue = toggle.getAttribute('data-comment-reply-toggle') || toggle.getAttribute('data-comment-edit-toggle');
    if (!selectorValue) return;

    window.setTimeout(() => {
        const form = document.querySelector(selectorValue);
        if (!(form instanceof HTMLElement) || !form.classList.contains('is-open')) return;

        initializeReplyEditors(form);
        const editor = form.querySelector('.ografi-reply-editor');
        if (editor instanceof HTMLElement) {
            editor.focus({ preventScroll: true });
        }
    }, 0);
};

const initializeCommentActionUi = (root = document) => {
    ensureCommentActionStyles();
    decorateReplyCounters(root);
    initializeReplyEditors(root);
};

document.addEventListener('input', (event) => {
    if (event.target instanceof HTMLTextAreaElement && event.target.matches(selector)) {
        resizeCommentComposer(event.target);
    }
}, true);

document.addEventListener('paste', (event) => {
    if (!(event.target instanceof HTMLTextAreaElement) || !event.target.matches(selector)) return;
    window.requestAnimationFrame(() => resizeCommentComposer(event.target));
}, true);

document.addEventListener('click', (event) => {
    const toggle = event.target instanceof Element
        ? event.target.closest('[data-comment-reply-toggle], [data-comment-edit-toggle]')
        : null;

    if (toggle) focusVisibleReplyEditor(toggle);
}, false);

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('.ogx-reply-form, .ogx-edit-form')) return;

    const editor = form.querySelector('.ografi-reply-editor');
    const textarea = form.querySelector('textarea[data-ografi-hidden-reply-textarea]');
    if (!(editor instanceof HTMLElement) || !(textarea instanceof HTMLTextAreaElement)) return;

    syncReplyEditor(editor);

    if (form.getAttribute('data-ografi-editor-required') === '1' && textarea.value.trim() === '') {
        event.preventDefault();
        editor.focus();
    }
}, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeCommentComposers();
        initializeCommentActionUi();
    }, { once: true });
} else {
    initializeCommentComposers();
    initializeCommentActionUi();
}

window.addEventListener('pageshow', () => {
    initializeCommentComposers();
    initializeCommentActionUi();
});

if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) return;
                if (node.matches(selector)) resizeCommentComposer(node);
                initializeCommentComposers(node);
                initializeCommentActionUi(node);
            });
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
}

const ensureCommentSkeletonStyles = () => {
    if (document.querySelector('style[data-ogx-comment-skeleton="v1"]')) return;

    const style = document.createElement('style');
    style.setAttribute('data-ogx-comment-skeleton', 'v1');
    style.textContent = `
        .ografi-comments-skeleton {
            width: 100% !important;
            max-width: 680px !important;
            margin: 14px auto 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        .ografi-comments-skeleton-card {
            width: 100% !important;
            padding: 16px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
        }

        .ografi-comments-skeleton-head {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin: 0 0 14px !important;
        }

        .ografi-comments-skeleton-avatar {
            width: 44px !important;
            min-width: 44px !important;
            height: 44px !important;
            border-radius: 9999px !important;
            background: #eef1f5 !important;
        }

        .ografi-comments-skeleton-copy {
            display: flex !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
            flex-direction: column !important;
            gap: 9px !important;
        }

        .ografi-comments-skeleton-line {
            display: block !important;
            height: 10px !important;
            border-radius: 9999px !important;
            background: #eef1f5 !important;
        }

        .ografi-comments-skeleton-line--wide {
            width: min(72%, 410px) !important;
        }

        .ografi-comments-skeleton-line--short {
            width: min(42%, 240px) !important;
        }

        .ografi-comments-skeleton-body {
            display: block !important;
            width: 100% !important;
            height: 130px !important;
            border-radius: 14px !important;
            background: #eef1f5 !important;
        }

        html.dark .ografi-comments-skeleton-card,
        body.dark .ografi-comments-skeleton-card,
        [data-theme="dark"] .ografi-comments-skeleton-card {
            border-color: #2f333b !important;
            background: #171a21 !important;
        }

        html.dark .ografi-comments-skeleton-avatar,
        html.dark .ografi-comments-skeleton-line,
        html.dark .ografi-comments-skeleton-body,
        body.dark .ografi-comments-skeleton-avatar,
        body.dark .ografi-comments-skeleton-line,
        body.dark .ografi-comments-skeleton-body,
        [data-theme="dark"] .ografi-comments-skeleton-avatar,
        [data-theme="dark"] .ografi-comments-skeleton-line,
        [data-theme="dark"] .ografi-comments-skeleton-body {
            background: #272b33 !important;
        }

        @media (max-width: 700px) {
            .ografi-comments-skeleton {
                max-width: 100% !important;
                margin-top: 10px !important;
            }

            .ografi-comments-skeleton-card {
                padding: 14px !important;
                border-radius: 14px !important;
            }

            .ografi-comments-skeleton-body {
                height: 118px !important;
            }
        }
    `;

    document.head.appendChild(style);
};

let commentSkeletonStartedAt = 0;

const mountCommentSkeleton = () => {
    const panel = document.querySelector('.ogx-comments-panel');
    if (!(panel instanceof HTMLElement)) return false;
    if (document.querySelector('[data-ogx-comment-skeleton-root]')) return true;

    ensureCommentSkeletonStyles();

    const skeleton = document.createElement('div');
    skeleton.className = 'ografi-comments-skeleton';
    skeleton.setAttribute('data-ogx-comment-skeleton-root', '');
    skeleton.setAttribute('aria-hidden', 'true');
    skeleton.innerHTML = `
        <div class="ografi-comments-skeleton-card">
            <div class="ografi-comments-skeleton-head">
                <span class="ografi-comments-skeleton-avatar"></span>
                <span class="ografi-comments-skeleton-copy">
                    <span class="ografi-comments-skeleton-line ografi-comments-skeleton-line--wide"></span>
                    <span class="ografi-comments-skeleton-line ografi-comments-skeleton-line--short"></span>
                </span>
            </div>
            <span class="ografi-comments-skeleton-body"></span>
        </div>
    `;

    panel.before(skeleton);
    panel.setAttribute('aria-busy', 'true');
    panel.style.setProperty('display', 'none', 'important');
    commentSkeletonStartedAt = performance.now();
    return true;
};

const revealCommentsAfterSkeleton = () => {
    const panel = document.querySelector('.ogx-comments-panel');
    const skeleton = document.querySelector('[data-ogx-comment-skeleton-root]');
    if (!(panel instanceof HTMLElement)) return;

    const elapsed = commentSkeletonStartedAt > 0 ? performance.now() - commentSkeletonStartedAt : 0;
    const wait = Math.max(0, 320 - elapsed);

    window.setTimeout(() => {
        skeleton?.remove();
        panel.style.removeProperty('display');
        panel.removeAttribute('aria-busy');
    }, wait);
};

const bootCommentSkeleton = () => {
    if (!mountCommentSkeleton()) return;

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(revealCommentsAfterSkeleton);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootCommentSkeleton, { once: true });
} else {
    bootCommentSkeleton();
}
