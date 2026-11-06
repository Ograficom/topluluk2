<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Sitemap içindeki URL</div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ number_format($this->urlCount) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Son oluşturulma</div>
                <div class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ $this->lastGeneratedAt ?? 'Henüz oluşturulmadı' }}</div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Ayarları kaydet
                </x-filament::button>
                <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-path" wire:click="rebuild" wire:loading.attr="disabled" wire:target="rebuild">
                    Şimdi yeniden oluştur
                </x-filament::button>
                <a href="{{ $this->sitemapUrl() }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-medium text-primary-600 hover:bg-gray-50 dark:text-primary-400 dark:hover:bg-white/5">
                    Sitemap dosyasını aç
                </a>
            </div>
        </form>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm leading-6 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Yazı, kanal, etiket veya sabit sayfa eklendiğinde, güncellendiğinde ya da silindiğinde sitemap önbelleği otomatik yenilenir. Ayrıca Laravel zamanlayıcısı her 15 dakikada bir sitemap önbelleğini yeniler.
            <div class="mt-2 font-medium">
                Sitemap adresi: <a class="text-primary-600 underline dark:text-primary-400" href="{{ $this->sitemapUrl() }}" target="_blank" rel="noopener">{{ $this->sitemapUrl() }}</a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
