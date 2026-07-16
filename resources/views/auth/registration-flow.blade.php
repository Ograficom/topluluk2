<x-guest-layout>
    <style>
        .simple-auth-page,
        .simple-auth-page * {
            box-sizing: border-box;
        }

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
            margin: 0 0 18px;
            color: #18181b;
            font-size: 22px;
            font-weight: 400;
            line-height: 1;
            letter-spacing: -0.04em;
            text-decoration: none;
        }

        .simple-auth-brand img {
            display: block;
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .simple-auth-card {
            position: relative;
            width: 100%;
            max-width: 380px;
            margin: 0;
            padding: 48px 20px 22px;
            border: 1px solid #d9d9d9;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .12);
        }

        .simple-auth-title {
            margin: 0 0 8px;
            color: #18181b;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.3;
            text-align: left;
        }

        .simple-auth-description {
            margin: 0 0 24px;
            color: #71717a;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.55;
            text-align: left;
        }

        .simple-auth-description-email {
            color: #3f3f46;
            overflow-wrap: anywhere;
        }

        .simple-auth-field {
            margin-bottom: 20px;
        }

        .simple-auth-label {
            display: block;
            margin-bottom: 6px;
            color: #111;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.2;
        }

        .simple-auth-input-wrap {
            position: relative;
            width: 100%;
        }

        .simple-auth-input-icon {
            position: absolute;
            top: 50%;
            left: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            color: #71717a;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .simple-auth-input-icon svg {
            display: block;
            width: 14px;
            height: 14px;
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
            font-weight: 400;
            outline: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        }

        .simple-auth-input.has-left-icon {
            padding-left: 35px;
        }

        .simple-auth-input.has-both-icons {
            padding-right: 40px;
            padding-left: 35px;
        }

        .simple-auth-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
        }

        .simple-auth-code {
            padding: 0 12px;
            font-size: 16px;
            letter-spacing: .22em;
            text-align: center;
        }

        .simple-auth-eye {
            position: absolute;
            top: 50%;
            right: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: #71717a;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .simple-auth-eye:hover {
            background: #f4f4f5;
            color: #18181b;
        }

        .simple-auth-eye svg {
            display: block;
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
        }

        .simple-auth-eye svg[hidden] {
            display: none !important;
        }

        .simple-auth-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            height: 36px;
            min-height: 36px;
            margin-top: 4px;
            padding: 0 12px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            font-size: 12px;
            font-weight: 400;
            line-height: 36px;
            cursor: pointer;
            box-shadow: none;
            transition: background-color .2s ease;
        }

        .simple-auth-submit:hover {
            background: #1d4ed8;
            color: #fff;
        }

        .simple-auth-submit svg {
            display: block;
            width: 13px;
            height: 13px;
            flex: 0 0 13px;
        }

        .simple-auth-footer {
            margin: 32px 0 0;
            color: #111;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.3;
            text-align: center;
        }

        .simple-auth-footer a,
        .simple-auth-change {
            color: #71717a;
            font-size: 12px;
            font-weight: 400;
            text-decoration: none;
        }

        .simple-auth-footer a:hover,
        .simple-auth-change:hover {
            color: #111;
            text-decoration: underline;
        }

        .simple-auth-change {
            display: block;
            width: fit-content;
            margin: 28px auto 0;
            text-align: center;
        }

        @media (max-width: 480px) {
            .simple-auth-page {
                justify-content: flex-start;
                padding-top: 24px;
            }

            .simple-auth-brand {
                margin-bottom: 24px;
                font-size: 21px;
            }

            .simple-auth-card {
                padding: 52px 24px 24px;
            }
        }

        html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) {
            font-size: 22px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
        }

        html body .simple-auth-page .simple-auth-title:not(#comments *):not(#app *) {
            margin: 0 0 8px !important;
            color: #18181b !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            line-height: 1.3 !important;
            text-align: left !important;
        }

        html body .simple-auth-page .simple-auth-description:not(#comments *):not(#app *) {
            margin: 0 0 24px !important;
            color: #71717a !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.55 !important;
            text-align: left !important;
        }

        html body .simple-auth-page .simple-auth-label:not(#comments *):not(#app *) {
            margin-bottom: 6px !important;
            color: #111 !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.2 !important;
        }

        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *) {
            height: 36px !important;
            border: 1px solid #dedede !important;
            border-radius: 6px !important;
            background: #fff !important;
            color: #111 !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            line-height: normal !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06) !important;
        }

        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *):focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 1px #2563eb !important;
        }

        html body .simple-auth-page .simple-auth-code:not(#comments *):not(#app *) {
            padding: 0 12px !important;
            font-size: 16px !important;
            letter-spacing: .22em !important;
            text-align: center !important;
        }

        html body .simple-auth-page .simple-auth-eye:not(#comments *):not(#app *) {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            min-height: 28px !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 5px !important;
            background: transparent !important;
            color: #71717a !important;
            box-shadow: none !important;
        }

        html body .simple-auth-page .simple-auth-eye:not(#comments *):not(#app *):hover {
            background: #f4f4f5 !important;
            color: #18181b !important;
        }

        html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *) {
            height: 36px !important;
            min-height: 36px !important;
            padding: 0 12px !important;
            border: 0 !important;
            border-radius: 6px !important;
            background: #2563eb !important;
            color: #fff !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 36px !important;
            box-shadow: none !important;
        }

        html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):hover {
            background: #1d4ed8 !important;
            color: #fff !important;
        }

        html body .simple-auth-page .simple-auth-footer:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-footer a:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-change:not(#comments *):not(#app *) {
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.3 !important;
        }

        @media (max-width: 480px) {
            html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) {
                font-size: 21px !important;
            }
        }
    </style>

    <main class="simple-auth-page">
        <a class="simple-auth-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="Ografi">
            <span>Ografi</span>
        </a>

        <section class="simple-auth-card" aria-label="Üyelik oluşturma">
            @if (session('status'))
                <div
                    role="alert"
                    style="
                        all: initial !important;
                        display: grid !important;
                        grid-template-columns: 13px minmax(0, 1fr) !important;
                        align-items: start !important;
                        column-gap: 8px !important;
                        width: 100% !important;
                        margin: 0 0 14px !important;
                        padding: 10px 11px !important;
                        box-sizing: border-box !important;
                        border: 1px solid #d4d4d8 !important;
                        border-radius: 7px !important;
                        background: #ffffff !important;
                        color: #18181b !important;
                        font-family: Arial, Helvetica, sans-serif !important;
                    "
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#16a34a"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                        style="
                            all: initial !important;
                            display: block !important;
                            width: 13px !important;
                            height: 13px !important;
                            margin-top: 1px !important;
                        "
                    >
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <path d="m9 11 3 3L22 4"/>
                    </svg>

                    <div style="all:initial!important;display:block!important;min-width:0!important;font-family:Arial,Helvetica,sans-serif!important;">
                        <div
                            style="
                                all: initial !important;
                                display: block !important;
                                margin: 0 0 2px !important;
                                color: #18181b !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 11px !important;
                                font-weight: 500 !important;
                                line-height: 14px !important;
                            "
                        >
                            İşlem başarılı
                        </div>

                        <div
                            style="
                                all: initial !important;
                                display: block !important;
                                margin: 0 !important;
                                color: #71717a !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 10px !important;
                                font-weight: 400 !important;
                                line-height: 14px !important;
                            "
                        >
                            {{ session('status') }}
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div
                    role="alert"
                    style="
                        all: initial !important;
                        display: grid !important;
                        grid-template-columns: 13px minmax(0, 1fr) !important;
                        align-items: start !important;
                        column-gap: 8px !important;
                        width: 100% !important;
                        margin: 0 0 14px !important;
                        padding: 10px 11px !important;
                        box-sizing: border-box !important;
                        border: 1px solid #fecaca !important;
                        border-radius: 7px !important;
                        background: #ffffff !important;
                        color: #dc2626 !important;
                        font-family: Arial, Helvetica, sans-serif !important;
                    "
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#dc2626"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                        style="
                            all: initial !important;
                            display: block !important;
                            width: 13px !important;
                            height: 13px !important;
                            margin-top: 1px !important;
                        "
                    >
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" x2="12" y1="8" y2="12"/>
                        <line x1="12" x2="12.01" y1="16" y2="16"/>
                    </svg>

                    <div style="all:initial!important;display:block!important;min-width:0!important;font-family:Arial,Helvetica,sans-serif!important;">
                        <div
                            style="
                                all: initial !important;
                                display: block !important;
                                margin: 0 0 2px !important;
                                color: #dc2626 !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 11px !important;
                                font-weight: 500 !important;
                                line-height: 14px !important;
                            "
                        >
                            İşlem başarısız
                        </div>

                        <div
                            style="
                                all: initial !important;
                                display: block !important;
                                margin: 0 !important;
                                color: #b91c1c !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 10px !important;
                                font-weight: 400 !important;
                                line-height: 14px !important;
                            "
                        >
                            @foreach ($errors->all() as $error)
                                <div
                                    style="
                                        all: initial !important;
                                        display: block !important;
                                        margin: 0 !important;
                                        color: #b91c1c !important;
                                        font-family: Arial, Helvetica, sans-serif !important;
                                        font-size: 10px !important;
                                        font-weight: 400 !important;
                                        line-height: 14px !important;
                                    "
                                >
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($stage === 'email')
                <h1 class="simple-auth-title">Üyelik oluşturun</h1>

                <p class="simple-auth-description">
                    Önce e-posta adresinizi doğrulayın. Kod doğrulanmadan üyelik oluşturulmaz.
                </p>

                <form method="POST" action="{{ route('register.email') }}" novalidate>
                    @csrf

                    <div class="simple-auth-field">
                        <label class="simple-auth-label" for="email">E-posta</label>

                        <div class="simple-auth-input-wrap">
                            <span class="simple-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                    <path
                                        d="m3 7 9 6 9-6"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>

                            <input
                                class="simple-auth-input has-left-icon"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                autofocus
                                required
                            >
                        </div>
                    </div>

                    <button class="simple-auth-submit" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M22 2 11 13"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="m22 2-7 20-4-9-9-4 20-7Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Doğrulama Kodu Gönder
                    </button>
                </form>

                <p class="simple-auth-footer">
                    Hesabınız var mı?
                    <a href="{{ route('login') }}">Giriş yapın</a>
                </p>
            @elseif ($stage === 'verify')
                <h1 class="simple-auth-title">Doğrulama kodunu girin</h1>

                <p class="simple-auth-description">
                    <span class="simple-auth-description-email">{{ $pending->email }}</span>
                    adresine gönderilen 6 haneli kodu girin.
                </p>

                <form method="POST" action="{{ route('register.verify.submit') }}" novalidate>
                    @csrf

                    <div class="simple-auth-field">
                        <label class="simple-auth-label" for="code">Doğrulama kodu</label>

                        <input
                            class="simple-auth-input simple-auth-code"
                            id="code"
                            name="code"
                            type="text"
                            value="{{ old('code') }}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            autofocus
                            required
                        >
                    </div>

                    <button class="simple-auth-submit" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="m5 12 4 4L19 6"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Kodu Doğrula
                    </button>
                </form>

                <a class="simple-auth-change" href="{{ route('register') }}">
                    E-posta adresini değiştir
                </a>
            @else
                <h1 class="simple-auth-title">Üyeliğinizi tamamlayın</h1>

                <p class="simple-auth-description">
                    E-postanız doğrulandı. Şimdi adınızı ve giriş şifrenizi oluşturun.
                </p>

                <form method="POST" action="{{ route('register.complete.submit') }}" novalidate>
                    @csrf

                    <div class="simple-auth-field">
                        <label class="simple-auth-label" for="name">Ad Soyad</label>

                        <div class="simple-auth-input-wrap">
                            <span class="simple-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle
                                        cx="12"
                                        cy="8"
                                        r="4"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                    <path
                                        d="M4 21a8 8 0 0 1 16 0"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </span>

                            <input
                                class="simple-auth-input has-left-icon"
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                autofocus
                                required
                            >
                        </div>
                    </div>

                    <div class="simple-auth-field">
                        <label class="simple-auth-label" for="password">Şifre</label>

                        <div class="simple-auth-input-wrap">
                            <span class="simple-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M7 11V8a5 5 0 0 1 10 0v3"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>

                            <input
                                class="simple-auth-input has-both-icons"
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                class="simple-auth-eye"
                                type="button"
                                data-password-toggle="password"
                                aria-label="Şifreyi göster"
                                aria-pressed="false"
                            >
                                <svg data-eye-open viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                </svg>

                                <svg data-eye-closed viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden style="display:none;">
                                    <path
                                        d="M3 3l18 18"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M10.7 10.7a2 2 0 0 0 2.6 2.6"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M9.88 5.18A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.18 3.1M6.61 6.61C3.75 8.54 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 4.38-.9"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="simple-auth-field">
                        <label class="simple-auth-label" for="password_confirmation">
                            Şifre tekrar
                        </label>

                        <div class="simple-auth-input-wrap">
                            <span class="simple-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M7 11V8a5 5 0 0 1 10 0v3"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>

                            <input
                                class="simple-auth-input has-both-icons"
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                class="simple-auth-eye"
                                type="button"
                                data-password-toggle="password_confirmation"
                                aria-label="Şifreyi göster"
                                aria-pressed="false"
                            >
                                <svg data-eye-open viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                </svg>

                                <svg data-eye-closed viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden style="display:none;">
                                    <path
                                        d="M3 3l18 18"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M10.7 10.7a2 2 0 0 0 2.6 2.6"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M9.88 5.18A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.18 3.1M6.61 6.61C3.75 8.54 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 4.38-.9"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button class="simple-auth-submit" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M12 5v14M5 12h14"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                        Üyeliği Oluştur
                    </button>
                </form>
            @endif
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                const inputId = button.getAttribute('data-password-toggle');
                const input = document.getElementById(inputId);
                const eyeOpen = button.querySelector('[data-eye-open]');
                const eyeClosed = button.querySelector('[data-eye-closed]');

                if (!input || !eyeOpen || !eyeClosed) {
                    return;
                }

                button.addEventListener('click', function () {
                    const showPassword = input.type === 'password';

                    input.type = showPassword ? 'text' : 'password';

                    eyeOpen.hidden = showPassword;
                    eyeClosed.hidden = !showPassword;

                    eyeOpen.style.display = showPassword ? 'none' : 'block';
                    eyeClosed.style.display = showPassword ? 'block' : 'none';

                    button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                    button.setAttribute(
                        'aria-label',
                        showPassword ? 'Şifreyi gizle' : 'Şifreyi göster'
                    );
                });
            });
        });
    </script>
</x-guest-layout>