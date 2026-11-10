<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm leading-6 text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            Bu ekran tek bir genel açıklama alanı değildir: ana sayfa, sabit/liste sayfaları ve genel varsayılanları ayrı ayrı yönetin. Yazı SEO'su yazı düzenleme ekranında; kanal SEO'su kanal düzenleme ekranında; statik sayfaların SEO'su sayfa düzenleme ekranında; kullanıcı profil SEO'su kullanıcı yönetim ekranındadır. Giriş, kayıt, mesajlar, favoriler ve kişisel ayarlar gibi özel alanlar güvenlik ve gizlilik nedeniyle noindex kalır.
        </div>

        <form wire:submit="save" class="grid gap-5">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit">SEO ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
