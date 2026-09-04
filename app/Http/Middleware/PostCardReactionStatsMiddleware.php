<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PostCardReactionStatsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isReactionDetailsRequest($request)) {
            return $this->reactionDetails($request);
        }

        $response = $next($request);

        if (! $this->shouldInject($response)) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '' || str_contains($html, 'data-og-post-reaction-stats-assets')) {
            return $response;
        }

        $endpointTemplate = route('blog.post.reactions', ['post' => '__OGRAFI_SLUG__']);
        $assets = $this->assets($endpointTemplate);

        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        } else {
            $html .= $assets;
        }

        $response->setContent($html);
        $response->headers->remove('Content-Length');

        return $response;
    }

    private function isReactionDetailsRequest(Request $request): bool
    {
        if (! $request->isMethod('GET') || ! $request->boolean('details')) {
            return false;
        }

        return preg_match('#(?:^|/)posts/[^/]+/reactions$#u', trim($request->path(), '/')) === 1;
    }

    private function reactionDetails(Request $request): JsonResponse
    {
        $path = trim($request->path(), '/');
        preg_match('#(?:^|/)posts/([^/]+)/reactions$#u', $path, $matches);
        $slug = rawurldecode((string) ($matches[1] ?? ''));

        abort_if($slug === '', 404);

        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $reactions = $post->reactions()
            ->with([
                'user:id,name,username,profile_photo_path',
                'type:id,label,short_code,emoji,gif_url',
            ])
            ->whereNotNull('user_id')
            ->latest('id')
            ->get()
            ->unique('user_id')
            ->values();

        $items = $reactions
            ->map(function ($reaction): ?array {
                $user = $reaction->user;
                if (! $user) {
                    return null;
                }

                $type = $reaction->type;
                $name = trim((string) $user->name) ?: 'Ografi kullanıcısı';
                $username = trim((string) $user->username);
                $avatar = trim((string) $user->profile_photo_url);
                $initials = collect(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY))
                    ->take(2)
                    ->map(fn ($part) => Str::upper(Str::substr((string) $part, 0, 1)))
                    ->implode('');

                $gif = trim((string) ($type?->gif_url ?? ''));
                $reactionImage = null;
                if ($gif !== '') {
                    if (Str::startsWith($gif, ['http://', 'https://', '//', 'data:', '/'])) {
                        $reactionImage = Str::startsWith($gif, '//') ? 'https:' . $gif : $gif;
                    } elseif (Str::startsWith($gif, 'storage/')) {
                        $reactionImage = asset($gif);
                    } else {
                        $reactionImage = asset('storage/' . ltrim($gif, '/'));
                    }
                }

                return [
                    'id' => (int) $user->id,
                    'name' => $name,
                    'username' => $username,
                    'profile_url' => $username !== '' ? route('users.show', ['user' => $username]) : null,
                    'avatar' => $avatar !== '' ? $avatar : null,
                    'initials' => $initials !== '' ? $initials : Str::upper(Str::substr($name, 0, 1)),
                    'reaction' => [
                        'label' => trim((string) ($type?->label ?? 'Tepki')) ?: 'Tepki',
                        'emoji' => trim((string) ($type?->emoji ?? '')) ?: null,
                        'image_url' => $reactionImage,
                    ],
                ];
            })
            ->filter()
            ->values();

        $reactorCount = $items->count();
        $overflow = min(max($reactorCount - 5, 0), 99);

        return response()->json([
            'total' => (int) $post->reactions()->count(),
            'reactors_count' => $reactorCount,
            'overflow' => $overflow,
            'preview' => $items->take(5)->values()->all(),
            'items' => $items->all(),
        ]);
    }

    private function shouldInject(Response $response): bool
    {
        if (! method_exists($response, 'getContent')) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        $html = $response->getContent();

        return is_string($html)
            && str_contains($html, 'data-post-card-stats-modal')
            && str_contains($html, 'data-post-card-shell');
    }

    private function assets(string $endpointTemplate): string
    {
        $endpointJson = json_encode($endpointTemplate, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<'HTML'
<style data-og-post-reaction-stats-assets>
.post-card__stats-item--reactions {
    cursor: pointer !important;
    border-radius: 10px !important;
    outline: none !important;
}

.post-card__stats-item--reactions:hover,
.post-card__stats-item--reactions:focus-visible {
    background: #f7f8fa !important;
}

.post-card__reaction-summary-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
    min-width: 0 !important;
}

.post-card__reaction-summary-row > .post-card__reaction-summary-label {
    display: inline !important;
    flex: 0 1 auto !important;
    color: #666666 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.25 !important;
    white-space: nowrap !important;
}

.post-card__reaction-avatar-stack {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex: 0 0 auto !important;
    min-width: 0 !important;
    padding-left: 6px !important;
}

.post-card__reaction-avatar,
.post-card__reaction-avatar-fallback,
.post-card__reaction-avatar-more {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    margin-left: -7px !important;
    border: 2px solid #ffffff !important;
    border-radius: 9999px !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    background: #eef2f7 !important;
    color: #4b5563 !important;
    font-size: 9px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
    object-fit: cover !important;
    box-shadow: none !important;
}

.post-card__reaction-avatar-stack > :first-child {
    margin-left: 0 !important;
}

.post-card__reaction-avatar-more {
    z-index: 8 !important;
    overflow: visible !important;
    background: #f3f4f6 !important;
    color: #111827 !important;
    font-size: 9px !important;
    white-space: nowrap !important;
}

.post-card__reaction-details {
    display: none;
    min-width: 0;
}

.post-card__reaction-details.is-open {
    display: block !important;
}

.post-card__reaction-details-head {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin: -4px 0 12px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid #eceef1 !important;
}

.post-card__reaction-details-title {
    color: #111111 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
}

.post-card__reaction-details-back {
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
}

.post-card__reaction-details-list {
    display: flex !important;
    flex-direction: column !important;
    max-height: min(360px, 50vh) !important;
    overflow-y: auto !important;
}

.post-card__reaction-detail-row {
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

.post-card__reaction-detail-row:last-child {
    border-bottom: 0 !important;
}

.post-card__reaction-detail-person {
    display: flex !important;
    align-items: center !important;
    min-width: 0 !important;
    gap: 10px !important;
}

.post-card__reaction-detail-avatar,
.post-card__reaction-detail-avatar-fallback {
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

.post-card__reaction-detail-name {
    overflow: hidden !important;
    color: #111827 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.post-card__reaction-detail-value {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex: 0 0 auto !important;
    min-width: 38px !important;
    color: #111827 !important;
    font-size: 22px !important;
    line-height: 1 !important;
}

.post-card__reaction-detail-value img {
    display: block !important;
    width: 30px !important;
    height: 30px !important;
    border: 0 !important;
    border-radius: 8px !important;
    object-fit: contain !important;
    background: transparent !important;
    box-shadow: none !important;
}

.post-card__reaction-details-empty,
.post-card__reaction-details-loading {
    padding: 18px 0 !important;
    color: #6b7280 !important;
    font-size: 13px !important;
    text-align: center !important;
}

html.dark .post-card__stats-item--reactions:hover,
html.dark .post-card__stats-item--reactions:focus-visible,
.dark .post-card__stats-item--reactions:hover,
.dark .post-card__stats-item--reactions:focus-visible {
    background: #202226 !important;
}

html.dark .post-card__reaction-avatar,
html.dark .post-card__reaction-avatar-fallback,
html.dark .post-card__reaction-avatar-more,
.dark .post-card__reaction-avatar,
.dark .post-card__reaction-avatar-fallback,
.dark .post-card__reaction-avatar-more {
    border-color: #17181b !important;
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

html.dark .post-card__reaction-details-head,
.dark .post-card__reaction-details-head {
    border-bottom-color: #30343a !important;
}

html.dark .post-card__reaction-details-title,
html.dark .post-card__reaction-detail-name,
html.dark .post-card__reaction-detail-value,
.dark .post-card__reaction-details-title,
.dark .post-card__reaction-detail-name,
.dark .post-card__reaction-detail-value {
    color: #f4f4f5 !important;
}

html.dark .post-card__reaction-details-back,
.dark .post-card__reaction-details-back {
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

html.dark .post-card__reaction-detail-row,
.dark .post-card__reaction-detail-row {
    border-bottom-color: #2a2d32 !important;
}

@media (max-width: 520px) {
    .post-card__reaction-summary-row {
        gap: 6px !important;
    }

    .post-card__reaction-avatar,
    .post-card__reaction-avatar-fallback,
    .post-card__reaction-avatar-more {
        width: 24px !important;
        height: 24px !important;
        min-width: 24px !important;
        margin-left: -7px !important;
    }
}
</style>
<script data-og-post-reaction-stats-assets>
(() => {
    const endpointTemplate = __ENDPOINT_TEMPLATE__;

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const cardSlug = (card) => {
        const id = String(card?.id || '');
        return id.startsWith('post-card-shell-') ? id.slice('post-card-shell-'.length) : '';
    };

    const detailsEndpoint = (card) => {
        const slug = cardSlug(card);
        if (!slug) return null;

        const url = new URL(endpointTemplate.replace('__OGRAFI_SLUG__', encodeURIComponent(slug)), window.location.origin);
        url.searchParams.set('details', '1');
        return url.toString();
    };

    const reactionTile = (modal) => modal?.querySelector('.post-card__stats-grid > .post-card__stats-item:nth-child(3)') || null;

    const ensureTile = (modal) => {
        const tile = reactionTile(modal);
        if (!tile || tile.dataset.reactionStatsReady === '1') return tile;

        tile.dataset.reactionStatsReady = '1';
        tile.classList.add('post-card__stats-item--reactions');
        tile.setAttribute('role', 'button');
        tile.setAttribute('tabindex', '0');
        tile.setAttribute('aria-label', 'Tepkileri göster');

        const label = tile.querySelector('span');
        if (label) {
            label.textContent = 'Tepki';
            label.classList.add('post-card__reaction-summary-label');

            const row = document.createElement('div');
            row.className = 'post-card__reaction-summary-row';
            const stack = document.createElement('div');
            stack.className = 'post-card__reaction-avatar-stack';
            stack.setAttribute('data-post-card-reaction-avatar-stack', '');
            row.append(label, stack);
            tile.appendChild(row);
        }

        return tile;
    };

    const avatarNode = (person, detail = false) => {
        if (person?.avatar) {
            const img = document.createElement('img');
            img.src = person.avatar;
            img.alt = person.name ? `${person.name} profil resmi` : 'Profil resmi';
            img.loading = 'lazy';
            img.decoding = 'async';
            img.className = detail ? 'post-card__reaction-detail-avatar' : 'post-card__reaction-avatar';
            return img;
        }

        const fallback = document.createElement('span');
        fallback.className = detail ? 'post-card__reaction-detail-avatar-fallback' : 'post-card__reaction-avatar-fallback';
        fallback.textContent = person?.initials || '?';
        return fallback;
    };

    const renderPreview = (modal, payload) => {
        const tile = ensureTile(modal);
        if (!tile) return;

        const countNode = tile.querySelector('strong');
        if (countNode && Number.isFinite(Number(payload?.total))) {
            countNode.textContent = new Intl.NumberFormat('tr-TR').format(Number(payload.total));
        }

        const stack = tile.querySelector('[data-post-card-reaction-avatar-stack]');
        if (!stack) return;
        stack.replaceChildren();

        (Array.isArray(payload?.preview) ? payload.preview.slice(0, 5) : []).forEach((person) => {
            stack.appendChild(avatarNode(person));
        });

        const overflow = Math.min(Math.max(Number(payload?.overflow) || 0, 0), 99);
        if (overflow > 0) {
            const more = document.createElement('span');
            more.className = 'post-card__reaction-avatar-more';
            more.textContent = `+${overflow}`;
            stack.appendChild(more);
        }
    };

    const reactionValue = (person) => {
        const wrap = document.createElement('span');
        wrap.className = 'post-card__reaction-detail-value';
        wrap.title = person?.reaction?.label || 'Tepki';

        if (person?.reaction?.image_url) {
            const img = document.createElement('img');
            img.src = person.reaction.image_url;
            img.alt = person?.reaction?.label || 'Tepki';
            img.loading = 'lazy';
            img.decoding = 'async';
            wrap.appendChild(img);
        } else {
            wrap.textContent = person?.reaction?.emoji || person?.reaction?.label || 'Tepki';
        }

        return wrap;
    };

    const ensureDetailsPanel = (modal) => {
        let detail = modal?.querySelector('[data-post-card-reaction-details]');
        if (detail) return detail;

        const panel = modal?.querySelector('.post-card__stats-panel');
        const grid = modal?.querySelector('.post-card__stats-grid');
        if (!panel || !grid) return null;

        detail = document.createElement('div');
        detail.className = 'post-card__reaction-details';
        detail.setAttribute('data-post-card-reaction-details', '');
        detail.innerHTML = `
            <div class="post-card__reaction-details-head">
                <strong class="post-card__reaction-details-title">Tepkiler</strong>
                <button type="button" class="post-card__reaction-details-back" data-post-card-reaction-details-back>Geri</button>
            </div>
            <div class="post-card__reaction-details-list" data-post-card-reaction-details-list></div>
        `;
        grid.insertAdjacentElement('afterend', detail);
        return detail;
    };

    const renderDetails = (modal, payload) => {
        const detail = ensureDetailsPanel(modal);
        const list = detail?.querySelector('[data-post-card-reaction-details-list]');
        if (!detail || !list) return;

        list.replaceChildren();
        const items = Array.isArray(payload?.items) ? payload.items : [];

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'post-card__reaction-details-empty';
            empty.textContent = 'Henüz tepki veren kullanıcı yok.';
            list.appendChild(empty);
            return;
        }

        items.forEach((person) => {
            const row = person?.profile_url ? document.createElement('a') : document.createElement('div');
            row.className = 'post-card__reaction-detail-row';
            if (person?.profile_url) row.href = person.profile_url;

            const left = document.createElement('span');
            left.className = 'post-card__reaction-detail-person';
            left.appendChild(avatarNode(person, true));

            const name = document.createElement('span');
            name.className = 'post-card__reaction-detail-name';
            name.textContent = person?.name || 'Ografi kullanıcısı';
            left.appendChild(name);

            row.append(left, reactionValue(person));
            list.appendChild(row);
        });
    };

    const showDetails = (modal) => {
        if (!modal) return;
        const grid = modal.querySelector('.post-card__stats-grid');
        const detail = ensureDetailsPanel(modal);
        if (!grid || !detail) return;

        grid.style.display = 'none';
        detail.classList.add('is-open');
    };

    const hideDetails = (modal) => {
        if (!modal) return;
        const grid = modal.querySelector('.post-card__stats-grid');
        const detail = modal.querySelector('[data-post-card-reaction-details]');
        if (grid) grid.style.removeProperty('display');
        detail?.classList.remove('is-open');
    };

    const hydrate = async (modal, { openDetails = false } = {}) => {
        if (!modal || modal.dataset.reactionStatsLoading === '1') {
            if (openDetails) showDetails(modal);
            return;
        }

        const card = modal.closest('[data-post-card-shell]');
        const endpoint = detailsEndpoint(card);
        if (!endpoint) return;

        modal.dataset.reactionStatsLoading = '1';
        const detail = openDetails ? ensureDetailsPanel(modal) : null;
        const list = detail?.querySelector('[data-post-card-reaction-details-list]');
        if (openDetails && list) {
            list.innerHTML = '<div class="post-card__reaction-details-loading">Tepkiler yükleniyor...</div>';
            showDetails(modal);
        }

        try {
            const response = await fetch(endpoint, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('reaction details request failed');
            const payload = await response.json();
            modal.__ografiReactionStatsPayload = payload;
            renderPreview(modal, payload);
            renderDetails(modal, payload);
        } catch (error) {
            if (openDetails && list) {
                list.innerHTML = '<div class="post-card__reaction-details-empty">Tepkiler yüklenemedi.</div>';
            }
        } finally {
            delete modal.dataset.reactionStatsLoading;
        }
    };

    const initModal = (modal) => {
        if (!modal || modal.dataset.ogReactionStatsInit === '1') return;
        modal.dataset.ogReactionStatsInit = '1';
        ensureTile(modal);
        modal.addEventListener('close', () => hideDetails(modal));
    };

    const initAll = (root = document) => {
        root.querySelectorAll?.('[data-post-card-stats-modal]').forEach(initModal);
    };

    initAll();
    document.addEventListener('ografi:feed-appended', () => initAll());

    document.addEventListener('click', (event) => {
        const statsTrigger = event.target.closest('[data-post-card-stats-trigger]');
        if (statsTrigger) {
            const card = statsTrigger.closest('[data-post-card-shell]');
            const modal = card?.querySelector('[data-post-card-stats-modal]');
            if (modal) {
                initModal(modal);
                window.setTimeout(() => hydrate(modal), 0);
            }
            return;
        }

        const reactionItem = event.target.closest('.post-card__stats-item--reactions');
        if (reactionItem) {
            event.preventDefault();
            const modal = reactionItem.closest('[data-post-card-stats-modal]');
            if (!modal) return;
            if (modal.__ografiReactionStatsPayload) {
                renderDetails(modal, modal.__ografiReactionStatsPayload);
                showDetails(modal);
            } else {
                hydrate(modal, { openDetails: true });
            }
            return;
        }

        const back = event.target.closest('[data-post-card-reaction-details-back]');
        if (back) {
            event.preventDefault();
            hideDetails(back.closest('[data-post-card-stats-modal]'));
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!['Enter', ' '].includes(event.key)) return;
        const reactionItem = event.target.closest?.('.post-card__stats-item--reactions');
        if (!reactionItem) return;
        event.preventDefault();
        reactionItem.click();
    });
})();
</script>
HTML;

        return str_replace('__ENDPOINT_TEMPLATE__', $endpointJson ?: '""', $assets);
    }
}
