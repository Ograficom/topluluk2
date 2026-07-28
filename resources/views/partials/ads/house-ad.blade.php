@php
    $slotKey = (string) ($slotKey ?? '');

    $sizes = [
        'ads_sidebar_top' => ['h' => 380, 'layout' => 'vertical'],
        'ads_sidebar_story' => ['h' => 540, 'layout' => 'vertical'],
        'ads_left_sidebar_top' => ['h' => 320, 'layout' => 'vertical'],
        'ads_feed_top' => ['h' => 369, 'layout' => 'wide'],
        'ads_feed_inline' => ['h' => 369, 'layout' => 'wide'],
        'ads_main_before_content' => ['h' => 220, 'layout' => 'compact'],
        'ads_main_after_content' => ['h' => 220, 'layout' => 'compact'],
        'ads_mobile_inline' => ['h' => 203, 'layout' => 'compact'],
    ];

    $size = $sizes[$slotKey] ?? ['h' => 220, 'layout' => 'compact'];
    $advertiseUrl = route('advertise.create');
    $logoUrl = asset('images/ografi-logo.png') . '?v=20260714a';
@endphp

<a
    href="{{ $advertiseUrl }}"
    class="house-ad house-ad--{{ $size['layout'] }}"
    style="min-height: {{ $size['h'] }}px;"
>
    <span class="house-ad__tag">Reklam Alanı</span>

    @if ($size['layout'] === 'vertical')
        <div class="house-ad__body house-ad__body--vertical">
            <img src="{{ $logoUrl }}" alt="Ografi" class="house-ad__logo">
            <h3 class="house-ad__title">Bu alan senin<br>reklamın olabilir</h3>
            <p class="house-ad__text">Ografi kullanıcılarına bu alandan ulaş. Kurulum birkaç dakika sürer.</p>
            <span class="house-ad__cta">Reklam Ver</span>
        </div>
    @elseif ($size['layout'] === 'wide')
        <div class="house-ad__body house-ad__body--wide">
            <img src="{{ $logoUrl }}" alt="Ografi" class="house-ad__logo house-ad__logo--wide">
            <div class="house-ad__copy">
                <h3 class="house-ad__title">Bu alan senin reklamın olabilir</h3>
                <p class="house-ad__text">Ografi'de akış içinde, sağ sütunda veya sayfa üstünde - binlerce kullanıcıya ulaş.</p>
            </div>
            <span class="house-ad__cta house-ad__cta--wide">Reklam Ver</span>
        </div>
    @else
        <div class="house-ad__body house-ad__body--compact">
            <img src="{{ $logoUrl }}" alt="Ografi" class="house-ad__logo">
            <div class="house-ad__copy">
                <h3 class="house-ad__title house-ad__title--compact">Bu alan senin reklamın olabilir</h3>
                <span class="house-ad__cta">Reklam Ver</span>
            </div>
        </div>
    @endif
</a>

@once
    <style>
        .house-ad {
            display: flex;
            width: 100%;
            box-sizing: border-box;
            flex-direction: column;
            position: relative;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            padding: 16px;
            text-decoration: none;
            overflow: hidden;
        }

        .house-ad__tag {
            position: absolute;
            top: 10px;
            right: 12px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .house-ad__body {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
        }

        .house-ad__body--vertical {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            padding-top: 8px;
        }

        .house-ad__body--wide {
            align-items: center;
            gap: 20px;
        }

        .house-ad__body--compact {
            align-items: center;
            gap: 14px;
        }

        .house-ad__logo {
            display: block;
            width: 40px;
            height: 40px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .house-ad__logo--wide {
            width: 56px;
            height: 56px;
        }

        .house-ad__copy {
            min-width: 0;
            flex: 1 1 auto;
        }

        .house-ad__title {
            margin: 0;
            color: #0f172a;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.01em;
        }

        .house-ad__title--compact {
            font-size: 13px;
        }

        .house-ad__text {
            margin: 0;
            color: #64748b;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .house-ad__body--vertical .house-ad__text {
            max-width: 220px;
        }

        .house-ad__cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 999px;
            background: #2563eb;
            color: #ffffff !important;
            padding: 8px 16px;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .house-ad__body--compact .house-ad__cta {
            margin-top: 6px;
            padding: 6px 13px;
            font-size: 11.5px;
        }

        .house-ad:hover .house-ad__cta {
            background: #1d4ed8;
        }

        @media (max-width: 480px) {
            .house-ad__body--wide {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
                gap: 10px;
            }

            .house-ad__body--wide .house-ad__cta--wide {
                align-self: flex-start;
            }
        }
    </style>
@endonce
