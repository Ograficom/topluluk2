@php
    $mobileSidebar = $mobileSidebar ?? false;
    $isFeed = request()->routeIs('home', 'blog.index', 'blog.posts');
    $isExplore = request()->routeIs('discover', 'search');
    $isCategories = request()->routeIs('blog.categories', 'blog.category', 'blog.category.*');
    $isMessages = request()->routeIs('messages.*');
    $activeCategorySlug = request()->routeIs('blog.category', 'blog.category.*')
        ? (string) optional(request()->route('category'))->slug
        : '';

    $sidebarCategories = \App\Models\Category::query()
        ->withCount('posts')
        ->orderByDesc('posts_count')
        ->orderBy('name')
        ->take(12)
        ->get();

    $mainLinks = [
        ['label' => __('site.discover_page.title'), 'route' => 'discover', 'active' => $isExplore, 'icon' => 'compass'],
        ['label' => __('site.tags_page.title'), 'route' => 'blog.tags', 'active' => request()->routeIs('blog.tags'), 'icon' => 'tag'],
        ['label' => __('site.users.kicker'), 'route' => 'users.index', 'active' => request()->routeIs('users.index'), 'icon' => 'users'],
    ];
@endphp

<div class="sidebar__content{{ $mobileSidebar ? ' sidebar__content--drawer' : '' }}" aria-label="{{ __('site.discover_page.title') }}">
    <div class="sidebar__body">
        <nav class="sidebar__section" aria-label="Ana menü">
            <h2 class="sidebar__title">Menü</h2>
            <ul class="sidebar__list">
                <li>
                    <a href="{{ route('home') }}" class="sidebar__link{{ $isFeed ? ' is-active' : '' }}" @if($isFeed) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                        <span>Akış</span>
                    </a>
                </li>
                @foreach($mainLinks as $link)
                    @if(\Illuminate\Support\Facades\Route::has($link['route']))
                        <li>
                            <a href="{{ route($link['route']) }}" class="sidebar__link{{ $link['active'] ? ' is-active' : '' }}" @if($link['active']) aria-current="page" @endif>
                                @if($link['icon'] === 'compass')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2.2 4.8-4.8 2.2 2.2-4.8z"/></svg>
                                @elseif($link['icon'] === 'tag')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13 13 20 3 10V4h6z"/><circle cx="7.5" cy="7.5" r="1"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 20v-1.5a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4V20M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 20v-1.5a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                @endif
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
                @auth
                    <li>
                        <a href="{{ route('messages.index') }}" class="sidebar__link{{ $isMessages ? ' is-active' : '' }}" @if($isMessages) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v12H8l-4 3z"/><path d="M8 9h8M8 13h5"/></svg>
                            <span>Mesajlar</span>
                        </a>
                    </li>
                    @if(\Illuminate\Support\Facades\Route::has('blog.bookmarks'))
                        <li>
                            <a href="{{ route('blog.bookmarks') }}" class="sidebar__link{{ request()->routeIs('blog.bookmarks') ? ' is-active' : '' }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z"/></svg>
                                <span>Kaydedilenler</span>
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </nav>

        <section class="sidebar__section" aria-labelledby="repost-categories-title">
            <h2 class="sidebar__title" id="repost-categories-title">Kategoriler</h2>
            <ul class="sidebar__list sidebar__list--channels">
                @forelse($sidebarCategories as $category)
                    <li>
                        <a href="{{ route('blog.category', ['category' => $category->slug]) }}"
                           class="channel-icon{{ $activeCategorySlug === (string) $category->slug ? ' is-active' : '' }}"
                           title="{{ $category->name }}"
                           aria-label="{{ $category->name }}"
                           @if($activeCategorySlug === (string) $category->slug) aria-current="page" @endif>
                            @if($category->profile_image_url)
                                <img src="{{ $category->profile_image_url }}" alt="" width="40" height="40" loading="lazy">
                            @else
                                <span class="channel-icon__initial">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                            @endif
                            <span class="channel-icon__label">{{ $category->name }}</span>
                        </a>
                    </li>
                @empty
                    <li class="sidebar__empty">Henüz kategori yok.</li>
                @endforelse
            </ul>
            @if(\Illuminate\Support\Facades\Route::has('blog.categories'))
                <a class="sidebar__more" href="{{ route('blog.categories') }}">Tüm kategoriler</a>
            @endif
        </section>
    </div>

    <div class="sidebar__footer">
        @auth
            <a href="{{ route('profile.edit') }}" class="sidebar__account">
                <span class="sidebar__account-avatar">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="" width="36" height="36">
                    @else
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <span class="sidebar__account-name">{{ auth()->user()->name }}</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="sidebar__login">Giriş yap</a>
        @endauth
        <span class="sidebar__copyright">© {{ now()->year }} OgraFi</span>
    </div>
</div>
