@extends('layouts.app')

@section('title', $pageTitle)
@section('hide_feed_header', '1')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&display=swap');

        .contact-page,
        .contact-page :where(p, span, a, input, textarea, label, button) {
            font-family: "Roboto", Arial, sans-serif !important;
            font-weight: 300 !important;
        }

        .contact-page :where(h1, h2, h3),
        .contact-page .contact-medium,
        .contact-page .contact-form-label,
        .contact-page .contact-submit-button {
            font-family: "Roboto", Arial, sans-serif !important;
            font-weight: 500 !important;
        }

        .contact-section-heading,
        .contact-form-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            padding: 0;
            background: transparent !important;
        }

        .contact-section-heading__icon,
        .contact-form-heading__icon {
            display: inline-flex;
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 20px;
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
        }

        .contact-field {
            width: 100% !important;
            border: 1px solid #d1d5db !important;
            background: #ffffff !important;
            color: #111827 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .contact-field:hover {
            border-color: #9ca3af !important;
            background: #ffffff !important;
        }

        .contact-field:focus,
        .contact-field:focus-visible {
            border-color: #2563eb !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 1px #2563eb !important;
            outline: none !important;
        }

        .contact-consent-row {
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .contact-consent-switch {
            position: relative;
            display: inline-flex;
            width: 34px;
            height: 20px;
            flex: 0 0 34px;
            margin-top: 0;
            border-radius: 999px;
        }

        .contact-consent-switch input {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
            pointer-events: none;
        }

        .contact-consent-switch__track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #e5e7eb;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .contact-consent-switch__track::before {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
            transition: transform 0.2s ease;
        }

        .contact-consent-switch input:checked + .contact-consent-switch__track {
            background: #2563eb;
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.2);
        }

        .contact-consent-switch input:checked + .contact-consent-switch__track::before {
            transform: translateX(14px);
        }

        .contact-consent-switch input:focus-visible + .contact-consent-switch__track {
            outline: 3px solid rgba(37, 99, 235, 0.25);
            outline-offset: 3px;
        }

        .contact-submit-button {
            background: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #2563eb !important;
            box-shadow: none;
        }

        .contact-submit-button:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }

        .contact-submit-button:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.25);
            outline-offset: 3px;
        }

        .contact-submit-button:disabled {
            background: #93c5fd !important;
            color: #ffffff !important;
            border-color: #93c5fd !important;
            cursor: not-allowed;
            opacity: 0.72;
            box-shadow: none !important;
        }

        .contact-submit-button:disabled:hover {
            background: #93c5fd !important;
            border-color: #93c5fd !important;
        }

        html.dark .contact-field {
            border-color: #475569 !important;
            background: transparent !important;
            color: #f8fafc !important;
        }

        html.dark .contact-field:hover {
            border-color: #64748b !important;
            background: transparent !important;
        }

        html.dark .contact-field:focus,
        html.dark .contact-field:focus-visible {
            border-color: #3b82f6 !important;
            background: transparent !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }

        @media (max-width: 640px) {
            .contact-consent-row {
                gap: 9px;
            }

            .contact-consent-switch {
                width: 32px;
                height: 18px;
                flex-basis: 32px;
            }

            .contact-consent-switch__track::before {
                top: 3px;
                left: 3px;
                width: 12px;
                height: 12px;
            }

            .contact-consent-switch input:checked + .contact-consent-switch__track::before {
                transform: translateX(14px);
            }
        }

        html.dark .contact-consent-switch__track {
            background: var(--alma-hover-muted, rgba(30, 41, 59, .82));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
        }

        html.dark .contact-consent-switch input:checked + .contact-consent-switch__track {
            background: #2563eb;
        }
    </style>

    <section class="contact-page mt-4 space-y-4 sm:mt-6">
        @if (session('contact_status'))
            <div class="rounded-[20px] border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400">
                {{ session('contact_status') }}
            </div>
        @endif

        <section class="rounded-[22px] border border-slate-200 bg-white px-5 py-5 dark:border-slate-700 dark:bg-slate-900 sm:px-6 sm:py-6" aria-labelledby="contact-channels-title">
            <div class="mb-5">
                <div class="contact-section-heading">
                    <span class="contact-section-heading__icon" aria-hidden="true">
                        <iconify-icon icon="lucide:messages-square"></iconify-icon>
                    </span>
                    <h1 id="contact-channels-title" class="contact-medium text-xl tracking-[-0.02em] text-slate-950 dark:text-slate-100">
                        Bizimle iletişime geçin
                    </h1>
                </div>
                <p class="mt-1.5 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400 sm:text-[0.95rem]">
                    En geç 48 saat içinde dönüş yapmayı hedefliyoruz ve ihtiyaç duyduğunuz desteği almanızı temenni ediyoruz.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a
                    href="mailto:reklam@ografi.com"
                    class="group flex min-h-[86px] items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-left hover:bg-slate-100 active:bg-slate-200 dark:border-slate-700 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400" aria-hidden="true">
                        <iconify-icon icon="lucide:handshake" class="text-xl"></iconify-icon>
                    </span>
                    <span class="min-w-0">
                        <span class="contact-medium block text-sm leading-5 text-slate-950 dark:text-slate-100">İş birliği ve reklam</span>
                        <span class="mt-0.5 block break-all text-sm text-blue-600 dark:text-blue-400">reklam@ografi.com</span>
                    </span>
                </a>

                <a
                    href="mailto:editor@ografi.com"
                    class="group flex min-h-[86px] items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-left hover:bg-slate-100 active:bg-slate-200 dark:border-slate-700 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400" aria-hidden="true">
                        <iconify-icon icon="lucide:square-pen" class="text-xl"></iconify-icon>
                    </span>
                    <span class="min-w-0">
                        <span class="contact-medium block text-sm leading-5 text-slate-950 dark:text-slate-100">Editör</span>
                        <span class="mt-0.5 block break-all text-sm text-blue-600 dark:text-blue-400">editor@ografi.com</span>
                    </span>
                </a>

                <a
                    href="tel:+908503059806"
                    class="group flex min-h-[86px] items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-left hover:bg-slate-100 active:bg-slate-200 dark:border-slate-700 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400" aria-hidden="true">
                        <iconify-icon icon="lucide:phone" class="text-xl"></iconify-icon>
                    </span>
                    <span class="min-w-0">
                        <span class="contact-medium block text-sm leading-5 text-slate-950 dark:text-slate-100">Telefon</span>
                        <span class="mt-0.5 block text-sm text-blue-600 dark:text-blue-400">08503059806</span>
                    </span>
                </a>

                <a
                    href="mailto:destek@ografi.com"
                    class="group flex min-h-[86px] items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-left hover:bg-slate-100 active:bg-slate-200 dark:border-slate-700 dark:hover:bg-slate-800 dark:active:bg-slate-700"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400" aria-hidden="true">
                        <iconify-icon icon="lucide:shield-check" class="text-xl"></iconify-icon>
                    </span>
                    <span class="min-w-0">
                        <span class="contact-medium block text-sm leading-5 text-slate-950 dark:text-slate-100">Güvenlik ve destek</span>
                        <span class="mt-0.5 block break-all text-sm text-blue-600 dark:text-blue-400">destek@ografi.com</span>
                    </span>
                </a>
            </div>
        </section>

        <div class="overflow-hidden rounded-[22px] border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:px-6">
                <div class="contact-form-heading">
                    <span class="contact-form-heading__icon" aria-hidden="true">
                        <iconify-icon icon="lucide:mail"></iconify-icon>
                    </span>
                    <h2 class="contact-medium text-[1.15rem] tracking-[-0.01em] text-slate-950 dark:text-slate-100">Mesaj gönder</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('contact.store') }}" class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                @csrf

                <div class="space-y-2">
                    <label for="contact-full-name" class="contact-form-label block text-[1rem] text-slate-900 dark:text-slate-100">Ad Soyad</label>
                    <input
                        id="contact-full-name"
                        name="full_name"
                        type="text"
                        value="{{ old('full_name', $user?->name) }}"
                        class="contact-field block h-12 rounded-xl px-4 text-[0.98rem]"
                        autocomplete="name"
                        required
                    >
                    @error('full_name')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="contact-email" class="contact-form-label block text-[1rem] text-slate-900 dark:text-slate-100">E-posta adresi</label>
                    <input
                        id="contact-email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user?->email) }}"
                        class="contact-field block h-12 rounded-xl px-4 text-[0.98rem]"
                        autocomplete="email"
                        required
                    >
                    @error('email')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <label for="contact-subject" class="contact-form-label block text-[1rem] text-slate-900 dark:text-slate-100">Başlık</label>
                    <input
                        id="contact-subject"
                        name="subject"
                        type="text"
                        value="{{ old('subject') }}"
                        class="contact-field block h-12 rounded-xl px-4 text-[0.98rem]"
                        required
                    >
                    @error('subject')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <label for="contact-message" class="contact-form-label block text-[1rem] text-slate-900 dark:text-slate-100">Mesaj</label>
                    <textarea
                        id="contact-message"
                        name="message"
                        rows="7"
                        class="contact-field block min-h-[148px] resize-none overflow-hidden rounded-xl px-4 py-3 text-[0.98rem]"
                        oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                        required
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <label for="contact-consent" class="contact-consent-row text-[0.95rem] text-slate-700 dark:text-slate-300">
                        <span class="contact-consent-switch">
                            <input
                                id="contact-consent"
                                name="consent"
                                type="checkbox"
                                value="1"
                                {{ old('consent') ? 'checked' : '' }}
                                required
                            >
                            <span class="contact-consent-switch__track"></span>
                        </span>

                        <span class="leading-5">
                            Kabul ediyorum
                            @if (\Illuminate\Support\Facades\Route::has('terms.show'))
                                <a href="{{ route('terms.show') }}" target="_blank" rel="noopener" class="contact-medium text-blue-600 hover:text-blue-700">Koşullar</a>
                            @else
                                <span class="contact-medium text-blue-600">Hüküm ve Şartları</span>
                            @endif
                            ile
                            @if (\Illuminate\Support\Facades\Route::has('policy.show'))
                                <a href="{{ route('policy.show') }}" target="_blank" rel="noopener" class="contact-medium text-blue-600 hover:text-blue-700">Gizlilik Politikası</a>
                            @else
                                <span class="contact-medium text-blue-600">Gizlilik Politikasını</span>
                            @endif
                        </span>
                    </label>

                    @error('consent')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1 sm:col-span-2">
                    <button
                        id="contact-submit-button"
                        type="submit"
                        class="contact-submit-button w-full rounded-xl px-6 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5"
                        style="background-color: #2563eb !important; color: #ffffff !important; border: none !important;"
                        onmouseover="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                        onmouseout="this.style.setProperty('background-color', '#2563eb', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                        onmousedown="this.style.setProperty('background-color', '#1e40af', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                        onmouseup="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                        {{ old('consent') ? '' : 'disabled' }}
                        aria-disabled="{{ old('consent') ? 'false' : 'true' }}"
                    >
                        Gönder
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const consentToggle = document.getElementById('contact-consent');
            const submitButton = document.getElementById('contact-submit-button');

            if (!consentToggle || !submitButton) {
                return;
            }

            const syncSubmitState = function () {
                const isAccepted = consentToggle.checked;

                submitButton.disabled = !isAccepted;
                submitButton.setAttribute('aria-disabled', isAccepted ? 'false' : 'true');
            };

            syncSubmitState();
            consentToggle.addEventListener('change', syncSubmitState);
        });
    </script>
@endsection