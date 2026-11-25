<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 dark:border-primary-900 dark:bg-primary-950 dark:text-primary-200">
            Bu ekrandaki ikonlar PWA manifestinde, kısa yollarda ve yeni PWABuilder Android paketlerinde kullanılır. Tarayıcıdan kurulan PWA ikonunun yenilenmesi tarayıcıya bağlıdır. Mevcut Android TWA uygulamasının ana ekran ikonu APK/AAB içine gömülüdür; değiştirmek için yeni paket sürümü yayımlayın.
        </div>
        <x-filament::section>
            <x-slot name="heading">Yayındaki uygulama ikonları</x-slot>
            <div class="flex flex-wrap gap-6">
                @foreach (\App\Http\Controllers\PwaIconController::manifestIcons(\App\Models\SiteSetting::current()) as $icon)
                    <div class="grid justify-items-center gap-2">
                        <img src="{{ $icon['src'] }}" alt="{{ $icon['sizes'] }}" width="96" height="96" class="rounded-xl border border-gray-200 object-contain dark:border-gray-700">
                        <span class="text-sm">{{ $icon['sizes'] }}{{ $icon['purpose'] === 'maskable' ? ' · Android maskable' : '' }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
        <form wire:submit="save" class="grid gap-5">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit">PWA ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
