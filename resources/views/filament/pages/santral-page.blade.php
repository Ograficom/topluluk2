<x-filament-panels::page>
    <style>
        .santral-editor-toolbar {\n            display: flex;\n            justify-content: flex-end;\n            align-items: center;\n            width: 100%;\n            margin: 0 0 14px;\n        }\n        .santral-editor-toolbar .fi-btn {\n            width: auto !important;\n            flex: 0 0 auto;\n        }\n        .santral-editor-intro {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
            padding: 20px 22px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
        }
        .santral-editor-intro__title {
            margin: 0;
            color: #111827;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.5;
        }
        .santral-editor-intro__description {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }
        .santral-editor-savebar {
            position: sticky;
            bottom: 12px;
            z-index: 20;
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 4px 18px rgba(15,23,42,.08);
            backdrop-filter: blur(12px);
        }
        .dark .santral-editor-intro {
            border-color: #374151;
            background: #111827;
        }
        .dark .santral-editor-intro__title {
            color: #f9fafb;
        }
        .dark .santral-editor-intro__description {
            color: #9ca3af;
        }
        .dark .santral-editor-savebar {
            border-color: #374151;
            background: rgba(17,24,39,.96);
            box-shadow: 0 4px 18px rgba(0,0,0,.24);
        }
        @media (max-width: 640px) {\n            .santral-editor-toolbar {\n                margin-bottom: 10px;\n            }
            .santral-editor-intro {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px;
            }
            .santral-editor-savebar {
                bottom: 8px;
            }
        }
    </style>

<div class="santral-editor-toolbar">
        <x-filament::button
            tag="a"
            :href="url('/santral')"
            target="_blank"
            color="gray"
            icon="heroicon-o-arrow-top-right-on-square"
            size="sm"
        >
            Sayfayı önizle
        </x-filament::button>
    </div>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="santral-editor-savebar">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Değişiklikleri kaydet
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
