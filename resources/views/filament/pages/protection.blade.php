<x-filament-panels::page>
    <form wire:submit="save" class="protection-form">
        {{ $this->form }}

        <div class="protection-save">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Koruma ayarlarını kaydet
            </x-filament::button>
        </div>
    </form>

    <style>
        .protection-intro {
            display:flex; align-items:flex-start; gap:14px; width:100%;
            margin:0 0 22px; padding:18px 20px; border:1px solid #dbeafe;
            border-radius:12px; background:#f8fbff;
        }
        .protection-intro-icon {
            display:flex; align-items:center; justify-content:center; flex:0 0 42px;
            width:42px; height:42px; border:1px solid #dbeafe; border-radius:10px;
            background:#fff; color:#2563eb;
        }
        .protection-intro-icon svg { width:21px; height:21px; }
        .protection-intro h2 { margin:0; color:#111827; font-size:15px; font-weight:650; }
        .protection-intro p { margin:4px 0 0; color:#64748b; font-size:13px; line-height:1.6; }
        .protection-form { width:100%; }
        .protection-form .fi-tabs {
            width:100% !important; max-width:100%; margin:0 0 20px !important;
            justify-content:flex-start !important;
        }
        .protection-form .fi-tabs nav,
        .protection-form [role="tablist"] {
            justify-content:flex-start !important; margin-inline:0 !important;
        }
        .protection-save {
            display:flex; justify-content:flex-end; width:100%;
            margin-top:20px; padding-top:18px; border-top:1px solid #e5e7eb;
        }
        .dark .protection-intro { border-color:#1e3a5f; background:#111c2e; }
        .dark .protection-intro-icon { border-color:#1e3a5f; background:#172554; color:#93c5fd; }
        .dark .protection-intro h2 { color:#f9fafb; }
        .dark .protection-intro p { color:#9ca3af; }
        .dark .protection-save { border-color:#374151; }
        @media(max-width:640px) {
            .protection-intro { padding:15px; gap:11px; }
            .protection-form .fi-tabs { overflow-x:auto; }
        }
    </style>
</x-filament-panels::page>
