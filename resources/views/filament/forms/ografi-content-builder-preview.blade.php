@php
    $blocks = $get('builder_content') ?? [];
    $blocks = is_array($blocks) ? $blocks : [];
@endphp

<div class="rounded-lg border border-slate-200 bg-white p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-800">Canlı önizleme</h3>
        <span class="text-xs text-slate-500">Kaydedilmemiş form verisi</span>
    </div>
    @if (class_exists(\VanOns\FilamentContentBuilder\FilamentContentBuilder::class) && $blocks !== [])
        <x-filament-content-builder::block-renderer :blocks="$blocks" />
    @else
        <p class="text-sm text-slate-500">Önizleme için içerik blokları ekleyin. Mevcut EditorJS içeriğiniz değişmeden kalır.</p>
    @endif
</div>
