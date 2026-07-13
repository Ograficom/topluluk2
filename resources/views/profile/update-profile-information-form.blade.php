<x-form-section submit="updateProfileInformation">
    <x-slot name="title"></x-slot>
    <x-slot name="description"></x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        <div x-data="{photoName: null, photoPreview: null, hasCurrent: {{ $this->user->profile_photo_url ? 'true' : 'false' }} }" class="col-span-6 sm:col-span-4">
            @php
                $initials = mb_strtoupper(mb_substr($this->user->name ?? 'K', 0, 2, 'UTF-8'), 'UTF-8');
            @endphp
            <input type="file" id="photo" class="hidden"
                       wire:model.live="photo"
                       x-ref="photo"
                       x-on:change="
                            photoName = $refs.photo.files[0].name;
                            const reader = new FileReader();
                            reader.onload = (e) => { photoPreview = e.target.result; };
                            reader.readAsDataURL($refs.photo.files[0]);
                       " />

            <x-label for="photo" value="Profil fotografi" />

            <div class="mt-2">
                <div class="relative size-20">
                    <div class="absolute inset-0 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center text-slate-500 font-semibold" x-show="photoPreview" x-cloak>
                        <img x-bind:src="photoPreview" alt="Yeni profil" class="h-full w-full object-cover">
                    </div>
                    <div class="absolute inset-0 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center text-slate-500 font-semibold" x-show="!photoPreview && hasCurrent" x-cloak>
                        <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="absolute inset-0 rounded-full overflow-hidden bg-slate-50 flex items-center justify-center text-slate-500 font-semibold" x-show="!photoPreview && !hasCurrent" x-cloak>
                        <span>{{ $initials }}</span>
                    </div>
                    <div class="size-20"></div>
                </div>
            </div>

            <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                Yeni profil foto sec
            </x-secondary-button>

            @if ($this->user->profile_photo_path)
                <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                    Profil foto sil
                </x-secondary-button>
            @endif

            <x-input-error for="photo" class="mt-2" />
        </div>

        <!-- Cover Photo -->
        <div x-data="{coverName: null, coverPreview: null}" class="col-span-6 sm:col-span-4">
            <input type="file" id="cover_photo" class="hidden"
                   wire:model.live="cover_photo"
                   x-ref="cover_photo"
                   x-on:change="
                        coverName = $refs.cover_photo.files[0].name;
                        const reader = new FileReader();
                        reader.onload = (e) => { coverPreview = e.target.result; };
                        reader.readAsDataURL($refs.cover_photo.files[0]);
                   " />

            <x-label for="cover_photo" value="Kapak gorseli" />

            <div class="mt-2" x-show="! coverPreview">
                @if ($this->user->cover_photo_path)
                    <img src="{{ $this->user->cover_photo_url }}" alt="{{ $this->user->name }}" class="rounded-xl w-full max-w-xl h-32 object-cover">
                @else
                    <div class="rounded-xl w-full max-w-xl h-32 bg-slate-100 flex items-center justify-center text-sm text-slate-500">
                        Kapak gorseli yok
                    </div>
                @endif
            </div>

            <div class="mt-2" x-show="coverPreview" style="display: none;">
                <div class="rounded-xl w-full max-w-xl h-32 bg-cover bg-center" x-bind:style="'background-image: url(' + coverPreview + ');'"></div>
            </div>

            <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.cover_photo.click()">
                Yeni kapak sec
            </x-secondary-button>

            @if ($this->user->cover_photo_path)
                <x-secondary-button type="button" class="mt-2" wire:click="deleteCoverPhoto">
                    Kapagi kaldir
                </x-secondary-button>
            @endif

            <x-input-error for="cover_photo" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Profile Type -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="profile_type" value="Profil türü" />
            <select id="profile_type" class="mt-1 block w-full rounded-md shadow-sm" wire:model="state.profile_type">
                <option value="person">Kişi</option>
                <option value="organization">Kuruluş</option>
            </select>
            <x-input-error for="profile_type" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>

        <!-- Bio -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="bio" value="Bio" />
            <textarea id="bio" class="mt-1 block w-full rounded-md shadow-sm" rows="3" wire:model="state.bio"></textarea>
            <x-input-error for="bio" class="mt-2" />
        </div>

        <!-- Social links -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="social_x" value="X" />
            <x-input id="social_x" type="text" class="mt-1 block w-full" wire:model="state.social_x" placeholder="@kullanici" />
            <x-input-error for="social_x" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="social_instagram" value="Instagram" />
            <x-input id="social_instagram" type="text" class="mt-1 block w-full" wire:model="state.social_instagram" placeholder="@kullanici" />
            <x-input-error for="social_instagram" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="social_whatsapp" value="WhatsApp" />
            <x-input id="social_whatsapp" type="text" class="mt-1 block w-full" wire:model="state.social_whatsapp" placeholder="+90..." />
            <x-input-error for="social_whatsapp" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="social_tiktok" value="TikTok" />
            <x-input id="social_tiktok" type="text" class="mt-1 block w-full" wire:model="state.social_tiktok" placeholder="@kullanici" />
            <x-input-error for="social_tiktok" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="social_facebook" value="Facebook" />
            <x-input id="social_facebook" type="text" class="mt-1 block w-full" wire:model="state.social_facebook" placeholder="profil veya sayfa URL" />
            <x-input-error for="social_facebook" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="website_url" value="Website" />
            <x-input id="website_url" type="url" class="mt-1 block w-full" wire:model="state.website_url" placeholder="https://..." />
            <x-input-error for="website_url" class="mt-2" />
        </div>

        <!-- Join date -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="joined_at" value="Katilma tarihi" />
            <x-input id="joined_at" type="date" class="mt-1 block w-full" wire:model="state.joined_at" />
            <x-input-error for="joined_at" class="mt-2" />
        </div>

        <!-- Verification -->
        <div class="col-span-6 sm:col-span-4">
            <div class="flex items-center space-x-2">
                <x-checkbox id="is_verified" wire:model="state.is_verified" />
                <x-label for="is_verified" value="Mavi tik / onayli hesap" />
            </div>
            <x-input-error for="is_verified" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="verification_badge" value="Rozet secimi" />
            <select id="verification_badge" wire:model="state.verification_badge" class="mt-1 block w-full rounded-md shadow-sm">
                <option value="">Yok</option>
                <option value="blue-check">Mavi tik</option>
                <option value="gold-check">Altin tik</option>
                <option value="custom">Ozel SVG</option>
            </select>
            <x-input-error for="verification_badge" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="verification_badge_svg" value="Ozel rozet SVG" />
            <textarea id="verification_badge_svg" class="mt-1 block w-full rounded-md shadow-sm" rows="3" wire:model="state.verification_badge_svg" placeholder="<svg>..."></textarea>
            <p class="text-sm text-gray-500 mt-1">SVG kodunu yapistirabilirsiniz. Bos birakilirsa varsayilan kullanilir.</p>
            <x-input-error for="verification_badge_svg" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>


