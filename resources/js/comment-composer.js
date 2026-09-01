const selector = '#show-comment-form textarea.ogx3-textarea';
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
    if (document.querySelector('style[data-ogx-comment-action-style="v1"]')) return;

    const style = document.createElement('style');
    style.setAttribute('data-ogx-comment-action-style', 'v1');
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

const initializeCommentActionUi = (root = document) => {
    ensureCommentActionStyles();
    decorateReplyCounters(root);
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
