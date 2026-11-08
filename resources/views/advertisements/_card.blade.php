@php
    $preview = $preview ?? false;
    $post = $ad->post;
    $detailUrl = $preview ? null : route('advertisements.show', ['advertisement' => $ad, 'source' => 'feed']);
    $isOwner = ! $preview && auth()->check() && auth()->id() === $ad->user_id;
@endphp

<article class="card post post--sponsored" @unless($preview) data-ad-impression-url="{{ route('advertisements.impression', $ad) }}" @endunless>
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                @if ($ad->user)
                    <a href="{{ $preview ? '#' : route('profiles.show', $ad->user) }}" class="avatar">
                        <img src="{{ $ad->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy">
                    </a>
                @else
                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                @endif
                <div class="user-box-content">
                    <span class="name">
                        @if ($preview)
                            <span class="name-text">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</span>
                        @else
                            <a class="name-text" href="{{ $ad->user ? route('profiles.show', $ad->user) : route('posts.index') }}">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</a>
                        @endif
                    </span>
                    <div class="meta">
                        <span>Sponsorlu içerik</span>
                        <time datetime="{{ $ad->created_at?->toAtomString() }}">{{ $ad->created_at?->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
            @if ($isOwner)
                <a class="post--sponsored-edit" href="{{ route('advertisements.edit', $ad) }}" aria-label="Reklamı düzenle" title="Reklamı düzenle">
                    <x-repost-icon name="edit" size="17" />
                </a>
            @endif
        </header>
        <div class="post__short">
            <h2 class="post__short-title h3">
                @if ($preview)
                    <span class="color-title"><span class="post__short-title-text">{{ $ad->title }}</span></span>
                @else
                    <a href="{{ $detailUrl }}" class="color-title"><span class="post__short-title-text">{{ $ad->title }}</span></a>
                @endif
            </h2>
            <p class="text-clamp-3 color-text">{{ $ad->description }}</p>
        </div>
    </section>

    @if ($post && ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url)))
        <section class="card-body post__short-media">
            @include('posts._media', ['post' => $post])
        </section>
    @elseif ($ad->imageUrl())
        <section class="card-body post__short-media">
            <div class="post__media">
                @if ($preview)
                    <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="post__media-image" loading="lazy">
                @else
                    <a href="{{ $detailUrl }}"><img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="post__media-image" loading="lazy"></a>
                @endif
            </div>
        </section>
    @endif

    <footer class="post__footer card-body" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;box-sizing:border-box;padding-top:14px;padding-bottom:26px;min-height:78px">
        <span class="small color-gray">Sponsorlu içerik</span>
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;min-width:0">
            @if ($preview)
                <span class="small color-gray">Reklam önizlemesi</span>
            @else
                <a class="btn btn-primary" href="{{ $detailUrl }}" style="display:inline-flex;flex:0 0 auto;align-items:center;justify-content:center;gap:9px;width:auto;min-width:max-content;height:42px;box-sizing:border-box;padding:0 20px;border-radius:11px;font-size:14px;line-height:1;font-weight:600;text-decoration:none;white-space:nowrap" aria-label="Reklam ayrıntılarını görüntüle">
                    <span>{{ $ad->button_text ?: 'Daha fazla bilgi al' }}</span>
                    @switch($ad->button_text ?: 'Daha fazla bilgi al')
                        @case('Daha fazla bilgi al')
                            <svg style="display:block;flex:0 0 18px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 11v5"/><path d="M12 7h.01"/></svg>
                            @break
                        @case('İncele')
                            <svg style="display:block;flex:0 0 18px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            @break
                        @case('Alışveriş yap')
                            <svg style="display:block;flex:0 0 18px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
                            @break
                        @case('Kaydol')
                            <svg style="display:block;flex:0 0 18px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 6-6h2"/><path d="M19 14v6"/><path d="M16 17h6"/></svg>
                            @break
                        @case('Hemen başvur')
                            <svg style="display:block;flex:0 0 18px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            @break
                        @default
                            <svg style="display:block;flex:0 0 18px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                    @endswitch
                </a>
            @endif
        </div>
    </footer>

<style>
/* Sponsored advertisement card: mobile-only layout corrections */
@media (max-width: 600px) {
    .post--sponsored {
        position: relative;
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .post--sponsored .card-body,
    .post--sponsored .post__header,
    .post--sponsored .user-box,
    .post--sponsored .user-box-content,
    .post--sponsored .post__short,
    .post--sponsored .post__short-media {
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
    }

    .post--sponsored .user-box-content {
        flex: 1 1 auto;
        overflow: hidden;
    }

    .post--sponsored .post__header {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .post--sponsored .post__header {
        padding-right: 42px;
    }

    .post--sponsored .post--sponsored-edit {
        position: absolute;
        top: 12px;
        right: 12px;
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e7ebf2;
        border-radius: 9px;
        color: #64748b;
        background: #fff;
        text-decoration: none;
        z-index: 2;
    }

    .post--sponsored .post--sponsored-edit:hover {
        color: #1769ff;
        background: #f5f8ff;
    }

    .post--sponsored .user-box-content .name,
    .post--sponsored .user-box-content .meta {
        min-width: 0;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .post--sponsored .post__short-title,
    .post--sponsored .post__short-title-text,
    .post--sponsored .post__short p {
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .post--sponsored .post__short-media {
        padding-top: 0;
    }

    .post--sponsored .post__media,
    .post--sponsored .post__media a {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    /* The theme gives .post__media a fixed 340px height.
       Remove that fixed box on phones so the complete ad image is visible. */
    .post--sponsored .post__media:not(:has(.post__media-player-slide)) {
        height: auto !important;
        min-height: 0;
        max-height: none;
        aspect-ratio: auto;
        overflow: visible;
    }

    .post--sponsored .post__media-image,
    .post--sponsored .post__media img,
    .post--sponsored .post__media video {
        display: block;
        width: 100%;
        max-width: 100%;
        height: auto !important;
        max-height: none;
        object-fit: contain;
        box-sizing: border-box;
    }

    .post--sponsored .post__footer {
        align-items: center !important;
        flex-wrap: nowrap !important;
        gap: 8px !important;
        padding-top: 12px !important;
        padding-bottom: 16px !important;
        min-height: 0 !important;
    }

    .post--sponsored .post__footer > span {
        flex: 0 1 auto;
        min-width: 0;
        margin-right: auto;
        white-space: nowrap;
    }

    .post--sponsored .post__footer > div {
        display: flex !important;
        flex: 0 0 auto;
        width: auto;
        min-width: 0 !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        margin-left: auto;
    }

    .post--sponsored .post__footer > div > a {
        flex: 0 0 auto !important;
        min-width: 0 !important;
        max-width: 100%;
        justify-content: center;
        padding-right: 8px !important;
        padding-left: 8px !important;
        min-height: 42px;
        font-size: 13px;
        line-height: 1.2;
        white-space: normal !important;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .post--sponsored .post__footer > div > a.btn-primary {
        min-width: 0 !important;
    }
}

</style>

</article>

@if (! $preview)
    @push('scripts')
    <script>
    (() => {
        if (window.__ografiAdImpressionsBound) return;
        window.__ografiAdImpressionsBound = true;
        const observeAds = () => {
            const cards = document.querySelectorAll('[data-ad-impression-url]:not([data-ad-impression-sent])');
            const sendImpression = (card) => {
                if (card.dataset.adImpressionSent) return;
                card.dataset.adImpressionSent = '1';
                fetch(card.dataset.adImpressionUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).catch(() => { delete card.dataset.adImpressionSent; });
            };
            if (!('IntersectionObserver' in window)) {
                cards.forEach(sendImpression);
                return;
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    sendImpression(entry.target);
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.5 });
            cards.forEach((card) => observer.observe(card));
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', observeAds, { once: true });
        else observeAds();
    })();
    </script>
    @endpush
@endif
