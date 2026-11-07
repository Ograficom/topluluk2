@php
    $attachments = $ticket?->attachments ?? [];
    $storage = \Illuminate\Support\Facades\Storage::disk('local');
@endphp

@if (count($attachments))
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,280px),1fr));gap:14px;width:100%;">
        @foreach ($attachments as $index => $path)
            @php
                $exists = $storage->exists($path);
                $fileName = basename($path);
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $mimeType = $exists ? $storage->mimeType($path) : null;
                $isPreviewableImage = $exists && in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
                $fileSize = $exists ? $storage->size($path) : null;
                $sizeLabel = $fileSize !== null
                    ? ($fileSize >= 1048576
                        ? number_format($fileSize / 1048576, 2) . ' MB'
                        : number_format($fileSize / 1024, 1) . ' KB')
                    : 'Dosya bulunamadı';
                $previewUrl = route('support-tickets.attachments.preview', ['supportTicket' => $ticket, 'index' => $index]);
                $downloadUrl = route('support-tickets.attachments.download', ['supportTicket' => $ticket, 'index' => $index]);
            @endphp

            <div style="overflow:hidden;min-width:0;border:1px solid #e5e7eb;border-radius:14px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                @if ($isPreviewableImage)
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $fileName }} görselini ön izle"
                       style="display:flex;align-items:center;justify-content:center;height:170px;padding:8px;background:#f3f6fb;text-decoration:none;">
                        <img src="{{ $previewUrl }}" alt="{{ $fileName }}" loading="lazy"
                             style="display:block;max-width:100%;max-height:154px;object-fit:contain;border-radius:8px;">
                    </a>
                @else
                    <div style="display:flex;align-items:center;justify-content:center;height:170px;background:#f3f6fb;color:#64748b;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <x-filament::icon icon="heroicon-o-document" style="width:42px;height:42px;color:#64748b;" />
                            <span style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">{{ $extension ?: 'DOSYA' }}</span>
                        </div>
                    </div>
                @endif

                <div style="display:flex;align-items:center;gap:10px;padding:12px 13px;">
                    <div style="display:flex;align-items:center;justify-content:center;flex:0 0 36px;width:36px;height:36px;border-radius:10px;background:#eff6ff;color:#2563eb;">
                        <x-filament::icon icon="heroicon-o-paper-clip" style="width:18px;height:18px;" />
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div title="{{ $fileName }}" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:600;color:#111827;">{{ $fileName }}</div>
                        <div style="margin-top:3px;font-size:11px;color:#6b7280;">{{ strtoupper($extension ?: 'DOSYA') }} <span style="padding:0 3px;color:#cbd5e1;">·</span> {{ $sizeLabel }}</div>
                    </div>
                    <a href="{{ $downloadUrl }}" aria-label="{{ $fileName }} dosyasını indir"
                       style="display:inline-flex;align-items:center;justify-content:center;flex:0 0 36px;width:36px;height:36px;border:1px solid #dbeafe;border-radius:10px;background:#eff6ff;color:#2563eb;text-decoration:none;">
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:17px;height:17px;" />
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div style="padding:16px;border:1px dashed #d1d5db;border-radius:12px;background:#f9fafb;color:#6b7280;font-size:13px;">
        Bu talebe dosya eklenmemiş.
    </div>
@endif
