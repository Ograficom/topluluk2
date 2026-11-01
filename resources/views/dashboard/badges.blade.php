@extends('layouts.repost')

@section('title', 'Rozetler — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">TOPLULUK KATKISI</span><h1>Rozetlerim</h1><p>Katkıların ve topluluk içindeki ilerlemen.</p></div></div>
    <section class="card card-body repost-badge-summary"><strong>{{ number_format((int) $badgePoints) }} puan</strong><span class="meta">Toplam rozet puanın</span></section>
    <div class="repost-badge-grid">
        @forelse($badges as $badge)
            <article class="card card-body"><strong>{{ $badge->name ?? $badge->title ?? 'Rozet' }}</strong><span class="meta">{{ $badge->description ?? '' }}</span></article>
        @empty
            <div class="card card-body repost-empty-state">Henüz rozet kazanmadın.</div>
        @endforelse
    </div>
    @if($nextBadge)
        <p class="meta" style="margin-top:14px">Sıradaki rozet: {{ $nextBadge->name ?? $nextBadge->title ?? 'Yeni rozet' }}</p>
    @endif
@endsection
