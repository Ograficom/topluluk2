<x-filament-panels::page>
    @php
        $admin = auth()->user();
        $stats = [
            [
                'label' => 'Üyeler',
                'value' => \App\Models\User::query()->count(),
                'description' => 'Kayıtlı hesaplar',
                'icon' => 'heroicon-o-users',
                'tone' => 'blue',
                'url' => \App\Filament\Resources\Users\UserResource::getUrl(),
            ],
            [
                'label' => 'Yazılar',
                'value' => \App\Models\Post::query()->count(),
                'description' => 'Toplam içerik',
                'icon' => 'heroicon-o-document-text',
                'tone' => 'violet',
                'url' => \App\Filament\Resources\Posts\PostResource::getUrl(),
            ],
            [
                'label' => 'Taslaklar',
                'value' => \App\Models\Post::query()->where('is_published', false)->count(),
                'description' => 'Yayın bekleyen içerik',
                'icon' => 'heroicon-o-pencil-square',
                'tone' => 'amber',
                'url' => \App\Filament\Resources\Posts\PostResource::getUrl(),
            ],
            [
                'label' => 'Yorumlar',
                'value' => \App\Models\Comment::query()->count(),
                'description' => 'Topluluk yorumları',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'tone' => 'emerald',
                'url' => \App\Filament\Resources\Comments\CommentResource::getUrl(),
            ],
            [
                'label' => 'Kanallar',
                'value' => \App\Models\Channel::query()->count(),
                'description' => 'İçerik kategorileri',
                'icon' => 'heroicon-o-rectangle-stack',
                'tone' => 'cyan',
                'url' => \App\Filament\Resources\Channels\ChannelResource::getUrl(),
            ],
            [
                'label' => 'Etiketler',
                'value' => \App\Models\Tag::query()->count(),
                'description' => 'İçerik etiketleri',
                'icon' => 'heroicon-o-hashtag',
                'tone' => 'pink',
                'url' => \App\Filament\Resources\Tags\TagResource::getUrl(),
            ],
            [
                'label' => 'Destek talepleri',
                'value' => \App\Models\SupportTicket::query()->count(),
                'description' => \App\Models\SupportTicket::query()->where('status', \App\Models\SupportTicket::STATUS_NEW)->count().' yeni talep',
                'icon' => 'heroicon-o-lifebuoy',
                'tone' => 'orange',
                'url' => \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl(),
            ],
        ];
        $recentTickets = \App\Models\SupportTicket::query()->latest()->limit(4)->get();
        $toneClasses = [
            'blue' => ['icon' => 'bg-blue-50 text-blue-600', 'value' => 'text-blue-700'],
            'violet' => ['icon' => 'bg-violet-50 text-violet-600', 'value' => 'text-violet-700'],
            'amber' => ['icon' => 'bg-amber-50 text-amber-600', 'value' => 'text-amber-700'],
            'emerald' => ['icon' => 'bg-emerald-50 text-emerald-600', 'value' => 'text-emerald-700'],
            'cyan' => ['icon' => 'bg-cyan-50 text-cyan-600', 'value' => 'text-cyan-700'],
            'pink' => ['icon' => 'bg-pink-50 text-pink-600', 'value' => 'text-pink-700'],
            'orange' => ['icon' => 'bg-orange-50 text-orange-600', 'value' => 'text-orange-700'],
        ];
    @endphp

    <div class="space-y-7">
        <section class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 sm:p-7 dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full bg-blue-50 dark:bg-blue-500/5"></div>
            <div class="absolute -right-2 -top-8 h-44 w-44 rounded-full border-[22px] border-blue-100/70 dark:border-blue-500/10"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <img src="{{ $admin->avatarUrl() }}" alt="" class="h-14 w-14 rounded-2xl border border-gray-100 object-cover dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">OgraFi yönetim paneli</p>
                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Merhaba, {{ $admin->displayName() }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Topluluğunun bugünkü durumuna genel bir bakış.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl() }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <x-filament::icon icon="heroicon-o-document-plus" class="h-4 w-4" />
                        Yazıları yönet
                    </a>
                    <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl() }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        <x-filament::icon icon="heroicon-o-inbox" class="h-4 w-4" />
                        Destek kutusu
                    </a>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">Genel durum</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">OgraFi'deki içerik ve topluluk verileri.</p>
                </div>
                <span class="text-xs font-medium text-gray-400">Güncel veriler</span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    <a href="{{ $stat['url'] }}" class="group rounded-2xl border border-gray-200 bg-white p-4 transition duration-150 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/30 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-blue-900 dark:hover:bg-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $stat['label'] }}</span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $toneClasses[$stat['tone']]['icon'] }}">
                                <x-filament::icon :icon="$stat['icon']" class="h-[18px] w-[18px]" />
                            </span>
                        </div>
                        <div class="mt-3 flex items-end justify-between gap-3">
                            <span class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($stat['value']) }}</span>
                            <x-filament::icon icon="heroicon-m-arrow-up-right" class="mb-1 h-4 w-4 text-gray-300 transition group-hover:text-blue-600" />
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $stat['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="space-y-3">
            <div>
                <h3 class="text-base font-bold text-gray-950 dark:text-white">Büyüme istatistikleri</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Son 12 ayın aylık kayıt ve içerik hareketleri.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 2xl:grid-cols-2">
                <div class="min-w-0 [&_.fi-section]:rounded-2xl [&_.fi-section]:border-gray-200 [&_.fi-section]:shadow-none dark:[&_.fi-section]:border-gray-800">
                    @livewire(\App\Filament\Widgets\PostsGrowthChart::class)
                </div>
                <div class="min-w-0 [&_.fi-section]:rounded-2xl [&_.fi-section]:border-gray-200 [&_.fi-section]:shadow-none dark:[&_.fi-section]:border-gray-800">
                    @livewire(\App\Filament\Widgets\ChannelsGrowthChart::class)
                </div>
                <div class="min-w-0 [&_.fi-section]:rounded-2xl [&_.fi-section]:border-gray-200 [&_.fi-section]:shadow-none dark:[&_.fi-section]:border-gray-800">
                    @livewire(\App\Filament\Widgets\MembersGrowthChart::class)
                </div>
                <div class="min-w-0 [&_.fi-section]:rounded-2xl [&_.fi-section]:border-gray-200 [&_.fi-section]:shadow-none dark:[&_.fi-section]:border-gray-800">
                    @livewire(\App\Filament\Widgets\SupportTicketsGrowthChart::class)
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 xl:col-span-2 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-950 dark:text-white">Son destek talepleri</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kullanıcılardan gelen son mesajlar.</p>
                    </div>
                    <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl() }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">Tümünü gör</a>
                </div>
                <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($recentTickets as $ticket)
                        <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl('edit', ['record' => $ticket]) }}" class="flex flex-col gap-2 py-3 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $ticket->subject }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $ticket->name }} · {{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                            </div>
                            <span @class([
                                'inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-amber-50 text-amber-700' => $ticket->status === 'new',
                                'bg-blue-50 text-blue-700' => $ticket->status === 'reviewing',
                                'bg-emerald-50 text-emerald-700' => $ticket->status === 'replied',
                                'bg-gray-100 text-gray-600' => $ticket->status === 'resolved',
                            ])>{{ match($ticket->status) { 'new' => 'Yeni', 'reviewing' => 'İnceleniyor', 'replied' => 'Yanıtlandı', 'resolved' => 'Çözüldü', default => $ticket->status } }}</span>
                        </a>
                    @empty
                        <div class="py-8 text-center">
                            <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-8 w-8 text-gray-300" />
                            <p class="mt-2 text-sm text-gray-500">Henüz destek talebi yok.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="font-bold text-gray-950 dark:text-white">Hızlı erişim</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sık kullanılan yönetim alanları.</p>
                <div class="mt-4 space-y-2">
                    @foreach ([
                        ['Etiketleri yönet', \App\Filament\Resources\Tags\TagResource::getUrl(), 'heroicon-o-hashtag'],
                        ['Kanalları yönet', \App\Filament\Resources\Channels\ChannelResource::getUrl(), 'heroicon-o-rectangle-stack'],
                        ['Üyeleri yönet', \App\Filament\Resources\Users\UserResource::getUrl(), 'heroicon-o-users'],
                        ['Yorumları yönet', \App\Filament\Resources\Comments\CommentResource::getUrl(), 'heroicon-o-chat-bubble-left-right'],
                    ] as [$label, $url, $icon])
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-blue-700 dark:text-gray-200 dark:hover:bg-gray-800">
                            <x-filament::icon :icon="$icon" class="h-4 w-4 text-gray-400" />
                            {{ $label }}
                            <x-filament::icon icon="heroicon-m-chevron-right" class="ml-auto h-4 w-4 text-gray-300" />
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
