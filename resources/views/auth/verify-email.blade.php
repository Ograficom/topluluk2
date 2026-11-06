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
        .verify-send-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .verify-countdown {
            min-width: 24px;
            padding-left: 8px;
            border-left: 1px solid rgb(255 255 255 / 45%);
            font-variant-numeric: tabular-nums;
        }
        .verify-send-button:disabled {
            cursor: not-allowed;
            opacity: .75;
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
                    <button class="btn btn-primary btn-lg verify-send-button" type="submit">
                        <span>Kodu gönder</span>
                        <span class="verify-countdown" data-verify-countdown hidden>59</span>
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-secondary btn-lg" type="submit">Çıkış yap</button>
                </form>
            </div>
        </div>
    </section>
@push('scripts')
    <script>
        (() => {
            const button = document.querySelector('.verify-send-button');
            const countdown = document.querySelector('[data-verify-countdown]');

            if (!button || !countdown) return;

            let remaining = 59;
            let interval = null;

            const startCountdown = () => {
                if (interval) window.clearInterval(interval);

                remaining = 59;
                button.disabled = true;
                button.setAttribute('aria-disabled', 'true');
                countdown.hidden = false;
                countdown.textContent = remaining;

                interval = window.setInterval(() => {
                    remaining -= 1;
                    countdown.textContent = remaining;

                    if (remaining <= 0) {
                        window.clearInterval(interval);
                        interval = null;
                        countdown.hidden = true;
                        button.disabled = false;
                        button.removeAttribute('aria-disabled');
                    }
                }, 1000);
            };

            @if (session('status'))
                startCountdown();
            @endif
        })();
    </script>
@endpush

@endsection
