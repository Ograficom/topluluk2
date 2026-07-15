<x-guest-layout>
    <style>
        .reg-page { min-height:100vh; display:flex; align-items:flex-start; justify-content:center; padding:44px 18px 30px; background:#f5f5f6; color:#18181b; font-family:Arial,Helvetica,sans-serif; }
        .reg-shell { width:100%; max-width:400px; }
        .reg-brand { display:flex; align-items:center; justify-content:center; gap:6px; margin-bottom:25px; color:#18181b; font-size:20px; font-weight:400; text-decoration:none; }
        .reg-brand img { width:38px; height:38px; object-fit:contain; transform:scale(1.3); }
        .reg-card { position:relative; padding:28px 24px; border:1px solid #d9d9dd; border-radius:12px; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.05); }
        .reg-close { position:absolute; top:13px; right:13px; display:flex; width:36px; height:36px; align-items:center; justify-content:center; border:0; border-radius:999px; background:#f4f4f5; color:#52525b; font-size:24px; line-height:1; text-decoration:none; }
        .reg-title { margin:4px 42px 8px; font-size:17px; line-height:1.35; font-weight:500; text-align:center; }
        .reg-copy { margin:0 0 22px; color:#71717a; font-size:12.5px; line-height:1.55; text-align:center; }
        .reg-label { display:block; margin:0 0 7px; font-size:12.5px; font-weight:400; }
        .reg-field { width:100%; height:42px; margin:0 0 16px; padding:0 11px; border:1px solid #d4d4d8; border-radius:6px; background:#fff; color:#18181b; font-size:13px; font-weight:400; outline:none; }
        .reg-field:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.11); }
        .reg-code { height:48px; font-size:20px; letter-spacing:.3em; text-align:center; }
        .reg-button { width:100%; height:38px; border:0; border-radius:6px; background:#2563eb; color:#fff; font-size:12.5px; font-weight:500; cursor:pointer; }
        .reg-button:hover { background:#1d4ed8; }
        .reg-error { margin:-9px 0 13px; color:#dc2626; font-size:12px; }
        .reg-footer { margin:22px 0 0; color:#18181b; font-size:12px; text-align:center; }
        .reg-footer a { color:#71717a; text-decoration:none; }
        .reg-change { display:block; margin:15px auto 0; color:#2563eb; font-size:12px; text-align:center; text-decoration:none; }
        @media(max-width:640px){ .reg-page{padding-top:22px}.reg-brand{margin-bottom:18px}.reg-card{padding:26px 21px} }
    </style>

    <main class="reg-page">
        <div class="reg-shell">
            <a class="reg-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="">
                <span>Ografi</span>
            </a>

            <section class="reg-card">
                <a class="reg-close" href="{{ route('home') }}" aria-label="Kapat">×</a>

                @if($stage === 'email')
                    <h1 class="reg-title">Üyelik oluşturun</h1>
                    <p class="reg-copy">Önce e-posta adresinizi doğrulayın. Kod doğrulanmadan üyelik oluşturulmaz.</p>
                    <form method="POST" action="{{ route('register.email') }}">
                        @csrf
                        <label class="reg-label" for="email">E-posta</label>
                        <input class="reg-field" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required>
                        @error('email')<p class="reg-error">{{ $message }}</p>@enderror
                        <button class="reg-button" type="submit">Doğrulama kodu gönder</button>
                    </form>
                    <p class="reg-footer">Hesabınız var mı? <a href="{{ route('login') }}">Giriş yapın</a></p>
                @elseif($stage === 'verify')
                    <h1 class="reg-title">Doğrulama kodunu girin</h1>
                    <p class="reg-copy"><span style="color:#3f3f46">{{ $pending->email }}</span> adresine gönderilen 6 haneli kodu girin.</p>
                    <form method="POST" action="{{ route('register.verify.submit') }}">
                        @csrf
                        <label class="reg-label" for="code">Doğrulama kodu</label>
                        <input class="reg-field reg-code" id="code" name="code" type="text" value="{{ old('code') }}" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" autofocus required>
                        @error('code')<p class="reg-error">{{ $message }}</p>@enderror
                        <button class="reg-button" type="submit">Kodu doğrula</button>
                    </form>
                    <a class="reg-change" href="{{ route('register') }}">E-posta adresini değiştir</a>
                @else
                    <h1 class="reg-title">Üyeliğinizi tamamlayın</h1>
                    <p class="reg-copy">E-postanız doğrulandı. Şimdi adınızı ve giriş şifrenizi oluşturun.</p>
                    <form method="POST" action="{{ route('register.complete.submit') }}">
                        @csrf
                        <label class="reg-label" for="name">Ad Soyad</label>
                        <input class="reg-field" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" autofocus required>
                        @error('name')<p class="reg-error">{{ $message }}</p>@enderror
                        <label class="reg-label" for="password">Şifre</label>
                        <input class="reg-field" id="password" name="password" type="password" autocomplete="new-password" required>
                        @error('password')<p class="reg-error">{{ $message }}</p>@enderror
                        <label class="reg-label" for="password_confirmation">Şifre tekrar</label>
                        <input class="reg-field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        <button class="reg-button" type="submit">Üyeliği oluştur</button>
                    </form>
                @endif
            </section>
        </div>
    </main>
</x-guest-layout>
