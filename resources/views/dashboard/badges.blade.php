<x-app-layout>
    <div class="w-full sm:mx-auto sm:max-w-[45rem] sm:px-6 lg:px-8">
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] min-h-[70vh] w-screen -translate-x-1/2 bg-white text-gray-900 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-gray-200 sm:shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-4 sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5">
                        <img
                            src="{{ auth()->user()->profile_photo_url }}"
                            alt="{{ auth()->user()->name }}"
                            class="h-7 w-7 shrink-0 rounded-full object-cover"
                        >

                        <span class="truncate text-sm font-medium text-gray-900">
                            {{ auth()->user()->name }}
                        </span>
                    </div>

                    <span class="shrink-0 text-gray-400">&rsaquo;</span>

                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-gray-500 hover:text-gray-700">
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-gray-400">&rsaquo;</span>

                    <span class="min-w-0 truncate text-sm font-medium text-gray-700">
                        Rozetler
                    </span>
                </div>

                <div class="space-y-4 px-3 pt-4 pb-6 sm:p-6 sm:pb-8">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-700">
                        <div class="font-medium text-gray-950">
                            Rozet puanın: {{ number_format((int) ($badgePoints ?? 0)) }}
                        </div>

                        <div class="mt-1 leading-6 text-gray-500">
                            Puanın arttıkça birden fazla rozet otomatik olarak açılır.
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse(($badges ?? collect()) as $badge)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white"
                                        style="background-color: {{ $badge->color ?? '#9ca3af' }}"
                                    >
                                        <span class="text-xs font-medium">
                                            {{ mb_strtoupper(mb_substr((string) $badge->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                                        </span>
                                    </span>

                                    <div class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-gray-900">
                                            {{ $badge->name }}
                                        </span>

                                        @if(filled($badge->description))
                                            <div class="mt-1 text-xs leading-5 text-gray-500">
                                                {{ $badge->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs text-gray-500 ring-1 ring-gray-200">
                                    {{ number_format((int) $badge->min_points) }}+ puan
                                </span>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm leading-6 text-gray-600">
                                Henüz açılmış rozet yok.
                            </div>
                        @endforelse

                        @if(isset($nextBadge) && $nextBadge)
                            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-4 text-sm leading-6 text-gray-700">
                                Sonraki rozet:
                                <span class="font-medium text-gray-950">
                                    {{ $nextBadge->name }}
                                </span>

                                <span>
                                    ({{ number_format((int) $nextBadge->min_points) }} puan).
                                </span>

                                Kalan:
                                <span class="font-medium text-gray-950">
                                    {{ number_format(max(0, (int) $nextBadge->min_points - (int) ($badgePoints ?? 0))) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>