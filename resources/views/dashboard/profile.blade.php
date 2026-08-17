@section('title', 'Profil Ayarları')
@section('meta_description', 'Ografi profil ayarları sayfasında biyografi, konum, iş, eğitim, web sitesi ve sosyal medya bilgilerini güncelle.')

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
                        Profil
                    </span>
                </div>

                <div class="px-3 pt-4 pb-6 sm:p-6 sm:pb-8">
                    @if (session('status') === 'profile-updated')
                        <div class="mb-4 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
                            Profil bilgileri güncellendi.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('dashboard.profile.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <label for="bio" class="block text-sm font-medium text-gray-900 dark:text-slate-100">
                                    Biyografi
                                </label>

                                <span class="shrink-0 text-xs text-gray-500 dark:text-slate-400">
                                    <span id="bioCount">{{ mb_strlen((string) old('bio', auth()->user()->bio)) }}</span>/160
                                </span>
                            </div>

                            <textarea
                                id="bio"
                                name="bio"
                                maxlength="160"
                                rows="3"
                                class="w-full resize-none rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Hakkımda"
                            >{{ old('bio', auth()->user()->bio) }}</textarea>
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="location" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Konum
                            </label>

                            <input
                                id="location"
                                name="location"
                                type="text"
                                value="{{ old('location', auth()->user()->location) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Konum"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="company" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                İş
                            </label>

                            <input
                                id="company"
                                name="company"
                                type="text"
                                value="{{ old('company', auth()->user()->company) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="İş"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="profile_type" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Profil türü
                            </label>

                            <select
                                id="profile_type"
                                name="profile_type"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500"
                            >
                                @php
                                    $profileTypeValue = old('profile_type', auth()->user()->profile_type ?? 'person');
                                @endphp

                                <option value="person" {{ $profileTypeValue === 'person' ? 'selected' : '' }}>
                                    Kişi
                                </option>

                                <option value="organization" {{ $profileTypeValue === 'organization' ? 'selected' : '' }}>
                                    Kuruluş
                                </option>
                            </select>
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="education" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Eğitim
                            </label>

                            <input
                                id="education"
                                name="education"
                                type="text"
                                value="{{ old('education', auth()->user()->education) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Eğitim"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="website_url" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Web sitesi
                            </label>

                            <input
                                id="website_url"
                                name="website_url"
                                type="url"
                                value="{{ old('website_url', auth()->user()->website_url) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Web sitesi"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="social_facebook" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Facebook
                            </label>

                            <input
                                id="social_facebook"
                                name="social_facebook"
                                type="text"
                                value="{{ old('social_facebook', auth()->user()->social_facebook) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Facebook"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="social_x" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Twitter
                            </label>

                            <input
                                id="social_x"
                                name="social_x"
                                type="text"
                                value="{{ old('social_x', auth()->user()->social_x) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Twitter"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="social_instagram" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                Instagram
                            </label>

                            <input
                                id="social_instagram"
                                name="social_instagram"
                                type="text"
                                value="{{ old('social_instagram', auth()->user()->social_instagram) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Instagram"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="social_tiktok" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                TikTok
                            </label>

                            <input
                                id="social_tiktok"
                                name="social_tiktok"
                                type="text"
                                value="{{ old('social_tiktok', auth()->user()->social_tiktok) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="TikTok"
                            >
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-4">
                            <label for="social_youtube" class="mb-2 block text-sm font-medium text-gray-900 dark:text-slate-100">
                                YouTube
                            </label>

                            <input
                                id="social_youtube"
                                name="social_youtube"
                                type="text"
                                value="{{ old('social_youtube', auth()->user()->social_youtube) }}"
                                class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="YouTube"
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bio = document.getElementById('bio');
            const bioCount = document.getElementById('bioCount');

            if (!bio || !bioCount) {
                return;
            }

            const refreshCount = () => {
                bioCount.textContent = bio.value.length.toString();
            };

            bio.addEventListener('input', refreshCount);
            refreshCount();
        });
    </script>
</x-app-layout>