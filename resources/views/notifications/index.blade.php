@section('hide_feed_header', '1')

<x-app-layout>
    <div class="py-6">
        <div class="site-main-shell">
            @php
                $themeNotifications = \App\Models\ThemeSetting::render('notifications');
            @endphp
            @if ($themeNotifications !== '')
                <div class="mb-4">
                    {!! $themeNotifications !!}
                </div>
            @endif

            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-7">
                    <div>
                        <h1 class="text-[1.75rem] font-semibold tracking-[-0.02em] text-slate-900">Bildirimler</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            @if ($unreadCount > 0)
                                {{ $unreadCount }} okunmamis bildirim var.
                            @else
                                Tum bildirimler okundu.
                            @endif
                        </p>
                    </div>

                    <details class="relative">
                        <summary class="inline-flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M12 6h.01M12 12h.01M12 18h.01"/>
                            </svg>
                        </summary>

                        <div class="absolute right-0 top-full z-20 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-[0_18px_44px_rgba(15,23,42,0.14)]">
                            @if ($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.mark-all') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m5 12 5 5L20 7"/>
                                        </svg>
                                        <span>Tumunu okundu isaretle</span>
                                    </button>
                                </form>
                            @endif

                            @if ($notifications->count() > 0)
                                <form method="POST" action="{{ route('notifications.delete-all') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 14H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>
                                        <span>Tumunu sil</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </details>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($notifications as $notification)
                        @php
                            $avatarName = $notification['actor_name'] ?: $notification['title'];
                            $avatarInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($avatarName, 0, 1));
                        @endphp

                        <a
                            href="{{ $notification['read_url'] }}"
                            class="group flex items-start gap-4 px-5 py-5 transition hover:bg-slate-50 sm:px-7 {{ $notification['is_unread'] ? 'bg-white' : 'bg-white/80' }}"
                        >
                            @if (!empty($notification['actor_avatar']))
                                <img
                                    src="{{ $notification['actor_avatar'] }}"
                                    alt="{{ $avatarName }}"
                                    class="h-12 w-12 shrink-0 rounded-full object-cover"
                                >
                            @else
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">
                                    {{ $avatarInitial }}
                                </span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 3"/>
                                    </svg>
                                    <span>{{ $notification['time_human'] }}</span>
                                </div>

                                <p class="text-[1.08rem] leading-6 text-slate-800">
                                    <span class="font-semibold text-slate-900">{{ $notification['actor_name'] }}</span>
                                    <span>{{ ' ' . $notification['action_text'] . ' ' }}</span>
                                    @if ($notification['subject'] !== '')
                                        <span class="font-semibold text-slate-900">{{ $notification['subject'] }}</span>
                                    @elseif ($notification['actor_name'] === 'Bir kullanici')
                                        <span class="font-semibold text-slate-900">{{ $notification['title'] }}</span>
                                    @endif
                                </p>

                                @if ($notification['preview'] !== '')
                                    <p class="mt-1 text-[0.97rem] leading-6 text-slate-500">
                                        {{ $notification['preview'] }}
                                    </p>
                                @endif
                            </div>

                            @if ($notification['is_unread'])
                                <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></span>
                            @endif
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center text-sm text-slate-500">
                            Henuz bildirim yok.
                        </div>
                    @endforelse
                </div>
            </section>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
