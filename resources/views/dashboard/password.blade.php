<x-app-layout>
    <div class="mx-auto w-full max-w-[var(--profile-shell-width)]">
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] min-h-[70vh] w-screen -translate-x-1/2 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-gray-200 dark:sm:border-slate-700 sm:shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-200 dark:border-slate-700 px-4 py-4 sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-gray-100 dark:bg-slate-800 px-3 py-1.5">
                        <img
                            src="{{ auth()->user()->profile_photo_url }}"
                            alt="{{ auth()->user()->name }}"
                            class="h-7 w-7 shrink-0 rounded-full object-cover"
                        >

                        <span class="truncate text-sm font-medium text-gray-900 dark:text-slate-100">
                            {{ auth()->user()->name }}
                        </span>
                    </div>

                    <span class="shrink-0 text-gray-400 dark:text-slate-600">›</span>

                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200">
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-gray-400 dark:text-slate-600">›</span>

                    <span class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-slate-300">
                        Şifre
                    </span>
                </div>

                <div class="px-3 pt-4 pb-6 sm:p-6 sm:pb-8">
                    @if (session('status') === 'password-updated')
                        <div class="mb-4 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
                            Şifre başarıyla güncellendi.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('dashboard.password.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="current_password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Mevcut Şifre
                            </label>

                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Yeni Şifre
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Şifreyi Onayla
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                style="background-color: #2563eb !important; color: #ffffff !important; border: none !important;"
                                onmouseover="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                onmouseout="this.style.setProperty('background-color', '#2563eb', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                onmousedown="this.style.setProperty('background-color', '#1e40af', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                onmouseup="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                                class="w-full rounded-xl px-6 py-3 text-sm font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5"
                            >
                                Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>