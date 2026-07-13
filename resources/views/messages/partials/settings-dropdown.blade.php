@php
    $buttonClass = $buttonClass ?? 'inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100';
    $panelClass = $panelClass ?? '';
    $buttonLabel = $buttonLabel ?? __('messages.actions.settings');
@endphp

@once
    @push('head')
        <style>
            .message-settings-menu {
                position: relative;
            }

            .message-settings-menu summary {
                list-style: none;
            }

            .message-settings-menu summary::-webkit-details-marker {
                display: none;
            }

            .message-settings-panel {
                position: absolute;
                top: calc(100% + 10px);
                right: 0;
                z-index: 40;
                width: min(92vw, 320px);
                border-radius: 24px;
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: #ffffff;
                padding: 12px;
                box-shadow: 0 24px 48px rgba(15, 23, 42, 0.14);
            }

            .message-settings-backdrop {
                display: none;
            }

            .message-settings-panel--left {
                left: 0;
                right: auto;
            }

            .message-settings-row {
                border-radius: 18px;
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: #f1f5f9;
                padding: 14px;
                transition: background-color 0.16s ease;
            }

            .message-settings-row:hover {
                background: #ffffff;
            }

            .message-settings-panel-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 12px;
                padding-inline: 4px;
            }

            .message-settings-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                border-radius: 999px;
                border: 1px solid rgba(148, 163, 184, 0.22);
                background: #ffffff;
                color: #64748b;
                transition: background-color 0.16s ease, color 0.16s ease;
            }

            .message-settings-close:hover {
                background: #f1f5f9;
                color: #0f172a;
            }

            @media (max-width: 639px) {
                .message-settings-menu[open] .message-settings-backdrop {
                    position: fixed;
                    inset: 0;
                    z-index: 65;
                    display: block;
                    background: rgba(15, 23, 42, 0.14);
                }

                .message-settings-panel {
                    position: fixed;
                    inset-inline: 12px;
                    top: auto;
                    right: 12px;
                    left: 12px;
                    bottom: calc(82px + env(safe-area-inset-bottom));
                    z-index: 70;
                    width: auto;
                    max-width: none;
                    border-radius: 28px;
                    background: #f7f8fa;
                    padding: 14px;
                    box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
                }

                .message-settings-row {
                    background: #ffffff;
                }

                .message-settings-row:hover {
                    background: #f1f5f9;
                }
            }
        </style>
    @endpush
@endonce

<details class="message-settings-menu" data-message-settings-menu>
    <summary class="{{ $buttonClass }}" aria-label="{{ $buttonLabel }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .33 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .31 1.7 1.7 0 0 0-.8 1.45V21a2 2 0 1 1-4 0v-.08a1.7 1.7 0 0 0-1-1.53 1.7 1.7 0 0 0-1.87.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.53-1H3a2 2 0 1 1 0-4h.08a1.7 1.7 0 0 0 1.53-1A1.7 1.7 0 0 0 4.6 7a1.7 1.7 0 0 0-.33-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.31 1.7 1.7 0 0 0 .8-1.45V3a2 2 0 1 1 4 0v.08a1.7 1.7 0 0 0 1 1.53 1.7 1.7 0 0 0 1.87-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.53 1H21a2 2 0 1 1 0 4h-.08a1.7 1.7 0 0 0-1.53 1Z"/>
        </svg>
    </summary>

    <button type="button" class="message-settings-backdrop" data-message-settings-backdrop aria-label="{{ __('messages.actions.close') }}"></button>

    <div class="message-settings-panel {{ $panelClass }}">
        <div class="message-settings-panel-head">
            <div class="px-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">{{ __('messages.settings.title') }}</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ __('messages.settings.subtitle') }}</p>
            </div>

            <button type="button" class="message-settings-close" data-message-settings-close aria-label="{{ __('messages.actions.close') }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>

        <form method="post" action="{{ route('messages.settings.update') }}" class="space-y-3">
            @csrf

            <div class="message-settings-row flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ __('messages.settings.allow_messages') }}</div>
                    <div class="text-xs text-slate-500">{{ __('messages.settings.allow_messages_help') }}</div>
                </div>
                <x-ui.switch
                    name="allow_messages"
                    :checked="$preferences->allow_messages"
                />
            </div>

            <div class="message-settings-row flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ __('messages.settings.following_only') }}</div>
                    <div class="text-xs text-slate-500">{{ __('messages.settings.following_only_help') }}</div>
                </div>
                <x-ui.switch
                    name="allow_following_only"
                    :checked="$preferences->allow_following_only"
                />
            </div>

            <div class="flex justify-end pt-1">
                <button class="inline-flex h-10 items-center justify-center rounded-full bg-slate-900 px-5 text-sm font-medium text-white transition hover:bg-slate-800">
                    {{ __('messages.actions.save') }}
                </button>
            </div>
        </form>
    </div>
</details>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menus = Array.from(document.querySelectorAll('[data-message-settings-menu]'));
            if (!menus.length) {
                return;
            }

            const closeMenus = (activeMenu = null) => {
                menus.forEach((menu) => {
                    if (menu !== activeMenu) {
                        menu.removeAttribute('open');
                    }
                });
            };

            const syncBodyLock = () => {
                const hasOpenMobileMenu = window.innerWidth < 640 && menus.some((menu) => menu.hasAttribute('open'));
                document.documentElement.classList.toggle('overflow-hidden', hasOpenMobileMenu);
                document.body.classList.toggle('overflow-hidden', hasOpenMobileMenu);
            };

            menus.forEach((menu) => {
                const summary = menu.querySelector('summary');
                if (!summary) {
                    return;
                }

                summary.addEventListener('click', () => {
                    window.requestAnimationFrame(() => {
                        if (menu.hasAttribute('open')) {
                            closeMenus(menu);
                        }
                        syncBodyLock();
                    });
                });

                menu.querySelectorAll('[data-message-settings-backdrop], [data-message-settings-close]').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        menu.removeAttribute('open');
                        syncBodyLock();
                    });
                });

                menu.addEventListener('toggle', syncBodyLock);
            });

            document.addEventListener('click', (event) => {
                if (event.target instanceof Element && menus.some((menu) => menu.contains(event.target))) {
                    return;
                }

                closeMenus();
                syncBodyLock();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenus();
                    syncBodyLock();
                }
            });

            window.addEventListener('resize', syncBodyLock);
        });
    </script>
@endonce
