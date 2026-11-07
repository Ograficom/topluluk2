@php
    $attachments = $ticket?->attachments ?? [];
@endphp

@if (count($attachments))
    <div class="space-y-2">
        @foreach ($attachments as $index => $path)
            @php
                $fileName = basename($path);
                $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileSize = \Illuminate\Support\Facades\Storage::disk('local')->exists($path)
                    ? \Illuminate\Support\Facades\Storage::disk('local')->size($path)
                    : null;
            @endphp
            <a
                href="{{ route('support-tickets.attachments.download', ['supportTicket' => $ticket, 'index' => $index]) }}"
                class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-filament::icon icon="heroicon-o-paper-clip" class="h-5 w-5" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-gray-950 dark:text-white">{{ $fileName }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">
                        {{ $extension ?: 'DOSYA' }}@if ($fileSize !== null) · {{ number_format($fileSize / 1024, 1) }} KB @endif
                    </span>
                </span>
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-5 w-5 shrink-0 text-gray-400" />
            </a>
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500">Bu talebe dosya eklenmemiş.</p>
@endif
