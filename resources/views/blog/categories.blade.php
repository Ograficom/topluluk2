@extends('layouts.repost')

@section('title', 'Topluluklar — OgraFi')
@section('meta_description', 'OgraFi topluluklarını keşfet ve ilgi alanlarına katıl.')

@section('content')
    <div data-ografi-layout-container="categories">
        <section data-ografi-layout-section="categories_header" class="repost-feed-heading">
            <div><span class="repost-eyebrow">BİRLİKTE DAHA GÜÇLÜ</span><h1>Toplulukları keşfet</h1><p>İlgi alanına uygun toplulukları bul, sohbete katıl.</p></div>
            @auth<a href="{{ route('blog.category.create') }}" class="btn btn-primary btn-rounded">Topluluk oluştur</a>@endauth
        </section>
        <div data-ografi-layout-section="categories_count" class="small color-gray">{{ number_format((int)($categories->total() ?? $categories->count())) }} topluluk</div>
        <form method="GET" action="{{ route('blog.categories') }}" class="repost-sort-row">
            <label for="category-sort">Sıralama</label>
            <select id="category-sort" name="sort" onchange="this.form.submit()">
                <option value="relevance" @selected(($sort ?? 'relevance') === 'relevance')>Öne çıkanlar</option>
                <option value="members" @selected(($sort ?? '') === 'members')>Üye sayısı</option>
                <option value="time" @selected(($sort ?? '') === 'time')>En yeniler</option>
            </select>
        </form>
        <div data-ografi-layout-section="categories_list" class="repost-category-grid">
            @forelse($categories as $category)
                <article class="card card-body repost-category-card">
                    <a href="{{ route('blog.category', $category->slug) }}" class="repost-category-card__title"><span class="repost-category-card__mark">{{ mb_substr($category->name, 0, 1) }}</span><span>{{ $category->name }}</span></a>
                    @if(filled($category->description))<p>{{ \Illuminate\Support\Str::limit(strip_tags($category->description), 115) }}</p>@endif
                    <div class="repost-category-card__meta"><span>{{ number_format((int) $category->posts_count) }} gönderi</span><span>{{ number_format((int) $category->followers_count) }} üye</span></div>
                    <a href="{{ route('blog.category', $category->slug) }}" class="repost-read-more">Topluluğa git</a>
                </article>
            @empty
                <div data-ografi-layout-section="categories_empty" class="card card-body repost-empty-state">Henüz topluluk oluşturulmamış.</div>
            @endforelse
        </div>
        <div class="repost-pagination">{{ $categories->links() }}</div>
    </div>
@endsection
