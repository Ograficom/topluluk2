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
        const explicit = card?.getAttribute?.('data-post-vote-slug') || card?.getAttribute?.('data-post-slug') || '';
        if (explicit) return decodeSlug(explicit);

        const id = String(card?.id || '');
        if (!id.startsWith('post-card-shell-')) return '';
        return decodeSlug(id.slice('post-card-shell-'.length));
    };

    const slugFromPostShow = () => {
        const shell = document.querySelector('.post-show-shell');
        if (!shell) return '';

        const explicit = shell.getAttribute('data-post-vote-slug') || shell.getAttribute('data-post-slug') || '';
        if (explicit) return decodeSlug(explicit);

        const canonical = document.querySelector('link[rel="canonical"]')?.getAttribute('href') || '';
        try {
            if (canonical) {
                const parts = new URL(canonical, window.location.origin).pathname.replace(/\/+$/, '').split('/').filter(Boolean);
                if (parts.length) return decodeSlug(parts[parts.length - 1]);
            }
        } catch {
            // Fall through to current pathname.
        }

        const parts = window.location.pathname.replace(/\/+$/, '').split('/').filter(Boolean);
        if (!parts.length) return '';
        return decodeSlug(parts[parts.length - 1]);
    };

    const cardActionTarget = (card) => card?.querySelector(
        '.action-left, [data-post-actions-left], .post-card__actions-left'
    ) || null;

    const postShowActionTarget = (shell) => shell?.querySelector(
        '.ps-action-row, [data-post-show-actions], .post-show-actions'
    ) || null;

    const findVoteTargets = () => {
        const entries = [];

        document.querySelectorAll('article[data-post-card-shell]').forEach((card) => {
            const slug = slugFromCard(card);
            const target = cardActionTarget(card);
            if (!slug || !target || target.dataset.postVoteHydrating === '1') return;
            entries.push({ slug, target, surface: 'card' });
        });

        const showShell = document.querySelector('.post-show-shell');
        const showTarget = postShowActionTarget(showShell);
        const showSlug = slugFromPostShow();
        if (showSlug && showTarget && showTarget.dataset.postVoteHydrating !== '1') {
            entries.push({ slug: showSlug, target: showTarget, surface: 'show' });
        }

        return entries;
    };

    const findPreferenceRow = (checkbox, form) => {
        let node = checkbox?.parentElement || null;
        while (node && node !== form) {
            if (
                node.classList?.contains('flex')
                && node.classList?.contains('items-center')
                && node.classList?.contains('justify-between')
            ) {
                return node;
            }
            node = node.parentElement;
        }
        return null;
    };

    const initComposerVoteSetting = () => {
        const form = document.querySelector('#post-create-form, #post-edit-form');
        if (!form || form.querySelector('[data-post-vote-setting]')) return;

        const commentsCheckbox = form.querySelector('input[type="checkbox"][name="comments_disabled"]');
        if (!commentsCheckbox) return;

        const preferenceRow = findPreferenceRow(commentsCheckbox, form);
        if (!preferenceRow?.parentElement) return;

        const meta = document.querySelector('meta[name="ografi-post-votes-enabled"]');
        const enabled = String(meta?.getAttribute('content') ?? '0') === '1';

        const row = document.createElement('div');
        row.className = preferenceRow.className;
        row.classList.add('post-vote-setting-row');
        row.dataset.postVoteSetting = '';

        const copy = document.createElement('div');
        copy.className = 'post-vote-setting-copy';

        const title = document.createElement('div');
        title.className = 'post-vote-setting-title';
        title.textContent = 'Bu bir oylama';

        const description = document.createElement('div');
        description.className = 'post-vote-setting-description';
        description.textContent = 'Açıldığında post-card ve post-show üzerinde yukarı / aşağı oy sistemi görünür.';

        copy.append(title, description);

        const toggle = document.createElement('div');
        toggle.className = 'post-vote-setting-toggle';

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'votes_enabled';
        hidden.value = '0';

        const label = document.createElement('label');
        label.className = 'post-vote-setting-switch';
        label.title = 'Bu gönderiyi oylama olarak aç veya kapat';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'votes_enabled';
        input.value = '1';
        input.checked = enabled;
        input.setAttribute('aria-label', 'Bu bir oylama');

        const track = document.createElement('span');
        track.className = 'post-vote-setting-track';
        track.setAttribute('aria-hidden', 'true');

        label.append(input, track);
        toggle.append(hidden, label);
        row.append(copy, toggle);
        preferenceRow.insertAdjacentElement('afterend', row);
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

    const mountControl = (target, slug, state) => {
        if (!target || !state?.enabled) return;

        const existing = target.querySelector(CONTROL_SELECTOR);
        if (existing) {
            existing.dataset.postSlug = slug;
            applyState(existing, state);
            return;
        }

        // Eski post-show vote bloğu tepki sayısını gösteriyordu; gerçek post oylamasıyla
        // karışmaması için yalnızca yeni kontrol aktif olduğunda kaldırılır.
        const legacy = target.querySelector('.ps-vote-cluster');
        const control = createControl(slug, state);

        if (legacy) {
            legacy.replaceWith(control);
        } else {
            target.prepend(control);
        }
    };

    const hydrate = async () => {
        const entries = findVoteTargets();
        if (!entries.length) return;

        const bySlug = new Map();
        entries.forEach((entry) => {
            entry.target.dataset.postVoteHydrating = '1';
            if (!bySlug.has(entry.slug)) bySlug.set(entry.slug, []);
            bySlug.get(entry.slug).push(entry);
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
                targets.forEach(({ target }) => {
                    delete target.dataset.postVoteHydrating;

                    if (!state) {
                        delete target.dataset.postVoteChecked;
                        return;
                    }

                    target.dataset.postVoteChecked = '1';

                    if (!state.enabled) {
                        target.querySelector(CONTROL_SELECTOR)?.remove();
                        return;
                    }

                    mountControl(target, slug, state);
                });
            });
        } catch {
            entries.forEach(({ target }) => {
                delete target.dataset.postVoteHydrating;
                delete target.dataset.postVoteChecked;
            });
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
            // İstek başarısızsa eski sayı/oy durumu korunur; sayfa patlatılmaz.
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

    document.addEventListener('ografi:feed-appended', () => {
        initComposerVoteSetting();
        scheduleHydrate();
    });

    const observer = new MutationObserver((records) => {
        if (!records.some((record) => record.addedNodes.length > 0)) return;
        initComposerVoteSetting();
        scheduleHydrate();
    });

    const start = () => {
        initComposerVoteSetting();
        scheduleHydrate();
        if (document.body) observer.observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
