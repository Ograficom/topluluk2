@extends('layouts.repost')

@section('title', isset($category) ? $category->name . ' — OgraFi' : 'Gönderiler — OgraFi')
@section('meta_description', isset($category) ? $category->name . ' topluluğundaki gönderiler.' : 'OgraFi topluluğundaki gönderiler.')

@section('content')
    <div class="repost-feed-heading">
        <div>
            <span class="repost-eyebrow">{{ isset($category) ? 'TOPLULUK' : 'GÖNDERİLER' }}</span>
            <h1>{{ $category->name ?? 'Gönderiler' }}</h1>
            @if(isset($category))
                <p>{{ number_format((int) ($categoryPostsCount ?? 0)) }} gönderi · {{ number_format((int) ($followersCount ?? 0)) }} üye</p>
            @endif
        </div>
        @if(isset($category))
            @auth
                <form method="POST" action="{{ route('blog.category.join', $category->slug) }}">
                    @csrf
                    <button class="btn {{ !empty($isCategoryJoined) ? 'btn-secondary' : 'btn-primary' }} btn-rounded" type="submit">
                        {{ !empty($isCategoryJoined) ? 'Topluluktan ayrıl' : 'Topluluğa katıl' }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-rounded">Katıl</a>
            @endauth
        @else
            <a href="{{ route('blog.categories') }}" class="btn btn-secondary btn-rounded">Toplulukları keşfet</a>
        @endif
    </div>

    @if(isset($category) && filled($category->description))
        <div class="card card-body repost-category-description">{{ $category->description }}</div>
    @endif

    @forelse($posts as $post)
        @include('components.repost-post-card', ['post' => $post])
    @empty
        <div class="card card-body repost-empty-state">
            <h2>Burada henüz gönderi yok</h2>
            <p>Bu alandaki ilk paylaşımı sen yapabilirsin.</p>
        </div>
    @endforelse

    @if(method_exists($posts, 'links'))
        <div class="repost-pagination">{{ $posts->links() }}</div>
    @endif
@endsection
