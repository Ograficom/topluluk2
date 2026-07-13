const nextButtonSelector = '[data-feed-load-next], [data-load-more-button][rel="next"]';

const cardKey = (card) => card?.id || card?.getAttribute('data-post-url') || '';

const feedItemFor = (card) => card.closest('.ografi-filterable-post, .profile-post-card-wrapper') || card;

const incomingFeedItems = (doc) => {
    const control = doc.querySelector('[data-feed-load-more], .ografi-feed-loadmore');
    const scope = control?.parentElement || doc;

    return Array.from(scope.querySelectorAll('[data-post-card-shell]')).map(feedItemFor);
};

document.addEventListener('click', async (event) => {
    const button = event.target instanceof Element ? event.target.closest(nextButtonSelector) : null;
    if (!button) return;

    event.preventDefault();
    event.stopImmediatePropagation();

    if (button.dataset.loading === '1') return;

    const controls = button.closest('[data-feed-load-more], .ografi-feed-loadmore');
    const parent = controls?.parentElement;
    const url = button.getAttribute('href');

    if (!controls || !parent || !url) {
        window.location.href = button.href;
        return;
    }

    button.dataset.loading = '1';
    button.classList.add('is-loading');
    button.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/xhtml+xml',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) throw new Error(`Feed request failed: ${response.status}`);

        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const currentKeys = new Set(Array.from(document.querySelectorAll('[data-post-card-shell]')).map(cardKey).filter(Boolean));
        const fragment = document.createDocumentFragment();

        incomingFeedItems(doc).forEach((item) => {
            const card = item.matches('[data-post-card-shell]') ? item : item.querySelector('[data-post-card-shell]');
            const key = cardKey(card);
            if (!card || (key && currentKeys.has(key))) return;
            if (key) currentKeys.add(key);
            fragment.appendChild(item);
        });

        parent.insertBefore(fragment, controls);

        const nextControls = Array.from(doc.querySelectorAll('[data-feed-load-more], .ografi-feed-loadmore'))
            .find((node) => node.querySelector(nextButtonSelector));

        if (nextControls) controls.replaceWith(nextControls);
        else controls.remove();

        document.dispatchEvent(new CustomEvent('ografi:feed-appended', { detail: { url } }));
    } catch (error) {
        window.location.href = button.href;
    }
}, true);

