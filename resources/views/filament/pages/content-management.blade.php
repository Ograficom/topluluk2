<x-filament-panels::page>
    @php
        $admin = auth()->user();

        $postsQuery = \App\Models\Post::query();
        $stats = [
            ['label' => 'Postlar', 'value' => (clone $postsQuery)->count(), 'description' => 'Toplam içerik', 'icon' => 'heroicon-o-document-text', 'tone' => 'blue', 'url' => \App\Filament\Resources\Posts\PostResource::getUrl()],
            ['label' => 'Yayındaki postlar', 'value' => (clone $postsQuery)->published()->count(), 'description' => 'Ziyaretçilere açık içerikler', 'icon' => 'heroicon-o-check-circle', 'tone' => 'green', 'url' => \App\Filament\Resources\Posts\PostResource::getUrl()],
            ['label' => 'Taslaklar', 'value' => (clone $postsQuery)->where('is_published', false)->count(), 'description' => 'Yayın bekleyen içerikler', 'icon' => 'heroicon-o-pencil-square', 'tone' => 'amber', 'url' => \App\Filament\Resources\Posts\PostResource::getUrl()],
            ['label' => 'Kanallar', 'value' => \App\Models\Channel::query()->count(), 'description' => 'İçerik kategorileri', 'icon' => 'heroicon-o-rectangle-stack', 'tone' => 'cyan', 'url' => \App\Filament\Resources\Channels\ChannelResource::getUrl()],
            ['label' => 'Etiketler', 'value' => \App\Models\Tag::query()->count(), 'description' => 'İçerik etiketleri', 'icon' => 'heroicon-o-hashtag', 'tone' => 'pink', 'url' => \App\Filament\Resources\Tags\TagResource::getUrl()],
            ['label' => 'Yorumlar', 'value' => \App\Models\Comment::query()->count(), 'description' => 'Topluluk etkileşimleri', 'icon' => 'heroicon-o-chat-bubble-left-right', 'tone' => 'violet', 'url' => \App\Filament\Resources\Comments\CommentResource::getUrl()],
            ['label' => 'Dış RSS bağlantıları', 'value' => \App\Models\RssSource::query()->count(), 'description' => \App\Models\RssSource::query()->where('is_active', true)->count().' aktif kaynak', 'icon' => 'heroicon-o-rss', 'tone' => 'orange', 'url' => \App\Filament\Pages\RssSources::getUrl()],
        ];

        $recentPosts = \App\Models\Post::query()->with('user')->latest()->limit(5)->get();
        $recentComments = \App\Models\Comment::query()->with(['user', 'post'])->latest()->limit(5)->get();
        $recentRssSources = \App\Models\RssSource::query()->latest()->limit(4)->get();
    @endphp

    <style>
        .cm-dashboard{--cm-border:#e4e9f1;--cm-muted:#718096;--cm-ink:#172033;--cm-blue:#1769f5;color:var(--cm-ink);display:flex;flex-direction:column;gap:27px}
        .dark .cm-dashboard{--cm-border:#303846;--cm-muted:#9aa6b8;--cm-ink:#f3f6fb}
        .cm-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:27px 30px;border:1px solid var(--cm-border);border-radius:20px;background:linear-gradient(115deg,#fff 0%,#f7faff 68%,#edf4ff 100%)}
        .dark .cm-hero{background:linear-gradient(115deg,#151b26,#17243a)}
        .cm-hero:after{content:"";position:absolute;z-index:-1;width:250px;height:250px;right:8%;top:-165px;border:34px solid rgba(37,112,245,.08);border-radius:50%}
        .cm-identity{display:flex;align-items:center;gap:16px;min-width:0}
        .cm-avatar{width:62px;height:62px;flex:none;object-fit:cover;border:1px solid var(--cm-border);border-radius:18px;background:#fff}
        .cm-eyebrow{margin:0 0 5px;color:var(--cm-blue);font-size:11px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
        .cm-hero-title{margin:0;color:var(--cm-ink);font-size:24px;font-weight:800;letter-spacing:-.035em;line-height:1.2}
        .cm-hero-note{margin:7px 0 0;color:var(--cm-muted);font-size:13px}
        .cm-actions{display:flex;flex-wrap:wrap;gap:9px}
        .cm-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 14px;border:1px solid var(--cm-border);border-radius:11px;background:#fff;color:#334155!important;font-size:12px;font-weight:750;text-decoration:none!important}
        .cm-action svg{width:16px;height:16px}
        .cm-action-primary{border-color:#1769f5;background:#1769f5;color:#fff!important}
        .cm-action-primary:hover{background:#0757dc}
        .dark .cm-action{border-color:#3b4657;background:#202938;color:#e5eaf2!important}
        .dark .cm-action-primary{border-color:#1769f5;background:#1769f5;color:#fff!important}
        .cm-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:13px}
        .cm-section-title{margin:0;color:var(--cm-ink);font-size:17px;font-weight:800;letter-spacing:-.02em}
        .cm-section-note{margin:5px 0 0;color:var(--cm-muted);font-size:12px}
        .cm-live{padding:6px 10px;border-radius:20px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:800;white-space:nowrap}
        .cm-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}
        .cm-stat{display:block;min-width:0;padding:17px 18px 16px;border:1px solid var(--cm-border);border-radius:16px;background:#fff;text-decoration:none!important;transition:transform .16s,border-color .16s,box-shadow .16s}
        .cm-stat:hover{transform:translateY(-2px);border-color:#b9d2ff;box-shadow:0 8px 22px rgba(30,75,140,.07)}
        .dark .cm-stat,.dark .cm-panel{background:#171e29}
        .cm-stat-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
        .cm-stat-label{color:#64748b;font-size:12px;font-weight:700}
        .cm-stat-icon{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:11px}
        .cm-stat-icon svg{width:18px;height:18px}
        .cm-stat-value{margin-top:13px;color:var(--cm-ink);font-size:29px;font-weight:850;letter-spacing:-.045em;line-height:1.1}
        .cm-stat-desc{margin:6px 0 0;color:var(--cm-muted);font-size:11px}
        .cm-blue{background:#eaf2ff;color:#2563eb}.cm-green{background:#e7f8ef;color:#15965b}.cm-amber{background:#fff5df;color:#c17b08}.cm-cyan{background:#e4f8fb;color:#07869b}.cm-pink{background:#fff0f6;color:#d34e8c}.cm-violet{background:#f1edff;color:#7955d9}.cm-orange{background:#fff0e6;color:#dc6b25}
        .dark .cm-blue{background:#1d3151}.dark .cm-green{background:#193c30}.dark .cm-amber{background:#49391e}.dark .cm-cyan{background:#173a43}.dark .cm-pink{background:#45263a}.dark .cm-violet{background:#30264a}.dark .cm-orange{background:#4a3022}
        .cm-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
        .cm-chart{min-width:0;overflow:hidden;padding:12px;border:1px solid var(--cm-border);border-radius:17px;background:#fff}
        .dark .cm-chart{background:#171e29}
        .cm-chart .fi-section{border:0!important;box-shadow:none!important;background:transparent!important}
        .cm-chart .fi-section-header{padding:9px 9px 12px!important}
        .cm-chart .fi-section-content{padding:7px 4px 4px!important}
        .cm-chart canvas{max-height:250px}
        .cm-panels{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
        .cm-panel{min-width:0;padding:19px;border:1px solid var(--cm-border);border-radius:17px;background:#fff}
        .cm-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
        .cm-panel-title{margin:0;color:var(--cm-ink);font-size:14px;font-weight:800}
        .cm-panel-note{margin:5px 0 0;color:var(--cm-muted);font-size:11px}
        .cm-panel-link{color:#1769f5;font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
        .cm-list{margin-top:12px}
        .cm-list-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid #eef1f5;text-decoration:none!important}
        .dark .cm-list-row{border-color:#303846}
        .cm-list-main{min-width:0}
        .cm-list-title{overflow:hidden;color:var(--cm-ink);font-size:12px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
        .cm-list-meta{margin-top:4px;color:var(--cm-muted);font-size:10px}
        .cm-pill{flex:none;padding:5px 8px;border-radius:20px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:750}
        .cm-pill-muted{background:#f1f5f9;color:#64748b}
        .cm-empty{padding:24px 8px;text-align:center;color:var(--cm-muted);font-size:12px}
        .cm-quick-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:14px}
        .cm-quick{display:flex;align-items:center;gap:9px;padding:11px;border:1px solid var(--cm-border);border-radius:11px;color:var(--cm-ink)!important;font-size:11px;font-weight:700;text-decoration:none!important}
        .cm-quick:hover{border-color:#b9d2ff;background:#f7faff}
        .cm-quick svg{width:17px;height:17px;color:#64748b}
        .dark .cm-quick:hover{background:#202938}
        @media(max-width:1100px){.cm-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:767px){.cm-dashboard{gap:21px}.cm-hero{align-items:flex-start;flex-direction:column;padding:20px}.cm-hero-title{font-size:21px}.cm-avatar{width:53px;height:53px}.cm-actions{width:100%}.cm-action{flex:1}.cm-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.cm-stat{padding:13px}.cm-stat-value{font-size:25px}.cm-charts,.cm-panels{grid-template-columns:1fr}.cm-panel{padding:16px}}
        @media(max-width:380px){.cm-stats{grid-template-columns:1fr}.cm-quick-grid{grid-template-columns:1fr}}
    </style>

    <div class="cm-dashboard" wire:poll.60s>
        <section class="cm-hero">
            <div class="cm-identity">
                <img src="{{ $admin->avatarUrl() }}" alt="" class="cm-avatar">
                <div>
                    <p class="cm-eyebrow">OgraFi içerik yönetimi</p>
                    <h2 class="cm-hero-title">İçerik merkezine hoş geldin, {{ $admin->displayName() }}</h2>
                    <p class="cm-hero-note">Postları, kanalları, etiketleri, yorumları ve RSS kaynaklarını tek merkezden yönet.</p>
                </div>
            </div>
            <div class="cm-actions">
                <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create') }}" class="cm-action cm-action-primary"><x-filament::icon icon="heroicon-o-plus" /> Yeni post oluştur</a>
                <a href="{{ \App\Filament\Pages\RssSources::getUrl() }}" class="cm-action"><x-filament::icon icon="heroicon-o-rss" /> RSS kaynakları</a>
            </div>
        </section>

        <section>
            <div class="cm-section-head">
                <div><h3 class="cm-section-title">İçerik genel durumu</h3><p class="cm-section-note">Veritabanından alınan güncel içerik ve etkileşim sayıları.</p></div>
                <span class="cm-live">● CANLI · 60 SN'DE YENİLENİR</span>
            </div>
            <div class="cm-stats">
                @foreach ($stats as $stat)
                    <a href="{{ $stat['url'] }}" class="cm-stat">
                        <div class="cm-stat-top">
                            <span class="cm-stat-label">{{ $stat['label'] }}</span>
                            <span class="cm-stat-icon cm-{{ $stat['tone'] }}"><x-filament::icon :icon="$stat['icon']" /></span>
                        </div>
                        <div class="cm-stat-value">{{ number_format($stat['value']) }}</div>
                        <p class="cm-stat-desc">{{ $stat['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="cm-section-head"><div><h3 class="cm-section-title">İçerik büyümesi</h3><p class="cm-section-note">Son 12 aylık içerik ve topluluk hareketleri.</p></div></div>
            <div class="cm-charts">
                <div class="cm-chart">@livewire(\App\Filament\Widgets\PostsGrowthChart::class)</div>
                <div class="cm-chart">@livewire(\App\Filament\Widgets\ChannelsGrowthChart::class)</div>
                <div class="cm-chart">@livewire(\App\Filament\Widgets\MembersGrowthChart::class)</div>
            </div>
        </section>

        <section class="cm-panels">
            <div class="cm-panel">
                <div class="cm-panel-head"><div><h3 class="cm-panel-title">Son eklenen postlar</h3><p class="cm-panel-note">En son oluşturulan içerikler.</p></div><a class="cm-panel-link" href="{{ \App\Filament\Resources\Posts\PostResource::getUrl() }}">Tüm postlar</a></div>
                <div class="cm-list">
                    @forelse($recentPosts as $post)
                        <a class="cm-list-row" href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $post]) }}">
                            <div class="cm-list-main"><div class="cm-list-title">{{ $post->title }}</div><div class="cm-list-meta">{{ $post->user?->displayName() ?? 'Bilinmeyen yazar' }} · {{ $post->created_at?->format('d.m.Y H:i') }}</div></div>
                            <span class="cm-pill {{ $post->is_published ? '' : 'cm-pill-muted' }}">{{ $post->is_published ? 'Yayında' : 'Taslak' }}</span>
                        </a>
                    @empty
                        <div class="cm-empty">Henüz post eklenmemiş.</div>
                    @endforelse
                </div>
            </div>

            <div class="cm-panel">
                <div class="cm-panel-head"><div><h3 class="cm-panel-title">Son yorumlar</h3><p class="cm-panel-note">Topluluğun son etkileşimleri.</p></div><a class="cm-panel-link" href="{{ \App\Filament\Resources\Comments\CommentResource::getUrl() }}">Tüm yorumlar</a></div>
                <div class="cm-list">
                    @forelse($recentComments as $comment)
                        <a class="cm-list-row" href="{{ \App\Filament\Resources\Comments\CommentResource::getUrl('edit', ['record' => $comment]) }}">
                            <div class="cm-list-main"><div class="cm-list-title">{{ \Illuminate\Support\Str::limit(strip_tags($comment->body), 72) }}</div><div class="cm-list-meta">{{ $comment->user?->displayName() ?? 'Misafir' }} · {{ $comment->created_at?->format('d.m.Y H:i') }}</div></div>
                            <span class="cm-pill cm-pill-muted">Yorum</span>
                        </a>
                    @empty
                        <div class="cm-empty">Henüz yorum yok.</div>
                    @endforelse
                </div>
            </div>

            <div class="cm-panel">
                <div class="cm-panel-head"><div><h3 class="cm-panel-title">Dış RSS kaynakları</h3><p class="cm-panel-note">Bağlı yayın kaynaklarının durumu.</p></div><a class="cm-panel-link" href="{{ \App\Filament\Pages\RssSources::getUrl() }}">Yönet</a></div>
                <div class="cm-list">
                    @forelse($recentRssSources as $source)
                        <div class="cm-list-row"><div class="cm-list-main"><div class="cm-list-title">{{ $source->name }}</div><div class="cm-list-meta">{{ $source->last_fetched_at?->diffForHumans() ?? 'Henüz kontrol edilmedi' }}</div></div><span class="cm-pill {{ $source->is_active ? '' : 'cm-pill-muted' }}">{{ $source->is_active ? 'Aktif' : 'Pasif' }}</span></div>
                    @empty
                        <div class="cm-empty">Henüz RSS kaynağı eklenmemiş.</div>
                    @endforelse
                </div>
            </div>

            <div class="cm-panel">
                <h3 class="cm-panel-title">Hızlı erişim</h3>
                <p class="cm-panel-note">Sık kullanılan içerik yönetim alanları.</p>
                <div class="cm-quick-grid">
                    <a class="cm-quick" href="{{ \App\Filament\Resources\Posts\PostResource::getUrl() }}"><x-filament::icon icon="heroicon-o-document-text" />Postlar</a>
                    <a class="cm-quick" href="{{ \App\Filament\Resources\Channels\ChannelResource::getUrl() }}"><x-filament::icon icon="heroicon-o-rectangle-stack" />Kanallar</a>
                    <a class="cm-quick" href="{{ \App\Filament\Resources\Tags\TagResource::getUrl() }}"><x-filament::icon icon="heroicon-o-hashtag" />Etiketler</a>
                    <a class="cm-quick" href="{{ \App\Filament\Resources\Comments\CommentResource::getUrl() }}"><x-filament::icon icon="heroicon-o-chat-bubble-left-right" />Yorumlar</a>
                    <a class="cm-quick" href="{{ \App\Filament\Pages\RssSources::getUrl() }}"><x-filament::icon icon="heroicon-o-rss" />Dış bağlantılar</a>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>