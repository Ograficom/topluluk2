<x-guest-layout>
    <main class="min-h-screen bg-zinc-100 px-4 py-8 text-zinc-950">
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-md items-center justify-center">
            <section class="w-full rounded-lg border border-zinc-200 bg-white p-6" aria-labelledby="device-verification-title">
                <div class="mb-6">
                    <h1 id="device-verification-title" class="text-lg font-semibold text-zinc-950">
                        Yeni cihaz doğrulaması
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-zinc-600">
                        E-postana gönderilen 6 haneli doğrulama kodunu gir. Kod 10 dakika geçerlidir.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('social.device.verify.submit') }}" novalidate>
                    @csrf

                    <div class="fixed left-[-10000px] top-[-10000px] h-px w-px overflow-hidden opacity-0" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="device_verification_code" class="mb-2 block text-sm font-medium text-zinc-800">
                            Doğrulama kodu
                        </label>
                        <input
                            id="device_verification_code"
                            name="device_verification_code"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            autocomplete="one-time-code"
                            autofocus
                            class="block h-11 w-full rounded-md border border-zinc-300 bg-white px-3 text-base tracking-[0.3em] text-zinc-950 outline-none focus:border-zinc-500"
                            value="{{ old('device_verification_code') }}"
                            aria-describedby="device-verification-help"
                        >
                        <p id="device-verification-help" class="mt-2 text-xs leading-5 text-zinc-500">
                            Kod gelmediyse giriş işlemini yeniden başlat; yeni bir kod gönderilir.
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="mt-6 h-11 w-full rounded-md bg-zinc-950 px-4 text-sm font-medium text-white"
                    >
                        Cihazı doğrula
                    </button>
                </form>

                <div class="mt-5 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-zinc-600 underline underline-offset-4">
                        Giriş ekranına dön
                    </a>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
