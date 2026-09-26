@extends('layouts.app')

@section('title', 'Site Sağlığı')

@section('content')
<style>
    .site-health-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 24px 16px 64px;
        font-family: Inter, ui-sans-serif, system-ui, sans-serif;
    }
    .site-health-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 22px;
    }
    .site-health-title {
        margin: 0 0 6px;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 500;
        color: #172033;
    }
    .site-health-muted { color: #64748b; }
    .site-health-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }
    .site-health-stat {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
    }
    .site-health-stat strong { display:block; font-size: 21px; color:#111827; margin-top:5px; }
    .site-health-table {
        width:100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .site-health-table th,
    .site-health-table td {
        padding: 12px 8px;
        text-align:left;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }
    .site-health-table th { color:#475569; font-weight:600; }
    .site-health-table td { color:#334155; }
    .site-health-badge {
        display:inline-flex;
        align-items:center;
        min-height:24px;
        padding:0 9px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
    }
    .site-health-badge--good { background:#ecfdf3; color:#15803d; }
    .site-health-badge--needs { background:#fff7ed; color:#c2410c; }
    .site-health-badge--poor { background:#fef2f2; color:#dc2626; }
    .site-health-badge--none { background:#f1f5f9; color:#64748b; }
    .site-health-metric {
        display:grid;
        grid-template-columns: 90px 1fr 100px;
        gap:12px;
        align-items:center;
        padding:12px 0;
        border-bottom:1px solid #e5e7eb;
    }
    .site-health-metric:last-child { border-bottom:0; }
    .site-health-bar {
        height:7px;
        border-radius:999px;
        background:#eef2f7;
        overflow:hidden;
        display:flex;
    }
    .site-health-bar span { height:100%; }
    .site-health-bar .good { background:#22c55e; }
    .site-health-bar .needs { background:#f59e0b; }
    .site-health-bar .poor { background:#ef4444; }
    .site-health-two {
        display:grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap:16px;
        margin-top:16px;
    }
    @media(max-width:760px) {
        .site-health-page { padding:14px 10px 40px; }
        .site-health-card { padding:16px; border-radius:14px; }
        .site-health-grid, .site-health-two { grid-template-columns:1fr; }
        .site-health-metric { grid-template-columns:70px 1fr 72px; }
        .site-health-table { min-width:600px; }
        .site-health-table-wrap { overflow-x:auto; }
    }
</style>

<div class="site-health-page">
    <div class="site-health-card">
        <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <h1 class="site-health-title">Site sağlığı</h1>
                <p class="site-health-muted" style="margin:0;font-size:13px;">
                    Ografi gerçek ziyaretçilerden Core Web Vitals verisi topluyor.
                    Son 28 günlük saha verisi.
                </p>
            </div>
            <div class="site-health-badge site-health-badge--good">HTTPS aktif</div>
        </div>

        <div class="site-health-grid">
            <div class="site-health-stat">
                <span class="site-health-muted" style="font-size:12px;">Toplam ölçüm</span>
                <strong>{{ number_format($allSamples) }}</strong>
            </div>
            <div class="site-health-stat">
                <span class="site-health-muted" style="font-size:12px;">HTTPS ölçümleri</span>
                <strong>{{ number_format($secureSamples) }}</strong>
            </div>
            <div class="site-health-stat">
                <span class="site-health-muted" style="font-size:12px;">Son veri</span>
                <strong style="font-size:16px;">{{ $lastSampleAt ? \Illuminate\Support\Carbon::parse($lastSampleAt)->diffForHumans() : 'Veri yok' }}</strong>
            </div>
        </div>
    </div>

    <div class="site-health-two">
        @foreach(['mobile' => 'Mobil', 'desktop' => 'Masaüstü'] as $deviceKey => $deviceLabel)
            <div class="site-health-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <div style="font-size:17px;font-weight:600;color:#172033;">{{ $deviceLabel }}</div>
                        <div class="site-health-muted" style="font-size:12px;">75. yüzdelik değer</div>
                    </div>
                    @php
                        $core = collect(['LCP', 'INP', 'CLS'])->map(fn($m) => $summary[$deviceKey][$m]['status'])->filter();
                        $overall = $core->contains('poor') ? 'poor' : ($core->contains('needs-improvement') ? 'needs-improvement' : ($core->isNotEmpty() ? 'good' : 'no-data'));
                    @endphp
                    <span class="site-health-badge site-health-badge--{{ $overall === 'good' ? 'good' : ($overall === 'needs-improvement' ? 'needs' : ($overall === 'poor' ? 'poor' : 'none')) }}">
                        {{ $overall === 'good' ? 'İyi' : ($overall === 'needs-improvement' ? 'İyileştirme gereken' : ($overall === 'poor' ? 'Yavaş' : 'Veri yok')) }}
                    </span>
                </div>

                @foreach(['LCP' => 'ms', 'INP' => 'ms', 'CLS' => ''] as $metric => $unit)
                    @php $item = $summary[$deviceKey][$metric]; @endphp
                    <div class="site-health-metric">
                        <div>
                            <strong style="font-size:13px;color:#172033;">{{ $metric }}</strong>
                            <div class="site-health-muted" style="font-size:11px;">{{ number_format($item['count']) }} örnek</div>
                        </div>
                        <div>
                            <div class="site-health-bar">
                                <span class="good" style="width:{{ $item['count'] ? ($item['good'] / $item['count']) * 100 : 0 }}%"></span>
                                <span class="needs" style="width:{{ $item['count'] ? ($item['needs'] / $item['count']) * 100 : 0 }}%"></span>
                                <span class="poor" style="width:{{ $item['count'] ? ($item['poor'] / $item['count']) * 100 : 0 }}%"></span>
                            </div>
                            <div class="site-health-muted" style="font-size:11px;margin-top:5px;">
                                İyi {{ number_format($item['good_percent'] ?? 0, 1) }}%
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <strong style="font-size:15px;color:#111827;">
                                {{ $item['p75'] === null ? 'Veri yok' : number_format($item['p75'], $metric === 'CLS' ? 3 : 0) . ' ' . $unit }}
                            </strong>
                            @if($item['count'])
                                <div class="site-health-badge site-health-badge--{{ $item['status'] === 'good' ? 'good' : ($item['status'] === 'needs-improvement' ? 'needs' : 'poor') }}" style="margin-top:4px;">
                                    {{ $item['status'] === 'good' ? 'İyi' : ($item['status'] === 'needs-improvement' ? 'İyileştirme gereken' : 'Yavaş') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="site-health-card" style="margin-top:16px;">
        <h2 style="margin:0;font-size:18px;font-weight:600;color:#172033;">Ölçüm alınan sayfalar</h2>
        <p class="site-health-muted" style="margin:5px 0 0;font-size:12px;">Son 28 günde en çok veri gönderen URL yolları.</p>
        <div class="site-health-table-wrap">
            <table class="site-health-table">
                <thead>
                    <tr>
                        <th>Sayfa</th>
                        <th>Cihaz</th>
                        <th>Ölçüm</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pageStats as $row)
                    <tr>
                        <td style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;">{{ $row->page_path }}</td>
                        <td>{{ $row->device_type === 'mobile' ? 'Mobil' : 'Masaüstü' }}</td>
                        <td>{{ number_format($row->samples) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="site-health-muted">Henüz veri toplanmadı. Siteyi kullanan ziyaretçiler geldikçe burada oluşacak.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="site-health-muted" style="font-size:11px;margin-top:12px;">
        Bu panel Search Console'un birebir verisi değildir; Ografi'nin kendi gerçek kullanıcı ölçümlerini gösterir.
        Google'ın Core Web Vitals eşikleri LCP 2.5s, INP 200ms ve CLS 0.1 seviyelerini "iyi" kabul eder.
    </div>
</div>
@endsection
