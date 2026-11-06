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

                [x-cloak]{display:none!important}
        .social-network-settings{display:grid;gap:1rem;padding:1.25rem 1.5rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff}
        .social-network-settings h2{margin:0;color:#172033;font-size:1rem;font-weight:700}
        .social-network-options{display:flex;flex-wrap:wrap;gap:.75rem}
        .social-network-option{display:inline-flex;min-width:106px;min-height:42px;align-items:center;justify-content:center;gap:.55rem;padding:0 1rem;border:1px solid #e2e8f0;border-radius:.8rem;background:#f1f4f8;color:#253044;font-size:.875rem;font-weight:600;cursor:pointer}
        .social-network-option:hover{background:#e8edf4}
        .social-network-option.is-selected{border-color:#bfdbfe;background:#eff6ff;color:#1d4ed8}

        /* Repost kit.css modal component: same structure, dimensions and motion */
        .social-confirm-modal[data-modal]{--modal-content-width:450px;--modal-viewport-offset:40px;--modal-viewport-padding-x:16px;--modal-content-radius:30px;--modal-content-bg:#fff;--modal-content-shadow:0 18px 55px rgb(15 23 42 / 22%);--modal-header-padding:20px;--modal-body-padding:20px;--modal-footer-padding:16px 20px;--modal-transition-duration:240ms;position:fixed;z-index:99999;inset:0;display:flex;align-items:center;justify-content:center;padding:var(--modal-viewport-offset) var(--modal-viewport-padding-x);overflow:hidden;background-color:transparent;backdrop-filter:blur(0);opacity:0;visibility:hidden;pointer-events:none;transition:background-color var(--modal-transition-duration) ease,backdrop-filter var(--modal-transition-duration) ease,opacity var(--modal-transition-duration) ease,visibility var(--modal-transition-duration) ease}
        .social-confirm-modal[data-modal].is-open{background-color:rgb(15 23 42 / 48%);backdrop-filter:blur(4px);opacity:1;visibility:visible;pointer-events:auto}
        .social-confirm-modal[data-modal]>.modal-content{position:relative;display:flex;width:min(100%,var(--modal-content-width));max-height:calc(100dvh - (var(--modal-viewport-offset)*2));flex-direction:column;overflow:hidden;border-radius:var(--modal-content-radius);color:#252b35;background:var(--modal-content-bg);box-shadow:var(--modal-content-shadow);opacity:0;transform:translate3d(0,-10px,0) scale(1.08);transform-origin:center;transition:opacity var(--modal-transition-duration) ease,transform var(--modal-transition-duration) cubic-bezier(.2,.8,.2,1)}
        .social-confirm-modal[data-modal].is-open>.modal-content{opacity:1;transform:translate3d(0,0,0) scale(1)}
        .social-confirm-modal[data-modal] .modal-header,.social-confirm-modal[data-modal] .modal-footer{position:sticky;z-index:2;display:flex;flex:0 0 auto;align-items:center;gap:12px;background:#fff}
        .social-confirm-modal[data-modal] .modal-header{top:0;min-height:64px;padding:20px;padding-right:64px;border-bottom:1px solid #e5eaf0}
        .social-confirm-modal[data-modal] .modal-title{margin:0;color:#151b25;font-size:1.125rem;font-weight:700;line-height:1.25}
        .social-confirm-modal[data-modal] .modal-body{min-height:0;padding:20px;overflow-y:auto;color:#343b46;font-size:.95rem;line-height:1.6}
        .social-confirm-modal[data-modal] .modal-footer{bottom:0;justify-content:flex-end;padding:16px 20px;border-top:1px solid #e5eaf0}
        .social-confirm-modal[data-modal] .modal-close{position:absolute;z-index:3;top:15px;right:15px}
        @media(max-width:480px){.social-confirm-modal[data-modal]{--modal-viewport-offset:20px;--modal-viewport-padding-x:10px;--modal-header-padding:16px;--modal-body-padding:16px;--modal-footer-padding:14px 16px}.social-confirm-modal[data-modal] .modal-header{padding:16px;padding-right:58px}.social-confirm-modal[data-modal] .modal-body{padding:16px}.social-confirm-modal[data-modal] .modal-footer{padding:14px 16px}}
        @media(prefers-reduced-motion:reduce){.social-confirm-modal[data-modal],.social-confirm-modal[data-modal]>.modal-content{transition:none}}

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

            <section class="social-network-settings" aria-labelledby="social-network-title" x-data="{ socialModal: null }">
                <h2 id="social-network-title">Social networks</h2>
                <div class="social-network-options">
                    @foreach (['google' => 'Google', 'yandex' => 'Yandex'] as $provider => $label)
                        <button type="button"
                            class="social-network-option {{ in_array($provider, $data['social_networks'] ?? [], true) ? 'is-selected' : '' }}"
                            @click="socialModal = '{{ $provider }}'">
                            <x-repost-icon :name="$provider" size="20" />
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                @foreach (['google' => 'Google', 'yandex' => 'Yandex'] as $provider => $label)
                    <div class="modal social-confirm-modal"
                        data-modal="social-{{ $provider }}"
                        x-cloak
                        x-show="socialModal === '{{ $provider }}'"
                        :class="{ 'is-open': socialModal === '{{ $provider }}' }"
                        @keydown.escape.window="socialModal = null"
                        @click.self="socialModal = null"
                        role="dialog" aria-modal="true"
                        aria-labelledby="social-{{ $provider }}-title">
                        <div class="modal-content">
                            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close"
                                @click="socialModal = null" aria-label="Close">
                                <svg width="16" height="16" aria-hidden="true"><use href="#close"></use></svg>
                            </button>
                            <header class="modal-header">
                                <h2 class="modal-title" id="social-{{ $provider }}-title">{{ $label }} ayarını değiştirmek istiyor musunuz?</h2>
                            </header>
                            <div class="modal-body">
                                {{ in_array($provider, $data['social_networks'] ?? [], true)
                                    ? $label.' ile giriş özelliği kapatılacak.'
                                    : $label.' ile giriş özelliği etkinleştirilecek.' }}
                            </div>
                            <footer class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-md" @click="socialModal = null">İptal</button>
                                <button type="button" class="btn btn-primary btn-md"
                                    wire:click="confirmSocialNetworkToggle('{{ $provider }}')"
                                    @click="socialModal = null">Onayla</button>
                            </footer>
                        </div>
                    </div>
                @endforeach
            </section>

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
