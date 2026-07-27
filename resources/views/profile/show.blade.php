@section('title', 'Profili Düzenle')
@section('meta_description', 'Ografi profilini, kapak görselini, biyografini, sosyal bağlantılarını ve hesap güvenliğini yönet.')

<x-app-layout>
    @php
        $editUser = auth()->user();
    @endphp

    <div class="space-y-5 pb-12">
        <div class="flex flex-wrap items-center gap-2 px-1 text-sm">
            <a href="{{ $editUser?->username ? route('users.show', ['user' => $editUser->username]) : route('profile.show') }}"
               class="flex min-w-0 items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 transition-colors hover:bg-slate-200">
                <span class="relative flex h-5 w-5 shrink-0 overflow-hidden rounded-full bg-slate-200">
                    <img src="{{ $editUser?->profile_photo_url }}" alt="{{ $editUser?->name }}" class="h-full w-full object-cover">
                </span>
                <span class="truncate text-sm font-medium text-slate-900">{{ $editUser?->name }}</span>
            </a>

            <iconify-icon icon="lucide:chevron-right" style="font-size: 15px; color: #94a3b8;"></iconify-icon>

            <a href="{{ route('dashboard') }}" class="shrink-0 text-slate-500 transition-colors hover:text-slate-700">
                Ayarlar
            </a>

            <iconify-icon icon="lucide:chevron-right" style="font-size: 15px; color: #94a3b8;"></iconify-icon>

            <span class="min-w-0 truncate font-medium text-slate-900">
                Profili Düzenle
            </span>
        </div>

        <div class="px-1">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Profili Düzenle</h1>
            <p class="mt-1 text-sm text-slate-500">Herkese açık profilini ve hesap güvenliğini buradan yönet.</p>
        </div>

        <div
            x-data="{
                tab: (window.location.hash || '#section-profile').replace('#section-', ''),
                go(name) { this.tab = name; window.location.hash = 'section-' + name; },
            }"
            x-init="window.addEventListener('hashchange', () => { tab = (window.location.hash || '#section-profile').replace('#section-', ''); })"
        >
            <nav class="settings-quicknav" aria-label="Profil ayarları bölümleri">
                <a href="#section-profile" x-on:click.prevent="go('profile')" class="settings-quicknav__pill" x-bind:class="tab === 'profile' && 'is-active'">
                    <iconify-icon icon="lucide:user-round"></iconify-icon>
                    Profil
                </a>
                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    <a href="#section-password" x-on:click.prevent="go('password')" class="settings-quicknav__pill" x-bind:class="tab === 'password' && 'is-active'">
                        <iconify-icon icon="lucide:lock"></iconify-icon>
                        Şifre
                    </a>
                @endif
                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <a href="#section-2fa" x-on:click.prevent="go('2fa')" class="settings-quicknav__pill" x-bind:class="tab === '2fa' && 'is-active'">
                        <iconify-icon icon="lucide:shield-check"></iconify-icon>
                        Güvenlik
                    </a>
                @endif
                <a href="#section-sessions" x-on:click.prevent="go('sessions')" class="settings-quicknav__pill" x-bind:class="tab === 'sessions' && 'is-active'">
                    <iconify-icon icon="lucide:monitor-smartphone"></iconify-icon>
                    Oturumlar
                </a>
                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                    <a href="#section-danger" x-on:click.prevent="go('danger')" class="settings-quicknav__pill settings-quicknav__pill--danger" x-bind:class="tab === 'danger' && 'is-active'">
                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                        Hesabı sil
                    </a>
                @endif
            </nav>

            <div class="mt-5">
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    <div x-show="tab === 'profile'">
                        @livewire('profile.update-profile-information-form')
                    </div>
                @endif

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    <div x-show="tab === 'password'">
                        @livewire('profile.update-password-form')
                    </div>
                @endif

                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <div x-show="tab === '2fa'">
                        @livewire('profile.two-factor-authentication-form')
                    </div>
                @endif

                <div x-show="tab === 'sessions'">
                    @livewire('profile.logout-other-browser-sessions-form')
                </div>

                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                    <div x-show="tab === 'danger'">
                        @livewire('profile.delete-user-form')
                    </div>
                @endif
            </div>
        </div>
    </div>

    @once
        <style>
            .settings-quicknav {
                display: flex;
                align-items: center;
                gap: 8px;
                overflow-x: auto;
                padding: 2px 4px 6px;
                scrollbar-width: none;
            }

            .settings-quicknav::-webkit-scrollbar {
                display: none;
            }

            .settings-quicknav__pill {
                display: inline-flex;
                flex-shrink: 0;
                align-items: center;
                gap: 6px;
                border-radius: 999px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                background: #ffffff;
                padding: 7px 14px;
                font-size: 13px;
                font-weight: 500;
                color: #475569;
                transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
            }

            .settings-quicknav__pill iconify-icon {
                font-size: 15px;
                color: #94a3b8;
            }

            .settings-quicknav__pill:hover {
                background: #f1f5f9;
                color: #0f172a;
                border-color: rgba(15, 23, 42, 0.12);
            }

            .settings-quicknav__pill--danger {
                color: #b91c1c;
            }

            .settings-quicknav__pill--danger iconify-icon {
                color: #ef4444;
            }

            .settings-quicknav__pill--danger:hover {
                background: #fef2f2;
                color: #991b1b;
                border-color: rgba(239, 68, 68, 0.25);
            }

            .settings-quicknav__pill.is-active {
                background: #2563eb;
                border-color: #2563eb;
                color: #ffffff;
            }

            .settings-quicknav__pill.is-active iconify-icon {
                color: #ffffff;
            }

            .settings-quicknav__pill--danger.is-active {
                background: #dc2626;
                border-color: #dc2626;
                color: #ffffff;
            }

            .settings-quicknav__pill--danger.is-active iconify-icon {
                color: #ffffff;
            }

            .settings-card {
                border-radius: 20px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                background: #ffffff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                scroll-margin-top: 88px;
            }

            .settings-card__head {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 20px 20px 0;
            }

            @media (min-width: 640px) {
                .settings-card__head {
                    padding: 24px 24px 0;
                }
            }

            .settings-card__icon {
                display: inline-flex;
                height: 38px;
                width: 38px;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: #eff6ff;
                color: #2563eb;
                font-size: 18px;
            }

            .settings-card--danger {
                border-color: rgba(239, 68, 68, 0.22);
            }

            .settings-card--danger .settings-card__icon {
                background: #fef2f2;
                color: #dc2626;
            }

            .settings-card--danger .settings-card__title {
                color: #b91c1c;
            }

            .settings-card__title {
                font-size: 15px;
                font-weight: 600;
                color: #0f172a;
                letter-spacing: -0.01em;
            }

            .settings-card__desc {
                margin-top: 2px;
                font-size: 13px;
                line-height: 1.5;
                color: #64748b;
            }

            .settings-card__body {
                padding: 16px 20px 20px;
            }

            @media (min-width: 640px) {
                .settings-card__body {
                    padding: 18px 24px 24px;
                }
            }

            .settings-field-label {
                display: block;
                margin-bottom: 6px;
                font-size: 13px;
                font-weight: 500;
                color: #334155;
            }

            .settings-status-banner {
                display: flex;
                align-items: center;
                gap: 8px;
                border-radius: 12px;
                padding: 10px 14px;
                font-size: 13px;
                font-weight: 500;
            }

            .settings-status-banner--success {
                background: #ecfdf5;
                color: #027a48;
                border: 1px solid rgba(2, 122, 72, 0.16);
            }
        </style>
    @endonce
</x-app-layout>
