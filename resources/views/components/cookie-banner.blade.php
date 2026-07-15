@if(($showBanner ?? false) && isset($policy))
    <div id="cookie-banner" class="cookie-consent-bar">
        <div class="cookie-consent-bar__surface">
            <div class="cookie-consent-bar__content">
                <div class="cookie-consent-bar__message-wrap">
                    <p class="cookie-consent-bar__message">
                        {{ $policy->banner_message ?? __('site.cookie.default_message') }}
                        @if (Route::has('cookie.policy'))
                            <a href="{{ route('cookie.policy') }}" class="cookie-consent-bar__link">
                                {{ __('site.common.privacy_policy') }}
                            </a>
                        @endif
                    </p>
                </div>

                <div class="cookie-consent-bar__actions">
                    <button type="button"
                            data-consent-action="accept"
                            class="cookie-consent-bar__accept">
                        {{ __('site.cookie.accept') }}
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
        .cookie-consent-bar__link { color: #475569; text-decoration: none; }
        .cookie-consent-bar__actions { flex: 0 0 auto; gap: 3px; }
        .cookie-consent-bar__accept {
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
            .cookie-consent-bar {
                left: 6px;
                right: 6px;
                bottom: 6px;
                width: auto;
                max-width: none;
            }
            .cookie-consent-bar__surface {
                padding: 5px 6px 5px 10px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(15,23,42,.08);
            }
            .cookie-consent-bar__content { gap: 6px; }
            .cookie-consent-bar__message {
                overflow: hidden;
                font-size: 10px;
                line-height: 1.15;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .cookie-consent-bar__accept {
                min-height: 26px;
                padding: 0 11px;
                font-size: 10px;
            }
            .cookie-consent-bar__reject { width: 24px; height: 24px; }
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
