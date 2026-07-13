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

            .auth-google {
                background: #020617 !important;
                border-color: #334155 !important;
                color: #f8fafc !important;
            }

            .auth-google:hover {
                background: #1e293b !important;
                border-color: #475569 !important;
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

            .auth-forgot {
                color: #60a5fa !important;
            }

            .auth-forgot:hover {
                color: #93c5fd !important;
            }

            .auth-register-box {
                background: #020617 !important;
                border-color: #1e293b !important;
            }

            .auth-register-link {
                color: #60a5fa !important;
            }

            .auth-submit {
                background: linear-gradient(135deg, #3b82f6, #06b6d4) !important;
                box-shadow: 0 18px 35px rgba(59, 130, 246, .28) !important;
                color: #fff !important;
            }

            .auth-submit:hover {
                background: linear-gradient(135deg, #60a5fa, #22d3ee) !important;
            }
        }
    </style>

    <main class="auth-page h-screen overflow-hidden bg-slate-100 px-4 py-4 text-slate-950">
        <section class="flex h-full w-full items-center justify-center">
            <div class="auth-card w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">

                {{-- LOGO --}}
                <div class="mb-6 text-center">
                    <div class="auth-logo-wrap mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white">
                        <x-application-logo class="block h-12 w-auto object-contain" />
                    </div>

                    <div class="auth-badge mb-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 22s8-4 8-10V5l-8-3l-8 3v7c0 6 8 10 8 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Güvenli giriş
                    </div>

                    <h2 class="auth-title text-2xl font-black tracking-tight text-slate-950">
                        Hesabına hoş geldin
                    </h2>

                    <p class="auth-muted mt-2 text-sm text-slate-500">
                        Devam etmek için giriş bilgilerini kullan.
                    </p>
                </div>

                {{-- GOOGLE --}}
                <a href="{{ route('social.redirect', 'google') }}"
                   class="auth-google inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                        <path d="M21.8 12.3c0-.7-.1-1.3-.2-2H12v3.8h5.5a4.7 4.7 0 0 1-2 3.1v2.5h3.2c1.9-1.7 3.1-4.3 3.1-7.4Z" fill="#4285F4" />
                        <path d="M12 22c2.7 0 5-1 6.7-2.7l-3.2-2.5c-.9.6-2 1-3.5 1-2.7 0-4.9-1.8-5.7-4.2H3v2.6A10 10 0 0 0 12 22Z" fill="#34A853" />
                        <path d="M6.3 13.6a6 6 0 0 1 0-3.2V7.8H3a10 10 0 0 0 0 8.9l3.3-2.6Z" fill="#FBBC05" />
                        <path d="M12 6.1c1.5 0 2.8.5 3.9 1.5l2.9-2.9A10 10 0 0 0 12 2 10 10 0 0 0 3 7.8l3.3 2.6C7.1 8 9.3 6.1 12 6.1Z" fill="#EA4335" />
                    </svg>
                    Google ile devam et
                </a>

                <div class="relative py-4">
                    <div class="auth-divider-line h-px w-full bg-slate-200"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="auth-divider-text bg-white px-4 text-xs font-bold text-slate-400">veya</span>
                    </div>
                </div>

                {{-- STATUS --}}
                @if (session('status'))
                    <div class="mb-3 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                {{-- ERRORS --}}
                @if ($errors->any())
                    <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <div class="mb-2 font-black text-rose-800">Giriş yapılamadı</div>

                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-3" novalidate>
                    @csrf

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
                        <div class="mb-1.5 flex items-center justify-between gap-3">
                            <label for="password" class="auth-label block text-sm font-black text-slate-800">
                                {{ __('site.auth.password') }}
                            </label>

                            <p id="capsWarning" class="hidden text-xs font-black text-amber-600">
                                {{ __('site.auth.caps_lock_on') }}
                            </p>
                        </div>

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
                                autocomplete="current-password"
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

                    {{-- BENİ HATIRLA / ŞİFREMİ UNUTTUM --}}
                    <div class="flex items-center justify-between gap-3">
                        <label for="rememberMe" class="flex cursor-pointer items-center gap-3 select-none">
                            <span
                                id="rememberTrack"
                                class="relative inline-flex shrink-0 items-center rounded-full transition-all duration-200"
                                style="width: 48px; height: 28px; background-color: #e2e8f0; box-shadow: inset 0 0 0 1px #cbd5e1;"
                            >
                                <input
                                    id="rememberMe"
                                    name="remember"
                                    type="checkbox"
                                    value="1"
                                    class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                    {{ old('remember') ? 'checked' : '' }}
                                >

                                <span
                                    id="rememberDot"
                                    class="absolute rounded-full shadow-md transition-all duration-200"
                                    style="width: 22px; height: 22px; left: 3px; top: 3px; transform: translateX(0); background-color: #ffffff;"
                                ></span>
                            </span>

                            <span class="auth-muted text-sm font-black text-slate-700">
                                {{ __('site.auth.remember_me') }}
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-forgot text-sm font-black text-blue-600 transition hover:text-blue-700">
                                {{ __('site.auth.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    {{-- SUBMIT --}}
                    <button
                        id="submitBtn"
                        type="submit"
                        class="auth-submit inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                        {{ __('site.auth.login_title') }}
                    </button>

                    <p class="auth-muted text-center text-xs font-medium text-slate-400">
                        {!! str_replace(':year', '<span id="year"></span>', __('site.auth.copyright')) !!}
                    </p>
                </form>

                {{-- KAYIT --}}
                <div class="auth-register-box mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-center">
                    <p class="auth-muted text-sm text-slate-500">
                        {{ __('site.auth.no_account') }}
                        <a href="{{ route('register') }}" class="auth-register-link font-black text-blue-600 hover:text-blue-700">
                            {{ __('site.auth.register_cta') }}
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

            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeOpenIcon = document.getElementById('eyeOpenIcon');
            const eyeClosedIcon = document.getElementById('eyeClosedIcon');
            const capsWarning = document.getElementById('capsWarning');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';

                    if (eyeOpenIcon && eyeClosedIcon) {
                        eyeOpenIcon.classList.toggle('hidden', isPassword);
                        eyeClosedIcon.classList.toggle('hidden', !isPassword);
                    }
                });
            }

            if (passwordInput && capsWarning) {
                passwordInput.addEventListener('keyup', function (event) {
                    if (event.getModifierState && event.getModifierState('CapsLock')) {
                        capsWarning.classList.remove('hidden');
                    } else {
                        capsWarning.classList.add('hidden');
                    }
                });

                passwordInput.addEventListener('blur', function () {
                    capsWarning.classList.add('hidden');
                });
            }

            const rememberMe = document.getElementById('rememberMe');
            const rememberTrack = document.getElementById('rememberTrack');
            const rememberDot = document.getElementById('rememberDot');

            function isDarkMode() {
                return window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            function updateRememberVisual() {
                if (!rememberMe || !rememberTrack || !rememberDot) return;

                if (rememberMe.checked) {
                    rememberTrack.style.backgroundColor = '#3b82f6';
                    rememberTrack.style.boxShadow = 'inset 0 0 0 1px #3b82f6';
                    rememberDot.style.transform = 'translateX(20px)';
                    rememberDot.style.backgroundColor = '#ffffff';
                } else {
                    if (isDarkMode()) {
                        rememberTrack.style.backgroundColor = '#1e293b';
                        rememberTrack.style.boxShadow = 'inset 0 0 0 1px #475569';
                        rememberDot.style.backgroundColor = '#94a3b8';
                    } else {
                        rememberTrack.style.backgroundColor = '#e2e8f0';
                        rememberTrack.style.boxShadow = 'inset 0 0 0 1px #cbd5e1';
                        rememberDot.style.backgroundColor = '#ffffff';
                    }

                    rememberDot.style.transform = 'translateX(0)';
                }
            }

            if (rememberMe) {
                updateRememberVisual();
                rememberMe.addEventListener('change', updateRememberVisual);
                rememberMe.addEventListener('click', updateRememberVisual);

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateRememberVisual);
            }
        });
    </script>
</x-guest-layout>