const HOME_FEED_TIMEOUT = 5 * 60 * 1000;

const isHomePage = () => {
    const path = window.location.pathname.replace(/\/+$/, '') || '/';
    return path === '/';
};

const createHomeFeedTimeoutState = () => {
    const main = document.querySelector('main');

    if (!main || main.dataset.homeFeedTimeoutShown === '1') {
        return;
    }

    main.dataset.homeFeedTimeoutShown = '1';
    main.innerHTML = `
        <div class="alma-home-feed-error" role="alert">
            <div class="alma-home-feed-error__inner">
                <div class="alma-home-feed-error__icon" aria-hidden="true">
                    <iconify-icon icon="lucide:shield-alert"></iconify-icon>
                </div>

                <p class="alma-home-feed-error__title">Sunucu geçersiz sayfa verisi döndürdü.</p>

                <div class="alma-home-feed-error__actions">
                    <button
                        type="button"
                        class="alma-home-feed-error__button"
                        data-home-feed-retry
                    >
                        <iconify-icon icon="lucide:refresh-cw"></iconify-icon>
                        <span>Tekrarlamak</span>
                    </button>

                    <a href="/" class="alma-home-feed-error__button">
                        <iconify-icon icon="lucide:house"></iconify-icon>
                        <span>Ev</span>
                    </a>
                </div>
            </div>
        </div>
    `;

    main.querySelector('[data-home-feed-retry]')?.addEventListener('click', () => {
        window.location.reload();
    });
};

const startHomeFeedTimeout = () => {
    if (!isHomePage()) {
        return;
    }

    window.setTimeout(createHomeFeedTimeoutState, HOME_FEED_TIMEOUT);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startHomeFeedTimeout, { once: true });
} else {
    startHomeFeedTimeout();
}
