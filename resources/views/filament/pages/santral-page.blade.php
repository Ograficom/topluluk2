<x-filament-panels::page>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div>
            <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Santral sayfası düzenleyicisi</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">İçerikleri sekmelerden düzenleyin. Değişiklikler, kaydet butonuna bastığınızda canlı sayfaya uygulanır.</p>
        </div>
        <x-filament::button
            tag="a"
            :href="url('/santral')"
            target="_blank"
            color="gray"
            icon="heroicon-o-arrow-top-right-on-square"
        >
            Sayfayı önizle
        </x-filament::button>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="sticky bottom-3 z-10 flex justify-end rounded-xl border border-gray-200 bg-white/95 p-3 shadow-sm backdrop-blur dark:border-gray-700 dark:bg-gray-900/95">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Değişiklikleri kaydet
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
