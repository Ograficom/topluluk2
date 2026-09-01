const selector = '#show-comment-form textarea.ogx3-textarea';
const replySelector = '#comments .ogx-reply-compose textarea';
const minimumHeight = 72;
const maximumHeight = 360;
const replyMinimumHeight = 36;
const replyMaximumHeight = 420;
const commentUiStyleId = 'ografi-comment-ui-fixes';

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

const resizeReplyComposer = (textarea) => {
    if (!(textarea instanceof HTMLTextAreaElement) || !textarea.matches(replySelector)) return;

    const configuredMaximum = Number(textarea.getAttribute('data-ogx-max-height') || replyMaximumHeight);
    const maxHeight = Number.isFinite(configuredMaximum) && configuredMaximum > 0
        ? configuredMaximum
        : replyMaximumHeight;

    textarea.style.setProperty('height', 'auto', 'important');
    textarea.style.setProperty('min-height', `${replyMinimumHeight}px`, 'important');
    textarea.style.setProperty('max-height', `${maxHeight}px`, 'important');

    const contentHeight = Math.max(textarea.scrollHeight + 2, replyMinimumHeight);
    const height = Math.min(contentHeight, maxHeight);

    textarea.style.setProperty('height', `${height}px`, 'important');
    textarea.style.setProperty('overflow-y', contentHeight > maxHeight ? 'auto' : 'hidden', 'important');
};

const installCommentUiStyles = () => {
    if (!document.querySelector('#comments') || document.getElementById(commentUiStyleId)) return;

    const style = document.createElement('style');
    style.id = commentUiStyleId;
    style.textContent = `
        #comments .ogx-filter-item,
        #comments .ogx-comment-menu button,
        #comments .ogx-comment-menu a {
            font-family: "Inter", Arial, Helvetica, sans-serif !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            line-height: 20px !important;
        }

        #comments .ogx-filter-item-icon,
        #comments .ogx-comment-menu-icon {
            width: 16px !important;
            height: 16px !important;
            min-width: 16px !important;
            flex: 0 0 16px !important;
            font-size: 16px !important;
            color: #71717a !important;
        }

        #comments .ogx-filter-item {
            justify-content: flex-start !important;
            gap: 8px !important;
        }

        #comments .ogx-filter-item.is-active::after {
            margin-left: auto !important;
        }

        #comments .ogx-vote-btn {
            width: 28px !important;
            min-width: 28px !important;
            height: 28px !important;
            min-height: 28px !important;
            border-radius: 9999px !important;
            background: transparent !important;
            color: #4b5563 !important;
            transition: none !important;
        }

        #comments .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible) {
            background: #dcfce7 !important;
            color: #16a34a !important;
        }

        #comments .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible) {
            background: #fee2e2 !important;
            color: #dc2626 !important;
        }

        #comments .ogx-reply-compose textarea {
            display: block !important;
            flex: 1 1 100% !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 8px 10px !important;
            box-sizing: border-box !important;
            border: 0 !important;
            outline: 0 !important;
            background: transparent !important;
            color: #111827 !important;
            font-family: "Inter", Arial, Helvetica, sans-serif !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            line-height: 20px !important;
            text-align: left !important;
            text-indent: 0 !important;
            letter-spacing: 0 !important;
            direction: ltr !important;
            unicode-bidi: plaintext !important;
            white-space: pre-wrap !important;
            overflow-wrap: anywhere !important;
            resize: none !important;
            caret-color: currentColor !important;
            cursor: text !important;
            vertical-align: top !important;
            transition: none !important;
        }

        #comments .ogx-reply-compose textarea::placeholder {
            color: #6b7280 !important;
            opacity: 1 !important;
        }

        #comments .ogx-reply-compose textarea:focus::placeholder {
            color: transparent !important;
            opacity: 0 !important;
        }

        html.dark #comments .ogx-vote-btn,
        body.dark #comments .ogx-vote-btn,
        .dark #comments .ogx-vote-btn,
        [data-theme="dark"] #comments .ogx-vote-btn {
            color: #d1d5db !important;
        }

        html.dark #comments .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible),
        body.dark #comments .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible),
        .dark #comments .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible),
        [data-theme="dark"] #comments .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible) {
            background: rgba(34, 197, 94, 0.16) !important;
            color: #4ade80 !important;
        }

        html.dark #comments .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible),
        body.dark #comments .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible),
        .dark #comments .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible),
        [data-theme="dark"] #comments .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible) {
            background: rgba(239, 68, 68, 0.16) !important;
            color: #f87171 !important;
        }

        html.dark #comments .ogx-reply-compose textarea,
        body.dark #comments .ogx-reply-compose textarea,
        .dark #comments .ogx-reply-compose textarea,
        [data-theme="dark"] #comments .ogx-reply-compose textarea {
            color: #f8fafc !important;
        }
    `;

    document.head.appendChild(style);
};

const ensureFilterIcons = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;

    const items = root.matches?.('#comments .ogx-filter-item')
        ? [root]
        : Array.from(root.querySelectorAll?.('#comments .ogx-filter-item') || []);

    items.forEach((item) => {
        if (item.querySelector('.ogx-filter-item-icon')) return;

        const mode = item.getAttribute('data-ogx-sort') || '';
        const icon = document.createElement('iconify-icon');
        icon.className = 'ogx-filter-item-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.setAttribute('icon', mode === 'popular' ? 'lucide:flame' : 'lucide:clock-3');

        const label = item.querySelector('span');
        if (label) {
            item.insertBefore(icon, label);
        } else {
            item.prepend(icon);
        }
    });
};

const removeForeignCommentOwnerControls = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;

    const comments = root.matches?.('#comments [data-ogx-comment][data-ogx-mine="0"]')
        ? [root]
        : Array.from(root.querySelectorAll?.('#comments [data-ogx-comment][data-ogx-mine="0"]') || []);

    comments.forEach((comment) => {
        const menu = comment.querySelector(':scope > .ogx-comment-main > .ogx-comment-actions [data-comment-more-menu]');
        if (menu) {
            menu.querySelectorAll('[data-comment-edit-toggle]').forEach((button) => button.remove());
            menu.querySelectorAll('form').forEach((form) => {
                if (form.querySelector('button.danger')) {
                    form.remove();
                }
            });
        }

        const editForm = comment.querySelector(':scope > .ogx-comment-main > .ogx-edit-form');
        editForm?.remove();
    });
};

const normalizeReplyTextareas = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;

    const textareas = root.matches?.(replySelector)
        ? [root]
        : Array.from(root.querySelectorAll?.(replySelector) || []);

    textareas.forEach((textarea) => {
        textarea.removeAttribute('oninput');
        resizeReplyComposer(textarea);
    });
};

const normalizeCommentUi = (root = document) => {
    installCommentUiStyles();
    ensureFilterIcons(root);
    removeForeignCommentOwnerControls(root);
    normalizeReplyTextareas(root);
};

const initializeCommentComposers = (root = document) => {
    if (!(root instanceof Document || root instanceof Element)) return;
    root.querySelectorAll(selector).forEach(resizeCommentComposer);
    normalizeCommentUi(root);
};

document.addEventListener('input', (event) => {
    if (!(event.target instanceof HTMLTextAreaElement)) return;

    if (event.target.matches(selector)) {
        resizeCommentComposer(event.target);
    }

    if (event.target.matches(replySelector)) {
        resizeReplyComposer(event.target);
    }
}, true);

document.addEventListener('paste', (event) => {
    if (!(event.target instanceof HTMLTextAreaElement)) return;

    if (event.target.matches(selector)) {
        window.requestAnimationFrame(() => resizeCommentComposer(event.target));
    }

    if (event.target.matches(replySelector)) {
        window.requestAnimationFrame(() => resizeReplyComposer(event.target));
    }
}, true);

document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    const toggle = target?.closest('[data-comment-reply-toggle]');
    if (!toggle) return;

    window.requestAnimationFrame(() => {
        const commentsRoot = toggle.closest('#comments');
        const formSelector = toggle.getAttribute('data-comment-reply-toggle') || '';
        if (!commentsRoot || !formSelector) return;

        let form = null;
        try {
            form = commentsRoot.querySelector(formSelector);
        } catch (error) {
            form = null;
        }

        if (!form?.classList.contains('is-open')) return;

        const textarea = form.querySelector('textarea[name="content"]');
        if (!(textarea instanceof HTMLTextAreaElement)) return;

        textarea.removeAttribute('oninput');
        resizeReplyComposer(textarea);
        textarea.focus({ preventScroll: true });

        const end = textarea.value.length;
        if (typeof textarea.setSelectionRange === 'function') {
            textarea.setSelectionRange(end, end);
        }
    });
}, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initializeCommentComposers(), { once: true });
} else {
    initializeCommentComposers();
}

window.addEventListener('pageshow', () => initializeCommentComposers());

if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) return;
                if (node.matches(selector)) resizeCommentComposer(node);
                if (node.matches(replySelector)) resizeReplyComposer(node);
                initializeCommentComposers(node);
            });
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
}
