@extends('layouts.app')

@section('title', 'Dış bağlantı')
@section('meta_description', 'Ografi dış bağlantı geçiş sayfası.')

@section('content')
    <section class="mx-auto w-full max-w-[calc(100vw-4px)] px-0.5 py-8 sm:max-w-xl sm:px-4 sm:py-10">
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 sm:p-5">
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white dark:bg-transparent">
                    <img
                        src="https://ografi.com//storage/app/tasar%C4%B1m/S42.svg"
                        alt="Ografi dış bağlantı görseli"
                        class="h-10 w-10 object-contain"
                        loading="lazy"
                        decoding="async"
                    >
                </span>

                <div class="min-w-0">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Dış siteye gidiyorsun</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Ografi güvenli geçiş uyarısı</p>
                </div>
            </div>

            <h1 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">
                Ografi dışında bir bağlantı
            </h1>

            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Bu bağlantı seni
                <span class="font-medium text-slate-950 dark:text-white">{{ $targetHost }}</span>
                adresine götürecek.
            </p>

            <div class="mt-4">
                <div class="flex items-stretch overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <div
                        id="external-target-url"
                        class="min-w-0 flex-1 break-all px-3 py-2 text-sm text-slate-700 dark:text-slate-200"
                    >
                        {{ $targetUrl }}
                    </div>

                    <button
                        type="button"
                        data-copy-external-url
                        data-url="{{ $targetUrl }}"
                        aria-label="Bağlantıyı kopyala"
                        title="Bağlantıyı kopyala"
                        class="inline-flex w-11 shrink-0 items-center justify-center border-l border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <rect x="9" y="9" width="10" height="10" rx="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </button>
                </div>

                <p
                    data-copy-external-message
                    class="mt-2 hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-200"
                >
                    Kopyalandı, güvenli tarayıcıda açmayı unutmayın.
                </p>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <a
                    href="{{ $targetUrl }}"
                    data-external-bridge="off"
                    rel="nofollow noopener noreferrer"
                    style="background-color:#0e7c86 !important; color:#ffffff !important; border-color:#0e7c86 !important;"
                    class="inline-flex items-center justify-center rounded-lg border border-blue-600 !bg-blue-600 px-4 py-2 text-sm font-medium !text-white shadow-none transition hover:!bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-blue-500 dark:!bg-blue-500 dark:!text-white dark:hover:!bg-blue-600 dark:focus:ring-blue-400 dark:focus:ring-offset-slate-900"
                >
                    Siteye git
                </a>

                <button
                    type="button"
                    data-go-back
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700 dark:focus:ring-slate-600"
                >
                    Geri dön
                </button>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 sm:p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-950 dark:text-white">
                Bağlantı güvenliği hakkında bilgilendirme
            </h2>

            <div class="space-y-2">
                <details class="group rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                        Bu sayfa bağlantı taraması yapmaz
                        <span class="shrink-0 text-slate-500 transition group-open:rotate-180 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-slate-200 px-3 pb-3 pt-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Ografi bu bağlantıyı otomatik olarak virüs, zararlı yazılım veya sahte site açısından taramaz. Bu ekran sadece dış bağlantıya geçmeden önce gösterilen bir uyarı sayfasıdır.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                        Her bağlantının sorumluluğu paylaşan kullanıcıya aittir
                        <span class="shrink-0 text-slate-500 transition group-open:rotate-180 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-slate-200 px-3 pb-3 pt-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Dış bağlantıların içeriği, güvenliği ve doğruluğu Ografi tarafından garanti edilmez. Bağlantıyı paylaşan kullanıcı veya ilgili kaynak kendi bağlantısından sorumludur.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                        Linkler yanıltıcı olabilir
                        <span class="shrink-0 text-slate-500 transition group-open:rotate-180 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-slate-200 px-3 pb-3 pt-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Bazı bağlantılar gerçek siteye benzer görünebilir, kısaltılmış olabilir veya farklı bir adrese yönlendirebilir. Alan adını, yazım hatalarını ve şüpheli karakterleri kontrol etmeden devam etmeyiniz.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                        Linkleri güvenlik araçlarıyla kontrol ediniz
                        <span class="shrink-0 text-slate-500 transition group-open:rotate-180 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-slate-200 px-3 pb-3 pt-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Bağlantıya gitmeden önce linki güvenilir virüs tarayıcıları, site güvenlik kontrol araçları veya tarayıcınızın güvenlik özellikleriyle kontrol etmeniz önerilir.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                        Kişisel bilgilerinizi dikkatli paylaşın
                        <span class="shrink-0 text-slate-500 transition group-open:rotate-180 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-slate-200 px-3 pb-3 pt-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Şüpheli görünen sitelerde şifre, ödeme bilgisi, kimlik bilgisi veya kişisel veri paylaşmayınız. Giriş yapmanız istenirse adres çubuğundaki alan adını dikkatlice kontrol ediniz.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                        Şüpheli bağlantıları bize bildirin
                        <span class="shrink-0 text-slate-500 transition group-open:rotate-180 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </summary>
                    <div class="border-t border-slate-200 px-3 pb-3 pt-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Bağlantı dolandırıcılık, zararlı yazılım, yanıltıcı yönlendirme veya izinsiz içerik gibi görünüyorsa lütfen bize bildirin. Güvenli değilse devam etmek yerine geri dönmeniz önerilir.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <script>
        (() => {

            const backButton = document.querySelector('[data-go-back]');

            if (backButton) {
                backButton.addEventListener('click', () => {
                    if (window.history.length > 1) {
                        window.history.back();
                        return;
                    }

                    window.location.href = "{{ route('home') }}";
                });
            }

            const button = document.querySelector('[data-copy-external-url]');
            const message = document.querySelector('[data-copy-external-message]');

            if (!button || !message) return;

            let timer = null;

            const showMessage = () => {
                message.classList.remove('hidden');

                window.clearTimeout(timer);
                timer = window.setTimeout(() => {
                    message.classList.add('hidden');
                }, 3200);
            };

            const fallbackCopy = (text) => {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.top = '-9999px';
                textarea.style.left = '-9999px';

                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
            };

            button.addEventListener('click', async () => {
                const url = button.dataset.url || '';

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        fallbackCopy(url);
                    }

                    showMessage();
                } catch (error) {
                    fallbackCopy(url);
                    showMessage();
                }
            });
        })();
    </script>
@endsection
