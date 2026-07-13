<x-app-layout>
    @section('title', 'Reklam Ver')
    @section('custom_shell')
        <main class="ad-buy-page">
            <style>
                .ad-buy-page {
                    min-height: calc(100vh - var(--site-header-height, 64px));
                    padding: 28px 16px 48px;
                    background: #f4f4f5;
                }

                .ad-buy-shell {
                    width: min(100%, 1100px);
                    margin: 0 auto;
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) 360px;
                    gap: 24px;
                    align-items: start;
                }

                .ad-buy-card {
                    background: #ffffff;
                    border-radius: 10px;
                    padding: 22px;
                }

                .ad-buy-title {
                    margin: 0 0 18px;
                    color: #111827;
                    font-size: 24px;
                    line-height: 1.2;
                    font-weight: 700;
                }

                .ad-buy-grid {
                    display: grid;
                    gap: 16px;
                }

                .ad-buy-field {
                    display: grid;
                    gap: 8px;
                }

                .ad-buy-label {
                    color: #111827;
                    font-size: 14px;
                    font-weight: 600;
                }

                .ad-buy-input,
                .ad-buy-select {
                    width: 100%;
                    min-height: 46px;
                    border: 0;
                    border-radius: 10px;
                    background: #f4f4f5;
                    color: #111827;
                    padding: 0 14px;
                    font-size: 14px;
                }

                .ad-buy-file {
                    width: 100%;
                    border-radius: 10px;
                    background: #f4f4f5;
                    color: #111827;
                    padding: 12px;
                    font-size: 14px;
                }

                .ad-buy-help,
                .ad-buy-error {
                    margin: 0;
                    font-size: 12px;
                    line-height: 1.4;
                }

                .ad-buy-help {
                    color: #64748b;
                }

                .ad-buy-error {
                    color: #dc2626;
                }

                .ad-buy-summary {
                    display: grid;
                    gap: 10px;
                    margin-top: 18px;
                    padding-top: 18px;
                }

                .ad-buy-summary-row {
                    display: flex;
                    justify-content: space-between;
                    gap: 16px;
                    color: #111827;
                    font-size: 14px;
                }

                .ad-buy-price {
                    color: #059669;
                    font-size: 26px;
                    font-weight: 700;
                }

                .ad-buy-button {
                    width: 100%;
                    min-height: 48px;
                    border: 0;
                    border-radius: 10px;
                    background: #059669;
                    color: #ffffff;
                    font-size: 15px;
                    font-weight: 700;
                    cursor: pointer;
                }

                .ad-preview-box {
                    display: grid;
                    gap: 14px;
                }

                .ad-preview-area {
                    width: 100%;
                    background: #f4f4f5;
                    border-radius: 10px;
                    overflow: hidden;
                }

                .ad-preview-image {
                    display: none;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .ad-preview-empty {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 180px;
                    color: #64748b;
                    font-size: 14px;
                    text-align: center;
                    padding: 20px;
                }

                .ad-preview-meta {
                    display: grid;
                    gap: 6px;
                    color: #111827;
                    font-size: 13px;
                }

                @media (max-width: 900px) {
                    .ad-buy-shell {
                        grid-template-columns: 1fr;
                        max-width: 656px;
                    }
                }
            </style>

            <div class="ad-buy-shell">
                <section class="ad-buy-card">
                    <h1 class="ad-buy-title">Reklam Ver</h1>

                    <form method="POST" action="{{ route('advertise.store') }}" enctype="multipart/form-data" class="ad-buy-grid" id="adBuyForm">
                        @csrf

                        <div class="ad-buy-field">
                            <label class="ad-buy-label" for="placement">Reklam yeri</label>
                            <select class="ad-buy-select" id="placement" name="placement" required>
                                @foreach($placements as $key => $placement)
                                    <option
                                        value="{{ $key }}"
                                        data-width="{{ $placement['width'] }}"
                                        data-height="{{ $placement['height'] }}"
                                        data-description="{{ $placement['description'] }}"
                                        @selected(old('placement', 'sidebar_top') === $key)
                                    >
                                        {{ $placement['label'] }} - {{ $placement['width'] }}x{{ $placement['height'] }} px
                                    </option>
                                @endforeach
                            </select>
                            @error('placement')
                                <p class="ad-buy-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ad-buy-field">
                            <label class="ad-buy-label" for="duration_days">Süre</label>
                            <select class="ad-buy-select" id="duration_days" name="duration_days" required>
                                @foreach($durations as $days => $priceCents)
                                    <option
                                        value="{{ $days }}"
                                        data-price="{{ $priceCents }}"
                                        @selected((int) old('duration_days', 7) === (int) $days)
                                    >
                                        {{ $days }} gün
                                    </option>
                                @endforeach
                            </select>
                            @error('duration_days')
                                <p class="ad-buy-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ad-buy-field">
                            <label class="ad-buy-label" for="title">Reklam başlığı</label>
                            <input class="ad-buy-input" id="title" name="title" value="{{ old('title') }}" maxlength="80" placeholder="Kısa reklam adı">
                            @error('title')
                                <p class="ad-buy-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ad-buy-field">
                            <label class="ad-buy-label" for="target_url">Hedef bağlantı</label>
                            <input class="ad-buy-input" id="target_url" name="target_url" value="{{ old('target_url') }}" placeholder="https://">
                            @error('target_url')
                                <p class="ad-buy-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ad-buy-field">
                            <label class="ad-buy-label" for="image">Reklam görseli</label>
                            <input class="ad-buy-file" id="image" name="image" type="file" accept="image/*" required>
                            <p class="ad-buy-help">JPG, PNG, WEBP veya GIF. En fazla 4 MB.</p>
                            @error('image')
                                <p class="ad-buy-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ad-buy-summary">
                            <div class="ad-buy-summary-row">
                                <span>Seçilen boyut</span>
                                <strong id="selectedSize">304x380 px</strong>
                            </div>
                            <div class="ad-buy-summary-row">
                                <span>Toplam ödeme</span>
                                <strong class="ad-buy-price" id="selectedPrice">350,00 TRY</strong>
                            </div>
                        </div>

                        <button type="submit" class="ad-buy-button">Ödeme Sayfasına Geç</button>
                    </form>
                </section>

                <aside class="ad-buy-card ad-preview-box">
                    <h2 class="ad-buy-title">Canlı Ön İzleme</h2>
                    <div class="ad-preview-area" id="previewArea">
                        <img class="ad-preview-image" id="previewImage" alt="Reklam ön izlemesi">
                        <div class="ad-preview-empty" id="previewEmpty">Görsel yükleyince seçilen reklam alanında burada görünür.</div>
                    </div>
                    <div class="ad-preview-meta">
                        <strong id="previewPlacement">Sağ sidebar üst</strong>
                        <span id="previewDescription">Masaüstünde sağ kolonda yorumların üstünde görünür.</span>
                    </div>
                </aside>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const placement = document.getElementById('placement');
                    const duration = document.getElementById('duration_days');
                    const image = document.getElementById('image');
                    const selectedSize = document.getElementById('selectedSize');
                    const selectedPrice = document.getElementById('selectedPrice');
                    const previewArea = document.getElementById('previewArea');
                    const previewImage = document.getElementById('previewImage');
                    const previewEmpty = document.getElementById('previewEmpty');
                    const previewPlacement = document.getElementById('previewPlacement');
                    const previewDescription = document.getElementById('previewDescription');

                    const formatPrice = function (cents) {
                        return (Number(cents || 0) / 100).toLocaleString('tr-TR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ' TRY';
                    };

                    const sync = function () {
                        const selectedPlacement = placement.options[placement.selectedIndex];
                        const selectedDuration = duration.options[duration.selectedIndex];
                        const width = Number(selectedPlacement.dataset.width || 304);
                        const height = Number(selectedPlacement.dataset.height || 380);

                        selectedSize.textContent = width + 'x' + height + ' px';
                        selectedPrice.textContent = formatPrice(selectedDuration.dataset.price);
                        previewPlacement.textContent = selectedPlacement.textContent.split(' - ')[0].trim();
                        previewDescription.textContent = selectedPlacement.dataset.description || '';
                        previewArea.style.aspectRatio = width + ' / ' + height;
                    };

                    placement.addEventListener('change', sync);
                    duration.addEventListener('change', sync);

                    image.addEventListener('change', function () {
                        const file = image.files && image.files[0];

                        if (!file) {
                            previewImage.removeAttribute('src');
                            previewImage.style.display = 'none';
                            previewEmpty.style.display = 'flex';
                            return;
                        }

                        previewImage.src = URL.createObjectURL(file);
                        previewImage.style.display = 'block';
                        previewEmpty.style.display = 'none';
                    });

                    sync();
                });
            </script>
        </main>
    @endsection
</x-app-layout>
