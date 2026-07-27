@php
    $fieldClass = 'block w-full rounded-2xl border border-input bg-transparent px-3.5 py-2.5 text-sm text-foreground shadow-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring';
    $iconFieldClass = 'block w-full rounded-2xl border border-input bg-transparent py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring';
@endphp

<div id="section-profile" class="settings-card">
    <form wire:submit="updateProfileInformation">
        <div class="settings-card__head">
            <span class="settings-card__icon">
                <iconify-icon icon="lucide:user-round"></iconify-icon>
            </span>
            <div class="min-w-0">
                <h2 class="settings-card__title">Profil</h2>
                <p class="settings-card__desc">Herkese açık görünen profil bilgilerin.</p>
            </div>
        </div>

        <div class="settings-card__body space-y-6">
            {{-- Cover + avatar preview --}}
            <div class="relative">
                <div
                    x-data="{ coverPreview: null }"
                    class="group relative h-36 w-full overflow-hidden rounded-2xl bg-slate-100 sm:h-44"
                >
                    <input type="file" id="cover_photo" class="hidden" accept="image/png,image/jpeg"
                           wire:model.live="cover_photo"
                           x-ref="cover_photo"
                           x-on:change="
                                const reader = new FileReader();
                                reader.onload = (e) => { coverPreview = e.target.result; };
                                reader.readAsDataURL($refs.cover_photo.files[0]);
                           " />

                    <img x-show="coverPreview" x-cloak x-bind:src="coverPreview" alt="Yeni kapak önizleme" class="h-full w-full object-cover">

                    @if ($this->user->cover_photo_path)
                        <img x-show="!coverPreview" src="{{ $this->user->cover_photo_url }}" alt="Kapak görseli" class="h-full w-full object-cover" data-no-shimmer>
                    @else
                        <div x-show="!coverPreview" class="flex h-full w-full items-center justify-center text-sm text-slate-400">
                            Kapak görseli yok
                        </div>
                    @endif

                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-black/0 to-black/0"></div>

                    <div class="absolute right-3 top-3 flex gap-2">
                        <button type="button"
                                class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-800 shadow-sm backdrop-blur transition hover:bg-white"
                                x-on:click.prevent="$refs.cover_photo.click()">
                            <iconify-icon icon="lucide:image-up" style="font-size: 14px;"></iconify-icon>
                            Kapağı değiştir
                        </button>

                        @if ($this->user->cover_photo_path)
                            <button type="button"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-sm backdrop-blur transition hover:bg-white hover:text-red-600"
                                    wire:click="deleteCoverPhoto"
                                    aria-label="Kapak görselini kaldır">
                                <iconify-icon icon="lucide:trash-2" style="font-size: 14px;"></iconify-icon>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Avatar overlapping the cover --}}
                <div
                    x-data="{ photoPreview: null }"
                    class="absolute -bottom-8 left-4 sm:-bottom-9 sm:left-5"
                >
                    <input type="file" id="photo" class="hidden" accept="image/png,image/jpeg"
                           wire:model.live="photo"
                           x-ref="photo"
                           x-on:change="
                                const reader = new FileReader();
                                reader.onload = (e) => { photoPreview = e.target.result; };
                                reader.readAsDataURL($refs.photo.files[0]);
                           " />

                    <div class="relative h-20 w-20 overflow-hidden rounded-full border-4 border-white bg-slate-100 shadow-md sm:h-[5.5rem] sm:w-[5.5rem]">
                        <img x-show="photoPreview" x-cloak x-bind:src="photoPreview" alt="Yeni profil önizleme" class="h-full w-full object-cover">
                        <img x-show="!photoPreview" src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="h-full w-full object-cover" data-no-shimmer>
                    </div>

                    <button type="button"
                            class="absolute -bottom-0.5 -right-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-900 text-white shadow-sm transition hover:bg-slate-700"
                            x-on:click.prevent="$refs.photo.click()"
                            aria-label="Profil fotoğrafını değiştir">
                        <iconify-icon icon="lucide:camera" style="font-size: 14px;"></iconify-icon>
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-11 sm:pt-12">
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs">
                    @if ($this->user->profile_photo_path)
                        <button type="button" class="font-medium text-slate-500 transition hover:text-red-600" wire:click="deleteProfilePhoto">
                            Profil fotoğrafını kaldır
                        </button>
                    @endif
                </div>
            </div>

            <x-input-error for="photo" class="-mt-4" />
            <x-input-error for="cover_photo" />

            {{-- Name + profile type --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="settings-field-label">Ad Soyad</label>
                    <input id="name" type="text" class="{{ $fieldClass }}" wire:model="state.name" required autocomplete="name">
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <label for="profile_type" class="settings-field-label">Profil türü</label>
                    <select id="profile_type" class="{{ $fieldClass }}" wire:model="state.profile_type">
                        <option value="person">Kişi</option>
                        <option value="organization">Kuruluş</option>
                    </select>
                    <x-input-error for="profile_type" class="mt-2" />
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="settings-field-label">E-posta</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                        <iconify-icon icon="lucide:mail" style="font-size: 16px;"></iconify-icon>
                    </span>
                    <input id="email" type="email" class="{{ $iconFieldClass }}" wire:model="state.email" required autocomplete="username">
                </div>
                <x-input-error for="email" class="mt-2" />

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                            <iconify-icon icon="lucide:alert-triangle" style="font-size: 13px;"></iconify-icon>
                            E-posta adresin doğrulanmadı
                        </span>

                        <button type="button" class="text-xs font-medium text-blue-600 underline-offset-2 hover:underline" wire:click.prevent="sendEmailVerification">
                            Doğrulama bağlantısını yeniden gönder
                        </button>
                    </div>

                    @if ($this->verificationLinkSent)
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            E-posta adresine yeni bir doğrulama bağlantısı gönderildi.
                        </p>
                    @endif
                @endif
            </div>

            {{-- Bio --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="bio" class="settings-field-label !mb-0">Biyografi</label>
                    <span class="text-xs text-slate-400" data-bio-count>{{ mb_strlen((string) ($this->state['bio'] ?? '')) }}/2000</span>
                </div>
                <textarea id="bio" rows="3" maxlength="2000" placeholder="Kendinden bahset..." class="{{ $fieldClass }} resize-none" wire:model="state.bio" x-on:input="$el.closest('.settings-card__body').querySelector('[data-bio-count]').textContent = $el.value.length + '/2000'"></textarea>
                <x-input-error for="bio" class="mt-2" />
            </div>

            {{-- Social links --}}
            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">Sosyal bağlantılar</h3>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="social_x" class="settings-field-label">X (Twitter)</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                                <iconify-icon icon="lucide:at-sign" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <input id="social_x" type="text" class="{{ $iconFieldClass }}" wire:model="state.social_x" placeholder="kullanici">
                        </div>
                        <x-input-error for="social_x" class="mt-2" />
                    </div>

                    <div>
                        <label for="social_instagram" class="settings-field-label">Instagram</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                                <iconify-icon icon="lucide:camera" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <input id="social_instagram" type="text" class="{{ $iconFieldClass }}" wire:model="state.social_instagram" placeholder="kullanici">
                        </div>
                        <x-input-error for="social_instagram" class="mt-2" />
                    </div>

                    <div>
                        <label for="social_whatsapp" class="settings-field-label">WhatsApp</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                                <iconify-icon icon="lucide:message-circle" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <input id="social_whatsapp" type="text" class="{{ $iconFieldClass }}" wire:model="state.social_whatsapp" placeholder="+90...">
                        </div>
                        <x-input-error for="social_whatsapp" class="mt-2" />
                    </div>

                    <div>
                        <label for="social_tiktok" class="settings-field-label">TikTok</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                                <iconify-icon icon="lucide:music-2" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <input id="social_tiktok" type="text" class="{{ $iconFieldClass }}" wire:model="state.social_tiktok" placeholder="kullanici">
                        </div>
                        <x-input-error for="social_tiktok" class="mt-2" />
                    </div>

                    <div>
                        <label for="social_facebook" class="settings-field-label">Facebook</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                                <iconify-icon icon="lucide:thumbs-up" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <input id="social_facebook" type="text" class="{{ $iconFieldClass }}" wire:model="state.social_facebook" placeholder="profil veya sayfa URL">
                        </div>
                        <x-input-error for="social_facebook" class="mt-2" />
                    </div>

                    <div>
                        <label for="website_url" class="settings-field-label">Website</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                                <iconify-icon icon="lucide:globe" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <input id="website_url" type="url" class="{{ $iconFieldClass }}" wire:model="state.website_url" placeholder="https://...">
                        </div>
                        <x-input-error for="website_url" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Join date --}}
            <div class="sm:w-1/2 sm:pr-2.5">
                <label for="joined_at" class="settings-field-label">Katılma tarihi</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                        <iconify-icon icon="lucide:calendar-days" style="font-size: 16px;"></iconify-icon>
                    </span>
                    <input id="joined_at" type="date" class="{{ $iconFieldClass }}" wire:model="state.joined_at">
                </div>
                <x-input-error for="joined_at" class="mt-2" />
            </div>

            {{-- Verification --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <div class="flex items-start justify-between gap-4">
                    <label for="is_verified" class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            <iconify-icon icon="lucide:badge-check" style="font-size: 16px;"></iconify-icon>
                        </span>
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Mavi tik / onaylı hesap</span>
                            <span class="mt-0.5 block text-xs text-slate-500">Profilinde doğrulama rozeti göster.</span>
                        </span>
                    </label>

                    <x-ui.switch id="is_verified" wire:model="state.is_verified" :checked="(bool) $this->user->is_verified" />
                </div>
                <x-input-error for="is_verified" class="mt-2" />

                <div class="mt-4 grid grid-cols-1 gap-5 border-t border-slate-200/80 pt-4 sm:grid-cols-2">
                    <div>
                        <label for="verification_badge" class="settings-field-label">Rozet seçimi</label>
                        <select id="verification_badge" class="{{ $fieldClass }}" wire:model="state.verification_badge">
                            <option value="">Yok</option>
                            <option value="blue-check">Mavi tik</option>
                            <option value="gold-check">Altın tik</option>
                            <option value="custom">Özel SVG</option>
                        </select>
                        <x-input-error for="verification_badge" class="mt-2" />
                    </div>

                    <div x-show="$wire.state.verification_badge === 'custom'" x-cloak>
                        <label for="verification_badge_svg" class="settings-field-label">Özel rozet SVG</label>
                        <textarea id="verification_badge_svg" rows="2" placeholder="<svg>..." class="{{ $fieldClass }} resize-none font-mono text-xs" wire:model="state.verification_badge_svg"></textarea>
                    </div>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">SVG kodunu yapıştırabilirsin. Boş bırakılırsa varsayılan kullanılır.</p>
                <x-input-error for="verification_badge_svg" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-5 py-4 sm:px-6">
            <x-action-message class="text-sm text-emerald-600" on="saved">
                Kaydedildi.
            </x-action-message>

            <x-button wire:loading.attr="disabled" wire:target="photo">
                Kaydet
            </x-button>
        </div>
    </form>
</div>
