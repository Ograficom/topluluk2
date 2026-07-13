<x-filament-panels::page>
    <div class="space-y-3">
        <p>Debugbar durumu: <strong>{{ session('debugbar_disabled') ? 'Kapalı' : 'Açık' }}</strong></p>
        <p class="text-sm text-gray-500">Bu ayar yalnızca mevcut tarayıcı oturumunuz için geçerlidir.</p>
        @if (session('status'))
            <div class="rounded bg-success-500/10 text-success-700 px-3 py-2 text-sm">
                {{ session('status') }}
            </div>
        @endif
        <p class="text-sm text-gray-500">Butona tıklayınca sayfa yenilenir ve diğer sayfalarda da geçerli olur.</p>
    </div>
</x-filament-panels::page>

