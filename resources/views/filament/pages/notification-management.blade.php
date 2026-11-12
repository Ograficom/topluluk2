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
                <a class="font-medium text-primary-600 hover:underline dark:text-primary-400" href="{{ \App\Filament\Pages\MailSettings::getUrl() }}">SMTP ayarlarını yönet</a>
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
            <form wire:submit="sendAnnouncement" class="notification-announcement-form">
                <div class="notification-announcement-field">
                    <label for="announcementTitle">Duyuru başlığı</label>
                    <input id="announcementTitle" type="text" wire:model="announcementTitle" maxlength="180" required class="notification-input w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Önemli site duyurusu">
                    @error('title') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>
                <div class="notification-announcement-field">
                    <label for="announcementMessage">Duyuru metni</label>
                    <textarea id="announcementMessage" wire:model="announcementMessage" rows="4" maxlength="5000" required class="notification-input w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Kullanıcılara iletilecek duyuru..."></textarea>
                    @error('message') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>
                <div class="notification-announcement-actions">
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
            display: grid;
            gap: 24px;
            color: #172033;
        }

        /* This view has its own layout rules so it does not depend on Tailwind
           generating utility classes from dynamically discovered Blade files. */
        .notification-center-page > .notification-panel {
            box-sizing: border-box;
            padding: 24px;
        }

        .notification-center-page > .notification-table-panel {
            padding: 0;
        }

        .notification-center-page .notification-push-panel {
            box-sizing: border-box;
            padding: 25px 27px;
        }

        .notification-center-page .notification-announcement-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
            margin-top: 22px;
        }

        .notification-center-page .notification-announcement-field {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 8px;
            min-width: 0;
        }

        .notification-center-page .notification-announcement-field > label {
            display: block;
            margin: 0;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
            line-height: 1.5;
        }

        .notification-center-page .notification-announcement-field .notification-input {
            display: block;
            width: 100%;
            max-width: none;
            box-sizing: border-box;
        }

        .notification-center-page .notification-announcement-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-top: 2px;
        }

        .notification-center-page .notification-table-panel > div:first-child {
            padding: 20px 24px;
        }

        .notification-center-page .notification-table th,
        .notification-center-page .notification-table td {
            padding: 13px 20px;
        }

        .notification-center-page .notification-empty {
            padding: 38px 24px;
            text-align: center;
        }

        .notification-center-page > .notification-panel:not(.notification-table-panel) + .notification-panel {
            margin-top: 0;
        }

        .notification-center-page .notification-panel,
        .notification-center-page .notification-section {
            position: relative;
            overflow: hidden;
            border: 1px solid #dce4ef !important;
            border-radius: 16px !important;
            background: #fff;
            box-shadow: 0 3px 12px rgb(15 23 42 / 3%);
        }

        .notification-center-page .notification-panel:hover,
        .notification-center-page .notification-section:hover {
            border-color: #b8c9e2 !important;
            box-shadow: 0 8px 24px rgb(15 23 42 / 6%);
        }

        .notification-center-page .notification-push-panel {
            padding: 25px 27px;
            border-color: #cbdcf4 !important;
            background: linear-gradient(120deg, #f1f6ff 0%, #ffffff 62%, #f6faff 100%);
        }

        .notification-center-page .notification-push-panel::before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #3478ee;
            content: "";
        }

        .notification-center-page .notification-push-panel h2,
        .notification-center-page .notification-section .fi-section-header-heading {
            color: #14243d;
            font-size: 15px;
            font-weight: 750;
            letter-spacing: -.015em;
        }

        .notification-center-page .notification-push-panel p,
        .notification-center-page .notification-section .fi-section-header-description {
            color: #64748b;
            line-height: 1.65;
        }

        .notification-center-page .notification-info {
            border: 1px solid #dce7f7;
            border-radius: 11px;
            background: #edf4ff;
            line-height: 1.75;
            padding: 13px 16px;
        }

        .notification-center-page .notification-info code {
            border: 1px solid #d5e3fa;
            border-radius: 6px;
            background: #fff;
            padding: 3px 7px;
            color: #1d4ed8;
            font-size: .86em;
        }

        .notification-center-page .notification-section {
            border-radius: 16px !important;
        }

        .notification-center-page .notification-section .fi-section-header {
            min-height: 76px;
            padding: 19px 23px;
            border-bottom: 1px solid #e8edf4;
            background: linear-gradient(110deg, #f8faff 0%, #fff 75%);
        }

        .notification-center-page .notification-section .fi-section-header-icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border: 1px solid #d9e6fb;
            border-radius: 11px;
            background: #edf4ff;
            color: #3478ee;
        }

        .notification-center-page .notification-section .fi-section-content {
            padding: 19px 22px 22px;
        }

        .notification-center-page .notification-section .fi-fo-field-wrp {
            min-height: 58px;
            border: 1px solid #e5ebf3;
            border-radius: 11px;
            background: #fbfcfe;
            padding: 13px 15px;
            transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
        }

        .notification-center-page .notification-section .fi-fo-field-wrp:hover {
            border-color: #c6d8f3;
            background: #f7faff;
        }

        .notification-center-page .notification-section .fi-fo-field-wrp-label {
            color: #26364d;
            font-size: 13px;
            font-weight: 650;
        }

        .notification-center-page .notification-section .fi-toggle {
            --toggle-color: #3478ee;
        }

        .notification-center-page .notification-section--general .fi-fo-field-wrp {
            min-height: 68px;
            background: #f5f8ff;
        }

        .notification-center-page .notification-section--channels .fi-section-content,
        .notification-center-page .notification-section--types .fi-section-content {
            display: grid;
            gap: 12px;
        }

        .notification-center-page .notification-input {
            min-height: 46px;
            border: 1px solid #d5deeb;
            border-radius: 10px;
            background-color: #fff;
            padding: 11px 14px;
            color: #111827;
            font-size: 14px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .notification-center-page textarea.notification-input {
            min-height: 120px;
            resize: vertical;
        }

        .notification-center-page .notification-input:focus {
            border-color: #3478ee;
            box-shadow: 0 0 0 4px rgb(52 120 238 / 12%);
        }

        .notification-center-page .notification-save-row {
            border-color: #e3eaf3;
            padding-top: 18px;
        }

        .notification-center-page .notification-save-row button {
            min-height: 43px;
            border-radius: 10px;
            padding-inline: 19px;
            font-weight: 650;
        }

        .notification-center-page .notification-table thead th {
            background: #f5f8fd;
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .notification-center-page .notification-table tbody tr {
            transition: background-color .15s ease;
        }

        .notification-center-page .notification-table tbody tr:hover {
            background: #f2f7ff;
        }

        .notification-center-page .notification-empty {
            background: #f8faff;
        }

        .notification-center-page .notification-section--channels .fi-sc,
        .notification-center-page .notification-section--types .fi-sc {
            gap: 12px;
        }

        @media (max-width: 767px) {
            .notification-center-page {
                gap: 15px;
            }

            .notification-center-page > .notification-panel,
            .notification-center-page .notification-push-panel {
                padding: 17px;
            }

            .notification-center-page > .notification-table-panel {
                padding: 0;
            }

            .notification-center-page .notification-announcement-form {
                gap: 15px;
                margin-top: 17px;
            }

            .notification-center-page .notification-announcement-actions {
                justify-content: stretch;
            }

            .notification-center-page .notification-announcement-actions button {
                width: 100%;
                justify-content: center;
            }

            .notification-center-page .notification-table-panel > div:first-child {
                padding: 16px;
            }

            .notification-center-page .notification-table th,
            .notification-center-page .notification-table td {
                padding: 11px 14px;
            }

            .notification-center-page .notification-panel,
            .notification-center-page .notification-push-panel {
                border-radius: 12px !important;
                padding: 17px;
            }

            .notification-center-page .notification-section .fi-section-header {
                padding: 15px 16px;
            }

            .notification-center-page .notification-section .fi-section-content {
                padding: 15px;
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
        .dark .notification-center-page .notification-section {
            border-color: #37465d !important;
            background: #111c2e;
        }

        .dark .notification-center-page .notification-push-panel {
            border-color: #30496d !important;
            background: linear-gradient(120deg, #14243b 0%, #111c2e 75%);
        }

        .dark .notification-center-page .notification-push-panel h2,
        .dark .notification-center-page .notification-section .fi-section-header-heading {
            color: #f1f5f9;
        }

        .dark .notification-center-page .notification-push-panel p,
        .dark .notification-center-page .notification-section .fi-section-header-description {
            color: #a8b6ca;
        }

        .dark .notification-center-page .notification-info {
            border-color: #344a69;
            background: #1b2d47;
            color: #cbd5e1;
        }

        .dark .notification-center-page .notification-info code {
            border-color: #405a7d;
            background: #13233a;
            color: #93c5fd;
        }

        .dark .notification-center-page .notification-section .fi-section-header {
            border-color: #344258;
            background: linear-gradient(110deg, #18263b 0%, #111c2e 80%);
        }

        .dark .notification-center-page .notification-section .fi-section-header-icon {
            border-color: #344e72;
            background: #1d3556;
            color: #8bb8ff;
        }

        .dark .notification-center-page .notification-section .fi-fo-field-wrp {
            border-color: #344258;
            background: #172438;
        }

        .dark .notification-center-page .notification-section .fi-fo-field-wrp:hover {
            border-color: #49668f;
            background: #1b2c45;
        }

        .dark .notification-center-page .notification-section .fi-fo-field-wrp-label {
            color: #e2e8f0;
        }

        .dark .notification-center-page .notification-section--general .fi-fo-field-wrp {
            background: #1a2b45;
        }

        .dark .notification-center-page .notification-input {
            border-color: #43536b;
            background: #111c2e;
            color: #f9fafb;
        }

        .dark .notification-center-page .notification-save-row {
            border-color: #344258;
        }

        .dark .notification-center-page .notification-table thead th {
            background: #18263b;
            color: #a8b6ca;
        }

        .dark .notification-center-page .notification-table tbody tr:hover {
            background: #1a2b45;
        }

        .dark .notification-center-page .notification-empty {
            background: #172438;
        }
    </style>
</x-filament-panels::page>
