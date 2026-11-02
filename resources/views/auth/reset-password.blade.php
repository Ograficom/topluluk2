@extends('layouts.app')

@section('title', 'Yeni şifre belirle')

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="h2 text-center">Yeni şifre belirle</h1>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group"><input type="email" name="email" value="{{ old('email', $email) }}" placeholder="E-posta adresin" autocomplete="email" required></div>
                <div class="form-group"><input type="password" name="password" placeholder="Yeni şifre (en az 12 karakter)" autocomplete="new-password" minlength="12" required></div>
                <div class="form-group"><input type="password" name="password_confirmation" placeholder="Yeni şifreyi tekrar gir" autocomplete="new-password" minlength="12" required></div>
                <button class="btn btn-primary btn-lg btn-full" type="submit">Şifreyi güncelle</button>
            </form>
        </div>
    </section>
@endsection
