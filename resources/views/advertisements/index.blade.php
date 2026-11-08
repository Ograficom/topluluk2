@extends('layouts.app')

@section('title', 'Reklamlarım')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">Reklamlarım</h1>
        <p class="color-gray">Reklam başvurularını, yayın sürelerini ve performansını takip et.</p>
        <a class="btn btn-primary" href="{{ route('advertisements.create') }}">Reklam oluştur</a>
    </header>

    @forelse ($ads as $ad)
        @include('advertisements._card', ['ad' => $ad, 'preview' => true])
        <section class="card card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h2 class="h5">{{ $ad->title }}</h2>
                    <p class="color-gray">{{ match($ad->status) {'pending' => 'Onay bekliyor','approved' => ($ad->ends_at->isPast() ? 'Süresi doldu' : 'Onaylandı'),'rejected' => 'Reddedildi','paused' => 'Durduruldu',default => $ad->status} }}</p>
                </div>
                <span class="badge">{{ $ad->status }}</span>
            </div>
            <p class="small color-gray">Yayın: {{ $ad->starts_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }} – {{ $ad->ends_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</p>
            <div class="d-flex flex-wrap gap-2 mt-3"><a class="btn btn-secondary-ghost btn-sm" href="{{ route('advertisements.edit', $ad) }}"><x-repost-icon name="edit" size="14" /> Reklamı düzenle</a><a class="btn btn-secondary-ghost btn-sm" href="{{ route('advertisements.show', $ad) }}">Ayrıntıları görüntüle</a></div>
            <div class="d-flex flex-wrap gap-3 small">
                <span>{{ number_format($ad->impressions) }} gösterim</span>
                <span>{{ number_format($ad->clicks) }} tıklama</span>
                <span>CTR: %{{ number_format($ad->click_through_rate, 2) }}</span>
                <span>Bütçe: {{ number_format((float)$ad->total_budget, 2, ',', '.') }} ₺</span>
                <span>Harcanan: {{ number_format((float)$ad->spent_amount, 2, ',', '.') }} ₺</span>
            </div>
            @if ($ad->rejection_reason)<p class="color-red small">Ret nedeni: {{ $ad->rejection_reason }}</p>@endif
        </section>
    @empty
        <section class="card card-body empty-state"><h2 class="h5">Henüz reklamın yok</h2><p class="color-gray">İlk reklam başvurunu oluşturarak başlayabilirsin.</p></section>
    @endforelse
    @include('layouts._pagination', ['paginator' => $ads])
@endsection
