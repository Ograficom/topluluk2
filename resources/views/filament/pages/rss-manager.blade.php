<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-m-check">
                    Ayarları kaydet
                </x-filament::button>
            </div>
        </form>

        <x-filament::section heading="RSS adresleri" description="Yazar, kanal ve etiket adreslerinde ilgili kaydın slug veya kullanıcı adı kullanılır.">
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($this->feedUrls() as $feed)
                    <div class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $feed['label'] }}</span>
                        <div class="flex items-center gap-2">
                            <code class="text-xs break-all">{{ $feed['url'] }}</code>
                            <x-filament::icon-button
                                icon="heroicon-m-clipboard"
                                color="gray"
                                tooltip="Adresi kopyala"
                                x-on:click="window.navigator.clipboard.writeText(@js($feed['url']))"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
