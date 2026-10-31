@extends('layouts.repost')

@section('title', 'Arama — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">ARAMA</span><h1>OgraFi'de ara</h1></div></div>
    <form method="GET" action="{{ route('search') }}" class="repost-sort-row">
        <input type="search" name="q" value="{{ $query }}" placeholder="Gönderi, üye veya topluluk ara" autofocus>
        <button type="submit" class="btn btn-primary btn-rounded">Ara</button>
    </form>
    @if($query === '')
        <div class="card card-body repost-empty-state">Aramak istediğin kelimeyi yaz.</div>
    @elseif($meta['too_short'] ?? false)
        <div class="card card-body repost-empty-state">Arama terimi en az {{ $meta['min_length'] }} karakter olmalı.</div>
    @else
        @foreach(['posts' => 'Gönderiler', 'categories' => 'Topluluklar', 'users' => 'Üyeler', 'tags' => 'Etiketler', 'comments' => 'Yorumlar', 'pages' => 'Sayfalar'] as $key => $label)
            @php($items = $results[$key] ?? [])
            @if(count($items))
                <section class="repost-discover-section">
                    <h2>{{ $label }}</h2>
                    <div class="repost-search-results">
                        @foreach($items as $item)
                            <a class="card card-body repost-search-result" href="{{ data_get($item, 'url', '#') }}">
                                <strong>{{ data_get($item, 'title', data_get($item, 'name', data_get($item, 'username', 'Sonuç'))) }}</strong>
                                <span class="meta">{{ \Illuminate\Support\Str::limit(strip_tags((string) data_get($item, 'excerpt', data_get($item, 'content', ''))), 130) }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
        @if(collect($results)->flatten(1)->isEmpty())
            <div class="card card-body repost-empty-state">“{{ $query }}” için sonuç bulunamadı.</div>
        @endif
    @endif
@endsection
