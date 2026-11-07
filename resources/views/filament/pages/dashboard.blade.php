<x-filament-panels::page>
    @php
        $admin = auth()->user();
        $stats = [
            ['label' => 'Üyeler', 'value' => \App\Models\User::query()->count(), 'description' => 'Kayıtlı hesaplar', 'icon' => 'heroicon-o-users', 'tone' => 'blue', 'url' => \App\Filament\Resources\Users\UserResource::getUrl()],
            ['label' => 'Yazılar', 'value' => \App\Models\Post::query()->count(), 'description' => 'Toplam içerik', 'icon' => 'heroicon-o-document-text', 'tone' => 'violet', 'url' => \App\Filament\Resources\Posts\PostResource::getUrl()],
            ['label' => 'Taslaklar', 'value' => \App\Models\Post::query()->where('is_published', false)->count(), 'description' => 'Yayın bekleyen içerik', 'icon' => 'heroicon-o-pencil-square', 'tone' => 'amber', 'url' => \App\Filament\Resources\Posts\PostResource::getUrl()],
            ['label' => 'Yorumlar', 'value' => \App\Models\Comment::query()->count(), 'description' => 'Topluluk yorumları', 'icon' => 'heroicon-o-chat-bubble-left-right', 'tone' => 'green', 'url' => \App\Filament\Resources\Comments\CommentResource::getUrl()],
            ['label' => 'Kanallar', 'value' => \App\Models\Channel::query()->count(), 'description' => 'İçerik kategorileri', 'icon' => 'heroicon-o-rectangle-stack', 'tone' => 'cyan', 'url' => \App\Filament\Resources\Channels\ChannelResource::getUrl()],
            ['label' => 'Etiketler', 'value' => \App\Models\Tag::query()->count(), 'description' => 'İçerik etiketleri', 'icon' => 'heroicon-o-hashtag', 'tone' => 'pink', 'url' => \App\Filament\Resources\Tags\TagResource::getUrl()],
            ['label' => 'Destek talepleri', 'value' => \App\Models\SupportTicket::query()->count(), 'description' => \App\Models\SupportTicket::query()->where('status', \App\Models\SupportTicket::STATUS_NEW)->count().' yeni talep', 'icon' => 'heroicon-o-lifebuoy', 'tone' => 'orange', 'url' => \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl()],
        ];
        $recentTickets = \App\Models\SupportTicket::query()->latest()->limit(4)->get();
    @endphp

    <style>
        .ogra-dashboard{--od-border:#e6eaf0;--od-muted:#718096;--od-ink:#172033;--od-blue:#1769f5;color:var(--od-ink);display:flex;flex-direction:column;gap:28px}
        .dark .ogra-dashboard{--od-border:#303846;--od-muted:#9aa6b8;--od-ink:#f3f6fb}
        .od-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:28px 30px;border:1px solid var(--od-border);border-radius:22px;background:linear-gradient(115deg,#fff 0%,#f7faff 68%,#edf4ff 100%)}
        .dark .od-hero{background:linear-gradient(115deg,#151b26,#17243a)}
        .od-hero:after{content:"";position:absolute;z-index:-1;width:260px;height:260px;right:8%;top:-165px;border:35px solid rgba(37,112,245,.08);border-radius:50%}
        .od-identity{display:flex;align-items:center;gap:17px;min-width:0}
        .od-avatar{width:64px;height:64px;flex:none;border-radius:19px;object-fit:cover;background:#fff;border:1px solid var(--od-border)}
        .od-eyebrow{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--od-blue);margin:0 0 5px}
        .od-welcome{font-size:25px;line-height:1.2;font-weight:750;letter-spacing:-.035em;margin:0;color:var(--od-ink)}
        .od-subtitle{font-size:14px;color:var(--od-muted);margin:7px 0 0}
        .od-actions{display:flex;flex-wrap:wrap;gap:10px}
        .od-button{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:11px 15px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;transition:background .15s,border-color .15s}
        .od-button svg{width:17px;height:17px}
        .od-button-primary{background:#1769f5;color:#fff!important;border:1px solid #1769f5}
        .od-button-primary:hover{background:#0757dc}
        .od-button-secondary{background:#fff;color:#334155!important;border:1px solid var(--od-border)}
        .od-button-secondary:hover{background:#f8fafc}
        .dark .od-button-secondary{background:#202938;color:#e5eaf2!important}
        .od-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:14px}
        .od-section-title{font-size:17px;font-weight:750;letter-spacing:-.02em;margin:0;color:var(--od-ink)}
        .od-section-note{font-size:13px;color:var(--od-muted);margin:5px 0 0}
        .od-live-label{font-size:11px;font-weight:650;color:#64748b;background:#f1f5f9;border-radius:20px;padding:6px 10px}
        .od-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
        .od-stat{display:block;min-width:0;padding:18px 19px 17px;background:#fff;border:1px solid var(--od-border);border-radius:17px;text-decoration:none!important;transition:transform .16s,border-color .16s,box-shadow .16s}
        .od-stat:hover{transform:translateY(-2px);border-color:#b9d2ff;box-shadow:0 9px 24px rgba(30,75,140,.07)}
        .dark .od-stat,.dark .od-panel{background:#171e29}
        .od-stat-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
        .od-stat-label{font-size:13px;font-weight:650;color:#64748b}
        .od-stat-icon{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:12px}
        .od-stat-icon svg{width:19px;height:19px}
        .od-stat-number{font-size:30px;font-weight:780;line-height:1.1;letter-spacing:-.04em;color:var(--od-ink);margin-top:14px}
        .od-stat-description{font-size:12px;color:var(--od-muted);margin-top:6px}
        .od-blue{background:#eaf2ff;color:#2563eb}.od-violet{background:#f1edff;color:#7955d9}.od-amber{background:#fff5df;color:#c17b08}.od-green{background:#e7f8ef;color:#15965b}.od-cyan{background:#e4f8fb;color:#07869b}.od-pink{background:#fff0f6;color:#d34e8c}.od-orange{background:#fff0e6;color:#dc6b25}
        .dark .od-blue{background:#1d3151}.dark .od-violet{background:#30264a}.dark .od-amber{background:#49391e}.dark .od-green{background:#193c30}.dark .od-cyan{background:#173a43}.dark .od-pink{background:#45263a}.dark .od-orange{background:#4a3022}
        .od-chart-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .od-chart{min-width:0;border:1px solid var(--od-border);border-radius:18px;background:#fff;padding:12px;overflow:hidden}
        .od-chart .fi-section{border:0!important;box-shadow:none!important;background:transparent!important}
        .od-chart .fi-section-header{padding:10px 10px 14px!important}
        .od-chart .fi-section-content{padding:8px 4px 4px!important}
        .od-chart canvas{max-height:270px}
        .od-bottom{display:grid;grid-template-columns:minmax(0,2fr) minmax(260px,1fr);gap:16px}
        .od-panel{border:1px solid var(--od-border);border-radius:18px;background:#fff;padding:21px}
        .od-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
        .od-panel-title{font-size:15px;font-weight:750;color:var(--od-ink);margin:0}
        .od-panel-note{font-size:12px;color:var(--od-muted);margin:5px 0 0}
        .od-text-link{font-size:12px;font-weight:700;color:var(--od-blue);text-decoration:none}
        .od-ticket-list{margin-top:14px}
        .od-ticket{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 0;border-top:1px solid #eef1f5;text-decoration:none}
        .dark .od-ticket{border-color:#303846}
        .od-ticket-main{min-width:0}
        .od-ticket-subject{font-size:13px;font-weight:650;color:var(--od-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .od-ticket-meta{font-size:11px;color:var(--od-muted);margin-top:4px}
        .od-status{font-size:11px;font-weight:700;padding:6px 9px;border-radius:20px;white-space:nowrap;background:#f1f5f9;color:#64748b}
        .od-status-new{background:#fff4dc;color:#a96a00}.od-status-reviewing{background:#eaf2ff;color:#2563eb}.od-status-replied{background:#e7f8ef;color:#16834f}
        .od-empty{padding:30px 10px;text-align:center;color:var(--od-muted);font-size:13px}
        .od-shortcuts{display:flex;flex-direction:column;gap:4px;margin-top:15px}
        .od-shortcut{display:flex;align-items:center;gap:11px;padding:11px 10px;border-radius:11px;color:#475569!important;text-decoration:none!important;font-size:13px;font-weight:600}
        .od-shortcut:hover{background:#f3f7ff;color:#155bd4!important}
        .od-shortcut svg{width:17px;height:17px;color:#8b98aa}
        .od-shortcut .od-chevron{margin-left:auto;width:15px}
        @media(max-width:1100px){.od-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:767px){.ogra-dashboard{gap:22px}.od-hero{align-items:flex-start;flex-direction:column;padding:21px}.od-welcome{font-size:21px}.od-avatar{width:54px;height:54px;border-radius:16px}.od-actions{width:100%}.od-button{flex:1}.od-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.od-stat{padding:14px}.od-stat-number{font-size:26px}.od-chart-grid,.od-bottom{grid-template-columns:1fr}.od-chart{padding:8px}.od-panel{padding:17px}}
        @media(max-width:380px){.od-stats{grid-template-columns:1fr}}
    </style>

    <div class="ogra-dashboard">
        <section class="od-hero">
            <div class="od-identity">
                <img src="{{ $admin->avatarUrl() }}" alt="" class="od-avatar">
                <div>
                    <p class="od-eyebrow">OgraFi yönetim paneli</p>
                    <h2 class="od-welcome">Merhaba, {{ $admin->displayName() }}</h2>
                    <p class="od-subtitle">Topluluğunun bugünkü durumuna genel bir bakış.</p>
                </div>
            </div>
            <div class="od-actions">
                <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl() }}" class="od-button od-button-primary">
                    <x-filament::icon icon="heroicon-o-document-plus" /> Yazıları yönet
                </a>
                <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl() }}" class="od-button od-button-secondary">
                    <x-filament::icon icon="heroicon-o-inbox" /> Destek kutusu
                </a>
            </div>
        </section>

        <section>
            <div class="od-section-head">
                <div><h3 class="od-section-title">Genel durum</h3><p class="od-section-note">OgraFi'deki içerik ve topluluk verileri.</p></div>
                <span class="od-live-label">Güncel veriler</span>
            </div>
            <div class="od-stats">
                @foreach ($stats as $stat)
                    <a href="{{ $stat['url'] }}" class="od-stat">
                        <div class="od-stat-top">
                            <span class="od-stat-label">{{ $stat['label'] }}</span>
                            <span class="od-stat-icon od-{{ $stat['tone'] }}"><x-filament::icon :icon="$stat['icon']" /></span>
                        </div>
                        <div class="od-stat-number">{{ number_format($stat['value']) }}</div>
                        <p class="od-stat-description">{{ $stat['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="od-section-head"><div><h3 class="od-section-title">Büyüme istatistikleri</h3><p class="od-section-note">Son 12 ayın aylık kayıt ve içerik hareketleri.</p></div></div>
            <div class="od-chart-grid">
                <div class="od-chart">@livewire(\App\Filament\Widgets\PostsGrowthChart::class)</div>
                <div class="od-chart">@livewire(\App\Filament\Widgets\ChannelsGrowthChart::class)</div>
                <div class="od-chart">@livewire(\App\Filament\Widgets\MembersGrowthChart::class)</div>
                <div class="od-chart">@livewire(\App\Filament\Widgets\SupportTicketsGrowthChart::class)</div>
            </div>
        </section>

        <section class="od-bottom">
            <div class="od-panel">
                <div class="od-panel-head">
                    <div><h3 class="od-panel-title">Son destek talepleri</h3><p class="od-panel-note">Kullanıcılardan gelen son mesajlar.</p></div>
                    <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl() }}" class="od-text-link">Tümünü gör</a>
                </div>
                <div class="od-ticket-list">
                    @forelse ($recentTickets as $ticket)
                        <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl('edit', ['record' => $ticket]) }}" class="od-ticket">
                            <div class="od-ticket-main">
                                <div class="od-ticket-subject">{{ $ticket->subject }}</div>
                                <div class="od-ticket-meta">{{ $ticket->name }} · {{ $ticket->created_at?->format('d.m.Y H:i') }}</div>
                            </div>
                            <span class="od-status {{ $ticket->status === 'new' ? 'od-status-new' : ($ticket->status === 'reviewing' ? 'od-status-reviewing' : ($ticket->status === 'replied' ? 'od-status-replied' : '')) }}">{{ match($ticket->status) { 'new' => 'Yeni', 'reviewing' => 'İnceleniyor', 'replied' => 'Yanıtlandı', 'resolved' => 'Çözüldü', default => $ticket->status } }}</span>
                        </a>
                    @empty
                        <div class="od-empty">Henüz destek talebi yok.</div>
                    @endforelse
                </div>
            </div>
            <div class="od-panel">
                <h3 class="od-panel-title">Hızlı erişim</h3>
                <p class="od-panel-note">Sık kullanılan yönetim alanları.</p>
                <div class="od-shortcuts">
                    @foreach ([
                        ['Etiketleri yönet', \App\Filament\Resources\Tags\TagResource::getUrl(), 'heroicon-o-hashtag'],
                        ['Kanalları yönet', \App\Filament\Resources\Channels\ChannelResource::getUrl(), 'heroicon-o-rectangle-stack'],
                        ['Üyeleri yönet', \App\Filament\Resources\Users\UserResource::getUrl(), 'heroicon-o-users'],
                        ['Yorumları yönet', \App\Filament\Resources\Comments\CommentResource::getUrl(), 'heroicon-o-chat-bubble-left-right'],
                    ] as [$label, $url, $icon])
                        <a href="{{ $url }}" class="od-shortcut"><x-filament::icon :icon="$icon" />{{ $label }}<x-filament::icon icon="heroicon-m-chevron-right" class="od-chevron" /></a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>