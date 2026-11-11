<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">

<form wire:submit="save" class="grid gap-5">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit">SEO ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
