@once
    <style>
        .live-market-widget {
            width: 100%;
            overflow: hidden;
            border-radius: 0;
            background: transparent;
            font-family: Poppins, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .live-market-widget__track {
            display: flex;
            align-items: center;
            gap: 16px;
            white-space: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 8px 0;
        }

        .live-market-widget__track::-webkit-scrollbar {
            display: none;
        }

        .live-market-widget__item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-width: max-content;
            font-size: 12px;
            line-height: 1;
            color: #5f6368;
            font-weight: 400;
        }

        .live-market-widget__label {
            color: #5f6368;
            font-weight: 400;
        }

        .live-market-widget__value {
            color: #4b5563;
            font-weight: 400;
        }

        .live-market-widget__arrow {
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 1px;
        }

        .live-market-widget__arrow.is-down {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 6px solid #ef4444;
        }

        .live-market-widget__arrow.is-up {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-bottom: 6px solid #22c55e;
        }

        .live-market-widget__arrow.is-flat {
            width: 6px;
            height: 2px;
            border-radius: 999px;
            background: #9ca3af;
        }

        .dark .live-market-widget__item,
        .dark .live-market-widget__label {
            color: #d1d5db;
        }

        .dark .live-market-widget__value {
            color: #e5e7eb;
        }

        @media (max-width: 640px) {
            .live-market-widget__track {
                gap: 12px;
                padding: 7px 0;
            }

            .live-market-widget__item {
                font-size: 11px;
            }
        }
    </style>
@endonce

<div
    class="live-market-widget"
    data-live-market-widget
    aria-label="Canlı piyasa verileri"
>
    <div class="live-market-widget__track">
        <div class="live-market-widget__item" data-symbol="usdtry">
            <span class="live-market-widget__label">Amerikan Doları</span>
            <span class="live-market-widget__value" data-value>Yükleniyor</span>
            <span class="live-market-widget__arrow is-flat" data-arrow></span>
        </div>

        <div class="live-market-widget__item" data-symbol="eurtry">
            <span class="live-market-widget__label">EUR</span>
            <span class="live-market-widget__value" data-value>Yükleniyor</span>
            <span class="live-market-widget__arrow is-flat" data-arrow></span>
        </div>

        <div class="live-market-widget__item" data-symbol="btcusd">
            <span class="live-market-widget__label">BTC /USD</span>
            <span class="live-market-widget__value" data-value>Yükleniyor</span>
            <span class="live-market-widget__arrow is-flat" data-arrow></span>
        </div>

        <div class="live-market-widget__item" data-symbol="tonusd">
            <span class="live-market-widget__label">TON /USD</span>
            <span class="live-market-widget__value" data-value>Yükleniyor</span>
            <span class="live-market-widget__arrow is-flat" data-arrow></span>
        </div>
    </div>
</div>

@once
    <script>
        (() => {
            const widgets = document.querySelectorAll('[data-live-market-widget]');

            if (!widgets.length) {
                return;
            }

            const previousValues = {
                usdtry: null,
                eurtry: null,
                btcusd: null,
                tonusd: null,
            };

            const formatTry = (value) => {
                if (!Number.isFinite(value)) {
                    return '-';
                }

                return value.toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            };

            const formatUsd = (value) => {
                if (!Number.isFinite(value)) {
                    return '-';
                }

                if (value >= 1000) {
                    return (value / 1000).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }) + 'K';
                }

                return value.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            };

            const setArrow = (arrow, currentValue, previousValue) => {
                arrow.classList.remove('is-up', 'is-down', 'is-flat');

                if (!Number.isFinite(previousValue) || currentValue === previousValue) {
                    arrow.classList.add('is-flat');
                    return;
                }

                if (currentValue > previousValue) {
                    arrow.classList.add('is-up');
                    return;
                }

                arrow.classList.add('is-down');
            };

            const updateItem = (symbol, value, formatter) => {
                widgets.forEach((widget) => {
                    const item = widget.querySelector(`[data-symbol="${symbol}"]`);

                    if (!item) {
                        return;
                    }

                    const valueElement = item.querySelector('[data-value]');
                    const arrowElement = item.querySelector('[data-arrow]');

                    if (!valueElement || !arrowElement) {
                        return;
                    }

                    const oldValue = previousValues[symbol];

                    valueElement.textContent = formatter(value);
                    setArrow(arrowElement, value, oldValue);

                    previousValues[symbol] = value;
                });
            };

            const fetchCurrencyRates = async () => {
                const response = await fetch('https://open.er-api.com/v6/latest/USD', {
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error('Döviz verisi alınamadı.');
                }

                const data = await response.json();

                const usdTry = Number(data?.rates?.TRY);
                const eurRate = Number(data?.rates?.EUR);
                const tryRate = Number(data?.rates?.TRY);

                const eurTry = eurRate > 0 ? tryRate / eurRate : null;

                if (Number.isFinite(usdTry)) {
                    updateItem('usdtry', usdTry, formatTry);
                }

                if (Number.isFinite(eurTry)) {
                    updateItem('eurtry', eurTry, formatTry);
                }
            };

            const fetchCryptoRates = async () => {
                const response = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,the-open-network&vs_currencies=usd', {
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error('Kripto verisi alınamadı.');
                }

                const data = await response.json();

                const btcUsd = Number(data?.bitcoin?.usd);
                const tonUsd = Number(data?.['the-open-network']?.usd);

                if (Number.isFinite(btcUsd)) {
                    updateItem('btcusd', btcUsd, formatUsd);
                }

                if (Number.isFinite(tonUsd)) {
                    updateItem('tonusd', tonUsd, formatUsd);
                }
            };

            const loadMarketData = async () => {
                await Promise.allSettled([
                    fetchCurrencyRates(),
                    fetchCryptoRates(),
                ]);
            };

            loadMarketData();

            setInterval(loadMarketData, 60000);
        })();
    </script>
@endonce