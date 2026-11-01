@extends('layouts.app')

@section('title', 'Giriş yap')

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="text-center h1">Giriş yap</h1>
            <p class="text-center color-gray">Topluluğa katılmak ve hesabına erişmek için giriş yap.</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf
                <div class="signin__body">
                    <div class="form-group">
                        <input
                            type="text"
                            name="login_name"
                            value="{{ old('login_name') }}"
                            placeholder="Kullanıcı adı veya e-posta"
                            aria-label="Kullanıcı adı veya e-posta"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                    <div class="form-group view-pass">
                        <input
                            type="password"
                            name="password"
                            data-pass-form="login"
                            placeholder="Şifre"
                            aria-label="Şifre"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="view-pass-btn" data-pass-btn="login" aria-label="Şifreyi göster" title="Şifreyi göster" aria-pressed="false"></button>
                    </div>
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>Beni hatırla</span>
                    </label>
                    <div class="signin__submit">
                        <button type="submit" class="btn btn-primary btn-lg btn-full">Giriş yap</button>
                        <a href="{{ route('register') }}" class="btn btn-secondary btn-lg btn-full">Hesap oluştur</a>
                    </div>
                </div>
            </form>

            <div class="signin__footer">
                <p class="small color-gray text-center">Henüz hesabın yok mu? <a href="{{ route('register') }}" class="link">Ücretsiz kayıt ol</a>.</p>
            </div>
        </div>
    </section>
@endsection
