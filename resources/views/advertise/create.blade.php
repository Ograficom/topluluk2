@extends('layouts.app')

@section('title', 'Reklam Ver')
@section('hide_feed_header')

@section('content')
<div class="adx-page">
    <style>
        .adx-page {
            display: grid;
            gap: 18px;
            padding-bottom: 24px;
        }

        .adx-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
            padding: 28px 20px;
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .adx-hero__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: #2563eb;
            color: #ffffff;
        }

        .adx-hero__icon svg {
            width: 26px;
            height: 26px;
        }

        .adx-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .adx-hero p {
            margin: 0;
            max-width: 480px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        .adx-card {
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            padding: 20px;
        }

        .adx-card__head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 16px;
        }

        .adx-card__step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .adx-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
        }

        .adx-card__title small {
            display: block;
            margin-top: 2px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 500;
        }

        .adx-placement-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .adx-option {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 12px;
            border-radius: 14px;
            border: 1.5px solid rgba(15, 23, 42, 0.09);
            background: #fafafa;
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .adx-option input {
            position: absolute;
            inset: 0;
            opacity: 0;
            margin: 0;
            cursor: pointer;
        }

        .adx-option:hover {
            border-color: rgba(37, 99, 235, 0.35);
        }

        .adx-option:has(input:checked) {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .adx-option__visual {
            width: 100%;
            border-radius: 8px;
            background: repeating-linear-gradient(135deg, #e2e8f0, #e2e8f0 6px, #edf1f5 6px, #edf1f5 12px);
        }

        .adx-option:has(input:checked) .adx-option__visual {
            background: repeating-linear-gradient(135deg, #bfdbfe, #bfdbfe 6px, #dbeafe 6px, #dbeafe 12px);
        }

        .adx-option__label {
            color: #0f172a;
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.25;
        }

        .adx-option__size {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
        }

        .adx-option__desc {
            display: none;
        }

        .adx-check {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 1.5px solid rgba(15, 23, 42, 0.15);
            background: #ffffff;
        }

        .adx-option:has(input:checked) .adx-check {
            border-color: #2563eb;
            background: #2563eb;
        }

        .adx-check svg {
            width: 10px;
            height: 10px;
            color: #ffffff;
            opacity: 0;
        }

        .adx-option:has(input:checked) .adx-check svg {
            opacity: 1;
        }

        .adx-placement-note {
            margin: 12px 0 0;
            padding: 10px 12px;
            border-radius: 10px;
            background: #f8fafc;
            color: #475569;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .adx-duration-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .adx-duration-option {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 14px 8px;
            border-radius: 14px;
            border: 1.5px solid rgba(15, 23, 42, 0.09);
            background: #fafafa;
            cursor: pointer;
            text-align: center;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .adx-duration-option input {
            position: absolute;
            inset: 0;
            opacity: 0;
            margin: 0;
            cursor: pointer;
        }

        .adx-duration-option:hover {
            border-color: rgba(37, 99, 235, 0.35);
        }

        .adx-duration-option:has(input:checked) {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .adx-duration-option__days {
            color: #0f172a;
            font-size: 16px;
            font-weight: 800;
        }

        .adx-duration-option__price {
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .adx-duration-option--best::before {
            content: "En avantajlı";
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            padding: 2px 8px;
            border-radius: 999px;
            background: #16a34a;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .adx-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 180px;
            padding: 20px;
            border-radius: 16px;
            border: 1.5px dashed rgba(37, 99, 235, 0.35);
            background: #f8fafc;
            text-align: center;
            cursor: pointer;
            overflow: hidden;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .adx-upload:hover,
        .adx-upload.is-dragover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .adx-upload input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .adx-upload__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #dbeafe;
            color: #2563eb;
        }

        .adx-upload__icon svg {
            width: 20px;
            height: 20px;
        }

        .adx-upload__title {
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 700;
        }

        .adx-upload__hint {
            color: #94a3b8;
            font-size: 12px;
        }

        .adx-upload__image {
            display: none;
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .adx-upload.has-image .adx-upload__image {
            display: block;
        }

        .adx-upload.has-image .adx-upload__icon,
        .adx-upload.has-image .adx-upload__title,
        .adx-upload.has-image .adx-upload__hint {
            display: none;
        }

        .adx-upload__badge {
            display: none;
            position: absolute;
            bottom: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.72);
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
        }

        .adx-upload.has-image .adx-upload__badge {
            display: block;
        }

        .adx-help {
            margin: 10px 0 0;
            color: #94a3b8;
            font-size: 12px;
        }

        .adx-field {
            display: grid;
            gap: 6px;
        }

        .adx-field + .adx-field {
            margin-top: 14px;
        }

        .adx-label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 600;
        }

        .adx-label__optional {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 500;
        }

        .adx-input {
            width: 100%;
            min-height: 44px;
            border: 1.5px solid rgba(15, 23, 42, 0.09);
            border-radius: 12px;
            background: #fafafa;
            color: #0f172a;
            padding: 0 14px;
            font-size: 14px;
        }

        .adx-input:focus {
            outline: none;
            border-color: #2563eb;
            background: #ffffff;
        }

        .adx-error {
            margin: 0;
            color: #dc2626;
            font-size: 12px;
        }

        .adx-summary-spacer {
            height: 84px;
        }

        .adx-summary {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            background: #0f172a;
            box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.18);
            padding: 12px 16px;
            padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
        }

        .adx-summary__inner {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            max-width: var(--profile-shell-width, 656px);
            margin: 0 auto;
        }

        .adx-summary__meta {
            display: grid;
            gap: 1px;
            flex: 1 1 auto;
            min-width: 0;
        }

        .adx-summary__label {
            color: rgba(255, 255, 255, 0.55);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .adx-summary__price {
            color: #ffffff;
            font-size: 19px;
            font-weight: 800;
            line-height: 1.2;
        }

        .adx-summary__price span {
            margin-left: 6px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-weight: 600;
        }

        .adx-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            border: 0;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .adx-submit-btn:hover {
            background: #1d4ed8;
        }

        .adx-submit-btn svg {
            width: 16px;
            height: 16px;
        }

        html.dark .adx-hero,
        [data-theme="dark"] .adx-hero {
            background: linear-gradient(180deg, #172554 0%, #111827 100%);
            border-color: rgba(255, 255, 255, 0.08);
        }

        html.dark .adx-hero h1,
        [data-theme="dark"] .adx-hero h1 {
            color: #ffffff;
        }

        html.dark .adx-card,
        [data-theme="dark"] .adx-card {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.08);
        }

        html.dark .adx-card__title,
        [data-theme="dark"] .adx-card__title,
        html.dark .adx-option__label,
        [data-theme="dark"] .adx-option__label,
        html.dark .adx-duration-option__days,
        [data-theme="dark"] .adx-duration-option__days,
        html.dark .adx-upload__title,
        [data-theme="dark"] .adx-upload__title,
        html.dark .adx-label,
        [data-theme="dark"] .adx-label,
        html.dark .adx-input,
        [data-theme="dark"] .adx-input {
            color: #ffffff;
        }

        html.dark .adx-option,
        [data-theme="dark"] .adx-option,
        html.dark .adx-duration-option,
        [data-theme="dark"] .adx-duration-option,
        html.dark .adx-input,
        [data-theme="dark"] .adx-input {
            background: #1f2937;
            border-color: rgba(255, 255, 255, 0.09);
        }

        html.dark .adx-option:has(input:checked),
        [data-theme="dark"] .adx-option:has(input:checked),
        html.dark .adx-duration-option:has(input:checked),
        [data-theme="dark"] .adx-duration-option:has(input:checked) {
            background: rgba(37, 99, 235, 0.18);
            border-color: #3b82f6;
        }

        html.dark .adx-upload,
        [data-theme="dark"] .adx-upload {
            background: #1f2937;
        }

        html.dark .adx-placement-note,
        [data-theme="dark"] .adx-placement-note {
            background: #1f2937;
            color: #cbd5e1;
        }

        @media (max-width: 640px) {
            .adx-hero {
                padding: 22px 16px;
            }

            .adx-hero h1 {
                font-size: 20px;
            }

            .adx-card {
                padding: 16px;
                border-radius: 16px;
            }

            .adx-placement-grid {
                grid-template-columns: 1fr;
            }

            .adx-option {
                flex-direction: row;
                align-items: center;
            }

            .adx-option__visual {
                width: 48px;
                height: 48px;
                flex-shrink: 0;
            }

            .adx-duration-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }

            .adx-summary-spacer {
                height: 168px;
            }

            .adx-summary {
                bottom: calc(64px + max(8px, env(safe-area-inset-bottom, 0px)) + 10px);
                border-radius: 16px;
                left: 6px;
                right: 6px;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.18);
            }

            .adx-summary__inner {
                flex-wrap: wrap;
            }

            .adx-summary__meta {
                order: 1;
                flex: 1 1 100%;
            }

            .adx-submit-btn {
                order: 2;
                flex: 1 1 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="adx-hero">
        <span class="adx-hero__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="M11 5.5 5.5 9H3a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h2.5L11 18.5V5.5Z" fill="currentColor"/><path d="M14.5 8.5c1 .9 1 5.1 0 6M17.2 6.3c2 2.2 2 8.2 0 10.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </span>
        <h1>Ografi'de Reklam Ver</h1>
        <p>Binlerce aktif kullanıcıya ulaşın. Alanınızı seçin, görselinizi yükleyin, birkaç dakikada yayına hazır olun.</p>
    </div>

    @if ($errors->any())
        <div class="adx-card" style="border-color:#fecaca;background:#fef2f2;">
            <p class="adx-error" style="font-weight:600;margin-bottom:6px;">Formda düzeltilmesi gereken alanlar var:</p>
            <ul style="margin:0;padding-left:18px;color:#dc2626;font-size:12.5px;line-height:1.6;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('advertise.store') }}" enctype="multipart/form-data" id="adxForm">
        @csrf

        <div class="adx-card" style="margin-bottom:14px;">
            <div class="adx-card__head">
                <span class="adx-card__step">1</span>
                <h2 class="adx-card__title">Reklam alanını seçin</h2>
            </div>

            <div class="adx-placement-grid">
                @foreach($placements as $key => $placement)
                    <label class="adx-option" data-placement-option>
                        <input
                            type="radio"
                            name="placement"
                            value="{{ $key }}"
                            data-width="{{ $placement['width'] }}"
                            data-height="{{ $placement['height'] }}"
                            data-label="{{ $placement['label'] }}"
                            data-description="{{ $placement['description'] }}"
                            @checked(old('placement', 'sidebar_top') === $key)
                            required
                        >
                        <span class="adx-check" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="adx-option__visual" style="aspect-ratio: {{ $placement['width'] }} / {{ $placement['height'] }};"></span>
                        <span class="adx-option__label">{{ $placement['label'] }}</span>
                        <span class="adx-option__size">{{ $placement['width'] }}×{{ $placement['height'] }} px</span>
                    </label>
                @endforeach
            </div>
            <p class="adx-placement-note" id="placementDescription"></p>
            @error('placement')
                <p class="adx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="adx-card" style="margin-bottom:14px;">
            <div class="adx-card__head">
                <span class="adx-card__step">2</span>
                <h2 class="adx-card__title">Yayın süresini seçin</h2>
            </div>

            <div class="adx-duration-grid">
                @foreach($durations as $days => $priceCents)
                    <label class="adx-duration-option {{ (int) $days === 14 ? 'adx-duration-option--best' : '' }}">
                        <input
                            type="radio"
                            name="duration_days"
                            value="{{ $days }}"
                            data-price="{{ $priceCents }}"
                            @checked((int) old('duration_days', 7) === (int) $days)
                            required
                        >
                        <span class="adx-duration-option__days">{{ $days }} gün</span>
                        <span class="adx-duration-option__price">{{ number_format($priceCents / 100, 0, ',', '.') }} TRY</span>
                    </label>
                @endforeach
            </div>
            @error('duration_days')
                <p class="adx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="adx-card" style="margin-bottom:14px;">
            <div class="adx-card__head">
                <span class="adx-card__step">3</span>
                <h2 class="adx-card__title">
                    Reklam görselini yükleyin
                    <small id="uploadSizeHint">Önerilen boyut: 304×380 px</small>
                </h2>
            </div>

            <label class="adx-upload" id="adxUpload" for="image">
                <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp,image/gif" required>
                <span class="adx-upload__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0 4 4m-4-4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
                <span class="adx-upload__title">Görsel yüklemek için tıklayın veya sürükleyin</span>
                <span class="adx-upload__hint">JPG, PNG, WEBP veya GIF · en fazla 4 MB</span>
                <img class="adx-upload__image" id="uploadPreview" alt="Reklam görseli önizlemesi">
                <span class="adx-upload__badge">Değiştirmek için tıklayın</span>
            </label>
            <p class="adx-help">Görsel, seçtiğiniz alanın oranına göre otomatik kırpılarak gösterilir.</p>
            @error('image')
                <p class="adx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="adx-card" style="margin-bottom:14px;">
            <div class="adx-card__head">
                <span class="adx-card__step">4</span>
                <h2 class="adx-card__title">Reklam bilgileri</h2>
            </div>

            <div class="adx-field">
                <label class="adx-label" for="title">Reklam başlığı <span class="adx-label__optional">(opsiyonel)</span></label>
                <input class="adx-input" type="text" id="title" name="title" value="{{ old('title') }}" maxlength="80" placeholder="Örn. Yaz indirimi kampanyası">
                @error('title')
                    <p class="adx-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="adx-field">
                <label class="adx-label" for="target_url">Hedef bağlantı <span class="adx-label__optional">(opsiyonel)</span></label>
                <input class="adx-input" type="url" id="target_url" name="target_url" value="{{ old('target_url') }}" placeholder="https://siteniz.com">
                @error('target_url')
                    <p class="adx-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="adx-summary-spacer" aria-hidden="true"></div>
    </form>

    <div class="adx-summary">
        <div class="adx-summary__inner">
            <div class="adx-summary__meta">
                <span class="adx-summary__label" id="summaryPlacement">Sağ sidebar üst</span>
                <span class="adx-summary__price" id="summaryPrice">350 <span>TRY toplam</span></span>
            </div>
            <button type="submit" form="adxForm" class="adx-submit-btn">
                Ödemeye Geç
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('adxForm');
        if (!form) return;

        const placementInputs = form.querySelectorAll('input[name="placement"]');
        const durationInputs = form.querySelectorAll('input[name="duration_days"]');
        const imageInput = document.getElementById('image');
        const upload = document.getElementById('adxUpload');
        const uploadPreview = document.getElementById('uploadPreview');
        const uploadSizeHint = document.getElementById('uploadSizeHint');
        const placementDescription = document.getElementById('placementDescription');
        const summaryPlacement = document.getElementById('summaryPlacement');
        const summaryPrice = document.getElementById('summaryPrice');

        const formatPrice = function (cents) {
            return (Number(cents || 0) / 100).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
        };

        const syncSummary = function () {
            const checkedPlacement = form.querySelector('input[name="placement"]:checked');
            const checkedDuration = form.querySelector('input[name="duration_days"]:checked');

            if (checkedPlacement) {
                const w = checkedPlacement.dataset.width;
                const h = checkedPlacement.dataset.height;
                summaryPlacement.textContent = checkedPlacement.dataset.label + ' · ' + w + '×' + h + ' px';
                uploadSizeHint.textContent = 'Önerilen boyut: ' + w + '×' + h + ' px';
                placementDescription.textContent = checkedPlacement.dataset.description || '';
            }

            if (checkedDuration) {
                summaryPrice.innerHTML = formatPrice(checkedDuration.dataset.price) + ' <span>TRY toplam</span>';
            }
        };

        placementInputs.forEach(function (input) { input.addEventListener('change', syncSummary); });
        durationInputs.forEach(function (input) { input.addEventListener('change', syncSummary); });

        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];

            if (!file) {
                upload.classList.remove('has-image');
                uploadPreview.removeAttribute('src');
                return;
            }

            uploadPreview.src = URL.createObjectURL(file);
            upload.classList.add('has-image');
        });

        ['dragenter', 'dragover'].forEach(function (evt) {
            upload.addEventListener(evt, function (e) {
                e.preventDefault();
                upload.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            upload.addEventListener(evt, function (e) {
                e.preventDefault();
                upload.classList.remove('is-dragover');
            });
        });

        upload.addEventListener('drop', function (e) {
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (!file) return;
            imageInput.files = e.dataTransfer.files;
            imageInput.dispatchEvent(new Event('change'));
        });

        syncSummary();
    });
</script>
@endsection
