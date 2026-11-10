<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm leading-6 text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            Bu ekran tek bir genel açıklama alanı değildir: ana sayfa, sabit/liste sayfaları ve genel varsayılanları ayrı ayrı yönetin. Yazı SEO'su yazı düzenleme ekranında; kanal SEO'su kanal düzenleme ekranında; statik sayfaların SEO'su sayfa düzenleme ekranında; kullanıcı profil SEO'su kullanıcı yönetim ekranındadır. Giriş, kayıt, mesajlar, favoriler ve kişisel ayarlar gibi özel alanlar güvenlik ve gizlilik nedeniyle noindex kalır.
        </div>

        <form wire:submit="save" class="grid gap-5">
            {{ $this->form }}

            @php
                $previewTitle = trim((string) data_get($this->data, 'home_seo_title')) ?: 'OgraFi – Fikirlerin ve toplulukların buluşma noktası';
                $previewDescription = trim((string) data_get($this->data, 'home_seo_description')) ?: 'OgraFi topluluğunda teknoloji, gündem ve ilgi alanlarını keşfedin; yazılar paylaşın ve tartışmalara katılın.';
                $previewImage = trim((string) data_get($this->data, 'home_seo_og_image'));
            @endphp

            <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900" aria-labelledby="seo-preview-heading">
                <div class="mb-4">
                    <h2 id="seo-preview-heading" class="text-base font-semibold text-gray-950 dark:text-white">Google arama sonucu önizlemesi</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bu yalnızca yaklaşık bir önizlemedir. Google başlığı ve açıklamayı arama sorgusuna göre değiştirebilir.</p>
                </div>
                <div class="max-w-2xl rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <span class="flex size-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">O</span>
                        <div class="min-w-0">
                            <div class="truncate">OgraFi</div>
                            <div class="truncate text-xs text-gray-500">https://ografi.com/</div>
                        </div>
                    </div>
                    <div class="mt-2 text-xl leading-snug text-[#1a0dab] dark:text-[#8ab4f8]">{{ $previewTitle }} — OgraFi</div>
                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $previewDescription }}</p>
                </div>
                @if ($previewImage !== '')
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Sosyal paylaşım görseli önizlemesi</p>
                        <img src="{{ $previewImage }}" alt="Ana sayfa sosyal paylaşım görseli önizlemesi" loading="lazy" class="max-h-52 max-w-full rounded-lg border border-gray-200 object-contain dark:border-gray-700">
                    </div>
                @endif
            </section>

            <div class="flex justify-end">
                <x-filament::button type="submit">SEO ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
