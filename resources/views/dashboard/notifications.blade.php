<x-app-layout>
    @php
        $user = auth()->user();
        $prefs = session('dashboard_notifications', [
            'comments' => true,
            'replies' => true,
            'likes' => true,
            'followers' => true,
            'mentions' => true,
        ]);
        $digestEmail = old('daily_digest_email', $user->daily_digest_email ?: $user->email);
        $digestEmailVerified = filled($user->daily_digest_email_verified_at);
        $statusMessages = [
            'notifications-updated' => 'Bildirim tercihleri güncellendi.',
            'digest-email-updated' => 'Günlük özet adresi güncellendi.',
            'digest-verification-sent' => 'Doğrulama bağlantısı yeni e-posta adresine gönderildi.',
            'digest-email-verified' => 'Günlük özet e-posta adresi doğrulandı.',
            'digest-unsubscribed' => 'Günlük e-posta özeti kapatıldı.',
        ];
    @endphp

    <style>
        [x-cloak] { display: none !important; }

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
            border-color: #2563eb !important;
            background-color: #2563eb !important;
        }

        .blue-switch-wrap input[type="checkbox"]:checked ~ span:last-of-type {
            transform: translateX(20px) !important;
        }

        .blue-switch-wrap input[type="checkbox"]:focus-visible ~ span:first-of-type {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
        }

        html.dark .blue-switch-wrap label > span:first-of-type {
            border-color: rgba(148, 163, 184, .35) !important;
            background-color: rgba(30, 41, 59, .9) !important;
        }

        html.dark .blue-switch-wrap input[type="checkbox"]:checked ~ span:first-of-type {
            border-color: #3b82f6 !important;
            background-color: #3b82f6 !important;
        }
    </style>

    <div
        x-data="{ digestSettingsOpen: {{ $errors->has('daily_digest_email') ? 'true' : 'false' }} }"
        @keydown.escape.window="digestSettingsOpen = false"
        class="mx-auto w-full max-w-[var(--profile-shell-width)]"
    >
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] w-screen -translate-x-1/2 bg-white text-gray-900 dark:bg-slate-900 dark:text-slate-100 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-gray-200 sm:shadow-sm dark:sm:border-slate-700">
                <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-4 dark:border-slate-700 sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 dark:bg-slate-800">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-7 w-7 shrink-0 rounded-full object-cover">
                        <span class="truncate text-sm font-medium text-gray-900 dark:text-slate-100">{{ $user->name }}</span>
                    </div>
                    <span class="shrink-0 text-gray-400 dark:text-slate-600">&rsaquo;</span>
                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">Ayarlar</a>
                    <span class="shrink-0 text-gray-400 dark:text-slate-600">&rsaquo;</span>
                    <span class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-slate-300">Bildirimler</span>
                </div>

                <div class="px-2 pb-6 pt-3 sm:p-6 sm:pb-8">
                    @if (isset($statusMessages[session('status')]))
                        <div role="status" class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            {{ $statusMessages[session('status')] }}
                        </div>
                    @endif

                    <form action="{{ route('dashboard.notifications.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-3 sm:space-y-4">
                            @foreach ([
                                ['comments', 'Yorum bildirimleri', 'Gönderilerime yeni yorumlar geldiğinde bildirim al.'],
                                ['replies', 'Yanıt bildirimleri', 'Yorumlarıma verilen yanıtlar için bildirim al.'],
                                ['likes', 'Beğeni bildirimleri', 'Gönderi veya yorumum beğenildiğinde bildirim al.'],
                                ['followers', 'Takipçi bildirimleri', 'Yeni biri beni takip ettiğinde bildirim al.'],
                                ['mentions', 'Bahsetme bildirimleri', 'Yorumlarda benden bahsedildiğinde bildirim al.'],
                            ] as [$name, $title, $description])
                                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-700 dark:bg-slate-800 sm:gap-6">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-[15px] font-medium leading-6 text-gray-950 dark:text-slate-100">{{ $title }}</h3>
                                        <p class="mt-0.5 text-sm leading-6 text-gray-600 dark:text-slate-400">{{ $description }}</p>
                                    </div>
                                    <div class="blue-switch-wrap shrink-0">
                                        <x-ui.switch name="{{ $name }}" :checked="old($name, $prefs[$name] ?? false)" />
                                    </div>
                                </div>
                            @endforeach

                            <div class="flex items-center justify-between gap-4 rounded-xl border border-blue-200 bg-blue-50/60 px-4 py-4 dark:border-blue-500/30 dark:bg-blue-500/10 sm:gap-6">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-[15px] font-medium leading-6 text-gray-950 dark:text-slate-100">Günlük e-posta özeti</h3>
                                    <p class="mt-0.5 text-sm leading-6 text-gray-600 dark:text-slate-400">Her gün yayımlanan en fazla 10 gönderiyi e-posta ile al.</p>
                                    <p class="mt-1 truncate text-xs text-gray-500 dark:text-slate-400">
                                        {{ $digestEmail }}
                                        <span class="{{ $digestEmailVerified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                                            · {{ $digestEmailVerified ? 'Doğrulandı' : 'Doğrulama gerekli' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <div class="blue-switch-wrap">
                                        <x-ui.switch name="daily_digest_enabled" :checked="old('daily_digest_enabled', $user->daily_digest_enabled)" />
                                    </div>
                                    <button
                                        type="button"
                                        @click="digestSettingsOpen = true; $nextTick(() => $refs.digestEmail.focus())"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:bg-blue-500/10 dark:hover:text-blue-300"
                                        aria-label="E-posta özeti ayarlarını aç"
                                        title="E-posta özeti ayarları"
                                    >
                                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 sm:w-auto sm:py-2.5">
                                Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <div
            x-cloak
            x-show="digestSettingsOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-[100] flex items-end justify-center bg-slate-950/55 p-0 sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="digest-settings-title"
            @click.self="digestSettingsOpen = false"
        >
            <div x-show="digestSettingsOpen" x-transition class="w-full rounded-t-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900 sm:max-w-md sm:rounded-xl sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="digest-settings-title" class="text-base font-semibold text-gray-950 dark:text-white">E-posta özeti ayarları</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-slate-400">Günlük özetin gönderileceği adresi değiştirin.</p>
                    </div>
                    <button type="button" @click="digestSettingsOpen = false" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Pencereyi kapat">
                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('dashboard.notifications.digest-email') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="daily_digest_email" class="block text-sm font-medium text-gray-900 dark:text-slate-100">Teslimat e-postası</label>
                        <input
                            x-ref="digestEmail"
                            id="daily_digest_email"
                            name="daily_digest_email"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ $digestEmail }}"
                            class="mt-2 block w-full rounded-lg border-slate-300 bg-white text-sm text-gray-950 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        >
                        @error('daily_digest_email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-slate-400">Hesap adresinizden farklı bir adres girerseniz doğrulama bağlantısı gönderilir.</p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" @click="digestSettingsOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Vazgeç</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">E-postayı güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
