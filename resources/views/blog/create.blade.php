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

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('content')
    @php
        $initialCategoryId = (int) old('category_id');
    @endphp

    <style>
        :root {
            --composer-bg: #f3f4f6;
            --composer-text: #14171c;
            --composer-muted: #667085;
            --composer-subtle: #98a2b3;
            --composer-blue: #0a84ff;
            --composer-blue-hover: #0077ed;
            --composer-border: rgba(17, 24, 39, .14);
            --composer-border-strong: rgba(17, 24, 39, .20);
            --composer-glass: rgba(255, 255, 255, .70);
            --composer-glass-strong: rgba(255, 255, 255, .88);
            --composer-glass-soft: rgba(255, 255, 255, .48);
            --composer-field: rgba(255, 255, 255, .72);
            --composer-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .82),
                inset 0 -1px 0 rgba(15, 23, 42, .035),
                inset 4px 0 14px rgba(255, 255, 255, .10),
                inset -4px 0 14px rgba(15, 23, 42, .035),
                0 2px 12px rgba(15, 23, 42, .065);
        }

        html.dark,
        html[data-system-theme="dark"],
        html[data-theme="dark"],
        body.dark {
            --composer-bg: #090d14;
            --composer-text: #f7f8fa;
            --composer-muted: #a5afbe;
            --composer-subtle: #748196;
            --composer-border: rgba(255, 255, 255, .12);
            --composer-border-strong: rgba(255, 255, 255, .18);
            --composer-glass: rgba(17, 24, 39, .70);
            --composer-glass-strong: rgba(15, 23, 42, .90);
            --composer-glass-soft: rgba(30, 41, 59, .54);
            --composer-field: rgba(15, 23, 42, .72);
            --composer-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .08),
                inset 0 -1px 0 rgba(0, 0, 0, .20),
                inset 4px 0 14px rgba(255, 255, 255, .025),
                inset -4px 0 14px rgba(0, 0, 0, .10),
                0 2px 14px rgba(0, 0, 0, .20);
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

        .post-composer,
        .post-composer *:not(iconify-icon) {
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
        }

        .post-composer {
            position: fixed;
            inset: 0;
            z-index: 99999;
            overflow-y: auto;
            color: var(--composer-text);
            background: var(--composer-bg);
        }

        .composer-shell {
            width: min(1180px, calc(100% - 24px));
            min-height: 100vh;
            margin: 0 auto;
            padding: 12px 0 34px;
        }

        .composer-glass,
        .composer-glass-strong {
            border: .5px solid var(--composer-border);
            box-shadow: var(--composer-shadow);
            -webkit-backdrop-filter: blur(24px) saturate(155%);
            backdrop-filter: blur(24px) saturate(155%);
        }

        .composer-glass {
            background: var(--composer-glass);
        }

        .composer-glass-strong {
            border-color: var(--composer-border-strong);
            background: var(--composer-glass-strong);
            -webkit-backdrop-filter: blur(30px) saturate(165%);
            backdrop-filter: blur(30px) saturate(165%);
        }

        .composer-topbar {
            position: sticky;
            top: 10px;
            z-index: 90;
            display: flex;
            min-height: 60px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px;
            border-radius: 999px;
        }

        .composer-topbar-left,
        .composer-topbar-actions {
            display: flex;
            align-items: center;
        }

        .composer-topbar-left {
            min-width: 0;
            gap: 9px;
        }

        .composer-topbar-actions {
            flex: 0 0 auto;
            gap: 6px;
        }

        .composer-icon-button,
        .composer-pill-button {
            border: .5px solid var(--composer-border) !important;
            background: var(--composer-glass-soft) !important;
            color: var(--composer-text) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .32) !important;
            text-decoration: none !important;
            outline: none !important;
            transition: transform 120ms ease-out, background-color 140ms ease-out, border-color 140ms ease-out;
        }

        .composer-icon-button {
            display: inline-flex !important;
            width: 42px !important;
            height: 42px !important;
            flex: 0 0 42px !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 999px !important;
            padding: 0 !important;
        }

        .composer-pill-button {
            display: inline-flex !important;
            min-height: 42px !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            border-radius: 999px !important;
            padding: 0 15px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        .composer-icon-button:hover,
        .composer-icon-button:focus-visible,
        .composer-pill-button:hover,
        .composer-pill-button:focus-visible {
            background: var(--composer-glass-strong) !important;
        }

        .composer-icon-button:active,
        .composer-pill-button:active {
            transform: scale(.97);
        }

        .composer-pill-button--primary,
        .composer-pill-button--primary:hover,
        .composer-pill-button--primary:focus-visible {
            border-color: rgba(10, 132, 255, .86) !important;
            background: var(--composer-blue) !important;
            color: #fff !important;
        }

        .composer-pill-button--primary:hover {
            background: var(--composer-blue-hover) !important;
        }

        .composer-brand {
            min-width: 0;
        }

        .composer-brand-title {
            overflow: hidden;
            color: var(--composer-text);
            font-size: 15px;
            font-weight: 600;
            line-height: 19px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .composer-brand-subtitle {
            overflow: hidden;
            margin-top: 1px;
            color: var(--composer-muted);
            font-size: 12px;
            line-height: 16px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .composer-info-wrap {
            position: relative;
        }

        .composer-info-popover {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            z-index: 130;
            width: 254px;
            overflow: hidden;
            border-radius: 22px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-4px) scale(.98);
            transform-origin: top right;
            transition: opacity 160ms cubic-bezier(.23, 1, .32, 1), transform 160ms cubic-bezier(.23, 1, .32, 1), visibility 160ms;
        }

        .composer-info-popover.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .composer-info-head {
            padding: 13px 14px 10px;
            border-bottom: .5px solid var(--composer-border);
            font-size: 13px;
            font-weight: 600;
        }

        .composer-info-row {
            display: flex;
            min-height: 42px;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 0 14px;
            color: var(--composer-muted);
            font-size: 13px;
        }

        .composer-info-row + .composer-info-row {
            border-top: .5px solid var(--composer-border);
        }

        .composer-info-row strong {
            color: var(--composer-text);
            font-size: 13px;
            font-weight: 500 !important;
            white-space: nowrap;
        }

        .composer-error {
            margin-top: 14px;
            border: .5px solid rgba(220, 38, 38, .28);
            border-radius: 18px;
            background: rgba(254, 226, 226, .72);
            padding: 12px 14px;
            color: #991b1b;
            font-size: 13px;
        }

        html.dark .composer-error,
        html[data-system-theme="dark"] .composer-error,
        html[data-theme="dark"] .composer-error {
            background: rgba(127, 29, 29, .24);
            color: #fecaca;
        }

        .composer-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 310px;
            gap: 14px;
            margin-top: 14px;
            align-items: start;
        }

        .composer-editor-card,
        .composer-cover-card {
            border-radius: 28px;
        }

        .composer-editor-card {
            min-width: 0;
            overflow: visible;
        }

        .composer-cover-card {
            position: sticky;
            top: 84px;
            overflow: hidden;
        }

        .composer-title-zone {
            padding: 25px 28px 18px;
        }

        .composer-field-label {
            display: block;
            margin-bottom: 8px;
            color: var(--composer-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .post-composer .composer-title-input {
            display: block !important;
            width: 100% !important;
            min-height: 42px !important;
            margin: 0 !important;
            overflow: hidden !important;
            resize: none !important;
            border: 0 !important;
            border-radius: 0 !important;
            outline: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            color: var(--composer-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-size: clamp(26px, 2.45vw, 32px) !important;
            font-weight: 600 !important;
            line-height: 1.24 !important;
            letter-spacing: -.018em !important;
        }

        .post-composer .composer-title-input::placeholder {
            color: var(--composer-subtle) !important;
        }

        .composer-section-head {
            display: flex;
            min-height: 46px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 18px;
            border-top: .5px solid var(--composer-border);
            border-bottom: .5px solid var(--composer-border);
        }

        .composer-cover-card .composer-section-head {
            border-top: 0;
        }

        .composer-section-title {
            color: var(--composer-text);
            font-size: 13px;
            font-weight: 600;
        }

        .composer-section-note {
            color: var(--composer-muted);
            font-size: 11px;
            font-weight: 500;
        }

        .composer-editor-stage {
            position: relative;
            min-height: 66vh;
            padding-bottom: 18px;
        }

        .post-composer [data-editorjs-wrapper] [x-ref="holder"] {
            display: block;
            min-height: 62vh;
            padding: 22px 28px 42px;
            color: var(--composer-text);
        }

        .post-composer textarea[data-editor-content] {
            display: block;
            width: 100% !important;
            min-height: 62vh !important;
            resize: none !important;
            border: 0 !important;
            border-radius: 0 !important;
            outline: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 22px 28px 42px !important;
            color: var(--composer-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.7 !important;
        }

        .post-composer textarea[data-editor-content].hidden {
            display: none !important;
        }

        .post-composer [data-editorjs-wrapper] .codex-editor,
        .post-composer [data-editorjs-wrapper] .codex-editor *:not(iconify-icon) {
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
        }

        .post-composer [data-editorjs-wrapper] .codex-editor__redactor {
            padding-bottom: 130px !important;
        }

        .post-composer [data-editorjs-wrapper] .ce-block__content,
        .post-composer [data-editorjs-wrapper] .ce-toolbar__content {
            max-width: 720px !important;
        }

        .post-composer [data-editorjs-wrapper] .ce-paragraph {
            color: var(--composer-text) !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.7 !important;
        }

        .post-composer [data-editorjs-wrapper] .ce-header {
            color: var(--composer-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-weight: 600 !important;
            line-height: 1.28 !important;
        }

        .post-composer [data-editorjs-wrapper] .ce-toolbar__plus,
        .post-composer [data-editorjs-wrapper] .ce-toolbar__settings-btn {
            width: 34px !important;
            height: 34px !important;
            border: .5px solid var(--composer-border) !important;
            border-radius: 999px !important;
            background: var(--composer-glass-soft) !important;
            color: var(--composer-muted) !important;
            box-shadow: none !important;
        }

        .post-composer [data-editorjs-wrapper] .ce-popover,
        .post-composer [data-editorjs-wrapper] .ce-inline-toolbar,
        .post-composer [data-editorjs-wrapper] .ce-conversion-toolbar {
            border: .5px solid var(--composer-border) !important;
            border-radius: 18px !important;
            background: var(--composer-glass-strong) !important;
            color: var(--composer-text) !important;
            box-shadow: var(--composer-shadow) !important;
            -webkit-backdrop-filter: blur(24px) saturate(155%) !important;
            backdrop-filter: blur(24px) saturate(155%) !important;
        }

        .post-composer [data-editorjs-wrapper] .ce-popover-item__title,
        .post-composer [data-editorjs-wrapper] .ce-popover-item__secondary-title,
        .post-composer [data-editorjs-wrapper] .ce-inline-tool,
        .post-composer [data-editorjs-wrapper] .ce-conversion-tool__label {
            color: var(--composer-text) !important;
        }

        html.dark .post-composer [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50),
        html[data-system-theme="dark"] .post-composer [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50),
        html[data-theme="dark"] .post-composer [data-editorjs-wrapper] :is(.bg-white, .bg-slate-50, .bg-gray-50) {
            background-color: rgba(15, 23, 42, .84) !important;
        }

        html.dark .post-composer [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700),
        html[data-system-theme="dark"] .post-composer [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700),
        html[data-theme="dark"] .post-composer [data-editorjs-wrapper] :is(.text-slate-900, .text-slate-800, .text-slate-700) {
            color: #e8edf5 !important;
        }

        html.dark .post-composer [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300),
        html[data-system-theme="dark"] .post-composer [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300),
        html[data-theme="dark"] .post-composer [data-editorjs-wrapper] :is(.border-slate-200, .border-slate-300) {
            border-color: rgba(255, 255, 255, .12) !important;
        }

        .composer-cover-body {
            padding: 13px;
        }

        .composer-cover {
            position: relative;
            overflow: hidden;
            width: 100%;
            min-height: 190px;
            border: .5px solid var(--composer-border);
            border-radius: 20px;
            background: var(--composer-glass-soft);
        }

        .composer-cover-drop {
            display: flex;
            min-height: 190px;
            cursor: pointer;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            color: var(--composer-muted);
            text-align: center;
        }

        .composer-cover-drop:hover {
            background: rgba(10, 132, 255, .055);
            color: var(--composer-blue);
        }

        .composer-cover-symbol {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--composer-border);
            border-radius: 999px;
            background: var(--composer-glass-soft);
        }

        .composer-cover-preview {
            display: none;
            position: relative;
            min-height: 190px;
        }

        .composer-cover-preview img {
            display: block;
            width: 100%;
            min-height: 190px;
            max-height: 360px;
            object-fit: cover;
        }

        .composer-cover.has-image .composer-cover-drop {
            display: none;
        }

        .composer-cover.has-image .composer-cover-preview {
            display: block;
        }

        .composer-cover-actions {
            position: absolute;
            top: 9px;
            right: 9px;
            display: flex;
            gap: 6px;
        }

        .composer-settings-backdrop {
            position: fixed;
            inset: 0;
            z-index: 108;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(2, 6, 23, .34);
            transition: opacity 180ms cubic-bezier(.23, 1, .32, 1), visibility 180ms;
        }

        .composer-settings-backdrop.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .composer-settings-panel {
            position: fixed;
            top: 12px;
            right: 12px;
            bottom: 12px;
            z-index: 110;
            display: flex;
            width: min(392px, calc(100vw - 24px));
            flex-direction: column;
            overflow: hidden;
            border-radius: 30px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateX(calc(100% + 24px));
            transform-origin: top right;
            transition: transform 220ms cubic-bezier(.32, .72, 0, 1), opacity 160ms ease-out, visibility 220ms;
        }

        .composer-settings-panel.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateX(0);
        }

        .composer-settings-head {
            display: flex;
            min-height: 68px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 13px 11px 18px;
            border-bottom: .5px solid var(--composer-border);
        }

        .composer-settings-title {
            font-size: 15px;
            font-weight: 600;
        }

        .composer-settings-subtitle {
            margin-top: 2px;
            color: var(--composer-muted);
            font-size: 12px;
            line-height: 16px;
        }

        .composer-settings-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 12px;
        }

        .composer-settings-group {
            overflow: hidden;
            border: .5px solid var(--composer-border);
            border-radius: 20px;
            background: rgba(255, 255, 255, .18);
        }

        html.dark .composer-settings-group,
        html[data-system-theme="dark"] .composer-settings-group,
        html[data-theme="dark"] .composer-settings-group {
            background: rgba(15, 23, 42, .26);
        }

        .composer-settings-group + .composer-settings-group {
            margin-top: 10px;
        }

        .composer-settings-group-title {
            display: flex;
            min-height: 44px;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
            border-bottom: .5px solid var(--composer-border);
            color: var(--composer-text);
            font-size: 13px;
            font-weight: 600;
        }

        .composer-settings-group-body {
            padding: 12px;
        }

        .composer-field {
            display: block !important;
            width: 100% !important;
            min-height: 44px !important;
            border: .5px solid var(--composer-border) !important;
            border-radius: 15px !important;
            background: var(--composer-field) !important;
            padding: 10px 12px !important;
            color: var(--composer-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        textarea.composer-field {
            min-height: 88px !important;
            resize: vertical !important;
        }

        .composer-field:focus {
            border-color: rgba(10, 132, 255, .62) !important;
            background: var(--composer-glass-strong) !important;
        }

        .composer-field::placeholder {
            color: var(--composer-subtle) !important;
        }

        .composer-setting-label {
            display: block;
            margin-bottom: 6px;
            color: var(--composer-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .composer-stack > * + * {
            margin-top: 11px;
        }

        .composer-tools {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .composer-tools .composer-pill-button {
            width: 100% !important;
            min-height: 41px !important;
            padding-inline: 10px !important;
            font-size: 13px !important;
        }

        .composer-tag-chip {
            position: relative;
            display: inline-flex;
            cursor: pointer;
        }

        .composer-tag-chip input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .composer-tag-chip span {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--composer-border);
            border-radius: 999px;
            background: var(--composer-glass-soft);
            padding: 0 10px;
            color: var(--composer-muted);
            font-size: 12px;
        }

        .composer-tag-chip input:checked + span {
            border-color: rgba(10, 132, 255, .42);
            background: rgba(10, 132, 255, .11);
            color: var(--composer-blue);
        }

        .composer-toggle-list {
            overflow: hidden;
            border: .5px solid var(--composer-border);
            border-radius: 16px;
        }

        .composer-toggle-row {
            display: flex;
            min-height: 58px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 10px;
        }

        .composer-toggle-row + .composer-toggle-row {
            border-top: .5px solid var(--composer-border);
        }

        .composer-toggle-title {
            font-size: 13px;
            font-weight: 500;
        }

        .composer-toggle-note {
            margin-top: 2px;
            color: var(--composer-muted);
            font-size: 11px;
            line-height: 15px;
        }

        .post-composer .composer-settings-panel input[role="switch"] + span {
            border-color: rgba(15, 23, 42, .15) !important;
            background: rgba(120, 120, 128, .18) !important;
        }

        .post-composer .composer-settings-panel input[role="switch"]:checked + span {
            border-color: rgba(10, 132, 255, .70) !important;
            background: var(--composer-blue) !important;
        }

        .composer-settings-footer {
            flex: 0 0 auto;
            padding: 11px 12px 12px;
            border-top: .5px solid var(--composer-border);
        }

        .composer-settings-footer .composer-pill-button {
            width: 100% !important;
        }

        .composer-preview-modal {
            position: fixed;
            inset: 0;
            z-index: 140;
            overflow-y: auto;
            padding: 18px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(2, 6, 23, .44);
            transition: opacity 180ms ease-out, visibility 180ms;
        }

        .composer-preview-modal.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .composer-preview-card {
            width: min(760px, 100%);
            margin: 4vh auto;
            overflow: hidden;
            border-radius: 28px;
        }

        .composer-preview-body {
            max-height: 78vh;
            overflow-y: auto;
            padding: 20px 24px 30px;
        }

        .composer-toast {
            position: fixed;
            left: 50%;
            bottom: 22px;
            z-index: 170;
            width: min(430px, calc(100vw - 28px));
            transform: translateX(-50%);
            border: .5px solid var(--composer-border);
            border-radius: 18px;
            background: var(--composer-glass-strong);
            padding: 12px 14px;
            color: var(--composer-text);
            font-size: 13px;
            box-shadow: var(--composer-shadow);
            -webkit-backdrop-filter: blur(24px);
            backdrop-filter: blur(24px);
        }

        @media (min-width: 1024px) {
            .composer-settings-backdrop,
            .composer-settings-backdrop.is-open {
                background: transparent;
                pointer-events: none;
            }
        }

        @media (max-width: 980px) {
            .composer-shell {
                width: min(820px, calc(100% - 18px));
                padding-top: 8px;
            }

            .composer-workspace {
                grid-template-columns: 1fr;
            }

            .composer-cover-card {
                position: static;
                order: 2;
            }

            .composer-editor-card {
                order: 1;
            }
        }

        @media (max-width: 640px) {
            .composer-shell {
                width: calc(100% - 14px);
            }

            .composer-topbar {
                top: 7px;
                min-height: 56px;
                gap: 7px;
                padding: 7px;
            }

            .composer-brand-subtitle {
                display: none;
            }

            .composer-icon-button {
                width: 40px !important;
                height: 40px !important;
                flex-basis: 40px !important;
            }

            .composer-pill-button--primary {
                min-height: 40px !important;
                padding-inline: 13px !important;
            }

            .composer-pill-button--primary iconify-icon {
                display: none;
            }

            .composer-workspace {
                margin-top: 9px;
                gap: 9px;
            }

            .composer-editor-card,
            .composer-cover-card {
                border-radius: 23px;
            }

            .composer-title-zone {
                padding: 20px 17px 15px;
            }

            .post-composer .composer-title-input {
                font-size: 25px !important;
            }

            .composer-section-head {
                min-height: 44px;
                padding-inline: 17px;
            }

            .post-composer [data-editorjs-wrapper] [x-ref="holder"] {
                min-height: 66vh;
                padding: 18px 17px 36px;
            }

            .post-composer textarea[data-editor-content] {
                min-height: 66vh !important;
                padding: 18px 17px 36px !important;
                font-size: 16px !important;
            }

            .composer-cover-body {
                padding: 12px;
            }

            .composer-settings-panel {
                top: 8px;
                right: 8px;
                bottom: 8px;
                width: calc(100vw - 16px);
                border-radius: 26px;
            }

            .composer-settings-backdrop {
                -webkit-backdrop-filter: blur(5px);
                backdrop-filter: blur(5px);
            }

            .composer-preview-modal {
                padding: 8px;
            }

            .composer-preview-card {
                border-radius: 23px;
            }

            .composer-preview-body {
                padding: 16px 17px 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .composer-icon-button,
            .composer-pill-button,
            .composer-info-popover,
            .composer-settings-panel,
            .composer-settings-backdrop,
            .composer-preview-modal {
                transition: none !important;
            }
        }

        @media (prefers-reduced-transparency: reduce) {
            .composer-glass,
            .composer-glass-strong,
            .composer-info-popover,
            .composer-settings-panel,
            .composer-preview-modal,
            .composer-toast {
                -webkit-backdrop-filter: none !important;
                backdrop-filter: none !important;
            }
        }
    </style>

    <div class="post-composer" data-create-page>
        <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published', 0) ? 1 : 0 }}">
            <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">

            <div class="composer-shell">
                <header class="composer-topbar composer-glass-strong">
                    <div class="composer-topbar-left">
                        <a href="{{ route('blog.index') }}" class="composer-icon-button" aria-label="{{ __('post_create.back') }}" title="{{ __('post_create.back') }}">
                            <iconify-icon icon="lucide:chevron-left" class="text-[21px]"></iconify-icon>
                        </a>

                        <div class="composer-brand">
                            <div class="composer-brand-title">Yeni gönderi</div>
                            <div class="composer-brand-subtitle">Ografi Editor</div>
                        </div>
                    </div>

                    <div class="composer-topbar-actions">
                        <div class="composer-info-wrap">
                            <button type="button" class="composer-icon-button" data-info-toggle aria-label="Yazı bilgisi" title="Yazı bilgisi" aria-expanded="false">
                                <iconify-icon icon="lucide:info" class="text-[19px]"></iconify-icon>
                            </button>

                            <div class="composer-info-popover composer-glass-strong" data-info-popover aria-hidden="true">
                                <div class="composer-info-head">Yazı bilgisi</div>
                                <div class="composer-info-row">
                                    <span>Okuma süresi</span>
                                    <strong data-reading-time>1 dk okuma</strong>
                                </div>
                                <div class="composer-info-row">
                                    <span>Kelime</span>
                                    <strong data-word-count>0 kelime</strong>
                                </div>
                                <div class="composer-info-row">
                                    <span>Kayıt</span>
                                    <strong>Taslak destekli</strong>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="composer-icon-button" data-open-settings aria-label="Gelişmiş seçenekler" title="Gelişmiş seçenekler" aria-expanded="false">
                            <iconify-icon icon="lucide:settings" class="text-[19px]"></iconify-icon>
                        </button>

                        <button type="submit" class="composer-pill-button composer-pill-button--primary" data-submit-intent="publish">
                            <iconify-icon icon="lucide:send" class="text-[16px]"></iconify-icon>
                            <span>Yayınla</span>
                        </button>
                    </div>
                </header>

                @if ($errors->any())
                    <div class="composer-error">
                        <div class="font-medium">Gönderi kaydedilemedi.</div>
                        <div class="mt-1">{{ $errors->first() }}</div>
                    </div>
                @endif

                <div class="composer-workspace">
                    <section class="composer-editor-card composer-glass-strong" aria-label="Gönderi düzenleyici">
                        <div class="composer-title-zone">
                            <label for="title" class="composer-field-label">Başlık</label>
                            <textarea
                                id="title"
                                name="title"
                                rows="1"
                                required
                                class="composer-title-input"
                                placeholder="Başlığını yaz..."
                                data-autogrow
                            >{{ old('title') }}</textarea>
                        </div>

                        <div class="composer-section-head">
                            <div class="composer-section-title">EditorJS</div>
                            <div class="composer-section-note" data-editor-status>Hazırlanıyor</div>
                        </div>

                        <div class="composer-editor-stage" data-editorjs-wrapper>
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

                    <aside class="composer-cover-card composer-glass-strong" aria-label="Kapak görseli">
                        <div class="composer-section-head">
                            <div class="composer-section-title">Kapak görseli</div>
                            <iconify-icon icon="lucide:image" class="text-[17px] text-[var(--composer-muted)]"></iconify-icon>
                        </div>

                        <div class="composer-cover-body">
                            <div class="composer-cover" data-cover-field>
                                <label for="featured_image" class="composer-cover-drop">
                                    <span class="composer-cover-symbol">
                                        <iconify-icon icon="lucide:image-plus" class="text-[19px]"></iconify-icon>
                                    </span>
                                    <span class="text-[13px] font-medium text-[var(--composer-text)]">Görsel seç</span>
                                    <span class="text-[11px] leading-4">JPG, PNG veya WebP · en fazla 5 MB</span>
                                </label>

                                <div class="composer-cover-preview" data-cover-preview>
                                    <img data-cover-preview-img alt="Kapak görseli ön izlemesi">
                                    <div class="composer-cover-actions">
                                        <button type="button" class="composer-icon-button !h-9 !w-9 !basis-9" data-cover-change aria-label="Görseli değiştir" title="Görseli değiştir">
                                            <iconify-icon icon="lucide:pencil" class="text-[15px]"></iconify-icon>
                                        </button>
                                        <button type="button" class="composer-icon-button !h-9 !w-9 !basis-9" data-cover-remove aria-label="Görseli kaldır" title="Görseli kaldır">
                                            <iconify-icon icon="lucide:x" class="text-[15px]"></iconify-icon>
                                        </button>
                                    </div>
                                </div>

                                <input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-cover-input>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <div class="composer-settings-backdrop" data-settings-backdrop></div>

            <aside class="composer-settings-panel composer-glass-strong" data-settings-panel aria-label="Gelişmiş seçenekler" aria-hidden="true">
                <div class="composer-settings-head">
                    <div>
                        <div class="composer-settings-title">Gelişmiş seçenekler</div>
                        <div class="composer-settings-subtitle">Yayın, SEO ve görünürlük</div>
                    </div>
                    <button type="button" class="composer-icon-button !h-10 !w-10 !basis-10" data-close-settings aria-label="Kapat" title="Kapat">
                        <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                    </button>
                </div>

                <div class="composer-settings-scroll">
                    <section class="composer-settings-group">
                        <div class="composer-settings-group-title">
                            <iconify-icon icon="lucide:wand-sparkles" class="text-[17px] text-[var(--composer-muted)]"></iconify-icon>
                            <span>Araçlar</span>
                        </div>
                        <div class="composer-settings-group-body">
                            <div class="composer-tools">
                                <button type="button" class="composer-pill-button" data-open-preview>
                                    <iconify-icon icon="lucide:eye" class="text-[16px]"></iconify-icon>
                                    Ön izleme
                                </button>
                                <button type="button" class="composer-pill-button" data-ai-assist>
                                    <iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[16px]"></iconify-icon>
                                    AI yardım
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="composer-settings-group">
                        <div class="composer-settings-group-title">
                            <iconify-icon icon="lucide:layout-list" class="text-[17px] text-[var(--composer-muted)]"></iconify-icon>
                            <span>Gönderi bilgileri</span>
                        </div>
                        <div class="composer-settings-group-body composer-stack">
                            <div>
                                <label for="category_id" class="composer-setting-label">Topluluk / kategori</label>
                                <select id="category_id" name="category_id" class="composer-field">
                                    <option value="">Kategori seç</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($initialCategoryId === (int) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="excerpt" class="composer-setting-label">Kısa açıklama</label>
                                <textarea id="excerpt" name="excerpt" class="composer-field" rows="3" placeholder="Gönderinin kısa özeti...">{{ old('excerpt') }}</textarea>
                            </div>

                            <div>
                                <label for="new_tags" class="composer-setting-label">Yeni etiketler</label>
                                <input id="new_tags" name="new_tags" type="text" class="composer-field" value="{{ old('new_tags') }}" placeholder="laravel, tasarım, teknoloji">
                            </div>

                            @if(isset($tags) && collect($tags)->isNotEmpty())
                                <div>
                                    <div class="composer-setting-label">Mevcut etiketler</div>
                                    <div class="flex max-h-36 flex-wrap gap-1.5 overflow-y-auto pr-1">
                                        @foreach($tags as $tag)
                                            <label class="composer-tag-chip">
                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id))>
                                                <span>#{{ $tag->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="composer-settings-group">
                        <div class="composer-settings-group-title">
                            <iconify-icon icon="lucide:calendar-clock" class="text-[17px] text-[var(--composer-muted)]"></iconify-icon>
                            <span>Yayınlama</span>
                        </div>
                        <div class="composer-settings-group-body composer-stack">
                            <div>
                                <label for="published_at" class="composer-setting-label">Yayın tarihi</label>
                                <input id="published_at" name="published_at" type="datetime-local" class="composer-field" value="{{ old('published_at') }}">
                            </div>

                            <div class="composer-toggle-list">
                                <div class="composer-toggle-row">
                                    <div>
                                        <div class="composer-toggle-title">Yorumları kapat</div>
                                        <div class="composer-toggle-note">Yeni yorum alınmaz.</div>
                                    </div>
                                    <x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" />
                                </div>
                                <div class="composer-toggle-row">
                                    <div>
                                        <div class="composer-toggle-title">Hassas içerik</div>
                                        <div class="composer-toggle-note">İçerik uyarısıyla gösterilir.</div>
                                    </div>
                                    <x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" />
                                </div>
                                <div class="composer-toggle-row">
                                    <div>
                                        <div class="composer-toggle-title">Gönderiyi sabitle</div>
                                        <div class="composer-toggle-note">Uygun alanlarda üstte gösterilir.</div>
                                    </div>
                                    <x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="composer-settings-group">
                        <div class="composer-settings-group-title">
                            <iconify-icon icon="lucide:search" class="text-[17px] text-[var(--composer-muted)]"></iconify-icon>
                            <span>SEO</span>
                        </div>
                        <div class="composer-settings-group-body composer-stack">
                            <div>
                                <label for="meta_title" class="composer-setting-label">SEO başlığı</label>
                                <input id="meta_title" name="meta_title" type="text" class="composer-field" value="{{ old('meta_title') }}" placeholder="Arama sonucunda görünecek başlık">
                            </div>
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <label for="meta_description" class="composer-setting-label !mb-0">Meta açıklama</label>
                                    <span class="text-[10px] text-[var(--composer-subtle)]" data-meta-description-count>0/160</span>
                                </div>
                                <textarea id="meta_description" name="meta_description" maxlength="160" class="composer-field" rows="3" placeholder="Arama sonucunda görünecek açıklama">{{ old('meta_description') }}</textarea>
                            </div>
                            <div>
                                <label for="slug" class="composer-setting-label">Özel bağlantı</label>
                                <input id="slug" name="slug" type="text" class="composer-field" value="{{ old('slug') }}" placeholder="ornek-gonderi">
                            </div>
                            <div>
                                <label for="meta_keywords" class="composer-setting-label">Anahtar kelimeler</label>
                                <input id="meta_keywords" name="meta_keywords" type="text" class="composer-field" value="{{ old('meta_keywords') }}" placeholder="teknoloji, yazılım, gündem">
                            </div>
                        </div>
                    </section>

                    <section class="composer-settings-group">
                        <div class="composer-settings-group-title">
                            <iconify-icon icon="lucide:copyright" class="text-[17px] text-[var(--composer-muted)]"></iconify-icon>
                            <span>Görsel hakları</span>
                        </div>
                        <div class="composer-settings-group-body composer-stack">
                            <input id="image_creator_name" name="image_creator_name" type="text" class="composer-field" value="{{ old('image_creator_name') }}" placeholder="Görsel üreticisi / fotoğrafçı">
                            <input id="image_credit_text" name="image_credit_text" type="text" class="composer-field" value="{{ old('image_credit_text') }}" placeholder="Görsel kredisi">
                            <input id="image_copyright_notice" name="image_copyright_notice" type="text" class="composer-field" value="{{ old('image_copyright_notice') }}" placeholder="Telif bildirimi">
                            <input id="image_license_url" name="image_license_url" type="url" class="composer-field" value="{{ old('image_license_url') }}" placeholder="Lisans bağlantısı">
                            <input id="image_acquire_url" name="image_acquire_url" type="url" class="composer-field" value="{{ old('image_acquire_url') }}" placeholder="Kaynak / satın alma bağlantısı">
                        </div>
                    </section>
                </div>

                <div class="composer-settings-footer">
                    <button type="submit" class="composer-pill-button" data-submit-intent="draft">
                        <iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>
                        Taslağa kaydet
                    </button>
                </div>
            </aside>
        </form>

        <div class="composer-preview-modal" data-preview-modal aria-hidden="true">
            <div class="composer-preview-card composer-glass-strong">
                <div class="composer-settings-head">
                    <div>
                        <div class="composer-settings-title">Gönderi ön izlemesi</div>
                        <div class="composer-settings-subtitle">Yayınlamadan önce son görünüm</div>
                    </div>
                    <button type="button" class="composer-icon-button !h-10 !w-10 !basis-10" data-close-preview aria-label="Kapat" title="Kapat">
                        <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                    </button>
                </div>
                <div class="composer-preview-body">
                    <div data-preview-content class="prose prose-slate max-w-none dark:prose-invert"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('filament.assets.editorjs')

    <script>
        (() => {
            const bootComposer = () => {
                const page = document.querySelector('[data-create-page]');
                if (!page || page.dataset.composerBooted === '1') return;
                page.dataset.composerBooted = '1';

                const form = document.getElementById('post-create-form');
                const wrapper = page.querySelector('[data-editorjs-wrapper]');
                const holder = wrapper?.querySelector('[x-ref="holder"]');
                const fallbackTextarea = document.getElementById('content');
                const jsonField = document.getElementById('content_json');
                const isPublishedInput = document.getElementById('is_published');
                const titleField = document.getElementById('title');
                const editorStatus = page.querySelector('[data-editor-status]');
                const coverInput = page.querySelector('[data-cover-input]');
                const coverField = page.querySelector('[data-cover-field]');
                const coverPreviewImg = page.querySelector('[data-cover-preview-img]');
                const settingsPanel = page.querySelector('[data-settings-panel]');
                const settingsBackdrop = page.querySelector('[data-settings-backdrop]');
                const settingsTrigger = page.querySelector('[data-open-settings]');
                const previewModal = page.querySelector('[data-preview-modal]');
                const previewContent = page.querySelector('[data-preview-content]');
                const infoToggle = page.querySelector('[data-info-toggle]');
                const infoPopover = page.querySelector('[data-info-popover]');
                const metaDescription = document.getElementById('meta_description');
                const metaDescriptionCount = page.querySelector('[data-meta-description-count]');
                const wordCountEl = page.querySelector('[data-word-count]');
                const readingTimeEl = page.querySelector('[data-reading-time]');
                const aiAssistButton = page.querySelector('[data-ai-assist]');
                const aiAssistIcon = aiAssistButton?.querySelector('[data-ai-assist-icon]');

                const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;
                const wait = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

                const showToast = (message, isError = false) => {
                    document.querySelectorAll('.composer-toast').forEach((el) => el.remove());
                    const toast = document.createElement('div');
                    toast.className = 'composer-toast';
                    toast.textContent = message;
                    if (isError) {
                        toast.style.borderColor = 'rgba(220,38,38,.34)';
                        toast.style.color = '#ef4444';
                    }
                    document.body.appendChild(toast);
                    window.setTimeout(() => toast.remove(), isError ? 5200 : 3200);
                };

                const autoGrowTitle = () => {
                    if (!titleField) return;
                    titleField.style.height = 'auto';
                    titleField.style.height = `${Math.max(42, titleField.scrollHeight)}px`;
                };
                titleField?.addEventListener('input', autoGrowTitle);
                autoGrowTitle();

                const setCoverFile = (file) => {
                    if (!file || !coverPreviewImg || !coverField || !coverInput) return;
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                        coverInput.value = '';
                        showToast('Kapak görseli JPG, PNG veya WebP olmalı.', true);
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        coverInput.value = '';
                        showToast('Kapak görseli en fazla 5 MB olabilir.', true);
                        return;
                    }
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

                page.querySelector('[data-cover-change]')?.addEventListener('click', () => coverInput?.click());
                page.querySelector('[data-cover-remove]')?.addEventListener('click', () => {
                    if (coverInput) coverInput.value = '';
                    if (coverPreviewImg) coverPreviewImg.src = '';
                    coverField?.classList.remove('has-image');
                });

                const openSettings = () => {
                    settingsPanel?.classList.add('is-open');
                    settingsPanel?.setAttribute('aria-hidden', 'false');
                    settingsTrigger?.setAttribute('aria-expanded', 'true');
                    settingsBackdrop?.classList.add('is-open');
                    if (!isDesktop()) document.documentElement.classList.add('overflow-hidden');
                };

                const closeSettings = () => {
                    settingsPanel?.classList.remove('is-open');
                    settingsPanel?.setAttribute('aria-hidden', 'true');
                    settingsTrigger?.setAttribute('aria-expanded', 'false');
                    settingsBackdrop?.classList.remove('is-open');
                    document.documentElement.classList.remove('overflow-hidden');
                };

                settingsTrigger?.addEventListener('click', () => {
                    if (settingsPanel?.classList.contains('is-open')) closeSettings();
                    else openSettings();
                });
                page.querySelector('[data-close-settings]')?.addEventListener('click', closeSettings);
                settingsBackdrop?.addEventListener('click', () => {
                    if (!isDesktop()) closeSettings();
                });

                const closeInfo = () => {
                    infoPopover?.classList.remove('is-open');
                    infoPopover?.setAttribute('aria-hidden', 'true');
                    infoToggle?.setAttribute('aria-expanded', 'false');
                };

                infoToggle?.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const willOpen = !infoPopover?.classList.contains('is-open');
                    infoPopover?.classList.toggle('is-open', willOpen);
                    infoPopover?.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
                    infoToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });

                document.addEventListener('click', (event) => {
                    if (!infoPopover?.classList.contains('is-open')) return;
                    if (event.target instanceof Element && (infoPopover.contains(event.target) || infoToggle?.contains(event.target))) return;
                    closeInfo();
                });

                const syncMetaCount = () => {
                    if (metaDescription && metaDescriptionCount) {
                        metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
                    }
                };
                metaDescription?.addEventListener('input', syncMetaCount);
                syncMetaCount();

                const stripHtml = (value) => {
                    const el = document.createElement('div');
                    el.innerHTML = String(value || '');
                    return el.textContent || '';
                };

                const blockText = (block) => {
                    const data = block?.data || {};
                    const values = [data.text, data.caption, data.question, data.answer, data.title, data.label, data.message, data.note];
                    if (Array.isArray(data.items)) {
                        data.items.forEach((item) => {
                            if (typeof item === 'string') values.push(item);
                            else if (item && typeof item === 'object') values.push(item.text, item.content, item.label);
                        });
                    }
                    return values
                        .filter((value) => typeof value === 'string')
                        .map(stripHtml)
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
                    const text = (data?.blocks || []).map(blockText).join(' ').trim();
                    return text || stripHtml(fallbackTextarea?.value || '').trim();
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
                    statsTimer = window.setTimeout(updateStats, 280);
                };

                wrapper?.addEventListener('input', scheduleStats);
                wrapper?.addEventListener('keyup', scheduleStats);
                jsonField?.addEventListener('input', scheduleStats);
                titleField?.addEventListener('input', scheduleStats);

                const showFallback = () => {
                    if (holder) holder.style.display = 'none';
                    fallbackTextarea?.classList.remove('hidden');
                    if (editorStatus) editorStatus.textContent = 'Temel düzenleyici';
                    showToast('EditorJS yüklenemedi. Yazı alanı temel düzenleyiciyle açık bırakıldı.', true);
                };

                const markEditorReady = () => {
                    if (holder) holder.style.display = '';
                    fallbackTextarea?.classList.add('hidden');
                    if (editorStatus) editorStatus.textContent = 'Hazır';
                    scheduleStats();
                };

                const waitForEditorReady = async (editor) => {
                    if (!editor?.isReady || typeof editor.isReady.then !== 'function') return true;
                    await Promise.race([
                        editor.isReady,
                        new Promise((_, reject) => window.setTimeout(() => reject(new Error('EditorJS ready timeout')), 6000)),
                    ]);
                    return true;
                };

                const initEditor = async () => {
                    if (!wrapper || !holder || !fallbackTextarea || !jsonField) {
                        showFallback();
                        return;
                    }

                    if (editorStatus) editorStatus.textContent = 'Hazırlanıyor';

                    for (let attempt = 0; attempt < 18; attempt += 1) {
                        try {
                            if (wrapper.__editorInstance) {
                                await waitForEditorReady(wrapper.__editorInstance);
                                markEditorReady();
                                return;
                            }

                            if (typeof window.initFilamentEditorJsField === 'function') {
                                await window.initFilamentEditorJsField(wrapper);
                                if (wrapper.__editorInstance) {
                                    await waitForEditorReady(wrapper.__editorInstance);
                                    markEditorReady();
                                    return;
                                }
                            }
                        } catch (error) {
                            console.error('EditorJS init attempt failed', error);
                            if (wrapper.__editorInstance?.destroy) {
                                try { await wrapper.__editorInstance.destroy(); } catch {}
                            }
                            wrapper.__editorInstance = null;
                        }

                        await wait(attempt < 5 ? 250 : 500);
                    }

                    showFallback();
                };

                initEditor();
                window.setTimeout(updateStats, 900);

                const syncEditorFields = async () => {
                    if (!wrapper?.__editorInstance?.save) return;
                    const output = await wrapper.__editorInstance.save();
                    if (jsonField) jsonField.value = JSON.stringify(output);
                    if (fallbackTextarea && window.filamentEditorBlocksToHtml) {
                        fallbackTextarea.value = window.filamentEditorBlocksToHtml(output.blocks || []);
                    }
                };

                let submitLocked = false;
                page.querySelectorAll('[data-submit-intent]').forEach((button) => {
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
                        button.disabled = true;
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
                        <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.018em] text-[var(--composer-text)]">${escapeHtml(title || 'Başlıksız gönderi')}</h1>
                        ${excerpt ? `<p class="mt-3 text-[15px] leading-6 text-[var(--composer-muted)]">${escapeHtml(excerpt)}</p>` : ''}
                        <div class="mt-7 text-[16px] leading-7 text-[var(--composer-text)]">${contentHtml || '<p>Henüz içerik yok.</p>'}</div>
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
                    if (!settingsPanel?.classList.contains('is-open') || isDesktop()) {
                        document.documentElement.classList.remove('overflow-hidden');
                    }
                };

                page.querySelector('[data-open-preview]')?.addEventListener('click', openPreview);
                page.querySelector('[data-close-preview]')?.addEventListener('click', closePreview);
                previewModal?.addEventListener('click', (event) => {
                    if (event.target === previewModal) closePreview();
                });

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
                    } catch (error) {
                        showToast(error?.message || 'Yapay zeka isteği başarısız.', true);
                    } finally {
                        aiBusy = false;
                        aiAssistButton.disabled = false;
                        aiAssistIcon?.setAttribute('icon', 'lucide:sparkles');
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeInfo();
                    closePreview();
                    closeSettings();
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootComposer, { once: true });
            } else {
                bootComposer();
            }
        })();
    </script>
@endpush
