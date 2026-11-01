@extends('layouts.app')

@section('title', 'OgraFi — Topluluk akışı')
@section('meta_description', 'OgraFi topluluğunda yeni gönderileri keşfet, tartışmalara katıl ve kendi içeriklerini paylaş.')

@section('content')
    <div data-ografi-layout-container="home">
        <section data-ografi-layout-section="feed_filters" aria-label="Akış ayarları">
            <div class="hero__header-main">
                <div>
                    <div class="dropdown dropdown-md">
                        <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false">
                            <svg width="16" height="16"><use href="#feed"/></svg>
                            <span class="hero__toggle-text">Son gönderiler</span>
                            <svg width="12" height="12"><use href="#chevron-down"/></svg>
                        </button>
                        <ul class="dropdown-menu">
                            <li class="dropdown-menu-item dropdown-menu-item-sm is-active"><a href="{{ url('/') }}">Son gönderiler</a></li>
                            <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ url('/discover') }}">Keşfet</a></li>
                            @auth<li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('blog.bookmarks') }}">Kaydedilenler</a></li>@endauth
                        </ul>
                    </div>
                </div>
                @auth
                    <a href="{{ url('/blog/create') }}" class="btn btn-primary btn-sm btn-rounded"><svg width="16" height="16"><use href="#pen"/></svg> Gönderi yaz</a>
                @endauth
            </div>
        </section>

        <section data-ografi-layout-section="read_filters">
            @if(isset($communityCategories) && $communityCategories->isNotEmpty())
                <div class="hero__digest card card-body">
                    <div class="hero__digest-header"><strong>Toplulukları keşfet</strong><a class="link-accent" href="{{ url('/Categorys') }}">Tümünü gör</a></div>
                    <div class="tagscloud">
                        @foreach($communityCategories->take(8) as $communityCategory)
                            <span><a href="{{ url('/Categorys/'.$communityCategory->slug) }}">{{ $communityCategory->name }}</a></span>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <section data-ografi-layout-section="post_feed">
            @forelse($posts as $post)
                @include('partials.post-card', ['post' => $post, 'reactionTypes' => $reactionTypes ?? collect()])
            @empty
                <section class="card card-body text-center">
                    <h2 class="h5">Henüz gönderi yok</h2>
                    <p class="color-gray">Toplulukta ilk paylaşımı sen yapabilirsin.</p>
                    @auth<a href="{{ url('/blog/create') }}" class="btn btn-primary btn-rounded">İlk gönderiyi oluştur</a>
                    @else<a href="{{ route('register') }}" class="btn btn-primary btn-rounded">OgraFi'ye katıl</a>@endauth
                </section>
            @endforelse
            @if(method_exists($posts, 'links'))
                <div class="navigation">{{ $posts->links() }}</div>
            @endif
        </section>
    </div>
@endsection
