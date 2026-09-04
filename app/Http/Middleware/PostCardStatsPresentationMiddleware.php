<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCardStatsPresentationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! method_exists($response, 'getContent')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '' || ! str_contains($html, 'data-post-card-stats-modal')) {
            return $response;
        }

        $html = str_replace(
            [
                '<span>akışlardaki izlenimler</span>',
                '<span>ilanları</span>',
                '<span>gönderilere verilen tepkiler</span>',
                '<span>yorumlar</span>',
                '<span>yer işaretleri</span>',
                '</span> etkileşim</strong>',
                "label.textContent = 'Tepki';",
            ],
            [
                '<span>Akışlardan Gösterim</span>',
                '<span>Görüntülenen</span>',
                '<span>Tepkiler</span>',
                '<span>Yorumlar</span>',
                '<span>Kaydedildi</span>',
                '</span> Etkileşim</strong>',
                "label.textContent = 'Tepkiler';",
            ],
            $html
        );

        if (! str_contains($html, 'data-og-post-stats-presentation')) {
            $assets = <<<'HTML'
<style data-og-post-stats-presentation>
dialog.post-card__stats-modal[open] {
    position: fixed !important;
    inset: 0 !important;
    display: grid !important;
    place-items: center !important;
    width: 100vw !important;
    height: 100dvh !important;
    min-width: 100vw !important;
    min-height: 100dvh !important;
    margin: 0 !important;
    padding: 12px !important;
    box-sizing: border-box !important;
}

dialog.post-card__stats-modal[open] > .post-card__stats-panel {
    position: relative !important;
    top: auto !important;
    right: auto !important;
    bottom: auto !important;
    left: auto !important;
    margin: 0 !important;
    transform: none !important;
}

.post-card__stats-item--reactions .post-card__reaction-summary-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 5px !important;
    width: 100% !important;
    min-width: 0 !important;
    overflow: visible !important;
}

.post-card__stats-item--reactions .post-card__reaction-summary-label {
    display: inline-block !important;
    flex: 0 0 auto !important;
    white-space: nowrap !important;
}

.post-card__stats-item--reactions .post-card__reaction-avatar-stack {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex: 0 0 auto !important;
    min-width: 0 !important;
    padding-left: 2px !important;
    overflow: visible !important;
}

.post-card__stats-item--reactions .post-card__reaction-avatar,
.post-card__stats-item--reactions .post-card__reaction-avatar-fallback,
.post-card__stats-item--reactions .post-card__reaction-avatar-guest,
.post-card__stats-item--reactions .post-card__reaction-avatar-more {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    margin-left: -9px !important;
    border: 1.5px solid #ffffff !important;
    border-radius: 9999px !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    background: #eef2f7 !important;
    color: #64748b !important;
    object-fit: cover !important;
    box-shadow: none !important;
    font-size: 7px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
}

.post-card__stats-item--reactions .post-card__reaction-avatar-stack > :first-child {
    margin-left: 0 !important;
}

.post-card__stats-item--reactions .post-card__reaction-avatar-guest iconify-icon {
    font-size: 11px !important;
    color: #64748b !important;
}

.post-card__stats-item--reactions .post-card__reaction-avatar-more {
    overflow: visible !important;
    background: #f3f4f6 !important;
    color: #111827 !important;
    white-space: nowrap !important;
    z-index: 20 !important;
}

.post-card__reaction-detail-row--guest .post-card__reaction-detail-name {
    color: #6b7280 !important;
}

.post-card__reaction-detail-avatar-guest {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    border: 0 !important;
    border-radius: 9999px !important;
    background: #eef2f7 !important;
    color: #64748b !important;
}

.post-card__reaction-detail-avatar-guest iconify-icon {
    font-size: 18px !important;
}

html.dark .post-card__stats-item--reactions .post-card__reaction-avatar,
html.dark .post-card__stats-item--reactions .post-card__reaction-avatar-fallback,
html.dark .post-card__stats-item--reactions .post-card__reaction-avatar-guest,
html.dark .post-card__stats-item--reactions .post-card__reaction-avatar-more,
.dark .post-card__stats-item--reactions .post-card__reaction-avatar,
.dark .post-card__stats-item--reactions .post-card__reaction-avatar-fallback,
.dark .post-card__stats-item--reactions .post-card__reaction-avatar-guest,
.dark .post-card__stats-item--reactions .post-card__reaction-avatar-more {
    border-color: #17181b !important;
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

html.dark .post-card__reaction-detail-avatar-guest,
.dark .post-card__reaction-detail-avatar-guest {
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

@supports not (height: 100dvh) {
    dialog.post-card__stats-modal[open] {
        height: 100vh !important;
        min-height: 100vh !important;
    }
}
</style>
<script data-og-post-stats-presentation>
(() => {
    const MAX_VISIBLE = 5;
    const MAX_OVERFLOW = 99;

    const slugFromCard = (card) => {
        const id = String(card?.id || '');
        return id.startsWith('post-card-shell-') ? id.slice('post-card-shell-'.length) : '';
    };

    const endpointForCard = (card) => {
        const slug = slugFromCard(card);
        if (!slug) return null;
        return `/posts/${encodeURIComponent(slug)}/reactions?details=1`;
    };

    const reactionTile = (modal) => {
        return modal?.querySelector('.post-card__stats-item--reactions')
            || modal?.querySelector('.post-card__stats-grid > .post-card__stats-item:nth-child(3)')
            || null;
    };

    const ensureStack = (modal) => {
        const tile = reactionTile(modal);
        if (!tile) return null;

        tile.classList.add('post-card__stats-item--reactions');
        tile.setAttribute('role', 'button');
        tile.setAttribute('tabindex', '0');
        tile.setAttribute('aria-label', 'Tepkileri göster');

        let label = tile.querySelector('.post-card__reaction-summary-label');
        if (!label) {
            label = tile.querySelector('span');
        }
        if (!label) return null;

        label.textContent = 'Tepkiler';
        label.classList.add('post-card__reaction-summary-label');

        let row = tile.querySelector('.post-card__reaction-summary-row');
        let stack = tile.querySelector('[data-post-card-reaction-avatar-stack]');

        if (!row) {
            row = document.createElement('div');
            row.className = 'post-card__reaction-summary-row';
            label.parentNode?.insertBefore(row, label);
            row.appendChild(label);
        } else if (label.parentElement !== row) {
            row.prepend(label);
        }

        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'post-card__reaction-avatar-stack';
            stack.setAttribute('data-post-card-reaction-avatar-stack', '');
            row.appendChild(stack);
        }

        return { tile, stack };
    };

    const avatarNode = (person) => {
        if (person?.avatar) {
            const img = document.createElement('img');
            img.className = 'post-card__reaction-avatar';
            img.src = person.avatar;
            img.alt = person?.name ? `${person.name} profil resmi` : 'Profil resmi';
            img.loading = 'lazy';
            img.decoding = 'async';
            if (person?.profile_url) {
                img.title = person.name || 'Kullanıcı';
            }
            return img;
        }

        const fallback = document.createElement('span');
        fallback.className = 'post-card__reaction-avatar-fallback';
        fallback.textContent = person?.initials || '?';
        fallback.title = person?.name || 'Kullanıcı';
        return fallback;
    };

    const guestAvatarNode = () => {
        const guest = document.createElement('span');
        guest.className = 'post-card__reaction-avatar-guest';
        guest.title = 'Misafir tepki';
        guest.innerHTML = '<iconify-icon icon="lucide:user-round"></iconify-icon>';
        return guest;
    };

    const renderSummary = (modal, payload) => {
        const refs = ensureStack(modal);
        if (!refs) return;

        const total = Math.max(0, Number(payload?.total) || Number(modal?.dataset?.postCardStatsReactionsCount) || 0);
        const registered = Array.isArray(payload?.preview) ? payload.preview.slice(0, MAX_VISIBLE) : [];
        const visibleTarget = Math.min(total, MAX_VISIBLE);
        const missingVisible = Math.max(visibleTarget - registered.length, 0);

        const countNode = refs.tile.querySelector('strong');
        if (countNode) {
            countNode.textContent = new Intl.NumberFormat('tr-TR').format(total);
        }

        refs.stack.replaceChildren();
        registered.forEach((person) => refs.stack.appendChild(avatarNode(person)));
        for (let i = 0; i < missingVisible; i += 1) {
            refs.stack.appendChild(guestAvatarNode());
        }

        const overflow = Math.min(Math.max(total - MAX_VISIBLE, 0), MAX_OVERFLOW);
        if (overflow > 0) {
            const more = document.createElement('span');
            more.className = 'post-card__reaction-avatar-more';
            more.textContent = `+${overflow}`;
            more.title = `${overflow} tepki daha`;
            refs.stack.appendChild(more);
        }
    };

    const guestDetailRow = (count) => {
        const row = document.createElement('div');
        row.className = 'post-card__reaction-detail-row post-card__reaction-detail-row--guest';

        const left = document.createElement('span');
        left.className = 'post-card__reaction-detail-person';

        const avatar = document.createElement('span');
        avatar.className = 'post-card__reaction-detail-avatar-guest';
        avatar.innerHTML = '<iconify-icon icon="lucide:user-round"></iconify-icon>';

        const name = document.createElement('span');
        name.className = 'post-card__reaction-detail-name';
        name.textContent = count > 1 ? `Misafir kullanıcılar (${count})` : 'Misafir kullanıcı';

        const value = document.createElement('span');
        value.className = 'post-card__reaction-detail-value';
        value.textContent = 'Tepki';

        left.append(avatar, name);
        row.append(left, value);
        return row;
    };

    const patchDetails = (modal, payload) => {
        const list = modal?.querySelector('[data-post-card-reaction-details-list]');
        if (!list) return;

        const total = Math.max(0, Number(payload?.total) || 0);
        const items = Array.isArray(payload?.items) ? payload.items : [];
        const anonymousCount = Math.max(total - items.length, 0);

        if (anonymousCount <= 0) return;

        const existingGuest = list.querySelector('.post-card__reaction-detail-row--guest');
        if (existingGuest) existingGuest.remove();

        const empty = list.querySelector('.post-card__reaction-details-empty');
        if (empty) empty.remove();

        list.appendChild(guestDetailRow(anonymousCount));
    };

    const fetchPayload = async (modal) => {
        if (!modal) return null;
        if (modal.__ografiReactionStatsPayload) return modal.__ografiReactionStatsPayload;
        if (modal.__ografiPresentationPayloadPromise) return modal.__ografiPresentationPayloadPromise;

        const card = modal.closest('[data-post-card-shell]');
        const endpoint = endpointForCard(card);
        if (!endpoint) return null;

        modal.__ografiPresentationPayloadPromise = fetch(endpoint, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
        })
            .then((response) => {
                if (!response.ok) throw new Error('reaction detail request failed');
                return response.json();
            })
            .then((payload) => {
                modal.__ografiReactionStatsPayload = payload;
                return payload;
            })
            .catch(() => null)
            .finally(() => {
                delete modal.__ografiPresentationPayloadPromise;
            });

        return modal.__ografiPresentationPayloadPromise;
    };

    const hydrate = async (modal) => {
        if (!modal) return;
        ensureStack(modal);
        const payload = await fetchPayload(modal);
        if (!payload) {
            renderSummary(modal, {
                total: Number(modal.dataset.postCardStatsReactionsCount) || 0,
                preview: [],
                items: [],
            });
            return;
        }

        renderSummary(modal, payload);
        window.setTimeout(() => patchDetails(modal, payload), 0);
    };

    const initModal = (modal) => {
        if (!modal) return;
        ensureStack(modal);
    };

    const initAll = (root = document) => {
        root.querySelectorAll?.('[data-post-card-stats-modal]').forEach(initModal);
    };

    initAll();
    document.addEventListener('ografi:feed-appended', () => initAll());

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest?.('[data-post-card-stats-trigger]');
        if (trigger) {
            const card = trigger.closest('[data-post-card-shell]');
            const modal = card?.querySelector('[data-post-card-stats-modal]');
            if (modal) window.setTimeout(() => hydrate(modal), 0);
            return;
        }

        const reaction = event.target.closest?.('.post-card__stats-item--reactions');
        if (reaction) {
            const modal = reaction.closest('[data-post-card-stats-modal]');
            if (modal) {
                window.setTimeout(async () => {
                    const payload = await fetchPayload(modal);
                    if (payload) {
                        renderSummary(modal, payload);
                        patchDetails(modal, payload);
                    }
                }, 0);
            }
        }
    });
})();
</script>
HTML;

            if (stripos($html, '</body>') !== false) {
                $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
            } else {
                $html .= $assets;
            }
        }

        $response->setContent($html);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
