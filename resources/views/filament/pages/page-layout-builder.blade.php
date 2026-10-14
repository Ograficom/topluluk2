<x-filament::page>
    <div class="space-y-5">
        <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Tum Blade sayfalarini gorsel duzenle</h2>
                    <p class="mt-1 max-w-4xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Gercek sayfayi onizlemede ac. Uzerine geldiginde cikan tutamaciyla bolumleri surukle, gizle veya goster.
                        Hazir baslik, metin, gorsel ve buton bloklarini da ekleyebilirsin. Degisiklikler kaydedilene kadar yayina alinmaz.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button id="ografi-layout-reload" color="gray" type="button">Onizlemeyi yenile</x-filament::button>
                    <x-filament::button id="ografi-layout-reset" color="gray" type="button">Sayfayi varsayilana al</x-filament::button>
                    <x-filament::button id="ografi-layout-save" type="button">Duzeni kaydet</x-filament::button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <label for="ografi-layout-path" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Duzenlenecek sayfanin adresi</label>
                    <input id="ografi-layout-path" type="text" list="ografi-layout-page-shortcuts"
                        wire:model.live.debounce.700ms="previewPath"
                        placeholder="/, /discover, /blog/, /tr/yazi-slug, /u/kullanici-adi"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <datalist id="ografi-layout-page-shortcuts">
                        @foreach($this->pageShortcuts() as $path => $label)
                            <option value="{{ $path }}">{{ $label }}</option>
                        @endforeach
                    </datalist>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Herhangi bir ayni-site yolunu girebilirsin. Parametreli sayfalar icin gercek yolu yaz; ornegin /tr/yazi-slug veya /u/kullanici-adi.</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Secili Laravel rotasi</p>
                    <p id="ografi-layout-route-label" class="mt-1 max-w-xs break-all text-sm font-medium text-gray-900 dark:text-white">{{ $activeLayoutKey }}</p>
                    <p id="ografi-layout-dirty-label" class="mt-1 text-xs {{ $layoutDirty ? 'text-amber-600' : 'text-emerald-600' }}">{{ $layoutDirty ? 'Kaydedilmemis degisiklik var' : 'Degisiklikler kaydedildi' }}</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-5 2xl:grid-cols-[340px_minmax(0,1fr)]">
            <aside class="min-w-0 space-y-4">
                <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Yeni blok ekle</h3>
                    <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Blok sayfanin ana icerik alanina eklenir. Sonra onizlemede tutamaciyla istedigin yere tasi.</p>
                    <div class="mt-3 flex gap-2">
                        <select wire:model.live="newBlockType" aria-label="Yeni blok turu" class="min-w-0 flex-1 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                            <option value="heading">Baslik</option>
                            <option value="text">Metin</option>
                            <option value="image">Gorsel</option>
                            <option value="button">Buton</option>
                            <option value="callout">Vurgulu alan</option>
                            <option value="divider">Ayirici</option>
                            <option value="spacer">Bosluk</option>
                        </select>
                        <x-filament::button type="button" wire:click="addUniversalBlock">Blok ekle</x-filament::button>
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ekledigin bloklar</h4>
                        <div class="mt-2 space-y-2">
                            @forelse($this->contentBlocks() as $block)
                                <div wire:key="custom-blade-block-{{ $block['id'] }}" class="flex items-center gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $block['label'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $block['type'] }}</p>
                                    </div>
                                    <button type="button" wire:click="editUniversalBlock('{{ $block['id'] }}')" class="rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Duzenle</button>
                                    <button type="button" wire:click="removeUniversalBlock('{{ $block['id'] }}')" class="rounded-md border border-gray-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:border-gray-700 dark:hover:bg-red-950">Sil</button>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Bu sayfaya henuz yeni blok eklenmedi.</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                @if($editingBlockId)
                    <section class="space-y-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Blok icerigini duzenle</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Degisiklikler once onizlemeye uygulanir; kalici hale gelmesi icin sayfa duzenini de kaydet.</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Blok tipi</label>
                            <input value="{{ $blockDraft['type'] ?? '' }}" readonly class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm dark:border-gray-700 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Baslik</label>
                            <input wire:model="blockDraft.title" maxlength="140" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Metin</label>
                            <textarea wire:model="blockDraft.text" rows="4" maxlength="4000" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Gorsel adresi (HTTPS)</label>
                            <input wire:model="blockDraft.image_url" maxlength="2000" placeholder="https://..." class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Gorsel aciklamasi</label>
                            <input wire:model="blockDraft.alt" maxlength="180" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Buton yazisi</label>
                            <input wire:model="blockDraft.button_text" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tiklama adresi (HTTPS veya site ici yol)</label>
                            <input wire:model="blockDraft.url" maxlength="2000" placeholder="/iletisim" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Arka plan</label>
                                <input type="color" wire:model="blockDraft.background" class="h-9 w-full rounded border border-gray-300 p-1 dark:border-gray-600">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Yazi rengi</label>
                                <input type="color" wire:model="blockDraft.text_color" class="h-9 w-full rounded border border-gray-300 p-1 dark:border-gray-600">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Ic bosluk (px)</label>
                                <input type="number" min="0" max="64" wire:model="blockDraft.padding" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Genislik (px)</label>
                                <input type="number" min="240" max="1600" wire:model="blockDraft.max_width" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Hizalama</label>
                            <select wire:model="blockDraft.align" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                                <option value="left">Sola</option>
                                <option value="center">Ortala</option>
                                <option value="right">Saga</option>
                            </select>
                        </div>
                        <x-filament::button type="button" block wire:click="saveUniversalBlockDraft">Blok ayarlarini onizlemeye uygula</x-filament::button>
                    </section>
                @endif
            </aside>

            <section class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-col gap-2 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Gercek Blade sayfasi</h3>
                        <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Bolum uzerine gelerek tutamacini ac. Sadece kardes bolumler ayni kapsayicinin icinde yer degistirir.</p>
                    </div>
                    <a href="{{ $this->publicPreviewUrl() }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-600 hover:underline">Normal sayfayi ac</a>
                </div>

                <div class="bg-gray-100 p-2 dark:bg-gray-950 sm:p-3">
                    <iframe id="ografi-page-layout-preview" title="Gercek Blade sayfasi surukle birak onizlemesi"
                        src="{{ $this->previewUrl() }}"
                        wire:key="ografi-layout-preview-{{ md5($this->previewUrl()) }}"
                        class="h-[72vh] min-h-[560px] w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700"
                        loading="eager"></iframe>
                </div>
            </section>
        </div>

        <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Son sayfa duzeni yedekleri</h3>
            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Geri yukleme, kayit anindaki tum Blade sayfa duzenlerini geri alir.</p>
            <div class="mt-2 divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->revisions() as $revision)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $revision->label }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $revision->created_at?->format('d.m.Y H:i') }} @if($revision->user?->name) · {{ $revision->user->name }} @endif</p>
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
            const iframe = () => document.getElementById('ografi-page-layout-preview');
            const $wire = @this;
            const saveButton = document.getElementById('ografi-layout-save');
            const resetButton = document.getElementById('ografi-layout-reset');
            const reloadButton = document.getElementById('ografi-layout-reload');
            const pathInput = document.getElementById('ografi-layout-path');
            const routeLabel = document.getElementById('ografi-layout-route-label');
            const dirtyLabel = document.getElementById('ografi-layout-dirty-label');
            let pendingSync = Promise.resolve();

            const setDirtyLabel = (dirty) => {
                if (!dirtyLabel) return;
                dirtyLabel.textContent = dirty ? 'Kaydedilmemis degisiklik var' : 'Degisiklikler kaydedildi';
                dirtyLabel.classList.toggle('text-amber-600', dirty);
                dirtyLabel.classList.toggle('text-emerald-600', !dirty);
            };

            const reloadPreview = () => {
                const frame = iframe();
                if (!frame) return;
                const current = new URL(frame.src, window.location.origin);
                current.searchParams.set('ografi_layout_preview', '1');
                current.searchParams.set('_ografi_reload', String(Date.now()));
                frame.src = current.toString();
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
                const frame = iframe();
                if (!frame || event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
                if (!event.data || event.data.type !== 'ografi-layout-editor-state') return;
                if (typeof event.data.pageKey !== 'string' || !event.data.pageKey.startsWith('blade:')) return;
                if (!event.data.layout || typeof event.data.layout !== 'object') return;
                void enqueueSync(event.data.pageKey, event.data.layout, event.data.dirty === true);
            });

            window.addEventListener('ografi-layout-sync-preview', (event) => {
                const frame = iframe();
                if (!frame?.contentWindow || !event.detail) return;
                const pageKey = event.detail.pageKey;
                const layout = event.detail.layout;
                if (typeof pageKey !== 'string' || !layout) return;
                frame.contentWindow.postMessage({
                    type: 'ografi-layout-apply-draft',
                    pageKey,
                    layout,
                }, window.location.origin);
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
