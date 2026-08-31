@section('title', 'Tepkiler')
@section('meta_description', 'Aktif tepkileri görüntüleyin ve yeni emoji veya GIF tepkisi ekleyin.')

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

        $initialTab = $errors->any()
            ? 'create'
            : (request('tab') === 'create' ? 'create' : 'mine');
    @endphp

    <style>
        .reaction-scroll-strip {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
            -webkit-mask-image: linear-gradient(to right, transparent 0, #000 18px, #000 calc(100% - 18px), transparent 100%);
            mask-image: linear-gradient(to right, transparent 0, #000 18px, #000 calc(100% - 18px), transparent 100%);
        }

        .reaction-scroll-strip::-webkit-scrollbar {
            height: 3px;
        }

        .reaction-scroll-strip::-webkit-scrollbar-track {
            background: transparent;
        }

        .reaction-scroll-strip::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: #cbd5e1;
        }

        .dark .reaction-scroll-strip {
            scrollbar-color: #475569 transparent;
        }

        .dark .reaction-scroll-strip::-webkit-scrollbar-thumb {
            background: #475569;
        }
    </style>

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

                    <a
                        href="{{ route('dashboard') }}"
                        class="shrink-0 rounded-lg px-2 py-1.5 text-sm text-gray-500 hover:bg-gray-100 active:bg-gray-200 dark:text-slate-400 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                    >
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-gray-400 dark:text-slate-600">&rsaquo;</span>

                    <span class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-slate-300">
                        Tepkiler
                    </span>
                </div>

                <div class="space-y-6 px-3 pb-8 pt-4 sm:p-6 sm:pb-8">
                    @if(session('status') === 'reaction-created')
                        <div class="rounded-lg border border-emerald-200 px-4 py-3 text-sm leading-6 text-emerald-700 dark:border-emerald-900 dark:text-emerald-300">
                            Tepkin eklendi ve hemen kullanıma açıldı.
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="rounded-lg border border-red-200 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:text-red-300">
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <section class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h1 class="text-base font-semibold text-gray-950 dark:text-white">Tepkiler</h1>
                                <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-slate-400">
                                    Aynı anda 6 tepki görünür. Diğerlerini yatay kaydırarak görebilirsin.
                                </p>
                            </div>

                            <a
                                href="{{ route('dashboard.reactions.all') }}"
                                class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg px-3 text-sm font-medium text-gray-700 hover:bg-gray-100 active:bg-gray-200 dark:text-slate-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                            >
                                Tümünü göster
                            </a>
                        </div>

                        @if($reactionTypes->isNotEmpty())
                            <div class="reaction-scroll-strip flex snap-x snap-mandatory gap-2 overflow-x-auto px-2 pb-1.5" aria-label="Aktif tepkiler">
                                @foreach($reactionTypes as $reaction)
                                    @php($imageUrl = $reactionAssetUrl($reaction->gif_url))
                                    <div
                                        class="flex min-w-0 shrink-0 basis-[calc((100%_-_2.5rem)/6)] snap-start flex-col items-center justify-center gap-1.5 rounded-lg px-1 py-2.5 text-center hover:bg-gray-100 active:bg-gray-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
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
                            <div class="rounded-lg px-4 py-5 text-sm text-gray-500 dark:text-slate-400">
                                Henüz aktif tepki yok.
                            </div>
                        @endif
                    </section>

                    <section class="border-t border-gray-200 pt-5 dark:border-slate-700" data-reaction-tabs>
                        <div class="mb-5 flex items-center gap-1 border-b border-gray-200 pb-2 dark:border-slate-700" role="tablist" aria-label="Tepki yönetimi">
                            <button
                                type="button"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 active:bg-gray-200 dark:text-slate-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                                data-reaction-tab="mine"
                                role="tab"
                                aria-controls="reaction-panel-mine"
                            >
                                Eklediklerim
                            </button>

                            <button
                                type="button"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 active:bg-gray-200 dark:text-slate-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                                data-reaction-tab="create"
                                role="tab"
                                aria-controls="reaction-panel-create"
                            >
                                Yeni tepki ekle
                            </button>
                        </div>

                        <div id="reaction-panel-mine" data-reaction-panel="mine" role="tabpanel">
                            <div class="space-y-2">
                                @forelse($myReactionTypes as $reaction)
                                    @php($imageUrl = $reactionAssetUrl($reaction->gif_url))
                                    <div class="flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-gray-100 active:bg-gray-200 dark:hover:bg-slate-800 dark:active:bg-slate-700">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg">
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
                                    <div class="rounded-lg px-4 py-5 text-sm text-gray-500 dark:text-slate-400">
                                        Henüz tepki eklemedin.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div id="reaction-panel-create" data-reaction-panel="create" role="tabpanel" hidden>
                            @if($reactionUploadBlocked)
                                <div class="rounded-lg border border-red-200 px-4 py-4 text-sm leading-6 text-red-700 dark:border-red-900 dark:text-red-300">
                                    Yeni tepki ekleme yetkin yönetici tarafından kapatılmış.
                                </div>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('dashboard.reactions.store') }}"
                                    enctype="multipart/form-data"
                                    class="space-y-4"
                                    data-reaction-form
                                >
                                    @csrf

                                    <div>
                                        <label for="reaction-label" class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-slate-200">Tepki adı</label>
                                        <input
                                            id="reaction-label"
                                            name="label"
                                            type="text"
                                            value="{{ old('label') }}"
                                            maxlength="100"
                                            required
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-gray-500 focus:ring-0 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                            placeholder="Örn: Alkış"
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
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-transparent file:px-2 file:py-1 file:text-sm file:font-medium hover:file:bg-gray-100 active:file:bg-gray-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:file:bg-slate-800 dark:active:file:bg-slate-700"
                                        >
                                        <p class="mt-1.5 text-xs leading-5 text-gray-500 dark:text-slate-400">Emoji veya görselden en az biri gerekli. Görsel en fazla 10 MB olabilir.</p>
                                    </div>

                                    <label
                                        for="reaction-policy"
                                        class="flex cursor-pointer items-center justify-between gap-4 rounded-lg px-3 py-3 hover:bg-gray-100 active:bg-gray-200 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                                    >
                                        <span class="min-w-0 text-xs leading-5 text-gray-600 dark:text-slate-300">
                                            +18, uygunsuz veya rahatsız edici tepki ekleme. Yönetici tepkiyi silebilir ve hesabının yeni tepki ekleme yetkisini kapatabilir.
                                        </span>

                                        <span class="relative inline-flex h-6 w-11 shrink-0 items-center">
                                            <input
                                                id="reaction-policy"
                                                name="policy_ack"
                                                type="checkbox"
                                                value="1"
                                                class="peer sr-only"
                                                data-reaction-policy
                                                @checked(old('policy_ack'))
                                            >
                                            <span class="absolute inset-0 rounded-full bg-gray-300 peer-checked:bg-blue-600 dark:bg-slate-700"></span>
                                            <span class="absolute left-0.5 h-5 w-5 rounded-full bg-white peer-checked:translate-x-5"></span>
                                        </span>
                                    </label>

                                    <button
                                        type="submit"
                                        class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white hover:bg-gray-200 hover:text-gray-900 active:bg-gray-300 disabled:cursor-not-allowed disabled:bg-blue-300 disabled:text-white dark:bg-blue-600 dark:text-white dark:hover:bg-slate-700 dark:hover:text-white dark:active:bg-slate-600 dark:disabled:bg-blue-900 dark:disabled:text-blue-100"
                                        data-reaction-submit
                                        disabled
                                    >
                                        Tepki ekle
                                    </button>
                                </form>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-reaction-tabs]');
            if (!root) return;

            const tabs = Array.from(root.querySelectorAll('[data-reaction-tab]'));
            const panels = Array.from(root.querySelectorAll('[data-reaction-panel]'));
            const initialTab = @json($initialTab);

            const activateTab = (name) => {
                tabs.forEach((tab) => {
                    const active = tab.getAttribute('data-reaction-tab') === name;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                    tab.classList.toggle('bg-gray-100', active);
                    tab.classList.toggle('dark:bg-slate-800', active);
                });

                panels.forEach((panel) => {
                    panel.hidden = panel.getAttribute('data-reaction-panel') !== name;
                });
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => activateTab(tab.getAttribute('data-reaction-tab')));
            });

            activateTab(initialTab);

            const policy = root.querySelector('[data-reaction-policy]');
            const submit = root.querySelector('[data-reaction-submit]');

            const syncSubmit = () => {
                if (!policy || !submit) return;
                submit.disabled = !policy.checked;
            };

            policy?.addEventListener('change', syncSubmit);
            syncSubmit();
        })();
    </script>
</x-app-layout>
