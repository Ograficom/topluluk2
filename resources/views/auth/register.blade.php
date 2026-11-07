@push('head')
    <style>
        #result-registration.username-available {
            color: #16803c;
        }
        #result-registration.username-taken {
            color: #dc2626;
        }
    </style>
@endpush

@extends('layouts.app')

@section('title', 'Hesap oluştur')

@section('content')
    <section class="feed">
        <form class="card card-body" action="{{ route('register.store') }}" method="POST">
            @csrf
            <header>
                <h1>Hesap oluştur</h1>
                <p>OgraFi topluluğuna katıl ve tüm özelliklere eriş. Kayıt sırasında sorun yaşarsan <a href="{{ route('contacts') }}" class="link">destek ekibine</a> ulaş.</p>
            </header>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <ul class="signup__list">
                <li>
                    <input type="email" name="email" id="signup-email" value="{{ old('email') }}" placeholder="E-posta" aria-label="E-posta" autocomplete="email" required>
                </li>
                <li class="signup__login">
                    <input type="text" name="username" id="signup-name" value="{{ old('username') }}" placeholder="Kullanıcı adı" aria-label="Kullanıcı adı" autocomplete="nickname" maxlength="30" spellcheck="false" autocapitalize="none" required>
                    <small class="small color-gray">E-posta adresinden bağımsız, kendine ait kullanıcı adını seç.</small>
                    <button type="button" class="btn btn-secondary signup__login-check" id="username-check" title="Kullanıcı adını kontrol et" aria-label="Kullanıcı adını kontrol et">Kontrol et</button>
                </li>
                <li id="result-registration" aria-live="polite"></li>
                <li class="view-pass">
                    <input type="password" name="password" id="signup-password" placeholder="Şifre (en az 12 karakter)" aria-label="Şifre" autocomplete="new-password" minlength="12" data-pass-form="signup-pass1" required>
                    <button type="button" class="view-pass-btn" data-pass-btn="signup-pass1" aria-label="Şifreyi göster" title="Şifreyi göster" aria-pressed="false"></button>
                </li>
                <li class="view-pass">
                    <input type="password" name="password_confirmation" id="signup-password-confirm" placeholder="Şifreyi tekrar gir" aria-label="Şifreyi tekrar gir" autocomplete="new-password" minlength="12" data-pass-form="signup-pass2" required>
                    <button type="button" class="view-pass-btn" data-pass-btn="signup-pass2" aria-label="Şifreyi göster" title="Şifreyi göster" aria-pressed="false"></button>
                </li>
                <li>
                    <div class="checkbox">
                        <label class="form-check-label" for="signup-rules">
                            <input class="form-check-input" type="checkbox" id="signup-rules" name="agree_rules" value="1" @checked(old('agree_rules')) required>
                            <span>Site kurallarını kabul ediyorum</span>
                        </label>
                    </div>
                </li>
                <li>
                    <button class="btn btn-primary" type="submit">Hesap oluştur</button>
                </li>
            </ul>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const input = document.getElementById('signup-name');
            const button = document.getElementById('username-check');
            const result = document.getElementById('result-registration');

            if (!input || !button || !result) return;

            const showResult = (message, available) => {
                result.textContent = message;
                result.classList.remove('username-available', 'username-taken');

                if (message === 'Kullanıcı adı kullanılabilir.') {
                    result.classList.add('username-available');
                } else if (message === 'Bu kullanıcı adı zaten alınmış.') {
                    result.classList.add('username-taken');
                }
            };

            input.addEventListener('input', () => {
                result.textContent = '';
                result.classList.remove('text-success', 'text-danger');
            });

            button.addEventListener('click', async () => {
                const username = input.value.trim();

                if (!username) {
                    showResult('Önce kullanıcı adı gir.', false);
                    input.focus();
                    return;
                }

                button.disabled = true;
                button.textContent = 'Kontrol ediliyor…';
                result.textContent = '';

                try {
                    const url = new URL(@json(route('username.check')), window.location.origin);
                    url.searchParams.set('username', username);

                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const message = data.errors?.username?.[0] || 'Kullanıcı adı kontrol edilemedi.';
                        showResult(message, false);
                        return;
                    }

                    showResult(data.message, data.available);
                } catch (error) {
                    showResult('Bağlantı hatası. Tekrar dene.', false);
                } finally {
                    button.disabled = false;
                    button.textContent = 'Kontrol et';
                }
            });
        })();
    </script>
@endpush
