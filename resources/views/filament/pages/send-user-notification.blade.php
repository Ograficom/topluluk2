<x-filament::page>
    <form wire:submit.prevent="send" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit">
                Bildirim Gonder
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
