<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-6" id="bladeEditorRoot">
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
            Bu editÃ¶r sadece izinli Blade ÅŸablonlarÄ±nÄ± dÃ¼zenler. Kaydetmeden Ã¶nce otomatik yedek alÄ±nÄ±r.
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.25fr,1fr]">
            <div class="space-y-4">
                {{ $this->form }}
            </div>

            <div class="space-y-4 lg:sticky lg:top-6">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-sm font-semibold">GÃ¶rsel Ã–nizleme</div>
                        <div class="flex flex-wrap gap-2">
                            <x-filament::button wire:click="setPreviewMode('blade')" type="button" color="{{ $this->previewMode === 'blade' ? 'primary' : 'secondary' }}">
                                Blade Ã–nizleme
                            </x-filament::button>
                            <x-filament::button wire:click="setPreviewMode('html')" type="button" color="{{ $this->previewMode === 'html' ? 'primary' : 'secondary' }}">
                                HTML Ã–nizleme
                            </x-filament::button>
                        <x-filament::button id="openFullPreviewBtn" type="button" color="secondary">
                                Tam Goruntule
                            </x-filament::button>                        </div>
                    </div>

                    <div class="mt-3 text-xs text-slate-500">
                        Blade Ã¶nizleme kaydedilmiÅŸ dosyayÄ±, HTML Ã¶nizleme ise yazdÄ±ÄŸÄ±n iÃ§eriÄŸi gÃ¶sterir.
                    </div>

                    <div class="mt-4">
                        @if($this->previewMode === 'blade' && $this->getPreviewUrl())
                            <iframe
                                id="bladePreviewFrame"
                                src="{{ $this->getPreviewUrl() }}"
                                class="max-w-full rounded-lg border border-slate-200 bg-white"
                                style="width: 900px; height: 12000px;"
                                loading="lazy"
                            ></iframe>
                        @else
                            <iframe
                                id="htmlPreviewFrame"
                                class="max-w-full rounded-lg border border-slate-200 bg-white"
                                style="width: 900px; height: 12000px;"
                            ></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 text-xs text-slate-500">
            Son yedekler:
            @if(count($this->getRecentBackups()))
                <ul class="mt-2 list-disc ps-5">
                    @foreach($this->getRecentBackups() as $backup)
                        <li class="break-all">{{ $backup }}</li>
                    @endforeach
                </ul>
            @else
                <span>Yedek bulunamadÄ±.</span>
            @endif
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs text-slate-500">
                Not: Blade Ã¶nizleme hatasÄ± alÄ±rsan, ÅŸablonda zorunlu deÄŸiÅŸkenler eksik olabilir.
            </div>

            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="reloadFromDisk" type="button" color="secondary">
                    Diskten Yenile
                </x-filament::button>
                <x-filament::button type="submit">
                    Kaydet
                </x-filament::button>
            </div>
        </div>
    </form>
    <div id="fullPreviewModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div class="text-sm font-semibold">Tam Gorunum</div>
                    <button id="closeFullPreviewBtn" type="button" class="rounded-md px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">
                        Kapat
                    </button>
                </div>
                <iframe id="fullPreviewFrame" class="h-[85vh] w-full bg-white"></iframe>
            </div>
        </div>
    </div>
</x-filament::page>

@script
    <script>
        (function initBladeEditorPreview(){
            function bindPreview(){
                const root = document.getElementById('bladeEditorRoot');
                if (!root) return;

                const textarea = root.querySelector(
                    'textarea[name="state.content"], textarea[name="state[content]"], textarea[wire\\:model$=".content"], textarea[wire\\:model\\.defer$=".content"], textarea[wire\\:model\\.live$=".content"], textarea[wire\\:model\\.lazy$=".content"]'
                );
                const openBtn = document.getElementById('openFullPreviewBtn');
                const modal = document.getElementById('fullPreviewModal');
                const closeBtn = document.getElementById('closeFullPreviewBtn');
                const fullFrame = document.getElementById('fullPreviewFrame');
                if (!textarea) return;

                let raf = null;
                const update = () => {
                    const value = textarea.value || '';
                    const doc = `
                        <!doctype html>
                        <html lang="tr">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <script>
                                tailwind.config = {
                                    darkMode: 'class',
                                    theme: {
                                        extend: {
                                            fontFamily: {
                                                display: ['Poppins', 'sans-serif'],
                                                body: ['Poppins', 'sans-serif'],
                                                sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                                            },
                                            colors: {
                                                sitebg: '#f5f7fb',
                                                primary: '#10b981',
                                                'primary-dark': '#059669',
                                                'background-light': '#f1f5f9',
                                                'background-dark': '#0f172a',
                                                'surface-light': '#ffffff',
                                                'surface-dark': '#1e293b',
                                                'header-bg': '#dbeafe',
                                                'header-bg-dark': '#1e293b',
                                                'card-light': '#ffffff',
                                                'card-dark': '#1e293b',
                                                'text-light': '#1f2937',
                                                'text-dark': '#e2e8f0',
                                                'muted-light': '#6b7280',
                                                'muted-dark': '#94a3b8',
                                                'text-main-light': '#1f2937',
                                                'text-main-dark': '#e2e8f0',
                                                'text-sub-light': '#6b7280',
                                                'text-sub-dark': '#94a3b8',
                                            },
                                            borderRadius: {
                                                DEFAULT: '0.75rem',
                                                full: '9999px',
                                            },
                                        },
                                    },
                                };
                            </script>
                            <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
                            <style>body{margin:16px;background:#fff;color:#0f172a;font-family:'Poppins','Segoe UI',sans-serif;font-weight:200;}body :where(h1,h2,h3,h4,h5,h6,strong,b,button,.font-light,.font-medium,.font-semibold,.font-bold,.font-extrabold,.font-black){font-weight:300!important;}</style>
                        </head>
                        <body>${value}</body>
                        </html>
                    `;
                    const htmlFrame = document.getElementById('htmlPreviewFrame');
                    if (htmlFrame) {
                        htmlFrame.srcdoc = doc;
                    }
                    if (fullFrame && fullFrame.dataset.mode === 'html') {
                        fullFrame.srcdoc = doc;
                    }
                };

                const schedule = () => {
                    if (raf) cancelAnimationFrame(raf);
                    raf = requestAnimationFrame(update);
                };

                textarea.addEventListener('input', schedule);
                update();

                const openModal = () => {
                    if (!modal || !fullFrame) return;
                    const isBlade = document.getElementById('bladePreviewFrame');
                    if (isBlade) {
                        fullFrame.dataset.mode = 'blade';
                        fullFrame.src = isBlade.getAttribute('src') || '';
                        fullFrame.removeAttribute('srcdoc');
                    } else {
                        fullFrame.dataset.mode = 'html';
                        fullFrame.src = '';
                        fullFrame.srcdoc = htmlFrame.srcdoc || '';
                    }
                    modal.classList.remove('hidden');
                };

                const closeModal = () => {
                    if (!modal || !fullFrame) return;
                    modal.classList.add('hidden');
                    fullFrame.src = '';
                    fullFrame.srcdoc = '';
                };

                openBtn?.addEventListener('click', openModal);
                closeBtn?.addEventListener('click', closeModal);
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            }

            const bindAll = () => {
                bindPreview();

                const openBtn = document.getElementById('openFullPreviewBtn');
                const modal = document.getElementById('fullPreviewModal');
                const closeBtn = document.getElementById('closeFullPreviewBtn');
                const fullFrame = document.getElementById('fullPreviewFrame');
                const htmlFrame = document.getElementById('htmlPreviewFrame');

                const openModal = () => {
                    if (!modal || !fullFrame) return;
                    const isBlade = document.getElementById('bladePreviewFrame');
                    if (isBlade) {
                        fullFrame.dataset.mode = 'blade';
                        fullFrame.src = isBlade.getAttribute('src') || '';
                        fullFrame.removeAttribute('srcdoc');
                    } else if (htmlFrame) {
                        fullFrame.dataset.mode = 'html';
                        fullFrame.src = '';
                        fullFrame.srcdoc = htmlFrame.srcdoc || '';
                    }
                    modal.classList.remove('hidden');
                };

                const closeModal = () => {
                    if (!modal || !fullFrame) return;
                    modal.classList.add('hidden');
                    fullFrame.src = '';
                    fullFrame.srcdoc = '';
                };

                openBtn?.addEventListener('click', openModal);
                closeBtn?.addEventListener('click', closeModal);
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            };

            document.addEventListener('livewire:navigated', bindAll);
            document.addEventListener('DOMContentLoaded', bindAll);
            bindAll();
        })();
    </script>
@endscript

