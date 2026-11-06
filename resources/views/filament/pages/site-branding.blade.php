<x-filament-panels::page>
    <style>
        .branding-page {
            display: grid;
            gap: 1.5rem;
            max-width: 68rem;
            margin-inline: auto;
        }

        .branding-intro {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgb(226 232 240 / 75%);
            border-radius: 1rem;
            background: linear-gradient(115deg, rgb(239 246 255 / 90%), rgb(255 255 255 / 96%) 58%);
            color: #475569;
            font-size: .925rem;
            line-height: 1.65;
        }

        .branding-intro-mark {
            display: grid;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid #dbeafe;
            border-radius: .8rem;
            background: #fff;
            color: #2563eb;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .branding-intro p,
        .branding-asset-card p {
            margin: 0;
        }

        .branding-form {
            display: grid;
            gap: 1rem;
        }

        .branding-save-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid rgb(226 232 240 / 80%);
            border-radius: 1rem;
            background: rgb(255 255 255 / 90%);
        }

        .branding-save-note {
            color: #64748b;
            font-size: .85rem;
        }

        .branding-asset-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .branding-asset-card {
            display: grid;
            align-content: start;
            gap: 1rem;
            padding: 1.25rem;
            border: 1px solid rgb(226 232 240 / 80%);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgb(15 23 42 / 3%);
        }

        .branding-asset-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
        }

        .branding-asset-heading h2 {
            margin: 0;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 650;
            letter-spacing: -.01em;
        }

        .branding-asset-card p {
            margin-top: .3rem;
            color: #64748b;
            font-size: .82rem;
            line-height: 1.55;
        }

        .branding-pill {
            flex: 0 0 auto;
            padding: .25rem .55rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: .7rem;
            font-weight: 600;
        }

        .branding-preview {
            display: grid;
            min-height: 7rem;
            place-items: center;
            overflow: hidden;
            padding: 1rem;
            border: 1px dashed #cbd5e1;
            border-radius: .8rem;
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 0.65px, transparent 0.65px);
            background-size: 12px 12px;
        }

        .branding-preview--favicon {
            min-height: 7rem;
        }

        .branding-logo-preview {
            display: block;
            width: auto;
            max-width: min(100%, 18rem);
            height: auto;
            max-height: 4rem;
            object-fit: contain;
        }

        .branding-favicon-preview {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: .8rem;
            object-fit: contain;
            box-shadow: 0 8px 18px rgb(15 23 42 / 12%);
        }

        .branding-upload-form {
            display: grid;
            gap: .75rem;
        }

        .branding-upload-label {
            color: #334155;
            font-size: .8rem;
            font-weight: 600;
        }

        .branding-file-input {
            display: block;
            width: 100%;
            padding: .45rem;
            border: 1px solid #e2e8f0;
            border-radius: .7rem;
            background: #fff;
            color: #475569;
            font-size: .8rem;
        }

        .branding-file-input::file-selector-button {
            margin-right: .75rem;
            padding: .5rem .75rem;
            border: 0;
            border-radius: .5rem;
            background: #eff6ff;
            color: #1d4ed8;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .branding-upload-hint {
            color: #94a3b8;
            font-size: .75rem;
        }

        .branding-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.25rem;
        }

        .branding-status {
            padding: .85rem 1rem;
            border: 1px solid #bbf7d0;
            border-radius: .75rem;
            background: #f0fdf4;
            color: #166534;
            font-size: .875rem;
        }

        @media (max-width: 48rem) {
            .branding-asset-grid {
                grid-template-columns: 1fr;
            }

            .branding-save-row {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>

    <div class="branding-page">
        <div class="branding-intro">
            <span class="branding-intro-mark" aria-hidden="true">R</span>
            <p>Marka adını, site logosunu ve sekme simgesini buradan yönetin. Değişiklikler kaydedildiğinde site genelinde uygulanır.</p>
        </div>

        @if (session('status'))
            <div class="branding-status" role="status">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="branding-form">
            {{ $this->form }}

            <section class="social-network-settings" aria-labelledby="social-network-title">
                <h2 id="social-network-title">Social networks</h2>
                <div class="social-network-options">
                    @foreach (['google' => 'Google', 'yandex' => 'Yandex'] as $provider => $label)
                        <button
                            type="button"
                            class="social-network-option {{ in_array($provider, $data['social_networks'] ?? [], true) ? 'is-selected' : '' }}"
                            wire:click="requestSocialNetworkToggle('{{ $provider }}')"
                            wire:loading.attr="disabled"
                            wire:target="requestSocialNetworkToggle"
                            aria-label="{{ $label }} ayarını değiştir"
                        >
                            <x-repost-icon :name="$provider" size="20" />
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            @if ($showSocialNetworkModal)
                <div class="social-network-modal-backdrop" role="presentation">
                    <section
                        class="social-network-modal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="social-network-modal-title"
                    >
                        <header class="social-network-modal__header">
                            <h2 id="social-network-modal-title">Sosyal ağ ayarını onayla</h2>
                            <button type="button" class="social-network-modal__close" wire:click="cancelSocialNetworkToggle" aria-label="Kapat">×</button>
                        </header>
                        <div class="social-network-modal__body">
                            <span class="social-network-modal__icon">
                                <x-repost-icon :name="$pendingSocialProvider === 'yandex' ? 'yandex' : 'google'" size="24" />
                            </span>
                            <h3>{{ ucfirst($pendingSocialProvider ?? '') }} {{ $pendingSocialEnabled ? 'girişini etkinleştirmek' : 'girişini devre dışı bırakmak' }} istiyor musunuz?</h3>
                            <p>
                                {{ $pendingSocialEnabled
                                    ? 'Bu sağlayıcıyı etkinleştirmek için onay verin. Değişiklikleri uygulamak için ayrıca Ayarları kaydet butonuna basmanız gerekir.'
                                    : 'Bu sağlayıcıyı devre dışı bırakmak için onay verin. Değişiklikleri uygulamak için ayrıca Ayarları kaydet butonuna basmanız gerekir.' }}
                            </p>
                        </div>
                        <footer class="social-network-modal__actions">
                            <button type="button" class="social-network-modal__button social-network-modal__button--cancel" wire:click="cancelSocialNetworkToggle">İptal</button>
                            <button type="button" class="social-network-modal__button social-network-modal__button--confirm" wire:click="confirmSocialNetworkToggle" wire:loading.attr="disabled" wire:target="confirmSocialNetworkToggle">Onayla</button>
                        </footer>
                    </section>
                </div>
            @endif

            <div class="branding-save-row">
                <span class="branding-save-note">Marka ve sosyal bağlantı değişikliklerini kaydedin.</span>
                <x-filament::button type="submit">
                    Ayarları kaydet
                </x-filament::button>
            </div>
        </form>

        <div class="branding-asset-grid">
            <section class="branding-asset-card" aria-labelledby="brand-logo-heading">
                <div class="branding-asset-heading">
                    <div>
                        <h2 id="brand-logo-heading">Site logosu</h2>
                        <p>Başlıkta markanızın yanında kullanılır.</p>
                    </div>
                    <span class="branding-pill">LOGO</span>
                </div>

                <div class="branding-preview">
                    <img
                        src="{{ $currentLogoUrl ?? asset('assets/img/meta/logo.svg') }}"
                        alt="Site logosu önizlemesi"
                        class="branding-logo-preview"
                    >
                </div>

                <x-filament::button
                    type="button"
                    color="gray"
                    x-on:click="$dispatch('open-modal', { id: 'brand-logo-upload-modal' })"
                >
                    Logoyu düzenle
                </x-filament::button>

                <x-filament::modal
                    id="brand-logo-upload-modal"
                    heading="Site logosunu düzenle"
                    description="SVG, PNG, JPG veya WebP dosyası seçin. En fazla 2 MB."
                    width="md"
                    x-init="{{ $errors->has('logo') ? '$nextTick(() => open())' : 'void 0' }}"
                >
                    <form action="{{ route('admin.site-branding.logo.update') }}" method="POST" enctype="multipart/form-data" class="branding-upload-form">
                        @csrf
                        <label class="branding-upload-label" for="brand-logo-upload">Yeni logo dosyası</label>
                        <input
                            id="brand-logo-upload"
                            class="branding-file-input"
                            type="file"
                            name="logo"
                            accept=".svg,.png,.jpg,.jpeg,.webp,image/svg+xml,image/png,image/jpeg,image/webp"
                            required
                        >
                        @error('logo')
                            <p class="text-sm text-danger-600" role="alert">{{ $message }}</p>
                        @enderror
                        <div class="branding-modal-actions">
                            <x-filament::button
                                type="button"
                                color="gray"
                                x-on:click="$dispatch('close-modal', { id: 'brand-logo-upload-modal' })"
                            >
                                İptal
                            </x-filament::button>
                            <x-filament::button type="submit">
                                Logoyu yükle
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::modal>
            </section>

            <section class="branding-asset-card" aria-labelledby="brand-favicon-heading">
                <div class="branding-asset-heading">
                    <div>
                        <h2 id="brand-favicon-heading">Sekme simgesi</h2>
                        <p>Tarayıcı sekmesinde ve yer imlerinde görünür.</p>
                    </div>
                    <span class="branding-pill">FAVICON</span>
                </div>

                <div class="branding-preview branding-preview--favicon">
                    <img
                        src="{{ $currentFaviconUrl ?? asset('assets/img/meta/logo.svg') }}"
                        alt="Sekme simgesi önizlemesi"
                        class="branding-favicon-preview"
                    >
                </div>

                <form action="{{ route('admin.site-branding.favicon.update') }}" method="POST" enctype="multipart/form-data" class="branding-upload-form">
                    @csrf
                    <label class="branding-upload-label" for="brand-favicon-upload">Yeni favicon dosyası</label>
                    <input
                        id="brand-favicon-upload"
                        class="branding-file-input"
                        type="file"
                        name="favicon"
                        accept=".svg,.png,.ico,.webp,image/svg+xml,image/png,image/x-icon,image/webp"
                        required
                    >
                    <span class="branding-upload-hint">SVG, PNG, ICO veya WebP · En fazla 2 MB · Kare görsel önerilir</span>
                    @error('favicon')
                        <p class="text-sm text-danger-600" role="alert">{{ $message }}</p>
                    @enderror
                    <x-filament::button type="submit" color="gray">
                        Simgeyi yükle
                    </x-filament::button>
                </form>
            </section>
        </div>
    </div>
</x-filament-panels::page>
