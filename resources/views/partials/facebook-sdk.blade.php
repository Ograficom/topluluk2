@php
    $facebookAppId = config('services.facebook.client_id');
    $facebookSdkVersion = env('FACEBOOK_SDK_VERSION', 'v23.0');
    $facebookLocale = app()->getLocale() === 'tr' ? 'tr_TR' : 'en_US';
@endphp

@if (filled($facebookAppId))
    <div id="facebook-jssdk"></div>
    <script>
        window.fbAsyncInit = function () {
            FB.init({
                appId: @js($facebookAppId),
                cookie: true,
                xfbml: true,
                version: @js($facebookSdkVersion),
            });

            FB.AppEvents.logPageView();
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }

            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/{{ $facebookLocale }}/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
@endif
