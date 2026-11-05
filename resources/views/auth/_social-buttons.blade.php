@php($oauthSettings = \\App\\Models\\SiteSetting::current())
@if ($oauthSettings?->isGoogleLoginEnabled() || $oauthSettings?->isYandexLoginEnabled())
    @if ($oauthSettings?->isGoogleLoginEnabled())
        <a class="btn btn-secondary btn-lg btn-full" href="{{ route('oauth.redirect', ['provider' => 'google']) }}">
            <x-repost-icon name="google" size="20" />
            <span>Google ile devam et</span>
        </a>
    @endif

    @if ($oauthSettings?->isYandexLoginEnabled())
        <a class="btn btn-secondary btn-lg btn-full" href="{{ route('oauth.redirect', ['provider' => 'yandex']) }}">
            <x-repost-icon name="yandex" size="20" />
            <span>Yandex ile devam et</span>
        </a>
    @endif
@endif
