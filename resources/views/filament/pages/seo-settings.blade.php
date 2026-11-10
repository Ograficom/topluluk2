<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm leading-6 text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            Ana sayfanın arama motoru başlığını, açıklamasını ve paylaşım görselini buradan yönetin. Genel varsayılanlar, ayrıca SEO bilgisi girilmemiş herkese açık sayfalarda kullanılır. Her alanı boş bırakırsanız sistemin güvenli varsayılanları çalışır.
        </div>

        <form wire:submit="save" class="grid gap-5">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit">SEO ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
