@extends('layouts.app')

@section('title', 'Reklamlarım')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">Reklam yöneticisi</h1>
        <p class="color-gray">Yayınlanan reklamlarının erişimini, etkileşimini ve hedef kitlesini takip et.</p>
        <a class="btn btn-primary" href="{{ route('advertisements.create') }}">Reklam oluştur</a>
    </header>

    @forelse ($ads as $ad)
        @php
            $stats = $ad->analytics;
            $audienceTotal = max(1, $stats['members'] + $stats['visitors']);
            $memberPercent = round(($stats['members'] / $audienceTotal) * 100);
            $visitorPercent = 100 - $memberPercent;
            $clickTotal = max(1, $stats['member_clicks'] + $stats['visitor_clicks']);
            $memberClickPercent = round(($stats['member_clicks'] / $clickTotal) * 100);
            $visitorClickPercent = 100 - $memberClickPercent;
        @endphp

        <section class="card card-body" style="margin-bottom:22px">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <span class="small color-gray">REKLAM PERFORMANSI</span>
                    <h2 class="h4" style="margin-top:5px">{{ $ad->title }}</h2>
                    <p class="small color-gray">{{ match($ad->status) {'pending' => 'Onay bekliyor','approved' => ($ad->ends_at->isPast() ? 'Süresi doldu' : 'Yayında / onaylandı'),'rejected' => 'Reddedildi','paused' => 'Durduruldu',default => $ad->status} }}</p>
                    <p class="small color-gray">Yayın: {{ $ad->starts_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }} – {{ $ad->ends_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</p>
                </div>
                <a class="btn btn-secondary-ghost btn-sm" href="{{ route('advertisements.edit', $ad) }}">Reklamı düzenle</a>
            </div>

            @include('advertisements._card', ['ad' => $ad, 'preview' => true])

            @if ($ad->status === 'approved' && $ad->starts_at->isPast() && $ad->ends_at->isFuture())
            <div class="stats__counts" style="margin-top:18px">
                <div class="stats__count-card stats__count-card--posts card card-body">
                    <div class="stats__count-head"><span class="stats__count-label small color-gray">Gösterim</span><span class="stats__count-icon"><x-repost-icon name="eye" size="18" /></span></div>
                    <span class="stats__count-num">{{ number_format($stats['impressions']) }}</span>
                </div>
                <div class="stats__count-card stats__count-card--comments card card-body">
                    <div class="stats__count-head"><span class="stats__count-label small color-gray">Tıklama</span><span class="stats__count-icon"><x-repost-icon name="link" size="18" /></span></div>
                    <span class="stats__count-num">{{ number_format($stats['clicks']) }}</span>
                </div>
                <div class="stats__count-card stats__count-card--users card card-body">
                    <div class="stats__count-head"><span class="stats__count-label small color-gray">Tıklama oranı</span><span class="stats__count-icon"><x-repost-icon name="bolt" size="18" /></span></div>
                    <span class="stats__count-num">%{{ number_format($stats['ctr'], 2, ',', '.') }}</span>
                </div>
            </div>

            <section class="stats__chart card card-body" style="margin-top:16px">
                <header class="stats__chart-head">
                    <div><h3 class="h5">Son 14 gün</h3><p class="small color-gray">Gösterim ve tıklama değişimi</p></div>
                </header>
                <div id="advertisement-chart-{{ $ad->id }}"
                    class="stats__chart-canvas stats__chart-canvas--wide"
                    data-labels='@json($stats['timeline']->pluck('date')->values())'
                    data-impressions='@json($stats['timeline']->pluck('impressions')->values())'
                    data-clicks='@json($stats['timeline']->pluck('clicks')->values())'></div>
            </section>

            <div class="stats__chart-grid" style="margin-top:16px">
                <section class="stats__chart card card-body">
                    <header class="stats__chart-head">
                        <div><h3 class="h5">Erişim ve etkileşim</h3><p class="small color-gray">Reklamın nasıl kullanıldığının özeti</p></div>
                    </header>
                    <div class="d-flex flex-wrap gap-3">
                        <div><strong>{{ number_format($stats['detail_views']) }}</strong><div class="small color-gray">Ayrıntı görüntüleme</div></div>
                        <div><strong>{{ number_format($stats['outbound_clicks']) }}</strong><div class="small color-gray">Dış bağlantı tıklaması</div></div>
                        <div><strong>{{ number_format($stats['reads']) }}</strong><div class="small color-gray">Okuma (15 sn+)</div></div>
                        <div><strong>{{ gmdate('H:i:s', min($stats['read_seconds'], 86399)) }}</strong><div class="small color-gray">Toplam okuma süresi</div></div>
                        <div><strong>{{ number_format($stats['returns']) }}</strong><div class="small color-gray">Dış bağlantı sonrası geri dönüş</div></div>
                    </div>
                </section>

                <section class="stats__chart card card-body">
                    <header class="stats__chart-head">
                        <div><h3 class="h5">Reklamı kimler gördü?</h3><p class="small color-gray">Tekil oturum bazında erişim</p></div>
                    </header>
                    <div class="d-flex justify-content-between small"><span>Üyeler</span><strong>{{ number_format($stats['members']) }} · %{{ $memberPercent }}</strong></div>
                    <div style="height:9px;background:var(--border-color,#eef0f5);border-radius:99px;overflow:hidden;margin:7px 0 14px"><div style="height:100%;width:{{ $memberPercent }}%;background:var(--primary,#0d6efd);border-radius:99px"></div></div>
                    <div class="d-flex justify-content-between small"><span>Ziyaretçiler</span><strong>{{ number_format($stats['visitors']) }} · %{{ $visitorPercent }}</strong></div>
                    <div style="height:9px;background:var(--border-color,#eef0f5);border-radius:99px;overflow:hidden;margin:7px 0 0"><div style="height:100%;width:{{ $visitorPercent }}%;background:var(--green,#17b548);border-radius:99px"></div></div>
                </section>
            </div>

            <section class="stats__chart card card-body" style="margin-top:16px">
                <header class="stats__chart-head">
                    <div><h3 class="h5">Tıklayan kitle</h3><p class="small color-gray">Üye ve ziyaretçi tıklamaları</p></div>
                </header>
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div><strong>{{ number_format($stats['member_clicks']) }}</strong><div class="small color-gray">Üye tıklaması</div></div>
                    <div><strong>{{ number_format($stats['visitor_clicks']) }}</strong><div class="small color-gray">Ziyaretçi tıklaması</div></div>
                    <div><strong>{{ number_format((float) $ad->total_budget, 2, ',', '.') }} ₺</strong><div class="small color-gray">Toplam bütçe</div></div>
                    <div><strong>{{ number_format((float) $ad->spent_amount, 2, ',', '.') }} ₺</strong><div class="small color-gray">Harcanan</div></div>
                </div>
                @if ($stats['clicks'] > 0)
                    <div style="display:flex;height:12px;overflow:hidden;border-radius:99px;background:var(--border-color,#eef0f5);margin-top:16px">
                        <span style="width:{{ $memberClickPercent }}%;background:var(--primary,#0d6efd)"></span>
                        <span style="width:{{ $visitorClickPercent }}%;background:var(--green,#17b548)"></span>
                    </div>
                    <div class="d-flex flex-wrap gap-3 small color-gray" style="margin-top:8px"><span>Üye %{{ $memberClickPercent }}</span><span>Ziyaretçi %{{ $visitorClickPercent }}</span></div>
                @else
                    <p class="small color-gray" style="margin-top:12px">Henüz tıklama verisi yok.</p>
                @endif
            </section>

            @else
                <section class="card card-body" style="margin-top:16px">
                    <h3 class="h5">Reklam istatistikleri</h3>
                    <p class="small color-gray">Gösterim, tıklama, okuma ve hedef kitle verileri reklam yayınlandığında burada toplanmaya başlayacak.</p>
                </section>
            @endif

            @if ($ad->rejection_reason)<p class="color-red small">Ret nedeni: {{ $ad->rejection_reason }}</p>@endif
        </section>
    @empty
        <section class="card card-body empty-state"><h2 class="h5">Henüz reklamın yok</h2><p class="color-gray">İlk reklam başvurunu oluşturarak başlayabilirsin.</p><a class="btn btn-primary" href="{{ route('advertisements.create') }}">Reklam oluştur</a></section>
    @endforelse
    @include('layouts._pagination', ['paginator' => $ads])
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof ApexCharts === 'undefined') return;
    document.querySelectorAll('[id^="advertisement-chart-"]').forEach((element) => {
        const labels = JSON.parse(element.dataset.labels || '[]');
        const impressions = JSON.parse(element.dataset.impressions || '[]');
        const clicks = JSON.parse(element.dataset.clicks || '[]');
        const styles = getComputedStyle(document.documentElement);
        const textColor = styles.getPropertyValue('--text-color').trim() || '#212529';
        const mutedColor = styles.getPropertyValue('--muted').trim() || '#64748b';
        const borderColor = styles.getPropertyValue('--border-color').trim() || '#eef0f5';
        const primaryColor = styles.getPropertyValue('--primary').trim() || '#0d6efd';
        const greenColor = styles.getPropertyValue('--green').trim() || '#17b548';
        new ApexCharts(element, {
            chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'Inter, system-ui, sans-serif' },
            series: [{ name: 'Gösterim', data: impressions }, { name: 'Tıklama', data: clicks }],
            colors: [primaryColor, greenColor],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.04, stops: [0, 90, 100] } },
            grid: { borderColor, strokeDashArray: 4 },
            legend: { labels: { colors: textColor } },
            xaxis: { categories: labels, labels: { style: { colors: mutedColor } }, axisBorder: { color: borderColor }, axisTicks: { color: borderColor } },
            yaxis: { min: 0, labels: { style: { colors: mutedColor } } },
            tooltip: { theme: document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light' }
        }).render();
    });
});
</script>
@endpush
