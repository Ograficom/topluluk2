@extends('layouts.repost')

@section('title', 'Keşfet — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">KEŞFET</span><h1>Yeni şeyler keşfet</h1><p>Öne çıkan üyeler, topluluklar ve gönderiler.</p></div></div>
    @if($featuredCommunities->isNotEmpty())
        <section class="repost-discover-section"><h2>Öne çıkan topluluklar</h2><div class="repost-category-grid">
            @foreach($featuredCommunities as $community)
                <a class="card card-body repost-category-card" href="{{ route('blog.category', $community->slug) }}"><strong>{{ $community->name }}</strong><span class="meta">{{ number_format((int) ($community->followers_count ?? 0)) }} üye</span></a>
            @endforeach
        </div></section>
    @endif
    @if($featuredUsers->isNotEmpty())
        <section class="repost-discover-section"><h2>Öne çıkan üyeler</h2><div class="repost-people-grid">
            @foreach($featuredUsers as $person)
                <a class="card card-body repost-person-card" href="{{ route('users.show', $person->username) }}"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="58" height="58"></span><strong>{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></a>
            @endforeach
        </div></section>
    @endif
    <section class="repost-discover-section"><h2>Önerilen gönderiler</h2>
        @forelse($recommendedPosts as $post)
            @include('components.repost-post-card', ['post' => $post])
        @empty
            <div class="card card-body repost-empty-state">Şu anda önerilecek gönderi yok.</div>
        @endforelse
    </section>
@endsection
