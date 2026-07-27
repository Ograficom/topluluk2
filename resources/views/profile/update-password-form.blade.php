@php
    $fieldClass = 'block w-full rounded-2xl border border-input bg-transparent py-2.5 pl-3.5 pr-10 text-sm text-foreground shadow-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring';
@endphp

<div id="section-password" class="settings-card">
    <form wire:submit="updatePassword">
        <div class="settings-card__head">
            <span class="settings-card__icon">
                <iconify-icon icon="lucide:lock"></iconify-icon>
            </span>
            <div class="min-w-0">
                <h2 class="settings-card__title">Şifre</h2>
                <p class="settings-card__desc">Hesabına girişte kullandığın şifreyi güncelle.</p>
            </div>
        </div>

        <div class="settings-card__body space-y-5">
            <div x-data="{ show: false }">
                <label for="current_password" class="settings-field-label">Mevcut şifre</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="current_password" class="{{ $fieldClass }}" wire:model="state.current_password" autocomplete="current-password">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" x-on:click="show = !show" tabindex="-1">
                        <iconify-icon x-bind:icon="show ? 'lucide:eye-off' : 'lucide:eye'" style="font-size: 16px;"></iconify-icon>
                    </button>
                </div>
                <x-input-error for="current_password" class="mt-2" />
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="settings-field-label">Yeni şifre</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" class="{{ $fieldClass }}" wire:model="state.password" autocomplete="new-password">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" x-on:click="show = !show" tabindex="-1">
                        <iconify-icon x-bind:icon="show ? 'lucide:eye-off' : 'lucide:eye'" style="font-size: 16px;"></iconify-icon>
                    </button>
                </div>
                <x-input-error for="password" class="mt-2" />
            </div>

            <div x-data="{ show: false }">
                <label for="password_confirmation" class="settings-field-label">Yeni şifre (tekrar)</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password_confirmation" class="{{ $fieldClass }}" wire:model="state.password_confirmation" autocomplete="new-password">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" x-on:click="show = !show" tabindex="-1">
                        <iconify-icon x-bind:icon="show ? 'lucide:eye-off' : 'lucide:eye'" style="font-size: 16px;"></iconify-icon>
                    </button>
                </div>
                <x-input-error for="password_confirmation" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-5 py-4 sm:px-6">
            <x-action-message class="text-sm text-emerald-600" on="saved">
                Kaydedildi.
            </x-action-message>

            <x-button>
                Kaydet
            </x-button>
        </div>
    </form>
</div>
