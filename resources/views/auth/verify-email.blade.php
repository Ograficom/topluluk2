@extends('layouts.app')

@section('title', 'E-posta adresini doğrula')

@push('head')
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
