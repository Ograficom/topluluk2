<x-install-layout>
    <h2 class="text-xl font-semibold">Gereksinimler</h2>
    <p class="mt-1 text-sm text-slate-400">Sistem gereksinimlerini ve izinleri kontrol edin.</p>

    <div class="mt-6 space-y-6">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-widest text-slate-400">PHP Eklentileri</h3>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($requirements as $item)
                    <li class="flex items-center justify-between rounded-lg px-3 py-2">
                        <span>{{ $item['label'] }}</span>
                        <span class="{{ $item['passed'] ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $item['passed'] ? 'Tamam' : 'Eksik' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-widest text-slate-400">Izinler</h3>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($permissions as $item)
                    <li class="flex items-center justify-between rounded-lg px-3 py-2">
                        <span>{{ $item['label'] }}</span>
                        <span class="{{ $item['passed'] ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $item['passed'] ? 'Tamam' : 'Eksik' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-8 flex items-center justify-between">
        <div class="text-xs text-slate-400">
            {{ $allPassed ? 'Tum kontroller basariyla gecti.' : 'Devam etmeden once eksik olanlari duzeltin.' }}
        </div>
        @if ($allPassed)
            <a href="{{ route('install.database') }}" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">
                Devam Et
            </a>
        @else
            <button class="cursor-not-allowed rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-slate-400" disabled>
                Devam Et
            </button>
        @endif
    </div>
</x-install-layout>

