@extends('layouts.repost')

@section('title', 'Tüm tepkiler — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">TOPLULUK</span><h1>Tüm tepkiler</h1><p>Gönderilerde kullanılabilen aktif tepkiler.</p></div></div>
    <div class="repost-reaction-grid">
        @forelse($reactionTypes as $reaction)
            <div class="card card-body repost-reaction-tile">@if($reaction->gif_url)<img src="{{ $reaction->gif_url }}" alt="">@else<span>{{ $reaction->emoji }}</span>@endif<strong>{{ $reaction->label }}</strong></div>
        @empty
            <div class="card card-body repost-empty-state">Henüz aktif tepki yok.</div>
        @endforelse
    </div>
@endsection
