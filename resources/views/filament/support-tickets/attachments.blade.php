@php
    $attachments = $ticket?->attachments ?? [];
@endphp

@if (count($attachments))
    <div style="display:grid;gap:10px;width:100%;">
        @foreach ($attachments as $index => $path)
            @php
                $fileName = basename($path);
                $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileSize = \Illuminate\Support\Facades\Storage::disk('local')->exists($path)
                    ? \Illuminate\Support\Facades\Storage::disk('local')->size($path)
                    : null;
                $sizeLabel = $fileSize !== null
                    ? ($fileSize >= 1048576
                        ? number_format($fileSize / 1048576, 2) . ' MB'
                        : number_format($fileSize / 1024, 1) . ' KB')
                    : null;
            @endphp

            <a
                href="{{ route('support-tickets.attachments.download', ['supportTicket' => $ticket, 'index' => $index]) }}"
                aria-label="{{ $fileName }} dosyasını indir"
                style="display:flex;align-items:center;gap:13px;width:100%;box-sizing:border-box;padding:13px 15px;border:1px solid #e5e7eb;border-radius:12px;background:#f9fafb;color:#111827;text-decoration:none;transition:background .15s,border-color .15s;"
                onmouseover="this.style.background='#f3f6fb';this.style.borderColor='#cbd5e1';"
                onmouseout="this.style.background='#f9fafb';this.style.borderColor='#e5e7eb';"
            >
                <span style="display:flex;align-items:center;justify-content:center;flex:0 0 42px;width:42px;height:42px;border-radius:10px;background:#eaf2ff;color:#2563eb;">
                    <x-filament::icon icon="heroicon-o-document" style="width:21px;height:21px;" />
                </span>
                <span style="display:block;flex:1;min-width:0;">
                    <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:600;line-height:1.5;color:#111827;">{{ $fileName }}</span>
                    <span style="display:flex;align-items:center;gap:7px;margin-top:3px;font-size:11px;line-height:1.4;color:#6b7280;">
                        <span style="font-weight:600;letter-spacing:.03em;">{{ $extension ?: 'DOSYA' }}</span>
                        @if ($sizeLabel)
                            <span aria-hidden="true" style="color:#cbd5e1;">•</span>
                            <span>{{ $sizeLabel }}</span>
                        @endif
                    </span>
                </span>
                <span style="display:inline-flex;align-items:center;justify-content:center;gap:6px;flex:0 0 auto;padding:8px 11px;border:1px solid #dbeafe;border-radius:8px;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:600;">
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:15px;height:15px;" />
                    İndir
                </span>
            </a>
        @endforeach
    </div>
@else
    <div style="padding:13px 15px;border:1px dashed #d1d5db;border-radius:12px;background:#f9fafb;color:#6b7280;font-size:13px;">
        Bu talebe dosya eklenmemiş.
    </div>
@endif
