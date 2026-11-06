<x-filament-panels::page>
    <div>
        <x-filament::section
            heading="RSS akış merkezi"
            description="OgraFi içeriklerini RSS okuyucularına yayınlayın. Genel akışı ve her yazar, kanal veya etiket için ayrı akışları buradan yönetin."
            icon="heroicon-o-rss"
        >
            <p>RSS yayınlarını ve içerik dağıtım ayarlarını bu sayfadan yönetebilirsiniz.</p>
        </x-filament::section>

        <form wire:submit="save">
            {{ $this->form }}

            <div>
                <x-filament::button type="submit" icon="heroicon-m-check">
                    Ayarları kaydet
                </x-filament::button>
            </div>
        </form>

        <x-filament::section
            heading="RSS adresleri"
            description="Aşağıdaki adresleri RSS okuyucunuza ekleyebilir veya doğrudan açabilirsiniz."
            icon="heroicon-o-link"
        >
            @foreach ($this->feedUrls() as $index => $feed)
                <x-filament::section
                    :heading="$feed['label']"
                    :key="'rss-feed-'.$index"
                    collapsible
                    collapsed
                >
                    <p>{{ $feed['url'] }}</p>

                    @switch($index)
                        @case(0)
                            <p>Sitedeki tüm uygun yayınlar.</p>
                            @break
                        @case(1)
                            <p>En son yayınlanan yazılar.</p>
                            @break
                        @case(2)
                            <p>Kullanıcı adına göre yazar akışı. {username} alanını gerçek kullanıcı adıyla değiştirin.</p>
                            @break
                        @case(3)
                            <p>Kanal slug değerine göre akış. {slug} alanını gerçek kanal slug değeriyle değiştirin.</p>
                            @break
                        @default
                            <p>Etiket slug değerine göre akış. {slug} alanını gerçek etiket slug değeriyle değiştirin.</p>
                    @endswitch

                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-o-clipboard"
                        x-on:click="window.navigator.clipboard.writeText(@js($feed['url']))"
                    >
                        Adresi kopyala
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="$feed['url']"
                        target="_blank"
                        color="gray"
                        size="sm"
                        icon="heroicon-o-arrow-top-right-on-square"
                    >
                        Akışı aç
                    </x-filament::button>
                </x-filament::section>
            @endforeach
        </x-filament::section>
    </div>
</x-filament-panels::page>
