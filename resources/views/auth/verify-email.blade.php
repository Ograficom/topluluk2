@extends('layouts.app')

@section('title', 'E-posta adresini doğrula')

@push('head')
    <style>
        .verify-status-inline {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.08);
            font-size: 14px;
        }
        .verify-status-inline svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: #fff;
        }
        .verify-status-inline span {
            font-weight: 500;
            line-height: 1;
        }
    </style>
    <style>
        .verify-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 120px;
            gap: 8px;
            margin-top: 16px;
        }
        .verify-actions form {
            min-width: 0;
            margin: 0;
        }
        .verify-actions .btn {
            width: 100%;
            min-height: 46px;
            padding-inline: 10px;
            white-space: nowrap;
            font-size: 14px;
        }
        .verify-status {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 16px 0 0;
            padding: 12px 14px;
            border: 1px solid #b7e4c7;
            border-radius: 10px;
            color: #176b3a;
            background: #effaf3;
            font-size: 14px;
            line-height: 1.5;
        }
        .verify-status::before {
            content: "✓";
            display: inline-grid;
            flex: 0 0 20px;
            width: 20px;
            height: 20px;
            place-items: center;
            border-radius: 50%;
            color: #fff;
            background: #249653;
            font-size: 12px;
            font-weight: 700;
        }
        .verify-errors {
            margin: 12px 0 0;
        }
        @media (max-width: 420px) {
            .verify-actions {
                grid-template-columns: 1fr;
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
                <div class="verify-status-inline" role="status">
                    <svg class="w-4 h-4 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="font-medium leading-none">
                        {{ session('status') }}
                    </span>
                </div>
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
                    <button class="btn btn-primary btn-lg" type="submit">Kodu gönder</button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-secondary btn-lg" type="submit">Çıkış yap</button>
                </form>
            </div>
        </div>
    </section>
@endsection
