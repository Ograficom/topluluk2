@extends('layouts.repost')

@section('title', 'Reklam talebi — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">REKLAM TALEBİ</span><h1>Talep oluşturuldu</h1><p>Ödeme ve yayın süreci için talep bilgilerin.</p></div></div>
    <section class="card card-body repost-settings-form">
        <p><strong>Talep numarası:</strong> #{{ $adOrder->id }}</p>
        <p><strong>Alan:</strong> {{ $placement['label'] ?? $adOrder->placement }}</p>
        <p><strong>Süre:</strong> {{ $adOrder->duration_days }} gün</p>
        <p><strong>Tutar:</strong> {{ number_format($adOrder->price_cents / 100, 2, ',', '.') }} {{ $adOrder->currency }}</p>
        <p class="meta">Ödeme sağlayıcısı bağlantısı henüz bu ekrana bağlanmadı. Talebin ödeme bekliyor durumunda tutuluyor.</p>
        <a href="{{ route('home') }}" class="btn btn-secondary btn-rounded">Ana sayfaya dön</a>
    </section>
@endsection
