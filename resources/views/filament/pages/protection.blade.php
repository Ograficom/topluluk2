<x-filament-panels::page>
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ url('/admin/comments') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-4 w-4" />
            Yorum spam listesi
        </a>
        <a href="{{ url('/admin/posts?tableFilters[is_spam][value]=1') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
            Post spam listesi
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}
        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-shield-check">
                Koruma ayarlarını kaydet
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
