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

            .auth-link-card {
                background: #0f172a !important;
                border-color: #334155 !important;
                color: #e2e8f0 !important;
            }

            .auth-link-card:hover {
                background: #1e293b !important;
                border-color: #3b82f6 !important;
                color: #fff !important;
            }

            .auth-link-icon {
                background: rgba(59, 130, 246, .16) !important;
                color: #93c5fd !important;
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
        }
    </style>

    <main class="auth-page min-h-screen bg-slate-100 px-3 py-4 text-slate-950 sm:px-4">
        <section class="flex min-h-[calc(100vh-2rem)] w-full items-center justify-center">
            <div class="auth-card w-full max-w-md rounded-3xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">

                {{-- LOGO / HEADER --}}
                <div class="mb-6 text-center">
                    <div class="auth-logo-wrap mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white sm:h-20 sm:w-20">
                        <x-application-logo class="block h-10 w-auto object-contain sm:h-12" />
                    </div>

                    <div class="auth-badge mb-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 22s8-4 8-10V5l-8-3l-8 3v7c0 6 8 10 8 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Güvenli işlem
                    </div>

                    <h2 class="auth-title text-2xl font-black tracking-tight text-slate-950">
                        {{ __('site.auth.forgot_title') }}
                    </h2>

                    <p class="auth-muted mx-auto mt-2 max-w-xs text-sm leading-6 text-slate-500">
                        {{ __('site.auth.forgot_subtitle') }}
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
                        <div class="mb-2 font-black text-rose-800">
                            İşlem tamamlanamadı
                        </div>

                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-3" novalidate>
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

                        @error('email')
                            <p class="mt-1.5 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- SUBMIT --}}
                    <button
                        id="submitBtn"
                        type="submit"
                        class="auth-submit inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="m4 7l8 6l8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('site.auth.reset_link_send') }}
                    </button>
                </form>

                {{-- FOOTER --}}
                <div class="auth-footer-box mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('login') }}"
                            class="auth-link-card group flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-black text-slate-800 transition duration-200 hover:scale-[1.03] hover:border-blue-200 hover:bg-blue-50 active:scale-[0.98]"
                        >
                            <span class="auth-link-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition duration-200 group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M10 19l-7-7l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </span>

                            <span class="transition duration-200 group-hover:scale-110">
                                Giriş
                            </span>
                        </a>

                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="auth-link-card group flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-black text-slate-800 transition duration-200 hover:scale-[1.03] hover:border-blue-200 hover:bg-blue-50 active:scale-[0.98]"
                            >
                                <span class="auth-link-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition duration-200 group-hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 12l2 2l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M21 12a9 9 0 1 1-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>

                                <span class="transition duration-200 group-hover:scale-110">
                                    Kod
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>