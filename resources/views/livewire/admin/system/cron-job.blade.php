<div>
    {{ $this->form }}

    <div class="flex justify-start gap-x-4 px-2 py-5">
        <x-filament::button icon="heroicon-m-arrow-path-rounded-square" color="success" wire:click="generateKey"
            spinner="generateKey"
            loading-delay="long">
            {{ __('Generate key') }}
        </x-filament::button>
        <x-filament::button icon="heroicon-m-trash" color="danger" wire:click="removeKey"
            spinner="removeKey"
            loading-delay="long">
            {{ __('Remove key') }}
        </x-filament::button>

    </div>

    <x-filament-actions::modals />
</div>
