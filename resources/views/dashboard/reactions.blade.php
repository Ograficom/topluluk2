@section('title', 'Tepkiler')
@section('meta_description', 'Aktif tepkileri goruntuleyin ve yeni emoji veya GIF tepkisi ekleyin.')

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
                <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-4 dark:border-slate-700 sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 dark:bg-slate-800">
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

                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-gray-400 dark:text-slate-600">&rsaquo;</span>

                    <span class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-slate-300">
                        Tepkiler
                    </span>
                </div>

                <div class="space-y-6 px-3 pb-8 pt-4 sm:p-6 sm:pb-8">
                    @if(session('status') === 'reaction-created')
                        <div class="rounded-xl border border-emerald-200 px-4 py-3 text-sm leading-6 text-emerald-700 dark:border-emerald-900 dark:text-emerald-300">
                            Tepkin eklendi ve hemen kullanima acildi.
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="rounded-xl border border-red-200 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:text-red-300">
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <section class="space-y-3">
                        <div>
                            <h1 class="text-base font-semibold text-gray-950 dark:text-white">Tepkiler</h1>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-slate-400">
                                Sitede kullanilabilen aktif tepkiler burada listelenir.
                            </p>
                        </div>

                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            @forelse($reactionTypes as $reaction)
                                @php($imageUrl = $reactionAssetUrl($reaction->gif_url))
                                <div class="flex min-h-20 flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 px-2 py-3 text-center dark:border-slate-700">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $reaction->label }}" class="h-9 w-9 rounded-full object-cover">
                                    @elseif(filled($reaction->emoji))
                                        <span class="text-2xl leading-none" aria-hidden="true">{{ $reaction->emoji }}</span>
                                    @endif

                                    <span class="line-clamp-1 w-full text-[11px] text-gray-600 dark:text-slate-400">
                                        {{ $reaction->label }}
                                    </span>
                                </div>
                            @empty
                                <div class="col-span-full rounded-xl border border-gray-200 px-4 py-5 text-sm text-gray-500 dark:border-slate-700 dark:text-slate-400">
                                    Henuz aktif tepki yok.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="border-t border-gray-200 pt-5 dark:border-slate-700">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Yeni tepki ekle</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-slate-400">
                                Emoji yazabilir veya GIF, PNG, JPG ya da WebP gorseli yukleyebilirsin. Eklenen tepki hemen aktif olur.
                            </p>
                        </div>

                        @if($reactionUploadBlocked)
                            <div class="rounded-xl border border-red-200 px-4 py-4 text-sm leading-6 text-red-700 dark:border-red-900 dark:text-red-300">
                                Yeni tepki ekleme yetkin yonetici tarafindan kapatilmis.
                            </div>
                        @else
                            <form method="POST" action="{{ route('dashboard.reactions.store') }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="reaction-label" class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-slate-200">Tepki adi</label>
                                    <input
                                        id="reaction-label"
                                        name="label"
                                        type="text"
                                        value="{{ old('label') }}"
                                        maxlength="100"
                                        required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-gray-500 focus:ring-0 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                        placeholder="Orn: Alkis"
                                    >
                                </div>

                                <div>
                                    <label for="reaction-emoji" class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-slate-200">Emoji</label>
                                    <input
                                        id="reaction-emoji"
                                        name="emoji"
                                        type="text"
                                        value="{{ old('emoji') }}"
                                        maxlength="16"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-gray-500 focus:ring-0 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                        placeholder="Bir emoji gir"
                                    >
                                </div>

                                <div>
                                    <label for="reaction-image" class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-slate-200">GIF / Resim</label>
                                    <input
                                        id="reaction-image"
                                        name="reaction_image"
                                        type="file"
                                        accept="image/gif,image/png,image/jpeg,image/webp"
                                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 file:mr-3 file:border-0 file:bg-transparent file:p-0 file:text-sm file:font-medium dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300"
                                    >
                                    <p class="mt-1.5 text-xs leading-5 text-gray-500 dark:text-slate-400">Emoji veya gorselden en az biri gerekli. Gorsel en fazla 10 MB olabilir.</p>
                                </div>

                                <div class="rounded-xl border border-gray-200 px-4 py-3 text-xs leading-5 text-gray-500 dark:border-slate-700 dark:text-slate-400">
                                    +18, uygunsuz veya rahatsiz edici tepki ekleme. Yonetici tepkiyi silebilir ve hesabinin yeni tepki ekleme yetkisini kapatabilir.
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex min-h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-medium text-white dark:bg-slate-100 dark:text-slate-900"
                                >
                                    Tepki ekle
                                </button>
                            </form>
                        @endif
                    </section>

                    <section class="border-t border-gray-200 pt-5 dark:border-slate-700">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Eklediklerim</h2>
                        </div>

                        <div class="space-y-2">
                            @forelse($myReactionTypes as $reaction)
                                @php($imageUrl = $reactionAssetUrl($reaction->gif_url))
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-3 dark:border-slate-700">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-700">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $reaction->label }}" class="h-8 w-8 rounded-full object-cover">
                                        @elseif(filled($reaction->emoji))
                                            <span class="text-xl leading-none" aria-hidden="true">{{ $reaction->emoji }}</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-slate-100">{{ $reaction->label }}</span>
                                        <span class="mt-1 block text-xs text-gray-500 dark:text-slate-400">
                                            {{ $reaction->is_active ? 'Aktif' : 'Pasif' }}
                                            @if($reaction->created_at)
                                                · {{ $reaction->created_at->format('d.m.Y H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-gray-200 px-4 py-5 text-sm text-gray-500 dark:border-slate-700 dark:text-slate-400">
                                    Henuz tepki eklemedin.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
