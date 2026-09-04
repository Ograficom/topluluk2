(() => {
    'use strict';

    const SUMMARY_ENDPOINT = '/blog/post-votes/summary';
    const VOTE_ENDPOINT = (slug) => `/blog/posts/${encodeURIComponent(slug)}/vote`;
    const CONTROL_SELECTOR = '[data-post-vote-control]';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const numberFormatter = new Intl.NumberFormat('tr-TR');
    let hydrateTimer = null;

    const arrowUp = `
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 19V5"></path>
            <path d="m6.5 10.5 5.5-5.5 5.5 5.5"></path>
        </svg>
    `;

    const arrowDown = `
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 5v14"></path>
            <path d="m17.5 13.5-5.5 5.5-5.5-5.5"></path>
        </svg>
    `;

    const decodeSlug = (value) => {
        try {
            return decodeURIComponent(String(value || '').trim());
        } catch {
            return String(value || '').trim();
        }
    };

    const slugFromCard = (card) => {
        const id = String(card?.id || '');
        if (!id.startsWith('post-card-shell-')) return '';
        return decodeSlug(id.slice('post-card-shell-'.length));
    };

    const slugFromPostShow = () => {
        if (!document.querySelector('.post-show-shell')) return '';
        const parts = window.location.pathname.replace(/\/+$/, '').split('/').filter(Boolean);
        if (parts.length < 2 || parts[0] !== 'tr') return '';
        return decodeSlug(parts[parts.length - 1]);
    };

    const findVoteTargets = () => {
        const entries = [];

        document.querySelectorAll('article[data-post-card-shell]').forEach((card) => {
            const slug = slugFromCard(card);
            const target = card.querySelector('.action-left');
            if (!slug || !target || target.dataset.postVoteChecked === '1') return;
            entries.push({ slug, target });
        });

        const showShell = document.querySelector('.post-show-shell');
        const showTarget = showShell?.querySelector('.ps-action-row');
        const showSlug = slugFromPostShow();
        if (showSlug && showTarget && showTarget.dataset.postVoteChecked !== '1') {
            entries.push({ slug: showSlug, target: showTarget });
        }

        return entries;
    };

    const createControl = (slug, state = {}) => {
        const control = document.createElement('span');
        control.className = 'post-vote-control';
        control.dataset.postVoteControl = '';
        control.dataset.postSlug = slug;
        control.setAttribute('role', 'group');
        control.setAttribute('aria-label', 'Gönderi oylaması');

        const up = document.createElement('button');
        up.type = 'button';
        up.className = 'post-vote-btn post-vote-btn--up';
        up.dataset.postVoteValue = '1';
        up.setAttribute('aria-label', 'Olumlu oy ver');
        up.innerHTML = arrowUp;

        const score = document.createElement('span');
        score.className = 'post-vote-score';
        score.dataset.postVoteScore = '';
        score.setAttribute('aria-live', 'polite');

        const down = document.createElement('button');
        down.type = 'button';
        down.className = 'post-vote-btn post-vote-btn--down';
        down.dataset.postVoteValue = '-1';
        down.setAttribute('aria-label', 'Olumsuz oy ver');
        down.innerHTML = arrowDown;

        control.append(up, score, down);
        applyState(control, state);

        return control;
    };

    const applyState = (control, state = {}) => {
        if (!control) return;

        const score = Number(state.score || 0);
        const userVote = Number(state.user_vote || 0);
        const scoreNode = control.querySelector('[data-post-vote-score]');
        const up = control.querySelector('[data-post-vote-value="1"]');
        const down = control.querySelector('[data-post-vote-value="-1"]');

        if (scoreNode) {
            scoreNode.textContent = numberFormatter.format(score);
            scoreNode.title = `Net oy: ${numberFormatter.format(score)}`;
        }

        up?.classList.toggle('is-upvoted', userVote === 1);
        down?.classList.toggle('is-downvoted', userVote === -1);
        up?.setAttribute('aria-pressed', userVote === 1 ? 'true' : 'false');
        down?.setAttribute('aria-pressed', userVote === -1 ? 'true' : 'false');

        if (up) up.title = `Olumlu oy (${numberFormatter.format(Number(state.upvotes || 0))})`;
        if (down) down.title = `Olumsuz oy (${numberFormatter.format(Number(state.downvotes || 0))})`;
    };

    const syncSlugEverywhere = (slug, state) => {
        document.querySelectorAll(CONTROL_SELECTOR).forEach((control) => {
            if (control.dataset.postSlug === slug) applyState(control, state);
        });
    };

    const hydrate = async () => {
        const entries = findVoteTargets();
        if (!entries.length) return;

        const bySlug = new Map();
        entries.forEach((entry) => {
            if (!bySlug.has(entry.slug)) bySlug.set(entry.slug, []);
            bySlug.get(entry.slug).push(entry.target);
        });

        const params = new URLSearchParams();
        Array.from(bySlug.keys()).forEach((slug) => params.append('slugs[]', slug));

        try {
            const response = await fetch(`${SUMMARY_ENDPOINT}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) throw new Error(`vote summary ${response.status}`);
            const payload = await response.json();
            const posts = payload?.posts && typeof payload.posts === 'object' ? payload.posts : {};

            bySlug.forEach((targets, slug) => {
                const state = posts[slug];
                targets.forEach((target) => {
                    target.dataset.postVoteChecked = '1';
                    if (!state?.enabled || target.querySelector(CONTROL_SELECTOR)) return;
                    target.prepend(createControl(slug, state));
                });
            });
        } catch {
            // Ağ hatasında mevcut aksiyon çubuğunu bozma; bir sonraki DOM güncellemesinde tekrar denenebilir.
        }
    };

    const scheduleHydrate = () => {
        if (hydrateTimer) window.clearTimeout(hydrateTimer);
        hydrateTimer = window.setTimeout(() => {
            hydrateTimer = null;
            void hydrate();
        }, 60);
    };

    const setBusy = (control, busy) => {
        control?.querySelectorAll('[data-post-vote-value]').forEach((button) => {
            button.disabled = Boolean(busy);
        });
    };

    const redirectToLogin = (url = '/login') => {
        const target = new URL(url, window.location.origin);
        if (target.origin !== window.location.origin) target.href = '/login';
        if (!target.searchParams.has('redirect')) target.searchParams.set('redirect', window.location.href);
        window.location.assign(target.toString());
    };

    const submitVote = async (control, value) => {
        const slug = control?.dataset.postSlug || '';
        if (!slug || ![-1, 1].includes(value)) return;

        setBusy(control, true);

        try {
            const response = await fetch(VOTE_ENDPOINT(slug), {
                method: 'POST',
                credentials: 'same-origin',
                redirect: 'follow',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ value }),
            });

            const contentType = String(response.headers.get('content-type') || '').toLowerCase();

            if (response.status === 401 || (response.redirected && response.url.includes('/login'))) {
                redirectToLogin(response.url || '/login');
                return;
            }

            if (!response.ok || !contentType.includes('application/json')) {
                throw new Error(`vote request ${response.status}`);
            }

            const state = await response.json();
            syncSlugEverywhere(slug, state);
        } catch {
            // İstek başarısızsa eski sayı/oy durumu korunur.
        } finally {
            setBusy(control, false);
        }
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-post-vote-value]');
        if (!button) return;

        const control = button.closest(CONTROL_SELECTOR);
        if (!control) return;

        event.preventDefault();
        event.stopPropagation();
        const value = Number(button.dataset.postVoteValue || 0);
        void submitVote(control, value);
    }, true);

    document.addEventListener('ografi:feed-appended', scheduleHydrate);

    const observer = new MutationObserver((records) => {
        if (records.some((record) => record.addedNodes.length > 0)) scheduleHydrate();
    });

    const start = () => {
        scheduleHydrate();
        if (document.body) observer.observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
