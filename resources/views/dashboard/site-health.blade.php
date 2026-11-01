@extends('layouts.repost')

@section('title', 'Site sağlığı — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">YÖNETİM</span><h1>Site performansı</h1><p>Son 28 gündeki Core Web Vitals ölçümleri.</p></div></div>
    <div class="card card-body repost-health-summary"><strong>{{ number_format((int) $allSamples) }}</strong><span class="meta">Toplam ölçüm</span><span class="meta">Güvenli bağlantı: {{ number_format((int) $secureSamples) }} · Güvensiz: {{ number_format((int) $insecureSamples) }}</span><span class="meta">Son ölçüm: {{ $lastSampleAt ? \Illuminate\Support\Carbon::parse($lastSampleAt)->diffForHumans() : 'Henüz yok' }}</span></div>
    @foreach($summary as $device => $metrics)
        <section class="repost-discover-section"><h2>{{ $device === 'mobile' ? 'Mobil' : 'Masaüstü' }}</h2><div class="repost-health-grid">
            @foreach($metrics as $name => $data)
                <article class="card card-body"><strong>{{ $name }}</strong><span class="meta">Örnek: {{ $data['count'] }}</span><span>75. yüzdelik: {{ $data['p75'] ?? '—' }}</span><span class="meta">İyi: {{ $data['good'] }} · Geliştirilmeli: {{ $data['needs'] }} · Zayıf: {{ $data['poor'] }}</span></article>
            @endforeach
        </div></section>
    @endforeach
    <section class="card card-body"><h2>En çok ölçüm alınan sayfalar</h2>@foreach($pageStats as $stat)<p>{{ $stat->page_path }} — {{ $stat->device_type }} — {{ $stat->samples }} ölçüm</p>@endforeach</section>
@endsection
