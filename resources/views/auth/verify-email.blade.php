@extends('layouts.app')

@section('title', 'E-posta adresini doğrula')

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="text-center h1">E-posta adresini doğrula</h1>
            <p class="text-center color-gray">OgraFi hesabını kullanmaya başlamadan önce e-posta adresine gönderdiğimiz doğrulama bağlantısına tıkla.</p>

            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button class="btn btn-primary btn-lg btn-full" type="submit">Doğrulama e-postasını yeniden gönder</button>
            </form>

            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button class="btn btn-secondary btn-lg btn-full" type="submit">Çıkış yap</button>
            </form>
        </div>
    </section>
@endsection
