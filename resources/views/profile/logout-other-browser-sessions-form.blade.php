<div id="section-sessions" class="settings-card">
    <div class="settings-card__head">
        <span class="settings-card__icon">
            <iconify-icon icon="lucide:monitor-smartphone"></iconify-icon>
        </span>
        <div class="min-w-0">
            <h2 class="settings-card__title">Aktif Oturumlar</h2>
            <p class="settings-card__desc">
                Hesabına giriş yapılmış tüm cihazları gör ve gerekirse diğer oturumları kapat.
            </p>
        </div>
    </div>

    <div class="settings-card__body space-y-5">
        @if (count($this->sessions) > 0)
            <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200">
                @foreach ($this->sessions as $session)
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <iconify-icon icon="{{ $session->agent->isDesktop() ? 'lucide:monitor' : 'lucide:smartphone' }}" style="font-size: 17px;"></iconify-icon>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-medium text-slate-800">
                                    {{ $session->agent->platform() ? $session->agent->platform() : __('Unknown') }} · {{ $session->agent->browser() ? $session->agent->browser() : __('Unknown') }}
                                </p>

                                @if ($session->is_current_device)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        Bu cihaz
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $session->ip_address }}
                                @unless ($session->is_current_device)
                                    · Son aktif {{ $session->last_active }}
                                @endunless
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center gap-3">
            <x-button type="button" wire:click="confirmLogout" wire:loading.attr="disabled">
                Diğer Oturumları Kapat
            </x-button>

            <x-action-message class="text-sm text-emerald-600" on="loggedOut">
                Tamamlandı.
            </x-action-message>
        </div>
    </div>

    <x-dialog-modal wire:model.live="confirmingLogout">
        <x-slot name="title">
            Diğer Oturumları Kapat
        </x-slot>

        <x-slot name="content">
            Diğer tüm cihazlardaki oturumlarını kapatmak için lütfen şifreni onayla.

            <div class="mt-4" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                <input type="password" class="block w-full rounded-2xl border border-input bg-transparent px-3.5 py-2.5 text-sm shadow-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring"
                       autocomplete="current-password"
                       placeholder="Şifre"
                       x-ref="password"
                       wire:model="password"
                       wire:keydown.enter="logoutOtherBrowserSessions">

                <x-input-error for="password" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">
                İptal
            </x-secondary-button>

            <x-button class="ms-3"
                        wire:click="logoutOtherBrowserSessions"
                        wire:loading.attr="disabled">
                Diğer Oturumları Kapat
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
