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

            .auth-link {
                color: #60a5fa !important;
            }

            .auth-link:hover {
                color: #93c5fd !important;
            }

            .auth-footer-box {
                background: #020617 !important;
                border-color: #1e293b !important;
            }

            .auth-status {
                background: rgba(16, 185, 129, .12) !important;
                border-color: rgba(16, 185, 129, .30) !important;
                color: #6ee7b7 !important;
            }

            .auth-error {
                background: rgba(244, 63, 94, .10) !important;
                border-color: rgba(244, 63, 94, .30) !important;
                color: #fda4af !important;
            }

            .auth-error-title {
                color: #fecdd3 !important;
            }
        }
    </style>

    <main class="auth-page h-screen overflow-hidden bg-slate-100 px-4 py-4 text-slate-950">
        <section class="flex h-full w-full items-center justify-center">
            <div class="auth-card w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">

                {{-- LOGO / HEADER --}}
                <div class="mb-6 text-center">
                    <div class="auth-logo-wrap mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white">
                        <x-application-logo class="block h-12 w-auto object-contain" />
                    </div>

                    <div class="auth-badge mb-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 22s8-4 8-10V5l-8-3l-8 3v7c0 6 8 10 8 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Güvenli işlem
                    </div>

                    <h2 class="auth-title text-2xl font-black tracking-tight text-slate-950">
                        {{ __('site.auth.reset_title') }}
                    </h2>

                    <p class="auth-muted mt-2 text-sm leading-6 text-slate-500">
                        {{ __('site.auth.reset_subtitle') }}
                    </p>
                </div>

                {{-- STATUS --}}
                @if (session('status'))
                    <div class="auth-status mb-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- ERRORS --}}
                @if ($errors->any())
                    <div class="auth-error mb-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <div class="auth-error-title mb-2 font-black text-rose-800">
                            Şifre güncellenemedi
                        </div>

                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-3" novalidate>
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                                value="{{ old('email', $request->email) }}"
                                class="auth-input block w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>

                        @error('email')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- YENİ ŞİFRE --}}
                    <div>
                        <label for="password" class="auth-label mb-1.5 block text-sm font-black text-slate-800">
                            {{ __('site.auth.new_password') }}
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
                                id="toggle1"
                                type="button"
                                class="auth-eye-button absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-100"
                                aria-label="{{ __('site.auth.show_password') }}"
                            >
                                <svg id="eyeOpen1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6s10 6 10 6s-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6a3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <svg id="eyeClosed1" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10.7 10.7a2 2 0 0 0 2.6 2.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M9.88 5.18A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.18 3.1M6.61 6.61C3.75 8.54 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 4.38-.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- YENİ ŞİFRE TEKRAR --}}
                    <div>
                        <label for="password_confirmation" class="auth-label mb-1.5 block text-sm font-black text-slate-800">
                            {{ __('site.auth.new_password_repeat') }}
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
                                id="toggle2"
                                type="button"
                                class="auth-eye-button absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-100"
                                aria-label="{{ __('site.auth.show_password') }}"
                            >
                                <svg id="eyeOpen2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6s10 6 10 6s-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6a3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <svg id="eyeClosed2" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10.7 10.7a2 2 0 0 0 2.6 2.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M9.88 5.18A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.18 3.1M6.61 6.61C3.75 8.54 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 4.38-.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>

                        @error('password_confirmation')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- SUBMIT --}}
                    <button
                        type="submit"
                        class="auth-submit inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        {{ __('site.auth.update_password') }}
                    </button>
                </form>

                {{-- FOOTER --}}
                <div class="auth-footer-box mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <a href="{{ route('password.request') }}" class="auth-link font-black text-blue-600 transition hover:text-blue-700">
                            {{ __('site.auth.back') }}
                        </a>

                        <a href="{{ route('login') }}" class="auth-link font-black text-blue-600 transition hover:text-blue-700">
                            {{ __('site.auth.back_to_login') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pass1 = document.getElementById('password');
            const pass2 = document.getElementById('password_confirmation');
            const toggle1 = document.getElementById('toggle1');
            const toggle2 = document.getElementById('toggle2');

            const eyeOpen1 = document.getElementById('eyeOpen1');
            const eyeClosed1 = document.getElementById('eyeClosed1');
            const eyeOpen2 = document.getElementById('eyeOpen2');
            const eyeClosed2 = document.getElementById('eyeClosed2');

            function togglePassword(input, openIcon, closedIcon) {
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                if (openIcon && closedIcon) {
                    openIcon.classList.toggle('hidden', isPassword);
                    closedIcon.classList.toggle('hidden', !isPassword);
                }
            }

            if (toggle1 && pass1) {
                toggle1.addEventListener('click', function () {
                    togglePassword(pass1, eyeOpen1, eyeClosed1);
                });
            }

            if (toggle2 && pass2) {
                toggle2.addEventListener('click', function () {
                    togglePassword(pass2, eyeOpen2, eyeClosed2);
                });
            }
        });
    </script>
</x-guest-layout>