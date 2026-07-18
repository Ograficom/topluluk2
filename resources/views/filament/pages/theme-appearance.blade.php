<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-slate-500">
                Degisiklikler kaydedildikten sonra siteye hemen yansir.
            </div>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="resetToDefaults" type="button" color="gray">
                    Varsayilana Don
                </x-filament::button>
                <x-filament::button type="submit">
                    Ayarlari Kaydet
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament::page>
