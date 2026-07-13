@section('title', 'Ayarlar')
@section('meta_description', 'Ografi ayarlar sayfasında hesap, şifre, profil, tercihler, bildirimler, engellenenler, rozetler ve güvenlik seçeneklerini yönetin.')

<x-app-layout>
    @php
        $currentUser = auth()->user();
        $profileName = $currentUser?->name ?? $currentUser?->username ?? 'Kullanici';
        $profileUrl = $currentUser?->username
            ? route('users.show', ['user' => $currentUser->username])
            : '#';

        $avatarUrl = $currentUser?->profile_photo_url
            ?? $currentUser?->avatar
            ?? $currentUser?->photo
            ?? null;

        $initial = strtoupper(mb_substr($profileName, 0, 1));

        $dashboardIcon = function (string $name, string $class = 'h-6 w-6'): string {
            $icons = [
                'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
                'account' => '<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4"/><path d="M16 11h5"/><path d="M18.5 8.5v5"/>',
                'password' => '<rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="M12 15v2"/>',
                'badge' => '<path d="M7.5 4.5h9l2 3.5-6.5 11-6.5-11z"/><path d="M9 8h6"/><path d="M10.5 12h3"/>',
                'tune' => '<path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/>',
                'notifications' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
                'block' => '<circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 14.14 14.14"/>',
                'verified' => '<path d="M12 3 9.9 5.1 7 4.6 6.5 7.5 4 9l1.5 2.5L4 14l2.5 1.5.5 2.9 2.9-.5L12 21l2.1-3.1 2.9.5.5-2.9L20 14l-1.5-2.5L20 9l-2.5-1.5-.5-2.9-2.9.5z"/><path d="m9 12 2 2 4-4"/>',
                'security' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="M9.5 12 11 13.5 15 9.5"/>',
                'delete' => '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            ];

            return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? $icons['account']) . '</svg>';
        };
    @endphp

    <div class="w-full sm:mx-auto sm:max-w-[45rem] sm:px-6 lg:px-8">
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 w-screen -translate-x-1/2 bg-white text-gray-900 dark:bg-white dark:text-slate-900 sm:left-auto sm:right-auto sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-slate-200 sm:shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-4 dark:border-gray-700 sm:p-6">
                    <a class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 transition-colors hover:bg-gray-200 dark:bg-gray-700/50 dark:hover:bg-gray-600/60" href="{{ $profileUrl }}">
                        @if($avatarUrl)
                            <img alt="{{ $profileName }}" class="h-5 w-5 rounded-full object-cover" src="{{ $avatarUrl }}" />
                        @else
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-[10px] font-medium text-gray-700">
                                {{ $initial }}
                            </span>
                        @endif

                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $profileName }}
                        </span>
                    </a>

                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {!! $dashboardIcon('chevron-right', 'inline-block h-4 w-4 align-middle') !!}
                    </span>

                    <span class="font-medium text-gray-900 dark:text-white">
                        Ayarlar
                    </span>
                </div>

                <div class="space-y-1 px-2 pt-2 pb-[calc(8.5rem+env(safe-area-inset-bottom))] sm:px-4 sm:pt-3 sm:pb-8">
                    <a class="group flex items-start gap-4 rounded-xl bg-gray-50 px-3 py-5 transition-colors dark:bg-gray-800/80" href="{{ route('dashboard.account') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('account') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Hesap
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Hesap adınızı, kullanıcı adınızı ve e-posta adresinizi güncelleyin
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.password') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('password') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Şifre
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Hesap Şifrenizi güncelleyin
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.profile') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('badge') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Profil
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Biyografi, konum, web sitesi ve diger bilgiler
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.preferences') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('tune') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Tercihler
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                NSFW ve baglanti tercihleri
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.notifications') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('notifications') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Bildirimler
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Hesap bildirimlerinizi güncelleyin
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.blocks') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('block') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Engellenenler
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Engellediginiz kullanıcıların listesi
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.badges') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('verified') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                Rozetler
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Kazandiginiz rozetlerin listesi
                            </p>
                        </div>
                    </a>

                    <a class="group flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/80" href="{{ route('dashboard.two-factor') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-emerald-600 dark:text-gray-400">
                            {!! $dashboardIcon('security') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 dark:text-white">
                                İki Aşamalı Dogrulama
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Ek güvenlik önlemleri etkinleştirin
                            </p>
                        </div>
                    </a>

                    <a class="group mt-2 flex items-start gap-4 rounded-xl px-3 py-5 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20" href="{{ route('dashboard.delete-account') }}">
                        <div class="mt-1 shrink-0 text-gray-500 transition-colors group-hover:text-red-500 dark:text-gray-400">
                            {!! $dashboardIcon('delete') !!}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[15px] font-medium leading-6 text-gray-900 transition-colors group-hover:text-red-500 dark:text-white">
                                Hesabi Sil
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Tehlikeli alan, bu islem geri alinmaz
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>