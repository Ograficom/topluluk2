<x-filament::page>
    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">OgraFi gorsel bilesen duzenleyicisi</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Var olan Blade/Livewire bilesenleri yerinde kalir. Bu ekranda stil ayarlarini degistirip kaydetmeden once ornek gorunumu inceleyebilirsin.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button color="gray" size="sm" type="button" wire:click="applyPreset('minimal')">Sade sablon</x-filament::button>
                    <x-filament::button color="gray" size="sm" type="button" wire:click="applyPreset('blue')">Mavi topluluk sablonu</x-filament::button>
                    <x-filament::button color="gray" size="sm" type="button" wire:click="applyPreset('soft')">Yumusak kartlar sablonu</x-filament::button>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <div class="min-w-0">
                    {{ $this->form }}
                </div>

                <aside class="min-w-0 space-y-4 xl:sticky xl:top-4">
                    @php
                        $previewStyles = \App\Services\ThemeComponentDesign::normalize(data_get($state, 'component_styles', []));
                        $previewDeviceWidth = $previewDevice === 'mobile' ? '375px' : '100%';
                    @endphp
                    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-2 border-b border-gray-200 p-3 dark:border-gray-700">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Ornek tasarim onizlemesi</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Stil degisiklikleri ornek bilesenlerde gorunur; canli sayfanin birebir kopyasi degildir.</p>
                            </div>
                            <div class="flex gap-1">
                                <x-filament::button size="xs" type="button" :color="$previewDevice === 'desktop' ? 'primary' : 'gray'" wire:click="$set('previewDevice', 'desktop')">Masaustu</x-filament::button>
                                <x-filament::button size="xs" type="button" :color="$previewDevice === 'mobile' ? 'primary' : 'gray'" wire:click="$set('previewDevice', 'mobile')">Mobil</x-filament::button>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-3 dark:bg-gray-950">
                            <nav class="mb-3 flex flex-wrap gap-1">
                                @foreach([
                                    'header' => 'Header',
                                    'home' => 'Ana sayfa',
                                    'post_card' => 'Post-card',
                                    'post_show' => 'Post-show',
                                    'comments' => 'Yorumlar',
                                    'footer' => 'Footer',
                                ] as $key => $label)
                                    <button type="button"
                                        wire:click="$set('previewComponent', '{{ $key }}')"
                                        class="{{ $previewComponent === $key ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-300' }} rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-gray-700">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </nav>

                            <div class="mx-auto overflow-hidden rounded-lg border border-gray-200 bg-white shadow-none dark:border-gray-700"
                                style="width: {{ $previewDeviceWidth }}; max-width: 100%;">
                                @switch($previewComponent)
                                    @case('header')
                                        @php($component = $previewStyles['header'])
                                        @if($component['visible'])
                                            <div class="flex items-center justify-between gap-2 px-3" style="min-height: {{ $component['height'] }}px; padding-inline: {{ $component['padding_x'] }}px; color: {{ $component['text_color'] }}; background: {{ $component['background'] }}; border-bottom: 1px solid {{ $component['border_color'] }};">
                                                <span class="text-sm font-semibold">OgraFi</span>
                                                <span class="text-xs">Kesfet&nbsp;&nbsp; Kategoriler&nbsp;&nbsp; Giris</span>
                                            </div>
                                        @else
                                            <div class="p-4 text-xs text-gray-500">Header gizli.</div>
                                        @endif
                                        @break
                                    @case('home')
                                        @php($component = $previewStyles['home'])
                                        <div class="p-3" style="max-width: {{ $component['max_width'] }}px; margin: auto;">
                                            <div class="mb-3 rounded-lg border border-gray-200 p-3 text-xs text-gray-600">Ana sayfa baslik ve filtreleri</div>
                                            <div style="display:grid; grid-template-columns: {{ $component['show_sidebars'] && $previewDevice === 'desktop' ? 'minmax(0,1fr) 80px' : 'minmax(0,1fr)' }}; gap: {{ $component['gap'] }}px;">
                                                <div class="space-y-2">
                                                    <div class="rounded-lg border border-gray-200 bg-white p-3 text-sm">Post-card ornegi</div>
                                                    <div class="rounded-lg border border-gray-200 bg-white p-3 text-sm">Post-card ornegi</div>
                                                </div>
                                                @if($component['show_sidebars'] && $previewDevice === 'desktop')
                                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 text-xs text-gray-500">Yan<br>sutun</div>
                                                @endif
                                            </div>
                                        </div>
                                        @break
                                    @case('post_card')
                                        @php($component = $previewStyles['post_card'])
                                        <article class="space-y-3" style="padding: {{ $component['padding'] }}px; border-radius: {{ $component['radius'] }}px; background: {{ $component['background'] }}; color: {{ $component['text_color'] }}; border: 1px solid {{ $component['border_color'] }};">
                                            <div class="h-24 rounded-md bg-gray-100 dark:bg-gray-800"></div>
                                            <h4 style="font-size: {{ $component['title_size'] }}px;" class="font-semibold">Topluluk gonderisi basligi</h4>
                                            <p style="font-size: {{ $component['body_size'] }}px;">Gonderi aciklamasi ve onizleme metni burada gorunur.</p>
                                            <div class="border-t pt-2 text-xs" style="border-color: {{ $component['border_color'] }};">Yazar · 5 dk · Yorumlar · Kaydet</div>
                                        </article>
                                        @break
                                    @case('post_show')
                                        @php($component = $previewStyles['post_show'])
                                        <article style="max-width: {{ $component['max_width'] }}px; margin: auto; padding: {{ $component['padding'] }}px; border-radius: {{ $component['radius'] }}px; background: {{ $component['background'] }}; color: {{ $component['text_color'] }}; border: 1px solid {{ $component['border_color'] }};">
                                            <h4 style="font-size: {{ $component['title_size'] }}px;" class="mb-3 font-semibold">Gonderi ayrinti basligi</h4>
                                            <p class="text-sm leading-6">Bu kisim gonderinin ana metnini, medya bloklarini ve paylasim ayrintilarini temsil eder.</p>
                                        </article>
                                        @break
                                    @case('comments')
                                        @php($component = $previewStyles['comments'])
                                        <section style="padding: {{ $component['padding'] }}px; border-radius: {{ $component['radius'] }}px; background: {{ $component['background'] }}; color: {{ $component['text_color'] }}; border: 1px solid {{ $component['border_color'] }};">
                                            <h4 class="mb-3 font-semibold">Yorumlar</h4>
                                            <article class="rounded-lg p-3" style="background: {{ $component['background'] }}; border: 1px solid {{ $component['border_color'] }};">
                                                <div class="mb-2 text-xs font-semibold">Kullanici · az once</div>
                                                <p class="text-sm">Yorum kartinin ornek gorunumu.</p>
                                            </article>
                                        </section>
                                        @break
                                    @case('footer')
                                        @php($component = $previewStyles['footer'])
                                        @if($component['visible'])
                                            <footer style="padding: {{ $component['padding'] }}px 16px; background: {{ $component['background'] }}; color: {{ $component['text_color'] }}; border-top: 1px solid {{ $component['border_color'] }};" class="text-sm">
                                                <span>{{ $component['text'] !== '' ? $component['text'] : config('app.name', 'Ografi') }}</span>
                                                @if($component['link_label'] !== '' && $component['link_url'] !== '')
                                                    <a href="{{ $component['link_url'] }}" class="ml-3 underline underline-offset-2">{{ $component['link_label'] }}</a>
                                                @endif
                                            </footer>
                                        @else
                                            <div class="p-4 text-xs text-gray-500">Footer devre disi. Etkinlestirildiginde sayfanin altinda gorunur.</div>
                                        @endif
                                        @break
                                @endswitch
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Bu, gorsel ayarlarin onizlemesidir. Canli sitedeki veri, medya ve etkilesimler degistirilmez.</p>
                        </div>
                    </section>

                    <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="mb-3">
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Son tasarim surumleri</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Kaydetmeden onceki gorunumler otomatik yedeklenir. Son 10 surum gosterilir.</p>
                        </div>
                        @php($revisionItems = $this->revisions())
                        @forelse($revisionItems as $revision)
                            <div class="flex items-start justify-between gap-3 border-t border-gray-100 py-3 first:border-0 dark:border-gray-800">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $revision->label }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $revision->created_at?->format('d.m.Y H:i') }}
                                        @if($revision->user?->name) · {{ $revision->user->name }} @endif
                                    </p>
                                </div>
                                <x-filament::button size="xs" type="button" color="gray" wire:click="restoreRevision({{ $revision->id }})">
                                    Geri yukle
                                </x-filament::button>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Henuz yedek tasarim yok. Ilk degisiklik kaydedildiginde olusur.</p>
                        @endforelse
                    </section>
                </aside>
            </div>

            @php
                $livePreviewStyles = \App\Services\ThemeComponentDesign::normalize(data_get($state, 'component_styles', []));
                $livePreviewCss = app(\App\Services\ThemeComponentDesign::class)->render($livePreviewStyles);
            @endphp
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-col gap-3 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Gerçek Blade sayfası önizlemesi</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kaydedilmemiş stil değişiklikleri yalnızca bu yönetici önizlemesinde görünür. Siteye uygulamak için ayrıca kaydetmelisin.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <select wire:model.live="livePreviewPage" aria-label="Önizlenecek sayfa" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                            <option value="home">Ana sayfa</option>
                            <option value="post_show">Gönderi detayı</option>
                            <option value="categories">Kategoriler</option>
                            <option value="profile">Kullanıcı profili</option>
                        </select>
                        <x-filament::button size="sm" type="button" :color="$previewDevice === 'desktop' ? 'primary' : 'gray'" wire:click="$set('previewDevice', 'desktop')">Masaüstü</x-filament::button>
                        <x-filament::button size="sm" type="button" :color="$previewDevice === 'mobile' ? 'primary' : 'gray'" wire:click="$set('previewDevice', 'mobile')">Mobil</x-filament::button>
                    </div>
                </div>
                <div class="overflow-x-auto bg-gray-50 p-3 dark:bg-gray-950">
                    <script type="application/json" id="ografi-component-design-preview-config">{!! json_encode(['css' => $livePreviewCss], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
                    <iframe
                        id="ografi-component-design-live-preview"
                        title="OgraFi canlı Blade tasarım önizlemesi"
                        src="{{ $this->livePreviewUrl() }}"
                        class="mx-auto block rounded-lg border border-gray-200 bg-white dark:border-gray-700"
                        style="width: {{ $previewDevice === 'mobile' ? '375px' : '100%' }}; max-width: 100%; height: 760px;"
                        loading="eager"
                    ></iframe>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Sablon secimi ve form degisiklikleri kaydedene kadar onizleme modundadir.</p>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button color="gray" type="button" wire:click="resetToDefaults">Varsayilanlari yukle</x-filament::button>
                    <x-filament::button type="submit">Tasarimi kaydet ve yayinla</x-filament::button>
                </div>
            </div>
        </form>

        @script
        <script>
            (() => {
                const frameId = 'ografi-component-design-live-preview';
                const configId = 'ografi-component-design-preview-config';
                const readCss = () => {
                    try {
                        const node = document.getElementById(configId);
                        return JSON.parse(node?.textContent || '{}').css || '';
                    } catch (error) {
                        console.error('[Ografi component preview] Invalid draft style payload.', error);
                        return '';
                    }
                };

                const sendDraft = () => {
                    const frame = document.getElementById(frameId);
                    if (!frame?.contentWindow) return;

                    frame.contentWindow.postMessage({
                        type: 'ografi-component-design-apply-preview',
                        css: readCss(),
                    }, window.location.origin);
                };

                document.addEventListener('livewire:navigated', sendDraft);
                document.getElementById(frameId)?.addEventListener('load', sendDraft);

                const observer = new MutationObserver(() => sendDraft());
                observer.observe(document.body, { childList: true, subtree: true, characterData: true });

                sendDraft();
            })();
        </script>
        @endscript
    </div>
</x-filament::page>
