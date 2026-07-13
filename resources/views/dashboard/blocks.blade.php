<x-app-layout>
    <div class="w-full sm:mx-auto sm:max-w-[45rem] sm:px-6 lg:px-8">
        <main class="w-full">
            <div class="relative left-1/2 right-1/2 mb-[calc(7rem+env(safe-area-inset-bottom))] min-h-[70vh] w-screen -translate-x-1/2 bg-white text-gray-900 sm:left-auto sm:right-auto sm:mb-0 sm:w-full sm:translate-x-0 sm:rounded-xl sm:border sm:border-gray-200 sm:shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-4 sm:p-6">
                    <div class="flex min-w-0 items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5">
                        <img
                            src="{{ auth()->user()->profile_photo_url }}"
                            alt="{{ auth()->user()->name }}"
                            class="h-7 w-7 shrink-0 rounded-full object-cover"
                        >

                        <span class="truncate text-sm font-medium text-gray-900">
                            {{ auth()->user()->name }}
                        </span>
                    </div>

                    <span class="shrink-0 text-gray-400">&rsaquo;</span>

                    <a href="{{ route('dashboard') }}" class="shrink-0 text-sm text-gray-500 hover:text-gray-700">
                        Ayarlar
                    </a>

                    <span class="shrink-0 text-gray-400">&rsaquo;</span>

                    <span class="min-w-0 truncate text-sm font-medium text-gray-700">
                        Engellenen kullanıcılar
                    </span>
                </div>

                <div class="px-3 pt-4 pb-6 sm:p-6 sm:pb-8">
                    <div class="flex min-h-[220px] items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-8 text-center">
                        <p class="text-sm leading-6 text-gray-600">
                            Henüz burada gösterilecek bir şey yok
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>