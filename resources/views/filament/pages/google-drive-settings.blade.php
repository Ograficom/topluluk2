<x-filament-panels::page>
    @if (session('status')) <x-filament::section><p class="text-sm text-success-600">{{ session('status') }}</p></x-filament::section> @endif
    @if ($errors->has('drive')) <x-filament::section><p class="text-sm text-danger-600">{{ $errors->first('drive') }}</p></x-filament::section> @endif
    <x-filament::section heading="Google Drive bağlantısı" description="Kişisel Google hesabını OgraFi'ye bağla.">
        <div class="space-y-3 text-sm">
            <div class="flex items-center justify-between"><span>Bağlantı durumu</span>
                @if ($connection?->isConnected()) <x-filament::badge color="success">Bağlı</x-filament::badge> @else <x-filament::badge color="warning">Bağlı değil</x-filament::badge> @endif
            </div>
            @if ($connection?->isConnected())
                <p><strong>Hesap:</strong> {{ $connection->account_email }}</p>
                <p><strong>Ana klasör ID:</strong> <code>{{ $connection->root_folder_id }}</code></p>
                <form method="POST" action="{{ route('admin.google-drive.disconnect') }}">@csrf @method('DELETE')
                    <x-filament::button color="danger" type="submit">Bağlantıyı kaldır</x-filament::button>
                </form>
            @else
                <p class="text-gray-500">Bağlantı sırasında Drive içinde OGRAFI_STORAGE klasörü oluşturulur.</p>
                <a href="{{ route('admin.google-drive.connect') }}"><x-filament::button icon="heroicon-o-cloud-arrow-up">Google hesabını bağla</x-filament::button></a>
            @endif
        </div>
    </x-filament::section>
    <x-filament::section heading="Depolama güvenliği">
        <ul class="list-disc space-y-1 ps-5 text-sm text-gray-600">
            <li>Mevcut local/public dosyalar değiştirilmez.</li><li>Canlı MariaDB sunucuda kalır; yedekleri Drive'a aktarılır.</li>
            <li>OAuth tokenları Laravel şifrelemesiyle saklanır. APP_KEY korunmalıdır.</li><li>Test modundaki OAuth izinleri kalıcı üretim bağlantısı için uygun olmayabilir.</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
