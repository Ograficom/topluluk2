@extends('layouts.app')

@section('title', 'Reklam Ödeme')
@section('hide_feed_header')

@section('content')
<div class="adx-page">
    <style>
        .adx-page {
            display: grid;
            gap: 14px;
            padding-bottom: 24px;
        }

        .adx-pay-hero {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 20px;
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .adx-pay-hero__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 14px;
            background: #fef9c3;
            color: #a16207;
        }

        .adx-pay-hero__icon svg {
            width: 22px;
            height: 22px;
        }

        .adx-pay-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: 17px;
            font-weight: 800;
        }

        .adx-pay-hero p {
            margin: 2px 0 0;
            color: #64748b;
            font-size: 12.5px;
        }

        .adx-card {
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            padding: 20px;
        }

        .adx-card__title {
            margin: 0 0 14px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
        }

        .adx-preview {
            width: 100%;
            border-radius: 14px;
            background: #f1f5f9;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .adx-preview img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .adx-order-list {
            display: grid;
            gap: 10px;
            margin: 0;
        }

        .adx-order-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }

        .adx-order-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .adx-order-row dt {
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 600;
        }

        .adx-order-row dd {
            margin: 0;
            color: #0f172a;
            font-size: 13px;
            font-weight: 600;
            text-align: right;
            word-break: break-all;
        }

        .adx-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            background: #fef9c3;
            color: #854d0e;
            font-size: 12px;
            font-weight: 700;
        }

        .adx-status-pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #ca8a04;
        }

        .adx-status-pill--active {
            background: #dcfce7;
            color: #166534;
        }

        .adx-status-pill--active::before {
            background: #16a34a;
        }

        .adx-total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .adx-total-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .adx-total-price {
            color: #0f172a;
            font-size: 26px;
            font-weight: 800;
        }

        .adx-next-steps {
            display: grid;
            gap: 12px;
        }

        .adx-step-row {
            display: flex;
            gap: 12px;
        }

        .adx-step-row__index {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            flex-shrink: 0;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .adx-step-row__text {
            padding-top: 2px;
            color: #334155;
            font-size: 13px;
            line-height: 1.5;
        }

        .adx-step-row__text strong {
            display: block;
            color: #0f172a;
            font-size: 13.5px;
            margin-bottom: 1px;
        }

        .adx-contact-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 48px;
            margin-top: 18px;
            border: 0;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: background-color .15s ease;
        }

        .adx-contact-btn:hover {
            background: #1d4ed8;
        }

        .adx-contact-btn svg {
            width: 16px;
            height: 16px;
        }

        .adx-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
        }

        .adx-back-link:hover {
            color: #64748b;
        }

        html.dark .adx-pay-hero,
        [data-theme="dark"] .adx-pay-hero,
        html.dark .adx-card,
        [data-theme="dark"] .adx-card {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.08);
        }

        html.dark .adx-pay-hero h1,
        [data-theme="dark"] .adx-pay-hero h1,
        html.dark .adx-card__title,
        [data-theme="dark"] .adx-card__title,
        html.dark .adx-order-row dd,
        [data-theme="dark"] .adx-order-row dd,
        html.dark .adx-total-price,
        [data-theme="dark"] .adx-total-price,
        html.dark .adx-step-row__text strong,
        [data-theme="dark"] .adx-step-row__text strong {
            color: #ffffff;
        }

        html.dark .adx-step-row__text,
        [data-theme="dark"] .adx-step-row__text {
            color: #cbd5e1;
        }

        html.dark .adx-order-row,
        [data-theme="dark"] .adx-order-row,
        html.dark .adx-total-row,
        [data-theme="dark"] .adx-total-row {
            border-color: rgba(255, 255, 255, 0.08);
        }

        @media (max-width: 640px) {
            .adx-pay-hero {
                padding: 14px 16px;
            }

            .adx-card {
                padding: 16px;
                border-radius: 16px;
            }

            .adx-order-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }

            .adx-order-row dd {
                text-align: left;
            }
        }
    </style>

    <div class="adx-pay-hero">
        <span class="adx-pay-hero__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.5v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <h1>Sipariş Özeti</h1>
            <p>#{{ $adOrder->id }} numaralı reklam siparişiniz oluşturuldu.</p>
        </div>
    </div>

    <div class="adx-card">
        @if($adOrder->image_url)
            <div class="adx-preview" style="aspect-ratio: {{ $adOrder->width }} / {{ $adOrder->height ?: 1 }};">
                <img src="{{ $adOrder->image_url }}" alt="{{ $adOrder->title ?: 'Reklam görseli' }}">
            </div>
        @endif

        <dl class="adx-order-list">
            @if($adOrder->title)
                <div class="adx-order-row">
                    <dt>Reklam başlığı</dt>
                    <dd>{{ $adOrder->title }}</dd>
                </div>
            @endif
            <div class="adx-order-row">
                <dt>Reklam alanı</dt>
                <dd>{{ $placement['label'] ?? $adOrder->placement }}</dd>
            </div>
            <div class="adx-order-row">
                <dt>Boyut</dt>
                <dd>{{ $adOrder->width }}×{{ $adOrder->height }} px</dd>
            </div>
            <div class="adx-order-row">
                <dt>Yayın süresi</dt>
                <dd>{{ $adOrder->duration_days }} gün</dd>
            </div>
            @if($adOrder->target_url)
                <div class="adx-order-row">
                    <dt>Hedef bağlantı</dt>
                    <dd>{{ $adOrder->target_url }}</dd>
                </div>
            @endif
            <div class="adx-order-row">
                <dt>Durum</dt>
                <dd>
                    @if($adOrder->status === 'active')
                        <span class="adx-status-pill adx-status-pill--active">Yayında</span>
                    @else
                        <span class="adx-status-pill">Ödeme bekliyor</span>
                    @endif
                </dd>
            </div>
        </dl>

        <div class="adx-total-row">
            <span class="adx-total-label">Toplam tutar</span>
            <span class="adx-total-price">{{ $adOrder->formatted_price }}</span>
        </div>
    </div>

    @if($adOrder->status !== 'active')
        <div class="adx-card">
            <h2 class="adx-card__title">Ödemeyi tamamlamak için</h2>

            <div class="adx-next-steps">
                <div class="adx-step-row">
                    <span class="adx-step-row__index">1</span>
                    <p class="adx-step-row__text">
                        <strong>Bizimle iletişime geçin</strong>
                        Sipariş numaranızı (#{{ $adOrder->id }}) belirterek iletişim formundan ekibimize ulaşın.
                    </p>
                </div>
                <div class="adx-step-row">
                    <span class="adx-step-row__index">2</span>
                    <p class="adx-step-row__text">
                        <strong>Ödeme yönteminizi belirleyin</strong>
                        Ekibimiz size uygun ödeme yöntemini (havale/EFT veya kart) paylaşır.
                    </p>
                </div>
                <div class="adx-step-row">
                    <span class="adx-step-row__index">3</span>
                    <p class="adx-step-row__text">
                        <strong>Reklamınız yayına girsin</strong>
                        Ödeme onaylandıktan sonra reklamınız seçtiğiniz sürede otomatik olarak yayınlanır.
                    </p>
                </div>
            </div>

            <a href="{{ route('contact.create') }}" class="adx-contact-btn">
                İletişime Geç
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    @endif

    <a href="{{ route('advertise.create') }}" class="adx-back-link">
        <svg viewBox="0 0 24 24" fill="none" style="width:14px;height:14px;"><path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Yeni bir reklam siparişi oluştur
    </a>
</div>
@endsection
