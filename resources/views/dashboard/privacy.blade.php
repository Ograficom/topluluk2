@section('title', 'Gizlilik')

<x-app-layout>
    @php
        $user = auth()->user();
        $levels = [
            'public' => 'Herkese açık',
            'friends' => 'Arkadaşlar',
            'private' => 'Sadece ben',
        ];
        $levelIconNames = [
            'public' => 'globe',
            'friends' => 'users',
            'private' => 'lock',
        ];

        $privacyIcon = function (string $name): string {
            $paths = [
                'shield' => '<path fill="currentColor" stroke="none" d="M11.5 16.23h1v-5.653h-1zm.934-7.412q.182-.182.182-.434q0-.251-.182-.433T12 7.769t-.434.182t-.182.434t.182.433T12 9t.434-.182M12 20.962q-3.014-.895-5.007-3.651T5 11.1V5.692l7-2.615l7 2.615V11.1q0 3.454-1.993 6.21T12 20.963m0-1.062q2.6-.825 4.3-3.3t1.7-5.5V6.375l-6-2.23l-6 2.23V11.1q0 3.025 1.7 5.5t4.3 3.3m0-7.88"/>',
                'following' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5m0-5-5 5"/>',
                'posts' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
                'comments' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/>',
                'chevron' => '<path d="m9 18 6-6-6-6"/>',
                'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
                'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>',
                'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
                'check' => '<path d="m5 12 4 4L19 6"/>',
            ];

            return '<svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['shield']) . '</svg>';
        };
    @endphp

    <style>
        .privacy-menu {
            position: relative;
            flex: 0 0 auto;
        }

        .privacy-menu-trigger {
            display: inline-flex;
            width: 144px;
            min-height: 38px;
            align-items: center;
            gap: 8px;
            padding: 5px 9px;
            border: 1px solid #dbe2ea;
            border-radius: 9px;
            background: #fff;
            color: #1e293b;
            font-size: 12.5px;
            font-weight: 500;
            line-height: 18px;
            text-align: left;
            cursor: pointer;
            transition: border-color 140ms ease, background-color 140ms ease, box-shadow 140ms ease;
        }

        .privacy-menu-trigger:hover {
            border-color: #b8c4d3;
            background: #f8fafc;
        }

        .privacy-menu-trigger:focus-visible {
            border-color: #2563eb;
            outline: 0;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .16);
        }

        .privacy-menu-trigger__icon,
        .privacy-menu-option__icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
        }

        .privacy-menu-trigger__icon {
            width: 26px;
            height: 26px;
            background: #eaf2ff;
            color: #2563eb;
        }

        .privacy-menu-trigger__icon svg,
        .privacy-menu-option__icon svg {
            width: 15px !important;
            height: 15px !important;
        }

        .privacy-menu-trigger__label {
            min-width: 0;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .privacy-menu-trigger__chevron {
            display: inline-flex;
            color: #64748b;
            transition: transform 160ms cubic-bezier(.23, 1, .32, 1);
        }

        .privacy-menu-trigger__chevron svg {
            width: 14px !important;
            height: 14px !important;
        }

        .privacy-menu[data-open="true"] .privacy-menu-trigger__chevron {
            transform: rotate(180deg);
        }

        .privacy-menu-panel {
            position: absolute;
            z-index: 40;
            top: calc(100% + 6px);
            right: 0;
            display: none;
            width: 184px;
            padding: 6px;
            border: 1px solid #dbe2ea;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 16px 38px rgba(15, 23, 42, .16), 0 3px 10px rgba(15, 23, 42, .08);
            transform-origin: top right;
        }

        .privacy-menu[data-open="true"] .privacy-menu-panel {
            display: grid;
            gap: 3px;
            animation: privacy-menu-in 170ms cubic-bezier(.23, 1, .32, 1);
        }

        @keyframes privacy-menu-in {
            from { opacity: 0; transform: translateY(-4px) scale(.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .privacy-menu-option {
            display: flex;
            width: 100%;
            min-height: 40px;
            align-items: center;
            gap: 9px;
            padding: 5px 8px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #334155;
            font-size: 13px;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            transition: background-color 130ms ease, color 130ms ease, transform 100ms ease;
        }

        .privacy-menu-option:hover,
        .privacy-menu-option:focus-visible {
            background: #f1f5f9;
            color: #0f172a;
            outline: 0;
        }

        .privacy-menu-option:active {
            transform: scale(.98);
        }

        .privacy-menu-option[aria-selected="true"] {
            background: #eaf2ff;
            color: #1d4ed8;
        }

        .privacy-menu-option__icon {
            width: 28px;
            height: 28px;
        }

        .privacy-menu-option[data-value="public"] .privacy-menu-option__icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .privacy-menu-option[data-value="friends"] .privacy-menu-option__icon {
            background: #d1fae5;
            color: #059669;
        }

        .privacy-menu-option[data-value="private"] .privacy-menu-option__icon {
            background: #fef3c7;
            color: #b45309;
        }

        .privacy-menu-option__label {
            flex: 1;
        }

        .privacy-menu-option__check {
            display: inline-flex;
            color: #2563eb;
            opacity: 0;
        }

        .privacy-menu-option__check svg {
            width: 16px !important;
            height: 16px !important;
        }

        .privacy-menu-option[aria-selected="true"] .privacy-menu-option__check {
            opacity: 1;
        }

        html.dark .privacy-menu-trigger,
        html.dark .privacy-menu-panel {
            border-color: #344055;
            background: #172033;
            color: #e5edf8;
        }

        html.dark .privacy-menu-trigger:hover {
            border-color: #4b5b73;
            background: #1d293d;
        }

        html.dark .privacy-menu-trigger__icon {
            background: #223452;
            color: #93c5fd;
        }

        html.dark .privacy-menu-option {
            color: #dbe4f0;
        }

        html.dark .privacy-menu-option:hover,
        html.dark .privacy-menu-option:focus-visible {
            background: #263247;
            color: #f8fafc;
        }

        html.dark .privacy-menu-option[aria-selected="true"] {
            background: #223452;
            color: #bfdbfe;
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
            .privacy-menu-trigger {
                width: 124px;
                min-height: 36px;
                padding-inline: 7px;
                font-size: 12px;
            }

            .privacy-menu-panel {
                width: min(178px, calc(100vw - 24px));
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
                            @php($selectedPrivacyLevel = old('following_visibility', $user->following_visibility ?? 'public'))
                            @php($selectedPrivacyLevel = isset($levels[$selectedPrivacyLevel]) ? $selectedPrivacyLevel : 'public')
                            <div class="privacy-menu" data-privacy-menu data-open="false">
                                <input type="hidden" name="following_visibility" value="{{ $selectedPrivacyLevel }}" data-privacy-input>
                                <button type="button" class="privacy-menu-trigger" aria-label="Takip edilenlerin görünürlüğü" aria-haspopup="listbox" aria-expanded="false" aria-controls="following-privacy-menu" data-privacy-trigger>
                                    <span class="privacy-menu-trigger__icon" data-privacy-trigger-icon>{!! $privacyIcon($levelIconNames[$selectedPrivacyLevel]) !!}</span>
                                    <span class="privacy-menu-trigger__label" data-privacy-trigger-label>{{ $levels[$selectedPrivacyLevel] }}</span>
                                    <span class="privacy-menu-trigger__chevron">{!! $privacyIcon('chevron-down') !!}</span>
                                </button>
                                <div id="following-privacy-menu" class="privacy-menu-panel" role="listbox" aria-label="Takip edilenlerin görünürlüğü" data-privacy-panel>
                                    @foreach($levels as $value => $label)
                                        <button type="button" class="privacy-menu-option" role="option" aria-selected="{{ $selectedPrivacyLevel === $value ? 'true' : 'false' }}" data-value="{{ $value }}" data-privacy-option>
                                            <span class="privacy-menu-option__icon">{!! $privacyIcon($levelIconNames[$value]) !!}</span>
                                            <span class="privacy-menu-option__label">{{ $label }}</span>
                                            <span class="privacy-menu-option__check" aria-hidden="true">{!! $privacyIcon('check') !!}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="privacy-setting-row flex items-center rounded-lg hover:bg-slate-50 dark:hover:bg-[#172033]">
                            <span class="privacy-setting-icon shrink-0 text-slate-500 dark:text-slate-400">{!! $privacyIcon('posts') !!}</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="privacy-copy-title text-slate-900 dark:text-white">Tüm paylaşımların gizliliği</h2>
                                <p class="privacy-copy-description text-slate-500 dark:text-slate-400">Yayımladığınız gönderilerin genel görünürlüğünü belirleyin</p>
                            </div>
                            @php($selectedPrivacyLevel = old('posts_visibility', $user->posts_visibility ?? 'public'))
                            @php($selectedPrivacyLevel = isset($levels[$selectedPrivacyLevel]) ? $selectedPrivacyLevel : 'public')
                            <div class="privacy-menu" data-privacy-menu data-open="false">
                                <input type="hidden" name="posts_visibility" value="{{ $selectedPrivacyLevel }}" data-privacy-input>
                                <button type="button" class="privacy-menu-trigger" aria-label="Paylaşımların görünürlüğü" aria-haspopup="listbox" aria-expanded="false" aria-controls="posts-privacy-menu" data-privacy-trigger>
                                    <span class="privacy-menu-trigger__icon" data-privacy-trigger-icon>{!! $privacyIcon($levelIconNames[$selectedPrivacyLevel]) !!}</span>
                                    <span class="privacy-menu-trigger__label" data-privacy-trigger-label>{{ $levels[$selectedPrivacyLevel] }}</span>
                                    <span class="privacy-menu-trigger__chevron">{!! $privacyIcon('chevron-down') !!}</span>
                                </button>
                                <div id="posts-privacy-menu" class="privacy-menu-panel" role="listbox" aria-label="Paylaşımların görünürlüğü" data-privacy-panel>
                                    @foreach($levels as $value => $label)
                                        <button type="button" class="privacy-menu-option" role="option" aria-selected="{{ $selectedPrivacyLevel === $value ? 'true' : 'false' }}" data-value="{{ $value }}" data-privacy-option>
                                            <span class="privacy-menu-option__icon">{!! $privacyIcon($levelIconNames[$value]) !!}</span>
                                            <span class="privacy-menu-option__label">{{ $label }}</span>
                                            <span class="privacy-menu-option__check" aria-hidden="true">{!! $privacyIcon('check') !!}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="privacy-setting-row flex items-center rounded-lg hover:bg-slate-50 dark:hover:bg-[#172033]">
                            <span class="privacy-setting-icon shrink-0 text-slate-500 dark:text-slate-400">{!! $privacyIcon('comments') !!}</span>
                            <div class="min-w-0 flex-1">
                                <h2 class="privacy-copy-title text-slate-900 dark:text-white">Yorum gizliliği</h2>
                                <p class="privacy-copy-description text-slate-500 dark:text-slate-400">Yorumlarınızı kimlerin görebileceğini seçin</p>
                            </div>
                            @php($selectedPrivacyLevel = old('comments_visibility', $user->comments_visibility ?? 'public'))
                            @php($selectedPrivacyLevel = isset($levels[$selectedPrivacyLevel]) ? $selectedPrivacyLevel : 'public')
                            <div class="privacy-menu" data-privacy-menu data-open="false">
                                <input type="hidden" name="comments_visibility" value="{{ $selectedPrivacyLevel }}" data-privacy-input>
                                <button type="button" class="privacy-menu-trigger" aria-label="Yorumların görünürlüğü" aria-haspopup="listbox" aria-expanded="false" aria-controls="comments-privacy-menu" data-privacy-trigger>
                                    <span class="privacy-menu-trigger__icon" data-privacy-trigger-icon>{!! $privacyIcon($levelIconNames[$selectedPrivacyLevel]) !!}</span>
                                    <span class="privacy-menu-trigger__label" data-privacy-trigger-label>{{ $levels[$selectedPrivacyLevel] }}</span>
                                    <span class="privacy-menu-trigger__chevron">{!! $privacyIcon('chevron-down') !!}</span>
                                </button>
                                <div id="comments-privacy-menu" class="privacy-menu-panel" role="listbox" aria-label="Yorumların görünürlüğü" data-privacy-panel>
                                    @foreach($levels as $value => $label)
                                        <button type="button" class="privacy-menu-option" role="option" aria-selected="{{ $selectedPrivacyLevel === $value ? 'true' : 'false' }}" data-value="{{ $value }}" data-privacy-option>
                                            <span class="privacy-menu-option__icon">{!! $privacyIcon($levelIconNames[$value]) !!}</span>
                                            <span class="privacy-menu-option__label">{{ $label }}</span>
                                            <span class="privacy-menu-option__check" aria-hidden="true">{!! $privacyIcon('check') !!}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menus = Array.from(document.querySelectorAll('[data-privacy-menu]'));

            const optionsFor = (menu) => Array.from(menu.querySelectorAll('[data-privacy-option]'));

            const closeMenu = (menu, restoreFocus = false) => {
                menu.dataset.open = 'false';
                const trigger = menu.querySelector('[data-privacy-trigger]');
                trigger?.setAttribute('aria-expanded', 'false');
                if (restoreFocus) trigger?.focus();
            };

            const closeAll = (except = null) => {
                menus.forEach((menu) => {
                    if (menu !== except) closeMenu(menu);
                });
            };

            const openMenu = (menu, focusIndex = null) => {
                closeAll(menu);
                menu.dataset.open = 'true';
                const trigger = menu.querySelector('[data-privacy-trigger]');
                trigger?.setAttribute('aria-expanded', 'true');

                if (focusIndex !== null) {
                    window.requestAnimationFrame(() => {
                        const options = optionsFor(menu);
                        options[focusIndex < 0 ? options.length - 1 : focusIndex]?.focus();
                    });
                }
            };

            menus.forEach((menu) => {
                const trigger = menu.querySelector('[data-privacy-trigger]');
                const input = menu.querySelector('[data-privacy-input]');
                const label = menu.querySelector('[data-privacy-trigger-label]');
                const triggerIcon = menu.querySelector('[data-privacy-trigger-icon]');
                const panel = menu.querySelector('[data-privacy-panel]');

                trigger?.addEventListener('click', () => {
                    if (menu.dataset.open === 'true') closeMenu(menu);
                    else openMenu(menu);
                });

                trigger?.addEventListener('keydown', (event) => {
                    if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
                    event.preventDefault();
                    openMenu(menu, event.key === 'ArrowUp' ? -1 : 0);
                });

                panel?.addEventListener('keydown', (event) => {
                    const options = optionsFor(menu);
                    const current = options.indexOf(document.activeElement);
                    let next = current;

                    if (event.key === 'ArrowDown') next = (current + 1 + options.length) % options.length;
                    else if (event.key === 'ArrowUp') next = (current - 1 + options.length) % options.length;
                    else if (event.key === 'Home') next = 0;
                    else if (event.key === 'End') next = options.length - 1;
                    else if (event.key === 'Escape') {
                        event.preventDefault();
                        closeMenu(menu, true);
                        return;
                    } else if (event.key === 'Tab') {
                        closeMenu(menu);
                        return;
                    } else return;

                    event.preventDefault();
                    options[next]?.focus();
                });

                optionsFor(menu).forEach((option) => {
                    option.addEventListener('click', () => {
                        const value = option.dataset.value;
                        const optionLabel = option.querySelector('.privacy-menu-option__label');
                        const optionIcon = option.querySelector('.privacy-menu-option__icon');

                        input.value = value;
                        label.textContent = optionLabel?.textContent?.trim() || '';
                        triggerIcon.innerHTML = optionIcon?.innerHTML || '';
                        optionsFor(menu).forEach((item) => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
                        closeMenu(menu);
                        menu.closest('form')?.requestSubmit();
                    });
                });
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-privacy-menu]')) closeAll();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') menus.forEach((menu) => {
                    if (menu.dataset.open === 'true') closeMenu(menu, true);
                });
            });
        });
    </script>
</x-app-layout>
