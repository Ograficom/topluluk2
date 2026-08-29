@extends('layouts.app')

@section('title', __('post_create.page_title'))

@section('hide_global_header')
@endsection

@section('no_container_padding')
@endsection

@section('page_background_class', 'bg-[#f3f5f8]')
@section('hide_feed_header')
@endsection
@section('hide_mobile_bottom_nav')
@endsection

@section('content')
    @php
        $user = auth()->user();
        $initialCategoryId = (int) old('category_id');
        $selectedCategory = collect($categories)->firstWhere('id', $initialCategoryId);
        $categoryPalette = ['#2563eb', '#0ea5e9', '#14b8a6', '#8b5cf6', '#ec4899', '#f97316', '#ef4444', '#22c55e'];
    @endphp

    <style>
        :root {
            --create-bg: #f3f5f8;
            --create-surface: rgba(255, 255, 255, .78);
            --create-surface-strong: rgba(255, 255, 255, .94);
            --create-surface-soft: rgba(248, 250, 252, .78);
            --create-border: rgba(15, 23, 42, .12);
            --create-border-soft: rgba(15, 23, 42, .08);
            --create-text: #0f172a;
            --create-muted: #64748b;
            --create-faint: #94a3b8;
            --create-accent: #2563eb;
            --create-accent-strong: #1d4ed8;
            --create-control: rgba(255, 255, 255, .82);
            --create-control-hover: rgba(241, 245, 249, .94);
            --create-input: rgba(248, 250, 252, .86);
            --create-overlay: rgba(15, 23, 42, .42);
            --create-highlight: rgba(255, 255, 255, .72);
            --create-shadow: 0 2px 10px rgba(15, 23, 42, .06);
        }

        html.dark,
        html[data-theme="dark"],
        html[data-system-theme="dark"] {
            --create-bg: #08101f;
            --create-surface: rgba(15, 23, 42, .72);
            --create-surface-strong: rgba(15, 23, 42, .9);
            --create-surface-soft: rgba(15, 23, 42, .58);
            --create-border: rgba(148, 163, 184, .22);
            --create-border-soft: rgba(148, 163, 184, .14);
            --create-text: #f8fafc;
            --create-muted: #a8b3c5;
            --create-faint: #718096;
            --create-accent: #3b82f6;
            --create-accent-strong: #2563eb;
            --create-control: rgba(30, 41, 59, .72);
            --create-control-hover: rgba(51, 65, 85, .82);
            --create-input: rgba(15, 23, 42, .72);
            --create-overlay: rgba(2, 6, 23, .7);
            --create-highlight: rgba(255, 255, 255, .14);
            --create-shadow: 0 2px 10px rgba(0, 0, 0, .18);
        }

        html,
        body {
            overflow: hidden;
        }

        body > aside,
        body > nav,
        .sidebar,
        .left-sidebar,
        [data-sidebar],
        [data-left-sidebar],
        [data-app-sidebar],
        [data-feed-sidebar],
        [data-right-sidebar],
        [data-comments-sidebar] {
            display: none !important;
        }

        .create-page-fixed {
            position: fixed;
            inset: 0;
            z-index: 99999;
            overflow-x: hidden;
            overflow-y: auto;
            background: var(--create-bg);
            color: var(--create-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .create-glass,
        .create-glass-strong {
            border: .5px solid var(--create-border);
            box-shadow: var(--create-shadow), inset 0 1px 0 var(--create-highlight);
            backdrop-filter: blur(28px) saturate(135%);
            -webkit-backdrop-filter: blur(28px) saturate(135%);
        }

        .create-glass { background: var(--create-surface); }
        .create-glass-strong { background: var(--create-surface-strong); }

        .create-topbar {
            position: sticky;
            top: 12px;
            z-index: 60;
            border-radius: 30px;
        }

        .create-icon-button,
        .create-pill-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--create-border);
            background: var(--create-control);
            color: var(--create-text);
            box-shadow: inset 0 1px 0 var(--create-highlight);
            backdrop-filter: blur(18px) saturate(130%);
            -webkit-backdrop-filter: blur(18px) saturate(130%);
        }

        .create-icon-button {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            flex: 0 0 auto;
        }

        .create-pill-button {
            min-height: 44px;
            border-radius: 999px;
            gap: 8px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }

        .create-icon-button:hover,
        .create-pill-button:hover {
            background: var(--create-control-hover);
        }

        .create-pill-button--primary {
            border-color: var(--create-accent);
            background: var(--create-accent);
            color: #fff;
            box-shadow: none;
        }

        .create-pill-button--primary:hover {
            background: var(--create-accent-strong);
        }

        .create-brand-mark {
            display: inline-flex;
            width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: var(--create-accent);
            color: #fff;
            flex: 0 0 auto;
        }

        .create-category-pill {
            display: inline-flex;
            max-width: min(360px, 58vw);
            min-height: 32px;
            align-items: center;
            gap: 7px;
            border: .5px solid var(--create-border);
            border-radius: 999px;
            background: var(--create-control);
            padding: 0 11px;
            color: var(--create-muted);
            font-size: 12px;
            font-weight: 600;
            box-shadow: inset 0 1px 0 var(--create-highlight);
        }

        .create-category-menu {
            position: absolute;
            left: 0;
            top: calc(100% + 10px);
            z-index: 90;
            width: 330px;
            max-width: calc(100vw - 28px);
            overflow: hidden;
            border-radius: 24px;
            border: .5px solid var(--create-border);
            background: var(--create-surface-strong);
            box-shadow: var(--create-shadow), inset 0 1px 0 var(--create-highlight);
            backdrop-filter: blur(30px) saturate(135%);
            -webkit-backdrop-filter: blur(30px) saturate(135%);
        }

        [data-category-option] {
            color: var(--create-text);
        }

        [data-category-option]:hover,
        [data-category-option].is-selected {
            background: var(--create-control-hover);
        }

        .create-editor-card {
            overflow: hidden;
            border-radius: 30px;
        }

        .create-cover {
            position: relative;
            display: block;
            width: 100%;
            overflow: hidden;
            border-radius: 22px;
        }

        .create-cover-drop {
            display: flex;
            min-height: 154px;
            width: 100%;
            cursor: pointer;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px dashed var(--create-border);
            border-radius: 22px;
            background: var(--create-surface-soft);
            color: var(--create-muted);
            padding: 20px;
            text-align: center;
        }

        .create-cover-drop:hover {
            border-color: rgba(37, 99, 235, .46);
            color: var(--create-accent);
        }

        .create-cover-preview {
            display: none;
            position: relative;
            width: 100%;
            overflow: hidden;
            border-radius: 22px;
            background: #0f172a;
        }

        .create-cover-preview img {
            display: block;
            width: 100%;
            max-height: 420px;
            object-fit: cover;
        }

        .create-cover-preview__actions {
            position: absolute;
            right: 12px;
            top: 12px;
            display: flex;
            gap: 7px;
        }

        .create-cover-preview__actions button {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            gap: 6px;
            border: .5px solid rgba(255,255,255,.26);
            border-radius: 999px;
            background: rgba(15, 23, 42, .68);
            color: #fff;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .create-cover.has-image .create-cover-drop { display: none; }
        .create-cover.has-image .create-cover-preview { display: block; }

        .create-title-input {
            width: 100%;
            min-height: 0;
            resize: none;
            overflow: hidden;
            border: 0 !important;
            border-radius: 0 !important;
            outline: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            color: var(--create-text) !important;
            font-family: inherit !important;
            font-size: clamp(1.9rem, 1.5rem + 1.7vw, 2.8rem) !important;
            font-weight: 720 !important;
            letter-spacing: -.032em !important;
            line-height: 1.1 !important;
        }

        .create-title-input::placeholder {
            color: var(--create-faint) !important;
        }

        .create-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--create-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .create-editor-surface {
            border-top: .5px solid var(--create-border-soft);
            background: transparent;
        }

        [data-editorjs-wrapper] .codex-editor__redactor {
            padding-bottom: 130px !important;
        }

        [data-editorjs-wrapper] .ce-block__content,
        [data-editorjs-wrapper] .ce-toolbar__content {
            max-width: 100% !important;
        }

        [data-editorjs-wrapper] .ce-paragraph,
        [data-editorjs-wrapper] .cdx-block,
        [data-editorjs-wrapper] .ce-header {
            color: var(--create-text) !important;
        }

        [data-editorjs-wrapper] .ce-paragraph[data-placeholder-active]:empty::before,
        [data-editorjs-wrapper] .ce-paragraph:empty::before {
            color: var(--create-faint) !important;
        }

        [data-editorjs-wrapper] .ce-toolbar__plus,
        [data-editorjs-wrapper] .ce-toolbar__settings-btn,
        [data-editorjs-wrapper] .ce-inline-toolbar__dropdown,
        [data-editorjs-wrapper] .ce-conversion-toolbar__label {
            color: var(--create-muted) !important;
        }

        html.dark [data-editorjs-wrapper] .ce-popover,
        html.dark [data-editorjs-wrapper] .ce-inline-toolbar,
        html.dark [data-editorjs-wrapper] .ce-conversion-toolbar,
        html[data-theme="dark"] [data-editorjs-wrapper] .ce-popover,
        html[data-theme="dark"] [data-editorjs-wrapper] .ce-inline-toolbar,
        html[data-theme="dark"] [data-editorjs-wrapper] .ce-conversion-toolbar,
        html[data-system-theme="dark"] [data-editorjs-wrapper] .ce-popover,
        html[data-system-theme="dark"] [data-editorjs-wrapper] .ce-inline-toolbar,
        html[data-system-theme="dark"] [data-editorjs-wrapper] .ce-conversion-toolbar {
            border-color: var(--create-border) !important;
            background: #111827 !important;
            color: #f8fafc !important;
        }

        html.dark [data-editorjs-wrapper] .cdx-search-field,
        html[data-theme="dark"] [data-editorjs-wrapper] .cdx-search-field,
        html[data-system-theme="dark"] [data-editorjs-wrapper] .cdx-search-field {
            background: #0f172a !important;
            color: #f8fafc !important;
        }

        .create-side-card {
            border-radius: 26px;
            padding: 18px;
        }

        .create-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
        }

        .create-input,
        .create-textarea {
            width: 100%;
            border: .5px solid var(--create-border);
            border-radius: 18px;
            outline: none;
            background: var(--create-input);
            color: var(--create-text);
            box-shadow: inset 0 1px 0 var(--create-highlight);
            font-size: 14px;
        }

        .create-input {
            min-height: 46px;
            padding: 0 14px;
        }

        .create-textarea {
            padding: 12px 14px;
            resize: vertical;
        }

        .create-input::placeholder,
        .create-textarea::placeholder {
            color: var(--create-faint);
        }

        .create-input:focus,
        .create-textarea:focus {
            border-color: rgba(37, 99, 235, .58);
            background: var(--create-surface-strong);
        }

        .create-settings-section {
            overflow: hidden;
            border: .5px solid var(--create-border);
            border-radius: 24px;
            background: var(--create-surface-soft);
            box-shadow: inset 0 1px 0 var(--create-highlight);
        }

        .create-settings-section__head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 15px;
        }

        .create-settings-icon {
            display: inline-flex;
            width: 36px;
            height: 36px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: rgba(37, 99, 235, .12);
            color: var(--create-accent);
        }

        .create-settings-body {
            border-top: .5px solid var(--create-border-soft);
            padding: 14px;
        }

        .create-preference-row {
            display: flex;
            min-height: 58px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 0 14px;
            color: var(--create-text);
        }

        .create-preference-row + .create-preference-row {
            border-top: .5px solid var(--create-border-soft);
        }

        .create-page-fixed input[role="switch"] + span {
            border-color: var(--create-border) !important;
            background: rgba(100, 116, 139, .28) !important;
        }

        .create-page-fixed input[role="switch"]:checked + span {
            border-color: var(--create-accent) !important;
            background: var(--create-accent) !important;
        }

        .create-page-fixed input[role="switch"] + span + span {
            box-shadow: none !important;
        }

        label[data-tag-option] {
            position: relative !important;
            display: inline-flex !important;
            cursor: pointer !important;
        }

        label[data-tag-option] input[type="checkbox"] {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        label[data-tag-option] span,
        .create-new-tag-chip {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border: .5px solid var(--create-border);
            border-radius: 999px;
            background: var(--create-control);
            padding: 0 12px;
            color: var(--create-muted);
            font-size: 12px;
            font-weight: 600;
        }

        label[data-tag-option]:hover span,
        label[data-tag-option]:has(input[type="checkbox"]:checked) span {
            border-color: rgba(37, 99, 235, .48);
            background: rgba(37, 99, 235, .1);
            color: var(--create-accent);
        }

        .create-settings-modal,
        .create-preview-modal {
            position: fixed;
            inset: 0;
            z-index: 120;
        }

        .create-modal-overlay {
            position: absolute;
            inset: 0;
            background: var(--create-overlay);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .settings-panel {
            position: absolute;
            inset-inline: 10px;
            bottom: 10px;
            display: flex;
            max-height: calc(100vh - 20px);
            flex-direction: column;
            overflow: hidden;
            border: .5px solid var(--create-border);
            border-radius: 30px;
            background: var(--create-surface-strong);
            color: var(--create-text);
            box-shadow: var(--create-shadow), inset 0 1px 0 var(--create-highlight);
            backdrop-filter: blur(34px) saturate(140%);
            -webkit-backdrop-filter: blur(34px) saturate(140%);
        }

        .create-preview-panel {
            position: relative;
            width: min(760px, calc(100vw - 24px));
            max-height: calc(100vh - 40px);
            overflow: hidden;
            border: .5px solid var(--create-border);
            border-radius: 30px;
            background: var(--create-surface-strong);
            color: var(--create-text);
            box-shadow: var(--create-shadow), inset 0 1px 0 var(--create-highlight);
            backdrop-filter: blur(34px) saturate(140%);
            -webkit-backdrop-filter: blur(34px) saturate(140%);
        }

        .create-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            z-index: 999999;
            width: min(92vw, 430px);
            transform: translateX(-50%);
            border: .5px solid var(--create-border);
            border-radius: 20px;
            background: var(--create-surface-strong);
            color: var(--create-text);
            padding: 12px 14px;
            box-shadow: var(--create-shadow), inset 0 1px 0 var(--create-highlight);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            font-size: 13px;
        }

        .create-toast[data-error="true"] {
            border-color: rgba(244, 63, 94, .36);
            color: #e11d48;
        }

        @media (min-width: 768px) {
            .settings-panel {
                inset: 18px 18px 18px auto;
                width: 460px;
                max-height: calc(100vh - 36px);
            }
        }

        @media (max-width: 767px) {
            .create-topbar {
                top: 8px;
                border-radius: 24px;
            }

            .create-icon-button {
                width: 40px;
                height: 40px;
            }

            .create-pill-button {
                min-height: 40px;
                padding: 0 13px;
            }

            .create-category-pill {
                max-width: 66vw;
            }

            .create-editor-card {
                border-radius: 24px;
            }

            .create-cover,
            .create-cover-drop,
            .create-cover-preview {
                border-radius: 18px;
            }
        }
    </style>

    <div class="create-page-fixed">
        <div class="mx-auto min-h-screen w-full max-w-[1320px] px-3 pb-10 pt-3 sm:px-5 lg:px-6">
            <header class="create-topbar create-glass px-3 py-3 sm:px-4">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('blog.index') }}" class="create-icon-button" aria-label="{{ __('post_create.back') }}">
                        <iconify-icon icon="lucide:chevron-left" class="text-[21px]"></iconify-icon>
                    </a>

                    <div class="create-brand-mark">
                        <iconify-icon icon="lucide:pen-line" class="text-[17px]"></iconify-icon>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[15px] font-semibold tracking-[-.01em]">Yeni gönderi</div>
                        <details class="relative mt-1.5 w-fit" data-category-menu>
                            <summary class="create-category-pill cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <iconify-icon icon="lucide:layers-3" class="text-[13px]"></iconify-icon>
                                <span class="truncate" data-category-label>{{ $selectedCategory?->name ?: __('post_create.select_category') }}</span>
                                <iconify-icon icon="lucide:chevron-down" class="text-[13px]"></iconify-icon>
                            </summary>

                            <div class="create-category-menu">
                                <div class="border-b px-4 py-3 text-xs font-semibold" style="border-color:var(--create-border-soft); color:var(--create-muted);">Topluluk seç</div>
                                <div class="max-h-[340px] overflow-y-auto p-2">
                                    @foreach ($categories as $index => $category)
                                        @php($categoryAvatar = $category->profile_image_url ?? $category->profile_image ?? null)
                                        @php($fallbackColor = $categoryPalette[$index % count($categoryPalette)])
                                        <button
                                            type="button"
                                            data-category-option
                                            data-value="{{ $category->id }}"
                                            data-label="{{ $category->name }}"
                                            class="{{ $initialCategoryId === (int) $category->id ? 'is-selected' : '' }} flex w-full items-center gap-3 rounded-[18px] px-3 py-2.5 text-left"
                                        >
                                            @if ($categoryAvatar)
                                                <img src="{{ $categoryAvatar }}" alt="{{ $category->name }}" class="h-9 w-9 rounded-full object-cover">
                                            @else
                                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold text-white" style="background-color: {{ $fallbackColor }};">
                                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 2)) }}
                                                </span>
                                            @endif
                                            <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ $category->name }}</span>
                                            <iconify-icon icon="lucide:check" class="hidden text-[15px] text-blue-500" data-category-check></iconify-icon>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" data-submit-intent="draft" class="create-pill-button hidden sm:inline-flex" title="Taslak olarak kaydet">
                            <iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>
                            <span>Taslak</span>
                        </button>

                        <button type="button" data-open-preview class="create-icon-button" aria-label="Ön izleme" title="Ön izleme">
                            <iconify-icon icon="lucide:eye" class="text-[18px]"></iconify-icon>
                        </button>

                        <button type="button" data-ai-assist class="create-icon-button" aria-label="Yapay zeka yardımcısı" title="Yapay zeka yardımcısı">
                            <iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[18px]"></iconify-icon>
                        </button>

                        <button type="button" data-open-settings class="create-icon-button" aria-label="{{ __('post_create.settings') }}" title="Gelişmiş seçenekler">
                            <iconify-icon icon="lucide:sliders-horizontal" class="text-[18px]"></iconify-icon>
                        </button>

                        <button type="button" data-submit-intent="publish" class="create-pill-button create-pill-button--primary">
                            <iconify-icon icon="lucide:send" class="text-[16px]"></iconify-icon>
                            <span class="hidden sm:inline">{{ __('post_create.publish') }}</span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="mt-4">
                @if ($errors->any())
                    <div class="create-glass-strong mx-auto mb-4 max-w-[920px] rounded-[24px] border border-rose-300/50 px-4 py-3 text-sm text-rose-600">
                        <div class="font-semibold">{{ __('post_create.validation_errors') }}</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published') ? 1 : 0 }}">
                    <input type="hidden" id="category_id" name="category_id" data-category-input value="{{ $initialCategoryId ?: '' }}">

                    <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_300px]">
                        <section class="create-editor-card create-glass-strong min-w-0">
                            <div class="px-4 pb-5 pt-4 sm:px-7 sm:pb-7 sm:pt-7">
                                <div class="create-cover" data-cover-field>
                                    <label for="featured_image" class="create-cover-drop">
                                        <span class="create-settings-icon h-11 w-11 rounded-[16px]">
                                            <iconify-icon icon="lucide:image-plus" class="text-[20px]"></iconify-icon>
                                        </span>
                                        <span class="mt-1 text-sm font-semibold">{{ __('post_create.featured_image') }}</span>
                                        <span class="text-xs" style="color:var(--create-faint);">JPG, PNG veya WebP · en fazla 5 MB</span>
                                    </label>

                                    <div class="create-cover-preview" data-cover-preview>
                                        <img data-cover-preview-img alt="">
                                        <div class="create-cover-preview__actions">
                                            <button type="button" data-cover-change>
                                                <iconify-icon icon="lucide:pencil" class="text-[14px]"></iconify-icon>
                                                Değiştir
                                            </button>
                                            <button type="button" data-cover-remove>
                                                <iconify-icon icon="lucide:trash-2" class="text-[14px]"></iconify-icon>
                                                Kaldır
                                            </button>
                                        </div>
                                    </div>

                                    <input id="featured_image" name="featured_image" type="file" accept="image/*" class="sr-only" data-cover-input>
                                </div>

                                <textarea
                                    id="title"
                                    name="title"
                                    required
                                    rows="1"
                                    data-autogrow
                                    placeholder="{{ __('post_create.title_placeholder') }}"
                                    class="create-title-input mt-6"
                                >{{ old('title') }}</textarea>

                                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2">
                                    <span class="create-meta-chip">
                                        <iconify-icon icon="lucide:clock-3" class="text-[14px]"></iconify-icon>
                                        <span data-reading-time>1 dk okuma</span>
                                    </span>
                                    <span class="create-meta-chip">
                                        <iconify-icon icon="lucide:type" class="text-[14px]"></iconify-icon>
                                        <span data-word-count>0 kelime</span>
                                    </span>
                                    <span class="create-meta-chip">
                                        <iconify-icon icon="lucide:shield-check" class="text-[14px]"></iconify-icon>
                                        <span>Yayınlamadan önce kontrol edilir</span>
                                    </span>
                                </div>
                            </div>

                            <div data-editorjs-wrapper class="create-editor-surface">
                                <div x-ref="holder" class="min-h-[52vh] px-4 py-6 text-[15px] leading-7 sm:px-7 sm:py-7"></div>
                                <div class="flex items-center gap-2 px-4 pb-7 text-xs sm:px-7" style="color:var(--create-faint);">
                                    <iconify-icon icon="lucide:circle-plus" class="text-[15px]"></iconify-icon>
                                    <span>Yeni blok eklemek için satır başındaki + düğmesini kullan.</span>
                                </div>
                                <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">
                                <textarea id="content" name="content" data-editor-content data-mentionable="users" class="hidden min-h-[52vh] w-full resize-none bg-transparent px-4 py-6 text-[15px] leading-7 outline-none sm:px-7 sm:py-7" placeholder="Gönderinizi buraya yazmaya başlayın...">{{ old('content') }}</textarea>
                            </div>
                        </section>

                        <aside class="hidden space-y-4 lg:block lg:sticky lg:top-[108px]">
                            <section class="create-side-card create-glass">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold">Yayın kontrolü</div>
                                        <div class="mt-1 text-xs" style="color:var(--create-muted);">Gönderi hazırlık durumu</div>
                                    </div>
                                    <span class="create-status-dot"></span>
                                </div>

                                <div class="mt-4 space-y-2.5">
                                    <div class="flex items-center justify-between gap-3 text-xs">
                                        <span style="color:var(--create-muted);">Kelime</span>
                                        <span class="font-semibold" data-word-count>0 kelime</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-xs">
                                        <span style="color:var(--create-muted);">Okuma</span>
                                        <span class="font-semibold" data-reading-time>1 dk okuma</span>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2">
                                    <button type="button" data-open-settings class="create-pill-button w-full">
                                        <iconify-icon icon="lucide:settings-2" class="text-[16px]"></iconify-icon>
                                        Gelişmiş seçenekler
                                    </button>
                                    <button type="button" data-open-preview class="create-pill-button w-full">
                                        <iconify-icon icon="lucide:eye" class="text-[16px]"></iconify-icon>
                                        Ön izleme
                                    </button>
                                </div>
                            </section>

                            <section class="create-side-card create-glass">
                                <div class="text-sm font-semibold">Yayın ayrıntıları</div>
                                <div class="mt-3 space-y-3 text-xs" style="color:var(--create-muted);">
                                    <div class="flex items-start gap-2.5">
                                        <iconify-icon icon="lucide:tags" class="mt-0.5 text-[15px]"></iconify-icon>
                                        <span>Etiketler ve kısa açıklama</span>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <iconify-icon icon="lucide:search" class="mt-0.5 text-[15px]"></iconify-icon>
                                        <span>SEO başlığı, açıklama ve bağlantı</span>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <iconify-icon icon="lucide:calendar-clock" class="mt-0.5 text-[15px]"></iconify-icon>
                                        <span>Yayın zamanlaması</span>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <iconify-icon icon="lucide:copyright" class="mt-0.5 text-[15px]"></iconify-icon>
                                        <span>Görsel lisans ve telif bilgileri</span>
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>

                    <div id="settings-modal" class="create-settings-modal hidden" aria-hidden="true">
                        <div class="create-modal-overlay" data-settings-close></div>

                        <aside class="settings-panel" role="dialog" aria-modal="true" aria-labelledby="settings-title">
                            <div class="flex items-center justify-between gap-3 border-b px-4 py-4 sm:px-5" style="border-color:var(--create-border-soft);">
                                <div class="min-w-0">
                                    <h2 id="settings-title" class="text-[16px] font-semibold tracking-[-.01em]">Gelişmiş seçenekler</h2>
                                    <p class="mt-1 text-xs" style="color:var(--create-muted);">Gönderinin yayın, SEO ve içerik ayrıntıları.</p>
                                </div>
                                <button type="button" data-settings-close class="create-icon-button h-9 w-9" aria-label="{{ __('post_create.close') }}">
                                    <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                                </button>
                            </div>

                            <div class="flex-1 space-y-3 overflow-y-auto p-3 sm:p-4">
                                <section class="create-settings-section">
                                    <div class="create-settings-section__head">
                                        <span class="create-settings-icon"><iconify-icon icon="lucide:tags" class="text-[17px]"></iconify-icon></span>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold">İçerik ayrıntıları</div>
                                            <div class="mt-0.5 text-xs" style="color:var(--create-muted);">Etiketler ve kısa açıklama.</div>
                                        </div>
                                    </div>
                                    <div class="create-settings-body space-y-3">
                                        <input id="new_tags" name="new_tags" type="hidden" value="{{ old('new_tags') }}">
                                        <input id="tag_search" type="text" placeholder="Etiket ara veya yeni etiket yaz..." class="create-input" autocomplete="off">

                                        @if(isset($tags) && collect($tags)->isNotEmpty())
                                            <div class="max-h-36 overflow-y-auto rounded-[18px] border p-2.5" style="border-color:var(--create-border-soft);">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($tags as $tag)
                                                        <label data-tag-option data-tag-name="{{ \Illuminate\Support\Str::lower($tag->name) }}">
                                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id))>
                                                            <span>#{{ $tag->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div id="new-tags-chips" class="flex flex-wrap gap-2"></div>
                                        <button type="button" id="add-new-tag-btn" class="create-pill-button hidden w-fit">{{ __('post_create.add_new_tag') }}</button>

                                        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Gönderi için kısa açıklama..." class="create-textarea">{{ old('excerpt') }}</textarea>
                                    </div>
                                </section>

                                <section class="create-settings-section">
                                    <details class="group" open>
                                        <summary class="create-settings-section__head cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                            <span class="create-settings-icon"><iconify-icon icon="lucide:search" class="text-[17px]"></iconify-icon></span>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold">SEO ve bağlantı</div>
                                                <div class="mt-0.5 text-xs" style="color:var(--create-muted);">Arama sonuçlarındaki görünümü düzenle.</div>
                                            </div>
                                            <iconify-icon icon="lucide:chevron-down" class="text-[16px] group-open:rotate-180"></iconify-icon>
                                        </summary>
                                        <div class="create-settings-body space-y-3">
                                            <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title') }}" placeholder="SEO başlığı" class="create-input">
                                            <div>
                                                <textarea id="meta_description" name="meta_description" rows="3" maxlength="160" placeholder="SEO açıklaması" class="create-textarea">{{ old('meta_description') }}</textarea>
                                                <div class="mt-1.5 text-right text-[11px]" style="color:var(--create-faint);" data-meta-description-count>0/160</div>
                                            </div>
                                            <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="gonderi-baglantisi" class="create-input">
                                            <textarea id="meta_keywords" name="meta_keywords" rows="2" placeholder="anahtar kelimeleri virgülle ayır" class="create-textarea">{{ old('meta_keywords') }}</textarea>
                                        </div>
                                    </details>
                                </section>

                                <section class="create-settings-section">
                                    <div class="create-settings-section__head">
                                        <span class="create-settings-icon"><iconify-icon icon="lucide:calendar-clock" class="text-[17px]"></iconify-icon></span>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold">Yayın</div>
                                            <div class="mt-0.5 text-xs" style="color:var(--create-muted);">Zamanlama ve görünürlük tercihleri.</div>
                                        </div>
                                    </div>
                                    <div class="create-settings-body">
                                        <label for="published_at" class="mb-2 block text-xs font-semibold" style="color:var(--create-muted);">Yayın zamanı</label>
                                        <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at') }}" class="create-input">

                                        <div class="mt-3 overflow-hidden rounded-[20px] border" style="border-color:var(--create-border-soft);">
                                            <div class="create-preference-row">
                                                <div>
                                                    <div class="text-sm font-medium">Yorumları kapat</div>
                                                    <div class="mt-0.5 text-[11px]" style="color:var(--create-muted);">Bu gönderiye yeni yorum alınmaz.</div>
                                                </div>
                                                <x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" />
                                            </div>
                                            <div class="create-preference-row">
                                                <div>
                                                    <div class="text-sm font-medium">Hassas içerik</div>
                                                    <div class="mt-0.5 text-[11px]" style="color:var(--create-muted);">İçeriği NSFW olarak işaretle.</div>
                                                </div>
                                                <x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" />
                                            </div>
                                            <div class="create-preference-row">
                                                <div>
                                                    <div class="text-sm font-medium">Gönderiyi sabitle</div>
                                                    <div class="mt-0.5 text-[11px]" style="color:var(--create-muted);">Akışta öncelikli göster.</div>
                                                </div>
                                                <x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" />
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="create-settings-section">
                                    <details class="group">
                                        <summary class="create-settings-section__head cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                            <span class="create-settings-icon"><iconify-icon icon="lucide:copyright" class="text-[17px]"></iconify-icon></span>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold">Görsel hakları</div>
                                                <div class="mt-0.5 text-xs" style="color:var(--create-muted);">Kaynak, lisans ve telif bilgileri.</div>
                                            </div>
                                            <iconify-icon icon="lucide:chevron-down" class="text-[16px] group-open:rotate-180"></iconify-icon>
                                        </summary>
                                        <div class="create-settings-body space-y-3">
                                            <input id="image_license_url" name="image_license_url" type="url" value="{{ old('image_license_url') }}" placeholder="Lisans bağlantısı" class="create-input">
                                            <input id="image_acquire_url" name="image_acquire_url" type="url" value="{{ old('image_acquire_url') }}" placeholder="Edinme / satın alma bağlantısı" class="create-input">
                                            <input id="image_credit_text" name="image_credit_text" type="text" value="{{ old('image_credit_text') }}" placeholder="{{ __('post_create.credit_placeholder') }}" class="create-input">
                                            <input id="image_creator_name" name="image_creator_name" type="text" value="{{ old('image_creator_name') }}" placeholder="{{ __('post_create.creator_placeholder') }}" class="create-input">
                                            <input id="image_copyright_notice" name="image_copyright_notice" type="text" value="{{ old('image_copyright_notice') }}" placeholder="{{ __('post_create.copyright_placeholder') }}" class="create-input">
                                        </div>
                                    </details>
                                </section>
                            </div>

                            <div class="grid grid-cols-2 gap-2 border-t p-3 sm:p-4" style="border-color:var(--create-border-soft);">
                                <button type="button" data-settings-close class="create-pill-button w-full">{{ __('post_create.close') }}</button>
                                <button type="button" data-submit-intent="publish" class="create-pill-button create-pill-button--primary w-full">{{ __('post_create.publish') }}</button>
                            </div>
                        </aside>
                    </div>
                </form>
            </main>

            <div id="post-preview-modal" class="create-preview-modal hidden">
                <div class="create-modal-overlay" data-preview-close></div>
                <div class="relative flex min-h-full items-center justify-center p-3 sm:p-5">
                    <div class="create-preview-panel">
                        <div class="flex items-center justify-between gap-3 border-b px-4 py-4 sm:px-5" style="border-color:var(--create-border-soft);">
                            <div>
                                <div class="text-sm font-semibold">{{ __('post_create.preview_title') }}</div>
                                <div class="mt-0.5 text-xs" style="color:var(--create-muted);">Yayınlanmadan önce gönderi görünümü.</div>
                            </div>
                            <button type="button" data-preview-close class="create-icon-button h-9 w-9" aria-label="{{ __('post_create.close') }}">
                                <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                            </button>
                        </div>
                        <div class="max-h-[76vh] overflow-y-auto p-4 sm:p-6">
                            <div id="post-preview-content" class="space-y-4 text-sm leading-7"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('filament.assets.editorjs')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('[data-editorjs-wrapper]');
            const fallbackTextarea = document.getElementById('content');
            const editorJsonField = document.getElementById('content_json');
            const form = document.getElementById('post-create-form');
            const isPublishedInput = document.getElementById('is_published');
            const submitIntentButtons = Array.from(document.querySelectorAll('[data-submit-intent]'));
            const categoryInput = document.querySelector('[data-category-input]');
            const categoryLabel = document.querySelector('[data-category-label]');
            const categoryMenu = document.querySelector('[data-category-menu]');
            const categoryOptions = Array.from(document.querySelectorAll('[data-category-option]'));
            const metaTitleField = document.getElementById('meta_title');
            const metaDescription = document.getElementById('meta_description');
            const metaDescriptionCount = document.querySelector('[data-meta-description-count]');
            const metaKeywordsField = document.getElementById('meta_keywords');
            const tagSearchInput = document.getElementById('tag_search');
            const newTagsInput = document.getElementById('new_tags');
            const newTagsChips = document.getElementById('new-tags-chips');
            const addNewTagButton = document.getElementById('add-new-tag-btn');
            const existingTagOptions = Array.from(document.querySelectorAll('[data-tag-option]'));
            const previewModal = document.getElementById('post-preview-modal');
            const previewContent = document.getElementById('post-preview-content');
            const settingsModal = document.getElementById('settings-modal');
            const coverField = document.querySelector('[data-cover-field]');
            const coverInput = document.querySelector('[data-cover-input]');
            const coverPreviewImg = document.querySelector('[data-cover-preview-img]');
            const readingTimeEls = Array.from(document.querySelectorAll('[data-reading-time]'));
            const wordCountEls = Array.from(document.querySelectorAll('[data-word-count]'));

            const blockText = (block) => {
                const data = block?.data || {};
                const parts = [data.text, data.caption, data.question, data.answer, data.title, data.label, data.note];
                if (Array.isArray(data.items)) parts.push(...data.items);
                if (Array.isArray(data.pros)) parts.push(...data.pros);
                if (Array.isArray(data.cons)) parts.push(...data.cons);
                return parts.filter((value) => typeof value === 'string').join(' ');
            };

            const readEditorData = async () => {
                if (wrapper?.__editorInstance?.save) {
                    try {
                        return await wrapper.__editorInstance.save();
                    } catch {}
                }
                return null;
            };

            const readEditorPlainText = async () => {
                const data = await readEditorData();
                if (data?.blocks?.length) {
                    return data.blocks.map(blockText).join(' ').replace(/\s+/g, ' ').trim();
                }
                return (fallbackTextarea?.value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            };

            const syncEditorFields = async () => {
                const data = await readEditorData();
                if (!data || !Array.isArray(data.blocks)) return;
                if (editorJsonField) editorJsonField.value = JSON.stringify(data);
                if (fallbackTextarea && window.filamentEditorBlocksToHtml) {
                    fallbackTextarea.value = window.filamentEditorBlocksToHtml(data.blocks);
                }
            };

            const updateReadingStats = async () => {
                const text = await readEditorPlainText();
                const words = (text.match(/\S+/g) || []).length;
                const reading = words === 0 ? 1 : Math.max(1, Math.ceil(words / 200));
                wordCountEls.forEach((el) => { el.textContent = `${words} kelime`; });
                readingTimeEls.forEach((el) => { el.textContent = `${reading} dk okuma`; });
            };

            let readingStatsTimer = null;
            const scheduleReadingStats = () => {
                window.clearTimeout(readingStatsTimer);
                readingStatsTimer = window.setTimeout(updateReadingStats, 450);
            };

            wrapper?.addEventListener('input', scheduleReadingStats);
            wrapper?.addEventListener('keyup', scheduleReadingStats);

            const titleField = document.getElementById('title');
            const autoGrowTitle = () => {
                if (!titleField) return;
                titleField.style.height = 'auto';
                titleField.style.height = `${titleField.scrollHeight}px`;
            };
            titleField?.addEventListener('input', autoGrowTitle);
            titleField?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                wrapper?.querySelector('[contenteditable]')?.focus();
            });
            autoGrowTitle();

            const showFallback = () => {
                wrapper?.classList.add('hidden');
                fallbackTextarea?.classList.remove('hidden');
            };

            const initEditor = async () => {
                if (!wrapper || !window.initFilamentEditorJsField) {
                    showFallback();
                    return;
                }
                try {
                    await window.initFilamentEditorJsField(wrapper);
                    if (!wrapper.__editorInstance) showFallback();
                } catch {
                    showFallback();
                }
                window.setTimeout(updateReadingStats, 500);
            };
            initEditor();

            const setCoverFile = (file) => {
                if (!file || !coverPreviewImg || !coverField) return;
                const reader = new FileReader();
                reader.onload = () => {
                    coverPreviewImg.src = String(reader.result || '');
                    coverField.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            };

            coverInput?.addEventListener('change', () => {
                const file = coverInput.files?.[0];
                if (file) setCoverFile(file);
            });
            document.querySelector('[data-cover-change]')?.addEventListener('click', () => coverInput?.click());
            document.querySelector('[data-cover-remove]')?.addEventListener('click', () => {
                if (coverInput) coverInput.value = '';
                if (coverPreviewImg) coverPreviewImg.src = '';
                coverField?.classList.remove('has-image');
            });

            const syncCategorySelection = () => {
                if (!categoryInput) return;
                const activeValue = String(categoryInput.value || '');
                const activeOption = categoryOptions.find((option) => String(option.dataset.value || '') === activeValue);
                if (categoryLabel) categoryLabel.textContent = activeOption?.dataset.label || @js(__('post_create.select_category'));
                categoryOptions.forEach((option) => {
                    const active = String(option.dataset.value || '') === activeValue;
                    option.classList.toggle('is-selected', active);
                    option.querySelector('[data-category-check]')?.classList.toggle('hidden', !active);
                });
            };
            syncCategorySelection();
            categoryOptions.forEach((option) => {
                option.addEventListener('click', () => {
                    if (!categoryInput) return;
                    categoryInput.value = option.dataset.value || '';
                    syncCategorySelection();
                    categoryMenu?.removeAttribute('open');
                });
            });

            const syncMetaDescriptionCount = () => {
                if (!metaDescription || !metaDescriptionCount) return;
                metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
            };
            syncMetaDescriptionCount();
            metaDescription?.addEventListener('input', syncMetaDescriptionCount);

            const existingTagNames = existingTagOptions
                .map((el) => String(el.dataset.tagName || '').trim())
                .filter(Boolean);
            const newTagSet = new Set(
                String(newTagsInput?.value || '')
                    .split(',')
                    .map((item) => item.trim().replace(/^#/, ''))
                    .filter(Boolean)
            );

            const syncNewTagsInput = () => {
                if (newTagsInput) newTagsInput.value = Array.from(newTagSet).join(', ');
            };

            const renderNewTagChips = () => {
                if (!newTagsChips) return;
                newTagsChips.innerHTML = '';
                Array.from(newTagSet).forEach((tag) => {
                    const chip = document.createElement('span');
                    chip.className = 'create-new-tag-chip';
                    const text = document.createElement('span');
                    text.textContent = `#${tag}`;
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.setAttribute('data-remove-new-tag', tag);
                    button.setAttribute('aria-label', `${tag} etiketini kaldır`);
                    button.className = 'inline-flex h-5 w-5 items-center justify-center rounded-full';
                    button.innerHTML = '<iconify-icon icon="lucide:x" class="text-[12px]"></iconify-icon>';
                    chip.append(text, button);
                    newTagsChips.appendChild(chip);
                });
            };

            const filterExistingTags = (term) => {
                const query = term.trim().toLowerCase();
                existingTagOptions.forEach((option) => {
                    const name = String(option.dataset.tagName || '');
                    option.classList.toggle('hidden', Boolean(query && !name.includes(query)));
                });
            };

            const normalizeTagText = (value) => String(value || '')
                .trim()
                .replace(/^#/, '')
                .replace(/[.,;:!?]+$/g, '')
                .trim();

            const tryAddNewTag = (rawValue) => {
                const value = normalizeTagText(rawValue);
                if (!value) return false;
                const normalized = value.toLowerCase();
                if (existingTagNames.includes(normalized)) return false;
                if (Array.from(newTagSet).some((tag) => tag.toLowerCase() === normalized)) return false;
                newTagSet.add(value);
                syncNewTagsInput();
                renderNewTagChips();
                return true;
            };

            const updateAddTagButton = (term) => {
                if (!addNewTagButton) return;
                const normalized = normalizeTagText(term).toLowerCase();
                const exists = !normalized || existingTagNames.includes(normalized) || Array.from(newTagSet).some((tag) => tag.toLowerCase() === normalized);
                addNewTagButton.classList.toggle('hidden', exists);
                if (!exists) {
                    addNewTagButton.textContent = @js(__('post_create.add_new_tag_with_value')).replace(':tag', normalizeTagText(term));
                }
            };

            const commitTagInput = (consumeAll = false) => {
                if (!tagSearchInput) return;
                const raw = tagSearchInput.value || '';
                const hasDelimiter = /[,.;:!?]/.test(raw);
                if (!consumeAll && !hasDelimiter) return;
                const parts = raw.split(/[,.;:!?]+/);
                const trailingDelimiter = /[,.;:!?]\s*$/.test(raw);
                const candidates = (consumeAll || trailingDelimiter) ? parts : parts.slice(0, -1);
                const remainder = (consumeAll || trailingDelimiter) ? '' : (parts.at(-1) || '');
                candidates.forEach(tryAddNewTag);
                tagSearchInput.value = remainder;
                filterExistingTags(remainder);
                updateAddTagButton(remainder);
            };

            renderNewTagChips();
            syncNewTagsInput();
            tagSearchInput?.addEventListener('input', () => {
                commitTagInput(false);
                filterExistingTags(tagSearchInput.value || '');
                updateAddTagButton(tagSearchInput.value || '');
            });
            tagSearchInput?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && ![',', '.', ';', ':', '!', '?'].includes(event.key)) return;
                event.preventDefault();
                commitTagInput(true);
            });
            tagSearchInput?.addEventListener('blur', () => commitTagInput(true));
            addNewTagButton?.addEventListener('click', () => {
                if (!tagSearchInput || !tryAddNewTag(tagSearchInput.value)) return;
                tagSearchInput.value = '';
                filterExistingTags('');
                updateAddTagButton('');
            });
            newTagsChips?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-new-tag]');
                if (!button) return;
                const tag = button.getAttribute('data-remove-new-tag');
                if (!tag) return;
                newTagSet.delete(tag);
                syncNewTagsInput();
                renderNewTagChips();
            });

            const syncScrollLock = () => {
                const anyOpen = [previewModal, settingsModal].some((modal) => modal && !modal.classList.contains('hidden'));
                document.documentElement.classList.toggle('overflow-hidden', anyOpen);
                document.body.classList.toggle('overflow-hidden', anyOpen);
            };

            const openSettings = () => {
                if (!settingsModal) return;
                settingsModal.classList.remove('hidden');
                settingsModal.setAttribute('aria-hidden', 'false');
                syncScrollLock();
            };
            const closeSettings = () => {
                if (!settingsModal) return;
                settingsModal.classList.add('hidden');
                settingsModal.setAttribute('aria-hidden', 'true');
                syncScrollLock();
            };
            document.querySelectorAll('[data-open-settings]').forEach((button) => button.addEventListener('click', openSettings));
            settingsModal?.querySelectorAll('[data-settings-close]').forEach((button) => button.addEventListener('click', closeSettings));

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const renderBlocks = (blocks = []) => blocks.map((block) => {
                if (!block?.type) return '';
                const data = block.data || {};
                if (block.type === 'header') {
                    const level = Math.min(Math.max(parseInt(data.level || 2, 10), 2), 4);
                    return `<h${level} class="font-semibold">${escapeHtml(data.text || '')}</h${level}>`;
                }
                if (block.type === 'paragraph') return `<p>${escapeHtml(data.text || '')}</p>`;
                if (block.type === 'quote') return `<blockquote class="border-l-2 pl-4 italic" style="border-color:var(--create-border);">${escapeHtml(data.text || '')}</blockquote>`;
                if (block.type === 'list') {
                    const items = Array.isArray(data.items) ? data.items : [];
                    const tag = data.style === 'ordered' ? 'ol' : 'ul';
                    return `<${tag} class="space-y-1 pl-5">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</${tag}>`;
                }
                return `<pre class="overflow-auto rounded-[18px] p-3 text-xs" style="background:var(--create-surface-soft);">${escapeHtml(JSON.stringify(block, null, 2))}</pre>`;
            }).join('');

            const buildPreview = async () => {
                await syncEditorFields();
                const title = document.getElementById('title')?.value || '';
                const excerpt = document.getElementById('excerpt')?.value || '';
                const data = await readEditorData();
                const contentHtml = data?.blocks?.length
                    ? renderBlocks(data.blocks)
                    : `<p>${escapeHtml((fallbackTextarea?.value || '').replace(/<[^>]*>/g, ' ') || 'Henüz içerik eklenmedi.')}</p>`;
                return `
                    <article class="space-y-5">
                        <div>
                            <div class="text-xs font-semibold" style="color:var(--create-muted);">ÖN İZLEME</div>
                            <h1 class="mt-2 text-2xl font-bold tracking-[-.03em] sm:text-3xl">${escapeHtml(title || 'Başlıksız gönderi')}</h1>
                            ${excerpt ? `<p class="mt-3" style="color:var(--create-muted);">${escapeHtml(excerpt)}</p>` : ''}
                        </div>
                        <div class="space-y-4">${contentHtml}</div>
                    </article>
                `;
            };

            const closePreview = () => {
                previewModal?.classList.add('hidden');
                syncScrollLock();
            };
            previewModal?.querySelectorAll('[data-preview-close]').forEach((button) => button.addEventListener('click', closePreview));
            document.querySelectorAll('[data-open-preview]').forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!previewModal || !previewContent) return;
                    previewContent.innerHTML = '<p style="color:var(--create-muted);">Hazırlanıyor...</p>';
                    previewModal.classList.remove('hidden');
                    syncScrollLock();
                    previewContent.innerHTML = await buildPreview();
                });
            });

            const isEditorContentPresent = async () => {
                const data = await readEditorData();
                if (Array.isArray(data?.blocks) && data.blocks.length > 0) return true;
                return Boolean((fallbackTextarea?.value || '').trim());
            };

            const submitWithIntent = async (intent) => {
                if (!form || !isPublishedInput) return;
                const titleValue = (titleField?.value || '').trim();
                const hasContent = await isEditorContentPresent();
                if (!titleValue || !hasContent) {
                    alert(@js(__('post_create.required_fields_alert')));
                    return;
                }
                await syncEditorFields();
                isPublishedInput.value = intent === 'publish' ? '1' : '0';
                HTMLFormElement.prototype.submit.call(form);
            };

            submitIntentButtons.forEach((button) => {
                button.addEventListener('click', () => submitWithIntent(button.dataset.submitIntent || 'draft'));
            });

            const showAiToast = (message, isError = false) => {
                const toast = document.createElement('div');
                toast.className = 'create-toast';
                toast.dataset.error = isError ? 'true' : 'false';
                toast.textContent = message;
                document.body.appendChild(toast);
                window.setTimeout(() => toast.remove(), isError ? 6000 : 7500);
            };

            const aiAssistButton = document.querySelector('[data-ai-assist]');
            const aiAssistIcon = document.querySelector('[data-ai-assist-icon]');
            let aiAssistBusy = false;

            aiAssistButton?.addEventListener('click', async () => {
                if (aiAssistBusy) return;
                const titleValue = (titleField?.value || '').trim();
                const contentValue = await readEditorPlainText();
                if (titleValue === '' && contentValue === '') {
                    showAiToast('Önce bir başlık veya içerik yazın.', true);
                    return;
                }

                aiAssistBusy = true;
                aiAssistButton.disabled = true;
                aiAssistIcon?.setAttribute('icon', 'lucide:loader-circle');

                try {
                    const response = await fetch('{{ route('blog.ai-assist') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ title: titleValue, content: contentValue }),
                    });
                    const data = await response.json();
                    if (!response.ok || !data.ok) {
                        showAiToast(data.message || 'Yapay zeka şu anda yardımcı olamadı.', true);
                        return;
                    }
                    if (data.meta_title && metaTitleField) metaTitleField.value = data.meta_title;
                    if (data.meta_description && metaDescription) {
                        metaDescription.value = data.meta_description;
                        metaDescription.dispatchEvent(new Event('input'));
                    }
                    if (Array.isArray(data.meta_keywords) && data.meta_keywords.length && metaKeywordsField) {
                        metaKeywordsField.value = data.meta_keywords.join(', ');
                    }
                    if (data.excerpt) {
                        const excerptField = document.getElementById('excerpt');
                        if (excerptField && excerptField.value.trim() === '') excerptField.value = data.excerpt;
                    }
                    openSettings();
                    showAiToast(data.suggestions || 'SEO alanları yapay zeka tarafından dolduruldu.');
                } catch {
                    showAiToast('Yapay zeka isteği başarısız oldu. Bağlantınızı kontrol edip tekrar deneyin.', true);
                } finally {
                    aiAssistBusy = false;
                    aiAssistButton.disabled = false;
                    aiAssistIcon?.setAttribute('icon', 'lucide:sparkles');
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                closePreview();
                closeSettings();
            });

            document.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) return;
                if (categoryMenu && !categoryMenu.contains(target)) categoryMenu.removeAttribute('open');
            });
        });
    </script>
@endpush
