<x-guest-layout>
    <style>
        .simple-auth-page,
        .simple-auth-page * { box-sizing: border-box; }
        .simple-auth-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
            background: #f4f4f5;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
        }
        .simple-auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 26px;
            color: #18181b;
            font-size: 22px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: -1.5px;
        }
        .simple-auth-brand img {
            display: block;
            width: 34px;
            height: 34px;
            object-fit: contain;
        }
        .simple-auth-card {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0;
            padding: 68px 24px 26px;
            border: 1px solid #d9d9d9;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,.12);
        }
        .simple-auth-close {
            position: absolute;
            top: 14px;
            right: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: #f4f4f5;
            color: #18181b;
            cursor: pointer;
        }
        .simple-auth-close svg { width: 20px; height: 20px; }
        .simple-auth-field { margin-bottom: 22px; }
        .simple-auth-label {
            display: block;
            margin-bottom: 7px;
            color: #111;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
        }
        .simple-auth-input {
            display: block;
            width: 100%;
            height: 36px;
            padding: 0 11px;
            border: 1px solid #dedede;
            border-radius: 6px;
            background: #fff;
            color: #111;
            font-size: 13px;
            outline: none;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
        }
        .simple-auth-input:focus {
            border-color: #10a37f;
            box-shadow: 0 0 0 1px #10a37f;
        }
        .simple-auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 0 0 26px;
        }
        .simple-auth-remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #71717a;
            font-size: 12px;
            white-space: nowrap;
        }
        .simple-auth-remember input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: #7fcdb7;
        }
        .simple-auth-link {
            color: #71717a;
            font-size: 12px;
            text-decoration: none;
        }
        .simple-auth-link:hover { color: #111; text-decoration: underline; }
        .simple-auth-submit {
            width: 100%;
            height: 36px;
            border: 0;
            border-radius: 6px;
            background: #7fcdb7;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .simple-auth-submit:hover { background: #70bea8; }
        .simple-auth-register {
            margin: 40px 0 0;
            color: #111;
            font-size: 12px;
            text-align: center;
        }
        .simple-auth-register a { color: #71717a; text-decoration: none; }
        .simple-auth-register a:hover { color: #111; text-decoration: underline; }
        .simple-auth-alert {
            margin: 0 0 18px;
            padding: 10px 12px;
            border: 1px solid #fecaca;
            border-radius: 6px;
            background: #fff1f2;
            color: #b91c1c;
            font-size: 13px;
        }
        .simple-auth-alert ul { margin: 0; padding-left: 18px; }
        @media (max-width: 480px) {
            .simple-auth-page { padding-top: 24px; }
            .simple-auth-brand { margin-bottom: 24px; font-size: 21px; }
            .simple-auth-card { padding: 52px 24px 24px; }
        }
        html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) { font-size: 22px !important; font-weight: 600 !important; line-height: 1 !important; }
        html body .simple-auth-page .simple-auth-label:not(#comments *):not(#app *) { font-size: 12px !important; font-weight: 600 !important; line-height: 1.2 !important; }
        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *) { height: 36px !important; padding: 0 11px !important; border: 1px solid #dedede !important; border-radius: 6px !important; background: #fff !important; color: #111 !important; font-size: 13px !important; box-shadow: 0 1px 2px rgba(0,0,0,.06) !important; }
        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *):focus { border-color: #10a37f !important; box-shadow: 0 0 0 1px #10a37f !important; }
        html body .simple-auth-page .simple-auth-remember:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-remember span:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-link:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-register:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-register a:not(#comments *):not(#app *) { font-size: 12px !important; font-weight: 400 !important; line-height: 1.3 !important; }
        html body .simple-auth-page .simple-auth-remember input:not(#comments *):not(#app *) { appearance: auto !important; width: 16px !important; height: 16px !important; min-width: 16px !important; min-height: 16px !important; padding: 0 !important; border-radius: 3px !important; background: #fff !important; }
        html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *) { height: 36px !important; min-height: 36px !important; padding: 0 12px !important; border: 0 !important; border-radius: 6px !important; background: #7fcdb7 !important; color: #fff !important; font-size: 12px !important; font-weight: 700 !important; line-height: 36px !important; box-shadow: none !important; }
        html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):hover { background: #70bea8 !important; color: #fff !important; }
        @media (max-width: 480px) { html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) { font-size: 21px !important; } }
    </style>

    <main class="simple-auth-page">
        <h1 class="simple-auth-brand">
            <img src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="">
            <span>Ografi</span>
        </h1>

        <section class="simple-auth-card" aria-label="Giriş">
            <button type="button" class="simple-auth-close" aria-label="Kapat" data-simple-auth-close>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            @if (session('status'))
                <div class="simple-auth-alert" style="border-color:#a7f3d0;background:#ecfdf5;color:#047857;">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="simple-auth-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="simple-auth-field">
                    <label class="simple-auth-label" for="email">E-posta</label>
                    <input class="simple-auth-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                </div>

                <div class="simple-auth-field">
                    <label class="simple-auth-label" for="password">Şifre</label>
                    <input class="simple-auth-input" id="password" name="password" type="password" autocomplete="current-password">
                </div>

                <div class="simple-auth-options">
                    <label class="simple-auth-remember" for="remember">
                        <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Beni Hatırla</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="simple-auth-link" href="{{ route('password.request') }}">Şifrenizi mi unuttunuz?</a>
                    @endif
                </div>

                <button class="simple-auth-submit" type="submit">Giriş yapmak</button>
            </form>

            <p class="simple-auth-register">
                Hesabınız yok mu?
                <a href="{{ route('register') }}">Kayıt olun</a>
            </p>
        </section>
    </main>

    <script>
        document.querySelector('[data-simple-auth-close]')?.addEventListener('click', function () {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = @json(route('home'));
        });
    </script>
</x-guest-layout>
