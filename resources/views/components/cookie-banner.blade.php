@if(($showBanner ?? false) && isset($policy))
    <div id="cookie-banner" class="cookie-consent-bar">
        <div class="cookie-consent-bar__surface">
            <div class="cookie-consent-bar__content">
                <div class="cookie-consent-bar__message-wrap">
                    <p class="cookie-consent-bar__message">
                        <a href="{{ route('cookie.policy') }}" class="cookie-consent-bar__link">Çerezler</a>
                        kullanıyoruz.
                    </p>
                </div>

                <div class="cookie-consent-bar__actions">
                    <button type="button"
                            data-consent-action="accept"
                            class="cookie-consent-bar__accept">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m5 12.5 4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>{{ __('site.cookie.accept') }}</span>
                    </button>

                    <button type="button"
                            data-consent-action="reject"
                            class="cookie-consent-bar__reject"
                            aria-label="{{ __('site.cookie.close') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .cookie-consent-bar {
            position: fixed;
            left: max(16px, calc(50% - 636px));
            bottom: 14px;
            z-index: 10000;
            width: auto;
            max-width: min(340px, calc(100vw - 32px));
            font-family: Arial, Helvetica, sans-serif;
        }
        .cookie-consent-bar__surface {
            padding: 7px 8px 7px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 4px 18px rgba(15,23,42,.10);
            backdrop-filter: blur(10px);
        }
        .cookie-consent-bar__content,
        .cookie-consent-bar__actions {
            display: flex;
            align-items: center;
        }
        .cookie-consent-bar__content { gap: 10px; }
        .cookie-consent-bar__message-wrap { min-width: 0; }
        .cookie-consent-bar__message {
            margin: 0;
            color: #64748b;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.25;
        }
        html body #cookie-banner a.cookie-consent-bar__link {
            color: #2563eb !important;
            text-decoration: underline !important;
            text-underline-offset: 2px !important;
        }
        .cookie-consent-bar__actions { flex: 0 0 auto; gap: 3px; }
        .cookie-consent-bar__accept {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 28px;
            padding: 0 13px;
            border: 0;
            border-radius: 999px;
            background: #f1f1f1;
            color: #27272a;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
        }
        html body #cookie-banner .cookie-consent-bar__accept svg {
            display: block !important;
            width: 13px !important;
            min-width: 13px !important;
            height: 13px !important;
            min-height: 13px !important;
            flex: 0 0 13px !important;
            color: currentColor !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        @media (hover: hover) {
            html body #cookie-banner .cookie-consent-bar__accept:hover {
                background: #2563eb !important;
                background-color: #2563eb !important;
                color: #ffffff !important;
            }
        }
        .cookie-consent-bar__reject {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
        }
        .cookie-consent-bar__reject svg { width: 13px; height: 13px; }
        @media (max-width: 640px) {
            html body #cookie-banner.cookie-consent-bar {
                --cookie-mobile-font-size: 11px;
                left: 50% !important;
                right: auto !important;
                bottom: max(6px, env(safe-area-inset-bottom, 0px)) !important;
                width: calc(100% - 16px) !important;
                max-width: 390px !important;
                transform: translateX(-50%) !important;
            }
            html body:has([data-mobile-bottom-nav]) #cookie-banner.cookie-consent-bar {
                bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important;
            }
            html body #cookie-banner .cookie-consent-bar__surface {
                width: 100% !important;
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                padding: 4px 5px 4px 10px !important;
                overflow: hidden !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 10px rgba(15,23,42,.08) !important;
                box-sizing: border-box !important;
            }
            html body #cookie-banner .cookie-consent-bar__content {
                width: 100% !important;
                height: 30px !important;
                gap: 6px !important;
            }
            html body #cookie-banner .cookie-consent-bar__message-wrap {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                overflow: hidden !important;
            }
            html body #cookie-banner .cookie-consent-bar__message {
                display: block !important;
                overflow: hidden !important;
                margin: 0 !important;
                font-size: var(--cookie-mobile-font-size) !important;
                line-height: 30px !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }
            html body #cookie-banner .cookie-consent-bar__message,
            html body #cookie-banner .cookie-consent-bar__message a.cookie-consent-bar__link,
            html body #cookie-banner .cookie-consent-bar__accept,
            html body #cookie-banner .cookie-consent-bar__accept span {
                font-size: var(--cookie-mobile-font-size) !important;
                font-weight: 400 !important;
            }
            html body #cookie-banner .cookie-consent-bar__actions {
                flex: 0 0 auto !important;
                height: 30px !important;
                gap: 2px !important;
            }
            html body #cookie-banner .cookie-consent-bar__accept {
                width: auto !important;
                height: 26px !important;
                min-height: 26px !important;
                max-height: 26px !important;
                padding: 0 10px !important;
                border-radius: 999px !important;
                font-size: var(--cookie-mobile-font-size) !important;
                line-height: 26px !important;
            }
            html body #cookie-banner .cookie-consent-bar__accept span {
                line-height: 26px !important;
                white-space: nowrap !important;
            }
            html body #cookie-banner .cookie-consent-bar__accept svg {
                width: 12px !important;
                height: 12px !important;
                flex-basis: 12px !important;
            }
            html body #cookie-banner .cookie-consent-bar__reject {
                width: 24px !important;
                min-width: 24px !important;
                height: 24px !important;
                min-height: 24px !important;
                max-height: 24px !important;
                padding: 0 !important;
            }
        }
    </style>

    <script>
        (() => {
            const banner = document.getElementById('cookie-banner');
            if (!banner) return;

            const endpoint = "{{ route('cookie-consent.store') }}";
            const policyVersion = {{ (int) $policy->version }};

            const sendDecision = async (decision) => {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ decision, version: policyVersion }),
                    });

                    if (!response.ok) {
                        console.error('Cookie consent could not be saved', await response.text());
                        return;
                    }

                    banner.remove();
                } catch (error) {
                    console.error('Cookie consent save error', error);
                }
            };

            banner.querySelector('[data-consent-action="accept"]')?.addEventListener('click', () => sendDecision('accept'));
            banner.querySelector('[data-consent-action="reject"]')?.addEventListener('click', () => sendDecision('reject'));
        })();
    </script>
@endif
