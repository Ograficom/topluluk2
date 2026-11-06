<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
            <x-filament::button type="submit" icon="heroicon-m-check">
                Ayarları kaydet
            </x-filament::button>
        </div>
    </form>

    <x-filament::section
        heading="RSS adresleri"
        description="Akış adreslerini kopyalayın veya doğrudan açın."
        icon="heroicon-o-rss"
    >
        <div>
            @foreach ($this->feedUrls() as $index => $feed)
                <div
                    wire:key="rss-url-{{ $index }}"
                    style="display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; padding: 1rem 0; border-bottom: {{ $loop->last ? '0' : '1px solid var(--gray-200)' }};"
                >
                    <div style="min-width: 0; flex: 1 1 auto;">
                        <div style="font-size: .875rem; line-height: 1.35rem; font-weight: 600; color: var(--gray-950);">
                            {{ $feed['label'] }}
                        </div>
                        <code style="display: block; margin-top: .3rem; overflow-wrap: anywhere; font-size: .8125rem; line-height: 1.25rem; color: var(--gray-500);">{{ $feed['url'] }}</code>
                    </div>

                    <div style="display: flex; flex: 0 0 auto; align-items: center; gap: .4rem;">
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

        <p style="margin-top: 1rem; font-size: .75rem; line-height: 1.2rem; color: var(--gray-500);">
            Yazar, kanal ve etiket örneklerindeki <code>{username}</code> ve <code>{slug}</code> alanlarını gerçek değerlerle değiştirin.
        </p>
    </x-filament::section>
</x-filament-panels::page>
