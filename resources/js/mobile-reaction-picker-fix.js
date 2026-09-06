const MOBILE_REACTION_MAX_WIDTH = 640;
const MOBILE_REACTION_MENU_WIDTH = 244;
const MOBILE_REACTION_EDGE = 10;
const MOBILE_REACTION_HEADER_GUARD = 72;
const MOBILE_REACTION_BOTTOM_GUARD = 88;
const MOBILE_REACTION_GAP = 8;

const setImportant = (element, property, value) => {
    if (!element) return;
    element.style.setProperty(property, value, 'important');
};

const getReactionTrigger = (menu) => {
    if (menu.matches('[data-post-card-reaction-menu]')) {
        const wrap = menu.closest('.post-card__reaction-wrap, [data-post-card-reaction-layer], .reaction-row, .reactions-row');
        return wrap?.querySelector('[data-post-card-reaction-trigger]')
            || menu.parentElement?.querySelector('[data-post-card-reaction-trigger]')
            || null;
    }

    if (menu.matches('[data-post-reaction-menu]')) {
        const picker = menu.closest('[data-post-reaction-picker]');
        return picker?.querySelector('button:not([data-post-reaction-menu])')
            || picker?.querySelector('[role="button"]')
            || null;
    }

    return null;
};

const unlockReactionAncestors = (menu) => {
    let element = menu.parentElement;
    let depth = 0;

    while (element && element !== document.body && depth < 8) {
        if (
            element.matches(
                'article.post-card, [data-post-card-shell], [data-post-card-reaction-layer], .post-card__reaction-wrap, .reaction-row, .reactions-row, .ps-actions-bar, .ps-reaction-row, [data-post-reaction-picker]'
            )
        ) {
            setImportant(element, 'overflow', 'visible');
            setImportant(element, 'overflow-x', 'visible');
            setImportant(element, 'overflow-y', 'visible');
        }

        element = element.parentElement;
        depth += 1;
    }
};

const normalizeReactionChildren = (menu) => {
    const title = menu.querySelector('.post-card__reaction-menu-title, .ps-reaction-menu-title');
    if (title) {
        setImportant(title, 'grid-column', '1 / -1');
        setImportant(title, 'width', '100%');
        setImportant(title, 'margin', '0 0 2px');
        setImportant(title, 'padding', '0 2px 7px');
        setImportant(title, 'box-sizing', 'border-box');
    }

    menu.querySelectorAll('.post-card__reaction-form, .ps-reaction-form, a.post-card__reaction-option').forEach((item) => {
        setImportant(item, 'display', 'inline-flex');
        setImportant(item, 'width', '40px');
        setImportant(item, 'min-width', '40px');
        setImportant(item, 'max-width', '40px');
        setImportant(item, 'height', '40px');
        setImportant(item, 'min-height', '40px');
        setImportant(item, 'max-height', '40px');
        setImportant(item, 'margin', '0');
        setImportant(item, 'padding', '0');
        setImportant(item, 'align-items', 'center');
        setImportant(item, 'justify-content', 'center');
    });

    menu.querySelectorAll('.post-card__reaction-option, .ps-reaction-option').forEach((item) => {
        setImportant(item, 'display', 'inline-flex');
        setImportant(item, 'align-items', 'center');
        setImportant(item, 'justify-content', 'center');
        setImportant(item, 'width', '40px');
        setImportant(item, 'min-width', '40px');
        setImportant(item, 'max-width', '40px');
        setImportant(item, 'height', '40px');
        setImportant(item, 'min-height', '40px');
        setImportant(item, 'max-height', '40px');
        setImportant(item, 'margin', '0');
        setImportant(item, 'padding', '5px');
        setImportant(item, 'box-sizing', 'border-box');
        setImportant(item, 'transform', 'none');
    });

    menu.querySelectorAll(
        '.reaction-emoji, .reaction-emoji--html, .post-card__reaction-asset, .ps-reaction-media, .reaction-emoji--html img, .reaction-emoji--html svg, .reaction-emoji--html iconify-icon, .ps-reaction-option img, .ps-reaction-option svg'
    ).forEach((media) => {
        setImportant(media, 'width', '28px');
        setImportant(media, 'min-width', '28px');
        setImportant(media, 'max-width', '28px');
        setImportant(media, 'height', '28px');
        setImportant(media, 'min-height', '28px');
        setImportant(media, 'max-height', '28px');
        setImportant(media, 'object-fit', 'contain');
    });
};

const positionReactionMenu = (menu) => {
    if (window.innerWidth > MOBILE_REACTION_MAX_WIDTH || menu.hidden) return;

    unlockReactionAncestors(menu);

    setImportant(menu, 'position', 'fixed');
    setImportant(menu, 'display', 'grid');
    setImportant(menu, 'grid-template-columns', 'repeat(5, 40px)');
    setImportant(menu, 'grid-auto-flow', 'row');
    setImportant(menu, 'align-items', 'center');
    setImportant(menu, 'align-content', 'start');
    setImportant(menu, 'justify-content', 'start');
    setImportant(menu, 'column-gap', '6px');
    setImportant(menu, 'row-gap', '6px');
    setImportant(menu, 'width', `min(${MOBILE_REACTION_MENU_WIDTH}px, calc(100vw - ${MOBILE_REACTION_EDGE * 2}px))`);
    setImportant(menu, 'min-width', `min(${MOBILE_REACTION_MENU_WIDTH}px, calc(100vw - ${MOBILE_REACTION_EDGE * 2}px))`);
    setImportant(menu, 'max-width', `calc(100vw - ${MOBILE_REACTION_EDGE * 2}px)`);
    setImportant(menu, 'max-height', `calc(100dvh - ${MOBILE_REACTION_HEADER_GUARD + MOBILE_REACTION_BOTTOM_GUARD + 20}px)`);
    setImportant(menu, 'margin', '0');
    setImportant(menu, 'padding', '9px 10px 10px');
    setImportant(menu, 'overflow-x', 'hidden');
    setImportant(menu, 'overflow-y', 'auto');
    setImportant(menu, 'box-sizing', 'border-box');
    setImportant(menu, 'transform', 'none');
    setImportant(menu, 'right', 'auto');
    setImportant(menu, 'bottom', 'auto');
    setImportant(menu, 'z-index', '2147483647');

    normalizeReactionChildren(menu);

    const trigger = getReactionTrigger(menu);
    if (!trigger) {
        setImportant(menu, 'left', `${MOBILE_REACTION_EDGE}px`);
        setImportant(menu, 'top', `${MOBILE_REACTION_HEADER_GUARD}px`);
        return;
    }

    const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
    const viewportHeight = window.innerHeight;
    const triggerRect = trigger.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const actualWidth = Math.min(menuRect.width || MOBILE_REACTION_MENU_WIDTH, viewportWidth - MOBILE_REACTION_EDGE * 2);
    const actualHeight = Math.min(menuRect.height || 190, viewportHeight - MOBILE_REACTION_HEADER_GUARD - MOBILE_REACTION_BOTTOM_GUARD - 20);

    const left = Math.min(
        Math.max(triggerRect.left, MOBILE_REACTION_EDGE),
        Math.max(MOBILE_REACTION_EDGE, viewportWidth - actualWidth - MOBILE_REACTION_EDGE)
    );

    const spaceAbove = triggerRect.top - MOBILE_REACTION_HEADER_GUARD;
    const spaceBelow = viewportHeight - MOBILE_REACTION_BOTTOM_GUARD - triggerRect.bottom;
    const openAbove = spaceAbove >= actualHeight + MOBILE_REACTION_GAP || spaceAbove >= spaceBelow;

    let top = openAbove
        ? triggerRect.top - actualHeight - MOBILE_REACTION_GAP
        : triggerRect.bottom + MOBILE_REACTION_GAP;

    top = Math.max(MOBILE_REACTION_HEADER_GUARD, top);
    top = Math.min(top, viewportHeight - MOBILE_REACTION_BOTTOM_GUARD - actualHeight);

    setImportant(menu, 'left', `${Math.round(left)}px`);
    setImportant(menu, 'top', `${Math.round(top)}px`);
};

const visibleReactionMenus = () => Array.from(
    document.querySelectorAll('[data-post-card-reaction-menu]:not([hidden]), [data-post-reaction-menu]:not([hidden])')
);

const syncReactionMenus = () => {
    if (window.innerWidth > MOBILE_REACTION_MAX_WIDTH) return;
    visibleReactionMenus().forEach(positionReactionMenu);
};

let scheduled = false;
const scheduleReactionMenuSync = () => {
    if (scheduled) return;
    scheduled = true;

    requestAnimationFrame(() => {
        scheduled = false;
        syncReactionMenus();
        requestAnimationFrame(syncReactionMenus);
    });
};

const observer = new MutationObserver((mutations) => {
    if (window.innerWidth > MOBILE_REACTION_MAX_WIDTH) return;

    if (mutations.some((mutation) => {
        if (mutation.type === 'childList') return true;
        if (mutation.type !== 'attributes') return false;
        return ['hidden', 'class', 'style', 'aria-expanded'].includes(mutation.attributeName);
    })) {
        scheduleReactionMenuSync();
    }
});

const startMobileReactionPickerFix = () => {
    observer.observe(document.documentElement, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['hidden', 'class', 'style', 'aria-expanded'],
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-post-card-reaction-trigger], [data-post-reaction-picker] button')) {
            scheduleReactionMenuSync();
        }
    }, true);

    window.addEventListener('resize', scheduleReactionMenuSync, { passive: true });
    window.addEventListener('scroll', scheduleReactionMenuSync, { passive: true });

    scheduleReactionMenuSync();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startMobileReactionPickerFix, { once: true });
} else {
    startMobileReactionPickerFix();
}
