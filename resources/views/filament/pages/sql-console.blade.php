<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Veritabanı sorgusu</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tek seferde bir SQL sorgusu çalıştırılır. Sonuç ekranı en fazla {{ \App\Services\DatabaseConsoleService::MAX_ROWS }} satır gösterir.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="useExample('tables')" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-200">
                        Tablolar
                    </button>
                    <button type="button" wire:click="useExample('users')" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-200">
                        Son kullanıcılar
                    </button>
                    <button type="button" wire:click="useExample('posts')" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-200">
                        Son gönderiler
                    </button>
                </div>
            </div>

            <textarea
                wire:model.defer="sql"
                rows="12"
                spellcheck="false"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 font-mono text-sm leading-6 text-gray-950 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                placeholder="SELECT * FROM users LIMIT 50;"
            ></textarea>

            <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                    <input
                        type="checkbox"
                        wire:model="confirmWrite"
                        class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900"
                    >
                    <span>
                        <span class="font-semibold">Veri değiştiren sorguyu onaylıyorum.</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">INSERT, UPDATE, DELETE, ALTER, CREATE, DROP, TRUNCATE ve benzeri sorgular için zorunludur.</span>
                    </span>
                </label>

                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="clearConsole"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-200"
                    >
                        Temizle
                    </button>
                    <button
                        type="button"
                        wire:click="runQuery"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="runQuery">Sorguyu çalıştır</span>
                        <span wire:loading wire:target="runQuery">Çalıştırılıyor...</span>
                    </button>
                </div>
            </div>
        </section>

        @if ($errorMessage)
            <section class="rounded-xl border border-danger-300 bg-danger-50 p-4 text-sm text-danger-800 dark:border-danger-800 dark:bg-danger-950/30 dark:text-danger-200">
                <div class="font-semibold">SQL hatası</div>
                <div class="mt-1 break-words font-mono text-xs">{{ $errorMessage }}</div>
            </section>
        @endif

        @if ($elapsedMs !== null)
            <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <span>Süre: <strong>{{ number_format($elapsedMs, 2) }} ms</strong></span>
                    @if ($affected !== null)
                        <span>Etkilenen satır: <strong>{{ $affected }}</strong></span>
                    @else
                        <span>Gösterilen satır: <strong>{{ count($rows) }}</strong></span>
                    @endif
                    @if ($truncated)
                        <span class="font-semibold text-warning-600 dark:text-warning-400">Sonuç {{ \App\Services\DatabaseConsoleService::MAX_ROWS }} satırda kesildi.</span>
                    @endif
                </div>
            </section>
        @endif

        @if ($columns !== [])
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-950">
                            <tr>
                                @foreach ($columns as $column)
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($rows as $row)
                                <tr>
                                    @foreach ($columns as $column)
                                        @php($value = $row[$column] ?? null)
                                        <td class="max-w-md whitespace-pre-wrap break-words px-4 py-3 align-top font-mono text-xs text-gray-700 dark:text-gray-300">
                                            @if ($value === null)
                                                <span class="text-gray-400">NULL</span>
                                            @elseif ($value === '')
                                                <span class="text-gray-400">''</span>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ max(1, count($columns)) }}" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Sorgu sonuç döndürmedi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
