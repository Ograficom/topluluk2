@section('title', 'Tüm Tepkiler')
@section('meta_description', 'Sitede kullanılabilen tüm aktif tepkileri görüntüleyin.')

<x-app-layout>
    @php
        $reactionAssetUrl = function (?string $path): ?string {
            if (! filled($path)) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        };
    @endphp

    <div class="mx-auto w-full max-w-[var(--profile-shell-width)]">
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] min-h-[70vh] w-screen -translate-x-1/2 bg-white text-gray-900 dark:bg-slate-900 dark:text-slate-100 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-gray-200 dark:sm:border-slate-700">
                <div class="flex items-center gap-2 border-b border-gray-200 px-4 py-4 dark:border-slate-700 sm:p-6">
                    <a
                        href="{{ route('dashboard.reactions') }}"
                        class="inline-flex h-9 items-center gap-2 rounded-lg px-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 active:bg-gray-200 dark:text-slate-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                    >
                        <iconify-icon icon="lucide:arrow-left" class="text-[18px]"></iconify-icon>
                        <span>Geri</span>
                    </a>

                    <div class="min-w-0">
                        <h1 class="truncate text-base font-semibold text-gray-950 dark:text-white">Tüm Tepkiler</h1>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-slate-400">Sitede kullanılabilen tüm aktif tepkiler.</p>
                    </div>
                </div>

                <div class="px-3 pb-8 pt-4 sm:p-6 sm:pb-8">
                    @if($reactionTypes->isNotEmpty())
                        <div class="grid grid-cols-6 gap-2">
                            @foreach($reactionTypes as $reaction)
                                @php($imageUrl = $reactionAssetUrl($reaction->gif_url))
                                <div
                                    class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-lg px-1 py-2.5 text-center hover:bg-gray-100 active:bg-gray-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                                    title="{{ $reaction->label }}"
                                >
                                    <div class="flex h-9 w-9 items-center justify-center">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $reaction->label }}" class="h-9 w-9 rounded-full object-cover">
                                        @elseif(filled($reaction->emoji))
                                            <span class="text-2xl leading-none" aria-hidden="true">{{ $reaction->emoji }}</span>
                                        @endif
                                    </div>

                                    <span class="w-full truncate text-[10px] leading-4 text-gray-600 dark:text-slate-400">
                                        {{ $reaction->label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg px-4 py-6 text-sm text-gray-500 dark:text-slate-400">
                            Henüz aktif tepki yok.
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
