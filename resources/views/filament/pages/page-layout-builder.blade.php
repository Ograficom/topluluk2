<x-filament::page>
    <div class="space-y-5">
        <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Tum Blade sayfalarini surukle-birak duzenle</h2>
                    <p class="mt-1 max-w-4xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Bu ekran sabit bir bolum listesinden ibaret degil. Var olan sitenin gercek sayfasini asagida ac;
                        mavi tutamaclari kullanarak HTML bolumlerini ayni kapsayici icinde surukle, bolumleri gizle veya geri getir.
                        Degisiklikler sadece kaydettiginde ziyaretcilere uygulanir.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button id="ografi-layout-reload" color="gray" type="button">Onizlemeyi yenile</x-filament::button>
                    <x-filament::button id="ografi-layout-reset" color="gray" type="button">Bu sayfayi varsayilana al</x-filament::button>
                    <x-filament::button id="ografi-layout-save" type="button">Duzeni kaydet</x-filament::button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <label for="ografi-layout-path" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Duzenlenecek sayfanin adresi</label>
                    <input
                        id="ografi-layout-path"
                        type="text"
                        list="ografi-layout-page-shortcuts"
                        wire:model.live.debounce.700ms="previewPath"
                        placeholder="/, /discover, /blog/, /tr/gonderi-slug, /u/kullanici-adi"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    <datalist id="ografi-layout-page-shortcuts">
                        @foreach($this->pageShortcuts() as $path => $label)
                            <option value="{{ $path }}">{{ $label }}</option>
                        @endforeach
                    </datalist>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Yalnizca kendi sitenin yolunu gir. Rota parametreli sayfalar icin tam yolu yazabilirsin; ornegin /tr/yazi-slug veya /u/kullanici-adi.
                    </p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Secili Blade rotasi</p>
                    <p id="ografi-layout-route-label" class="mt-1 max-w-xs break-all text-sm font-medium text-gray-900 dark:text-white">{{ $activeLayoutKey }}</p>
                    <p id="ografi-layout-dirty-label" class="mt-1 text-xs {{ $layoutDirty ? 'text-amber-600' : 'text-emerald-600' }}">
                        {{ $layoutDirty ? 'Kaydedilmemis degisiklik var' : 'Degisiklikler kaydedildi' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-2 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Gercek sayfa duzenleyicisi</h3>
                    <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        Sayfada bolumlerin uzerine gel. Gorunen surukleme araciyla o bolumu kardes bolumleri arasinda tasi;
                        Gizle/Goster ile yayinlanan gorunumunu kontrol et. Formlar ve baglantilar bu onizlemede islem yapmaz.
                    </p>
                </div>
                <a href="{{ $this->publicPreviewUrl() }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-600 hover:underline">Normal sayfayi yeni sekmede gor</a>
            </div>

            <div class="bg-gray-100 p-2 dark:bg-gray-950 sm:p-3">
                <iframe
                    id="ografi-page-layout-preview"
                    title="Gercek Blade sayfasi surukle-birak onizlemesi"
                    src="{{ $this->previewUrl() }}"
                    wire:key="ografi-layout-preview-{{ md5($this->previewUrl()) }}"
                    class="h-[72vh] min-h-[560px] w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700"
                    loading="eager"
                ></iframe>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Son sayfa duzeni yedekleri</h3>
            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                Geri yukleme kayit anindaki tum Blade sayfa duzenlerini geri alir. Son 10 yedek listelenir.
            </p>
            <div class="mt-2 divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->revisions() as $revision)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $revision->label }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $revision->created_at?->format('d.m.Y H:i') }}
                                @if($revision->user?->name) · {{ $revision->user->name }} @endif
                            </p>
                        </div>
                        <x-filament::button size="xs" color="gray" type="button" wire:click="restoreRevision({{ $revision->id }})">Geri yukle</x-filament::button>
                    </div>
                @empty
                    <p class="py-3 text-sm text-gray-500">Henuz yedek tasarim yok.</p>
                @endforelse
            </div>
        </section>
    </div>

    @script
    <script>
        (() => {
            const frame = () => document.getElementById('ografi-page-layout-preview');
            const saveButton = document.getElementById('ografi-layout-save');
            const resetButton = document.getElementById('ografi-layout-reset');
            const reloadButton = document.getElementById('ografi-layout-reload');
            const pathInput = document.getElementById('ografi-layout-path');
            const routeLabel = document.getElementById('ografi-layout-route-label');
            const dirtyLabel = document.getElementById('ografi-layout-dirty-label');
            const $wire = @this;
            let pendingSync = Promise.resolve();

            const setDirtyLabel = (dirty) => {
                if (!dirtyLabel) return;
                dirtyLabel.textContent = dirty ? 'Kaydedilmemis degisiklik var' : 'Degisiklikler kaydedildi';
                dirtyLabel.classList.toggle('text-amber-600', dirty);
                dirtyLabel.classList.toggle('text-emerald-600', !dirty);
            };

            const reloadPreview = () => {
                const iframe = frame();
                if (!iframe) return;

                const current = new URL(iframe.src, window.location.origin);
                current.searchParams.set('ografi_layout_preview', '1');
                current.searchParams.set('_ografi_reload', String(Date.now()));
                iframe.src = current.toString();
            };

            const enqueueSync = (pageKey, layout, dirty) => {
                pendingSync = pendingSync
                    .catch(() => undefined)
                    .then(() => $wire.syncUniversalDraft(pageKey, layout, dirty))
                    .then(() => {
                        if (routeLabel) routeLabel.textContent = pageKey;
                        setDirtyLabel(dirty);
                    });

                return pendingSync;
            };

            window.addEventListener('message', (event) => {
                const iframe = frame();
                if (!iframe || event.origin !== window.location.origin || event.source !== iframe.contentWindow) return;
                if (!event.data || event.data.type !== 'ografi-layout-editor-state') return;
                if (typeof event.data.pageKey !== 'string' || !event.data.pageKey.startsWith('blade:')) return;
                if (!event.data.layout || typeof event.data.layout !== 'object') return;

                void enqueueSync(event.data.pageKey, event.data.layout, event.data.dirty === true);
            });

            saveButton?.addEventListener('click', async () => {
                saveButton.disabled = true;
                try {
                    await pendingSync;
                    await $wire.saveLayout();
                    setDirtyLabel(false);
                } finally {
                    saveButton.disabled = false;
                }
            });

            resetButton?.addEventListener('click', async () => {
                resetButton.disabled = true;
                try {
                    await pendingSync;
                    await $wire.clearCurrentLayout();
                } finally {
                    resetButton.disabled = false;
                }
            });

            reloadButton?.addEventListener('click', reloadPreview);
            window.addEventListener('ografi-layout-reload-preview', reloadPreview);

            pathInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    pathInput.blur();
                }
            });
        })();
    </script>
    @endscript
</x-filament::page>
