@php
    $mobilePopularAuthors = $sidebarAuthors ?? collect();
    $mobilePopularPosts = $sidebarPosts ?? collect();
    $mobilePopularComments = $sidebarComments ?? collect();
    $mobilePopularTags = $popularTags ?? collect();
@endphp

<div class="side-panel side-panel-start mobile-side-panel" id="mobileAside" data-side-panel="mobileAside" aria-hidden="true">
    <div class="side-panel__dialog" role="dialog" aria-modal="true" aria-labelledby="mobileAsideLabel" tabindex="-1">
        <header class="side-panel__header">
            <a href="{{ route('posts.index') }}" class="logo" aria-label="{{ $brandName }} ana sayfa">
                <img src="{{ $brandLogoUrl ?? asset('assets/img/meta/logo.svg') }}" alt="" draggable="false" width="36" height="36" class="logo-icon" onerror="this.onerror=null;this.src='{{ asset('assets/img/meta/logo.svg') }}';">
                <span class="logo-text" id="mobileAsideLabel">{{ $brandName }}</span>
            </a>
            <button class="btn btn-secondary btn-md btn-circle side-panel__close" type="button" data-side-panel-close aria-label="Menüyü kapat">
                <x-repost-icon name="close" size="16" class="color-gray" />
            </button>
        </header>

        <div class="side-panel__body">
            <div class="widgets">
                <section class="widget card card-body">
                    <header><h2 class="widget__title h6">Etiketler</h2></header>
                    <div class="widget__content tagscloud">
                        @forelse ($mobilePopularTags as $tag)
                            <span><a href="{{ route('tags.show', ['tag' => $tag]) }}">{{ $tag }}</a></span>
                        @empty
                            <span class="small color-gray">Henüz etiket yok</span>
                        @endforelse
                    </div>
                    <footer class="widget__footer"><a href="{{ route('tags.index') }}" class="link-accent">Tüm etiketler</a></footer>
                </section>

                <section class="widget card card-body">
                    <header><h2 class="widget__title h6">Popüler yazarlar</h2></header>
                    <div class="widget__content widget__content--bleed">
                        <ul class="widget__list">
                            @forelse ($mobilePopularAuthors as $author)
                                <li class="user-card">
                                    <a class="user-box" href="{{ route('profiles.show', $author) }}">
                                        <span class="avatar"><img src="{{ $author->avatarUrl() }}" alt="" width="40" height="40" loading="lazy"></span>
                                        <span class="user-box-content">
                                            <strong class="name">
                                                <span class="name-text">{{ $author->displayName() }}</span>
                                                @if ($author->hasVerifiedBadge())
                                                    <span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                                                @endif
                                            </strong>
                                            <span class="meta">{{ $author->posts_count }} yazı</span>
                                        </span>
                                    </a>
                                </li>
                            @empty
                                <li class="user-card"><span class="small color-gray">Henüz yazar yok</span></li>
                            @endforelse
                        </ul>
                    </div>
                    <footer class="widget__footer"><a href="{{ route('members.index') }}" class="link-accent">Üye sıralaması</a></footer>
                </section>

                <section class="widget card card-body">
                    <header><h2 class="widget__title h6">Popüler yazılar</h2></header>
                    <div class="widget__content widget__content--bleed">
                        <ul class="widget__list widget__list--separator">
                            @forelse ($mobilePopularPosts as $post)
                                <li class="entry-card">
                                    <article class="entry-box">
                                        <div class="entry-box-content">
                                            <a href="{{ route('posts.show', $post) }}" class="text-clamp-2 color-text">{{ $post->title }}</a>
                                            <div class="meta">
                                                <span class="meta-item"><x-repost-icon name="comment" size="14" />{{ $post->comments_count }}</span>
                                                <span class="meta-item"><x-repost-icon name="eye" size="14" />{{ number_format($post->views) }}</span>
                                            </div>
                                        </div>
                                    </article>
                                </li>
                            @empty
                                <li class="entry-card"><span class="small color-gray">Henüz yayınlanmış yazı yok</span></li>
                            @endforelse
                        </ul>
                    </div>
                    <footer class="widget__footer"><a href="{{ route('posts.index', ['sort' => 'popular']) }}" class="link-accent">Tüm popüler yazılar</a></footer>
                </section>

                <section class="widget card card-body">
                    <header><h2 class="widget__title h6">Popüler yorumlar</h2></header>
                    <div class="widget__content widget__content-offset">
                        <ul class="widget__list widget__list--limit widget__list--separator widget__list--separator-lg">
                            @forelse ($mobilePopularComments as $comment)
                                <li class="comm-card">
                                    <div class="user-box user-box-sm">
                                        @if ($comment->user)
                                            <a href="{{ route('profiles.show', $comment->user) }}" class="avatar" aria-label="{{ $comment->user->displayName() }} profili">
                                                <img src="{{ $comment->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy">
                                            </a>
                                        @else
                                            <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                                        @endif
                                        <div class="user-box-content user-box-content--line">
                                            @if ($comment->user)
                                                <a href="{{ route('profiles.show', $comment->user) }}" class="name"><span class="name-text">{{ $comment->user->displayName() }}</span></a>
                                            @else
                                                <span class="name">Silinmiş kullanıcı</span>
                                            @endif
                                            <time class="meta">{{ $comment->created_at?->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                    <div class="comm-card-short text-clamp-3">{{ $comment->body }}</div>
                                    @if ($comment->post)
                                        <div class="small text-clamp-1">
                                            <span class="color-gray">Yazı:</span>
                                            <a href="{{ route('posts.show', $comment->post) }}#comments" class="link">{{ $comment->post->title }}</a>
                                        </div>
                                    @endif
                                </li>
                            @empty
                                <li class="comm-card"><span class="small color-gray">Henüz yorum yok</span></li>
                            @endforelse
                        </ul>
                    </div>
                    <footer class="widget__footer"><a href="{{ route('comments.latest') }}" class="link-accent">Tüm yorumlar</a></footer>
                </section>

                <section class="widget card card-body">
                    <header><h2 class="widget__title h6">Gezinme</h2></header>
                    <div class="widget__content widget__content--bleed">
                        <ul class="widget__list">
                            <li class="user-card"><a class="user-box" href="{{ auth()->check() ? route('community.following') : route('login') }}"><span class="user-box-content"><span class="name">Takip akışı</span></span></a></li>
                            <li class="user-card"><a class="user-box" href="{{ route('posts.latest') }}"><span class="user-box-content"><span class="name">Son yazılar</span></span></a></li>
                            <li class="user-card"><a class="user-box" href="{{ route('stats.index') }}"><span class="user-box-content"><span class="name">İstatistikler</span></span></a></li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
