@extends('layouts.app')

@section('title', filled($search) ? 'Arama sonuçları' : 'Akış')

@section('content')
    <section aria-label="Akış ayarları">
        <div class="hero__header-main">
            <div>
                <div class="dropdown dropdown-md">
                    <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false">
                        <x-repost-icon name="feed" size="16" />
                        <span class="hero__toggle-text">{{ filled($search) ? 'Arama sonuçları' : 'Son yazılar' }}</span>
                        <x-repost-icon name="chevron-down" size="12" />
                    </button>
                    <ul class="dropdown-menu">
                        <li class="dropdown-menu-item dropdown-menu-item-sm is-active"><a href="{{ route('posts.index') }}">Son yazılar</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'popular']) }}">Popüler</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'views']) }}">En çok okunan</a></li>
                    </ul>
                </div>
                <div class="dropdown dropdown-sm">
                    <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false">
                        <x-repost-icon name="calendar" size="16" />
                        <span class="hero__toggle-text">
                            {{ match ($period) {
                                'day' => 'Son 24 saat',
                                'week' => 'Bu hafta',
                                'month' => 'Bu ay',
                                default => 'Tüm zamanlar',
                            } }}
                        </span>
                        <x-repost-icon name="chevron-down" size="12" />
                    </button>
                    <ul class="dropdown-menu">
                        <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => blank($period)])><a href="{{ route('posts.index') }}">Tüm zamanlar</a></li>
                        <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => $period === 'day'])><a href="{{ route('posts.index', ['period' => 'day']) }}">Son 24 saat</a></li>
                        <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => $period === 'week'])><a href="{{ route('posts.index', ['period' => 'week']) }}">Bu hafta</a></li>
                        <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => $period === 'month'])><a href="{{ route('posts.index', ['period' => 'month']) }}">Bu ay</a></li>
                    </ul>
                </div>
            </div>
            <div class="dropdown">
                <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Sıralama seçenekleri">
                    <x-repost-icon name="sort" size="17" />
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => $sort === 'latest'])><a href="{{ route('posts.index', ['sort' => 'latest']) }}">En yeni</a></li>
                    <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => $sort === 'popular'])><a href="{{ route('posts.index', ['sort' => 'popular']) }}">Popüler</a></li>
                    <li @class(['dropdown-menu-item', 'dropdown-menu-item-sm', 'is-active' => $sort === 'views'])><a href="{{ route('posts.index', ['sort' => 'views']) }}">Görüntülenme</a></li>
                </ul>
            </div>
        </div>
    </section>
    <div class="hero__digest card card-body" data-loadpost="last" data-loadpost-limit="5">
        @forelse ($posts as $digestPost)
            <div class="digest d-none" data-loadpost-item="last">
                <a class="digest__title" href="{{ route('posts.show', $digestPost) }}">{{ $digestPost->title }}</a>
                <span class="digest__comm">
                    <span class="digest__comm-icon"><x-repost-icon name="comment" size="12" /></span>
                    {{ number_format($digestPost->comments_count ?? 0) }}
                </span>
            </div>
        @empty
            <div class="digest"><span class="color-gray">Henüz öne çıkan yazı yok.</span></div>
        @endforelse
        @if ($posts->count() > 5)
            <div>
                <button class="hero__digest-more link-accent" type="button" data-loadpost-more="last">
                    Daha fazla göster <x-repost-icon name="chevron-down" size="12" />
                </button>
            </div>
        @endif
    </div>
    @if (filled($search))
        <div class="d-flex justify-content-end">
            <a class="btn btn-secondary-ghost btn-sm" href="{{ route('posts.index') }}">Aramayı temizle</a>
        </div>
    @endif

    @php
        $feedAds = ($ads ?? collect())->values();
        $feedAdCount = $feedAds->count();
        $postCount = $posts->count();
        $adPositions = [];
        foreach ($feedAds as $adIndex => $feedAd) {
            $adPositions[(int) ceil(($postCount * ($adIndex + 1)) / ($feedAdCount + 1))] = $adIndex;
        }
    @endphp

    @forelse ($posts as $post)
        @include('posts._card', ['post' => $post])

        @if (isset($adPositions[$loop->iteration]))
            @include('advertisements._card', ['ad' => $feedAds->get($adPositions[$loop->iteration])])
        @endif
    @empty
        <section class="card card-body empty-state">
            <h2 class="h4">Henüz yayınlanmış yazı yok</h2>
            <p class="color-gray">
                {{ filled($search) ? 'Aramanızla eşleşen bir yazı bulunamadı.' : 'İlk yazılar yayınlandığında burada görünecek.' }}
            </p>
        </section>
    @endforelse

    @include('layouts._pagination', ['paginator' => $posts])
@endsection
