@extends('layouts.app')

@section('title', $channel->name)

@section('content')

    @php($channelTags = $posts->getCollection()->flatMap(fn ($post) => $post->tags ?? [])->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')->unique()->take(18))
    <div class="channel-page">
        <section class="channel card card-body">
            @if (auth()->user()?->is_admin)
                <div class="edit">
                    <div class="dropdown dropdown-md">
                        <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-controls="channel-admin-menu" aria-label="Kanal yönetimi">
                            <x-repost-icon name="more" size="18" class="color-gray" />
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="channel-admin-menu">
                            <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('channels.create') }}">Kategori oluştur</a></li>
                            <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('channels.edit', $channel) }}">Kategoriyi düzenle</a></li>
                            <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="#channel-panel-comments" data-channel-comments-tab>Yorumlar</a></li>
                        </ul>
                    </div>
                </div>
            @endif

            <div class="channel__icon">
                @if ($channel->image_path)
                    <img src="{{ asset('storage/'.$channel->image_path) }}" alt="{{ $channel->name }}" width="64" height="64">
                @else
                    <x-repost-icon name="image" size="28" />
                @endif
            </div>

            <div class="channel__info">
                <div class="channel__overview">
                    <div class="channel__content">
                        <h1 class="channel__title h3">{{ $channel->name }}</h1>
                        <div class="channel__description trim" data-trim data-trim-limit="2" data-trim-link-text="Gizle">
                            <div class="channel__description-text trim__content" data-trim-content>{{ $channel->description }}</div>
                            <button class="trim__toggle" type="button" data-trim-toggle hidden>
                                <span data-trim-label>Daha fazla göster</span>
                                <x-repost-icon name="chevron-down" size="14" />
                            </button>
                        </div>
                    </div>

                    <aside class="channel__side">
                        @auth
                            <form action="{{ route('channels.subscribe', $channel) }}" method="POST">
                                @csrf
                                <button class="btn {{ $isSubscribed ? 'btn-secondary' : 'btn-primary' }}" type="submit">
                                    {{ $isSubscribed ? 'Abonelikten çık' : 'Abone ol' }}
                                </button>
                            </form>
                        @else
                            <a class="btn btn-primary" href="{{ route('login') }}">Abone ol</a>
                        @endauth

                        <div class="channel__authors">
                            <span class="channel__authors-title">Popüler yazarlar:</span>
                            <ul class="user-set">
                                @foreach ($posts->getCollection()->pluck('user')->filter()->unique('id')->take(5) as $author)
                                    <li>
                                        <a class="avatar" href="{{ route('profiles.show', $author) }}" aria-label="{{ $author->displayName() }}">
                                            <img src="{{ $author->avatarUrl() }}" alt="{{ $author->displayName() }}" width="30" height="30" loading="lazy">
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>
                </div>

                <div class="channel__tabs">
                    <nav class="tabs-nav" role="tablist" aria-label="Kanal bölümleri" data-tabs-nav="channel">
                        <button class="tabs-nav__button is-active" type="button" role="tab" id="channel-tab-posts" aria-selected="true" aria-controls="channel-panel-posts" data-tab="posts">
                            <span>Yazılar</span>
                        </button>
                        <button class="tabs-nav__button" type="button" role="tab" id="channel-tab-comments" aria-selected="false" aria-controls="channel-panel-comments" tabindex="-1" data-tab="comments">
                            <span>Yorumlar</span>
                        </button>
                    </nav>
                </div>
            </div>
        </section>

        <div class="main main__grid-feed">
            <div class="channel-page__content tabs-content" data-tabs-container="channel">
                <section class="tabs-panel is-active" role="tabpanel" id="channel-panel-posts" aria-labelledby="channel-tab-posts" data-tab-panel="posts" tabindex="0">
                    <div class="channel__posts">
                        <div class="channel__hero">
                            <div class="channel__hero-start">
                                <span class="channel__hero-count">
                                    <x-repost-icon name="copy" size="17" />
                                    {{ number_format($posts->total()) }} yazı
                                </span>
                                <div class="dropdown dropdown-md">
                                    <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false" aria-controls="channel-sort-menu">
                                        <x-repost-icon name="sort" size="17" />
                                        <span class="hero__toggle-text">Sıralama</span>
                                        <x-repost-icon name="chevron-down" size="12" />
                                    </button>
                                    <div class="dropdown-menu" id="channel-sort-menu">
                                        <div class="dropdown-menu-title">Sıralama:</div>
                                        <ul class="sort">
                                            @foreach (['date' => 'Tarih', 'updated' => 'Son güncelleme', 'popular' => 'Popülerlik', 'views' => 'Görüntülenme', 'comments' => 'Yorumlar', 'alphabetical' => 'Alfabetik'] as $sort => $label)
                                                <li class="{{ request('sort', 'date') === $sort ? 'asc' : '' }}">
                                                    <a href="{{ request()->fullUrlWithQuery(['sort' => $sort, 'page' => 1]) }}">{{ $label }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            @if ($channelTags->isNotEmpty())
                                <div class="channel__tags">
                                    <div class="channel__tags-list">
                                        @foreach ($channelTags as $tag)
                                            <a href="{{ route('tags.show', ['tag' => $tag]) }}">{{ $tag }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="feed">
                            @forelse ($posts as $post)
                                <article class="card post">
                                    <section class="card-body">
                                        <header class="post__header">
                                            <div class="user-box">
                                                @if ($post->user)
                                                    <a href="{{ route('profiles.show', $post->user) }}" class="avatar"><img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40"></a>
                                                @else
                                                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                                                @endif
                                                <div class="user-box-content">
                                                    <span class="name">
                                                        @if ($post->user)
                                                            <a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->displayName() }}</a>
                                                        @else
                                                            <span class="name-text">Repost yazarı</span>
                                                        @endif
                                                        @if ($post->user?->hasVerifiedBadge())
                                                            <span class="verify" role="img" aria-label="Doğrulanmış profil"><x-repost-icon name="verify" size="14" /></span>
                                                        @endif
                                                    </span>
                                                    <div class="meta">
                                                        <a href="{{ route('channels.show', $channel) }}">{{ $channel->name }}</a>
                                                        <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </header>
                                        <div class="post__short">
                                            <h2 class="post__short-title h3"><a href="{{ route('posts.show', $post) }}" class="color-title"><span class="post__short-title-text">{{ $post->title }}</span></a></h2>
                                            <p class="text-clamp-3 color-text">{{ $post->excerpt }}</p>
                                        </div>
                                    </section>
                                    @if ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url))
                                        <section class="card-body post__short-media">
                                            @include('posts._media', ['post' => $post])
                                        </section>
                                    @endif
                                    <footer class="post__footer card-body">
                                        <div class="post__footer-stats small color-gray"><span>{{ number_format($post->likes_count) }} beğeni</span><span>{{ number_format($post->views) }} görüntülenme</span></div>
                                        @if ($post->user?->publicUsername())
                                            <span class="small color-gray">{{ '@'.$post->user->publicUsername() }}</span>
                                        @endif
                                    </footer>
                                    @include('posts._interactions', ['post' => $post])
                                </article>
                            @empty
                                <section class="card card-body empty-state">
                                    <h2 class="h4">Bu kanalda henüz yazı yok</h2>
                                    <p class="color-gray">İlk yazılar yayınlandığında burada görünecek.</p>
                                </section>
                            @endforelse
                            @include('layouts._pagination', ['paginator' => $posts])
                        </div>
                    </div>
                </section>

                <section class="tabs-panel" role="tabpanel" id="channel-panel-comments" aria-labelledby="channel-tab-comments" data-tab-panel="comments" tabindex="0">
                    <div class="comments-tree-list">
                        @forelse ($comments as $comment)
                            @include('posts._comment', ['comment' => $comment, 'post' => $comment->post, 'allowModeration' => false])
                        @empty
                            <section class="card card-body empty-state">
                                <h2 class="h4">Henüz yorum yok</h2>
                                <p class="color-gray">Bu kanaldaki yazılara yapılan yorumlar burada görünecek.</p>
                            </section>
                        @endforelse
                    </div>
                    @include('layouts._pagination', ['paginator' => $comments])
                </section>
            </div>

            <aside class="main__widgets">
                <div class="widgets">
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">{{ number_format($channel->subscribers_count) }} abone</h2></header>
                        <div class="widget__content">
                            @if ($subscribers->isNotEmpty())
                                <ul class="channel-subscriber-grid">
                                    @foreach ($subscribers->take(12) as $subscriber)
                                        <li><a class="avatar" href="{{ route('profiles.show', $subscriber) }}" aria-label="{{ $subscriber->displayName() }} profili" title="{{ $subscriber->displayName() }}"><img src="{{ $subscriber->avatarUrl() }}" alt="{{ $subscriber->displayName() }}" width="40" height="40" loading="lazy"></a></li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="small color-gray">Henüz abone yok.</p>
                            @endif
                        </div>
                        @if ($subscribers->isNotEmpty())
                            <footer class="widget__footer"><a class="link-accent" href="#channelSubscribersModal" data-modal-btn="channel-subscribers" aria-haspopup="dialog" aria-controls="channelSubscribersModal">Tüm aboneler</a></footer>
                        @endif
                    </section>

                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Kanaldaki popüler yazılar</h2></header>
                        <div class="widget__content widget__content--bleed">
                            @if ($popularPosts->isNotEmpty())
                                <ul class="widget__list widget__list--separator">
                                    @foreach ($popularPosts as $popularPost)
                                        <li class="entry-card">
                                            <article class="entry-box {{ $popularPost->coverImageUrl() ? 'has-image' : '' }}">
                                                <div class="entry-box-content">
                                                    @if ($popularPost->user)
                                                        <div class="user-box">
                                                            <a href="{{ route('profiles.show', $popularPost->user) }}" class="avatar"><img src="{{ $popularPost->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy"></a>
                                                            <div class="user-box-content"><a href="{{ route('profiles.show', $popularPost->user) }}" class="name"><span class="name-text">{{ $popularPost->user->displayName() }}</span></a><span class="meta">{{ $popularPost->published_at?->diffForHumans() ?? $popularPost->created_at->diffForHumans() }}</span></div>
                                                        </div>
                                                    @endif
                                                    <a href="{{ route('posts.show', $popularPost) }}" class="text-clamp-2 color-text">{{ $popularPost->title }}</a>
                                                    <div class="meta"><span class="meta-item"><x-repost-icon name="comment" size="14" />{{ number_format($popularPost->comments_count) }}</span><span class="meta-item"><x-repost-icon name="eye" size="14" />{{ number_format($popularPost->views) }}</span></div>
                                                </div>
                                                @if ($popularPost->coverImageUrl())
                                                    <a href="{{ route('posts.show', $popularPost) }}" class="entry-box-image"><img src="{{ $popularPost->coverImageUrl() }}" alt="{{ $popularPost->title }}" width="100" height="80" loading="lazy"></a>
                                                @endif
                                            </article>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="small color-gray">Henüz popüler yazı yok.</p>
                            @endif
                        </div>
                    </section>

                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">{{ number_format($channelCommentsCount) }} yorum</h2></header>
                        <div class="widget__content widget__content-offset">
                            @if ($latestChannelComments->isNotEmpty())
                                <ul class="widget__list widget__list--limit widget__list--separator widget__list--separator-lg">
                                    @foreach ($latestChannelComments as $latestComment)
                                        <li class="comm-card">
                                            @if ($latestComment->user)
                                                <div class="user-box user-box-sm">
                                                    <a href="{{ route('profiles.show', $latestComment->user) }}" class="avatar"><img src="{{ $latestComment->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy"></a>
                                                    <div class="user-box-content user-box-content--line"><a href="{{ route('profiles.show', $latestComment->user) }}" class="name"><span class="name-text">{{ $latestComment->user->displayName() }}</span></a><time class="meta">{{ $latestComment->created_at->diffForHumans() }}</time></div>
                                                </div>
                                            @endif
                                            <div class="comm-card-short text-clamp-3">{{ $latestComment->body }}</div>
                                            <div class="small text-clamp-1"><span class="color-gray">Yazıda:</span> <a href="{{ route('posts.show', $latestComment->post) }}" class="link">{{ $latestComment->post?->title }}</a></div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="small color-gray">Henüz yorum yok.</p>
                            @endif
                        </div>
                        <footer class="widget__footer"><a href="{{ route('channels.show', $channel) }}?tab=comments#channel-panel-comments" class="link-accent" data-channel-comments-tab>Tüm yorumları gör</a></footer>
                    </section>
                </div>
            </aside>
        </div>
    </div>

    @if ($subscribers->isNotEmpty())
        <div class="modal" id="channelSubscribersModal" data-modal="channel-subscribers" role="dialog" aria-modal="true" aria-labelledby="channelSubscribersTitle" aria-hidden="true">
            <div class="modal-content">
                <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat" title="Kapat"><x-repost-icon name="close" size="16" class="color-gray" /></button>
                <header class="modal-header"><h2 class="modal-title h4" id="channelSubscribersTitle">Tüm aboneler</h2></header>
                <div class="modal-body">
                    <ul class="channel__subscribers">
                        @foreach ($subscribers as $subscriber)
                            <li class="user-card">
                                <a class="user-box" href="{{ route('profiles.show', $subscriber) }}">
                                    <span class="avatar"><img src="{{ $subscriber->avatarUrl() }}" alt="" width="40" height="40" loading="lazy"></span>
                                    <span class="user-box-content"><span class="name"><span class="name-text">{{ $subscriber->displayName() }}</span>@if ($subscriber->hasVerifiedBadge()) <span class="verify" role="img" aria-label="Doğrulanmış profil"><x-repost-icon name="verify" size="14" /></span>@endif</span>@if ($subscriber->publicUsername()) <span class="small color-gray">{{ '@'.$subscriber->publicUsername() }}</span>@endif</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (event) {
    const trigger = event.target.closest('[data-channel-comments-tab]');
    if (!trigger) return;
    event.preventDefault();
    document.querySelector('#channel-tab-comments')?.click();
});
</script>
@endpush
