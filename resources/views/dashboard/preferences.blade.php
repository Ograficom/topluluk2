<x-app-layout>
    @php
        $prefs = session('dashboard_preferences', [
            'show_mature' => true,
            'blur_mature' => true,
            'open_new_tab' => false,
        ]);
    @endphp

    <style>
        .blue-switch-wrap label {
            position: relative !important;
            display: inline-flex !important;
            width: 48px !important;
            height: 28px !important;
            flex-shrink: 0 !important;
            cursor: pointer !important;
            align-items: center !important;
            border-radius: 9999px !important;
        }

        .blue-switch-wrap input[type="checkbox"] {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border-width: 0 !important;
        }

        .blue-switch-wrap label > span:first-of-type {
            position: absolute !important;
            inset: 0 !important;
            width: 48px !important;
            height: 28px !important;
            border-radius: 9999px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #e2e8f0 !important;
            transition: background-color 180ms ease, border-color 180ms ease !important;
        }

        .blue-switch-wrap label > span:last-of-type {
            pointer-events: none !important;
            position: absolute !important;
            left: 4px !important;
            top: 4px !important;
            width: 20px !important;
            height: 20px !important;
            border-radius: 9999px !important;
            background-color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.22) !important;
            transform: translateX(0) !important;
            transition: transform 180ms ease !important;
        }

        .blue-switch-wrap input[type="checkbox"]:checked ~ span:first-of-type {
            border-color: #0e7c86 !important;
            background-color: #0e7c86 !important;
        }

        .blue-switch-wrap input[type="checkbox"]:checked ~ span:last-of-type {
            transform: translateX(20px) !important;
            background-color: #ffffff !important;
        }

        .blue-switch-wrap input[type="checkbox"]:focus-visible ~ span:first-of-type {
            box-shadow: 0 0 0 3px rgba(14, 124, 134, 0.25) !important;
        }

        .blue-switch-wrap input[type="checkbox"]:disabled ~ span:first-of-type,
        .blue-switch-wrap input[type="checkbox"]:disabled ~ span:last-of-type {
            opacity: 0.55 !important;
            cursor: not-allowed !important;
        }
    </style>

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
                        Tercihler
                    </span>
                </div>

                <div class="px-3 pt-4 pb-6 sm:p-6 sm:pb-8">
                    @if (session('status') === 'preferences-updated')
                        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            Tercihler güncellendi.
                        </div>
                    @endif

                    <form action="{{ route('dashboard.preferences.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-3 sm:space-y-5">
                            <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 sm:gap-6 sm:py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[15px] font-medium leading-6 text-gray-950">
                                        18+ içeriği göster
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-gray-500">
                                        Akış ve arama sonuçlarında yetişkin ve NSFW içerikleri görmek için etkinleştirin.
                                    </p>
                                </div>

                                <div class="blue-switch-wrap shrink-0">
                                    <x-ui.switch
                                        name="show_mature"
                                        :checked="old('show_mature', $prefs['show_mature'] ?? false)"
                                    />
                                </div>
                            </div>

                            <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 sm:gap-6 sm:py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[15px] font-medium leading-6 text-gray-950">
                                        Akışta 18+ görsel ve medyaları bulanıklaştır
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-gray-500">
                                        Görmeyi seçene kadar hassas küçük görselleri gizleyin.
                                    </p>
                                </div>

                                <div class="blue-switch-wrap shrink-0">
                                    <x-ui.switch
                                        name="blur_mature"
                                        :checked="old('blur_mature', $prefs['blur_mature'] ?? false)"
                                    />
                                </div>
                            </div>

                            <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 sm:gap-6 sm:py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[15px] font-medium leading-6 text-gray-950">
                                        Gönderileri yeni sekmede aç
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-gray-500">
                                        Gönderilere göz atarken akışın açık kalmasını sağlayın.
                                    </p>
                                </div>

                                <div class="blue-switch-wrap shrink-0">
                                    <x-ui.switch
                                        name="open_new_tab"
                                        :checked="old('open_new_tab', $prefs['open_new_tab'] ?? false)"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                style="background-color: #0e7c86 !important; color: #ffffff !important; border: none !important;"
                                onmouseover="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                onmouseout="this.style.setProperty('background-color', '#0e7c86', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                onmousedown="this.style.setProperty('background-color', '#1e40af', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                onmouseup="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 text-sm font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5"
                            >
                                Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>