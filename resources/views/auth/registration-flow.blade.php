<x-guest-layout>
    <style>
            .reg-page { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px 18px; background:#eef5ff; color:#111827; font-family:Arial,Helvetica,sans-serif; }
            .reg-shell { width:100%; max-width:420px; }
            .reg-brand { display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:22px; color:#111827; font-size:22px; font-weight:700; text-decoration:none; }
            .reg-brand img { width:40px; height:40px; object-fit:contain; }
            .reg-card { position:relative; padding:32px 26px; border:1px solid #dbeafe; border-radius:18px; background:#ffffff; box-shadow:0 20px 50px rgba(15,23,42,.08); }
            .reg-close { position:absolute; top:16px; right:16px; display:flex; width:36px; height:36px; align-items:center; justify-content:center; border:0; border-radius:999px; background:#f8fafc; color:#475569; font-size:20px; line-height:1; text-decoration:none; }
            .reg-title { margin:0 0 10px; font-size:20px; line-height:1.3; font-weight:700; text-align:center; color:#0f172a; }
            .reg-copy { margin:0 0 24px; color:#475569; font-size:13px; line-height:1.6; text-align:center; }
            .reg-label { display:block; margin:0 0 8px; color:#475569; font-size:13px; font-weight:500; }
            .reg-field { width:100%; height:44px; margin:0 0 18px; padding:0 14px; border:1px solid #cbd5e1; border-radius:12px; background:#f8fafc; color:#0f172a; font-size:14px; outline:none; transition:all .2s ease; }
            .reg-field:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
            .reg-code { height:50px; font-size:20px; letter-spacing:.24em; text-align:center; }
            .reg-button { width:100%; min-height:44px; border:0; border-radius:12px; background:#2563eb; color:#ffffff; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s ease; }
            .reg-button:hover { background:#1d4ed8; }
            .reg-error { margin:-10px 0 14px; color:#dc2626; font-size:12px; }
            .reg-footer { margin:24px 0 0; color:#475569; font-size:13px; text-align:center; }
            .reg-footer a { color:#2563eb; text-decoration:none; font-weight:600; }
            .reg-change { display:block; margin:16px auto 0; color:#2563eb; font-size:13px; text-align:center; text-decoration:none; font-weight:600; }
            @media(max-width:640px){ .reg-page{padding:18px 14px}.reg-card{padding:28px 22px}.reg-brand{margin-bottom:18px} }
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
