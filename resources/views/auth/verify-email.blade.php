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

        .simple-auth-email {
            color: #3f3f46;
            font-weight: 500;
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
            padding: 0 11px 0 35px;
            border: 1px solid #dedede;
            border-radius: 6px;
            background: #fff;
            color: #111;
            font-size: 13px;
            font-weight: 400;
            outline: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        }

        .simple-auth-input:focus {
            border-color: #0e7c86;
            box-shadow: 0 0 0 1px #0e7c86;
        }

        .simple-auth-code {
            padding-right: 11px;
            font-size: 16px;
            letter-spacing: .22em;
            text-align: center;
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
            background: #0e7c86;
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

        .simple-auth-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-top: 28px;
        }

        .simple-auth-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            background: transparent;
            color: #71717a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.3;
            text-decoration: none;
            cursor: pointer;
        }

        .simple-auth-link:hover {
            color: #111;
            text-decoration: underline;
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

            .simple-auth-actions {
                gap: 12px;
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

        html body .simple-auth-page .simple-auth-email:not(#comments *):not(#app *) {
            color: #3f3f46 !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            line-height: 1.55 !important;
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
            padding: 0 11px 0 35px !important;
            border: 1px solid #dedede !important;
            border-radius: 6px !important;
            background: #fff !important;
            color: #111 !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            line-height: normal !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06) !important;
        }

        html body .simple-auth-page .simple-auth-code:not(#comments *):not(#app *) {
            padding-right: 11px !important;
            font-size: 16px !important;
            letter-spacing: .22em !important;
            text-align: center !important;
        }

        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *):focus {
            border-color: #0e7c86 !important;
            box-shadow: 0 0 0 1px #0e7c86 !important;
        }

        html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *) {
            height: 36px !important;
            min-height: 36px !important;
            padding: 0 12px !important;
            border: 0 !important;
            border-radius: 6px !important;
            background: #0e7c86 !important;
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

        html body .simple-auth-page .simple-auth-link:not(#comments *):not(#app *) {
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            color: #71717a !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.3 !important;
            box-shadow: none !important;
        }

        html body .simple-auth-page .simple-auth-link:not(#comments *):not(#app *):hover {
            color: #111 !important;
            text-decoration: underline !important;
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

        <section class="simple-auth-card" aria-label="E-posta doğrulama">
            <h1 class="simple-auth-title">E-postanızı doğrulayın</h1>

            <p class="simple-auth-description">
                <span class="simple-auth-email">{{ auth()->user()->email }}</span>
                adresine gönderdiğimiz 6 haneli kodu girin. Kod 10 dakika boyunca geçerlidir.
            </p>

            @if (session('status') === 'verification-link-sent')
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

                    <div
                        style="
                            all: initial !important;
                            display: block !important;
                            min-width: 0 !important;
                            font-family: Arial, Helvetica, sans-serif !important;
                        "
                    >
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
                            Kod gönderildi
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
                            Yeni doğrulama kodu e-posta adresinize gönderildi.
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

                    <div
                        style="
                            all: initial !important;
                            display: block !important;
                            min-width: 0 !important;
                            font-family: Arial, Helvetica, sans-serif !important;
                        "
                    >
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
                            Doğrulama başarısız
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

            <form method="POST" action="{{ route('verification.code.verify') }}" novalidate>
                @csrf

                <div class="simple-auth-field">
                    <label class="simple-auth-label" for="verification-code">
                        Doğrulama kodu
                    </label>

                    <div class="simple-auth-input-wrap">
                        <span class="simple-auth-input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect
                                    x="4"
                                    y="4"
                                    width="16"
                                    height="16"
                                    rx="3"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                />
                                <path
                                    d="M8 9h8M8 12h8M8 15h5"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>

                        <input
                            class="simple-auth-input simple-auth-code"
                            id="verification-code"
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
                    E-postayı Doğrula
                </button>
            </form>

            <div class="simple-auth-actions">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <button class="simple-auth-link" type="submit">
                        Kodu yeniden gönder
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="simple-auth-link" type="submit">
                        Çıkış yap
                    </button>
                </form>
            </div>
        </section>
    </main>
</x-guest-layout>