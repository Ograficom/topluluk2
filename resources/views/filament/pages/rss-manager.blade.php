<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-m-check">
                Ayarları kaydet
            </x-filament::button>
        </div>
    </form>

    <x-filament::section
        heading="RSS adresleri"
        description="Kullanmak istediğiniz akış adresini kopyalayın."
        icon="heroicon-o-rss"
    >
        <div class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($this->feedUrls() as $index => $feed)
                <div wire:key="rss-url-{{ $index }}" class="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $feed['label'] }}</p>
                        <code class="mt-1 block break-all text-xs text-gray-500">{{ $feed['url'] }}</code>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <x-filament::icon-button
                            icon="heroicon-o-clipboard"
                            color="gray"
                            tooltip="Adresi kopyala"
                            x-on:click="window.navigator.clipboard.writeText(@js($feed['url']))"
                        />
                        <x-filament::icon-button
                            tag="a"
                            :href="$feed['url']"
                            target="_blank"
                            rel="noopener noreferrer"
                            icon="heroicon-o-arrow-top-right-on-square"
                            color="gray"
                            tooltip="Akışı aç"
                        />
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-xs text-gray-500">
            Yazar, kanal ve etiket örneklerindeki <code>{username}</code> ve <code>{slug}</code> alanlarını gerçek değerlerle değiştirin.
        </p>
    </x-filament::section>
</x-filament-panels::page>
