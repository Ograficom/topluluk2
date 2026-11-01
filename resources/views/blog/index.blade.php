@extends('layouts.app')

@section('title', 'Gönderiler — OgraFi')

@section('content')
    <div data-ografi-layout-container="blog_index">
        @if(isset($category) && $category)
            @include('blog.categories.categories-show', ['category' => $category])
        @endif
        @if(request()->filled('tag'))
            <section data-ografi-layout-section="tag_title" class="repost-page-heading"><div><span class="repost-eyebrow">ETİKET AKIŞI</span><h1>#{{ request('tag') }}</h1></div></section>
        @endif
        @include('partials.ads.slot', ['layoutSection' => 'blog_ads_top'])
        <header class="repost-page-heading">
            <div><span class="repost-eyebrow">OGRAFI BLOG</span><h1>{{ isset($category) && $category ? $category->name : 'Gönderiler' }}</h1><p>Topluluğun yazılarını ve fikirlerini keşfet.</p></div>
            @auth<a href="{{ route('blog.create') }}" class="btn btn-primary btn-rounded">Gönderi oluştur</a>@endauth
        </header>
        <form method="GET" action="{{ route('blog.index') }}" class="card card-body repost-filter-form">
            <label for="post-search">Gönderilerde ara</label><input id="post-search" type="search" name="q" value="{{ request('q') }}" placeholder="Başlık veya içerik">
            <label for="post-category">Topluluk</label><select id="post-category" name="category"><option value="">Tüm topluluklar</option>@foreach($categories as $categoryOption)<option value="{{ $categoryOption->slug }}" @selected(($activeCategory ?? '') === $categoryOption->slug)>{{ $categoryOption->name }}</option>@endforeach</select>
            <button class="btn btn-primary btn-rounded" type="submit">Filtrele</button>
        </form>
        <section data-ografi-layout-section="blog_posts">
            @forelse($posts as $post)
                @include('partials.post-card', ['post' => $post, 'reactionTypes' => $reactionTypes ?? collect()])
            @empty
                <section class="card card-body text-center"><h2>Gönderi bulunamadı</h2><p class="color-gray">Filtreleri değiştirip yeniden deneyebilirsin.</p></section>
            @endforelse
            @if(method_exists($posts, 'links'))<div class="navigation">{{ $posts->links() }}</div>@endif
        </section>
    </div>
@endsection
