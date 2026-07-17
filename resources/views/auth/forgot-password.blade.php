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

        .simple-auth-description {
            margin: 0 0 24px;
            color: #71717a;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.55;
            text-align: left;
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
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        }

        .simple-auth-input:focus {
            border-color: #0e7c86;
            box-shadow: 0 0 0 1px #0e7c86;
        }

        .simple-auth-submit {
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

        html body .simple-auth-page .simple-auth-description:not(#comments *):not(#app *) {
            color: #71717a !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.55 !important;
        }

        html body .simple-auth-page .simple-auth-label:not(#comments *):not(#app *) {
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.2 !important;
        }

        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *) {
            height: 36px !important;
            padding: 0 11px !important;
            border: 1px solid #dedede !important;
            border-radius: 6px !important;
            background: #fff !important;
            color: #111 !important;
            font-size: 13px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06) !important;
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

        @media (max-width: 480px) {
            html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) {
                font-size: 21px !important;
            }
        }
    </style>

    <main class="simple-auth-page">
        <h1 class="simple-auth-brand">
            <img src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="Ografi">
            <span>Ografi</span>
        </h1>

        <section class="simple-auth-card" aria-label="Şifre sıfırlama">

            <p class="simple-auth-description">
                Şifrenizi mi unuttunuz? Sorun değil. E-posta adresinizi girin; size yeni bir şifre belirleyebilmeniz için şifre sıfırlama bağlantısı gönderelim.
            </p>

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
                            color: #16a34a !important;
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
                                padding: 0 !important;
                                color: #18181b !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 11px !important;
                                font-weight: 500 !important;
                                line-height: 14px !important;
                                letter-spacing: 0 !important;
                            "
                        >
                            Bağlantı gönderildi
                        </div>

                        <div
                            style="
                                all: initial !important;
                                display: block !important;
                                margin: 0 !important;
                                padding: 0 !important;
                                color: #71717a !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 10px !important;
                                font-weight: 400 !important;
                                line-height: 14px !important;
                                letter-spacing: 0 !important;
                            "
                        >
                            @if (session('status') === 'passwords.sent')
                                Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.
                            @else
                                {{ session('status') }}
                            @endif
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
                            color: #dc2626 !important;
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
                                padding: 0 !important;
                                color: #dc2626 !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 11px !important;
                                font-weight: 500 !important;
                                line-height: 14px !important;
                                letter-spacing: 0 !important;
                            "
                        >
                            İşlem başarısız
                        </div>

                        <div
                            style="
                                all: initial !important;
                                display: block !important;
                                margin: 0 !important;
                                padding: 0 !important;
                                color: #b91c1c !important;
                                font-family: Arial, Helvetica, sans-serif !important;
                                font-size: 10px !important;
                                font-weight: 400 !important;
                                line-height: 14px !important;
                                letter-spacing: 0 !important;
                            "
                        >
                            @foreach ($errors->all() as $error)
                                <div
                                    style="
                                        all: initial !important;
                                        display: block !important;
                                        margin: 0 !important;
                                        padding: 0 !important;
                                        color: #b91c1c !important;
                                        font-family: Arial, Helvetica, sans-serif !important;
                                        font-size: 10px !important;
                                        font-weight: 400 !important;
                                        line-height: 14px !important;
                                        letter-spacing: 0 !important;
                                    "
                                >
                                    @if ($error === 'passwords.user')
                                        Bu e-posta adresiyle kayıtlı bir kullanıcı bulunamadı.
                                    @elseif ($error === 'passwords.throttled')
                                        Çok fazla deneme yaptınız. Lütfen biraz sonra tekrar deneyin.
                                    @else
                                        {{ $error }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

                <div class="simple-auth-field">
                    <label class="simple-auth-label" for="email">E-posta</label>

                    <input
                        class="simple-auth-input"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                    >
                </div>

                <button class="simple-auth-submit" type="submit">
                    Şifre Sıfırlama Bağlantısı Gönder
                </button>
            </form>
        </section>
    </main>

</x-guest-layout>