@extends('layouts.app')

@section('title', __('post_create.page_title'))

@section('hide_global_header')
@endsection

@section('no_container_padding')
@endsection

@section('page_background_class', 'bg-[#f4f5f7]')

@section('hide_feed_header')
@endsection

@section('hide_mobile_bottom_nav')
@endsection

@section('content')
    @php
        $user = auth()->user();
        $initialCategoryId = (int) old('category_id');
    @endphp

    <style>
        :root {
            --create-bg: #f4f5f7;
            --create-surface: rgba(255, 255, 255, .76);
            --create-surface-strong: rgba(255, 255, 255, .92);
            --create-surface-soft: rgba(255, 255, 255, .58);
            --create-border: rgba(15, 23, 42, .12);
            --create-border-strong: rgba(15, 23, 42, .17);
            --create-text: #0f172a;
            --create-muted: #64748b;
            --create-subtle: #94a3b8;
            --create-blue: #2563eb;
            --create-blue-hover: #1d4ed8;
            --create-danger: #dc2626;
            --create-input: rgba(255, 255, 255, .72);
            --create-inset: inset 0 1px 0 rgba(255,255,255,.72), inset 0 -1px 0 rgba(15,23,42,.03);
        }

        html.dark,
        html[data-system-theme="dark"],
        body.dark {
            --create-bg: #070b14;
            --create-surface: rgba(17, 24, 39, .70);
            --create-surface-strong: rgba(15, 23, 42, .90);
            --create-surface-soft: rgba(30, 41, 59, .54);
            --create-border: rgba(255, 255, 255, .12);
            --create-border-strong: rgba(255, 255, 255, .18);
            --create-text: #f8fafc;
            --create-muted: #a8b3c5;
            --create-subtle: #718096;
            --create-input: rgba(15, 23, 42, .68);
            --create-inset: inset 0 1px 0 rgba(255,255,255,.08), inset 0 -1px 0 rgba(0,0,0,.20);
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
            overflow: auto;
            color: var(--create-text);
            background:
                radial-gradient(circle at 16% 0%, rgba(37, 99, 235, .08), transparent 28rem),
                radial-gradient(circle at 88% 14%, rgba(148, 163, 184, .12), transparent 24rem),
                var(--create-bg);
        }

        .create-glass {
            border: .5px solid var(--create-border);
            background: var(--create-surface);
            box-shadow: var(--create-inset);
            backdrop-filter: blur(26px) saturate(155%);
            -webkit-backdrop-filter: blur(26px) saturate(155%);
        }

        .create-glass-strong {
            border: .5px solid var(--create-border-strong);
            background: var(--create-surface-strong);
            box-shadow: var(--create-inset);
            backdrop-filter: blur(30px) saturate(165%);
            -webkit-backdrop-filter: blur(30px) saturate(165%);
        }

        .create-topbar {
            position: sticky;
            top: 12px;
            z-index: 70;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 64px;
            padding: 9px 10px 9px 12px;
            border-radius: 26px;
        }

        .create-icon-button {
            display: inline-flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--create-border);
            border-radius: 999px;
            background: var(--create-surface-soft);
            color: var(--create-text);
            box-shadow: var(--create-inset);
            text-decoration: none;
        }

        .create-icon-button:hover,
        .create-icon-button:focus-visible {
            background: var(--create-surface-strong);
            outline: none;
        }

        .create-action-button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: .5px solid var(--create-border);
            border-radius: 999px;
            padding: 0 16px;
            background: var(--create-surface-soft);
            color: var(--create-text);
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
        }

        .create-action-button:hover,
        .create-action-button:focus-visible {
            background: var(--create-surface-strong);
            outline: none;
        }

        .create-action-button--primary {
            border-color: rgba(37, 99, 235, .88);
            background: var(--create-blue);
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.22);
        }

        .create-action-button--primary:hover,
        .create-action-button--primary:focus-visible {
            background: var(--create-blue-hover);
        }

        .create-brand-mark {
            display: inline-flex;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #111827;
            color: #fff;
        }

        html.dark .create-brand-mark,
        html[data-system-theme="dark"] .create-brand-mark {
            background: #f8fafc;
            color: #0f172a;
        }

        .create-layout {
            display: grid;
            grid-template-columns: minmax(0, 780px) minmax(300px, 340px);
            align-items: start;
            justify-content: center;
            gap: 18px;
            width: 100%;
            margin-top: 18px;
        }

        .create-editor-card {
            overflow: hidden;
            border-radius: 28px;
        }

        .create-editor-head {
            padding: 18px;
            border-bottom: .5px solid var(--create-border);
        }

        .create-cover {
            position: relative;
            overflow: hidden;
            width: 100%;
            min-height: 190px;
            border: .5px solid var(--create-border);
            border-radius: 22px;
            background: var(--create-surface-soft);
        }

        .create-cover-drop {
            display: flex;
            min-height: 190px;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 22px;
            color: var(--create-muted);
            cursor: pointer;
            text-align: left;
        }

        .create-cover-drop:hover {
            background: rgba(37, 99, 235, .06);
            color: var(--create-blue);
        }

        .create-cover-preview {
            display: none;
            position: relative;
            min-height: 190px;
        }

        .create-cover-preview img {
            display: block;
            width: 100%;
            max-height: 390px;
            object-fit: cover;
        }

        .create-cover-preview-actions {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            gap: 7px;
        }

        .create-cover.has-image .create-cover-drop {
            display: none;
        }

        .create-cover.has-image .create-cover-preview {
            display: block;
        }

        .create-title {
            display: block;
            width: 100%;
            margin-top: 22px;
            padding: 0;
            resize: none;
            overflow: hidden;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--create-text);
            font: inherit;
            font-size: clamp(1.8rem, 1.4rem + 1.6vw, 2.55rem);
            font-weight: 720;
            line-height: 1.12;
            letter-spacing: -.035em;
        }

        .create-title::placeholder {
            color: var(--create-subtle);
        }

        .create-meta-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 14px;
            margin-top: 15px;
            color: var(--create-muted);
            font-size: 12px;
        }

        .create-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .create-editor-body {
            min-height: 58vh;
            background: rgba(255,255,255,.28);
        }

        html.dark .create-editor-body,
        html[data-system-theme="dark"] .create-editor-body {
            background: rgba(2,6,23,.18);
        }

        [data-editorjs-wrapper] .codex-editor__redactor {
            padding-bottom: 130px !important;
        }

        [data-editorjs-wrapper] .ce-block__content,
        [data-editorjs-wrapper] .ce-toolbar__content {
            max-width: 690px !important;
        }

        [data-editorjs-wrapper] .ce-paragraph,
        [data-editorjs-wrapper] .ce-header {
            color: var(--create-text) !important;
        }

        [data-editorjs-wrapper] .ce-toolbar__plus,
        [data-editorjs-wrapper] .ce-toolbar__settings-btn {
            color: var(--create-muted) !important;
            background: transparent !important;
        }

        [data-editorjs-wrapper] .ce-popover,
        [data-editorjs-wrapper] .ce-inline-toolbar,
        [data-editorjs-wrapper] .ce-conversion-toolbar {
            border: .5px solid var(--create-border) !important;
            background: var(--create-surface-strong) !important;
            color: var(--create-text) !important;
            box-shadow: none !important;
            backdrop-filter: blur(24px) saturate(150%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(150%) !important;
        }

        html.dark [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50),
        html[data-system-theme="dark"] [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50) {
            background-color: rgba(15, 23, 42, .74) !important;
        }

        html.dark [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700),
        html[data-system-theme="dark"] [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700) {
            color: #e5edf8 !important;
        }

        html.dark [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300),
        html[data-system-theme="dark"] [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300) {
            border-color: rgba(255,255,255,.12) !important;
        }

        .create-settings {
            position: sticky;
            top: 94px;
            overflow: hidden;
            border-radius: 28px;
        }

        .create-settings-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 17px 18px 14px;
            border-bottom: .5px solid var(--create-border);
        }

        .create-settings-scroll {
            max-height: calc(100vh - 178px);
            overflow: auto;
            padding: 12px;
        }

        .create-settings-section {
            overflow: hidden;
            border: .5px solid var(--create-border);
            border-radius: 20px;
            background: rgba(255,255,255,.28);
        }

        html.dark .create-settings-section,
        html[data-system-theme="dark"] .create-settings-section {
            background: rgba(15,23,42,.34);
        }

        .create-settings-section + .create-settings-section {
            margin-top: 10px;
        }

        .create-settings-summary {
            display: flex;
            min-height: 52px;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            color: var(--create-text);
            cursor: pointer;
            list-style: none;
        }

        .create-settings-summary::-webkit-details-marker {
            display: none;
        }

        .create-settings-content {
            padding: 0 14px 14px;
            border-top: .5px solid var(--create-border);
        }

        .create-field {
            display: block;
            width: 100%;
            min-height: 44px;
            border: .5px solid var(--create-border);
            border-radius: 15px;
            background: var(--create-input);
            padding: 10px 12px;
            color: var(--create-text);
            font-size: 13px;
            outline: none;
            box-shadow: var(--create-inset);
        }

        textarea.create-field {
            min-height: 92px;
            resize: vertical;
        }

        .create-field:focus {
            border-color: rgba(37, 99, 235, .68);
            background: var(--create-surface-strong);
        }

        .create-field::placeholder {
            color: var(--create-subtle);
        }

        .create-tag-chip {
            position: relative;
            display: inline-flex;
            cursor: pointer;
        }

        .create-tag-chip input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .create-tag-chip span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--create-border);
            border-radius: 999px;
            background: var(--create-surface-soft);
            padding: 0 11px;
            color: var(--create-muted);
            font-size: 12px;
        }

        .create-tag-chip input:checked + span {
            border-color: rgba(37,99,235,.48);
            background: rgba(37,99,235,.11);
            color: var(--create-blue);
        }

        .create-toggle-row {
            display: flex;
            min-height: 54px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 2px;
        }

        .create-toggle-row + .create-toggle-row {
            border-top: .5px solid var(--create-border);
        }

        .create-settings-footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 12px;
            border-top: .5px solid var(--create-border);
        }

        .create-settings-backdrop {
            display: none;
        }

        .create-mobile-settings-button {
            display: none;
        }

        .create-settings-mobile-tools {
            display: none;
            align-items: center;
            gap: 6px;
        }

        .create-preview-modal {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: none;
            overflow: auto;
            padding: 18px;
            background: rgba(2, 6, 23, .48);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .create-preview-modal.is-open {
            display: block;
        }

        .create-preview-card {
            width: min(760px, 100%);
            margin: 5vh auto;
            overflow: hidden;
            border-radius: 28px;
        }

        .create-error {
            margin-bottom: 14px;
            border: .5px solid rgba(220,38,38,.25);
            border-radius: 20px;
            background: rgba(254,226,226,.72);
            padding: 13px 15px;
            color: #991b1b;
            font-size: 13px;
        }

        html.dark .create-error,
        html[data-system-theme="dark"] .create-error {
            background: rgba(127,29,29,.24);
            color: #fecaca;
        }

        .create-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            z-index: 150;
            width: min(430px, calc(100vw - 28px));
            transform: translateX(-50%);
            border: .5px solid var(--create-border);
            border-radius: 18px;
            background: var(--create-surface-strong);
            padding: 12px 14px;
            color: var(--create-text);
            font-size: 13px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        @media (max-width: 1023px) {
            .create-page-fixed {
                padding-bottom: 82px;
            }

            .create-layout {
                display: block;
                margin-top: 12px;
            }

            .create-topbar {
                top: 8px;
                min-height: 58px;
                border-radius: 22px;
            }

            .create-topbar .create-action-label {
                display: none;
            }

            .create-topbar .create-action-button:not(.create-action-button--primary) {
                width: 42px;
                min-width: 42px;
                height: 42px;
                min-height: 42px;
                padding: 0;
            }

            .create-settings {
                position: fixed;
                left: 10px;
                right: 10px;
                bottom: 10px;
                top: auto;
                z-index: 111;
                display: none;
                max-height: calc(100vh - 90px);
                border-radius: 28px;
            }

            .create-settings.is-open {
                display: block;
            }

            .create-settings-scroll {
                max-height: calc(100vh - 210px);
            }

            .create-settings-backdrop {
                position: fixed;
                inset: 0;
                z-index: 110;
                background: rgba(2,6,23,.42);
                backdrop-filter: blur(7px);
                -webkit-backdrop-filter: blur(7px);
            }

            .create-settings-backdrop.is-open {
                display: block;
            }

            .create-mobile-settings-button {
                display: inline-flex;
            }

            .create-settings-mobile-tools {
                display: flex;
            }
        }

        @media (max-width: 640px) {
            .create-topbar {
                gap: 8px;
                padding: 8px;
            }

            .create-brand-mark {
                display: none;
            }

            .create-topbar [data-open-preview],
            .create-topbar [data-ai-assist] {
                display: none;
            }

            .create-topbar .create-action-button--primary {
                min-height: 40px;
                padding-inline: 13px;
            }

            .create-icon-button {
                width: 40px;
                height: 40px;
                flex-basis: 40px;
            }

            .create-editor-card {
                border-radius: 24px;
            }

            .create-editor-head {
                padding: 13px;
            }

            .create-cover,
            .create-cover-drop {
                min-height: 142px;
                border-radius: 18px;
            }

            .create-title {
                margin-top: 18px;
                font-size: 1.85rem;
            }

            .create-editor-body [x-ref="holder"] {
                min-height: 56vh !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .create-settings-footer {
                grid-template-columns: 1fr;
            }

            .create-preview-modal {
                padding: 10px;
            }
        }

        @media (prefers-reduced-transparency: reduce) {
            .create-glass,
            .create-glass-strong,
            .create-preview-modal,
            .create-settings-backdrop,
            .create-toast {
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
        }
    </style>

    <div class="create-page-fixed">
        <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published', 0) ? 1 : 0 }}">
            <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">

            <div class="mx-auto min-h-screen w-full max-w-[1190px] px-2.5 py-2.5 sm:px-4 sm:py-3 lg:px-5">
                <header class="create-topbar create-glass-strong">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <a href="{{ route('blog.index') }}" class="create-icon-button" aria-label="{{ __('post_create.back') }}" title="{{ __('post_create.back') }}">
                            <iconify-icon icon="lucide:chevron-left" class="text-[22px]"></iconify-icon>
                        </a>

                        <div class="create-brand-mark" aria-hidden="true">
                            <iconify-icon icon="lucide:pen-line" class="text-[18px]"></iconify-icon>
                        </div>

                        <div class="min-w-0">
                            <div class="truncate text-[15px] font-semibold leading-5">Ografi</div>
                            <div class="truncate text-[12px] text-[var(--create-muted)]">Yeni gönderi</div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5">
                        <button type="button" class="create-action-button" data-open-preview aria-label="Ön izleme">
                            <iconify-icon icon="lucide:eye" class="text-[18px]"></iconify-icon>
                            <span class="create-action-label">Ön izleme</span>
                        </button>

                        <button type="button" class="create-action-button" data-ai-assist aria-label="Yapay zeka yardımcısı">
                            <iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[18px]"></iconify-icon>
                            <span class="create-action-label">AI</span>
                        </button>

                        <button type="button" class="create-action-button create-mobile-settings-button" data-open-settings aria-label="Gelişmiş seçenekler">
                            <iconify-icon icon="lucide:sliders-horizontal" class="text-[18px]"></iconify-icon>
                        </button>

                        <button type="submit" class="create-action-button create-action-button--primary" data-submit-intent="publish">
                            <iconify-icon icon="lucide:send" class="text-[17px]"></iconify-icon>
                            <span>Yayınla</span>
                        </button>
                    </div>
                </header>

                <div class="create-layout">
                    <main class="min-w-0">
                        @if ($errors->any())
                            <div class="create-error">
                                <div class="font-semibold">Gönderi kaydedilemedi.</div>
                                <div class="mt-1">{{ $errors->first() }}</div>
                            </div>
                        @endif

                        <section class="create-editor-card create-glass-strong">
                            <div class="create-editor-head">
                                <div class="create-cover" data-cover-field>
                                    <label for="featured_image" class="create-cover-drop">
                                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[var(--create-border)] bg-[var(--create-surface-soft)]">
                                            <iconify-icon icon="lucide:image-plus" class="text-[21px]"></iconify-icon>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-[14px] font-semibold text-[var(--create-text)]">Kapak görseli ekle</span>
                                            <span class="mt-1 block text-[12px] text-[var(--create-muted)]">JPG, PNG veya WebP · en fazla 5 MB</span>
                                        </span>
                                    </label>

                                    <div class="create-cover-preview" data-cover-preview>
                                        <img data-cover-preview-img alt="">
                                        <div class="create-cover-preview-actions">
                                            <button type="button" class="create-action-button !min-h-9 !px-3 !text-xs" data-cover-change>
                                                <iconify-icon icon="lucide:pencil" class="text-[14px]"></iconify-icon>
                                                Değiştir
                                            </button>
                                            <button type="button" class="create-action-button !min-h-9 !px-3 !text-xs" data-cover-remove>
                                                <iconify-icon icon="lucide:x" class="text-[14px]"></iconify-icon>
                                                Kaldır
                                            </button>
                                        </div>
                                    </div>

                                    <input id="featured_image" name="featured_image" type="file" accept="image/*" class="sr-only" data-cover-input>
                                </div>

                                <textarea
                                    id="title"
                                    name="title"
                                    rows="1"
                                    required
                                    class="create-title"
                                    placeholder="Başlığını yaz..."
                                    data-autogrow
                                >{{ old('title') }}</textarea>

                                <div class="create-meta-row">
                                    <span class="create-meta-item">
                                        <iconify-icon icon="lucide:clock-3" class="text-[14px]"></iconify-icon>
                                        <span data-reading-time>1 dk okuma</span>
                                    </span>
                                    <span class="create-meta-item">
                                        <iconify-icon icon="lucide:type" class="text-[14px]"></iconify-icon>
                                        <span data-word-count>0 kelime</span>
                                    </span>
                                    <span class="create-meta-item">
                                        <iconify-icon icon="lucide:save" class="text-[14px]"></iconify-icon>
                                        <span>Taslak destekli</span>
                                    </span>
                                </div>
                            </div>

                            <div class="create-editor-body" data-editorjs-wrapper>
                                <div x-ref="holder" class="min-h-[58vh] px-4 py-6 text-[var(--create-text)] sm:px-7 sm:py-8"></div>
                                <textarea
                                    id="content"
                                    name="content"
                                    data-editor-content
                                    data-mentionable="users"
                                    class="hidden min-h-[58vh] w-full resize-none bg-transparent px-5 py-6 text-[var(--create-text)] outline-none"
                                    placeholder="Gönderini yazmaya başla..."
                                >{{ old('content') }}</textarea>
                            </div>
                        </section>
                    </main>

                    <aside class="create-settings create-glass-strong" data-settings-panel aria-label="Gelişmiş seçenekler">
                        <div class="create-settings-head">
                            <div>
                                <div class="text-[15px] font-semibold">Gelişmiş seçenekler</div>
                                <div class="mt-0.5 text-[12px] text-[var(--create-muted)]">Yayın, SEO ve görünürlük ayarları</div>
                            </div>
                            <div class="create-settings-mobile-tools">
                                <button type="button" class="create-icon-button !h-9 !w-9 !basis-9" data-open-preview aria-label="Ön izleme">
                                    <iconify-icon icon="lucide:eye" class="text-[17px]"></iconify-icon>
                                </button>
                                <button type="button" class="create-icon-button !h-9 !w-9 !basis-9" data-ai-assist aria-label="Yapay zeka yardımcısı">
                                    <iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[17px]"></iconify-icon>
                                </button>
                                <button type="button" class="create-icon-button !h-9 !w-9 !basis-9" data-close-settings aria-label="Kapat">
                                    <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                                </button>
                            </div>
                        </div>

                        <div class="create-settings-scroll">
                            <details class="create-settings-section" open>
                                <summary class="create-settings-summary">
                                    <span class="flex items-center gap-2.5">
                                        <iconify-icon icon="lucide:layout-list" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                                        <span class="text-[13px] font-semibold">Gönderi bilgileri</span>
                                    </span>
                                    <iconify-icon icon="lucide:chevron-down" class="text-[16px] text-[var(--create-muted)]"></iconify-icon>
                                </summary>
                                <div class="create-settings-content space-y-3">
                                    <div class="pt-3">
                                        <label for="category_id" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">Topluluk / kategori</label>
                                        <select id="category_id" name="category_id" class="create-field">
                                            <option value="">Kategori seç</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected($initialCategoryId === (int) $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="excerpt" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">Kısa açıklama</label>
                                        <textarea id="excerpt" name="excerpt" class="create-field" rows="3" placeholder="Gönderinin kısa özeti...">{{ old('excerpt') }}</textarea>
                                    </div>

                                    <div>
                                        <label for="new_tags" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">Yeni etiketler</label>
                                        <input id="new_tags" name="new_tags" type="text" class="create-field" value="{{ old('new_tags') }}" placeholder="laravel, tasarım, teknoloji">
                                    </div>

                                    @if(isset($tags) && collect($tags)->isNotEmpty())
                                        <div>
                                            <div class="mb-2 text-[11px] font-medium text-[var(--create-muted)]">Mevcut etiketler</div>
                                            <div class="flex max-h-36 flex-wrap gap-1.5 overflow-y-auto pr-1">
                                                @foreach($tags as $tag)
                                                    <label class="create-tag-chip">
                                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id))>
                                                        <span>#{{ $tag->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </details>

                            <details class="create-settings-section">
                                <summary class="create-settings-summary">
                                    <span class="flex items-center gap-2.5">
                                        <iconify-icon icon="lucide:calendar-clock" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                                        <span class="text-[13px] font-semibold">Yayınlama</span>
                                    </span>
                                    <iconify-icon icon="lucide:chevron-down" class="text-[16px] text-[var(--create-muted)]"></iconify-icon>
                                </summary>
                                <div class="create-settings-content space-y-3">
                                    <div class="pt-3">
                                        <label for="published_at" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">Yayın tarihi</label>
                                        <input id="published_at" name="published_at" type="datetime-local" class="create-field" value="{{ old('published_at') }}">
                                    </div>

                                    <div class="rounded-[16px] border border-[var(--create-border)] px-3">
                                        <div class="create-toggle-row">
                                            <div>
                                                <div class="text-[13px] font-medium">Yorumları kapat</div>
                                                <div class="text-[11px] text-[var(--create-muted)]">Bu gönderiye yeni yorum alınmaz.</div>
                                            </div>
                                            <x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" />
                                        </div>

                                        <div class="create-toggle-row">
                                            <div>
                                                <div class="text-[13px] font-medium">Hassas içerik</div>
                                                <div class="text-[11px] text-[var(--create-muted)]">İçerik uyarısıyla gösterilir.</div>
                                            </div>
                                            <x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" />
                                        </div>

                                        <div class="create-toggle-row">
                                            <div>
                                                <div class="text-[13px] font-medium">Gönderiyi sabitle</div>
                                                <div class="text-[11px] text-[var(--create-muted)]">Uygun alanlarda üstte gösterilir.</div>
                                            </div>
                                            <x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" />
                                        </div>
                                    </div>
                                </div>
                            </details>

                            <details class="create-settings-section">
                                <summary class="create-settings-summary">
                                    <span class="flex items-center gap-2.5">
                                        <iconify-icon icon="lucide:search" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                                        <span class="text-[13px] font-semibold">SEO</span>
                                    </span>
                                    <iconify-icon icon="lucide:chevron-down" class="text-[16px] text-[var(--create-muted)]"></iconify-icon>
                                </summary>
                                <div class="create-settings-content space-y-3">
                                    <div class="pt-3">
                                        <label for="meta_title" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">SEO başlığı</label>
                                        <input id="meta_title" name="meta_title" type="text" class="create-field" value="{{ old('meta_title') }}" placeholder="Arama sonucunda görünecek başlık">
                                    </div>

                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between gap-2">
                                            <label for="meta_description" class="text-[11px] font-medium text-[var(--create-muted)]">Meta açıklama</label>
                                            <span class="text-[10px] text-[var(--create-subtle)]" data-meta-description-count>0/160</span>
                                        </div>
                                        <textarea id="meta_description" name="meta_description" maxlength="160" class="create-field" rows="3" placeholder="Arama sonucunda görünecek açıklama">{{ old('meta_description') }}</textarea>
                                    </div>

                                    <div>
                                        <label for="slug" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">Özel bağlantı</label>
                                        <input id="slug" name="slug" type="text" class="create-field" value="{{ old('slug') }}" placeholder="ornek-gonderi">
                                    </div>

                                    <div>
                                        <label for="meta_keywords" class="mb-1.5 block text-[11px] font-medium text-[var(--create-muted)]">Anahtar kelimeler</label>
                                        <input id="meta_keywords" name="meta_keywords" type="text" class="create-field" value="{{ old('meta_keywords') }}" placeholder="teknoloji, yazılım, gündem">
                                    </div>
                                </div>
                            </details>

                            <details class="create-settings-section">
                                <summary class="create-settings-summary">
                                    <span class="flex items-center gap-2.5">
                                        <iconify-icon icon="lucide:copyright" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                                        <span class="text-[13px] font-semibold">Görsel hakları</span>
                                    </span>
                                    <iconify-icon icon="lucide:chevron-down" class="text-[16px] text-[var(--create-muted)]"></iconify-icon>
                                </summary>
                                <div class="create-settings-content space-y-3">
                                    <div class="pt-3">
                                        <input id="image_creator_name" name="image_creator_name" type="text" class="create-field" value="{{ old('image_creator_name') }}" placeholder="Görsel üreticisi / fotoğrafçı">
                                    </div>
                                    <input id="image_credit_text" name="image_credit_text" type="text" class="create-field" value="{{ old('image_credit_text') }}" placeholder="Görsel kredisi">
                                    <input id="image_copyright_notice" name="image_copyright_notice" type="text" class="create-field" value="{{ old('image_copyright_notice') }}" placeholder="Telif bildirimi">
                                    <input id="image_license_url" name="image_license_url" type="url" class="create-field" value="{{ old('image_license_url') }}" placeholder="Lisans bağlantısı">
                                    <input id="image_acquire_url" name="image_acquire_url" type="url" class="create-field" value="{{ old('image_acquire_url') }}" placeholder="Kaynak / satın alma bağlantısı">
                                </div>
                            </details>
                        </div>

                        <div class="create-settings-footer">
                            <button type="submit" class="create-action-button" data-submit-intent="draft">
                                <iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>
                                Taslak kaydet
                            </button>
                            <button type="submit" class="create-action-button create-action-button--primary" data-submit-intent="publish">
                                <iconify-icon icon="lucide:send" class="text-[16px]"></iconify-icon>
                                Yayınla
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </form>

        <div class="create-settings-backdrop" data-settings-backdrop></div>

        <div class="create-preview-modal" data-preview-modal aria-hidden="true">
            <div class="create-preview-card create-glass-strong">
                <div class="create-settings-head">
                    <div>
                        <div class="text-[15px] font-semibold">Gönderi ön izlemesi</div>
                        <div class="mt-0.5 text-[12px] text-[var(--create-muted)]">Yayınlanmadan önce son görünüm</div>
                    </div>
                    <button type="button" class="create-icon-button !h-9 !w-9 !basis-9" data-close-preview aria-label="Kapat">
                        <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                    </button>
                </div>
                <div class="max-h-[78vh] overflow-auto p-4 sm:p-6">
                    <div data-preview-content class="prose prose-slate max-w-none dark:prose-invert"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('filament.assets.editorjs')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('post-create-form');
            const wrapper = document.querySelector('[data-editorjs-wrapper]');
            const fallbackTextarea = document.getElementById('content');
            const jsonField = document.getElementById('content_json');
            const isPublishedInput = document.getElementById('is_published');
            const titleField = document.getElementById('title');
            const coverInput = document.querySelector('[data-cover-input]');
            const coverField = document.querySelector('[data-cover-field]');
            const coverPreviewImg = document.querySelector('[data-cover-preview-img]');
            const settingsPanel = document.querySelector('[data-settings-panel]');
            const settingsBackdrop = document.querySelector('[data-settings-backdrop]');
            const previewModal = document.querySelector('[data-preview-modal]');
            const previewContent = document.querySelector('[data-preview-content]');
            const metaDescription = document.getElementById('meta_description');
            const metaDescriptionCount = document.querySelector('[data-meta-description-count]');
            const wordCountEl = document.querySelector('[data-word-count]');
            const readingTimeEl = document.querySelector('[data-reading-time]');

            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            const showToast = (message, isError = false) => {
                document.querySelectorAll('.create-toast').forEach((el) => el.remove());
                const toast = document.createElement('div');
                toast.className = 'create-toast';
                toast.textContent = message;
                if (isError) {
                    toast.style.borderColor = 'rgba(220,38,38,.34)';
                    toast.style.color = '#ef4444';
                }
                document.body.appendChild(toast);
                window.setTimeout(() => toast.remove(), isError ? 5500 : 3500);
            };

            const autoGrowTitle = () => {
                if (!titleField) return;
                titleField.style.height = 'auto';
                titleField.style.height = `${titleField.scrollHeight}px`;
            };

            titleField?.addEventListener('input', autoGrowTitle);
            autoGrowTitle();

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

            const openSettings = () => {
                if (!settingsPanel || isDesktop()) return;
                settingsPanel.classList.add('is-open');
                settingsBackdrop?.classList.add('is-open');
                document.documentElement.classList.add('overflow-hidden');
            };

            const closeSettings = () => {
                if (!settingsPanel) return;
                settingsPanel.classList.remove('is-open');
                settingsBackdrop?.classList.remove('is-open');
                document.documentElement.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-open-settings]').forEach((button) => button.addEventListener('click', openSettings));
            document.querySelectorAll('[data-close-settings]').forEach((button) => button.addEventListener('click', closeSettings));
            settingsBackdrop?.addEventListener('click', closeSettings);

            window.addEventListener('resize', () => {
                if (isDesktop()) closeSettings();
            });

            const syncMetaCount = () => {
                if (!metaDescription || !metaDescriptionCount) return;
                metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
            };

            metaDescription?.addEventListener('input', syncMetaCount);
            syncMetaCount();

            const blockText = (block) => {
                const data = block?.data || {};
                const items = Array.isArray(data.items) ? data.items : [];
                return [data.text, data.caption, data.question, data.answer, data.title, data.label, ...items]
                    .filter((value) => typeof value === 'string')
                    .join(' ');
            };

            const readEditorData = async () => {
                if (wrapper?.__editorInstance?.save) {
                    try {
                        return await wrapper.__editorInstance.save();
                    } catch {}
                }

                if (jsonField?.value) {
                    try {
                        return JSON.parse(jsonField.value);
                    } catch {}
                }

                return { blocks: [] };
            };

            const readEditorPlainText = async () => {
                const data = await readEditorData();
                const blockTextValue = (data?.blocks || []).map(blockText).join(' ').trim();
                return blockTextValue || String(fallbackTextarea?.value || '').trim();
            };

            const updateStats = async () => {
                const text = await readEditorPlainText();
                const words = (text.match(/\S+/g) || []).length;
                if (wordCountEl) wordCountEl.textContent = `${words} kelime`;
                if (readingTimeEl) readingTimeEl.textContent = `${Math.max(1, Math.ceil(words / 200))} dk okuma`;
            };

            let statsTimer = null;
            const scheduleStats = () => {
                window.clearTimeout(statsTimer);
                statsTimer = window.setTimeout(updateStats, 450);
            };

            wrapper?.addEventListener('input', scheduleStats);
            wrapper?.addEventListener('keyup', scheduleStats);
            titleField?.addEventListener('input', scheduleStats);

            const showFallback = () => {
                wrapper?.querySelector('[x-ref="holder"]')?.classList.add('hidden');
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
                } catch (error) {
                    console.error(error);
                    showFallback();
                }
            };

            initEditor();
            window.setTimeout(updateStats, 1000);

            const syncEditorFields = async () => {
                if (!wrapper?.__editorInstance?.save) return;
                const output = await wrapper.__editorInstance.save();
                if (jsonField) jsonField.value = JSON.stringify(output);
                if (fallbackTextarea && window.filamentEditorBlocksToHtml) {
                    fallbackTextarea.value = window.filamentEditorBlocksToHtml(output.blocks || []);
                }
            };

            let submitLocked = false;

            document.querySelectorAll('[data-submit-intent]').forEach((button) => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    if (!form || submitLocked) return;

                    const intent = button.getAttribute('data-submit-intent') === 'draft' ? 'draft' : 'publish';
                    const title = String(titleField?.value || '').trim();

                    try {
                        await syncEditorFields();
                    } catch {
                        showToast('İçerik hazırlanırken hata oluştu.', true);
                        return;
                    }

                    const content = String(fallbackTextarea?.value || '').trim();

                    if (!title || !content) {
                        showToast('Başlık ve içerik alanlarını doldurun.', true);
                        return;
                    }

                    if (isPublishedInput) isPublishedInput.value = intent === 'publish' ? '1' : '0';

                    submitLocked = true;
                    form.submit();
                });
            });

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const buildPreview = async () => {
                const title = String(titleField?.value || '').trim();
                const excerpt = String(document.getElementById('excerpt')?.value || '').trim();
                const data = await readEditorData();
                const contentHtml = window.filamentEditorBlocksToHtml
                    ? window.filamentEditorBlocksToHtml(data?.blocks || [])
                    : String(fallbackTextarea?.value || '');

                const image = coverPreviewImg?.src
                    ? `<img src="${coverPreviewImg.src}" alt="" class="mb-5 w-full rounded-[22px] object-cover">`
                    : '';

                return `
                    ${image}
                    <div class="text-[12px] font-medium text-[var(--create-muted)]">Ön izleme</div>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-[var(--create-text)]">${escapeHtml(title || 'Başlıksız gönderi')}</h1>
                    ${excerpt ? `<p class="mt-3 text-[15px] leading-6 text-[var(--create-muted)]">${escapeHtml(excerpt)}</p>` : ''}
                    <div class="mt-7 text-[15px] leading-7 text-[var(--create-text)]">${contentHtml || '<p>Henüz içerik yok.</p>'}</div>
                `;
            };

            const openPreview = async () => {
                if (!previewModal || !previewContent) return;
                if (!isDesktop()) closeSettings();
                previewContent.innerHTML = await buildPreview();
                previewModal.classList.add('is-open');
                previewModal.setAttribute('aria-hidden', 'false');
                document.documentElement.classList.add('overflow-hidden');
            };

            const closePreview = () => {
                previewModal?.classList.remove('is-open');
                previewModal?.setAttribute('aria-hidden', 'true');
                document.documentElement.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-open-preview]').forEach((button) => button.addEventListener('click', openPreview));
            document.querySelectorAll('[data-close-preview]').forEach((button) => button.addEventListener('click', closePreview));

            previewModal?.addEventListener('click', (event) => {
                if (event.target === previewModal) closePreview();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                closeSettings();
                closePreview();
            });

            const aiAssistButtons = Array.from(document.querySelectorAll('[data-ai-assist]'));
            let aiBusy = false;

            const runAiAssist = async (button) => {
                if (aiBusy) return;

                const title = String(titleField?.value || '').trim();
                const content = await readEditorPlainText();

                if (!title && !content) {
                    showToast('Önce başlık veya içerik yazın.', true);
                    return;
                }

                aiBusy = true;
                aiAssistButtons.forEach((item) => { item.disabled = true; });
                aiAssistButtons.forEach((item) => item.querySelector('[data-ai-assist-icon]')?.setAttribute('icon', 'lucide:loader-circle'));

                try {
                    const response = await fetch('{{ route('blog.ai-assist') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ title, content }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Yapay zeka isteği başarısız.');
                    }

                    const metaTitle = document.getElementById('meta_title');
                    const metaKeywords = document.getElementById('meta_keywords');
                    const excerptField = document.getElementById('excerpt');

                    if (data.meta_title && metaTitle) metaTitle.value = data.meta_title;
                    if (data.meta_description && metaDescription) {
                        metaDescription.value = data.meta_description;
                        syncMetaCount();
                    }
                    if (Array.isArray(data.meta_keywords) && metaKeywords) {
                        metaKeywords.value = data.meta_keywords.join(', ');
                    }
                    if (data.excerpt && excerptField && !excerptField.value.trim()) {
                        excerptField.value = data.excerpt;
                    }

                    showToast('AI önerileri gelişmiş seçeneklere işlendi.');
                    if (!isDesktop()) openSettings();
                } catch (error) {
                    showToast(error?.message || 'Yapay zeka isteği başarısız.', true);
                } finally {
                    aiBusy = false;
                    aiAssistButtons.forEach((item) => {
                        item.disabled = false;
                        item.querySelector('[data-ai-assist-icon]')?.setAttribute('icon', 'lucide:sparkles');
                    });
                }
            };

            aiAssistButtons.forEach((button) => {
                button.addEventListener('click', () => runAiAssist(button));
            });
        });
    </script>
@endpush
