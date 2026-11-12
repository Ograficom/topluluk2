<x-filament-panels::page>
    <div class="grid gap-5">
        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Push altyapısı</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Arka plan tarayıcı bildirimleri için HTTPS, VAPID anahtarları ve cihaz aboneliği gerekir.</p>
                </div>
                @if ($pushConfigured)
                    <x-filament::badge color="success">VAPID yapılandırıldı</x-filament::badge>
                @else
                    <x-filament::badge color="warning">VAPID yapılandırılmadı</x-filament::badge>
                @endif
            </div>
            <div class="mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                @if ($pushConfigured)
                    VAPID anahtarları bulundu. Kullanıcıların Ayarlar → Bildirim Tercihleri alanından cihazlarını ayrıca kaydetmesi gerekir.
                @else
                    Sunucuda <code>php artisan webpush:generate-keys</code> komutunu çalıştırın ve üretilen anahtarları .env dosyasına ekleyin. Özel anahtar yönetim panelinde gösterilmez.
                @endif
            </div>
        </section>

        <form wire:submit="save" class="grid gap-4">
            {{ $this->form }}
            <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                <x-filament::button type="submit" icon="heroicon-o-check">Bildirim ayarlarını kaydet</x-filament::button>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-5 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Son bildirimler</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kullanıcılara oluşturulan son 50 site içi bildirim.</p>
                </div>
                <x-filament::button color="gray" outlined icon="heroicon-o-arrow-path" wire:click="refreshNotifications">Yenile</x-filament::button>
            </div>

            @if (count($recentNotifications))
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Bildirim</th>
                                <th class="px-5 py-3 font-medium">Alıcı</th>
                                <th class="px-5 py-3 font-medium">Tür</th>
                                <th class="px-5 py-3 font-medium">Durum</th>
                                <th class="px-5 py-3 font-medium">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($recentNotifications as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</div>
                                        <div class="mt-1 max-w-md truncate text-xs text-gray-500">{{ $item['message'] }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="text-gray-800 dark:text-gray-200">{{ $item['recipient'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $item['email'] }}</div>
                                    </td>
                                    <td class="px-5 py-3"><x-filament::badge color="gray">{{ $item['category'] }}</x-filament::badge></td>
                                    <td class="px-5 py-3">
                                        @if ($item['is_read'])
                                            <x-filament::badge color="success">Okundu</x-filament::badge>
                                        @else
                                            <x-filament::badge color="warning">Okunmadı</x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-500">{{ $item['created_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-10 text-center text-sm text-gray-500">Henüz bildirim kaydı bulunmuyor.</div>
            @endif
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Test bildirimi</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Oturum açmış yönetici hesabına test bildirimi gönderir. Kullanıcının güvenlik bildirimi tercihi ve global kanal ayarları uygulanır.</p>
            <div class="mt-4">
                <x-filament::button wire:click="sendTest" icon="heroicon-o-paper-airplane" color="gray">Kendime test bildirimi gönder</x-filament::button>
            </div>
        </section>
    </div>
</x-filament-panels::page>
