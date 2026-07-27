<div id="section-danger" class="settings-card settings-card--danger">
    <div class="settings-card__head">
        <span class="settings-card__icon">
            <iconify-icon icon="lucide:trash-2"></iconify-icon>
        </span>
        <div class="min-w-0">
            <h2 class="settings-card__title">Hesabı Sil</h2>
            <p class="settings-card__desc">
                Tehlikeli alan — bu işlem geri alınamaz.
            </p>
        </div>
    </div>

    <div class="settings-card__body">
        <p class="text-sm leading-relaxed text-slate-500">
            Hesabın silindiğinde tüm içerik ve verilerin kalıcı olarak silinir. Hesabını silmeden önce saklamak istediğin verileri indirdiğinden emin ol.
        </p>

        <div class="mt-4">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                <iconify-icon icon="lucide:trash-2" style="font-size: 14px;"></iconify-icon>
                Hesabı Sil
            </x-danger-button>
        </div>
    </div>

    <x-dialog-modal wire:model.live="confirmingUserDeletion">
        <x-slot name="title">
            Hesabı Sil
        </x-slot>

        <x-slot name="content">
            Hesabını silmek istediğinden emin misin? Hesabın silindiğinde tüm içerik ve verilerin kalıcı olarak silinir. Onaylamak için lütfen şifreni gir.

            <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                <input type="password" class="block w-full rounded-2xl border border-input bg-transparent px-3.5 py-2.5 text-sm shadow-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring"
                       autocomplete="current-password"
                       placeholder="Şifre"
                       x-ref="password"
                       wire:model="password"
                       wire:keydown.enter="deleteUser">

                <x-input-error for="password" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                İptal
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                Hesabı Sil
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
