<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-slate-500">
                Sitemap yolu: <code>/sitemap.xml</code>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="regenerate" type="button" color="secondary">
                    Sitemap Yeniden Olustur
                </x-filament::button>
                <x-filament::button type="submit">
                    Ayarlari Kaydet
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament::page>
