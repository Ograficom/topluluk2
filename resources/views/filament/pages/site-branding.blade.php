<x-filament-panels::page>
    @if (session('status'))
        <x-filament::section>
            {{ session('status') }}
        </x-filament::section>
    @endif

    <div class="space-y-6">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit">
                Marka ve alt bilgi ayarlarını kaydet
            </x-filament::button>
        </form>

        <x-filament::section heading="Logo dosyası">
            @if ($currentLogoUrl)
                <img src="{{ $currentLogoUrl }}" alt="Mevcut logo" style="max-height: 80px; max-width: 240px; object-fit: contain;">
            @endif

            <form action="{{ route('admin.site-branding.logo.update') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="brand-logo-upload" class="fi-fo-field-wrp-label">
                        Logo (SVG, PNG, JPG veya WebP; en fazla 2 MB)
                    </label>
                    <input
                        id="brand-logo-upload"
                        type="file"
                        name="logo"
                        accept=".svg,.png,.jpg,.jpeg,.webp,image/svg+xml,image/png,image/jpeg,image/webp"
                        required
                    >
                    @error('logo')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-filament::button type="submit">
                    Logoyu yükle
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
