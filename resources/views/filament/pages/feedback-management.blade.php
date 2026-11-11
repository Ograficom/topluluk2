<x-filament-panels::page>
    @php
        $reports = \\App\\Models\\PostReport::query();
        $tickets = \\App\\Models\\SupportTicket::query();

        $stats = [
            ['label' => 'Toplam şikayet', 'value' => (clone $reports)->count(), 'description' => 'Bildirilen tüm yazılar', 'icon' => 'heroicon-o-flag', 'tone' => 'red', 'url' => \\App\\Filament\\Resources\\PostReports\\PostReportResource::getUrl()],
            ['label' => 'Bekleyen şikayetler', 'value' => (clone $reports)->where('status', 'pending')->count(), 'description' => 'İnceleme bekliyor', 'icon' => 'heroicon-o-clock', 'tone' => 'amber', 'url' => \\App\\Filament\\Resources\\PostReports\\PostReportResource::getUrl()],
            ['label' => 'İncelenen şikayetler', 'value' => (clone $reports)->where('status', 'reviewing')->count(), 'description' => 'İşlem devam ediyor', 'icon' => 'heroicon-o-eye', 'tone' => 'blue', 'url' => \\App\\Filament\\Resources\\PostReports\\PostReportResource::getUrl()],
            ['label' => 'Sonuçlanan şikayetler', 'value' => (clone $reports)->whereIn('status', ['resolved', 'rejected'])->count(), 'description' => 'İşlem tamamlandı', 'icon' => 'heroicon-o-check-circle', 'tone' => 'green', 'url' => \\App\\Filament\\Resources\\PostReports\\PostReportResource::getUrl()],
            ['label' => 'Toplam destek talebi', 'value' => (clone $tickets)->count(), 'description' => 'Gelen tüm başvurular', 'icon' => 'heroicon-o-lifebuoy', 'tone' => 'violet', 'url' => \\App\\Filament\\Resources\\SupportTickets\\SupportTicketResource::getUrl()],
            ['label' => 'Yeni destek talepleri', 'value' => (clone $tickets)->where('status', \\App\\Models\\SupportTicket::STATUS_NEW)->count(), 'description' => 'Yanıt bekleyen talepler', 'icon' => 'heroicon-o-inbox', 'tone' => 'amber', 'url' => \\App\\Filament\\Resources\\SupportTickets\\SupportTicketResource::getUrl()],
            ['label' => 'İncelenen destekler', 'value' => (clone $tickets)->where('status', \\App\\Models\\SupportTicket::STATUS_REVIEWING)->count(), 'description' => 'Üzerinde çalışılıyor', 'icon' => 'heroicon-o-eye', 'tone' => 'blue', 'url' => \\App\\Filament\\Resources\\SupportTickets\\SupportTicketResource::getUrl()],
            ['label' => 'Yanıtlanan / çözülen', 'value' => (clone $tickets)->whereIn('status', [\\App\\Models\\SupportTicket::STATUS_REPLIED, \\App\\Models\\SupportTicket::STATUS_RESOLVED])->count(), 'description' => 'İşlemi tamamlanan talepler', 'icon' => 'heroicon-o-check-circle', 'tone' => 'green', 'url' => \\App\\Filament\\Resources\\SupportTickets\\SupportTicketResource::getUrl()],
        ];

        $recentReports = \\App\\Models\\PostReport::query()->with(['post', 'user'])->latest()->limit(5)->get();
        $recentTickets = \\App\\Models\\SupportTicket::query()->latest()->limit(5)->get();
        $totalOpen = (clone $reports)->whereIn('status', ['pending', 'reviewing'])->count()
            + (clone $tickets)->whereIn('status', [\\App\\Models\\SupportTicket::STATUS_NEW, \\App\\Models\\SupportTicket::STATUS_REVIEWING])->count();
    @endphp

    <style>
        .fb-dashboard{--fb-border:#e4e9f1;--fb-muted:#718096;--fb-ink:#172033;display:flex;flex-direction:column;gap:25px;color:var(--fb-ink)}
        .dark .fb-dashboard{--fb-border:#303846;--fb-muted:#9aa6b8;--fb-ink:#f3f6fb}
        .fb-overview{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 22px;border:1px solid var(--fb-border);border-radius:16px;background:#fff}
        .dark .fb-overview,.dark .fb-stat,.dark .fb-panel,.dark .fb-chart{background:#171e29}
        .fb-overview h2{margin:0;color:var(--fb-ink);font-size:16px;font-weight:800}
        .fb-overview p{margin:5px 0 0;color:var(--fb-muted);font-size:12px}
        .fb-open-total{display:flex;align-items:center;gap:9px;flex:none;padding:9px 12px;border-radius:10px;background:#fff7ed;color:#c2410c;font-size:12px;font-weight:800}
        .fb-open-total svg{width:17px;height:17px}
        .fb-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:12px}
        .fb-section-head h3{margin:0;color:var(--fb-ink);font-size:16px;font-weight:800}
        .fb-section-head p{margin:5px 0 0;color:var(--fb-muted);font-size:12px}
        .fb-live{padding:6px 10px;border-radius:20px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:800;white-space:nowrap}
        .fb-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
        .fb-stat{display:block;min-width:0;padding:16px 17px;border:1px solid var(--fb-border);border-radius:15px;background:#fff;text-decoration:none!important;transition:border-color .15s,transform .15s}
        .fb-stat:hover{transform:translateY(-2px);border-color:#b9d2ff}
        .fb-stat-top{display:flex;align-items:center;justify-content:space-between;gap:9px}
        .fb-stat-label{color:#64748b;font-size:11px;font-weight:750}
        .fb-stat-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px}
        .fb-stat-icon svg{width:17px;height:17px}
        .fb-stat-value{margin-top:12px;color:var(--fb-ink);font-size:28px;font-weight:850;letter-spacing:-.04em;line-height:1.1}
        .fb-stat-desc{margin:6px 0 0;color:var(--fb-muted);font-size:10px}
        .fb-red{background:#fff0f0;color:#dc2626}.fb-amber{background:#fff5df;color:#c17b08}.fb-blue{background:#eaf2ff;color:#2563eb}.fb-green{background:#e7f8ef;color:#15965b}.fb-violet{background:#f1edff;color:#7955d9}
        .dark .fb-red{background:#452426}.dark .fb-amber{background:#49391e}.dark .fb-blue{background:#1d3151}.dark .fb-green{background:#193c30}.dark .fb-violet{background:#30264a}
        .fb-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .fb-chart{min-width:0;overflow:hidden;padding:12px;border:1px solid var(--fb-border);border-radius:16px;background:#fff}
        .fb-chart .fi-section{border:0!important;box-shadow:none!important;background:transparent!important}
        .fb-chart .fi-section-header{padding:9px 9px 12px!important}
        .fb-chart .fi-section-content{padding:7px 4px 4px!important}
        .fb-chart canvas{max-height:260px}
        .fb-panels{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .fb-panel{min-width:0;padding:18px;border:1px solid var(--fb-border);border-radius:16px;background:#fff}
        .fb-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
        .fb-panel-head h3{margin:0;color:var(--fb-ink);font-size:14px;font-weight:800}
        .fb-panel-head p{margin:5px 0 0;color:var(--fb-muted);font-size:11px}
        .fb-panel-link{color:#1769f5;font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
        .fb-list{margin-top:12px}
        .fb-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 0;border-top:1px solid #eef1f5}
        .dark .fb-row{border-color:#303846}
        .fb-row-main{min-width:0}
        .fb-row-title{overflow:hidden;color:var(--fb-ink);font-size:12px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
        .fb-row-meta{margin-top:4px;color:var(--fb-muted);font-size:10px}
        .fb-pill{flex:none;padding:5px 8px;border-radius:20px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:750}
        .fb-empty{padding:22px 8px;text-align:center;color:var(--fb-muted);font-size:12px}
        @media(max-width:1000px){.fb-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:700px){.fb-dashboard{gap:20px}.fb-overview{align-items:flex-start;flex-direction:column;padding:16px}.fb-stats,.fb-charts,.fb-panels{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.fb-stat{padding:13px}.fb-stat-value{font-size:25px}.fb-panel{padding:14px}}
        @media(max-width:480px){.fb-stats,.fb-charts,.fb-panels{grid-template-columns:1fr}.fb-section-head{align-items:flex-start;flex-direction:column}}
    </style>

    <div class="fb-dashboard" wire:poll.60s>
        <section class="fb-overview">
            <div>
                <h2>Şikayet ve destek merkezi</h2>
                <p>Yazı bildirimlerini ve kullanıcı destek taleplerini tek ekrandan takip edin.</p>
            </div>
            <div class="fb-open-total">
                <x-filament::icon icon="heroicon-o-exclamation-circle" />
                {{ number_format($totalOpen) }} açık işlem
            </div>
        </section>

        <section>
            <div class="fb-section-head">
                <div><h3>Genel durum</h3><p>Şikayet ve destek kayıtlarının güncel özeti.</p></div>
                <span class="fb-live">● CANLI · 60 SN'DE YENİLENİR</span>
            </div>
            <div class="fb-stats">
                @foreach ($stats as $stat)
                    <a href="{{ $stat['url'] }}" class="fb-stat">
                        <div class="fb-stat-top">
                            <span class="fb-stat-label">{{ $stat['label'] }}</span>
                            <span class="fb-stat-icon fb-{{ $stat['tone'] }}"><x-filament::icon :icon="$stat['icon']" /></span>
                        </div>
                        <div class="fb-stat-value">{{ number_format($stat['value']) }}</div>
                        <p class="fb-stat-desc">{{ $stat['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="fb-section-head"><div><h3>Başvuru istatistikleri</h3><p>Son 12 ayda gelen şikayet ve destek talepleri.</p></div></div>
            <div class="fb-charts">
                <div class="fb-chart">@livewire(\\App\\Filament\\Widgets\\PostReportsGrowthChart::class)</div>
                <div class="fb-chart">@livewire(\\App\\Filament\\Widgets\\SupportTicketsGrowthChart::class)</div>
            </div>
        </section>

        <section class="fb-panels">
            <div class="fb-panel">
                <div class="fb-panel-head">
                    <div><h3>Son yazı şikayetleri</h3><p>En son gönderilen bildirimler.</p></div>
                    <a class="fb-panel-link" href="{{ \\App\\Filament\\Resources\\PostReports\\PostReportResource::getUrl() }}">Tümünü gör</a>
                </div>
                <div class="fb-list">
                    @forelse ($recentReports as $report)
                        <div class="fb-row">
                            <div class="fb-row-main">
                                <div class="fb-row-title">{{ $report->post?->title ?? 'Silinmiş yazı' }}</div>
                                <div class="fb-row-meta">{{ $report->user?->displayName() ?? 'Silinmiş kullanıcı' }} · {{ $report->created_at?->format('d.m.Y H:i') }}</div>
                            </div>
                            <span class="fb-pill">{{ match($report->status) {'pending' => 'Bekliyor', 'reviewing' => 'İnceleniyor', 'resolved' => 'İşlem yapıldı', 'rejected' => 'Reddedildi', default => $report->status} }}</span>
                        </div>
                    @empty
                        <div class="fb-empty">Henüz yazı şikayeti yok.</div>
                    @endforelse
                </div>
            </div>

            <div class="fb-panel">
                <div class="fb-panel-head">
                    <div><h3>Son destek talepleri</h3><p>Kullanıcılardan gelen son başvurular.</p></div>
                    <a class="fb-panel-link" href="{{ \\App\\Filament\\Resources\\SupportTickets\\SupportTicketResource::getUrl() }}">Tümünü gör</a>
                </div>
                <div class="fb-list">
                    @forelse ($recentTickets as $ticket)
                        <div class="fb-row">
                            <div class="fb-row-main">
                                <div class="fb-row-title">{{ $ticket->subject }}</div>
                                <div class="fb-row-meta">{{ $ticket->name }} · {{ $ticket->created_at?->format('d.m.Y H:i') }}</div>
                            </div>
                            <span class="fb-pill">{{ match($ticket->status) {'new' => 'Yeni', 'reviewing' => 'İnceleniyor', 'replied' => 'Yanıtlandı', 'resolved' => 'Çözüldü', default => $ticket->status} }}</span>
                        </div>
                    @empty
                        <div class="fb-empty">Henüz destek talebi yok.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>