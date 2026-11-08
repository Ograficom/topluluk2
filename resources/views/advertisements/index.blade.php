@extends('layouts.app')

@section('title', 'Reklamlarım')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">Reklam yöneticisi</h1>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <p class="color-gray" style="margin:0">Yayınlanan reklamlarının erişimini, etkileşimini ve hedef kitlesini takip et.</p>
            <a class="btn btn-primary" href="{{ route('advertisements.create') }}">Reklam oluştur</a>
        </div>
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

            <details class="advertisement-analytics" style="margin-top:16px">
                <summary class="btn btn-secondary-ghost" style="display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:44px;padding:11px 16px;border:1px solid var(--border-color);border-radius:12px;cursor:pointer;list-style:none">
                    <span style="display:inline-flex;align-items:center;gap:9px"><x-repost-icon name="feed" size="18" /> İstatistikler</span>
                    <x-repost-icon name="chevron-down" size="16" />
                </summary>
                <div class="advertisement-analytics__content" style="padding-top:16px">
            @if ($ad->status === 'approved' && $ad->starts_at->isPast() && $ad->ends_at->isFuture())
            <div class="stats__counts" style="margin-top:0">
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

            <div class="ad-analytics-details">
                <section class="ad-analytics-panel card card-body">
                    <header class="ad-analytics-panel__head">
                        <div>
                            <h3 class="h5">Erişim ve etkileşim</h3>
                            <p class="small color-gray">Reklamın ayrıntılı performansı</p>
                        </div>
                        <span class="ad-analytics-panel__icon"><x-repost-icon name="feed" size="19" /></span>
                    </header>
                    <div class="ad-analytics-metrics">
                        <div class="ad-analytics-metric">
                            <span class="ad-analytics-metric__label">Ayrıntı görüntüleme</span>
                            <strong>{{ number_format($stats['detail_views']) }}</strong>
                        </div>
                        <div class="ad-analytics-metric">
                            <span class="ad-analytics-metric__label">Dış bağlantı tıklaması</span>
                            <strong>{{ number_format($stats['outbound_clicks']) }}</strong>
                        </div>
                        <div class="ad-analytics-metric">
                            <span class="ad-analytics-metric__label">Okuma (15 sn+)</span>
                            <strong>{{ number_format($stats['reads']) }}</strong>
                        </div>
                        <div class="ad-analytics-metric">
                            <span class="ad-analytics-metric__label">Toplam okuma süresi</span>
                            <strong>{{ gmdate('H:i:s', min($stats['read_seconds'], 86399)) }}</strong>
                        </div>
                        <div class="ad-analytics-metric">
                            <span class="ad-analytics-metric__label">Geri dönüş</span>
                            <strong>{{ number_format($stats['returns']) }}</strong>
                        </div>
                    </div>
                </section>

                <section class="ad-analytics-panel card card-body">
                    <header class="ad-analytics-panel__head">
                        <div>
                            <h3 class="h5">Reklamı kimler gördü?</h3>
                            <p class="small color-gray">Tekil oturumlara göre hedef kitle dağılımı</p>
                        </div>
                        <span class="ad-analytics-panel__icon ad-analytics-panel__icon--green"><x-repost-icon name="user" size="19" /></span>
                    </header>
                    <div class="ad-analytics-audience">
                        <div class="ad-analytics-audience__row">
                            <span class="ad-analytics-dot ad-analytics-dot--member"></span>
                            <span>Üyeler</span>
                            <strong>{{ number_format($stats['members']) }}</strong>
                            <span class="ad-analytics-audience__percent">%{{ $memberPercent }}</span>
                        </div>
                        <div class="ad-analytics-progress"><span class="ad-analytics-progress__member" style="width:{{ $memberPercent }}%"></span></div>
                        <div class="ad-analytics-audience__row">
                            <span class="ad-analytics-dot ad-analytics-dot--visitor"></span>
                            <span>Ziyaretçiler</span>
                            <strong>{{ number_format($stats['visitors']) }}</strong>
                            <span class="ad-analytics-audience__percent">%{{ $visitorPercent }}</span>
                        </div>
                        <div class="ad-analytics-progress"><span class="ad-analytics-progress__visitor" style="width:{{ $visitorPercent }}%"></span></div>
                    </div>
                </section>

                <section class="ad-analytics-panel card card-body">
                    <header class="ad-analytics-panel__head">
                        <div>
                            <h3 class="h5">Tıklayan kitle</h3>
                            <p class="small color-gray">Reklam bağlantısına tıklayanların dağılımı</p>
                        </div>
                        <span class="ad-analytics-panel__icon ad-analytics-panel__icon--orange"><x-repost-icon name="link" size="19" /></span>
                    </header>
                    <div class="ad-analytics-clicks">
                        <div class="ad-analytics-clicks__item">
                            <span class="ad-analytics-clicks__label"><span class="ad-analytics-dot ad-analytics-dot--member"></span>Üye tıklaması</span>
                            <strong>{{ number_format($stats['member_clicks']) }}</strong>
                            <span class="small color-gray">%{{ $memberClickPercent }}</span>
                        </div>
                        <div class="ad-analytics-clicks__item">
                            <span class="ad-analytics-clicks__label"><span class="ad-analytics-dot ad-analytics-dot--visitor"></span>Ziyaretçi tıklaması</span>
                            <strong>{{ number_format($stats['visitor_clicks']) }}</strong>
                            <span class="small color-gray">%{{ $visitorClickPercent }}</span>
                        </div>
                    </div>
                    @if ($stats['clicks'] > 0)
                        <div class="ad-analytics-progress ad-analytics-progress--clicks">
                            <span class="ad-analytics-progress__member" style="width:{{ $memberClickPercent }}%"></span>
                            <span class="ad-analytics-progress__visitor" style="width:{{ $visitorClickPercent }}%"></span>
                        </div>
                    @else
                        <p class="small color-gray ad-analytics-empty">Henüz tıklama verisi yok.</p>
                    @endif
                </section>

                <section class="ad-analytics-panel card card-body">
                    <header class="ad-analytics-panel__head">
                        <div>
                            <h3 class="h5">Reklam bütçesi</h3>
                            <p class="small color-gray">Toplam bütçe ve şu ana kadarki harcama</p>
                        </div>
                        <span class="ad-analytics-panel__icon ad-analytics-panel__icon--blue"><x-repost-icon name="list" size="19" /></span>
                    </header>
                    <div class="ad-analytics-budget">
                        <div class="ad-analytics-budget__value">
                            <span class="small color-gray">Harcanan</span>
                            <strong>{{ number_format((float) $ad->spent_amount, 2, ',', '.') }} ₺</strong>
                        </div>
                        <div class="ad-analytics-budget__value ad-analytics-budget__value--right">
                            <span class="small color-gray">Toplam bütçe</span>
                            <strong>{{ number_format((float) $ad->total_budget, 2, ',', '.') }} ₺</strong>
                        </div>
                    </div>
                    <div class="ad-analytics-progress ad-analytics-progress--budget">
                        <span style="width:{{ $ad->total_budget > 0 ? min(100, ((float) $ad->spent_amount / (float) $ad->total_budget) * 100) : 0 }}%"></span>
                    </div>
                    <p class="small color-gray ad-analytics-budget__foot">
                        Kalan bütçe: <strong>{{ number_format(max(0, (float) $ad->total_budget - (float) $ad->spent_amount), 2, ',', '.') }} ₺</strong>
                    </p>
                </section>
            </div>
            @else
                <section class="card card-body">
                    <h3 class="h5">İstatistikler henüz hazır değil</h3>
                    <p class="small color-gray">Gösterim, tıklama, okuma ve hedef kitle verileri reklam yayınlandığında burada toplanmaya başlayacak.</p>
                </section>
            @endif
                </div>
            </details>

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
