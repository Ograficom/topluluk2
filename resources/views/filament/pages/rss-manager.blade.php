<x-filament-panels::page>
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-6 py-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 ring-1 ring-primary-100">
                        <x-filament::icon icon="heroicon-o-rss" class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-gray-950">RSS akış merkezi</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">OgraFi içeriklerini RSS okuyucularına yayınlayın. Genel akışı ve her yazar, kanal veya etiket için ayrı akışları buradan yönetin.</p>
                    </div>
                </div>
                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-success-50 px-3 py-1.5 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20">
                    <span class="h-2 w-2 rounded-full bg-success-500"></span>
                    Yönetim paneli
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                            <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-950">Yayın ayarları</h3>
                            <p class="mt-0.5 text-sm text-gray-500">Hangi akışların yayınlanacağını ve içerik biçimini belirleyin.</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    {{ $this->form }}
                </div>
                <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-gray-500">Yaptığınız değişiklikler kaydedildikten sonra akışlara uygulanır.</p>
                    <x-filament::button type="submit" icon="heroicon-m-check" size="lg">Ayarları kaydet</x-filament::button>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <x-filament::icon icon="heroicon-o-link" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-950">RSS adresleri</h3>
                        <p class="mt-0.5 text-sm text-gray-500">Akış adreslerini kopyalayın veya yeni sekmede açıp kontrol edin.</p>
                    </div>
                </div>
                <span class="text-xs font-medium text-gray-500">{{ count($this->feedUrls()) }} temel akış adresi</span>
            </div>

            <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->feedUrls() as $index => $feed)
                    <div wire:key="rss-feed-{{ $index }}" class="group flex min-w-0 flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 transition-colors hover:border-primary-300 hover:bg-primary-50/30">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $feed['label'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    @switch($index)
                                        @case(0) Sitedeki tüm uygun yayınlar @break
                                        @case(1) En son yayınlanan yazılar @break
                                        @case(2) Kullanıcı adına göre yazar akışı @break
                                        @case(3) Kanal slug değerine göre akış @break
                                        @default Etiket slug değerine göre akış
                                    @endswitch
                                </p>
                            </div>
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 group-hover:bg-primary-100 group-hover:text-primary-700">
                                <x-filament::icon icon="heroicon-o-rss" class="h-4 w-4" />
                            </span>
                        </div>

                        <div class="rounded-lg bg-gray-50 px-3 py-2.5 ring-1 ring-inset ring-gray-100">
                            <code class="block break-all text-xs leading-5 text-gray-700">{{ $feed['url'] }}</code>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-medium uppercase tracking-wide text-gray-400">RSS 2.0</span>
                            <div class="flex items-center gap-1">
                                <x-filament::icon-button icon="heroicon-o-clipboard" color="gray" size="sm" tooltip="Adresi kopyala" x-on:click="window.navigator.clipboard.writeText(@js($feed['url']));" />
                                <x-filament::icon-button tag="a" :href="$feed['url']" target="_blank" rel="noopener noreferrer" icon="heroicon-o-arrow-top-right-on-square" color="gray" size="sm" tooltip="Akışı aç" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-100 bg-gray-50/70 px-6 py-4">
                <p class="text-xs leading-5 text-gray-500">
                    <span class="font-semibold text-gray-700">Not:</span>
                    Örnek yazar, kanal ve etiket adreslerindeki <code class="rounded bg-gray-200/70 px-1 py-0.5 text-gray-700">{username}</code> veya <code class="rounded bg-gray-200/70 px-1 py-0.5 text-gray-700">{slug}</code> alanlarını gerçek kullanıcı adı ya da slug ile değiştirin.
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
