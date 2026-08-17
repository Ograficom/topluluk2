<x-app-layout>
    <div class="mx-auto w-full max-w-[var(--profile-shell-width)]">
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] min-h-[70vh] w-screen -translate-x-1/2 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-gray-200 dark:sm:border-slate-700 sm:shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-200 dark:border-slate-700 px-4 py-4 sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-gray-100 dark:bg-slate-800 px-3 py-1.5">
                        <img
                            src="{{ auth()->user()->profile_photo_url }}"
                            alt="{{ auth()->user()->name }}"
                            class="h-7 w-7 shrink-0 rounded-full object-cover"
                        >

                        <span class="truncate text-sm font-medium text-gray-900 dark:text-slate-100">
                            {{ auth()->user()->name }}
                        </span>
                    </div>

                    <span class="shrink-0 text-gray-400 dark:text-slate-600">&rsaquo;</span>

                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200">
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-gray-400 dark:text-slate-600">&rsaquo;</span>

                    <span class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-slate-300">
                        Rozetler
                    </span>
                </div>

                <div class="space-y-4 px-3 pt-4 pb-6 sm:p-6 sm:pb-8">
                    <div class="rounded-2xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 px-4 py-4 text-sm text-gray-700 dark:text-slate-300">
                        <div class="font-medium text-gray-950 dark:text-slate-100">
                            Rozet puanın: {{ number_format((int) ($badgePoints ?? 0)) }}
                        </div>

                        <div class="mt-1 leading-6 text-gray-500 dark:text-slate-400">
                            Puanın arttıkça birden fazla rozet otomatik olarak açılır.
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse(($badges ?? collect()) as $badge)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
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
                                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-slate-100">
                                            {{ $badge->name }}
                                        </span>

                                        @if(filled($badge->description))
                                            <div class="mt-1 text-xs leading-5 text-gray-500 dark:text-slate-400">
                                                {{ $badge->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <span class="shrink-0 rounded-full bg-white dark:bg-slate-900 px-3 py-1 text-xs text-gray-500 dark:text-slate-400 ring-1 ring-gray-200 dark:ring-slate-700">
                                    {{ number_format((int) $badge->min_points) }}+ puan
                                </span>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-6 text-center text-sm leading-6 text-gray-600 dark:text-slate-400">
                                Henüz açılmış rozet yok.
                            </div>
                        @endforelse

                        @if(isset($nextBadge) && $nextBadge)
                            <div class="rounded-2xl border border-dashed border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-4 text-sm leading-6 text-gray-700 dark:text-slate-300">
                                Sonraki rozet:
                                <span class="font-medium text-gray-950 dark:text-slate-100">
                                    {{ $nextBadge->name }}
                                </span>

                                <span>
                                    ({{ number_format((int) $nextBadge->min_points) }} puan).
                                </span>

                                Kalan:
                                <span class="font-medium text-gray-950 dark:text-slate-100">
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