@extends('layouts.app')

@section('title', 'Hesap oluştur')

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="text-center h1">Hesap oluştur</h1>
            <p class="text-center color-gray">OgraFi topluluğuna katıl. Kayıt sonrası hesabını etkinleştirmek için e-posta adresine gönderilen doğrulama bağlantısına tıklaman gerekecek.</p>

            <div class="signin__body">
                @include('auth._social-buttons')
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST">
                @csrf
                <ul class="signup__list">
                    <li>
                        <input
                            type="email"
                            name="email"
                            id="signup-email"
                            value="{{ old('email') }}"
                            placeholder="E-posta adresi"
                            aria-label="E-posta adresi"
                            autocomplete="email"
                            required
                        >
                    </li>
                    <li class="signup__login">
                        <input
                            type="text"
                            name="username"
                            id="signup-username"
                            value="{{ old('username') }}"
                            placeholder="Kullanıcı adı"
                            aria-label="Kullanıcı adı"
                            autocomplete="username"
                            maxlength="30"
                            spellcheck="false"
                            required
                        >
                    </li>
                    <li class="view-pass">
                        <input
                            type="password"
                            name="password"
                            id="signup-password"
                            placeholder="Şifre (en az 12 karakter)"
                            aria-label="Şifre"
                            autocomplete="new-password"
                            minlength="12"
                            data-pass-form="signup-pass1"
                            required
                        >
                        <button type="button" class="view-pass-btn" data-pass-btn="signup-pass1" aria-label="Şifreyi göster" title="Şifreyi göster" aria-pressed="false"></button>
                    </li>
                    <li class="view-pass">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="signup-password-confirm"
                            placeholder="Şifreyi tekrar gir"
                            aria-label="Şifreyi tekrar gir"
                            autocomplete="new-password"
                            minlength="12"
                            data-pass-form="signup-pass2"
                            required
                        >
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
                        <button class="btn btn-primary btn-lg btn-full" type="submit">Hesap oluştur</button>
                    </li>
                </ul>
            </form>

            <div class="signin__footer">
                <p class="small color-gray text-center">Zaten hesabın var mı? <a href="{{ route('login') }}" class="link">Giriş yap</a>.</p>
            </div>
        </div>
    </section>
@endsection
