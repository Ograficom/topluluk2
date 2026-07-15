<x-guest-layout>
    <style>
        .email-code-page {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 72px 18px 32px;
            background: #f5f5f6;
            color: #18181b;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-code-shell { width: 100%; max-width: 400px; }
        .email-code-brand {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            margin-bottom: 24px; color: #18181b; font-size: 20px; font-weight: 400;
        }
        .email-code-brand img { width: 38px; height: 38px; object-fit: contain; transform: scale(1.3); }
        .email-code-card {
            padding: 28px 24px; border: 1px solid #dedee2; border-radius: 12px;
            background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.06);
        }
        .email-code-title { margin: 0 0 8px; font-size: 18px; line-height: 1.35; font-weight: 500; text-align: center; }
        .email-code-copy { margin: 0 0 22px; color: #71717a; font-size: 13px; line-height: 1.55; text-align: center; }
        .email-code-label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 400; }
        .email-code-input {
            width: 100%; height: 46px; padding: 0 12px; border: 1px solid #d4d4d8;
            border-radius: 7px; background: #fff; color: #18181b; font-size: 20px;
            font-weight: 400; letter-spacing: .3em; text-align: center; outline: none;
        }
        .email-code-input:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }
        .email-code-error { margin: 7px 0 0; color: #dc2626; font-size: 12px; }
        .email-code-status { margin: 0 0 16px; color: #166534; font-size: 12px; text-align: center; }
        .email-code-submit {
            width: 100%; height: 40px; margin-top: 16px; border: 0; border-radius: 7px;
            background: #2563eb; color: #fff; font-size: 13px; font-weight: 500; cursor: pointer;
        }
        .email-code-submit:hover { background: #1d4ed8; }
        .email-code-actions { display: flex; justify-content: center; gap: 16px; margin-top: 18px; }
        .email-code-link { border: 0; padding: 0; background: transparent; color: #2563eb; font-size: 12px; cursor: pointer; text-decoration: none; }
        .email-code-link:hover { color: #1d4ed8; }
        @media (max-width: 640px) {
            .email-code-page { padding-top: 22px; }
            .email-code-brand { margin-bottom: 18px; }
            .email-code-card { padding: 24px 20px; }
        }
    </style>

    <main class="email-code-page">
        <div class="email-code-shell">
            <a href="{{ route('home') }}" class="email-code-brand">
                <img src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="">
                <span>Ografi</span>
            </a>

            <section class="email-code-card">
                <h1 class="email-code-title">E-postanızı doğrulayın</h1>
                <p class="email-code-copy">
                    <strong style="font-weight: 500; color: #3f3f46;">{{ auth()->user()->email }}</strong>
                    adresine gönderdiğimiz 6 haneli kodu girin. Kod 10 dakika geçerlidir.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="email-code-status">Yeni doğrulama kodu e-posta adresinize gönderildi.</p>
                @endif

                <form method="POST" action="{{ route('verification.code.verify') }}">
                    @csrf
                    <label class="email-code-label" for="verification-code">Doğrulama kodu</label>
                    <input
                        class="email-code-input"
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
                    @error('code')<p class="email-code-error">{{ $message }}</p>@enderror
                    <button class="email-code-submit" type="submit">E-postayı doğrula</button>
                </form>

                <div class="email-code-actions">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="email-code-link" type="submit">Kodu yeniden gönder</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="email-code-link" type="submit" style="color:#71717a;">Çıkış yap</button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
