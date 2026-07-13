<x-guest-layout>
    <style>
        @media (prefers-color-scheme: dark) {
            .auth-page {
                background: #020617 !important;
                color: #fff !important;
            }

            .auth-card {
                background: #0f172a !important;
                border-color: #1e293b !important;
                box-shadow: 0 24px 80px rgba(0, 0, 0, .55) !important;
            }

            .auth-logo-wrap {
                background: transparent !important;
            }

            .auth-badge {
                background: rgba(59, 130, 246, .16) !important;
                border-color: rgba(59, 130, 246, .35) !important;
                color: #93c5fd !important;
            }

            .auth-title {
                color: #fff !important;
            }

            .auth-muted {
                color: #94a3b8 !important;
            }

            .auth-divider-line {
                background: #334155 !important;
            }

            .auth-divider-text {
                background: #0f172a !important;
                color: #64748b !important;
            }

            .auth-label {
                color: #e2e8f0 !important;
            }

            .auth-input {
                background: #020617 !important;
                border-color: #334155 !important;
                color: #fff !important;
            }

            .auth-input::placeholder {
                color: #64748b !important;
            }

            .auth-input:hover {
                border-color: #475569 !important;
            }

            .auth-input:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 4px rgba(59, 130, 246, .18) !important;
            }

            .auth-input-icon {
                color: #64748b !important;
            }

            .auth-eye-button {
                background: transparent !important;
                color: #94a3b8 !important;
            }

            .auth-eye-button:hover {
                background: #1e293b !important;
                color: #fff !important;
            }

            .auth-submit {
                background: linear-gradient(135deg, #3b82f6, #06b6d4) !important;
                box-shadow: 0 18px 35px rgba(59, 130, 246, .28) !important;
                color: #fff !important;
            }

            .auth-submit:hover {
                background: linear-gradient(135deg, #60a5fa, #22d3ee) !important;
            }

            .auth-footer-box {
                background: #020617 !important;
                border-color: #1e293b !important;
            }

            .auth-footer-link {
                color: #60a5fa !important;
            }

            .auth-footer-link:hover {
                color: #93c5fd !important;
            }

            .auth-terms-box {
                background: #020617 !important;
                border-color: #1e293b !important;
            }

            .auth-terms-text {
                color: #94a3b8 !important;
            }

            .auth-terms-text a {
                color: #60a5fa !important;
            }
        }
    </style>

    <main class="auth-page h-screen overflow-hidden bg-slate-100 px-4 py-4 text-slate-950">
        <section class="flex h-full w-full items-center justify-center">
            <div class="auth-card w-full max-w-md rounded-3xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">

                {{-- LOGO / HEADER --}}
                <div class="mb-5 text-center">
                    <div class="auth-logo-wrap mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-white">
                        <x-application-logo class="block h-10 w-auto object-contain" />
                    </div>

                    <div class="auth-badge mb-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 22s8-4 8-10V5l-8-3l-8 3v7c0 6 8 10 8 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Güvenli kayıt
                    </div>

                    <h2 class="auth-title text-2xl font-black tracking-tight text-slate-950">
                        {{ __('site.auth.register_title') }}
                    </h2>

                    <p class="auth-muted mt-2 text-sm text-slate-500">
                        {{ __('site.auth.register_subtitle') }}
                    </p>
                </div>

                {{-- STATUS --}}
                @if (session('status'))
                    <div class="mb-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- ERRORS --}}
                @if ($errors->any())
                    <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <div class="mb-2 font-black text-rose-800">Kayıt oluşturulamadı</div>

                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-3" novalidate>
                    @csrf

                    {{-- AD SOYAD --}}
                    <div>
                        <label for="name" class="auth-label mb-1.5 block text-sm font-black text-slate-800">
                            Ad Soyad
                        </label>

                        <div class="relative">
                            <span class="auth-input-icon pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M12 13a5 5 0 1 0 0-10a5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                placeholder="Ad Soyad"
                                value="{{ old('name') }}"
                                class="auth-input block w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>
                    </div>

                    {{-- E-POSTA --}}
                    <div>
                        <label for="email" class="auth-label mb-1.5 block text-sm font-black text-slate-800">
                            E-posta
                        </label>

                        <div class="relative">
                            <span class="auth-input-icon pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="m4 7l8 6l8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                placeholder="ornek@mail.com"
                                value="{{ old('email') }}"
                                class="auth-input block w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>
                    </div>

                    {{-- ŞİFRE --}}
                    <div>
                        <label for="password" class="auth-label mb-1.5 block text-sm font-black text-slate-800">
                            {{ __('site.auth.password') }}
                        </label>

                        <div class="relative">
                            <span class="auth-input-icon pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="auth-input block w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-14 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >

                            <button
                                id="togglePassword"
                                type="button"
                                class="auth-eye-button absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-100"
                                aria-label="{{ __('site.auth.show_password') }}"
                            >
                                <svg id="eyeOpenIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6s10 6 10 6s-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6a3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <svg id="eyeClosedIcon" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10.7 10.7a2 2 0 0 0 2.6 2.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M9.88 5.18A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.18 3.1M6.61 6.61C3.75 8.54 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 4.38-.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- ŞİFRE TEKRAR --}}
                    <div>
                        <label for="password_confirmation" class="auth-label mb-1.5 block text-sm font-black text-slate-800">
                            {{ __('site.auth.password_repeat') }}
                        </label>

                        <div class="relative">
                            <span class="auth-input-icon pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 12l2 2l4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="auth-input block w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-14 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >

                            <button
                                id="toggleConfirm"
                                type="button"
                                class="auth-eye-button absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-100"
                                aria-label="{{ __('site.auth.show_password') }}"
                            >
                                <svg id="confirmEyeOpenIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6s10 6 10 6s-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6a3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <svg id="confirmEyeClosedIcon" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10.7 10.7a2 2 0 0 0 2.6 2.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M9.88 5.18A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.18 3.1M6.61 6.61C3.75 8.54 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 4.38-.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="auth-terms-box rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <label for="terms" class="flex cursor-pointer items-start gap-3">
                                <input
                                    id="terms"
                                    name="terms"
                                    type="checkbox"
                                    required
                                    class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >

                                <span class="auth-terms-text text-xs leading-5 text-slate-600">
                                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                            'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="font-black text-blue-600 hover:underline">'.__('Terms of Service').'</a>',
                                            'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="font-black text-blue-600 hover:underline">'.__('Privacy Policy').'</a>',
                                    ]) !!}
                                </span>
                            </label>
                        </div>
                    @endif

                    {{-- SUBMIT --}}
                    <button
                        id="submitBtn"
                        type="submit"
                        class="auth-submit inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 13a5 5 0 1 0 0-10a5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('site.auth.register_title') }}
                    </button>

                    <p class="auth-muted text-center text-xs font-medium text-slate-400">
                        {!! str_replace(':year', '<span id="year"></span>', __('site.auth.copyright')) !!}
                    </p>
                </form>

                {{-- FOOTER --}}
                <div class="auth-footer-box mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-center">
                    <p class="auth-muted text-sm text-slate-500">
                        {{ __('site.auth.already_registered') }}
                        <a href="{{ route('login') }}" class="auth-footer-link font-black text-blue-600 hover:text-blue-700">
                            {{ __('site.auth.login_cta') }}
                        </a>
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const yearEl = document.getElementById('year');
            if (yearEl) {
                yearEl.textContent = new Date().getFullYear();
            }

            const passEl = document.getElementById('password');
            const confirmEl = document.getElementById('password_confirmation');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirm = document.getElementById('toggleConfirm');

            const eyeOpenIcon = document.getElementById('eyeOpenIcon');
            const eyeClosedIcon = document.getElementById('eyeClosedIcon');
            const confirmEyeOpenIcon = document.getElementById('confirmEyeOpenIcon');
            const confirmEyeClosedIcon = document.getElementById('confirmEyeClosedIcon');

            function toggleInput(input, openIcon, closedIcon) {
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                if (openIcon && closedIcon) {
                    openIcon.classList.toggle('hidden', isPassword);
                    closedIcon.classList.toggle('hidden', !isPassword);
                }
            }

            if (togglePassword && passEl) {
                togglePassword.addEventListener('click', function () {
                    toggleInput(passEl, eyeOpenIcon, eyeClosedIcon);
                });
            }

            if (toggleConfirm && confirmEl) {
                toggleConfirm.addEventListener('click', function () {
                    toggleInput(confirmEl, confirmEyeOpenIcon, confirmEyeClosedIcon);
                });
            }
        });
    </script>
</x-guest-layout>