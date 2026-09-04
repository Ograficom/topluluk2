<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnifiedStatsModalMiddleware
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
        if (! is_string($html) || $html === '') {
            return $response;
        }

        $hasFeedStats = str_contains($html, 'data-post-card-stats-modal');
        $hasShowStats = str_contains($html, 'data-show-stats-modal');

        if (! $hasFeedStats && ! $hasShowStats) {
            return $response;
        }

        if ($hasShowStats) {
            $html = str_replace(
                [
                    '<span>akıştaki izlenimler</span>',
                    '<span>paylaşımlar</span>',
                    '<span>gönderilere verilen tepkiler</span>',
                    '<span>yorumlar</span>',
                    '<span>yer işaretleri</span>',
                    ' etkileşim</strong>',
                ],
                [
                    '<span>Akışlardan Gösterim</span>',
                    '<span>Görüntülenen</span>',
                    '<span>Tepkiler</span>',
                    '<span>Yorumlar</span>',
                    '<span>Kaydedildi</span>',
                    ' Etkileşim</strong>',
                ],
                $html
            );
        }

        if (! str_contains($html, 'data-og-unified-stats-modal')) {
            $assets = <<<'HTML'
<style data-og-unified-stats-modal>
/* Feed ve post-show istatistik pencerelerini tek bir görsel düzende tut. */
.post-show-shell .ps-show-stats-modal:not([hidden]) {
    position: fixed !important;
    inset: 0 !important;
    z-index: 999999999 !important;
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

.post-show-shell .ps-show-stats-backdrop {
    position: absolute !important;
    inset: 0 !important;
    background: rgba(0, 0, 0, .82) !important;
    -webkit-backdrop-filter: blur(14px) saturate(140%) !important;
    backdrop-filter: blur(14px) saturate(140%) !important;
}

.post-show-shell .ps-show-stats-panel {
    position: relative !important;
    inset: auto !important;
    z-index: 1 !important;
    width: min(520px, calc(100vw - 24px)) !important;
    min-height: 236px !important;
    max-height: calc(100dvh - 24px) !important;
    margin: 0 !important;
    padding: 20px 24px 26px !important;
    overflow-y: auto !important;
    border: 0 !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    color: #111111 !important;
    box-shadow: none !important;
    transform: none !important;
}

.post-show-shell .ps-show-stats-head {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    margin: 0 0 26px !important;
}

.post-show-shell .ps-show-stats-title {
    margin: 0 !important;
    color: #111111 !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
}

.post-show-shell .ps-show-stats-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    column-gap: 34px !important;
    row-gap: 46px !important;
}

.post-show-shell .ps-show-stats-item {
    min-width: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
}

.post-show-shell .ps-show-stats-item strong {
    display: block !important;
    margin: 0 0 3px !important;
    color: #111111 !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
}

.post-show-shell .ps-show-stats-item span {
    display: block !important;
    color: #666666 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.25 !important;
}

/* X butonu: normalde sade, hover/focus/tıklamada gri yüzey. */
.post-card__stats-close,
.post-show-shell .ps-show-stats-close {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #111111 !important;
    box-shadow: none !important;
    transition: background-color .15s ease, color .15s ease !important;
}

.post-card__stats-close:hover,
.post-card__stats-close:focus-visible,
.post-show-shell .ps-show-stats-close:hover,
.post-show-shell .ps-show-stats-close:focus-visible {
    background: #e4e4e4 !important;
    color: #111111 !important;
    outline: none !important;
}

.post-card__stats-close:active,
.post-show-shell .ps-show-stats-close:active {
    background: #d4d4d4 !important;
    color: #111111 !important;
    transform: scale(.96) !important;
}

.post-card__stats-close iconify-icon,
.post-show-shell .ps-show-stats-close svg {
    pointer-events: none !important;
}

/* Tepkiler: ana sayfa + post-show ayni renderer. */
.og-reaction-stats-tile {
    cursor: pointer !important;
    outline: none !important;
}

.og-reaction-summary-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 6px !important;
    width: 100% !important;
    min-width: 0 !important;
    overflow: visible !important;
}

.post-card__stats-item .og-reaction-summary-label,
.post-show-shell .ps-show-stats-item .og-reaction-summary-label {
    display: inline-block !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    color: #666666 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.25 !important;
    white-space: nowrap !important;
}

.og-reaction-avatar-stack {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex: 0 0 auto !important;
    min-width: 0 !important;
    padding-left: 4px !important;
    overflow: visible !important;
}

.og-reaction-avatar,
.og-reaction-avatar-fallback,
.og-reaction-avatar-guest,
.og-reaction-avatar-more {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 22px !important;
    height: 22px !important;
    min-width: 22px !important;
    margin-left: -8px !important;
    border: 1.5px solid #ffffff !important;
    border-radius: 9999px !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    background: #eef2f7 !important;
    color: #64748b !important;
    object-fit: cover !important;
    box-shadow: none !important;
    font-size: 8px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
}

.og-reaction-avatar-stack > :first-child {
    margin-left: 0 !important;
}

.og-reaction-avatar-guest svg {
    width: 12px !important;
    height: 12px !important;
}

.og-reaction-avatar-more {
    z-index: 20 !important;
    overflow: visible !important;
    background: #f3f4f6 !important;
    color: #111827 !important;
    white-space: nowrap !important;
}

.og-reaction-details {
    display: none !important;
    min-width: 0 !important;
}

.og-reaction-details.is-open {
    display: block !important;
}

.og-reaction-details-head {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin: -2px 0 10px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid #eceef1 !important;
}

.og-reaction-details-title {
    color: #111111 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
}

.og-reaction-details-back {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 30px !important;
    padding: 0 10px !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: #f3f4f6 !important;
    color: #111827 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    box-shadow: none !important;
    cursor: pointer !important;
}

.og-reaction-details-list {
    display: flex !important;
    flex-direction: column !important;
    max-height: min(360px, 50vh) !important;
    overflow-y: auto !important;
}

.og-reaction-detail-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 14px !important;
    min-height: 54px !important;
    padding: 8px 2px !important;
    border-bottom: 1px solid #f0f1f3 !important;
    color: inherit !important;
    text-decoration: none !important;
}

.og-reaction-detail-row:last-child {
    border-bottom: 0 !important;
}

.og-reaction-detail-person {
    display: flex !important;
    align-items: center !important;
    min-width: 0 !important;
    gap: 10px !important;
}

.og-reaction-detail-avatar,
.og-reaction-detail-avatar-fallback,
.og-reaction-detail-avatar-guest {
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
    object-fit: cover !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    box-shadow: none !important;
}

.og-reaction-detail-name {
    overflow: hidden !important;
    color: #111827 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.og-reaction-detail-value {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex: 0 0 auto !important;
    min-width: 38px !important;
    color: #111827 !important;
    font-size: 22px !important;
    line-height: 1 !important;
}

.og-reaction-detail-value img {
    display: block !important;
    width: 30px !important;
    height: 30px !important;
    border: 0 !important;
    border-radius: 8px !important;
    object-fit: contain !important;
    background: transparent !important;
    box-shadow: none !important;
}

.og-reaction-details-empty {
    padding: 18px 0 !important;
    color: #6b7280 !important;
    font-size: 13px !important;
    text-align: center !important;
}

html.dark .post-show-shell .ps-show-stats-panel,
body.dark .post-show-shell .ps-show-stats-panel,
.dark .post-show-shell .ps-show-stats-panel,
[data-theme="dark"] .post-show-shell .ps-show-stats-panel {
    background: #17181b !important;
    color: #f4f4f5 !important;
}

html.dark .post-show-shell .ps-show-stats-title,
html.dark .post-show-shell .ps-show-stats-item strong,
body.dark .post-show-shell .ps-show-stats-title,
body.dark .post-show-shell .ps-show-stats-item strong,
.dark .post-show-shell .ps-show-stats-title,
.dark .post-show-shell .ps-show-stats-item strong,
[data-theme="dark"] .post-show-shell .ps-show-stats-title,
[data-theme="dark"] .post-show-shell .ps-show-stats-item strong {
    color: #f4f4f5 !important;
}

html.dark .post-show-shell .ps-show-stats-item span,
body.dark .post-show-shell .ps-show-stats-item span,
.dark .post-show-shell .ps-show-stats-item span,
[data-theme="dark"] .post-show-shell .ps-show-stats-item span {
    color: #a1a1aa !important;
}

html.dark .post-card__stats-close,
html.dark .post-show-shell .ps-show-stats-close,
.dark .post-card__stats-close,
.dark .post-show-shell .ps-show-stats-close {
    color: #f4f4f5 !important;
}

html.dark .post-card__stats-close:hover,
html.dark .post-card__stats-close:focus-visible,
html.dark .post-show-shell .ps-show-stats-close:hover,
html.dark .post-show-shell .ps-show-stats-close:focus-visible,
.dark .post-card__stats-close:hover,
.dark .post-card__stats-close:focus-visible,
.dark .post-show-shell .ps-show-stats-close:hover,
.dark .post-show-shell .ps-show-stats-close:focus-visible {
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

html.dark .og-reaction-avatar,
html.dark .og-reaction-avatar-fallback,
html.dark .og-reaction-avatar-guest,
html.dark .og-reaction-avatar-more,
.dark .og-reaction-avatar,
.dark .og-reaction-avatar-fallback,
.dark .og-reaction-avatar-guest,
.dark .og-reaction-avatar-more {
    border-color: #17181b !important;
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

html.dark .og-reaction-details-title,
html.dark .og-reaction-detail-name,
html.dark .og-reaction-detail-value,
.dark .og-reaction-details-title,
.dark .og-reaction-detail-name,
.dark .og-reaction-detail-value {
    color: #f4f4f5 !important;
}

html.dark .og-reaction-details-head,
.dark .og-reaction-details-head,
html.dark .og-reaction-detail-row,
.dark .og-reaction-detail-row {
    border-color: #30343a !important;
}

html.dark .og-reaction-details-back,
.dark .og-reaction-details-back {
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

@media (max-width: 520px) {
    .post-show-shell .ps-show-stats-modal:not([hidden]) {
        padding: 10px !important;
    }

    .post-show-shell .ps-show-stats-panel {
        width: min(356px, calc(100vw - 20px)) !important;
        min-height: 240px !important;
        padding: 18px 20px 24px !important;
        border-radius: 12px !important;
    }

    .post-show-shell .ps-show-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        column-gap: 24px !important;
        row-gap: 28px !important;
    }

    .og-reaction-avatar,
    .og-reaction-avatar-fallback,
    .og-reaction-avatar-guest,
    .og-reaction-avatar-more {
        width: 21px !important;
        height: 21px !important;
        min-width: 21px !important;
        margin-left: -8px !important;
    }
}

@supports not (height: 100dvh) {
    .post-show-shell .ps-show-stats-modal:not([hidden]) {
        height: 100vh !important;
        min-height: 100vh !important;
    }
}
</style>
<script data-og-unified-stats-modal>
(() => {
    const MAX_VISIBLE = 5;
    const MAX_OVERFLOW = 99;

    const escapeText = (value) => String(value ?? '');

    const guestSvg = () => {
        const wrap = document.createElement('span');
        wrap.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>';
        return wrap.firstElementChild;
    };

    const feedEndpoint = (modal) => {
        const card = modal?.closest?.('[data-post-card-shell]');
        const id = String(card?.id || '');
        if (!id.startsWith('post-card-shell-')) return null;
        const slug = id.slice('post-card-shell-'.length);
        return slug ? `/posts/${encodeURIComponent(slug)}/reactions?details=1` : null;
    };

    const showEndpoint = () => {
        const path = window.location.pathname.replace(/\/+$/, '');
        const parts = path.split('/').filter(Boolean);
        const slug = parts.length ? decodeURIComponent(parts[parts.length - 1]) : '';
        if (!slug) return null;
        return `/posts/${encodeURIComponent(slug)}/reactions?details=1`;
    };

    const isFeedModal = (modal) => modal?.matches?.('[data-post-card-stats-modal]');
    const isShowModal = (modal) => modal?.matches?.('[data-show-stats-modal]');

    const panelFor = (modal) => {
        if (isFeedModal(modal)) return modal.querySelector('.post-card__stats-panel');
        if (isShowModal(modal)) return modal.querySelector('.ps-show-stats-panel');
        return null;
    };

    const gridFor = (modal) => {
        if (isFeedModal(modal)) return modal.querySelector('.post-card__stats-grid');
        if (isShowModal(modal)) return modal.querySelector('.ps-show-stats-grid');
        return null;
    };

    const reactionTileFor = (modal) => {
        if (isFeedModal(modal)) {
            return modal.querySelector('.post-card__stats-grid > .post-card__stats-item:nth-child(3)');
        }
        if (isShowModal(modal)) {
            return modal.querySelector('.ps-show-stats-grid > .ps-show-stats-item:nth-child(3)');
        }
        return null;
    };

    const endpointFor = (modal) => isFeedModal(modal) ? feedEndpoint(modal) : showEndpoint();

    const isOpen = (modal) => {
        if (isFeedModal(modal)) return modal.hasAttribute('open') || modal.open === true;
        if (isShowModal(modal)) return !modal.hasAttribute('hidden') && modal.getAttribute('aria-hidden') !== 'true';
        return false;
    };

    const avatarNode = (person) => {
        if (person?.avatar) {
            const img = document.createElement('img');
            img.className = 'og-reaction-avatar';
            img.src = person.avatar;
            img.alt = person?.name ? `${person.name} profil resmi` : 'Profil resmi';
            img.loading = 'lazy';
            img.decoding = 'async';
            img.title = person?.name || 'Kullanıcı';
            return img;
        }

        const fallback = document.createElement('span');
        fallback.className = 'og-reaction-avatar-fallback';
        fallback.textContent = person?.initials || '?';
        fallback.title = person?.name || 'Kullanıcı';
        return fallback;
    };

    const guestAvatarNode = () => {
        const guest = document.createElement('span');
        guest.className = 'og-reaction-avatar-guest';
        guest.title = 'Misafir tepki';
        guest.appendChild(guestSvg());
        return guest;
    };

    const normalizeSummary = (modal) => {
        const tile = reactionTileFor(modal);
        if (!tile) return null;

        tile.classList.add('og-reaction-stats-tile');
        tile.setAttribute('role', 'button');
        tile.setAttribute('tabindex', '0');
        tile.setAttribute('aria-label', 'Tepkileri göster');

        const count = tile.querySelector('strong');
        let label = tile.querySelector('.og-reaction-summary-label') || tile.querySelector('span');
        if (!label) return null;

        label.textContent = 'Tepkiler';
        label.classList.add('og-reaction-summary-label');

        let row = tile.querySelector('.og-reaction-summary-row') || tile.querySelector('.post-card__reaction-summary-row');
        if (!row) {
            row = document.createElement('div');
            row.className = 'og-reaction-summary-row';
            label.parentNode?.insertBefore(row, label);
            row.appendChild(label);
        } else {
            row.classList.add('og-reaction-summary-row');
            if (label.parentElement !== row) row.prepend(label);
        }

        let stack = tile.querySelector('.og-reaction-avatar-stack') || tile.querySelector('[data-post-card-reaction-avatar-stack]');
        if (!stack) {
            stack = document.createElement('div');
            row.appendChild(stack);
        } else if (stack.parentElement !== row) {
            row.appendChild(stack);
        }
        stack.classList.add('og-reaction-avatar-stack');
        stack.setAttribute('data-og-reaction-avatar-stack', '');

        return { tile, count, label, row, stack };
    };

    const fetchPayload = async (modal) => {
        if (!modal) return null;
        if (modal.__ogUnifiedReactionPayload) return modal.__ogUnifiedReactionPayload;
        if (modal.__ogUnifiedReactionPromise) return modal.__ogUnifiedReactionPromise;

        const endpoint = endpointFor(modal);
        if (!endpoint) return null;

        modal.__ogUnifiedReactionPromise = fetch(endpoint, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
        })
            .then((response) => {
                if (!response.ok) throw new Error(`reaction request failed: ${response.status}`);
                return response.json();
            })
            .then((payload) => {
                modal.__ogUnifiedReactionPayload = payload;
                return payload;
            })
            .catch(() => null)
            .finally(() => {
                delete modal.__ogUnifiedReactionPromise;
            });

        return modal.__ogUnifiedReactionPromise;
    };

    const renderSummary = (modal, payload) => {
        const refs = normalizeSummary(modal);
        if (!refs) return;

        const fallbackTotal = Number(refs.count?.textContent?.replace(/[^0-9]/g, '') || 0);
        const total = Math.max(0, Number(payload?.total) || fallbackTotal || 0);
        const preview = Array.isArray(payload?.preview) ? payload.preview.slice(0, MAX_VISIBLE) : [];
        const visibleTarget = Math.min(total, MAX_VISIBLE);
        const missing = Math.max(visibleTarget - preview.length, 0);

        if (refs.count) refs.count.textContent = new Intl.NumberFormat('tr-TR').format(total);

        refs.stack.replaceChildren();
        preview.forEach((person) => refs.stack.appendChild(avatarNode(person)));
        for (let i = 0; i < missing; i += 1) refs.stack.appendChild(guestAvatarNode());

        const overflow = Math.min(Math.max(total - MAX_VISIBLE, 0), MAX_OVERFLOW);
        if (overflow > 0) {
            const more = document.createElement('span');
            more.className = 'og-reaction-avatar-more';
            more.textContent = `+${overflow}`;
            more.title = `${overflow} tepki daha`;
            refs.stack.appendChild(more);
        }
    };

    const createPersonAvatar = (person) => {
        if (person?.avatar) {
            const img = document.createElement('img');
            img.className = 'og-reaction-detail-avatar';
            img.src = person.avatar;
            img.alt = person?.name ? `${person.name} profil resmi` : 'Profil resmi';
            img.loading = 'lazy';
            return img;
        }
        const fallback = document.createElement('span');
        fallback.className = 'og-reaction-detail-avatar-fallback';
        fallback.textContent = person?.initials || '?';
        return fallback;
    };

    const createReactionValue = (reaction) => {
        const value = document.createElement('span');
        value.className = 'og-reaction-detail-value';
        if (reaction?.image_url) {
            const img = document.createElement('img');
            img.src = reaction.image_url;
            img.alt = reaction?.label || 'Tepki';
            value.appendChild(img);
        } else if (reaction?.emoji) {
            value.textContent = reaction.emoji;
        } else {
            value.textContent = reaction?.label || 'Tepki';
            value.style.fontSize = '13px';
            value.style.fontWeight = '600';
        }
        return value;
    };

    const createRegisteredRow = (person) => {
        const row = person?.profile_url ? document.createElement('a') : document.createElement('div');
        row.className = 'og-reaction-detail-row';
        if (person?.profile_url) row.href = person.profile_url;

        const left = document.createElement('span');
        left.className = 'og-reaction-detail-person';
        left.appendChild(createPersonAvatar(person));

        const name = document.createElement('span');
        name.className = 'og-reaction-detail-name';
        name.textContent = escapeText(person?.name || 'Ografi kullanıcısı');
        left.appendChild(name);

        row.append(left, createReactionValue(person?.reaction || {}));
        return row;
    };

    const createGuestRow = (count) => {
        const row = document.createElement('div');
        row.className = 'og-reaction-detail-row';

        const left = document.createElement('span');
        left.className = 'og-reaction-detail-person';

        const avatar = document.createElement('span');
        avatar.className = 'og-reaction-detail-avatar-guest';
        avatar.appendChild(guestSvg());

        const name = document.createElement('span');
        name.className = 'og-reaction-detail-name';
        name.textContent = count > 1 ? `Misafir kullanıcılar (${count})` : 'Misafir kullanıcı';

        left.append(avatar, name);

        const value = document.createElement('span');
        value.className = 'og-reaction-detail-value';
        value.style.fontSize = '13px';
        value.style.fontWeight = '600';
        value.textContent = 'Tepki';

        row.append(left, value);
        return row;
    };

    const ensureDetails = (modal) => {
        const panel = panelFor(modal);
        const grid = gridFor(modal);
        if (!panel || !grid) return null;

        let details = panel.querySelector('.og-reaction-details');
        if (!details) {
            details = document.createElement('div');
            details.className = 'og-reaction-details';

            const head = document.createElement('div');
            head.className = 'og-reaction-details-head';

            const title = document.createElement('strong');
            title.className = 'og-reaction-details-title';
            title.textContent = 'Tepkiler';

            const back = document.createElement('button');
            back.type = 'button';
            back.className = 'og-reaction-details-back';
            back.textContent = 'Geri';
            back.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                details.classList.remove('is-open');
                grid.style.removeProperty('display');
            });

            const list = document.createElement('div');
            list.className = 'og-reaction-details-list';

            head.append(title, back);
            details.append(head, list);
            grid.insertAdjacentElement('afterend', details);
        }

        return { details, list: details.querySelector('.og-reaction-details-list'), grid };
    };

    const renderDetails = (modal, payload) => {
        const refs = ensureDetails(modal);
        if (!refs?.list) return;

        const items = Array.isArray(payload?.items) ? payload.items : [];
        const total = Math.max(0, Number(payload?.total) || 0);
        const anonymousCount = Math.max(Number(payload?.anonymous_count) || (total - items.length), 0);

        refs.list.replaceChildren();
        items.forEach((person) => refs.list.appendChild(createRegisteredRow(person)));
        if (anonymousCount > 0) refs.list.appendChild(createGuestRow(anonymousCount));

        if (items.length === 0 && anonymousCount === 0) {
            const empty = document.createElement('div');
            empty.className = 'og-reaction-details-empty';
            empty.textContent = 'Henüz tepki yok.';
            refs.list.appendChild(empty);
        }
    };

    const hydrate = async (modal) => {
        if (!modal) return null;
        normalizeSummary(modal);
        const payload = await fetchPayload(modal);
        if (payload) renderSummary(modal, payload);
        return payload;
    };

    const openDetails = async (modal) => {
        if (!modal) return;
        const payload = await hydrate(modal);
        if (!payload) return;
        renderDetails(modal, payload);
        const refs = ensureDetails(modal);
        if (!refs) return;
        refs.grid.style.setProperty('display', 'none', 'important');
        refs.details.classList.add('is-open');
    };

    const initModal = (modal) => {
        if (!modal) return;
        normalizeSummary(modal);
        if (isOpen(modal)) window.setTimeout(() => hydrate(modal), 0);
    };

    const initAll = (root = document) => {
        root.querySelectorAll?.('[data-post-card-stats-modal], [data-show-stats-modal]').forEach(initModal);
    };

    initAll();
    document.addEventListener('ografi:feed-appended', () => initAll());

    const observer = new MutationObserver((records) => {
        for (const record of records) {
            const modal = record.target;
            if (!(modal instanceof Element)) continue;
            if (!modal.matches('[data-post-card-stats-modal], [data-show-stats-modal]')) continue;
            if (isOpen(modal)) window.setTimeout(() => hydrate(modal), 0);
        }
    });

    document.querySelectorAll('[data-post-card-stats-modal], [data-show-stats-modal]').forEach((modal) => {
        observer.observe(modal, { attributes: true, attributeFilter: ['open', 'hidden', 'aria-hidden'] });
    });

    document.addEventListener('click', (event) => {
        const tile = event.target.closest?.('.og-reaction-stats-tile');
        if (tile) {
            event.preventDefault();
            event.stopPropagation();
            const modal = tile.closest('[data-post-card-stats-modal], [data-show-stats-modal]');
            openDetails(modal);
            return;
        }

        const feedTrigger = event.target.closest?.('[data-post-card-stats-trigger]');
        if (feedTrigger) {
            const card = feedTrigger.closest('[data-post-card-shell]');
            const modal = card?.querySelector('[data-post-card-stats-modal]');
            if (modal) window.setTimeout(() => hydrate(modal), 0);
            return;
        }

        const showTrigger = event.target.closest?.('[data-show-stats-trigger]');
        if (showTrigger) {
            window.setTimeout(() => {
                const modal = document.querySelector('[data-show-stats-modal]:not([hidden])');
                if (modal) hydrate(modal);
            }, 0);
        }
    }, true);

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const tile = event.target.closest?.('.og-reaction-stats-tile');
        if (!tile) return;
        event.preventDefault();
        const modal = tile.closest('[data-post-card-stats-modal], [data-show-stats-modal]');
        openDetails(modal);
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
