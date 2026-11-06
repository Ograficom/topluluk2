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

        .social-network-settings{display:grid;gap:1rem;padding:1.25rem 1.5rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff}
        .social-network-settings h2{margin:0;color:#172033;font-size:1rem;font-weight:700}
        .social-network-options{display:flex;flex-wrap:wrap;gap:.75rem}
        .social-network-option{display:inline-flex;min-width:106px;min-height:42px;align-items:center;justify-content:center;gap:.55rem;padding:0 1rem;border:1px solid #e2e8f0;border-radius:.8rem;background:#f1f4f8;color:#253044;font-size:.875rem;font-weight:600;cursor:pointer;transition:background .15s ease,border-color .15s ease}
        .social-network-option:hover{background:#e8edf4}
        .social-network-option.is-selected{border-color:#bfdbfe;background:#eff6ff;color:#1d4ed8}
        .social-network-option:disabled{opacity:.6;cursor:wait}
        .social-network-modal-backdrop{position:fixed;z-index:9999;inset:0;display:grid;place-items:center;padding:1rem;background:rgb(15 23 42 / 48%);backdrop-filter:blur(4px)}
        .social-network-modal{width:min(100%,440px);overflow:hidden;border:1px solid rgb(226 232 240 / 90%);border-radius:1.5rem;background:#fff;box-shadow:0 24px 70px rgb(15 23 42 / 22%);animation:social-network-modal-in .2s ease-out both}
        .social-network-modal__header{display:flex;min-height:62px;align-items:center;justify-content:space-between;gap:1rem;padding:.9rem 1.25rem;border-bottom:1px solid #e8edf3}
        .social-network-modal__header h2{margin:0;color:#111827;font-size:1rem;font-weight:700}
        .social-network-modal__close{display:grid;width:36px;height:36px;flex:0 0 36px;place-items:center;border:0;border-radius:50%;background:#eef1f5;color:#334155;font-size:1.35rem;cursor:pointer}
        .social-network-modal__body{display:grid;justify-items:center;gap:.65rem;padding:1.6rem 1.5rem 1.35rem;text-align:center}
        .social-network-modal__icon{display:grid;width:52px;height:52px;place-items:center;border:1px solid #dbeafe;border-radius:50%;background:#eff6ff;color:#2563eb}
        .social-network-modal__body h3{margin:.25rem 0 0;color:#202938;font-size:1.15rem;font-weight:700;line-height:1.4}
        .social-network-modal__body p{max-width:340px;margin:0;color:#64748b;font-size:.875rem;line-height:1.6}
        .social-network-modal__actions{display:flex;justify-content:flex-end;gap:.65rem;padding:1rem 1.25rem 1.2rem;border-top:1px solid #e8edf3}
        .social-network-modal__button{min-width:100px;min-height:42px;padding:0 1rem;border:0;border-radius:.75rem;font-size:.875rem;font-weight:650;cursor:pointer}
        .social-network-modal__button--cancel{background:#eef1f5;color:#334155}
        .social-network-modal__button--confirm{background:#1769ff;color:#fff}
        .social-network-modal__button:disabled{opacity:.6;cursor:wait}
        @keyframes social-network-modal-in{from{opacity:0;transform:translateY(8px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
        @media(max-width:640px){.social-network-modal-backdrop{align-items:end;padding:0}.social-network-modal{width:100%;border-radius:1.35rem 1.35rem 0 0;padding-bottom:env(safe-area-inset-bottom,0px);animation-name:social-network-modal-sheet-in}@keyframes social-network-modal-sheet-in{from{opacity:.7;transform:translateY(100%)}to{opacity:1;transform:translateY(0)}}}

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
                        <button
                            type="button"
                            class="social-network-option {{ in_array($provider, $data['social_networks'] ?? [], true) ? 'is-selected' : '' }}"
                            @click="socialModal = '{{ $provider }}'"
                        >
                            <x-repost-icon :name="$provider" size="20" />
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                @foreach (['google' => 'Google', 'yandex' => 'Yandex'] as $provider => $label)
                    <div
                        class="social-confirm-modal"
                        x-cloak
                        x-show="socialModal === '{{ $provider }}'"
                        x-transition.opacity
                        @keydown.escape.window="socialModal = null"
                        @click.self="socialModal = null"
                        role="presentation"
                    >
                        <section class="social-confirm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="social-confirm-title-{{ $provider }}">
                            <button type="button" class="social-confirm-modal__close" @click="socialModal = null" aria-label="Kapat">×</button>
                            <header class="social-confirm-modal__header">
                                <h3 id="social-confirm-title-{{ $provider }}">{{ $label }} ayarını değiştirmek istiyor musunuz?</h3>
                            </header>
                            <div class="social-confirm-modal__body">
                                <p>
                                    {{ in_array($provider, $data['social_networks'] ?? [], true)
                                        ? $label.' ile giriş özelliği kapatılacak.'
                                        : $label.' ile giriş özelliği etkinleştirilecek.' }}
                                </p>
                            </div>
                            <footer class="social-confirm-modal__footer">
                                <button type="button" class="btn btn-secondary" @click="socialModal = null">İptal</button>
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    wire:click="confirmSocialNetworkToggle('{{ $provider }}')"
                                    @click="socialModal = null"
                                >Onayla</button>
                            </footer>
                        </section>
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
