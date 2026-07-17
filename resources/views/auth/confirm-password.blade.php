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
            padding: 0 40px 0 35px;
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
            padding: 0 40px 0 35px !important;
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
            border-color: #0e7c86 !important;
            box-shadow: 0 0 0 1px #0e7c86 !important;
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

        <section class="simple-auth-card" aria-label="Şifre doğrulama">
            <p class="simple-auth-description">
                Devam edebilmeniz için hesabınızın şifresini yeniden girerek kimliğinizi doğrulamanız gerekiyor.
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
                            İşlem başarılı
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
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                @csrf

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
                            class="simple-auth-input"
                            id="password"
                            name="password"
                            type="password"
                            required
                            autofocus
                            autocomplete="current-password"
                            placeholder="••••••••"
                        >

                        <button
                            class="simple-auth-eye"
                            id="togglePassword"
                            type="button"
                            aria-label="Şifreyi göster"
                            aria-pressed="false"
                        >
                            <svg id="eyeOpenIcon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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

                            <svg id="eyeClosedIcon" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
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
                            d="M7 11V8a5 5 0 0 1 10 0v3"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                        <path
                            d="M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linejoin="round"
                        />
                    </svg>
                    Şifreyi Onayla
                </button>
            </form>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeOpenIcon = document.getElementById('eyeOpenIcon');
            const eyeClosedIcon = document.getElementById('eyeClosedIcon');

            if (!passwordInput || !togglePassword || !eyeOpenIcon || !eyeClosedIcon) {
                return;
            }

            togglePassword.addEventListener('click', function () {
                const showPassword = passwordInput.type === 'password';

                passwordInput.type = showPassword ? 'text' : 'password';
                eyeOpenIcon.hidden = showPassword;
                eyeClosedIcon.hidden = !showPassword;
                togglePassword.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                togglePassword.setAttribute(
                    'aria-label',
                    showPassword ? 'Şifreyi gizle' : 'Şifreyi göster'
                );
            });
        });
    </script>
</x-guest-layout>