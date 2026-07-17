<x-app-layout>
    @section('title', 'Reklam Ödeme')
    @section('custom_shell')
        <main class="ad-payment-page">
            <style>
                .ad-payment-page {
                    min-height: calc(100vh - var(--site-header-height, 64px));
                    padding: 28px 16px 48px;
                    background: #f4f4f5;
                }

                .ad-payment-shell {
                    width: min(100%, 980px);
                    margin: 0 auto;
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) 320px;
                    gap: 24px;
                    align-items: start;
                }

                .ad-payment-card {
                    background: #ffffff;
                    border-radius: 10px;
                    padding: 22px;
                }

                .ad-payment-title {
                    margin: 0 0 8px;
                    color: #111827;
                    font-size: 24px;
                    line-height: 1.2;
                    font-weight: 700;
                }

                .ad-payment-subtitle {
                    margin: 0 0 18px;
                    color: #64748b;
                    font-size: 13px;
                    line-height: 1.45;
                }

                .ad-payment-list,
                .ad-payment-methods {
                    display: grid;
                    gap: 12px;
                    margin: 0;
                }

                .ad-payment-methods {
                    margin-top: 18px;
                }

                .ad-payment-method {
                    display: grid;
                    grid-template-columns: 24px minmax(0, 1fr) auto;
                    gap: 12px;
                    align-items: center;
                    min-height: 62px;
                    padding: 14px;
                    border-radius: 10px;
                    background: #f8fafc;
                    color: #111827;
                    cursor: pointer;
                }

                .ad-payment-method input {
                    position: absolute;
                    opacity: 0;
                    pointer-events: none;
                }

                .ad-payment-toggle {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 18px;
                    height: 18px;
                    border-radius: 999px;
                    background: #ffffff;
                }

                .ad-payment-toggle::after {
                    content: "";
                    display: none;
                    width: 8px;
                    height: 8px;
                    border-radius: 999px;
                    background: #0e7c86;
                }

                .ad-payment-method:has(input:checked) .ad-payment-toggle {
                    background: #dbeafe;
                }

                .ad-payment-method:has(input:checked) .ad-payment-toggle::after {
                    display: block;
                }

                .ad-payment-method:has(input:checked) {
                    background: #eff6ff;
                }

                .ad-payment-method input {
                    margin: 0;
                    accent-color: #0e7c86;
                }

                .ad-payment-panel {
                    display: none;
                    margin-top: -6px;
                    padding: 16px;
                    border-radius: 10px;
                    background: #f8fafc;
                    color: #334155;
                    font-size: 13px;
                    line-height: 1.45;
                }

                .ad-payment-panel.is-open {
                    display: block;
                }

                .ad-payment-panel strong {
                    display: block;
                    margin-bottom: 4px;
                    color: #111827;
                    font-size: 13px;
                }

                .ad-payment-method-title {
                    display: block;
                    color: #111827;
                    font-size: 14px;
                    font-weight: 700;
                    line-height: 1.25;
                }

                .ad-payment-method-text {
                    display: block;
                    margin-top: 3px;
                    color: #64748b;
                    font-size: 12px;
                    line-height: 1.35;
                }

                .ad-payment-logos {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: flex-end;
                    gap: 6px;
                }

                .pay-logo {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 42px;
                    height: 24px;
                    border-radius: 5px;
                    background: #ffffff;
                    color: #111827;
                    font-size: 10px;
                    font-weight: 800;
                    letter-spacing: 0;
                    padding: 0 7px;
                    white-space: nowrap;
                }

                .pay-logo--visa {
                    color: #1634a4;
                }

                .pay-logo--mastercard {
                    color: #eb001b;
                }

                .pay-logo--amex {
                    color: #0f72b8;
                }

                .pay-logo--discover {
                    color: #f58220;
                }

                .pay-logo--troy {
                    color: #00a6d6;
                }

                .pay-logo--btc {
                    color: #f7931a;
                }

                .pay-logo--isbank {
                    color: #164194;
                    min-width: 76px;
                }

                .ad-payment-row {
                    display: flex;
                    justify-content: space-between;
                    gap: 16px;
                    color: #111827;
                    font-size: 14px;
                }

                .ad-payment-muted {
                    color: #64748b;
                }

                .ad-payment-price {
                    color: #059669;
                    font-size: 28px;
                    line-height: 1.2;
                    font-weight: 700;
                }

                .ad-payment-preview {
                    width: 100%;
                    background: #f4f4f5;
                    border-radius: 10px;
                    overflow: hidden;
                }

                .ad-payment-preview img {
                    display: block;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .ad-payment-button {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 100%;
                    min-height: 48px;
                    margin-top: 18px;
                    border: 0;
                    border-radius: 10px;
                    background: #0e7c86;
                    color: #ffffff;
                    text-decoration: none;
                    font-size: 15px;
                    font-weight: 700;
                    cursor: pointer;
                }

                .ad-payment-note {
                    margin: 18px 0 0;
                    color: #64748b;
                    font-size: 13px;
                    line-height: 1.45;
                }

                @media (max-width: 760px) {
                    .ad-payment-shell {
                        grid-template-columns: 1fr;
                        max-width: 656px;
                    }

                    .ad-payment-method {
                        grid-template-columns: 24px minmax(0, 1fr);
                    }

                    .ad-payment-logos {
                        grid-column: 2;
                        justify-content: flex-start;
                    }

                    .ad-payment-panel {
                        padding: 14px;
                    }
                }
            </style>

            <div class="ad-payment-shell">
                <section class="ad-payment-card">
                    <h1 class="ad-payment-title">Ödeme</h1>
                    <p class="ad-payment-subtitle">Tüm işlemler güvenli şekilde tamamlanır. Ödeme yöntemini seçip devam edebilirsin.</p>

                    <dl class="ad-payment-list">
                        <div class="ad-payment-row">
                            <dt class="ad-payment-muted">Reklam yeri</dt>
                            <dd>{{ $placement['label'] ?? $adOrder->placement }}</dd>
                        </div>
                        <div class="ad-payment-row">
                            <dt class="ad-payment-muted">Boyut</dt>
                            <dd>{{ $adOrder->width }}x{{ $adOrder->height }} px</dd>
                        </div>
                        <div class="ad-payment-row">
                            <dt class="ad-payment-muted">Süre</dt>
                            <dd>{{ $adOrder->duration_days }} gün</dd>
                        </div>
                        @if($adOrder->target_url)
                            <div class="ad-payment-row">
                                <dt class="ad-payment-muted">Hedef bağlantı</dt>
                                <dd>{{ $adOrder->target_url }}</dd>
                            </div>
                        @endif
                        <div class="ad-payment-row">
                            <dt class="ad-payment-muted">Durum</dt>
                            <dd>Ödeme bekliyor</dd>
                        </div>
                    </dl>

                    <div class="ad-payment-methods" role="radiogroup" aria-label="Ödeme yöntemi" data-payment-methods>
                        <label class="ad-payment-method" data-payment-option="card">
                            <input type="radio" name="payment_method" value="card" checked>
                            <span class="ad-payment-toggle" aria-hidden="true"></span>
                            <span>
                                <span class="ad-payment-method-title">Kredi kartı</span>
                                <span class="ad-payment-method-text">Visa, Mastercard, American Express, Discover ve Troy desteklenir.</span>
                            </span>
                            <span class="ad-payment-logos" aria-hidden="true">
                                <span class="pay-logo pay-logo--visa">VISA</span>
                                <span class="pay-logo pay-logo--mastercard">MC</span>
                                <span class="pay-logo pay-logo--amex">AMEX</span>
                                <span class="pay-logo pay-logo--discover">DISC</span>
                                <span class="pay-logo pay-logo--troy">TROY</span>
                            </span>
                        </label>
                        <div class="ad-payment-panel is-open" data-payment-panel="card">
                            <strong>Kredi kartı ile devam et</strong>
                            Kart ödeme altyapısı bağlandığında Visa, Mastercard, American Express, Discover veya Troy kart bilgileri burada alınacak.
                        </div>

                        <label class="ad-payment-method" data-payment-option="iban">
                            <input type="radio" name="payment_method" value="iban">
                            <span class="ad-payment-toggle" aria-hidden="true"></span>
                            <span>
                                <span class="ad-payment-method-title">IBAN ile öde</span>
                                <span class="ad-payment-method-text">İş Bankası hesabına havale/EFT ile ödeme yap.</span>
                            </span>
                            <span class="ad-payment-logos" aria-hidden="true">
                                <span class="pay-logo pay-logo--isbank">İş Bankası</span>
                            </span>
                        </label>
                        <div class="ad-payment-panel" data-payment-panel="iban">
                            <strong>IBAN ödeme bilgileri</strong>
                            İş Bankası IBAN bilgisi ve açıklama kodu ödeme entegrasyonu tamamlandığında bu alanda gösterilecek.
                        </div>

                        <label class="ad-payment-method" data-payment-option="crypto">
                            <input type="radio" name="payment_method" value="crypto">
                            <span class="ad-payment-toggle" aria-hidden="true"></span>
                            <span>
                                <span class="ad-payment-method-title">Kripto ile öde</span>
                                <span class="ad-payment-method-text">BTC ile ödeme için ödeme bilgileri sipariş onayında paylaşılır.</span>
                            </span>
                            <span class="ad-payment-logos" aria-hidden="true">
                                <span class="pay-logo pay-logo--btc">BTC</span>
                            </span>
                        </label>
                        <div class="ad-payment-panel" data-payment-panel="crypto">
                            <strong>BTC ile ödeme</strong>
                            BTC cüzdan adresi ve aktarılacak tutar ödeme entegrasyonu tamamlandığında bu alanda gösterilecek.
                        </div>
                    </div>

                    <p class="ad-payment-note">
                        Ödeme altyapısı bağlandığında seçilen yönteme göre kart, IBAN veya kripto ödeme adımı burada tamamlanacak. Şu an reklam siparişi ödeme bekliyor olarak kaydedildi.
                    </p>

                    <button type="button" class="ad-payment-button" data-payment-continue>İşleme Devam Et</button>
                </section>

                <aside class="ad-payment-card">
                    <h2 class="ad-payment-title">Reklam Ön İzleme</h2>
                    <div class="ad-payment-preview" style="aspect-ratio: {{ $adOrder->width }} / {{ $adOrder->height ?: 1 }};">
                        @if($adOrder->image_url)
                            <img src="{{ $adOrder->image_url }}" alt="{{ $adOrder->title ?: 'Reklam görseli' }}">
                        @endif
                    </div>
                    <p class="ad-payment-note">Toplam tutar</p>
                    <div class="ad-payment-price">{{ $adOrder->formatted_price }}</div>
                </aside>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const methods = document.querySelector('[data-payment-methods]');
                    const continueButton = document.querySelector('[data-payment-continue]');

                    if (!methods) {
                        return;
                    }

                    const syncPanels = function () {
                        const checked = methods.querySelector('input[name="payment_method"]:checked');
                        const selected = checked ? checked.value : 'card';

                        methods.querySelectorAll('[data-payment-panel]').forEach(function (panel) {
                            panel.classList.toggle('is-open', panel.dataset.paymentPanel === selected);
                        });
                    };

                    methods.querySelectorAll('input[name="payment_method"]').forEach(function (input) {
                        input.addEventListener('change', syncPanels);
                    });

                    continueButton?.addEventListener('click', function () {
                        syncPanels();
                        methods.querySelector('.ad-payment-panel.is-open')?.scrollIntoView({
                            block: 'nearest'
                        });
                    });

                    syncPanels();
                });
            </script>
        </main>
    @endsection
</x-app-layout>
