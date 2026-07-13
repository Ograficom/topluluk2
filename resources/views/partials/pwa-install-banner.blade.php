@php
    $pwa = \App\Models\PwaSetting::currentOrNull();
    $customPwaInstallBannerEnabled = (bool) config('app.custom_pwa_install_banner', false);
    $bannerLogo = null;
    if ($pwa && $pwa->is_enabled) {
        $bannerLogo = $pwa->icon_192 ?: $pwa->icon_512;
    }
@endphp
@if ($pwa && $pwa->is_enabled && $pwa->install_banner_enabled && $customPwaInstallBannerEnabled && !request()->routeIs('video'))
    <div data-pwa-install-banner class="fixed inset-x-0 bottom-4 z-[80] hidden px-4 sm:px-6">
        <div class="mx-auto flex max-w-3xl items-center gap-3 rounded-[26px] border border-slate-200 bg-white/95 px-4 py-3 shadow-[0_24px_60px_-30px_rgba(15,23,42,0.45)] backdrop-blur">
            @if ($bannerLogo)
                <img src="{{ $pwa->iconUrl($bannerLogo) }}" alt="{{ $pwa->app_name ?? __('site.pwa.app') }}" class="h-10 w-10 rounded-xl object-cover">
            @endif
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-900" data-pwa-install-title>
                    {{ $pwa->install_banner_title ?? ($pwa->app_name ?? __('site.pwa.install_title')) }}
                </p>
                <p class="text-xs text-slate-600" data-pwa-install-description>
                    {{ $pwa->install_banner_description ?? __('site.pwa.install_description') }}
                </p>
                <p class="hidden pt-1 text-[11px] text-slate-500" data-pwa-install-helper></p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" data-pwa-install class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v12" />
                        <path d="M7 10l5 5 5-5" />
                        <path d="M5 21h14" />
                    </svg>
                    <span data-pwa-install-button-label>{{ $pwa->install_banner_button_label ?? __('site.pwa.install_button') }}</span>
                </button>
                <button type="button" data-pwa-install-close class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100" aria-label="{{ __('site.pwa.close') }}">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6L6 18" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif


