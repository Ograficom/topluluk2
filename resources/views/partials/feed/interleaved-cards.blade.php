@php
    $topPosts = collect($topPosts ?? [])->take(5)->values();
    $feedCategories = collect($feedCategories ?? [])->take(5)->values();
    $feedSuggestedUsers = collect($feedSuggestedUsers ?? [])->take(5)->values();
    $feedGlobalStart = max(1, (int) ($feedGlobalStart ?? 1));
@endphp

<div data-og-feed-insert-assets data-global-start="{{ $feedGlobalStart }}" hidden>
    <template data-og-feed-template="top-posts">
        <section class="og-feed-insert-card" data-og-feed-insert-card data-og-feed-card-type="top-posts" aria-label="En İyi Postlar">
            <h2 class="og-feed-insert-card__title">En İyi Postlar</h2>
            <div class="og-feed-insert-card__list">
                @forelse($topPosts as $post)
                    @php
                        $postImage = $post->featured_image_url
                            ?? $post->featured_image
                            ?? null;
                    @endphp
                    <a class="og-feed-insert-card__row" href="{{ route('blog.post', $post->slug) }}">
                        <span class="og-feed-insert-card__copy">
                            <span class="og-feed-insert-card__name">{{ $post->title ?: __('site.post.untitled_story') }}</span>
                            <span class="og-feed-insert-card__meta">{{ number_format((int) ($post->views_count ?? 0)) }} görüntülenme</span>
                        </span>
                        @if(filled($postImage))
                            <img class="og-feed-insert-card__thumb" src="{{ $postImage }}" alt="" loading="lazy" decoding="async">
                        @else
                            <span class="og-feed-insert-card__thumb og-feed-insert-card__placeholder" aria-hidden="true"></span>
                        @endif
                    </a>
                @empty
                    <div class="og-feed-insert-card__empty">Henüz gösterilecek gönderi yok.</div>
                @endforelse
            </div>
        </section>
    </template>

    <template data-og-feed-template="categories">
        <section class="og-feed-insert-card" data-og-feed-insert-card data-og-feed-card-type="categories" aria-label="Kategoriler">
            <h2 class="og-feed-insert-card__title">Kategoriler</h2>
            <div class="og-feed-insert-card__list">
                @forelse($feedCategories as $category)
                    @php
                        $categoryImage = $category->profile_image_url ?? $category->profile_image ?? null;
                    @endphp
                    <a class="og-feed-insert-card__row" href="{{ route('blog.category', $category->slug) }}">
                        @if(filled($categoryImage))
                            <img class="og-feed-insert-card__avatar og-feed-insert-card__avatar--square" src="{{ $categoryImage }}" alt="" loading="lazy" decoding="async">
                        @else
                            <span class="og-feed-insert-card__avatar og-feed-insert-card__avatar--square og-feed-insert-card__placeholder" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr((string) $category->name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="og-feed-insert-card__copy">
                            <span class="og-feed-insert-card__name">{{ $category->name }}</span>
                            <span class="og-feed-insert-card__meta">{{ number_format((int) ($category->posts_count ?? 0)) }} gönderi</span>
                        </span>
                        <span class="og-feed-insert-card__side">Göz at</span>
                    </a>
                @empty
                    <div class="og-feed-insert-card__empty">Henüz gösterilecek kategori yok.</div>
                @endforelse
            </div>
        </section>
    </template>

    <template data-og-feed-template="people">
        <section class="og-feed-insert-card" data-og-feed-insert-card data-og-feed-card-type="people" aria-label="Takip Edilecek Kişiler">
            <h2 class="og-feed-insert-card__title">Takip Edilecek Kişiler</h2>
            <div class="og-feed-insert-card__list">
                @forelse($feedSuggestedUsers as $person)
                    @php
                        $isFollowing = (bool) ($person->is_followed_by_viewer ?? false);
                    @endphp
                    <div class="og-feed-insert-card__row og-feed-insert-card__row--person">
                        <a class="og-feed-insert-card__person-link" href="{{ route('users.show', $person) }}">
                            <img class="og-feed-insert-card__avatar" src="{{ $person->profile_photo_url }}" alt="" loading="lazy" decoding="async">
                            <span class="og-feed-insert-card__copy">
                                <span class="og-feed-insert-card__name">{{ $person->name }}</span>
                                <span class="og-feed-insert-card__meta">
                                    @if(filled($person->username)){{ '@' . $person->username }} · @endif{{ number_format((int) ($person->followers_count ?? 0)) }} takipçi
                                </span>
                            </span>
                        </a>

                        @auth
                            <form method="POST" action="{{ route('users.follow', $person->username) }}" class="og-feed-insert-card__follow-form">
                                @csrf
                                <button type="submit" class="og-feed-insert-card__follow{{ $isFollowing ? ' is-following' : '' }}">
                                    {{ $isFollowing ? 'Takiptesin' : 'Takip et' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="og-feed-insert-card__follow">Takip et</a>
                        @endauth
                    </div>
                @empty
                    <div class="og-feed-insert-card__empty">Şu anda önerilecek kişi yok.</div>
                @endforelse
            </div>
        </section>
    </template>
</div>

<style data-og-feed-insert-style>
.home-feed-shell .og-feed-insert-card {
    width: 100%;
    margin: 0;
    padding: 16px;
    border: 1px solid #e3e6ea;
    border-radius: 14px;
    background: #fff;
    box-sizing: border-box;
    overflow: hidden;
    font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}
.home-feed-shell .og-feed-insert-card__title {
    margin: 0 0 12px;
    color: #171717;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.35;
    text-align: left;
}
.home-feed-shell .og-feed-insert-card__list { display: flex; flex-direction: column; }
.home-feed-shell .og-feed-insert-card__row {
    display: flex;
    align-items: center;
    min-height: 58px;
    gap: 11px;
    padding: 7px 0;
    color: inherit;
    text-decoration: none;
    border: 0;
}
.home-feed-shell .og-feed-insert-card__copy {
    display: flex;
    flex: 1 1 auto;
    min-width: 0;
    flex-direction: column;
    gap: 3px;
}
.home-feed-shell .og-feed-insert-card__name {
    display: -webkit-box;
    overflow: hidden;
    color: #1d1d1f;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
    text-overflow: ellipsis;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
.home-feed-shell .og-feed-insert-card__meta,
.home-feed-shell .og-feed-insert-card__side {
    color: #747982;
    font-size: 12px;
    line-height: 1.35;
}
.home-feed-shell .og-feed-insert-card__side {
    flex: 0 0 auto;
    margin-left: auto;
    white-space: nowrap;
}
.home-feed-shell .og-feed-insert-card__thumb,
.home-feed-shell .og-feed-insert-card__avatar {
    flex: 0 0 auto;
    width: 44px;
    height: 44px;
    object-fit: cover;
    background: #f1f2f4;
}
.home-feed-shell .og-feed-insert-card__thumb { margin-left: auto; border-radius: 10px; }
.home-feed-shell .og-feed-insert-card__avatar { border-radius: 9999px; }
.home-feed-shell .og-feed-insert-card__avatar--square { border-radius: 10px; }
.home-feed-shell .og-feed-insert-card__placeholder {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #555b64;
    font-size: 14px;
    font-weight: 700;
}
.home-feed-shell .og-feed-insert-card__row--person { justify-content: space-between; }
.home-feed-shell .og-feed-insert-card__person-link {
    display: flex;
    align-items: center;
    flex: 1 1 auto;
    min-width: 0;
    gap: 11px;
    color: inherit;
    text-decoration: none;
}
.home-feed-shell .og-feed-insert-card__follow-form { flex: 0 0 auto; margin: 0; }
.home-feed-shell .og-feed-insert-card__follow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    min-width: 76px;
    height: 34px;
    padding: 0 12px;
    border: 1px solid #d9dde3;
    border-radius: 10px;
    background: #fff;
    color: #1f2937;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
}
.home-feed-shell .og-feed-insert-card__follow.is-following { background: #f1f2f4; color: #5c626b; }
.home-feed-shell .og-feed-insert-card__empty { padding: 10px 0 4px; color: #747982; font-size: 13px; }

html.dark .home-feed-shell .og-feed-insert-card,
.dark .home-feed-shell .og-feed-insert-card { border-color: #30343a; background: #17181b; }
html.dark .home-feed-shell .og-feed-insert-card__title,
html.dark .home-feed-shell .og-feed-insert-card__name,
.dark .home-feed-shell .og-feed-insert-card__title,
.dark .home-feed-shell .og-feed-insert-card__name { color: #f4f4f5; }
html.dark .home-feed-shell .og-feed-insert-card__meta,
html.dark .home-feed-shell .og-feed-insert-card__side,
html.dark .home-feed-shell .og-feed-insert-card__empty,
.dark .home-feed-shell .og-feed-insert-card__meta,
.dark .home-feed-shell .og-feed-insert-card__side,
.dark .home-feed-shell .og-feed-insert-card__empty { color: #a5a8ae; }
html.dark .home-feed-shell .og-feed-insert-card__follow,
.dark .home-feed-shell .og-feed-insert-card__follow { border-color: #3b4047; background: #202226; color: #f4f4f5; }
html.dark .home-feed-shell .og-feed-insert-card__follow.is-following,
.dark .home-feed-shell .og-feed-insert-card__follow.is-following { background: #2b2e33; color: #c7c9ce; }

@media (max-width: 640px) {
    .home-feed-shell .og-feed-insert-card { padding: 14px 13px; border-radius: 12px; }
    .home-feed-shell .og-feed-insert-card__title { margin-bottom: 9px; font-size: 15px; }
    .home-feed-shell .og-feed-insert-card__row { min-height: 54px; gap: 9px; padding: 6px 0; }
    .home-feed-shell .og-feed-insert-card__name { font-size: 13px; }
    .home-feed-shell .og-feed-insert-card__meta,
    .home-feed-shell .og-feed-insert-card__side { font-size: 11px; }
    .home-feed-shell .og-feed-insert-card__thumb,
    .home-feed-shell .og-feed-insert-card__avatar { width: 40px; height: 40px; }
    .home-feed-shell .og-feed-insert-card__follow {
        min-width: 70px;
        height: 32px;
        padding-inline: 10px;
        border-radius: 9px;
        font-size: 11px;
    }
}
</style>

<script data-og-feed-insert-script>
(() => {
    const initOgrafiFeedInserts = () => {
        const assets = document.querySelector('[data-og-feed-insert-assets]');
        const shell = document.querySelector('.home-feed-shell');
        if (!assets || !shell || shell.dataset.ogFeedInsertReady === '1') return;

        const globalStart = Math.max(1, parseInt(assets.dataset.globalStart || '1', 10) || 1);
        const templateOrder = ['top-posts', 'categories', 'people'];

        const buildSlots = (maxPosition) => {
            const slots = [];
            let position = 0;
            let cardNumber = 0;
            let gap = 6;

            while (position < maxPosition) {
                cardNumber += 1;

                if (cardNumber <= 5) {
                    gap = 6;
                } else if (cardNumber === 6) {
                    gap = 7;
                } else {
                    gap = (gap * 2) + 2;
                }

                position += gap;
                slots.push({
                    position,
                    type: templateOrder[(cardNumber - 1) % templateOrder.length],
                    cardNumber,
                    gap,
                });
            }

            return slots;
        };

        const posts = Array.from(shell.querySelectorAll('.ografi-filterable-post'));
        if (!posts.length) return;

        const globalEnd = globalStart + posts.length - 1;
        const slots = buildSlots(globalEnd).filter((slot) => slot.position >= globalStart && slot.position <= globalEnd);

        slots.forEach((slot) => {
            const localIndex = slot.position - globalStart;
            const targetPost = posts[localIndex];
            const template = assets.querySelector(`[data-og-feed-template="${slot.type}"]`);
            if (!targetPost || !template) return;

            const fragment = template.content.cloneNode(true);
            const card = fragment.querySelector('[data-og-feed-insert-card]');
            if (!card) return;

            card.dataset.ogFeedInsertGenerated = '1';
            card.dataset.ogFeedCardNumber = String(slot.cardNumber);
            card.dataset.ogFeedAfterPost = String(slot.position);
            card.dataset.ogFeedGap = String(slot.gap);
            targetPost.insertAdjacentElement('afterend', card);
        });

        shell.dataset.ogFeedInsertReady = '1';
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOgrafiFeedInserts, { once: true });
    } else {
        initOgrafiFeedInserts();
    }
})();
</script>
