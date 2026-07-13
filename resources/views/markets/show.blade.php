@extends('layouts.app')

@php
    $symbol = $symbol ?? 'usdtry';

    $markets = [
        'usdtry' => [
            'label' => 'Amerikan Doları',
            'title' => 'Doların Türk lirası karşısındaki döviz kuru',
            'from' => 'Amerikan Doları',
            'to' => 'Türk Lirası',
            'format' => 'try',
        ],
        'eurtry' => [
            'label' => 'EUR',
            'title' => 'Euro\'nun Türk lirası karşısındaki döviz kuru',
            'from' => 'Euro',
            'to' => 'Türk Lirası',
            'format' => 'try',
        ],
        'btcusd' => [
            'label' => 'BTC / USD',
            'title' => 'Bitcoinin Amerikan doları karşısındaki değeri',
            'from' => 'Bitcoin',
            'to' => 'Amerikan Doları',
            'format' => 'usd',
        ],
        'goldtry' => [
            'label' => 'Altın',
            'title' => 'Gram altının Türk lirası karşısındaki değeri',
            'from' => 'Gram Altın',
            'to' => 'Türk Lirası',
            'format' => 'try',
        ],
    ];

    $activeMarket = $markets[$symbol] ?? $markets['usdtry'];
@endphp

@section('title', $activeMarket['label'] . ' Canlı Kur')

@section('content')
    <div
        class="market-page"
        data-market-page
        data-active-symbol="{{ $symbol }}"
    >
        <section class="market-card market-main-card">
            <div class="market-tabs" aria-label="Piyasa sekmeleri">
                @foreach($markets as $key => $market)
                    <a
                        href="{{ route('markets.show', $key) }}"
                        class="market-tab {{ $symbol === $key ? 'is-active' : '' }}"
                        data-market-tab="{{ $key }}"
                    >
                        <span class="market-tab__label">{{ $market['label'] }}</span>

                        <span class="market-tab__bottom">
                            <span class="market-tab__value" data-tab-value="{{ $key }}">Yükleniyor</span>
                            <span class="market-tab__arrow is-flat" data-tab-arrow="{{ $key }}"></span>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="market-content">
                <h1 class="market-title">{{ $activeMarket['title'] }}</h1>

                <div class="market-big-row">
                    <strong class="market-big-value" data-main-value>Yükleniyor</strong>
                    <span class="market-change is-flat" data-main-change>0,00%</span>
                </div>

                <div class="market-range-buttons">
                    <button type="button" class="market-range-button is-active" data-range-button="1d">1 Gün</button>
                    <button type="button" class="market-range-button" data-range-button="1w">1 Hafta</button>
                    <button type="button" class="market-range-button" data-range-button="1m">1 Ay</button>
                </div>

                <div class="market-chart-wrap">
                    <svg class="market-chart" viewBox="0 0 760 300" preserveAspectRatio="none" aria-label="Kur grafiği">
                        <defs>
                            <linearGradient id="marketChartGradientPositive" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#22c55e" stop-opacity="0.22"></stop>
                                <stop offset="100%" stop-color="#22c55e" stop-opacity="0"></stop>
                            </linearGradient>

                            <linearGradient id="marketChartGradientNegative" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ef4444" stop-opacity="0.20"></stop>
                                <stop offset="100%" stop-color="#ef4444" stop-opacity="0"></stop>
                            </linearGradient>
                        </defs>

                        <g class="market-grid">
                            <line x1="0" y1="65" x2="760" y2="65"></line>
                            <line x1="0" y1="135" x2="760" y2="135"></line>
                            <line x1="0" y1="205" x2="760" y2="205"></line>
                        </g>

                        <path class="market-chart-area" data-chart-area d=""></path>
                        <path class="market-chart-line" data-chart-line d=""></path>

                        <g class="market-chart-y-labels">
                            <text x="718" y="70" data-y-label-top>-</text>
                            <text x="718" y="140" data-y-label-mid>-</text>
                            <text x="718" y="210" data-y-label-bottom>-</text>
                        </g>

                        <g class="market-chart-x-labels" data-chart-x-labels></g>
                    </svg>
                </div>
            </div>
        </section>

        <section class="market-card market-converter-card">
            <h2 class="market-converter-title">Para birimi dönüştürücü</h2>

            <div class="market-converter">
                <div class="market-converter-box">
                    <input
                        type="number"
                        min="0"
                        step="any"
                        value="100"
                        class="market-converter-input"
                        data-converter-input
                    >

                    <div class="market-converter-select" data-from-label>{{ $activeMarket['from'] }}</div>
                </div>

                <button type="button" class="market-swap-button" data-swap-button aria-label="Yer değiştir">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 7h10m0 0-3.5-3.5M17 7l-3.5 3.5M17 17H7m0 0 3.5-3.5M7 17l3.5 3.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="market-converter-box">
                    <input
                        type="text"
                        readonly
                        value="-"
                        class="market-converter-input"
                        data-converter-output
                    >

                    <div class="market-converter-select" data-to-label>{{ $activeMarket['to'] }}</div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .market-page {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 8px 0 28px !important;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            color: #111827 !important;
        }

        .market-card {
            width: 100% !important;
            background: #ffffff !important;
            border-radius: 16px !important;
            border: 1px solid #eceff3 !important;
            box-shadow: none !important;
            overflow: hidden !important;
        }

        .market-main-card { margin-bottom: 24px !important; }

        .market-tabs {
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
            min-height: 56px !important;
            border-bottom: 1px solid #e5e7eb !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        .market-tabs::-webkit-scrollbar { display: none !important; }

        .market-tab {
            position: relative !important;
            display: flex !important;
            min-width: 132px !important;
            flex-direction: column !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 10px 24px 11px !important;
            text-decoration: none !important;
            color: #6b7280 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .market-tab:hover,
        .market-tab:focus,
        .market-tab:active {
            background: #f9fafb !important;
            text-decoration: none !important;
            outline: none !important;
        }

        .market-tab.is-active::after {
            content: "" !important;
            position: absolute !important;
            left: 24px !important;
            right: 24px !important;
            bottom: 0 !important;
            height: 3px !important;
            border-radius: 999px 999px 0 0 !important;
            background: #2563eb !important;
        }

        .market-tab__label {
            display: block !important;
            color: #5f6368 !important;
            font-size: 10px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
            letter-spacing: .04em !important;
            white-space: nowrap !important;
        }

        .market-tab__bottom {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            min-height: 18px !important;
        }

        .market-tab__value {
            color: #4b5563 !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .market-tab__arrow { display: inline-block !important; flex: 0 0 auto !important; }
        .market-tab__arrow.is-down { width: 0 !important; height: 0 !important; border-left: 4px solid transparent !important; border-right: 4px solid transparent !important; border-top: 6px solid #ef4444 !important; }
        .market-tab__arrow.is-up { width: 0 !important; height: 0 !important; border-left: 4px solid transparent !important; border-right: 4px solid transparent !important; border-bottom: 6px solid #22c55e !important; }
        .market-tab__arrow.is-flat { width: 6px !important; height: 6px !important; border-radius: 2px !important; background: #9ca3af !important; }

        .market-content { padding: 24px !important; }

        .market-title {
            margin: 0 0 14px !important;
            color: #111827 !important;
            font-size: 20px !important;
            font-weight: 600 !important;
            line-height: 1.3 !important;
        }

        .market-big-row { display: flex !important; align-items: baseline !important; gap: 10px !important; margin-bottom: 26px !important; flex-wrap: wrap !important; }
        .market-big-value { color: #000000 !important; font-size: 44px !important; font-weight: 500 !important; line-height: 1 !important; letter-spacing: -.03em !important; }
        .market-change { font-size: 18px !important; font-weight: 400 !important; line-height: 1 !important; }
        .market-change.is-up { color: #16a34a !important; }
        .market-change.is-down { color: #ef4444 !important; }
        .market-change.is-flat { color: #6b7280 !important; }

        .market-range-buttons { display: flex !important; align-items: center !important; gap: 12px !important; margin-bottom: 30px !important; flex-wrap: wrap !important; }
        .market-range-button { appearance: none !important; min-height: 30px !important; padding: 0 14px !important; border: 0 !important; border-radius: 9px !important; background: transparent !important; color: #111827 !important; font-size: 15px !important; font-weight: 500 !important; line-height: 1 !important; cursor: pointer !important; box-shadow: none !important; transition: background-color .15s ease, color .15s ease !important; }
        .market-range-button:hover, .market-range-button:focus, .market-range-button:active { background: #f3f4f6 !important; color: #111827 !important; outline: none !important; }
        .market-range-button.is-active { background: #eef6ff !important; color: #111827 !important; }

        .market-chart-wrap { width: 100% !important; height: 300px !important; overflow: hidden !important; }
        .market-chart { display: block !important; width: 100% !important; height: 100% !important; }
        .market-grid line { stroke: #e5e7eb !important; stroke-width: 1 !important; }
        .market-chart-area { stroke: none !important; }
        .market-chart-line { fill: none !important; stroke-width: 2.5 !important; stroke-linecap: round !important; stroke-linejoin: round !important; }
        .market-chart-y-labels text, .market-chart-x-labels text { fill: #7b8694 !important; font-size: 12px !important; font-weight: 400 !important; }

        .market-converter-card { padding: 20px 20px 22px !important; background: #ffffff !important; }
        .market-converter-title { margin: 0 0 18px !important; color: #111827 !important; font-size: 18px !important; font-weight: 600 !important; line-height: 1.3 !important; }
        .market-converter { display: grid !important; grid-template-columns: minmax(0, 1fr) 44px minmax(0, 1fr) !important; align-items: center !important; gap: 14px !important; width: 100% !important; }
        .market-converter-box { display: grid !important; grid-template-columns: minmax(80px, 120px) minmax(0, 1fr) !important; align-items: center !important; min-height: 44px !important; border-radius: 12px !important; background: #f1f3f4 !important; border: 0 !important; box-shadow: none !important; overflow: hidden !important; }
        .market-converter-input { width: 100% !important; min-width: 0 !important; height: 44px !important; padding: 0 16px !important; border: 0 !important; outline: none !important; background: transparent !important; color: #111827 !important; font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important; font-size: 15px !important; font-weight: 400 !important; line-height: 1 !important; box-shadow: none !important; }
        .market-converter-input[readonly] { cursor: default !important; }
        .market-converter-select { display: inline-flex !important; align-items: center !important; justify-content: flex-end !important; height: 44px !important; min-width: 0 !important; padding: 0 14px !important; color: #111827 !important; font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important; font-size: 15px !important; font-weight: 400 !important; line-height: 1 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
        .market-converter-select::after { display: none !important; content: none !important; }

        .market-page .market-converter button.market-swap-button,
        .market-page button[data-swap-button],
        button.market-swap-button[data-swap-button] {
            display: inline-flex !important;
            width: 42px !important;
            min-width: 42px !important;
            max-width: 42px !important;
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 auto !important;
            border: 0 !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            background-color: #2563eb !important;
            color: #ffffff !important;
            opacity: 1 !important;
            visibility: visible !important;
            overflow: visible !important;
            cursor: pointer !important;
            box-shadow: none !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            z-index: 20 !important;
            position: relative !important;
            place-self: center !important;
            transition: background-color .16s ease, transform .16s ease !important;
        }

        .market-page .market-converter button.market-swap-button:hover,
        .market-page .market-converter button.market-swap-button:focus,
        .market-page .market-converter button.market-swap-button:active,
        .market-page button[data-swap-button]:hover,
        .market-page button[data-swap-button]:focus,
        .market-page button[data-swap-button]:active {
            background: #1d4ed8 !important;
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
            transform: scale(1.03) !important;
            opacity: 1 !important;
            visibility: visible !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .market-page .market-converter button.market-swap-button svg,
        .market-page button[data-swap-button] svg,
        button.market-swap-button[data-swap-button] svg { display: block !important; width: 18px !important; height: 18px !important; color: #ffffff !important; stroke: #ffffff !important; fill: none !important; opacity: 1 !important; visibility: visible !important; }
        .market-page .market-converter button.market-swap-button svg path,
        .market-page button[data-swap-button] svg path,
        button.market-swap-button[data-swap-button] svg path { stroke: #ffffff !important; color: #ffffff !important; }

        html.dark .market-page, .dark .market-page { color: #e5e7eb !important; }
        html.dark .market-card, .dark .market-card { background: #0f172a !important; border-color: #1e293b !important; }
        html.dark .market-main-card, .dark .market-main-card { background: #0f172a !important; }
        html.dark .market-tabs, .dark .market-tabs { background: #0f172a !important; border-bottom-color: #1e293b !important; }
        html.dark .market-tab, .dark .market-tab { color: #cbd5e1 !important; background: transparent !important; }
        html.dark .market-tab:hover, html.dark .market-tab:focus, html.dark .market-tab:active, .dark .market-tab:hover, .dark .market-tab:focus, .dark .market-tab:active { background: #111827 !important; }
        html.dark .market-tab.is-active::after, .dark .market-tab.is-active::after { background: #60a5fa !important; }
        html.dark .market-tab__label, .dark .market-tab__label { color: #cbd5e1 !important; }
        html.dark .market-tab__value, .dark .market-tab__value { color: #e5e7eb !important; }
        html.dark .market-content, .dark .market-content { background: #0f172a !important; }
        html.dark .market-title, html.dark .market-big-value, html.dark .market-converter-title, .dark .market-title, .dark .market-big-value, .dark .market-converter-title { color: #f8fafc !important; }
        html.dark .market-change.is-flat, .dark .market-change.is-flat { color: #94a3b8 !important; }
        html.dark .market-range-button, .dark .market-range-button { color: #e5e7eb !important; background: transparent !important; }
        html.dark .market-range-button:hover, html.dark .market-range-button:focus, html.dark .market-range-button:active, .dark .market-range-button:hover, .dark .market-range-button:focus, .dark .market-range-button:active { background: #1e293b !important; color: #ffffff !important; }
        html.dark .market-range-button.is-active, .dark .market-range-button.is-active { background: #1d4ed8 !important; color: #ffffff !important; }
        html.dark .market-grid line, .dark .market-grid line { stroke: #1e293b !important; }
        html.dark .market-chart-y-labels text, html.dark .market-chart-x-labels text, .dark .market-chart-y-labels text, .dark .market-chart-x-labels text { fill: #94a3b8 !important; }
        html.dark .market-converter-card, .dark .market-converter-card { background: #0f172a !important; }
        html.dark .market-converter-box, .dark .market-converter-box { background: #111827 !important; }
        html.dark .market-converter-input, html.dark .market-converter-select, .dark .market-converter-input, .dark .market-converter-select { color: #f8fafc !important; }
        html.dark .market-page .market-converter button.market-swap-button, html.dark .market-page button[data-swap-button], .dark .market-page .market-converter button.market-swap-button, .dark .market-page button[data-swap-button] { background: #2563eb !important; background-color: #2563eb !important; color: #ffffff !important; }
        html.dark .market-page .market-converter button.market-swap-button:hover, html.dark .market-page .market-converter button.market-swap-button:focus, html.dark .market-page .market-converter button.market-swap-button:active, .dark .market-page .market-converter button.market-swap-button:hover, .dark .market-page .market-converter button.market-swap-button:focus, .dark .market-page .market-converter button.market-swap-button:active { background: #1d4ed8 !important; background-color: #1d4ed8 !important; color: #ffffff !important; }

        @media (max-width: 640px) {
            html, body { overflow-x: hidden !important; }
            .market-page { width: 100vw !important; max-width: 100vw !important; margin-left: calc(50% - 50vw) !important; margin-right: calc(50% - 50vw) !important; padding: 0 0 22px !important; overflow-x: hidden !important; box-sizing: border-box !important; }
            .market-card { width: 100% !important; max-width: 100% !important; border-radius: 0 !important; border-left: 0 !important; border-right: 0 !important; box-shadow: none !important; overflow: hidden !important; }
            .market-main-card { margin-bottom: 14px !important; }
            .market-tabs { display: grid !important; grid-template-columns: repeat(4, minmax(0, 1fr)) !important; width: 100% !important; min-height: 54px !important; padding: 0 !important; gap: 0 !important; overflow: hidden !important; box-sizing: border-box !important; }
            .market-tab { min-width: 0 !important; width: 100% !important; padding: 8px 8px 10px !important; gap: 5px !important; align-items: flex-start !important; justify-content: center !important; }
            .market-tab.is-active::after { left: 8px !important; right: 8px !important; height: 2px !important; }
            .market-tab__label { font-size: 8.5px !important; letter-spacing: 0 !important; max-width: 100% !important; overflow: hidden !important; text-overflow: ellipsis !important; }
            .market-tab__value { font-size: 11px !important; }
            .market-tab__bottom { gap: 4px !important; width: 100% !important; }
            .market-content { padding: 16px 14px 18px !important; width: 100% !important; box-sizing: border-box !important; }
            .market-title { margin-bottom: 12px !important; font-size: 17px !important; font-weight: 500 !important; line-height: 1.35 !important; }
            .market-big-row { gap: 8px !important; margin-bottom: 18px !important; }
            .market-big-value { font-size: 34px !important; font-weight: 500 !important; letter-spacing: -0.03em !important; }
            .market-change { font-size: 15px !important; font-weight: 400 !important; }
            .market-range-buttons { gap: 8px !important; margin-bottom: 18px !important; padding: 0 !important; }
            .market-range-button { min-height: 30px !important; padding: 0 12px !important; border-radius: 9px !important; font-size: 13px !important; font-weight: 400 !important; }
            .market-chart-wrap { width: 100% !important; height: 230px !important; overflow: hidden !important; }
            .market-chart { width: 100% !important; height: 100% !important; }
            .market-chart-y-labels text, .market-chart-x-labels text { font-size: 10px !important; }
            .market-converter-card { padding: 18px 14px 20px !important; border-radius: 0 !important; }
            .market-converter-title { margin-bottom: 14px !important; font-size: 17px !important; font-weight: 500 !important; }
            .market-converter { display: grid !important; grid-template-columns: 1fr !important; gap: 10px !important; width: 100% !important; }
            .market-converter-box { width: 100% !important; max-width: 100% !important; min-height: 42px !important; grid-template-columns: minmax(78px, 96px) minmax(0, 1fr) !important; border-radius: 12px !important; background: #f1f3f4 !important; box-sizing: border-box !important; }
            .market-converter-input { height: 42px !important; padding: 0 12px !important; font-size: 14px !important; font-weight: 400 !important; }
            .market-converter-select { height: 42px !important; justify-content: flex-end !important; padding: 0 12px !important; font-size: 13px !important; font-weight: 400 !important; }
            .market-page .market-converter button.market-swap-button, .market-page button[data-swap-button] { width: 42px !important; min-width: 42px !important; height: 42px !important; min-height: 42px !important; margin: 0 auto !important; background: #2563eb !important; background-color: #2563eb !important; color: #ffffff !important; }
            .market-page .market-converter button.market-swap-button svg, .market-page button[data-swap-button] svg { width: 18px !important; height: 18px !important; stroke: #ffffff !important; }
            html.dark .market-card, .dark .market-card { background: #0f172a !important; border-color: #1e293b !important; }
            html.dark .market-converter-box, .dark .market-converter-box { background: #111827 !important; }
        }

        @media (max-width: 390px) {
            .market-content { padding-left: 12px !important; padding-right: 12px !important; }
            .market-tab { padding-left: 6px !important; padding-right: 6px !important; }
            .market-tab__label { font-size: 8px !important; }
            .market-tab__value { font-size: 10.5px !important; }
            .market-title { font-size: 16px !important; }
            .market-big-value { font-size: 31px !important; }
            .market-chart-wrap { height: 215px !important; }
            .market-converter-card { padding-left: 12px !important; padding-right: 12px !important; }
            .market-converter-box { grid-template-columns: minmax(70px, 88px) minmax(0, 1fr) !important; }
            .market-converter-input { font-size: 13px !important; }
            .market-converter-select { font-size: 12px !important; }
        }
    </style>

    <script>
        (() => {
            const page = document.querySelector('[data-market-page]');

            if (!page) {
                return;
            }

            const activeSymbol = page.dataset.activeSymbol || 'usdtry';

            let activeRange = '1d';
            let activeHistory = [];
            let converterSwapped = false;
            let isLoadingHistory = false;

            const LIVE_REFRESH_MS = 15000;
            const ONE_DAY = 24 * 60 * 60 * 1000;

            const marketConfig = {
                usdtry: { label: 'Amerikan Doları', from: 'Amerikan Doları', to: 'Türk Lirası', format: 'try', seed: 1.11 },
                eurtry: { label: 'EUR', from: 'Euro', to: 'Türk Lirası', format: 'try', seed: 1.42 },
                btcusd: { label: 'BTC / USD', from: 'Bitcoin', to: 'Amerikan Doları', format: 'usd', seed: 2.18 },
                goldtry: { label: 'Altın', from: 'Gram Altın', to: 'Türk Lirası', format: 'try', seed: 2.72 },
            };

            const liveValues = { usdtry: null, eurtry: null, btcusd: null, goldtry: null };
            const previousValues = { usdtry: null, eurtry: null, btcusd: null, goldtry: null };

            const mainValueEl = page.querySelector('[data-main-value]');
            const mainChangeEl = page.querySelector('[data-main-change]');
            const chartLineEl = page.querySelector('[data-chart-line]');
            const chartAreaEl = page.querySelector('[data-chart-area]');
            const xLabelsEl = page.querySelector('[data-chart-x-labels]');
            const yTopEl = page.querySelector('[data-y-label-top]');
            const yMidEl = page.querySelector('[data-y-label-mid]');
            const yBottomEl = page.querySelector('[data-y-label-bottom]');
            const converterInputEl = page.querySelector('[data-converter-input]');
            const converterOutputEl = page.querySelector('[data-converter-output]');
            const fromLabelEl = page.querySelector('[data-from-label]');
            const toLabelEl = page.querySelector('[data-to-label]');
            const swapButtonEl = page.querySelector('[data-swap-button]');

            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            const formatTry = (value) => {
                if (!Number.isFinite(value)) return '-';
                return value.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            const formatUsd = (value) => {
                if (!Number.isFinite(value)) return '-';
                if (value >= 1000) {
                    return (value / 1000).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + 'K';
                }
                return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            const formatPlain = (value, format = 'try') => format === 'usd' ? formatUsd(value) : formatTry(value);

            const formatAxis = (value, format = 'try') => {
                if (!Number.isFinite(value)) return '-';
                if (format === 'usd') {
                    if (value >= 1000) {
                        return (value / 1000).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + 'K';
                    }
                    return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                return value.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            const formatPercent = (value) => {
                if (!Number.isFinite(value)) return '0,00%';
                return value.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
            };

            const getDaysForRange = (range) => {
                if (range === '1m') return 30;
                if (range === '1w') return 7;
                return 1;
            };

            const setArrowState = (arrow, currentValue, previousValue) => {
                if (!arrow) return;
                arrow.classList.remove('is-up', 'is-down', 'is-flat');
                if (!Number.isFinite(currentValue) || !Number.isFinite(previousValue) || currentValue === previousValue) {
                    arrow.classList.add('is-flat');
                    return;
                }
                arrow.classList.add(currentValue > previousValue ? 'is-up' : 'is-down');
            };

            const updateTabs = () => {
                Object.keys(marketConfig).forEach((symbol) => {
                    const value = liveValues[symbol];
                    const valueEl = page.querySelector(`[data-tab-value="${symbol}"]`);
                    const arrowEl = page.querySelector(`[data-tab-arrow="${symbol}"]`);
                    if (!valueEl) return;
                    valueEl.textContent = formatPlain(value, marketConfig[symbol].format);
                    setArrowState(arrowEl, value, previousValues[symbol]);
                });
            };

            const buildXLabel = (timestamp, range) => {
                const date = new Date(timestamp);
                if (range === '1d') {
                    return date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
                }
                return date.toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' });
            };

            const reduceSeries = (series, maxPoints = 7) => {
                if (!Array.isArray(series) || series.length <= maxPoints) return series;
                const result = [];
                for (let i = 0; i < maxPoints; i++) {
                    const index = Math.round((series.length - 1) * (i / (maxPoints - 1)));
                    result.push(series[index]);
                }
                return result;
            };

            const makeTemporarySeries = (value, range, symbol = activeSymbol) => {
                if (!Number.isFinite(value)) return [];
                const now = Date.now();
                const days = getDaysForRange(range);
                const totalMs = days * ONE_DAY;
                const seed = marketConfig[symbol]?.seed || 1;
                const trend = previousValues[symbol] && previousValues[symbol] !== value
                    ? (value > previousValues[symbol] ? 0.008 : -0.008)
                    : 0.004 * Math.sin(seed);
                return Array.from({ length: 7 }).map((_, index) => {
                    const ratio = index / 6;
                    const wave = Math.sin((index + 1) * seed) * 0.005;
                    const drift = (ratio - 0.5) * trend;
                    const v = value * (1 + wave + drift);
                    return { t: now - totalMs + (totalMs * ratio), v };
                });
            };

            const drawChart = () => {
                const format = marketConfig[activeSymbol]?.format || 'try';
                let series = Array.isArray(activeHistory)
                    ? activeHistory.filter(item => Number.isFinite(item.v) && Number.isFinite(item.t))
                    : [];
                const liveValue = liveValues[activeSymbol];
                if (!series.length && Number.isFinite(liveValue)) {
                    series = makeTemporarySeries(liveValue, activeRange, activeSymbol);
                }
                if (Number.isFinite(liveValue) && series.length) {
                    const last = series[series.length - 1];
                    if (!last || Math.abs(last.v - liveValue) > 0.000001) {
                        series = [...series.slice(0, -1), { t: Date.now(), v: liveValue }];
                    }
                }
                series = reduceSeries(series, 7);
                if (!series.length) {
                    chartLineEl.setAttribute('d', '');
                    chartAreaEl.setAttribute('d', '');
                    xLabelsEl.innerHTML = '';
                    yTopEl.textContent = '-';
                    yMidEl.textContent = '-';
                    yBottomEl.textContent = '-';
                    return;
                }
                const values = series.map(item => item.v);
                const minValue = Math.min(...values);
                const maxValue = Math.max(...values);
                const rangeValue = Math.max(maxValue - minValue, Math.abs(maxValue * 0.01), 0.01);
                const chartWidth = 760;
                const topPadding = 28;
                const bottomLineY = 245;
                const usableHeight = 180;
                const points = series.map((item, index) => {
                    const x = (chartWidth / (series.length - 1 || 1)) * index;
                    const normalized = (item.v - minValue) / rangeValue;
                    const y = topPadding + (usableHeight - (normalized * usableHeight));
                    return { x, y, v: item.v, t: item.t };
                });
                const linePath = points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' ');
                const areaPath = `${linePath} L ${points[points.length - 1].x.toFixed(2)} ${bottomLineY} L ${points[0].x.toFixed(2)} ${bottomLineY} Z`;
                const firstValue = series[0].v;
                const lastValue = series[series.length - 1].v;
                const diffRatio = firstValue > 0 ? Math.abs((lastValue - firstValue) / firstValue) : 0;
                let lineColor = '#9ca3af';
                let areaFill = 'rgba(156, 163, 175, 0.12)';
                if (diffRatio > 0.00001) {
                    if (lastValue > firstValue) {
                        lineColor = '#22c55e';
                        areaFill = 'url(#marketChartGradientPositive)';
                    } else {
                        lineColor = '#ef4444';
                        areaFill = 'url(#marketChartGradientNegative)';
                    }
                }
                chartLineEl.setAttribute('d', linePath);
                chartAreaEl.setAttribute('d', areaPath);
                chartLineEl.style.stroke = lineColor;
                chartAreaEl.setAttribute('fill', areaFill);
                yTopEl.textContent = formatAxis(maxValue, format);
                yMidEl.textContent = formatAxis((maxValue + minValue) / 2, format);
                yBottomEl.textContent = formatAxis(minValue, format);
                xLabelsEl.innerHTML = points.map((point) => `<text x="${point.x.toFixed(2)}" y="270" text-anchor="middle">${buildXLabel(point.t, activeRange)}</text>`).join('');
            };

            const updateMainBlock = () => {
                const format = marketConfig[activeSymbol]?.format || 'try';
                const currentValue = liveValues[activeSymbol];
                if (!Number.isFinite(currentValue)) {
                    mainValueEl.textContent = isLoadingHistory ? 'Yükleniyor' : '-';
                    mainChangeEl.textContent = '0,00%';
                    mainChangeEl.classList.remove('is-up', 'is-down');
                    mainChangeEl.classList.add('is-flat');
                    drawChart();
                    return;
                }
                mainValueEl.textContent = formatPlain(currentValue, format);
                const baseValue = activeHistory?.[0]?.v ?? currentValue;
                const changePercent = baseValue > 0 ? ((currentValue - baseValue) / baseValue) * 100 : 0;
                mainChangeEl.classList.remove('is-up', 'is-down', 'is-flat');
                if (changePercent > 0) {
                    mainChangeEl.classList.add('is-up');
                    mainChangeEl.textContent = '+' + formatPercent(changePercent);
                } else if (changePercent < 0) {
                    mainChangeEl.classList.add('is-down');
                    mainChangeEl.textContent = formatPercent(changePercent);
                } else {
                    mainChangeEl.classList.add('is-flat');
                    mainChangeEl.textContent = '0,00%';
                }
                drawChart();
                updateConverter();
            };

            const updateConverterLabels = () => {
                const config = marketConfig[activeSymbol];
                if (!config || !fromLabelEl || !toLabelEl) return;
                fromLabelEl.textContent = converterSwapped ? config.to : config.from;
                toLabelEl.textContent = converterSwapped ? config.from : config.to;
            };

            const updateConverter = () => {
                const currentValue = liveValues[activeSymbol];
                const inputAmount = Number(converterInputEl?.value || 0);
                if (!converterOutputEl || !Number.isFinite(currentValue)) return;
                const output = converterSwapped
                    ? (currentValue > 0 ? inputAmount / currentValue : 0)
                    : inputAmount * currentValue;
                converterOutputEl.value = output.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            const fetchJsonFromAny = async (urls) => {
                let lastError = null;
                for (const url of urls) {
                    try {
                        const response = await fetch(url, { cache: 'no-store' });
                        if (!response.ok) throw new Error(`İstek başarısız: ${response.status}`);
                        return await response.json();
                    } catch (error) {
                        lastError = error;
                    }
                }
                throw lastError || new Error('Veri alınamadı.');
            };

            const fetchLiveCurrencyRates = async () => {
                const response = await fetch('https://open.er-api.com/v6/latest/USD', { cache: 'no-store' });
                if (!response.ok) throw new Error('Döviz verisi alınamadı.');
                const data = await response.json();
                const usdTry = Number(data?.rates?.TRY);
                const eurRate = Number(data?.rates?.EUR);
                const tryRate = Number(data?.rates?.TRY);
                const eurTry = eurRate > 0 ? tryRate / eurRate : null;
                if (Number.isFinite(usdTry)) { previousValues.usdtry = liveValues.usdtry; liveValues.usdtry = usdTry; }
                if (Number.isFinite(eurTry)) { previousValues.eurtry = liveValues.eurtry; liveValues.eurtry = eurTry; }
            };

            const fetchLiveBitcoinRate = async () => {
                try {
                    const data = await fetchJsonFromAny([
                        'https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT',
                        'https://api1.binance.com/api/v3/ticker/price?symbol=BTCUSDT',
                        'https://data-api.binance.vision/api/v3/ticker/price?symbol=BTCUSDT',
                    ]);
                    const btcUsd = Number(data?.price);
                    if (Number.isFinite(btcUsd)) { previousValues.btcusd = liveValues.btcusd; liveValues.btcusd = btcUsd; return; }
                } catch (error) {
                    const data = await fetchJsonFromAny(['https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd']);
                    const btcUsd = Number(data?.bitcoin?.usd);
                    if (Number.isFinite(btcUsd)) { previousValues.btcusd = liveValues.btcusd; liveValues.btcusd = btcUsd; }
                }
            };

            const fetchLiveGoldRate = async () => {
                const [currencyResponse, goldResponse] = await Promise.all([
                    fetch('https://open.er-api.com/v6/latest/USD', { cache: 'no-store' }),
                    fetch('https://api.gold-api.com/price/XAU', { cache: 'no-store' }),
                ]);
                if (!currencyResponse.ok || !goldResponse.ok) throw new Error('Altın verisi alınamadı.');
                const currencyData = await currencyResponse.json();
                const goldData = await goldResponse.json();
                const usdTry = Number(currencyData?.rates?.TRY);
                const goldUsdPerOunce = Number(goldData?.price ?? goldData?.ask ?? goldData?.bid);
                const gramGoldTry = Number.isFinite(goldUsdPerOunce) && Number.isFinite(usdTry)
                    ? (goldUsdPerOunce * usdTry) / 31.1034768
                    : null;
                if (Number.isFinite(gramGoldTry)) { previousValues.goldtry = liveValues.goldtry; liveValues.goldtry = gramGoldTry; }
            };

            const fetchHistoricalFiat = async (symbol, range) => {
                const days = getDaysForRange(range);
                const end = new Date();
                const start = new Date(Date.now() - (days * ONE_DAY));
                const url = `https://api.frankfurter.app/${formatDate(start)}..${formatDate(end)}?from=USD&to=TRY,EUR`;
                const response = await fetch(url, { cache: 'no-store' });
                if (!response.ok) throw new Error('Geçmiş döviz verisi alınamadı.');
                const data = await response.json();
                const rates = data?.rates || {};
                let series = Object.entries(rates).map(([date, rate]) => {
                    const tryRate = Number(rate?.TRY);
                    const eurRate = Number(rate?.EUR);
                    let value = null;
                    if (symbol === 'usdtry') value = tryRate;
                    if (symbol === 'eurtry') value = eurRate > 0 ? tryRate / eurRate : null;
                    return { t: new Date(date + 'T12:00:00').getTime(), v: Number(value) };
                }).filter(item => Number.isFinite(item.v) && Number.isFinite(item.t));
                if (range === '1d' && series.length <= 2) {
                    const live = liveValues[symbol];
                    if (Number.isFinite(live)) series = makeTemporarySeries(live, range, symbol);
                }
                return series;
            };

            const fetchHistoricalBitcoin = async (range) => {
                const interval = range === '1m' ? '1d' : (range === '1w' ? '4h' : '1h');
                const limit = range === '1m' ? 30 : (range === '1w' ? 42 : 24);
                try {
                    const data = await fetchJsonFromAny([
                        `https://api.binance.com/api/v3/klines?symbol=BTCUSDT&interval=${interval}&limit=${limit}`,
                        `https://api1.binance.com/api/v3/klines?symbol=BTCUSDT&interval=${interval}&limit=${limit}`,
                        `https://data-api.binance.vision/api/v3/klines?symbol=BTCUSDT&interval=${interval}&limit=${limit}`,
                    ]);
                    return (Array.isArray(data) ? data : [])
                        .map(item => ({ t: Number(item?.[0]), v: Number(item?.[4]) }))
                        .filter(item => Number.isFinite(item.t) && Number.isFinite(item.v));
                } catch (error) {
                    const days = range === '1m' ? 30 : (range === '1w' ? 7 : 1);
                    const data = await fetchJsonFromAny([`https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=usd&days=${days}`]);
                    return (data?.prices || [])
                        .map(item => ({ t: Number(item?.[0]), v: Number(item?.[1]) }))
                        .filter(item => Number.isFinite(item.t) && Number.isFinite(item.v));
                }
            };

            const fetchHistoricalGold = async (range) => {
                await fetchLiveGoldRate();
                const value = liveValues.goldtry;
                if (!Number.isFinite(value)) return [];
                return makeTemporarySeries(value, range, 'goldtry');
            };

            const fetchHistoricalSeries = async () => {
                isLoadingHistory = true;
                activeHistory = [];
                drawChart();
                try {
                    if (activeSymbol === 'usdtry' || activeSymbol === 'eurtry') activeHistory = await fetchHistoricalFiat(activeSymbol, activeRange);
                    else if (activeSymbol === 'btcusd') activeHistory = await fetchHistoricalBitcoin(activeRange);
                    else activeHistory = await fetchHistoricalGold(activeRange);
                } catch (error) {
                    const value = liveValues[activeSymbol];
                    activeHistory = makeTemporarySeries(value, activeRange, activeSymbol);
                }
                isLoadingHistory = false;
                updateMainBlock();
            };

            const refreshLiveData = async () => {
                await Promise.allSettled([fetchLiveCurrencyRates(), fetchLiveBitcoinRate(), fetchLiveGoldRate()]);
                updateTabs();
                updateConverterLabels();
                updateMainBlock();
            };

            const setActiveRangeButton = (range) => {
                page.querySelectorAll('[data-range-button]').forEach(item => {
                    item.classList.toggle('is-active', item.dataset.rangeButton === range);
                });
            };

            converterInputEl?.addEventListener('input', updateConverter);

            swapButtonEl?.addEventListener('click', () => {
                converterSwapped = !converterSwapped;
                updateConverterLabels();
                updateConverter();
            });

            page.querySelectorAll('[data-range-button]').forEach(button => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    const nextRange = button.dataset.rangeButton || '1d';
                    if (isLoadingHistory) return;
                    activeRange = nextRange;
                    setActiveRangeButton(activeRange);
                    await fetchHistoricalSeries();
                });
            });

            const boot = async () => {
                setActiveRangeButton(activeRange);
                await refreshLiveData();
                await fetchHistoricalSeries();
                setInterval(refreshLiveData, LIVE_REFRESH_MS);
            };

            boot();
        })();
    </script>
@endsection
