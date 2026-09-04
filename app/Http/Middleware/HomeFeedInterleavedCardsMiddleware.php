<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeFeedInterleavedCardsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->routeIs('home')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html)
            || $html === ''
            || ! str_contains($html, 'home-feed-shell')
            || ! str_contains($html, 'ografi-filterable-post')
            || str_contains($html, 'data-og-feed-insert-assets')
        ) {
            return $response;
        }

        $viewerId = $request->user()?->id;

        $topPosts = Post::query()
            ->published()
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get([
                'id',
                'title',
                'slug',
                'featured_image',
                'views_count',
                'published_at',
                'created_at',
            ]);

        $feedCategories = Category::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->take(5)
            ->get([
                'id',
                'name',
                'slug',
                'profile_image',
            ]);

        $feedSuggestedUsers = User::query()
            ->when($viewerId, fn ($query) => $query->whereKeyNot($viewerId))
            ->when($viewerId, function ($query) use ($viewerId) {
                $query->withExists([
                    'followers as is_followed_by_viewer' => fn ($inner) => $inner->where('users.id', $viewerId),
                ]);
            })
            ->withCount('followers')
            ->orderByDesc('is_verified')
            ->orderByDesc('followers_count')
            ->orderByDesc('id')
            ->take(5)
            ->get([
                'id',
                'name',
                'username',
                'profile_photo_path',
                'is_verified',
                'verification_badge',
                'verification_badge_svg',
            ]);

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 25;
        $feedGlobalStart = (($page - 1) * $perPage) + 1;

        try {
            $assets = view('partials.feed.interleaved-cards', [
                'topPosts' => $topPosts,
                'feedCategories' => $feedCategories,
                'feedSuggestedUsers' => $feedSuggestedUsers,
                'feedGlobalStart' => $feedGlobalStart,
            ])->render();
        } catch (\Throwable $exception) {
            report($exception);

            return $response;
        }

        $assets .= $this->interactionStyles();

        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        $response->setContent($html);
        $response->headers->set('X-Ografi-Feed-Inserts', 'v2');

        return $response;
    }

    private function interactionStyles(): string
    {
        return <<<'HTML'
<style data-og-feed-insert-interactions>
    /*
     * Ara-kart başlıkları özellikle bu kuralların dışında tutulur.
     * Yalnızca satır içindeki isimler, aksiyonlar ve küçük görseller etkileşim alır.
     */
    .home-feed-shell .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card__thumb,
    .home-feed-shell .og-feed-insert-card__side,
    .home-feed-shell .og-feed-insert-card__follow {
        transition: color .14s ease, background-color .14s ease, border-color .14s ease, transform .14s ease;
    }

    /* En İyi Postlar: satırın üzerine gelince / tıklayınca yalnızca yazı adı mavi olur. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="top-posts"] .og-feed-insert-card__row:hover .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="top-posts"] .og-feed-insert-card__row:focus .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="top-posts"] .og-feed-insert-card__row:active .og-feed-insert-card__name {
        color: #2563eb !important;
    }

    /* En İyi Postlar: küçük görsel fare/tıklama tepkisi verir, gölge kullanılmaz. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="top-posts"] .og-feed-insert-card__row:hover .og-feed-insert-card__thumb,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="top-posts"] .og-feed-insert-card__row:focus .og-feed-insert-card__thumb {
        transform: scale(1.07);
    }

    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="top-posts"] .og-feed-insert-card__row:active .og-feed-insert-card__thumb {
        transform: scale(.96);
    }

    /* Takip Edilecek Kişiler: yalnızca kişi adı hover/focus/active durumunda mavi olur. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__person-link:hover .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__person-link:focus .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__person-link:active .og-feed-insert-card__name {
        color: #2563eb !important;
    }

    /* Takip et butonu doğrudan mavi. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow:not(.is-following) {
        border-color: #2563eb !important;
        background: #2563eb !important;
        color: #ffffff !important;
    }

    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow:not(.is-following):hover,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow:not(.is-following):focus {
        border-color: #1d4ed8 !important;
        background: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow:not(.is-following):active {
        border-color: #1e40af !important;
        background: #1e40af !important;
        color: #ffffff !important;
        transform: scale(.97);
    }

    /* Zaten takip edilen hesap farklı ama yine mavi ailede tutulur. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow.is-following {
        border-color: #bfdbfe !important;
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    /* Kategoriler: kategori adı satır hover/focus/active durumunda mavi olur. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__row:hover .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__row:focus .og-feed-insert-card__name,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__row:active .og-feed-insert-card__name {
        color: #2563eb !important;
    }

    /* Göz at artık mavi aksiyon butonu görünümünde. */
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__side {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 62px !important;
        height: 32px !important;
        padding: 0 10px !important;
        border: 1px solid #2563eb !important;
        border-radius: 9px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        box-sizing: border-box !important;
        font-weight: 600 !important;
    }

    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__row:hover .og-feed-insert-card__side,
    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__row:focus .og-feed-insert-card__side {
        border-color: #1d4ed8 !important;
        background: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__row:active .og-feed-insert-card__side {
        border-color: #1e40af !important;
        background: #1e40af !important;
        color: #ffffff !important;
        transform: scale(.97);
    }

    @media (max-width: 640px) {
        .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="categories"] .og-feed-insert-card__side {
            min-width: 58px !important;
            height: 30px !important;
            padding-inline: 9px !important;
            border-radius: 8px !important;
            font-size: 11px !important;
        }
    }

    html.dark .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow.is-following,
    .dark .home-feed-shell .og-feed-insert-card[data-og-feed-card-type="people"] .og-feed-insert-card__follow.is-following {
        border-color: #1d4ed8 !important;
        background: #172554 !important;
        color: #93c5fd !important;
    }
</style>
HTML;
    }
}
