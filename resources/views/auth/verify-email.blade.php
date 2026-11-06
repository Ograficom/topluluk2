@extends('layouts.app')

@section('title', 'E-posta adresini doğrula')

@push('head')
    <style>
        .email-verify-page {
            display: grid;
            min-height: min(620px, calc(100vh - var(--header-height, 72px) - 48px));
            place-items: center;
            padding: clamp(24px, 6vw, 56px);
        }
        .email-verify-card {
            width: min(100%, 480px);
            margin-inline: auto;
            text-align: center;
        }
        .email-verify-icon {
            display: grid;
            width: 72px;
            height: 72px;
            margin: 0 auto 24px;
            place-items: center;
            border-radius: 22px;
            color: var(--primary, #1769ff);
            background: var(--primary-bg, rgb(23 105 255 / 9%));
        }
        .email-verify-card .h1 {
            margin-bottom: 12px;
        }
        .email-verify-description {
            max-width: 390px;
            margin: 0 auto;
            line-height: 1.7;
        }
        .email-verify-notice {
            margin: 24px 0 16px;
            text-align: left;
        }
        .email-verify-actions {
            display: grid;
            gap: 10px;
        }
        .email-verify-actions .btn {
            min-height: 48px;
        }
        @media (max-width: 575px) {
            .email-verify-page {
                min-height: auto;
                padding: 28px 18px;
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
