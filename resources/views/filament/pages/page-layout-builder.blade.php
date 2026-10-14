<x-filament::page>
    <div class="space-y-5">
        <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Blade sayfasi bolum duzenleyicisi</h2>
                    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                        Bir sayfa sec, bolumleri surukleyerek sirala, istemediklerini gizle ve onizleme sayfasinda gorunumu kontrol et.
                        Dinamik gonderi, profil ve yorum verileri mevcut Blade/Livewire kodundan gelmeye devam eder.
                    </p>
                </div>
                <div class="w-full lg:w-72">
                    <label for="ografi-page-layout-select" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Düzenlenecek sayfa</label>
                    <select id="ografi-page-layout-select" wire:model.live="pageKey" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        @foreach($this->pageOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-5 2xl:grid-cols-[360px_minmax(0,1fr)]">
            <section class="min-w-0 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-950 dark:text-white">Sayfa bolumleri</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tutamaktan sürükle. Göz simgesiyle bölümü gizle veya yeniden göster.</p>
                </div>
                <ol id="ografi-page-layout-order" class="space-y-2 p-3">
                    @foreach($this->blockItems() as $block)
                        <li wire:key="layout-block-{{ $pageKey }}-{{ $block['id'] }}"
                            data-layout-item="{{ $block['id'] }}"
                            class="flex items-center gap-2 rounded-lg border border-gray-200 p-3 dark:border-gray-700 {{ $block['hidden'] ? 'opacity-50' : '' }}">
                            <button type="button" data-drag-handle="{{ $block['id'] }}" aria-label="Surukle: {{ $block['label'] }}" title="Surukle" class="cursor-grab rounded-md p-2 text-gray-500 hover:bg-gray-100 active:cursor-grabbing dark:hover:bg-gray-800">
                                <svg viewBox="0 0 20 20" width="18" height="18" fill="currentColor" aria-hidden="true"><circle cx="6" cy="4" r="1.4"/><circle cx="14" cy="4" r="1.4"/><circle cx="6" cy="10" r="1.4"/><circle cx="14" cy="10" r="1.4"/><circle cx="6" cy="16" r="1.4"/><circle cx="14" cy="16" r="1.4"/></svg>
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $block['label'] }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $block['hidden'] ? 'Gizli' : 'Gorunur' }}</p>
                            </div>
                            <button type="button" wire:click="toggleBlock('{{ $block['id'] }}')" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ $block['hidden'] ? 'Bolumu goster' : 'Bolumu gizle' }}" title="{{ $block['hidden'] ? 'Göster' : 'Gizle' }}">
                                @if($block['hidden'])
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.3A10.7 10.7 0 0 1 12 5c5.5 0 9 7 9 7a17.9 17.9 0 0 1-3.3 4.1M6.2 6.2C4.1 7.6 3 12 3 12s3.5 7 9 7c.8 0 1.5-.1 2.2-.4"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ol>
                <div class="flex flex-wrap gap-2 border-t border-gray-200 p-3 dark:border-gray-700">
                    <x-filament::button color="gray" size="sm" type="button" wire:click="restoreDefaultOrder">Varsayilan sira</x-filament::button>
                    <x-filament::button color="gray" size="sm" type="button" wire:click="showAllBlocks">Tum bolumleri goster</x-filament::button>
                    <x-filament::button size="sm" type="button" wire:click="saveLayout">Duzeni kaydet</x-filament::button>
                </div>
            </section>

            <section class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-col gap-2 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                    <div>
                        <h3 class="font-semibold text-gray-950 dark:text-white">Canli sayfa onizlemesi</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Onizleme iframe'i mevcut sayfayi yukler. Sayfa bolumlerini suruklemek icin iframe icindeki tutamaklari kullan.</p>
                    </div>
                    <a href="{{ $this->previewUrl() }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-600 hover:underline">Onizlemeyi yeni sekmede ac</a>
                </div>
                <div class="bg-gray-50 p-2 dark:bg-gray-950">
                    <iframe
                        id="ografi-page-layout-preview"
                        title="Blade sayfa duzeni canli onizleme"
                        src="{{ $this->previewUrl() }}"
                        wire:key="layout-preview-{{ $pageKey }}"
                        class="h-[720px] w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700"
                        loading="eager"
                    ></iframe>
                </div>
            </section>
        </div>

        <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-3">
                <h3 class="font-semibold text-gray-950 dark:text-white">Sayfa duzeni gecmisi</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kayit oncesinde tum sayfa siralari yedeklenir; buradan onceki duzeni geri yukleyebilirsin.</p>
            </div>
            @forelse($this->revisions() as $revision)
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 py-3 first:border-0 dark:border-gray-800">
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
                <p class="text-sm text-gray-500">Henuz sayfa duzeni surumu kaydedilmedi.</p>
            @endforelse
        </section>
    </div>

    @script
    <script>
        (() => {
            const parentOrigin = window.location.origin;
            const getPageKey = () => document.getElementById('ografi-page-layout-select')?.value || 'home';
            const $wire = @this;
            let dragId = null;

            const getIds = () => Array.from(document.querySelectorAll('#ografi-page-layout-order [data-layout-item]'))
                .map((element) => element.dataset.layoutItem)
                .filter(Boolean);

            const getHidden = () => Array.from(document.querySelectorAll('#ografi-page-layout-order [data-layout-item]'))
                .filter((element) => element.classList.contains('opacity-50'))
                .map((element) => element.dataset.layoutItem)
                .filter(Boolean);

            const sendDraftToPreview = () => {
                const frame = document.getElementById('ografi-page-layout-preview');
                if (!frame?.contentWindow) return;
                frame.contentWindow.postMessage({
                    type: 'ografi-layout-apply-draft',
                    pageKey: getPageKey(),
                    order: getIds(),
                    hidden: getHidden(),
                }, parentOrigin);
            };

            const bindSorter = () => {
                const list = document.getElementById('ografi-page-layout-order');
                if (!list || list.dataset.sorterBound === '1') return;
                list.dataset.sorterBound = '1';

                list.querySelectorAll('[data-drag-handle]').forEach((handle) => {
                    const item = handle.closest('[data-layout-item]');
                    if (item) {
                        item.draggable = true;
                        item.classList.add('cursor-move');
                    }
                });

                list.addEventListener('dragstart', (event) => {
                    const item = event.target.closest('[data-layout-item]');
                    if (!item) return;
                    dragId = item.dataset.layoutItem;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', dragId);
                    item.classList.add('ring-2', 'ring-primary-500');
                });

                list.addEventListener('dragover', (event) => {
                    if (!dragId) return;
                    event.preventDefault();
                    const target = event.target.closest('[data-layout-item]');
                    if (!target || target.dataset.layoutItem === dragId) return;
                    const rect = target.getBoundingClientRect();
                    const after = event.clientY > rect.top + rect.height / 2;
                    list.insertBefore(list.querySelector('[data-layout-item="' + CSS.escape(dragId) + '"]'), after ? target.nextSibling : target);
                });

                list.addEventListener('drop', (event) => {
                    if (!dragId) return;
                    event.preventDefault();
                    const order = getIds();
                    dragId = null;
                    list.querySelectorAll('[data-layout-item]').forEach((item) => item.classList.remove('ring-2', 'ring-primary-500'));
                    $wire.setDraftOrder(order);
                    sendDraftToPreview();
                });

                list.addEventListener('dragend', () => {
                    dragId = null;
                    list.querySelectorAll('[data-layout-item]').forEach((item) => item.classList.remove('ring-2', 'ring-primary-500'));
                });
            };

            window.addEventListener('message', (event) => {
                if (event.origin !== parentOrigin || !event.data || event.data.type !== 'ografi-layout-order-changed') return;
                if (event.data.pageKey !== getPageKey() || !Array.isArray(event.data.order)) return;
                $wire.applyDraftFromPreview(event.data.pageKey, event.data.order, Array.isArray(event.data.hidden) ? event.data.hidden : []);
            });

            const layoutObserver = new MutationObserver(() => {
                bindSorter();
                sendDraftToPreview();
            });
            layoutObserver.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class'],
            });

            document.addEventListener('livewire:navigated', () => {
                dragId = null;
                bindSorter();
                sendDraftToPreview();
            });

            document.addEventListener('DOMContentLoaded', bindSorter);
            bindSorter();
            sendDraftToPreview();
        })();
    </script>
    @endscript
</x-filament::page>
