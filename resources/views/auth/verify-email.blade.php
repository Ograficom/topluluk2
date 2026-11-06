@extends('layouts.app')

@section('title', 'E-posta adresini doğrula')

@push('head')
    <style>
        .verify-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }
        .verify-actions form {
            flex: 1 1 0;
            min-width: 0;
            margin: 0;
        }
        .verify-actions .btn {
            width: 100%;
            min-height: 46px;
            white-space: normal;
        }
        .verify-status {
            margin: 12px 0 0;
        }
        .verify-errors {
            margin: 12px 0 0;
        }
        @media (max-width: 420px) {
            .verify-actions {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <section class="card card-body">
        <div class="modal-body signin">
            <h1 class="text-center h1">E-posta adresini doğrula</h1>
            <p class="text-center color-gray">OgraFi hesabını kullanmaya başlamadan önce e-posta adresine gönderdiğimiz doğrulama bağlantısına tıkla.</p>

            @if (session('status'))
                <div class="alert alert-success verify-status" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger verify-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="verify-actions">
                <form action="{{ route('verification.send') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary btn-lg" type="submit">Doğrulama e-postasını yeniden gönder</button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-secondary btn-lg" type="submit">Çıkış yap</button>
                </form>
            </div>
        </div>
    </section>
@endsection
