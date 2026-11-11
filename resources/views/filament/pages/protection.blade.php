<x-filament-panels::page>
    <div class="mb-6 rounded-xl border border-primary-200 bg-primary-50/70 p-5 dark:border-primary-900 dark:bg-primary-950/30">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 rounded-lg bg-white p-2 text-primary-600 shadow-sm dark:bg-gray-900 dark:text-primary-400">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-5 w-5" />
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">OgraFi içerik koruması</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">
                    Yorumları ve postları spam içeriklerine karşı koruyun. Ayarları sekmelerden yönetin, şüpheli içerikleri inceleme listelerinden kontrol edin.
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end border-t border-gray-200 pt-5 dark:border-gray-800">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Koruma ayarlarını kaydet
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
