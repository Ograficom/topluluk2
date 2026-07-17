@extends('layouts.app')

@section('title', $pageTitle)
@section('hide_feed_header', '1')

@section('content')
    <style>
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
            background: #0e7c86;
            box-shadow: inset 0 0 0 1px rgba(14, 124, 134, 0.2);
        }

        .contact-consent-switch input:checked + .contact-consent-switch__track::before {
            transform: translateX(14px);
        }

        .contact-consent-switch input:focus-visible + .contact-consent-switch__track {
            outline: 3px solid rgba(14, 124, 134, 0.25);
            outline-offset: 3px;
        }



        .contact-submit-button {
            background: #0e7c86 !important;
            color: #ffffff !important;
            border: 1px solid #0e7c86 !important;
            box-shadow: none;
        }

        .contact-submit-button:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }

        .contact-submit-button:focus-visible {
            outline: 3px solid rgba(14, 124, 134, 0.25);
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

        @media (prefers-color-scheme: dark) {
            .contact-consent-switch__track {
                background: #334155;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
            }

            .contact-consent-switch input:checked + .contact-consent-switch__track {
                background: #0e7c86;
            }
        }
    </style>

    <section class="mt-4 space-y-4 sm:mt-6">
        @if (session('contact_status'))
            <div class="rounded-[20px] border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700">
                {{ session('contact_status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-[22px] bg-white shadow-[0_20px_50px_rgba(15,23,42,0.05)]">
            <div class="border-b border-slate-200 px-6 py-4">
                <h1 class="text-[1.7rem] font-semibold tracking-[-0.02em] text-slate-950">Bize ulaşın</h1>
            </div>

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-5 px-6 py-5">
                @csrf

                <div class="space-y-2">
                    <label for="contact-full-name" class="block text-[1rem] font-semibold text-slate-900">Ad Soyad</label>
                    <input
                        id="contact-full-name"
                        name="full_name"
                        type="text"
                        value="{{ old('full_name', $user?->name) }}"
                        class="block h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-[0.98rem] text-slate-900 outline-none transition focus:border-slate-400"
                        required
                    >
                    @error('full_name')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="contact-email" class="block text-[1rem] font-semibold text-slate-900">E-posta</label>
                    <input
                        id="contact-email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user?->email) }}"
                        class="block h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-[0.98rem] text-slate-900 outline-none transition focus:border-slate-400"
                        required
                    >
                    @error('email')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="contact-subject" class="block text-[1rem] font-semibold text-slate-900">Konu</label>
                    <input
                        id="contact-subject"
                        name="subject"
                        type="text"
                        value="{{ old('subject') }}"
                        class="block h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-[0.98rem] text-slate-900 outline-none transition focus:border-slate-400"
                        required
                    >
                    @error('subject')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
    <label for="contact-message" class="block text-[1rem] font-semibold text-slate-900">Mesaj</label>
    <textarea
        id="contact-message"
        name="message"
        rows="7"
        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-[0.98rem] text-slate-900 outline-none transition focus:border-slate-400 overflow-hidden resize-none"
        oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
        required
    >{{ old('message') }}</textarea>
    @error('message')
        <p class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>

                <div class="space-y-2">
                    <label for="contact-consent" class="contact-consent-row text-[0.95rem] text-slate-700">
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
                                <a href="{{ route('terms.show') }}" target="_blank" rel="noopener" class="font-semibold text-blue-600 hover:text-blue-700">Kosullar</a>
                            @else
                                <span class="font-semibold text-blue-600">Hüküm ve Şartları</span>
                            @endif
                            ile
                            @if (\Illuminate\Support\Facades\Route::has('policy.show'))
                                <a href="{{ route('policy.show') }}" target="_blank" rel="noopener" class="font-semibold text-blue-600 hover:text-blue-700">Gizlilik Politikasi</a>
                            @else
                                <span class="font-semibold text-blue-600">Gizlilik Politikasını</span>
                            @endif
                        </span>
                    </label>

                    @error('consent')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <button
                        id="contact-submit-button"
                        type="submit"
                        class="contact-submit-button w-full rounded-xl px-6 py-3 text-sm font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5"
                        style="background-color: #0e7c86 !important; color: #ffffff !important; border: none !important;"
                        onmouseover="this.style.setProperty('background-color', '#1d4ed8', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
                        onmouseout="this.style.setProperty('background-color', '#0e7c86', 'important'); this.style.setProperty('color', '#ffffff', 'important');"
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
