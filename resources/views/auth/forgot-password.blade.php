@extends('layouts.app')

@section('title', 'Şifre sıfırlama')

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="h2 text-center">Şifremi unuttum</h1>
            <p class="text-center color-gray">Hesabına bağlı e-posta adresine sıfırlama bağlantısı gönderelim.</p>

            <div class="signin__body">
                @include('auth._social-buttons')
            </div>
            @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group"><input type="email" name="email" value="{{ old('email') }}" placeholder="E-posta adresin" autocomplete="email" required autofocus></div>
                <button class="btn btn-primary btn-lg btn-full" type="submit">Sıfırlama bağlantısı gönder</button>
            </form>
            <p class="small color-gray text-center mt-3"><a class="link" href="{{ route('login') }}">Giriş sayfasına dön</a></p>
        </div>
    </section>
@endsection
