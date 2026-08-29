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
        $initialCategoryId = (int) old('category_id');
    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap');

        :root {
            --create-bg: #f4f5f7;
            --create-surface: rgba(255, 255, 255, .72);
            --create-surface-strong: rgba(255, 255, 255, .90);
            --create-surface-soft: rgba(255, 255, 255, .52);
            --create-border: rgba(15, 23, 42, .14);
            --create-border-strong: rgba(15, 23, 42, .20);
            --create-text: #111827;
            --create-muted: #64748b;
            --create-subtle: #94a3b8;
            --create-blue: #2563eb;
            --create-blue-hover: #1d4ed8;
            --create-danger: #dc2626;
            --create-input: rgba(255, 255, 255, .78);
            --create-glass-shadow:
                inset 0 1px 0 rgba(255,255,255,.72),
                inset 0 -1px 0 rgba(15,23,42,.04),
                0 2px 10px rgba(15,23,42,.06);
        }

        html.dark,
        html[data-system-theme="dark"],
        body.dark {
            --create-bg: #080d17;
            --create-surface: rgba(17, 24, 39, .72);
            --create-surface-strong: rgba(15, 23, 42, .92);
            --create-surface-soft: rgba(30, 41, 59, .58);
            --create-border: rgba(255, 255, 255, .12);
            --create-border-strong: rgba(255, 255, 255, .18);
            --create-text: #f8fafc;
            --create-muted: #a6b1c2;
            --create-subtle: #748196;
            --create-input: rgba(15, 23, 42, .72);
            --create-glass-shadow:
                inset 0 1px 0 rgba(255,255,255,.08),
                inset 0 -1px 0 rgba(0,0,0,.18),
                0 2px 10px rgba(0,0,0,.16);
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

        .create-page-fixed,
        .create-page-fixed *:not(iconify-icon) {
            font-family: 'Roboto', 'Inter', Arial, Helvetica, sans-serif;
        }

        .create-page-fixed {
            position: fixed;
            inset: 0;
            z-index: 99999;
            overflow: auto;
            color: var(--create-text);
            background: var(--create-bg);
        }

        .create-shell {
            width: min(100%, 900px);
            min-height: 100vh;
            margin: 0 auto;
            padding: 12px 14px 34px;
        }

        .create-page-fixed.settings-open .create-shell {
            width: min(100%, 1310px);
            padding-right: 410px;
        }

        .create-glass {
            border: .5px solid var(--create-border);
            background: var(--create-surface);
            box-shadow: var(--create-glass-shadow);
            backdrop-filter: blur(22px) saturate(150%);
            -webkit-backdrop-filter: blur(22px) saturate(150%);
        }

        .create-glass-strong {
            border: .5px solid var(--create-border-strong);
            background: var(--create-surface-strong);
            box-shadow: var(--create-glass-shadow);
            backdrop-filter: blur(30px) saturate(165%);
            -webkit-backdrop-filter: blur(30px) saturate(165%);
        }

        .create-topbar {
            position: sticky;
            top: 10px;
            z-index: 80;
            display: flex;
            min-height: 62px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 9px;
            border-radius: 999px;
        }

        .create-topbar-left,
        .create-topbar-actions {
            display: flex;
            align-items: center;
        }

        .create-topbar-left {
            min-width: 0;
            gap: 9px;
        }

        .create-topbar-actions {
            flex: 0 0 auto;
            gap: 6px;
        }

        .create-icon-button,
        .create-pill-button {
            border: .5px solid var(--create-border);
            background: var(--create-surface-soft);
            color: var(--create-text);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.30);
        }

        .create-icon-button {
            display: inline-flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            text-decoration: none;
        }

        .create-pill-button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 999px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1;
        }

        .create-icon-button:hover,
        .create-icon-button:focus-visible,
        .create-pill-button:hover,
        .create-pill-button:focus-visible {
            background: var(--create-surface-strong);
            outline: none;
        }

        .create-pill-button--primary {
            border-color: rgba(37, 99, 235, .90);
            background: var(--create-blue);
            color: #fff;
        }

        .create-pill-button--primary:hover,
        .create-pill-button--primary:focus-visible {
            background: var(--create-blue-hover);
        }

        .create-brand {
            min-width: 0;
            padding-right: 4px;
        }

        .create-brand-title {
            overflow: hidden;
            color: var(--create-text);
            font-size: 15px;
            font-weight: 600;
            line-height: 19px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .create-brand-subtitle {
            overflow: hidden;
            margin-top: 1px;
            color: var(--create-muted);
            font-size: 12px;
            line-height: 16px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .create-info-wrap {
            position: relative;
        }

        .create-info-popover {
            position: absolute;
            top: 50px;
            right: 0;
            z-index: 100;
            display: none;
            width: 245px;
            overflow: hidden;
            border-radius: 22px;
        }

        .create-info-popover.is-open {
            display: block;
        }

        .create-info-head {
            padding: 13px 14px 10px;
            border-bottom: .5px solid var(--create-border);
            font-size: 13px;
            font-weight: 600;
        }

        .create-info-row {
            display: flex;
            min-height: 42px;
            align-items: center;
            gap: 10px;
            padding: 0 14px;
            color: var(--create-muted);
            font-size: 13px;
        }

        .create-info-row + .create-info-row {
            border-top: .5px solid var(--create-border);
        }

        .create-main {
            margin-top: 16px;
        }

        .create-editor-card {
            overflow: visible;
            border-radius: 28px;
        }

        .create-editor-head {
            padding: 26px 30px 18px;
            border-bottom: .5px solid var(--create-border);
        }

        .create-field-label {
            display: block;
            margin-bottom: 8px;
            color: var(--create-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .create-title {
            display: block;
            width: 100%;
            min-height: 40px;
            margin: 0;
            overflow: hidden;
            resize: none;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--create-text);
            font-size: 28px;
            font-weight: 600;
            line-height: 1.22;
            letter-spacing: -.018em;
        }

        .create-title::placeholder {
            color: var(--create-subtle);
        }

        .create-editor-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 30px 0;
            color: var(--create-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .create-editor-body {
            min-height: 66vh;
            padding-bottom: 18px;
        }

        .create-editor-body [x-ref="holder"] {
            min-height: 62vh;
            padding: 18px 30px 40px;
            color: var(--create-text);
        }

        .create-editor-body textarea[data-editor-content] {
            min-height: 62vh;
            width: 100%;
            resize: none;
            border: 0;
            outline: none;
            background: transparent;
            padding: 22px 30px 40px;
            color: var(--create-text);
            font-size: 16px;
            font-weight: 400;
            line-height: 1.72;
        }

        [data-editorjs-wrapper] .codex-editor,
        [data-editorjs-wrapper] .codex-editor *:not(iconify-icon) {
            font-family: 'Roboto', 'Inter', Arial, Helvetica, sans-serif !important;
        }

        [data-editorjs-wrapper] .codex-editor__redactor {
            padding-bottom: 130px !important;
        }

        [data-editorjs-wrapper] .ce-block__content,
        [data-editorjs-wrapper] .ce-toolbar__content {
            max-width: 700px !important;
        }

        [data-editorjs-wrapper] .ce-paragraph {
            color: var(--create-text) !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.72 !important;
        }

        [data-editorjs-wrapper] .ce-header {
            color: var(--create-text) !important;
            font-family: 'Roboto', 'Inter', Arial, Helvetica, sans-serif !important;
            font-weight: 600 !important;
            line-height: 1.28 !important;
        }

        [data-editorjs-wrapper] .ce-toolbar__plus,
        [data-editorjs-wrapper] .ce-toolbar__settings-btn {
            width: 34px !important;
            height: 34px !important;
            border: .5px solid var(--create-border) !important;
            border-radius: 999px !important;
            background: var(--create-surface-soft) !important;
            color: var(--create-muted) !important;
        }

        [data-editorjs-wrapper] .ce-popover,
        [data-editorjs-wrapper] .ce-inline-toolbar,
        [data-editorjs-wrapper] .ce-conversion-toolbar {
            border: .5px solid var(--create-border) !important;
            border-radius: 18px !important;
            background: var(--create-surface-strong) !important;
            color: var(--create-text) !important;
            box-shadow: var(--create-glass-shadow) !important;
            backdrop-filter: blur(24px) saturate(150%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(150%) !important;
        }

        html.dark [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50),
        html[data-system-theme="dark"] [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50) {
            background-color: rgba(15, 23, 42, .80) !important;
        }

        html.dark [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700),
        html[data-system-theme="dark"] [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700) {
            color: #e5edf8 !important;
        }

        html.dark [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300),
        html[data-system-theme="dark"] [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300) {
            border-color: rgba(255,255,255,.12) !important;
        }

        .create-settings-backdrop {
            position: fixed;
            inset: 0;
            z-index: 108;
            display: none;
            background: rgba(2, 6, 23, .32);
        }

        .create-settings-backdrop.is-open {
            display: block;
        }

        .create-settings {
            position: fixed;
            top: 12px;
            right: 12px;
            bottom: 12px;
            z-index: 110;
            display: none;
            width: min(388px, calc(100vw - 24px));
            overflow: hidden;
            border-radius: 30px;
        }

        .create-settings.is-open {
            display: flex;
            flex-direction: column;
        }

        .create-settings-head {
            display: flex;
            flex: 0 0 auto;
            min-height: 70px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px 12px 18px;
            border-bottom: .5px solid var(--create-border);
        }

        .create-settings-title {
            font-size: 15px;
            font-weight: 600;
            line-height: 20px;
        }

        .create-settings-subtitle {
            margin-top: 2px;
            color: var(--create-muted);
            font-size: 12px;
            line-height: 16px;
        }

        .create-settings-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 12px;
        }

        .create-settings-group {
            overflow: hidden;
            border: .5px solid var(--create-border);
            border-radius: 21px;
            background: rgba(255,255,255,.16);
        }

        html.dark .create-settings-group,
        html[data-system-theme="dark"] .create-settings-group {
            background: rgba(15,23,42,.24);
        }

        .create-settings-group + .create-settings-group {
            margin-top: 10px;
        }

        .create-settings-group-title {
            display: flex;
            min-height: 46px;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
            border-bottom: .5px solid var(--create-border);
            color: var(--create-text);
            font-size: 13px;
            font-weight: 600;
        }

        .create-settings-group-body {
            padding: 12px;
        }

        .create-cover {
            position: relative;
            overflow: hidden;
            width: 100%;
            min-height: 150px;
            border: .5px solid var(--create-border);
            border-radius: 18px;
            background: var(--create-surface-soft);
        }

        .create-cover-drop {
            display: flex;
            min-height: 150px;
            align-items: center;
            justify-content: center;
            gap: 11px;
            padding: 18px;
            color: var(--create-muted);
            cursor: pointer;
            text-align: left;
        }

        .create-cover-drop:hover {
            background: rgba(37,99,235,.06);
            color: var(--create-blue);
        }

        .create-cover-preview {
            display: none;
            position: relative;
            min-height: 150px;
        }

        .create-cover-preview img {
            display: block;
            width: 100%;
            max-height: 260px;
            object-fit: cover;
        }

        .create-cover-preview-actions {
            position: absolute;
            top: 9px;
            right: 9px;
            display: flex;
            gap: 6px;
        }

        .create-cover.has-image .create-cover-drop {
            display: none;
        }

        .create-cover.has-image .create-cover-preview {
            display: block;
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
            font-size: 14px;
            font-weight: 400;
            outline: none;
        }

        textarea.create-field {
            min-height: 88px;
            resize: vertical;
        }

        .create-field:focus {
            border-color: rgba(37,99,235,.65);
            background: var(--create-surface-strong);
        }

        .create-field::placeholder {
            color: var(--create-subtle);
        }

        .create-setting-label {
            display: block;
            margin-bottom: 6px;
            color: var(--create-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .create-stack > * + * {
            margin-top: 11px;
        }

        .create-tools {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .create-tools .create-pill-button {
            width: 100%;
            min-height: 42px;
            padding-inline: 12px;
            font-size: 13px;
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
            min-height: 32px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--create-border);
            border-radius: 999px;
            background: var(--create-surface-soft);
            padding: 0 10px;
            color: var(--create-muted);
            font-size: 12px;
        }

        .create-tag-chip input:checked + span {
            border-color: rgba(37,99,235,.42);
            background: rgba(37,99,235,.11);
            color: var(--create-blue);
        }

        .create-toggle-list {
            overflow: hidden;
            border: .5px solid var(--create-border);
            border-radius: 16px;
        }

        .create-toggle-row {
            display: flex;
            min-height: 57px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 10px;
        }

        .create-toggle-row + .create-toggle-row {
            border-top: .5px solid var(--create-border);
        }

        .create-toggle-title {
            font-size: 13px;
            font-weight: 500;
        }

        .create-toggle-note {
            margin-top: 2px;
            color: var(--create-muted);
            font-size: 11px;
            line-height: 15px;
        }

        .create-settings-footer {
            flex: 0 0 auto;
            padding: 11px 12px 12px;
            border-top: .5px solid var(--create-border);
        }

        .create-settings-footer .create-pill-button {
            width: 100%;
        }

        .create-preview-modal {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: none;
            overflow: auto;
            padding: 18px;
            background: rgba(2,6,23,.42);
        }

        .create-preview-modal.is-open {
            display: block;
        }

        .create-preview-card {
            width: min(760px, 100%);
            margin: 4vh auto;
            overflow: hidden;
            border-radius: 28px;
        }

        .create-preview-body {
            max-height: 78vh;
            overflow: auto;
            padding: 20px 24px 30px;
        }

        .create-error {
            margin-bottom: 12px;
            border: .5px solid rgba(220,38,38,.28);
            border-radius: 18px;
            background: rgba(254,226,226,.72);
            padding: 12px 14px;
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
            bottom: 22px;
            z-index: 150;
            width: min(430px, calc(100vw - 28px));
            transform: translateX(-50%);
            border: .5px solid var(--create-border);
            border-radius: 18px;
            background: var(--create-surface-strong);
            padding: 12px 14px;
            color: var(--create-text);
            font-size: 13px;
            box-shadow: var(--create-glass-shadow);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        @media (min-width: 1024px) {
            .create-settings-backdrop {
                background: transparent;
                pointer-events: none;
            }
        }

        @media (max-width: 1023px) {
            .create-shell,
            .create-page-fixed.settings-open .create-shell {
                width: 100%;
                padding: 8px 9px 26px;
            }

            .create-topbar {
                top: 7px;
                min-height: 58px;
                border-radius: 999px;
            }

            .create-settings {
                top: 8px;
                right: 8px;
                bottom: 8px;
                width: min(388px, calc(100vw - 16px));
            }

            .create-settings-backdrop {
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
            }
        }

        @media (max-width: 640px) {
            .create-topbar {
                gap: 7px;
                padding: 7px;
            }

            .create-brand-subtitle {
                display: none;
            }

            .create-icon-button {
                width: 40px;
                height: 40px;
                flex-basis: 40px;
            }

            .create-pill-button--primary {
                min-height: 40px;
                padding-inline: 13px;
            }

            .create-pill-button--primary iconify-icon {
                display: none;
            }

            .create-main {
                margin-top: 10px;
            }

            .create-editor-card {
                border-radius: 23px;
            }

            .create-editor-head {
                padding: 20px 17px 16px;
            }

            .create-title {
                font-size: 24px;
                line-height: 1.25;
            }

            .create-editor-label {
                padding: 12px 17px 0;
            }

            .create-editor-body [x-ref="holder"] {
                min-height: 65vh;
                padding: 16px 17px 34px;
            }

            .create-editor-body textarea[data-editor-content] {
                min-height: 65vh;
                padding: 18px 17px 34px;
                font-size: 16px;
            }

            [data-editorjs-wrapper] .ce-paragraph {
                font-size: 16px !important;
            }

            .create-preview-modal {
                padding: 8px;
            }

            .create-preview-card {
                border-radius: 23px;
            }

            .create-preview-body {
                padding: 16px 17px 24px;
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

    <div class="create-page-fixed" data-create-page>
        <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published', 0) ? 1 : 0 }}">
            <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">

            <div class="create-shell">
                <header class="create-topbar create-glass-strong">
                    <div class="create-topbar-left">
                        <a href="{{ route('blog.index') }}" class="create-icon-button" aria-label="{{ __('post_create.back') }}" title="{{ __('post_create.back') }}">
                            <iconify-icon icon="lucide:chevron-left" class="text-[21px]"></iconify-icon>
                        </a>

                        <div class="create-brand">
                            <div class="create-brand-title">Yeni gönderi</div>
                            <div class="create-brand-subtitle">Ografi yazı editörü</div>
                        </div>
                    </div>

                    <div class="create-topbar-actions">
                        <div class="create-info-wrap">
                            <button type="button" class="create-icon-button" data-info-toggle aria-label="Yazı bilgisi" aria-expanded="false">
                                <iconify-icon icon="lucide:info" class="text-[19px]"></iconify-icon>
                            </button>

                            <div class="create-info-popover create-glass-strong" data-info-popover>
                                <div class="create-info-head">Yazı bilgisi</div>
                                <div class="create-info-row">
                                    <iconify-icon icon="lucide:clock-3" class="text-[16px]"></iconify-icon>
                                    <span data-reading-time>1 dk okuma</span>
                                </div>
                                <div class="create-info-row">
                                    <iconify-icon icon="lucide:type" class="text-[16px]"></iconify-icon>
                                    <span data-word-count>0 kelime</span>
                                </div>
                                <div class="create-info-row">
                                    <iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>
                                    <span>Taslak destekli</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="create-icon-button" data-open-settings aria-label="Gelişmiş seçenekler" aria-expanded="false">
                            <iconify-icon icon="lucide:settings" class="text-[19px]"></iconify-icon>
                        </button>

                        <button type="submit" class="create-pill-button create-pill-button--primary" data-submit-intent="publish">
                            <iconify-icon icon="lucide:send" class="text-[17px]"></iconify-icon>
                            <span>Yayınla</span>
                        </button>
                    </div>
                </header>

                <main class="create-main">
                    @if ($errors->any())
                        <div class="create-error">
                            <div class="font-medium">Gönderi kaydedilemedi.</div>
                            <div class="mt-1">{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <section class="create-editor-card create-glass-strong">
                        <div class="create-editor-head">
                            <label for="title" class="create-field-label">Başlık</label>
                            <textarea
                                id="title"
                                name="title"
                                rows="1"
                                required
                                class="create-title"
                                placeholder="Başlığını yaz..."
                                data-autogrow
                            >{{ old('title') }}</textarea>
                        </div>

                        <div class="create-editor-label">
                            <span>İçerik</span>
                            <span>EditorJS</span>
                        </div>

                        <div class="create-editor-body" data-editorjs-wrapper>
                            <div x-ref="holder"></div>
                            <textarea
                                id="content"
                                name="content"
                                data-editor-content
                                data-mentionable="users"
                                class="hidden"
                                placeholder="Gönderini yazmaya başla..."
                            >{{ old('content') }}</textarea>
                        </div>
                    </section>
                </main>
            </div>
        </form>

        <div class="create-settings-backdrop" data-settings-backdrop></div>

        <aside class="create-settings create-glass-strong" data-settings-panel aria-label="Gelişmiş seçenekler" aria-hidden="true">
            <div class="create-settings-head">
                <div>
                    <div class="create-settings-title">Gelişmiş seçenekler</div>
                    <div class="create-settings-subtitle">Kapak, yayın, SEO ve görünürlük</div>
                </div>
                <button type="button" class="create-icon-button !h-10 !w-10 !basis-10" data-close-settings aria-label="Kapat">
                    <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                </button>
            </div>

            <div class="create-settings-scroll">
                <section class="create-settings-group">
                    <div class="create-settings-group-title">
                        <iconify-icon icon="lucide:image" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                        <span>Kapak görseli</span>
                    </div>
                    <div class="create-settings-group-body">
                        <div class="create-cover" data-cover-field>
                            <label for="featured_image" class="create-cover-drop">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[var(--create-border)] bg-[var(--create-surface-soft)]">
                                    <iconify-icon icon="lucide:image-plus" class="text-[19px]"></iconify-icon>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-[13px] font-medium text-[var(--create-text)]">Kapak görseli seç</span>
                                    <span class="mt-1 block text-[11px] text-[var(--create-muted)]">JPG, PNG veya WebP · en fazla 5 MB</span>
                                </span>
                            </label>

                            <div class="create-cover-preview" data-cover-preview>
                                <img data-cover-preview-img alt="">
                                <div class="create-cover-preview-actions">
                                    <button type="button" class="create-pill-button !min-h-9 !px-3 !text-xs" data-cover-change>
                                        <iconify-icon icon="lucide:pencil" class="text-[14px]"></iconify-icon>
                                        Değiştir
                                    </button>
                                    <button type="button" class="create-pill-button !min-h-9 !px-3 !text-xs" data-cover-remove>
                                        <iconify-icon icon="lucide:x" class="text-[14px]"></iconify-icon>
                                        Kaldır
                                    </button>
                                </div>
                            </div>

                            <input id="featured_image" name="featured_image" type="file" accept="image/*" class="sr-only" data-cover-input form="post-create-form">
                        </div>
                    </div>
                </section>

                <section class="create-settings-group">
                    <div class="create-settings-group-title">
                        <iconify-icon icon="lucide:wand-sparkles" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                        <span>Araçlar</span>
                    </div>
                    <div class="create-settings-group-body">
                        <div class="create-tools">
                            <button type="button" class="create-pill-button" data-open-preview>
                                <iconify-icon icon="lucide:eye" class="text-[17px]"></iconify-icon>
                                Ön izleme
                            </button>
                            <button type="button" class="create-pill-button" data-ai-assist>
                                <iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[17px]"></iconify-icon>
                                AI yardım
                            </button>
                        </div>
                    </div>
                </section>

                <section class="create-settings-group">
                    <div class="create-settings-group-title">
                        <iconify-icon icon="lucide:layout-list" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                        <span>Gönderi bilgileri</span>
                    </div>
                    <div class="create-settings-group-body create-stack">
                        <div>
                            <label for="category_id" class="create-setting-label">Topluluk / kategori</label>
                            <select id="category_id" name="category_id" class="create-field" form="post-create-form">
                                <option value="">Kategori seç</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected($initialCategoryId === (int) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="excerpt" class="create-setting-label">Kısa açıklama</label>
                            <textarea id="excerpt" name="excerpt" class="create-field" rows="3" placeholder="Gönderinin kısa özeti..." form="post-create-form">{{ old('excerpt') }}</textarea>
                        </div>

                        <div>
                            <label for="new_tags" class="create-setting-label">Yeni etiketler</label>
                            <input id="new_tags" name="new_tags" type="text" class="create-field" value="{{ old('new_tags') }}" placeholder="laravel, tasarım, teknoloji" form="post-create-form">
                        </div>

                        @if(isset($tags) && collect($tags)->isNotEmpty())
                            <div>
                                <div class="create-setting-label">Mevcut etiketler</div>
                                <div class="flex max-h-36 flex-wrap gap-1.5 overflow-y-auto pr-1">
                                    @foreach($tags as $tag)
                                        <label class="create-tag-chip">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id)) form="post-create-form">
                                            <span>#{{ $tag->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="create-settings-group">
                    <div class="create-settings-group-title">
                        <iconify-icon icon="lucide:calendar-clock" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                        <span>Yayınlama</span>
                    </div>
                    <div class="create-settings-group-body create-stack">
                        <div>
                            <label for="published_at" class="create-setting-label">Yayın tarihi</label>
                            <input id="published_at" name="published_at" type="datetime-local" class="create-field" value="{{ old('published_at') }}" form="post-create-form">
                        </div>

                        <div class="create-toggle-list">
                            <div class="create-toggle-row">
                                <div>
                                    <div class="create-toggle-title">Yorumları kapat</div>
                                    <div class="create-toggle-note">Bu gönderiye yeni yorum alınmaz.</div>
                                </div>
                                <x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" form="post-create-form" />
                            </div>

                            <div class="create-toggle-row">
                                <div>
                                    <div class="create-toggle-title">Hassas içerik</div>
                                    <div class="create-toggle-note">İçerik uyarısıyla gösterilir.</div>
                                </div>
                                <x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" form="post-create-form" />
                            </div>

                            <div class="create-toggle-row">
                                <div>
                                    <div class="create-toggle-title">Gönderiyi sabitle</div>
                                    <div class="create-toggle-note">Uygun alanlarda üstte gösterilir.</div>
                                </div>
                                <x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" form="post-create-form" />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="create-settings-group">
                    <div class="create-settings-group-title">
                        <iconify-icon icon="lucide:search" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                        <span>SEO</span>
                    </div>
                    <div class="create-settings-group-body create-stack">
                        <div>
                            <label for="meta_title" class="create-setting-label">SEO başlığı</label>
                            <input id="meta_title" name="meta_title" type="text" class="create-field" value="{{ old('meta_title') }}" placeholder="Arama sonucunda görünecek başlık" form="post-create-form">
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <label for="meta_description" class="create-setting-label !mb-0">Meta açıklama</label>
                                <span class="text-[10px] text-[var(--create-subtle)]" data-meta-description-count>0/160</span>
                            </div>
                            <textarea id="meta_description" name="meta_description" maxlength="160" class="create-field" rows="3" placeholder="Arama sonucunda görünecek açıklama" form="post-create-form">{{ old('meta_description') }}</textarea>
                        </div>

                        <div>
                            <label for="slug" class="create-setting-label">Özel bağlantı</label>
                            <input id="slug" name="slug" type="text" class="create-field" value="{{ old('slug') }}" placeholder="ornek-gonderi" form="post-create-form">
                        </div>

                        <div>
                            <label for="meta_keywords" class="create-setting-label">Anahtar kelimeler</label>
                            <input id="meta_keywords" name="meta_keywords" type="text" class="create-field" value="{{ old('meta_keywords') }}" placeholder="teknoloji, yazılım, gündem" form="post-create-form">
                        </div>
                    </div>
                </section>

                <section class="create-settings-group">
                    <div class="create-settings-group-title">
                        <iconify-icon icon="lucide:copyright" class="text-[17px] text-[var(--create-muted)]"></iconify-icon>
                        <span>Görsel hakları</span>
                    </div>
                    <div class="create-settings-group-body create-stack">
                        <input id="image_creator_name" name="image_creator_name" type="text" class="create-field" value="{{ old('image_creator_name') }}" placeholder="Görsel üreticisi / fotoğrafçı" form="post-create-form">
                        <input id="image_credit_text" name="image_credit_text" type="text" class="create-field" value="{{ old('image_credit_text') }}" placeholder="Görsel kredisi" form="post-create-form">
                        <input id="image_copyright_notice" name="image_copyright_notice" type="text" class="create-field" value="{{ old('image_copyright_notice') }}" placeholder="Telif bildirimi" form="post-create-form">
                        <input id="image_license_url" name="image_license_url" type="url" class="create-field" value="{{ old('image_license_url') }}" placeholder="Lisans bağlantısı" form="post-create-form">
                        <input id="image_acquire_url" name="image_acquire_url" type="url" class="create-field" value="{{ old('image_acquire_url') }}" placeholder="Kaynak / satın alma bağlantısı" form="post-create-form">
                    </div>
                </section>
            </div>

            <div class="create-settings-footer">
                <button type="submit" class="create-pill-button" data-submit-intent="draft" form="post-create-form">
                    <iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>
                    Taslağa kaydet
                </button>
            </div>
        </aside>

        <div class="create-preview-modal" data-preview-modal aria-hidden="true">
            <div class="create-preview-card create-glass-strong">
                <div class="create-settings-head">
                    <div>
                        <div class="create-settings-title">Gönderi ön izlemesi</div>
                        <div class="create-settings-subtitle">Yayınlanmadan önce son görünüm</div>
                    </div>
                    <button type="button" class="create-icon-button !h-10 !w-10 !basis-10" data-close-preview aria-label="Kapat">
                        <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                    </button>
                </div>
                <div class="create-preview-body">
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
            const page = document.querySelector('[data-create-page]');
            const form = document.getElementById('post-create-form');
            const wrapper = document.querySelector('[data-editorjs-wrapper]');
            const holder = wrapper?.querySelector('[x-ref="holder"]');
            const fallbackTextarea = document.getElementById('content');
            const jsonField = document.getElementById('content_json');
            const isPublishedInput = document.getElementById('is_published');
            const titleField = document.getElementById('title');
            const coverInput = document.querySelector('[data-cover-input]');
            const coverField = document.querySelector('[data-cover-field]');
            const coverPreviewImg = document.querySelector('[data-cover-preview-img]');
            const settingsPanel = document.querySelector('[data-settings-panel]');
            const settingsBackdrop = document.querySelector('[data-settings-backdrop]');
            const settingsTrigger = document.querySelector('[data-open-settings]');
            const previewModal = document.querySelector('[data-preview-modal]');
            const previewContent = document.querySelector('[data-preview-content]');
            const infoToggle = document.querySelector('[data-info-toggle]');
            const infoPopover = document.querySelector('[data-info-popover]');
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
                if (!settingsPanel) return;
                settingsPanel.classList.add('is-open');
                settingsPanel.setAttribute('aria-hidden', 'false');
                settingsTrigger?.setAttribute('aria-expanded', 'true');
                page?.classList.add('settings-open');
                settingsBackdrop?.classList.add('is-open');
                if (!isDesktop()) document.documentElement.classList.add('overflow-hidden');
            };

            const closeSettings = () => {
                if (!settingsPanel) return;
                settingsPanel.classList.remove('is-open');
                settingsPanel.setAttribute('aria-hidden', 'true');
                settingsTrigger?.setAttribute('aria-expanded', 'false');
                page?.classList.remove('settings-open');
                settingsBackdrop?.classList.remove('is-open');
                document.documentElement.classList.remove('overflow-hidden');
            };

            settingsTrigger?.addEventListener('click', () => {
                if (settingsPanel?.classList.contains('is-open')) closeSettings();
                else openSettings();
            });
            document.querySelector('[data-close-settings]')?.addEventListener('click', closeSettings);
            settingsBackdrop?.addEventListener('click', () => {
                if (!isDesktop()) closeSettings();
            });

            const closeInfo = () => {
                infoPopover?.classList.remove('is-open');
                infoToggle?.setAttribute('aria-expanded', 'false');
            };

            infoToggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                const willOpen = !infoPopover?.classList.contains('is-open');
                infoPopover?.classList.toggle('is-open', willOpen);
                infoToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            document.addEventListener('click', (event) => {
                if (!infoPopover?.classList.contains('is-open')) return;
                if (event.target instanceof Element && (infoPopover.contains(event.target) || infoToggle?.contains(event.target))) return;
                closeInfo();
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
                statsTimer = window.setTimeout(updateStats, 350);
            };

            wrapper?.addEventListener('input', scheduleStats);
            wrapper?.addEventListener('keyup', scheduleStats);
            titleField?.addEventListener('input', scheduleStats);

            const showFallback = () => {
                holder?.classList.add('hidden');
                fallbackTextarea?.classList.remove('hidden');
            };

            const initEditor = async (attempt = 0) => {
                if (!wrapper) return;

                if (!window.initFilamentEditorJsField) {
                    if (attempt < 2) {
                        window.setTimeout(() => initEditor(attempt + 1), 350);
                        return;
                    }
                    showFallback();
                    return;
                }

                try {
                    await window.initFilamentEditorJsField(wrapper);
                    if (!wrapper.__editorInstance) showFallback();
                } catch (error) {
                    console.error('EditorJS init error', error);
                    showFallback();
                }
            };

            initEditor();
            window.setTimeout(updateStats, 1100);

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
                    ? `<img src="${escapeHtml(coverPreviewImg.src)}" alt="" class="mb-5 w-full rounded-[20px] object-cover">`
                    : '';

                return `
                    ${image}
                    <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.018em] text-[var(--create-text)]">${escapeHtml(title || 'Başlıksız gönderi')}</h1>
                    ${excerpt ? `<p class="mt-3 text-[15px] leading-6 text-[var(--create-muted)]">${escapeHtml(excerpt)}</p>` : ''}
                    <div class="mt-7 text-[16px] leading-7 text-[var(--create-text)]">${contentHtml || '<p>Henüz içerik yok.</p>'}</div>
                `;
            };

            const openPreview = async () => {
                if (!previewModal || !previewContent) return;
                previewContent.innerHTML = await buildPreview();
                previewModal.classList.add('is-open');
                previewModal.setAttribute('aria-hidden', 'false');
                document.documentElement.classList.add('overflow-hidden');
            };

            const closePreview = () => {
                previewModal?.classList.remove('is-open');
                previewModal?.setAttribute('aria-hidden', 'true');
                if (!settingsPanel?.classList.contains('is-open') || isDesktop()) {
                    document.documentElement.classList.remove('overflow-hidden');
                }
            };

            document.querySelector('[data-open-preview]')?.addEventListener('click', openPreview);
            document.querySelector('[data-close-preview]')?.addEventListener('click', closePreview);
            previewModal?.addEventListener('click', (event) => {
                if (event.target === previewModal) closePreview();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                closeInfo();
                closePreview();
                closeSettings();
            });

            const aiAssistButton = document.querySelector('[data-ai-assist]');
            const aiAssistIcon = aiAssistButton?.querySelector('[data-ai-assist-icon]');
            let aiBusy = false;

            aiAssistButton?.addEventListener('click', async () => {
                if (aiBusy) return;

                const title = String(titleField?.value || '').trim();
                const content = await readEditorPlainText();

                if (!title && !content) {
                    showToast('Önce başlık veya içerik yazın.', true);
                    return;
                }

                aiBusy = true;
                aiAssistButton.disabled = true;
                aiAssistIcon?.setAttribute('icon', 'lucide:loader-circle');

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
                    openSettings();
                } catch (error) {
                    showToast(error?.message || 'Yapay zeka isteği başarısız.', true);
                } finally {
                    aiBusy = false;
                    aiAssistButton.disabled = false;
                    aiAssistIcon?.setAttribute('icon', 'lucide:sparkles');
                }
            });
        });
    </script>
@endpush
