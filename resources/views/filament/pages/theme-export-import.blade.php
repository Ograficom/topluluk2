<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">Tema paketleri</x-slot>
        <x-slot name="description">
            Sitenin gorunum ayarlarini (renkler, fontlar, yazi boyutlari, ozel CSS ve yuklu font/logo dosyalari) tek bir
            JSON dosyasi olarak indirip yedekleyebilir, daha sonra ayni dosyayi tekrar yukleyerek geri yukleyebilirsin.
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Temayi Indir</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Su anki tum gorunum ayarlarini (yuklu font ve logo dosyalari dahil) tek bir .json dosyasi olarak
                    bilgisayarina indirir. Buyuk degisiklikler yapmadan once yedek almak icin kullan.
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Tema Yukle</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Daha once indirilmis bir .json tema dosyasini yukleyerek su anki gorunum ayarlarinin yerine
                    uygular. Bu islem geri alinamaz - once mevcut temani indirmen onerilir.
                </p>
            </div>
        </div>

        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Islemleri sayfanin sag ust kosesindeki <span class="font-medium text-gray-950 dark:text-white">Temayi Indir</span>
            ve <span class="font-medium text-gray-950 dark:text-white">Tema Yukle</span> butonlarindan baslatabilirsin.
        </div>
    </x-filament::section>
</x-filament::page>
