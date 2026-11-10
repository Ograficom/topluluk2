<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-5 py-5 dark:border-gray-700 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-600 dark:text-primary-400">OGRAFI · ARAMA GÖRÜNÜRLÜĞÜ</p>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-gray-950 dark:text-white">SEO kontrol merkezi</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">Google arama görünümünü, sosyal paylaşım kartlarını ve indeksleme kurallarını tek noktadan yönetin. Her içerik türünün kendi SEO alanları ayrıca korunur.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">Sayfa bazlı yönetim</span>
                </div>
            </div>
            <div class="grid grid-cols-1 divide-y divide-gray-200 dark:divide-gray-700 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <a href="#seo-search-console" class="group flex items-center gap-3 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300"><x-heroicon-o-shield-check class="size-5" /></span>
                    <span><span class="block text-sm font-semibold text-gray-900 dark:text-white">Search Console</span><span class="mt-0.5 block text-xs text-gray-500">Site doğrulaması</span></span>
                </a>
                <a href="#seo-homepage" class="group flex items-center gap-3 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300"><x-heroicon-o-home class="size-5" /></span>
                    <span><span class="block text-sm font-semibold text-gray-900 dark:text-white">Ana sayfa</span><span class="mt-0.5 block text-xs text-gray-500">Başlık ve açıklama</span></span>
                </a>
                <a href="#seo-pages" class="group flex items-center gap-3 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"><x-heroicon-o-rectangle-stack class="size-5" /></span>
                    <span><span class="block text-sm font-semibold text-gray-900 dark:text-white">Diğer sayfalar</span><span class="mt-0.5 block text-xs text-gray-500">Sayfa bazlı kurallar</span></span>
                </a>
            </div>
        </section>

        <div class="rounded-xl border border-blue-200 bg-blue-50/70 px-4 py-3 text-sm leading-6 text-blue-950 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-100">
            <div class="flex gap-3">
                <x-heroicon-o-information-circle class="mt-0.5 size-5 shrink-0" />
                <p><strong>Önemli:</strong> Google meta keywords etiketini sıralama için kullanmaz. Bu nedenle burada gereksiz bir anahtar kelime alanı yok. Yazı, kanal, statik sayfa ve profil SEO bilgileri ilgili içerik düzenleme ekranlarından yönetilir. Mesajlar, favoriler, giriş/kayıt ve kişisel ayarlar gibi özel alanlar noindex kalır.</p>
            </div>
        </div>

        <form wire:submit="save" class="grid gap-5">
            <div id="seo-search-console" class="scroll-mt-24">
                {{ $this->form->getComponent('google_site_verification') ?? '' }}
            </div>
            {{ $this->form }}

            @php
                $previewTitle = trim((string) data_get($this->data, 'home_seo_title')) ?: 'OgraFi – Fikirlerin ve toplulukların buluşma noktası';
                $previewDescription = trim((string) data_get($this->data, 'home_seo_description')) ?: 'OgraFi topluluğunda teknoloji, gündem ve ilgi alanlarını keşfedin; yazılar paylaşın ve tartışmalara katılın.';
                $previewImage = trim((string) data_get($this->data, 'home_seo_og_image'));
            @endphp

            <section id="seo-homepage" class="scroll-mt-24 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900" aria-labelledby="seo-preview-heading">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 id="seo-preview-heading" class="text-base font-semibold text-gray-950 dark:text-white">Google arama sonucu önizlemesi</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Başlık ve açıklama alanlarını düzenledikçe önizleme güncellenir.</p>
                    </div>
                    <span class="text-xs text-gray-400">Yaklaşık görünüm · Google garantisi değildir</span>
                </div>
                <div class="max-w-2xl rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <span class="flex size-8 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">O</span>
                        <div class="min-w-0">
                            <div class="truncate font-medium">OgraFi</div>
                            <div class="truncate text-xs text-gray-500">https://ografi.com/</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xl leading-snug text-[#1a0dab] dark:text-[#8ab4f8]">{{ $previewTitle }} — OgraFi</div>
                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $previewDescription }}</p>
                </div>
                @if ($previewImage !== '')
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Sosyal paylaşım görseli</p>
                        <img src="{{ $previewImage }}" alt="Ana sayfa sosyal paylaşım görseli önizlemesi" loading="lazy" class="max-h-52 max-w-full rounded-lg border border-gray-200 object-contain dark:border-gray-700">
                    </div>
                @endif
            </section>

            <section id="seo-pages" class="scroll-mt-24 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Kaydetmeden önce kontrol</h2>
                <ul class="mt-3 grid gap-2 text-sm leading-6 text-gray-600 dark:text-gray-300 sm:grid-cols-2">
                    <li class="flex gap-2"><x-heroicon-o-check-circle class="mt-1 size-4 shrink-0 text-success-600" /> Her sayfa için benzersiz ve açıklayıcı başlık</li>
                    <li class="flex gap-2"><x-heroicon-o-check-circle class="mt-1 size-4 shrink-0 text-success-600" /> Sayfanın içeriğini doğru özetleyen açıklama</li>
                    <li class="flex gap-2"><x-heroicon-o-check-circle class="mt-1 size-4 shrink-0 text-success-600" /> Doğru canonical adres ve indeksleme tercihi</li>
                    <li class="flex gap-2"><x-heroicon-o-check-circle class="mt-1 size-4 shrink-0 text-success-600" /> Paylaşım görsellerinde 1200 × 630 px önerisi</li>
                </ul>
            </section>

            <div class="sticky bottom-3 z-10 flex justify-end rounded-xl border border-gray-200 bg-white/95 p-3 shadow-sm backdrop-blur dark:border-gray-700 dark:bg-gray-900/95">
                <x-filament::button type="submit" icon="heroicon-o-check">SEO ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
