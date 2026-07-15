<x-guest-layout>
    <style>
        .simple-auth-page,
        .simple-auth-page * { box-sizing: border-box; }
        .simple-auth-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px;
            background: #f4f4f5;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
        }
        .simple-auth-brand {
            margin: 0 0 30px;
            color: #18181b;
            font-size: 42px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -1.5px;
        }
        .simple-auth-card {
            width: 100%;
            max-width: 400px;
            padding: 24px;
            border: 1px solid #d9d9d9;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,.12);
        }
        .simple-auth-description {
            max-width: 330px;
            margin: 0 auto 28px;
            color: #71717a;
            font-size: 14px;
            line-height: 1.45;
            text-align: center;
        }
        .simple-auth-label {
            display: block;
            margin-bottom: 9px;
            color: #111;
            font-size: 14px;
            font-weight: 600;
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
            font-size: 14px;
            outline: none;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
        }
        .simple-auth-input:focus {
            border-color: #10a37f;
            box-shadow: 0 0 0 1px #10a37f;
        }
        .simple-auth-submit {
            width: 100%;
            height: 36px;
            margin-top: 24px;
            border: 0;
            border-radius: 6px;
            background: #7fcdb7;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .simple-auth-submit:hover { background: #70bea8; }
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
            .simple-auth-page { padding-top: 28px; }
            .simple-auth-brand { margin-bottom: 26px; font-size: 38px; }
        }
    </style>

    <main class="simple-auth-page">
        <h1 class="simple-auth-brand">alma</h1>

        <section class="simple-auth-card" aria-label="Şifre sıfırlama">
            <p class="simple-auth-description">
                Şifrenizi mi unuttunuz? Sorun değil. Bize e-posta adresinizi bildirin, size yeni bir şifre seçmenizi sağlayacak bir şifre sıfırlama bağlantısı göndereceğiz.
            </p>

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

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <label class="simple-auth-label" for="email">E-posta</label>
                <input class="simple-auth-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                <button class="simple-auth-submit" type="submit">E-posta ile Şifre Sıfırlama Bağlantısı</button>
            </form>
        </section>
    </main>
</x-guest-layout>
