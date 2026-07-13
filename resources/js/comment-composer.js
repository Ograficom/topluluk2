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
                initializeCommentComposers(node);
            });
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
}

