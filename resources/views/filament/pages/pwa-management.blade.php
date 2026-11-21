<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 dark:border-primary-900 dark:bg-primary-950 dark:text-primary-200">
            Bu ekrandaki ayarlar PWA manifest dosyasına anında yansıtılır. İkonların doğru görünmesi için 192 × 192 ve 512 × 512 PNG yüklemeni öneririm. Değişikliklerden sonra cihazdaki eski OgraFi kısayolunu kaldırıp yeniden yüklemek gerekebilir.
        </div>
        <form wire:submit="save" class="grid gap-5">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit">PWA ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
