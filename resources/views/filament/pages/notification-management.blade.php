<x-filament-panels::page>
    <div class="notification-center-page grid gap-5">
        <section class="notification-panel notification-push-panel rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
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
            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                <span class="rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800">Kayıtlı cihaz: <strong>{{ number_format($registeredDevicesCount) }}</strong></span>
                <a class="font-medium text-primary-600 hover:underline dark:text-primary-400" href="{{ \\App\\Filament\\Pages\\MailSettings::getUrl() }}">SMTP ayarlarını yönet</a>
            </div>
            <div class="notification-info mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                @if ($pushConfigured)
                    VAPID anahtarları bulundu. Kullanıcıların Ayarlar → Bildirim Tercihleri alanından cihazlarını ayrıca kaydetmesi gerekir.
                @else
                    Sunucuda <code>php artisan webpush:generate-keys</code> komutunu çalıştırın ve üretilen anahtarları .env dosyasına ekleyin. Özel anahtar yönetim panelinde gösterilmez.
                @endif
            </div>
        </section>

        <form wire:submit="save" class="notification-settings-form grid gap-4">
            {{ $this->form }}
            <div class="notification-save-row flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                <x-filament::button type="submit" icon="heroicon-o-check">Bildirim ayarlarını kaydet</x-filament::button>
            </div>
        </form>

        <section class="notification-panel rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <div class="notification-section-heading">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Site duyurusu gönder</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tüm kullanıcılara duyuru bildirimi oluşturur. E-posta ve Push gönderimleri, sistem ayarları ile kullanıcıların kendi tercihlerine göre yapılır.</p>
            </div>
            <form wire:submit="sendAnnouncement" class="mt-5 grid gap-4">
                <div>
                    <label for="announcementTitle" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Duyuru başlığı</label>
                    <input id="announcementTitle" type="text" wire:model="announcementTitle" maxlength="180" required class="notification-input w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Önemli site duyurusu">
                    @error('title') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="announcementMessage" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Duyuru metni</label>
                    <textarea id="announcementMessage" wire:model="announcementMessage" rows="4" maxlength="5000" required class="notification-input w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Kullanıcılara iletilecek duyuru..."></textarea>
                    @error('message') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-megaphone" wire:confirm="Duyuruyu tüm kullanıcılara göndermek istediğinize emin misiniz?">Duyuruyu gönder</x-filament::button>
                </div>
            </form>
        </section>

        <section class="notification-panel notification-table-panel overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-5 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Son bildirimler</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kullanıcılara oluşturulan son 50 site içi bildirim.</p>
                </div>
                <x-filament::button color="gray" outlined icon="heroicon-o-arrow-path" wire:click="refreshNotifications">Yenile</x-filament::button>
            </div>

            @if (count($recentNotifications))
                <div class="overflow-x-auto">
                    <table class="notification-table w-full min-w-[760px] text-left text-sm">
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
                <div class="notification-empty p-10 text-center text-sm text-gray-500">Henüz bildirim kaydı bulunmuyor.</div>
            @endif
        </section>

        <section class="notification-panel rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Test bildirimi</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Oturum açmış yönetici hesabına test bildirimi gönderir. Kullanıcının güvenlik bildirimi tercihi ve global kanal ayarları uygulanır.</p>
            <div class="mt-4">
                <x-filament::button wire:click="sendTest" icon="heroicon-o-paper-airplane" color="gray">Kendime test bildirimi gönder</x-filament::button>
            </div>
        </section>
    </div>

    <style>
        .notification-center-page {
            color: #111827;
        }

        .notification-center-page .notification-panel {
            border-color: #e5e7eb;
            border-radius: 14px;
            transition: border-color .18s ease;
        }

        .notification-center-page .notification-panel:hover {
            border-color: #cbd5e1;
        }

        .notification-center-page .notification-push-panel {
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        }

        .notification-center-page .notification-info {
            border: 1px solid #e8edf5;
            line-height: 1.7;
        }

        .notification-center-page .notification-info code {
            border-radius: 5px;
            background: #e9eef7;
            padding: 2px 6px;
            color: #1d4ed8;
            font-size: .85em;
        }

        .notification-center-page .fi-section {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: none;
        }

        .notification-center-page .fi-section-header {
            padding: 18px 22px;
        }

        .notification-center-page .fi-section-content {
            padding: 20px 22px;
        }

        .notification-center-page .fi-fo-field-wrp-label {
            font-weight: 600;
            color: #263449;
        }

        .notification-center-page .fi-toggle {
            --toggle-color: #2563eb;
        }

        .notification-center-page .notification-input {
            min-height: 44px;
            border: 1px solid #d7deea;
            border-radius: 9px;
            background-color: #fff;
            padding: 10px 13px;
            color: #111827;
            font-size: 14px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .notification-center-page textarea.notification-input {
            min-height: 112px;
            resize: vertical;
        }

        .notification-center-page .notification-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgb(59 130 246 / 12%);
        }

        .notification-center-page .notification-save-row {
            border-color: #e5e7eb;
        }

        .notification-center-page .notification-table thead th {
            letter-spacing: .025em;
            white-space: nowrap;
        }

        .notification-center-page .notification-table tbody tr {
            transition: background-color .15s ease;
        }

        .notification-center-page .notification-table tbody tr:hover {
            background: #f8fafc;
        }

        .notification-center-page .notification-empty {
            background: #fafbfc;
        }

        @media (max-width: 767px) {
            .notification-center-page {
                gap: 14px;
            }

            .notification-center-page .notification-panel {
                border-radius: 11px;
                padding: 16px;
            }

            .notification-center-page .fi-section-header {
                padding: 15px 16px;
            }

            .notification-center-page .fi-section-content {
                padding: 16px;
            }

            .notification-center-page .notification-save-row {
                justify-content: stretch;
            }

            .notification-center-page .notification-save-row button {
                width: 100%;
                justify-content: center;
            }
        }

        .dark .notification-center-page {
            color: #f3f4f6;
        }

        .dark .notification-center-page .notification-panel,
        .dark .notification-center-page .fi-section {
            border-color: #374151;
        }

        .dark .notification-center-page .notification-push-panel {
            background: linear-gradient(135deg, #111827 0%, #17243a 100%);
        }

        .dark .notification-center-page .notification-info,
        .dark .notification-center-page .notification-empty {
            border-color: #374151;
            background: #1f2937;
        }

        .dark .notification-center-page .notification-info code {
            background: #263750;
            color: #93c5fd;
        }

        .dark .notification-center-page .fi-fo-field-wrp-label {
            color: #e5e7eb;
        }

        .dark .notification-center-page .notification-input {
            border-color: #4b5563;
            background: #111827;
            color: #f9fafb;
        }

        .dark .notification-center-page .notification-table tbody tr:hover {
            background: #1f2937;
        }

        .dark .notification-center-page .notification-save-row {
            border-color: #374151;
        }
    </style>
</x-filament-panels::page>
