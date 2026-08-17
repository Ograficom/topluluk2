@section('title', 'Gizlilik')

<x-app-layout>
    @php
        $user = auth()->user();
        $levels = [
            'public' => 'Herkese açık',
            'friends' => 'Arkadaşlar',
            'private' => 'Sadece ben',
        ];

        $privacyIcon = function (string $name): string {
            $paths = [
                'shield' => '<path fill="currentColor" stroke="none" d="M11.5 16.23h1v-5.653h-1zm.934-7.412q.182-.182.182-.434q0-.251-.182-.433T12 7.769t-.434.182t-.182.434t.182.433T12 9t.434-.182M12 20.962q-3.014-.895-5.007-3.651T5 11.1V5.692l7-2.615l7 2.615V11.1q0 3.454-1.993 6.21T12 20.963m0-1.062q2.6-.825 4.3-3.3t1.7-5.5V6.375l-6-2.23l-6 2.23V11.1q0 3.025 1.7 5.5t4.3 3.3m0-7.88"/>',
                'following' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5m0-5-5 5"/>',
                'posts' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
                'comments' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/>',
                'chevron' => '<path d="m9 18 6-6-6-6"/>',
            ];

            return '<svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['shield']) . '</svg>';
        };
    @endphp

    <style>
        .privacy-select {
            width: 136px;
            min-height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #fff;
            color: #0f172a;
            padding: 6px 32px 6px 10px;
            font-size: 12.5px;
            line-height: 18px;
            cursor: pointer;
        }

        .privacy-select:focus {
            border-color: #2563eb;
            outline: 2px solid rgba(37, 99, 235, .18);
            outline-offset: 1px;
        }

        html.dark .privacy-select {
            border-color: #344055;
            background-color: #0f172a;
            color: #f8fafc;
        }

        .privacy-setting-row {
            min-height: 58px;
            gap: 10px !important;
            padding: 9px 12px !important;
        }

        .privacy-setting-icon svg {
            width: 18px !important;
            height: 18px !important;
        }

        .privacy-copy-title {
            margin: 0 !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            line-height: 18px !important;
            letter-spacing: 0 !important;
        }

        .privacy-copy-description {
            margin-top: 2px !important;
            font-size: 12.5px !important;
            font-weight: 400 !important;
            line-height: 17px !important;
            letter-spacing: 0 !important;
        }

        @media (max-width: 420px) {
            .privacy-select {
                width: 116px;
                min-height: 34px;
                padding-right: 28px;
                font-size: 12px;
            }
        }
    </style>

    <div class="mx-auto w-full max-w-[var(--profile-shell-width)]">
        <main class="w-full">
            <section class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] w-screen -translate-x-1/2 bg-white text-slate-900 dark:bg-[#111827] dark:text-slate-100 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-lg sm:border sm:border-slate-200 dark:sm:border-[#263247]">
                <header class="flex items-center gap-3 border-b border-slate-200 px-4 py-4 dark:border-[#263247] sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 dark:bg-[#172033]">
                        <img
                            src="{{ $user->profile_photo_url }}"
                            alt="{{ $user->name }}"
                            class="h-7 w-7 shrink-0 rounded-full object-cover"
                        >

                        <span class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ $user->name }}
                        </span>
                    </div>

                    <span class="shrink-0 text-slate-400 dark:text-slate-600">›</span>

                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-slate-400 dark:text-slate-600">›</span>

                    <span class="min-w-0 truncate text-sm font-medium text-slate-700 dark:text-slate-300">
                        Gizlilik
                    </span>
                </header>

                <div class="px-3 py-3 sm:px-4 sm:py-4">
                    @if(session('status') === 'privacy-updated')
                        <div role="status" aria-live="polite" class="mb-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5 text-[13px] text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            Gizlilik ayarları güncellendi.
                        </div>
                    @elseif(session('status') === 'privacy-all-private')
                        <div role="alert" class="mb-3 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2.5 text-[13px] font-medium text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
                            Her şey gizli olmaz.
                        </div>
                    @endif

                    <form action="{{ route('dashboard.privacy.update') }}" method="POST" class="space-y-1">
                        @csrf
                        @method('PUT')

                        <div class="privacy-setting-row flex items-center rounded-lg bg-slate-50 dark:bg-[#172033]">
                            <span class="privacy-setting-icon shrink-0 text-slate-500 dark:text-slate-400">{!! $privacyIcon('following') !!}</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="privacy-copy-title text-slate-900 dark:text-white">Takip edilenleri gizle</h2>
                                <p class="privacy-copy-description text-slate-500 dark:text-slate-400">Takip ettiğiniz hesapları kimlerin görebileceğini seçin</p>
                            </div>
                            <select class="privacy-select shrink-0" name="following_visibility" aria-label="Takip edilenlerin görünürlüğü" onchange="this.form.requestSubmit()">
                                @foreach($levels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('following_visibility', $user->following_visibility ?? 'public') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="privacy-setting-row flex items-center rounded-lg hover:bg-slate-50 dark:hover:bg-[#172033]">
                            <span class="privacy-setting-icon shrink-0 text-slate-500 dark:text-slate-400">{!! $privacyIcon('posts') !!}</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="privacy-copy-title text-slate-900 dark:text-white">Tüm paylaşımların gizliliği</h2>
                                <p class="privacy-copy-description text-slate-500 dark:text-slate-400">Yayımladığınız gönderilerin genel görünürlüğünü belirleyin</p>
                            </div>
                            <select class="privacy-select shrink-0" name="posts_visibility" aria-label="Paylaşımların görünürlüğü" onchange="this.form.requestSubmit()">
                                @foreach($levels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('posts_visibility', $user->posts_visibility ?? 'public') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="privacy-setting-row flex items-center rounded-lg hover:bg-slate-50 dark:hover:bg-[#172033]">
                            <span class="privacy-setting-icon shrink-0 text-slate-500 dark:text-slate-400">{!! $privacyIcon('comments') !!}</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="privacy-copy-title text-slate-900 dark:text-white">Yorum gizliliği</h2>
                                <p class="privacy-copy-description text-slate-500 dark:text-slate-400">Yorumlarınızı kimlerin görebileceğini seçin</p>
                            </div>
                            <select class="privacy-select shrink-0" name="comments_visibility" aria-label="Yorumların görünürlüğü" onchange="this.form.requestSubmit()">
                                @foreach($levels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('comments_visibility', $user->comments_visibility ?? 'public') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
