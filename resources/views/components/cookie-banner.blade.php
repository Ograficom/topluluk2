@if(($showBanner ?? false) && isset($policy))
    <div id="cookie-banner" class="fixed bottom-3 left-1/2 z-[60] w-[calc(100%-1.5rem)] max-w-md -translate-x-1/2 sm:bottom-4 sm:w-[calc(100%-2rem)]">
        <div class="rounded-2xl px-3 py-2.5 backdrop-blur-2xl backdrop-saturate-150 sm:px-4 sm:py-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <div class="flex items-start gap-2.5 sm:items-center sm:gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-2xl bg-transparent text-slate-900 sm:h-9 sm:w-9">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 1 0 9 9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 7.5a2.7 2.7 0 0 1-2.7-2.7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9.2h0.01M12.6 7.6h0.01M15.7 10.2h0.01M10.6 13.4h0.01M14.8 14.8h0.01"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.9 18.2a1.8 1.8 0 0 1-.9-2.3 1.8 1.8 0 0 1 1.9-1.1 1.8 1.8 0 0 1 1.6-1.7 1.8 1.8 0 0 1 2.2.9 1.8 1.8 0 0 1 2.4.6 1.8 1.8 0 0 1-.1 2.4"/>
                        </svg>
                    </div>

                    <p class="min-w-0 text-[11px] leading-relaxed text-slate-600 sm:text-xs">
                        {{ $policy->banner_message ?? __('site.cookie.default_message') }}
                        @if (Route::has('cookie.policy'))
                            <a href="{{ route('cookie.policy') }}" class="font-semibold text-slate-700 underline underline-offset-4 hover:text-slate-900">
                                {{ __('site.common.privacy_policy') }}
                            </a>
                        @endif
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 sm:ml-auto">
                    <button type="button"
                            data-consent-action="accept"
                            class="whitespace-nowrap rounded-full bg-slate-900 px-3.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-slate-800 active:scale-95 sm:px-4 sm:py-2 sm:text-xs">
                        {{ __('site.cookie.accept') }}
                    </button>

                    <button type="button"
                            data-consent-action="reject"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-900 transition hover:bg-slate-100 active:scale-95 sm:h-9 sm:w-9"
                            aria-label="{{ __('site.cookie.close') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

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
