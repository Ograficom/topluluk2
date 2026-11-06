<x-filament-panels::page>
    <div class="mx-auto w-full max-w-6xl space-y-6">

        {{-- Page introduction --}}
        <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                    <x-filament::icon icon="heroicon-o-globe-alt" class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold tracking-tight text-gray-950">XML Sitemap yönetimi</h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">
                        OgraFi içeriklerinin arama motorları tarafından keşfedilmesini sağlayan sitemap dosyasını buradan yönetin.
                    </p>
                </div>
            </div>
            <a
                href="{{ $this->sitemapUrl() }}"
                target="_blank"
                rel="noopener"
                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            >
                <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                Sitemap'i görüntüle
            </a>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-500">Toplam URL</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <x-filament::icon icon="heroicon-o-link" class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-950">{{ number_format($this->urlCount) }}</p>
                <p class="mt-1 text-xs text-gray-500">Sitemap dosyasına eklenen adres sayısı</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-500">Son oluşturulma</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-3 text-xl font-semibold tracking-tight text-gray-950">
                    {{ $this->lastGeneratedAt ?? 'Henüz oluşturulmadı' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Sitemap dosyasının son yenilenme zamanı</p>
            </div>
        </div>

        {{-- Settings form --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-7">
                <h3 class="text-base font-semibold text-gray-950">Yayın ve içerik ayarları</h3>
                <p class="mt-1 text-sm text-gray-500">Sitemap'in hangi içerikleri kapsayacağını belirleyin.</p>
            </div>

            <form wire:submit="save" class="space-y-0">
                <div class="p-5 sm:p-7">
                    {{ $this->form }}
                </div>

                <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <p class="text-xs leading-5 text-gray-500">
                        Değişikliklerin geçerli olması için ayarlarınızı kaydedin.
                    </p>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-check"
                            class="w-full sm:w-auto"
                        >
                            Ayarları kaydet
                        </x-filament::button>

                        <x-filament::button
                            type="button"
                            color="gray"
                            icon="heroicon-o-arrow-path"
                            wire:click="rebuild"
                            wire:loading.attr="disabled"
                            wire:target="rebuild"
                            class="w-full sm:w-auto"
                        >
                            <span wire:loading.remove wire:target="rebuild">Şimdi yeniden oluştur</span>
                            <span wire:loading wire:target="rebuild">Oluşturuluyor...</span>
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sitemap address --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-gray-950">Sitemap dosya adresi</h3>
                    <p class="mt-1 text-sm text-gray-500">Bu bağlantıyı Google Search Console ve diğer arama motorlarında kullanabilirsiniz.</p>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <code class="min-w-0 flex-1 break-all rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700">{{ $this->sitemapUrl() }}</code>
                        <x-filament::button
                            type="button"
                            color="gray"
                            outlined
                            icon="heroicon-o-clipboard-document"
                            x-on:click="window.navigator.clipboard.writeText(@js($this->sitemapUrl()))"
                            class="shrink-0"
                        >
                            Adresi kopyala
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Information --}}
        <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/70 p-4">
            <x-filament::icon icon="heroicon-o-information-circle" class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
            <div class="text-sm leading-6 text-blue-900">
                <p class="font-medium">Otomatik güncelleme</p>
                <p class="mt-1 text-blue-800">
                    Yazı, kanal, etiket veya sabit sayfa eklendiğinde, güncellendiğinde ya da silindiğinde sitemap önbelleği otomatik yenilenir. Laravel zamanlayıcısı da her 15 dakikada bir sitemap önbelleğini yeniler.
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
