<x-filament-panels::page>
    <div class="admin-page-heading"><h1>Reklam yöneticisi</h1></div>

    <section class="admin-page-intro ad-manager-welcome">
        <div class="admin-page-intro-icon ad-manager-icon"><x-filament::icon icon="heroicon-o-megaphone" /></div>
        <div class="admin-page-intro-content">
            <p class="admin-page-intro-eyebrow ad-manager-eyebrow">OGRAFI YÖNETİM MERKEZİ</p>
            <h2>Reklam performansı</h2>
            <p>Yayınlanan reklamların erişimini, etkileşimini ve harcama durumunu tek ekrandan takip edin.</p>
        </div>
    </section>

    <div class="ad-stat-grid">
        <article class="ad-stat-card">
            <span class="ad-stat-icon blue"><x-filament::icon icon="heroicon-o-megaphone" /></span>
            <p>Yayınlanan reklam</p>
            <strong>{{ number_format($totals['ads'], 0, ',', '.') }}</strong>
            <small>{{ number_format($totals['active'], 0, ',', '.') }} şu anda yayında</small>
        </article>
        <article class="ad-stat-card">
            <span class="ad-stat-icon violet"><x-filament::icon icon="heroicon-o-eye" /></span>
            <p>Toplam gösterim</p>
            <strong>{{ number_format($totals['impressions'], 0, ',', '.') }}</strong>
            <small>Reklam kartlarının görüntülenmesi</small>
        </article>
        <article class="ad-stat-card">
            <span class="ad-stat-icon green"><x-filament::icon icon="heroicon-o-cursor-arrow-rays" /></span>
            <p>Toplam tıklama</p>
            <strong>{{ number_format($totals['clicks'], 0, ',', '.') }}</strong>
            <small>{{ number_format($totals['ctr'], 2, ',', '.') }}% tıklama oranı</small>
        </article>
        <article class="ad-stat-card">
            <span class="ad-stat-icon amber"><x-filament::icon icon="heroicon-o-banknotes" /></span>
            <p>Toplam harcama</p>
            <strong>{{ number_format($totals['spent'], 2, ',', '.') }} ₺</strong>
            <small>Reklam bütçelerinden kullanılan</small>
        </article>
        <article class="ad-stat-card">
            <span class="ad-stat-icon cyan"><x-filament::icon icon="heroicon-o-document-magnifying-glass" /></span>
            <p>Detay görüntüleme</p>
            <strong>{{ number_format($totals['detail_views'], 0, ',', '.') }}</strong>
            <small>Reklam detay sayfası ziyaretleri</small>
        </article>
        <article class="ad-stat-card">
            <span class="ad-stat-icon rose"><x-filament::icon icon="heroicon-o-book-open" /></span>
            <p>Okuma etkileşimi</p>
            <strong>{{ number_format($totals['reads'], 0, ',', '.') }}</strong>
            <small>En az 15 saniyelik okumalar</small>
        </article>
    </div>

    <section class="ad-analytics-panel">
        <div class="ad-panel-heading">
            <div>
                <h3>Son 14 günlük performans</h3>
                <p>Yayınlanan reklamların günlük gösterim ve tıklama sayıları</p>
            </div>
            <span class="ad-period-label">Son 14 gün</span>
        </div>
        <div class="ad-chart-legend">
            <span><i class="impression-dot"></i> Gösterim</span>
            <span><i class="click-dot"></i> Tıklama</span>
        </div>
        <div class="ad-bar-chart">
            @foreach ($daily as $day)
                <div class="ad-chart-day" title="{{ $day['label'] }} — {{ number_format($day['impressions']) }} gösterim, {{ number_format($day['clicks']) }} tıklama">
                    <div class="ad-bars">
                        <span class="ad-bar impression-bar" style="height: {{ max(2, ($day['impressions'] / $maxDaily) * 100) }}%"></span>
                        <span class="ad-bar click-bar" style="height: {{ max(2, ($day['clicks'] / $maxDaily) * 100) }}%"></span>
                    </div>
                    <small>{{ $day['label'] }}</small>
                </div>
            @endforeach
        </div>
        @if ($totals['impressions'] === 0 && $totals['clicks'] === 0)
            <p class="ad-chart-empty">Henüz gösterim veya tıklama verisi bulunmuyor. Reklamlar yayınlandıkça grafik burada güncellenecek.</p>
        @endif
    </section>

    <section class="ad-analytics-panel ad-list-panel">
        <div class="ad-panel-heading">
            <div>
                <h3>Yayınlanan reklamlar</h3>
                <p>Her reklamın performansını ayrı ayrı inceleyin.</p>
            </div>
            <span class="ad-count-label">{{ number_format($ads->count()) }} reklam</span>
        </div>
        @if ($ads->isEmpty())
            <div class="ad-empty-state">
                <x-filament::icon icon="heroicon-o-megaphone" />
                <strong>Henüz yayınlanan reklam yok</strong>
                <span>Onaylanan reklamlar ve istatistikleri burada listelenecek.</span>
            </div>
        @else
            <div class="ad-table-wrap">
                <table class="ad-performance-table">
                    <thead>
                        <tr>
                            <th>Reklam</th>
                            <th>Gösterim</th>
                            <th>Tıklama</th>
                            <th>Tıklama oranı</th>
                            <th>Detay</th>
                            <th>Harcama</th>
                            <th>Yayın dönemi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ads as $ad)
                            @php
                                $adClicks = (int) $ad->clicks + (int) $ad->card_clicks_count;
                                $adCtr = $ad->impressions > 0 ? round(($adClicks / $ad->impressions) * 100, 2) : 0;
                                $isLive = $ad->starts_at && $ad->ends_at && $ad->starts_at->lte(now()) && $ad->ends_at->gt(now());
                            @endphp
                            <tr>
                                <td>
                                    <div class="ad-name-cell">
                                        @if ($ad->imageUrl())
                                            <img src="{{ $ad->imageUrl() }}" alt="" loading="lazy">
                                        @else
                                            <span class="ad-thumb-placeholder"><x-filament::icon icon="heroicon-o-photo" /></span>
                                        @endif
                                        <span><strong>{{ $ad->title }}</strong><small>{{ $ad->user?->name ?? '—' }}</small></span>
                                    </div>
                                </td>
                                <td class="ad-number">{{ number_format($ad->impressions, 0, ',', '.') }}</td>
                                <td class="ad-number">{{ number_format($adClicks, 0, ',', '.') }}</td>
                                <td><span class="ad-ctr-pill">{{ number_format($adCtr, 2, ',', '.') }}%</span></td>
                                <td class="ad-number">{{ number_format($ad->detail_views_count, 0, ',', '.') }}</td>
                                <td class="ad-number">{{ number_format((float) $ad->spent_amount, 2, ',', '.') }} ₺</td>
                                <td><span class="ad-live-status {{ $isLive ? 'is-live' : 'is-ended' }}">{{ $isLive ? 'Yayında' : 'Süresi doldu / bekliyor' }}</span><small class="ad-date-range">{{ $ad->starts_at?->format('d.m.Y') }} – {{ $ad->ends_at?->format('d.m.Y') }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <style>
        .admin-page-heading { margin:0 0 18px; }
        .admin-page-heading h1 { margin:0; color:#111827; font-size:30px; font-weight:750; letter-spacing:-.035em; line-height:1.2; }
        .admin-page-intro { display:flex; align-items:center; gap:16px; min-height:118px; width:100%; margin:0 0 18px; padding:22px 24px; border:1px solid #dbe5f2; border-radius:15px; background:linear-gradient(110deg,#fff 0%,#f4f8ff 100%); }
        .admin-page-intro-icon { display:grid; place-items:center; flex:0 0 48px; width:48px; height:48px; border:1px solid #dbeafe; border-radius:13px; background:#eaf2ff; color:#2563eb; }
        .admin-page-intro-icon svg { width:24px; height:24px; }
        .admin-page-intro-content { min-width:0; }
        .admin-page-intro-eyebrow { margin:0 0 4px; color:#2563eb; font-size:10px; font-weight:800; letter-spacing:.09em; }
        .admin-page-intro h2 { margin:0; color:#172033; font-size:18px; font-weight:800; }
        .admin-page-intro p:last-child { margin:5px 0 0; color:#64748b; font-size:13px; line-height:1.6; }
        body:has(.admin-page-heading) .fi-header-heading { display:none !important; }
        .dark .admin-page-heading h1 { color:#f9fafb; }
        .dark .admin-page-intro { border-color:#303b4d; background:linear-gradient(110deg,#171e29 0%,#1a2637 100%); }
        .dark .admin-page-intro-icon { border-color:#34445b; background:#202c3e; color:#93c5fd; }
        .dark .admin-page-intro-eyebrow { color:#93c5fd; }
        .dark .admin-page-intro h2 { color:#f3f6fb; }
        .dark .admin-page-intro p:last-child { color:#a0aec0; }
        @media(max-width:640px) {
            .admin-page-heading { margin-bottom:14px; }
            .admin-page-heading h1 { font-size:25px; }
            .admin-page-intro { min-height:0; gap:12px; padding:16px; border-radius:13px; }
            .admin-page-intro-icon { flex-basis:44px; width:44px; height:44px; }
            .admin-page-intro-icon svg { width:22px; height:22px; }
            .admin-page-intro h2 { font-size:16px; }
            .admin-page-intro p:last-child { font-size:12px; }
        }
    </style>
    <style>
        .ad-manager-welcome{display:flex;align-items:center;gap:16px;padding:22px 24px;border:1px solid #dbe5f2;border-radius:15px;background:linear-gradient(110deg,#fff 0%,#f4f8ff 100%)}
        .ad-manager-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:13px;background:#eaf2ff;color:#2563eb}
        .ad-manager-icon svg{width:24px;height:24px}
        .ad-manager-eyebrow{margin:0 0 4px;color:#2563eb;font-size:10px;font-weight:800;letter-spacing:.09em}
        .ad-manager-welcome h2{margin:0;color:#172033;font-size:18px;font-weight:800}
        .ad-manager-welcome p:last-child{margin:5px 0 0;color:#64748b;font-size:13px;line-height:1.6}
        .ad-stat-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px}
        .ad-stat-card,.ad-analytics-panel{min-width:0;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
        .ad-stat-card{position:relative;padding:18px}
        .ad-stat-icon{display:grid;place-items:center;width:38px;height:38px;margin-bottom:13px;border-radius:11px}
        .ad-stat-icon svg{width:19px;height:19px}
        .ad-stat-icon.blue{background:#eff6ff;color:#2563eb}.ad-stat-icon.violet{background:#f3e8ff;color:#9333ea}.ad-stat-icon.green{background:#dcfce7;color:#16a34a}.ad-stat-icon.amber{background:#fef3c7;color:#d97706}.ad-stat-icon.cyan{background:#cffafe;color:#0891b2}.ad-stat-icon.rose{background:#ffe4e6;color:#e11d48}
        .ad-stat-card p{margin:0 0 5px;color:#64748b;font-size:13px;font-weight:600}
        .ad-stat-card strong{display:block;color:#111827;font-size:27px;font-weight:800;line-height:1.25;letter-spacing:-.03em}
        .ad-stat-card small{display:block;margin-top:7px;color:#94a3b8;font-size:11px}
        .ad-analytics-panel{margin-top:18px;padding:20px}
        .ad-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:15px;border-bottom:1px solid #edf0f4}
        .ad-panel-heading h3{margin:0;color:#172033;font-size:16px;font-weight:750}
        .ad-panel-heading p{margin:4px 0 0;color:#64748b;font-size:12px}
        .ad-period-label,.ad-count-label{padding:6px 10px;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:11px;font-weight:650;white-space:nowrap}
        .ad-chart-legend{display:flex;gap:18px;margin:16px 0 8px;color:#64748b;font-size:11px}
        .ad-chart-legend span{display:flex;align-items:center;gap:7px}.ad-chart-legend i{width:8px;height:8px;border-radius:3px}.impression-dot{background:#2563eb}.click-dot{background:#16a34a}
        .ad-bar-chart{display:grid;grid-template-columns:repeat(14,minmax(0,1fr));gap:8px;min-height:180px;padding:12px 2px 0}
        .ad-chart-day{display:flex;min-width:0;flex-direction:column;align-items:center;justify-content:flex-end;gap:9px}
        .ad-bars{display:flex;align-items:flex-end;justify-content:center;gap:3px;width:100%;height:145px;border-bottom:1px solid #e2e8f0;background:repeating-linear-gradient(to top,transparent 0,transparent 35px,#f1f5f9 36px)}
        .ad-bar{display:block;width:min(13px,38%);min-height:2px;border-radius:4px 4px 0 0}.impression-bar{background:#3b82f6}.click-bar{background:#16a34a}
        .ad-chart-day small{color:#94a3b8;font-size:10px;white-space:nowrap}
        .ad-chart-empty{margin:12px 0 0;color:#94a3b8;font-size:12px;text-align:center}
        .ad-list-panel{padding-bottom:5px}.ad-table-wrap{overflow-x:auto}
        .ad-performance-table{width:100%;border-collapse:collapse;white-space:nowrap}
        .ad-performance-table th{padding:12px 13px;background:#f8fafc;color:#64748b;font-size:11px;font-weight:700;text-align:left}
        .ad-performance-table td{padding:13px;border-top:1px solid #edf0f4;color:#334155;font-size:12px}
        .ad-name-cell{display:flex;align-items:center;gap:10px;min-width:190px}.ad-name-cell img,.ad-thumb-placeholder{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;object-fit:cover;border-radius:8px;background:#f1f5f9;color:#94a3b8}
        .ad-name-cell strong,.ad-name-cell small{display:block;max-width:220px;overflow:hidden;text-overflow:ellipsis}.ad-name-cell strong{color:#1e293b;font-size:12px}.ad-name-cell small,.ad-date-range{margin-top:3px;color:#94a3b8;font-size:10px}
        .ad-number{font-variant-numeric:tabular-nums;font-weight:650}
        .ad-ctr-pill{padding:5px 8px;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700}
        .ad-live-status{display:inline-block;padding:4px 7px;border-radius:6px;font-size:10px;font-weight:700}.ad-live-status.is-live{background:#dcfce7;color:#15803d}.ad-live-status.is-ended{background:#f1f5f9;color:#64748b}.ad-date-range{display:block}
        .ad-empty-state{display:flex;align-items:center;flex-direction:column;gap:8px;padding:38px 15px;color:#94a3b8;text-align:center}.ad-empty-state svg{width:30px;height:30px}.ad-empty-state strong{color:#475569;font-size:14px}.ad-empty-state span{font-size:12px}
        .dark .ad-manager-welcome,.dark .ad-stat-card,.dark .ad-analytics-panel{border-color:#374151;background:#18181b}
        .dark .ad-manager-welcome h2,.dark .ad-panel-heading h3,.dark .ad-stat-card strong,.dark .ad-name-cell strong{color:#f3f4f6}
        .dark .ad-manager-welcome p:last-child,.dark .ad-panel-heading p,.dark .ad-stat-card p,.dark .ad-stat-card small,.dark .ad-chart-legend,.dark .ad-performance-table td{color:#a1a1aa}
        .dark .ad-period-label,.dark .ad-count-label,.dark .ad-performance-table th{background:#27272a;color:#a1a1aa}
        .dark .ad-panel-heading{border-color:#374151}.dark .ad-performance-table td{border-color:#303039}.dark .ad-bars{border-color:#3f3f46;background:repeating-linear-gradient(to top,transparent 0,transparent 35px,#303039 36px)}
        .dark .ad-empty-state strong{color:#d4d4d8}
        @media(max-width:1024px){.ad-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.ad-manager-welcome{align-items:flex-start;padding:17px}.ad-manager-icon{flex-basis:42px;width:42px;height:42px}.ad-stat-grid{gap:9px}.ad-stat-card{padding:13px}.ad-stat-card strong{font-size:22px}.ad-stat-card small{font-size:10px}.ad-analytics-panel{padding:14px}.ad-bar-chart{gap:3px;min-height:150px}.ad-bars{height:115px}.ad-chart-day small{font-size:8px;transform:rotate(-35deg);transform-origin:top center}.ad-panel-heading h3{font-size:14px}}
    </style>
</x-filament-panels::page>