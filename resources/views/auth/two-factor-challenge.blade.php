@extends('layouts.app')

@section('title', 'İki faktörlü doğrulama')

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="text-center h1">İki faktörlü doğrulama</h1>
            <p class="text-center color-gray">Authenticator uygulamanızdaki 6 haneli kodu veya kurtarma kodlarından birini girin.</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            <form action="{{ route('two-factor.challenge.verify') }}" method="POST">
                @csrf
                <div class="signin__body">
                    <div class="form-group">
                        <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="20" placeholder="Doğrulama kodu" aria-label="Doğrulama kodu" required autofocus>
                    </div>
                    <div class="signin__submit">
                        <button type="submit" class="btn btn-primary btn-lg btn-full">Doğrula ve giriş yap</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
