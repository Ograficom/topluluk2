@php
    $editorContent = (string) ($get('content') ?? '');
    $editorJson = $get('content_json') ?? [];
@endphp
<div class="space-y-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
            <strong class="text-sm font-semibold text-gray-800">Gönderi içeriği</strong>
            <span class="text-xs text-gray-500">HTML içerik alanı</span>
        </div>
        <textarea
            wire:model="data.content"
            rows="18"
            class="w-full rounded-lg border border-gray-300 p-3 text-sm leading-6"
            placeholder="Gönderi içeriğini buraya yazın..."
        >{{ $editorContent }}</textarea>
        <p class="mt-2 text-xs text-gray-500">Bu alan gönderi içeriğini kaydeder. EditörJS araçları yüklü değilse de içerik düzenlenebilir.</p>
    </div>
    <input type="hidden" wire:model="data.content_json" value="{{ is_array($editorJson) ? json_encode($editorJson, JSON_UNESCAPED_UNICODE) : $editorJson }}">
</div>
