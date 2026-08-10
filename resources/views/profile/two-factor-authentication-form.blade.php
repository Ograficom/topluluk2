<div id="section-2fa" class="settings-card">
    <div class="settings-card__head">
        <span class="settings-card__icon">
            <iconify-icon icon="lucide:shield-check"></iconify-icon>
        </span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="settings-card__title">İki Aşamalı Doğrulama</h2>

                @if ($this->enabled)
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">
                        <iconify-icon icon="lucide:check" style="font-size: 11px;"></iconify-icon>
                        Etkin
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                        Etkin değil
                    </span>
                @endif
            </div>
            <p class="settings-card__desc">
                Etkinleştirildiğinde girişte telefonundaki doğrulayıcı uygulamadan alınan tek kullanımlık bir kod istenir.
            </p>
        </div>
    </div>

    <div class="settings-card__body space-y-5">
        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/70 p-4">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        @if ($showingConfirmation)
                            Kurulumu tamamlamak için aşağıdaki QR kodu doğrulayıcı uygulamanla tara ya da kurulum anahtarını gir ve OTP kodunu doğrula.
                        @else
                            İki aşamalı doğrulama etkinleştirildi. QR kodu doğrulayıcı uygulamanla tara ya da kurulum anahtarını gir.
                        @endif
                    </p>

                    <div class="mt-4 inline-block rounded-2xl border border-slate-200 dark:border-slate-700 bg-white p-3">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="mt-4" x-data="{ copied: false }">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">Kurulum anahtarı</span>
                        <div class="mt-1 flex items-center gap-2">
                            <code x-ref="setupKey" class="truncate rounded-lg bg-white dark:bg-slate-900 px-2.5 py-1.5 font-mono text-xs text-slate-700 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700">{{ decrypt($this->user->two_factor_secret) }}</code>
                            <button type="button"
                                    class="settings-icon-btn inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition"
                                    x-on:click="navigator.clipboard.writeText($refs.setupKey.textContent.trim()); copied = true; setTimeout(() => copied = false, 1500)"
                                    aria-label="Kurulum anahtarını kopyala">
                                <iconify-icon x-bind:icon="copied ? 'lucide:check' : 'lucide:copy'" style="font-size: 14px;"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    @if ($showingConfirmation)
                        <div class="mt-4 max-w-xs">
                            <label for="code" class="settings-field-label">Doğrulama kodu</label>
                            <input id="code" type="text" name="code"
                                   class="block w-full rounded-2xl border border-input bg-white dark:bg-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-center font-mono text-lg tracking-[0.3em] shadow-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring"
                                   inputmode="numeric" autofocus autocomplete="one-time-code" maxlength="6"
                                   wire:model="code"
                                   wire:keydown.enter="confirmTwoFactorAuthentication">
                            <x-input-error for="code" class="mt-2" />
                        </div>
                    @endif
                </div>
            @endif

            @if ($showingRecoveryCodes)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/70 p-4">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        Bu kurtarma kodlarını güvenli bir parola yöneticisinde sakla. Doğrulama cihazına erişimini kaybedersen hesabına tekrar girmek için kullanabilirsin.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 font-mono text-sm sm:grid-cols-2">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div class="text-slate-700 dark:text-slate-300">{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                Doğrulayıcı uygulaman yoksa Google Authenticator, 1Password veya Authy gibi bir uygulama kullanabilirsin.
            </p>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            @if (! $this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-button type="button" wire:loading.attr="disabled">
                        <iconify-icon icon="lucide:shield-plus" style="font-size: 15px;"></iconify-icon>
                        Etkinleştir
                    </x-button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-secondary-button>
                            Kurtarma Kodlarını Yenile
                        </x-secondary-button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <x-button type="button" wire:loading.attr="disabled">
                            Onayla
                        </x-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-secondary-button>
                            Kurtarma Kodlarını Göster
                        </x-secondary-button>
                    </x-confirms-password>
                @endif

                @if ($showingConfirmation)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-secondary-button wire:loading.attr="disabled">
                            İptal
                        </x-secondary-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-danger-button wire:loading.attr="disabled">
                            Devre Dışı Bırak
                        </x-danger-button>
                    </x-confirms-password>
                @endif
            @endif
        </div>
    </div>
</div>
