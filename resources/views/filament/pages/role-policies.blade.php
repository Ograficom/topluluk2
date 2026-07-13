<x-filament::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-base font-semibold text-slate-900">Sabit rol mantigi</h2>
            <p class="mt-2 text-sm text-slate-600">
                Ilk kayit olan kullanici admin olur. Sonraki kayitlar varsayilan olarak yazar acilir.
                Admin paneline sadece admin rolu girer. Editor, yazar ve banned rolleri panel disinda tutulur.
            </p>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            @foreach ($this->getRoleCards() as $card)
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-900">{{ $card['label'] }}</h3>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ count($card['rules']) ? count($card['rules']) . ' sabit kisit' : 'Tam erisim' }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">{{ $card['description'] }}</p>

                    @if (count($card['rules']))
                        <ul class="mt-4 space-y-2 text-sm text-slate-700">
                            @foreach ($card['rules'] as $rule)
                                <li class="rounded-xl bg-slate-50 px-3 py-2">{{ $rule }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                            Bu rolde sabit bir kisit yok.
                        </div>
                    @endif
                </section>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-base font-semibold text-slate-900">Ek kisitlamalar</h2>
            <p class="mt-2 text-sm text-slate-600">
                Kullanici kartindaki togglelar role ek olarak ek yasaklar uygular. Banned rolunde bunlar otomatik acik kalir.
            </p>
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->getExtraRestrictions() as $restriction)
                    <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700">
                        {{ $restriction }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament::page>
