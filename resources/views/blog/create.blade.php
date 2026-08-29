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
            --pc-bg: #f4f5f7;
            --pc-text: #111827;
            --pc-muted: #667085;
            --pc-subtle: #98a2b3;
            --pc-blue: #2563eb;
            --pc-blue-hover: #1d4ed8;
            --pc-border: rgba(15, 23, 42, .14);
            --pc-border-strong: rgba(15, 23, 42, .20);
            --pc-glass: rgba(255, 255, 255, .64);
            --pc-glass-strong: rgba(255, 255, 255, .86);
            --pc-glass-soft: rgba(255, 255, 255, .48);
            --pc-input: rgba(255, 255, 255, .74);
            --pc-shadow:
                inset 0 1px 0 rgba(255,255,255,.74),
                inset 0 -1px 0 rgba(15,23,42,.035),
                inset 4px 0 14px rgba(255,255,255,.10),
                inset -4px 0 14px rgba(15,23,42,.035),
                0 2px 10px rgba(15,23,42,.065);
        }

        html.dark,
        html[data-system-theme="dark"],
        html[data-theme="dark"],
        body.dark {
            --pc-bg: #080d17;
            --pc-text: #f8fafc;
            --pc-muted: #a7b1c2;
            --pc-subtle: #748196;
            --pc-border: rgba(255, 255, 255, .12);
            --pc-border-strong: rgba(255, 255, 255, .18);
            --pc-glass: rgba(15, 23, 42, .66);
            --pc-glass-strong: rgba(15, 23, 42, .88);
            --pc-glass-soft: rgba(30, 41, 59, .52);
            --pc-input: rgba(15, 23, 42, .70);
            --pc-shadow:
                inset 0 1px 0 rgba(255,255,255,.08),
                inset 0 -1px 0 rgba(0,0,0,.18),
                inset 4px 0 14px rgba(255,255,255,.025),
                inset -4px 0 14px rgba(0,0,0,.10),
                0 2px 12px rgba(0,0,0,.18);
        }

        html, body { overflow: hidden; }

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

        .post-create-page,
        .post-create-page *:not(iconify-icon) {
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
        }

        .post-create-page {
            position: fixed;
            inset: 0;
            z-index: 99999;
            overflow-y: auto;
            color: var(--pc-text);
            background: var(--pc-bg);
        }

        .pc-shell {
            width: min(1180px, calc(100% - 24px));
            min-height: 100vh;
            margin: 0 auto;
            padding: 12px 0 32px;
        }

        .pc-glass,
        .pc-glass-strong {
            border: .5px solid var(--pc-border);
            box-shadow: var(--pc-shadow);
            backdrop-filter: blur(24px) saturate(155%);
            -webkit-backdrop-filter: blur(24px) saturate(155%);
        }

        .pc-glass { background: var(--pc-glass); }
        .pc-glass-strong {
            border-color: var(--pc-border-strong);
            background: var(--pc-glass-strong);
            backdrop-filter: blur(30px) saturate(165%);
            -webkit-backdrop-filter: blur(30px) saturate(165%);
        }

        .pc-topbar {
            position: sticky;
            top: 10px;
            z-index: 80;
            display: flex;
            min-height: 60px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px;
            border-radius: 999px;
        }

        .pc-topbar-left,
        .pc-topbar-actions {
            display: flex;
            align-items: center;
        }

        .pc-topbar-left { min-width: 0; gap: 9px; }
        .pc-topbar-actions { flex: 0 0 auto; gap: 6px; }

        .pc-icon-btn,
        .pc-pill-btn {
            border: .5px solid var(--pc-border) !important;
            background: var(--pc-glass-soft) !important;
            color: var(--pc-text) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.28) !important;
            text-decoration: none !important;
            outline: none !important;
        }

        .pc-icon-btn {
            display: inline-flex !important;
            width: 42px !important;
            height: 42px !important;
            flex: 0 0 42px !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 999px !important;
            padding: 0 !important;
        }

        .pc-pill-btn {
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

        .pc-icon-btn:hover,
        .pc-icon-btn:focus-visible,
        .pc-pill-btn:hover,
        .pc-pill-btn:focus-visible {
            background: var(--pc-glass-strong) !important;
        }

        .pc-icon-btn:active,
        .pc-pill-btn:active { transform: scale(.97); }

        .pc-pill-btn--primary,
        .pc-pill-btn--primary:hover,
        .pc-pill-btn--primary:focus-visible {
            border-color: rgba(37,99,235,.9) !important;
            background: var(--pc-blue) !important;
            color: #fff !important;
        }

        .pc-pill-btn--primary:hover { background: var(--pc-blue-hover) !important; }

        .pc-brand { min-width: 0; }
        .pc-brand-title {
            overflow: hidden;
            color: var(--pc-text);
            font-size: 15px;
            font-weight: 600;
            line-height: 19px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pc-brand-subtitle {
            overflow: hidden;
            margin-top: 1px;
            color: var(--pc-muted);
            font-size: 12px;
            line-height: 16px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pc-info-wrap { position: relative; }
        .pc-info-popover {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            z-index: 120;
            width: 250px;
            overflow: hidden;
            border-radius: 22px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-4px) scale(.98);
            transform-origin: top right;
            transition: opacity 160ms cubic-bezier(.23,1,.32,1), transform 160ms cubic-bezier(.23,1,.32,1), visibility 160ms;
        }
        .pc-info-popover.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }
        .pc-info-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 13px 14px 10px;
            border-bottom: .5px solid var(--pc-border);
            font-size: 13px;
            font-weight: 600;
        }
        .pc-info-row {
            display: flex;
            min-height: 40px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 14px;
            color: var(--pc-muted);
            font-size: 13px;
        }
        .pc-info-row + .pc-info-row { border-top: .5px solid var(--pc-border); }
        .pc-info-row strong { color: var(--pc-text); font-weight: 500; }

        .pc-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 310px;
            gap: 14px;
            margin-top: 14px;
            align-items: start;
        }

        .pc-editor-card,
        .pc-cover-card {
            overflow: visible;
            border-radius: 28px;
        }

        .pc-editor-card { min-width: 0; }
        .pc-cover-card { position: sticky; top: 84px; overflow: hidden; }

        .pc-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 46px;
            padding: 0 18px;
            border-bottom: .5px solid var(--pc-border);
        }
        .pc-section-title { font-size: 13px; font-weight: 600; color: var(--pc-text); }
        .pc-section-note { color: var(--pc-muted); font-size: 11px; font-weight: 500; }

        .pc-title-zone { padding: 24px 28px 18px; }
        .pc-field-label {
            display: block;
            margin-bottom: 8px;
            color: var(--pc-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .post-create-page .pc-title-input.pc-title-input {
            display: block !important;
            width: 100% !important;
            min-height: 42px !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            resize: none !important;
            border: 0 !important;
            border-radius: 0 !important;
            outline: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            color: var(--pc-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-size: clamp(25px, 2.4vw, 32px) !important;
            font-weight: 600 !important;
            line-height: 1.24 !important;
            letter-spacing: -.018em !important;
        }
        .post-create-page .pc-title-input.pc-title-input::placeholder { color: var(--pc-subtle) !important; }

        .pc-editor-stage {
            min-height: 66vh;
            padding: 0 0 18px;
        }

        .post-create-page [data-editorjs-wrapper] [x-ref="holder"] {
            display: block !important;
            min-height: 62vh !important;
            padding: 22px 28px 42px !important;
            color: var(--pc-text) !important;
        }

        .post-create-page textarea[data-editor-content] {
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
            color: var(--pc-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.7 !important;
        }
        .post-create-page textarea[data-editor-content].hidden { display: none !important; }

        .post-create-page [data-editorjs-wrapper] .codex-editor,
        .post-create-page [data-editorjs-wrapper] .codex-editor *:not(iconify-icon) {
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
        }
        .post-create-page [data-editorjs-wrapper] .codex-editor__redactor { padding-bottom: 120px !important; }
        .post-create-page [data-editorjs-wrapper] .ce-block__content,
        .post-create-page [data-editorjs-wrapper] .ce-toolbar__content { max-width: 720px !important; }
        .post-create-page [data-editorjs-wrapper] .ce-paragraph {
            color: var(--pc-text) !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.7 !important;
        }
        .post-create-page [data-editorjs-wrapper] .ce-header {
            color: var(--pc-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-weight: 600 !important;
            line-height: 1.28 !important;
        }
        .post-create-page [data-editorjs-wrapper] .ce-toolbar__plus,
        .post-create-page [data-editorjs-wrapper] .ce-toolbar__settings-btn {
            width: 34px !important;
            height: 34px !important;
            border: .5px solid var(--pc-border) !important;
            border-radius: 999px !important;
            background: var(--pc-glass-soft) !important;
            color: var(--pc-muted) !important;
            box-shadow: none !important;
        }
        .post-create-page [data-editorjs-wrapper] .ce-popover,
        .post-create-page [data-editorjs-wrapper] .ce-inline-toolbar,
        .post-create-page [data-editorjs-wrapper] .ce-conversion-toolbar {
            border: .5px solid var(--pc-border) !important;
            border-radius: 18px !important;
            background: var(--pc-glass-strong) !important;
            color: var(--pc-text) !important;
            box-shadow: var(--pc-shadow) !important;
            backdrop-filter: blur(24px) saturate(155%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(155%) !important;
        }
        html.dark .post-create-page [data-editorjs-wrapper] :is(.bg-white,.bg-slate-50,.bg-gray-50),
        html[data-system-theme="dark"] .post-create-page [data-editorjs-wrapper] :is(.bg-white,.bg-slate-50,.bg-gray-50),
        html[data-theme="dark"] .post-create-page [data-editorjs-wrapper] :is(.bg-white,.bg-slate-50,.bg-gray-50) {
            background-color: rgba(15,23,42,.82) !important;
        }
        html.dark .post-create-page [data-editorjs-wrapper] :is(.text-slate-900,.text-slate-800,.text-slate-700),
        html[data-system-theme="dark"] .post-create-page [data-editorjs-wrapper] :is(.text-slate-900,.text-slate-800,.text-slate-700),
        html[data-theme="dark"] .post-create-page [data-editorjs-wrapper] :is(.text-slate-900,.text-slate-800,.text-slate-700) {
            color: #e5edf8 !important;
        }

        .pc-cover-body { padding: 14px; }
        .pc-cover {
            position: relative;
            overflow: hidden;
            width: 100%;
            min-height: 188px;
            border: .5px solid var(--pc-border);
            border-radius: 20px;
            background: var(--pc-glass-soft);
        }
        .pc-cover-drop {
            display: flex;
            min-height: 188px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 20px;
            color: var(--pc-muted);
            cursor: pointer;
            text-align: center;
        }
        .pc-cover-drop:hover { background: rgba(37,99,235,.055); color: var(--pc-blue); }
        .pc-cover-symbol {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--pc-border);
            border-radius: 999px;
            background: var(--pc-glass-soft);
        }
        .pc-cover-preview { display: none; position: relative; min-height: 188px; }
        .pc-cover-preview img { display: block; width: 100%; min-height: 188px; max-height: 360px; object-fit: cover; }
        .pc-cover.has-image .pc-cover-drop { display: none; }
        .pc-cover.has-image .pc-cover-preview { display: block; }
        .pc-cover-actions {
            position: absolute;
            top: 9px;
            right: 9px;
            display: flex;
            gap: 6px;
        }
        .pc-cover-hint { margin-top: 10px; color: var(--pc-muted); font-size: 11px; line-height: 16px; }

        .pc-field {
            display: block !important;
            width: 100% !important;
            min-height: 44px !important;
            border: .5px solid var(--pc-border) !important;
            border-radius: 15px !important;
            background: var(--pc-input) !important;
            padding: 10px 12px !important;
            color: var(--pc-text) !important;
            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            outline: none !important;
            box-shadow: none !important;
        }
        textarea.pc-field { min-height: 88px !important; resize: vertical !important; }
        .pc-field:focus { border-color: rgba(37,99,235,.65) !important; background: var(--pc-glass-strong) !important; }
        .pc-field::placeholder { color: var(--pc-subtle) !important; }
        .pc-setting-label { display: block; margin-bottom: 6px; color: var(--pc-muted); font-size: 12px; font-weight: 500; }
        .pc-stack > * + * { margin-top: 11px; }

        .pc-tag-chip { position: relative; display: inline-flex; cursor: pointer; }
        .pc-tag-chip input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .pc-tag-chip span {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            border: .5px solid var(--pc-border);
            border-radius: 999px;
            background: var(--pc-glass-soft);
            padding: 0 10px;
            color: var(--pc-muted);
            font-size: 12px;
        }
        .pc-tag-chip input:checked + span {
            border-color: rgba(37,99,235,.42);
            background: rgba(37,99,235,.11);
            color: var(--pc-blue);
        }

        .pc-settings-backdrop {
            position: fixed;
            inset: 0;
            z-index: 108;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(2,6,23,.34);
            transition: opacity 180ms cubic-bezier(.23,1,.32,1), visibility 180ms;
        }
        .pc-settings-backdrop.is-open { opacity: 1; visibility: visible; pointer-events: auto; }

        .pc-settings-panel {
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
            transition: transform 220ms cubic-bezier(.32,.72,0,1), opacity 160ms ease-out, visibility 220ms;
        }
        .pc-settings-panel.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateX(0);
        }
        .pc-settings-head {
            display: flex;
            min-height: 68px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 13px 11px 18px;
            border-bottom: .5px solid var(--pc-border);
        }
        .pc-settings-title { font-size: 15px; font-weight: 600; }
        .pc-settings-subtitle { margin-top: 2px; color: var(--pc-muted); font-size: 12px; line-height: 16px; }
        .pc-settings-scroll { flex: 1 1 auto; overflow-y: auto; padding: 12px; }
        .pc-settings-group {
            overflow: hidden;
            border: .5px solid var(--pc-border);
            border-radius: 20px;
            background: rgba(255,255,255,.12);
        }
        html.dark .pc-settings-group,
        html[data-system-theme="dark"] .pc-settings-group,
        html[data-theme="dark"] .pc-settings-group { background: rgba(15,23,42,.26); }
        .pc-settings-group + .pc-settings-group { margin-top: 10px; }
        .pc-settings-group-title {
            display: flex;
            min-height: 44px;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
            border-bottom: .5px solid var(--pc-border);
            color: var(--pc-text);
            font-size: 13px;
            font-weight: 600;
        }
        .pc-settings-group-body { padding: 12px; }
        .pc-tools { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .pc-tools .pc-pill-btn { width: 100% !important; min-height: 41px !important; padding-inline: 10px !important; font-size: 13px !important; }
        .pc-toggle-list { overflow: hidden; border: .5px solid var(--pc-border); border-radius: 16px; }
        .pc-toggle-row { display: flex; min-height: 57px; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 10px; }
        .pc-toggle-row + .pc-toggle-row { border-top: .5px solid var(--pc-border); }
        .pc-toggle-title { font-size: 13px; font-weight: 500; }
        .pc-toggle-note { margin-top: 2px; color: var(--pc-muted); font-size: 11px; line-height: 15px; }
        .pc-settings-footer { flex: 0 0 auto; padding: 11px 12px 12px; border-top: .5px solid var(--pc-border); }
        .pc-settings-footer .pc-pill-btn { width: 100% !important; }

        .pc-preview-modal {
            position: fixed;
            inset: 0;
            z-index: 130;
            overflow-y: auto;
            padding: 18px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(2,6,23,.44);
            transition: opacity 180ms ease-out, visibility 180ms;
        }
        .pc-preview-modal.is-open { opacity: 1; visibility: visible; pointer-events: auto; }
        .pc-preview-card { width: min(760px,100%); margin: 4vh auto; overflow: hidden; border-radius: 28px; }
        .pc-preview-body { max-height: 78vh; overflow-y: auto; padding: 20px 24px 30px; }

        .pc-error {
            margin-top: 14px;
            border: .5px solid rgba(220,38,38,.28);
            border-radius: 18px;
            background: rgba(254,226,226,.72);
            padding: 12px 14px;
            color: #991b1b;
            font-size: 13px;
        }
        html.dark .pc-error,
        html[data-system-theme="dark"] .pc-error,
        html[data-theme="dark"] .pc-error { background: rgba(127,29,29,.24); color: #fecaca; }

        .pc-toast {
            position: fixed;
            left: 50%;
            bottom: 22px;
            z-index: 160;
            width: min(430px, calc(100vw - 28px));
            transform: translateX(-50%);
            border: .5px solid var(--pc-border);
            border-radius: 18px;
            background: var(--pc-glass-strong);
            padding: 12px 14px;
            color: var(--pc-text);
            font-size: 13px;
            box-shadow: var(--pc-shadow);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        @media (min-width: 1024px) {
            .pc-settings-backdrop { background: transparent; }
        }

        @media (max-width: 980px) {
            .pc-shell { width: min(820px, calc(100% - 18px)); padding-top: 8px; }
            .pc-workspace { grid-template-columns: 1fr; }
            .pc-cover-card { position: static; order: 2; }
            .pc-editor-card { order: 1; }
        }

        @media (max-width: 640px) {
            .pc-shell { width: calc(100% - 14px); }
            .pc-topbar { top: 7px; min-height: 56px; gap: 7px; padding: 7px; }
            .pc-brand-subtitle { display: none; }
            .pc-icon-btn { width: 40px !important; height: 40px !important; flex-basis: 40px !important; }
            .pc-pill-btn--primary { min-height: 40px !important; padding-inline: 13px !important; }
            .pc-pill-btn--primary iconify-icon { display: none; }
            .pc-workspace { margin-top: 9px; gap: 9px; }
            .pc-editor-card,
            .pc-cover-card { border-radius: 23px; }
            .pc-title-zone { padding: 20px 17px 15px; }
            .post-create-page .pc-title-input.pc-title-input { font-size: 25px !important; }
            .pc-section-head { min-height: 44px; padding-inline: 17px; }
            .post-create-page [data-editorjs-wrapper] [x-ref="holder"] { min-height: 66vh !important; padding: 18px 17px 36px !important; }
            .post-create-page textarea[data-editor-content] { min-height: 66vh !important; padding: 18px 17px 36px !important; font-size: 16px !important; }
            .post-create-page [data-editorjs-wrapper] .ce-paragraph { font-size: 16px !important; }
            .pc-cover-body { padding: 12px; }
            .pc-settings-panel { top: 8px; right: 8px; bottom: 8px; width: calc(100vw - 16px); border-radius: 26px; }
            .pc-settings-backdrop { backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); }
            .pc-preview-modal { padding: 8px; }
            .pc-preview-card { border-radius: 23px; }
            .pc-preview-body { padding: 16px 17px 24px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .pc-info-popover,
            .pc-settings-panel,
            .pc-settings-backdrop,
            .pc-preview-modal { transition: none !important; }
        }
        @media (prefers-reduced-transparency: reduce) {
            .pc-glass,
            .pc-glass-strong,
            .pc-settings-backdrop,
            .pc-preview-modal,
            .pc-toast { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        }
    </style>

    <div class="post-create-page" data-create-page>
        <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published', 0) ? 1 : 0 }}">
            <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">

            <div class="pc-shell">
                <header class="pc-topbar pc-glass-strong">
                    <div class="pc-topbar-left">
                        <a href="{{ route('blog.index') }}" class="pc-icon-btn" aria-label="{{ __('post_create.back') }}" title="{{ __('post_create.back') }}">
                            <iconify-icon icon="lucide:chevron-left" class="text-[21px]"></iconify-icon>
                        </a>
                        <div class="pc-brand">
                            <div class="pc-brand-title">Yeni gönderi</div>
                            <div class="pc-brand-subtitle">Ografi Editor</div>
                        </div>
                    </div>

                    <div class="pc-topbar-actions">
                        <div class="pc-info-wrap">
                            <button type="button" class="pc-icon-btn" data-info-toggle aria-label="Yazı bilgisi" aria-expanded="false">
                                <iconify-icon icon="lucide:info" class="text-[19px]"></iconify-icon>
                            </button>
                            <div class="pc-info-popover pc-glass-strong" data-info-popover>
                                <div class="pc-info-head">
                                    <iconify-icon icon="lucide:info" class="text-[16px]"></iconify-icon>
                                    <span>Yazı bilgisi</span>
                                </div>
                                <div class="pc-info-row"><span>Okuma süresi</span><strong data-reading-time>1 dk</strong></div>
                                <div class="pc-info-row"><span>Kelime</span><strong data-word-count>0</strong></div>
                                <div class="pc-info-row"><span>Kayıt</span><strong>Taslak destekli</strong></div>
                            </div>
                        </div>

                        <button type="button" class="pc-icon-btn" data-open-settings aria-label="Gelişmiş seçenekler" aria-expanded="false">
                            <iconify-icon icon="lucide:settings" class="text-[19px]"></iconify-icon>
                        </button>

                        <button type="submit" class="pc-pill-btn pc-pill-btn--primary" data-submit-intent="publish">
                            <iconify-icon icon="lucide:send" class="text-[16px]"></iconify-icon>
                            <span>Yayınla</span>
                        </button>
                    </div>
                </header>

                @if ($errors->any())
                    <div class="pc-error">
                        <div class="font-medium">Gönderi kaydedilemedi.</div>
                        <div class="mt-1">{{ $errors->first() }}</div>
                    </div>
                @endif

                <div class="pc-workspace">
                    <section class="pc-editor-card pc-glass-strong">
                        <div class="pc-title-zone">
                            <label for="title" class="pc-field-label">Başlık</label>
                            <textarea id="title" name="title" rows="1" required class="pc-title-input" placeholder="Başlığını yaz..." data-autogrow>{{ old('title') }}</textarea>
                        </div>

                        <div class="pc-section-head">
                            <div class="pc-section-title">Yazı</div>
                            <div class="pc-section-note">EditorJS</div>
                        </div>

                        <div class="pc-editor-stage" data-editorjs-wrapper>
                            <div x-ref="holder"></div>
                            <textarea id="content" name="content" data-editor-content data-mentionable="users" class="hidden" placeholder="Gönderini yazmaya başla...">{{ old('content') }}</textarea>
                        </div>
                    </section>

                    <aside class="pc-cover-card pc-glass-strong">
                        <div class="pc-section-head">
                            <div class="pc-section-title">Kapak görseli</div>
                            <iconify-icon icon="lucide:image" class="text-[17px] text-[var(--pc-muted)]"></iconify-icon>
                        </div>
                        <div class="pc-cover-body">
                            <div class="pc-cover" data-cover-field>
                                <label for="featured_image" class="pc-cover-drop">
                                    <span class="pc-cover-symbol"><iconify-icon icon="lucide:image-plus" class="text-[19px]"></iconify-icon></span>
                                    <span class="text-[13px] font-medium text-[var(--pc-text)]">Görsel seç</span>
                                    <span class="text-[11px] leading-4">JPG, PNG veya WebP · en fazla 5 MB</span>
                                </label>
                                <div class="pc-cover-preview" data-cover-preview>
                                    <img data-cover-preview-img alt="">
                                    <div class="pc-cover-actions">
                                        <button type="button" class="pc-icon-btn !h-9 !w-9 !basis-9" data-cover-change aria-label="Görseli değiştir"><iconify-icon icon="lucide:pencil" class="text-[15px]"></iconify-icon></button>
                                        <button type="button" class="pc-icon-btn !h-9 !w-9 !basis-9" data-cover-remove aria-label="Görseli kaldır"><iconify-icon icon="lucide:x" class="text-[15px]"></iconify-icon></button>
                                    </div>
                                </div>
                                <input id="featured_image" name="featured_image" type="file" accept="image/*" class="sr-only" data-cover-input>
                            </div>
                            <div class="pc-cover-hint">Kapak görseli yalnızca burada yönetilir; gelişmiş seçeneklerde ikinci bir kopyası yoktur.</div>
                        </div>
                    </aside>
                </div>
            </div>

            <div class="pc-settings-backdrop" data-settings-backdrop></div>

            <aside class="pc-settings-panel pc-glass-strong" data-settings-panel aria-label="Gelişmiş seçenekler" aria-hidden="true">
                <div class="pc-settings-head">
                    <div>
                        <div class="pc-settings-title">Gelişmiş seçenekler</div>
                        <div class="pc-settings-subtitle">Yayın, SEO ve görünürlük ayarları</div>
                    </div>
                    <button type="button" class="pc-icon-btn !h-10 !w-10 !basis-10" data-close-settings aria-label="Kapat">
                        <iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon>
                    </button>
                </div>

                <div class="pc-settings-scroll">
                    <section class="pc-settings-group">
                        <div class="pc-settings-group-title"><iconify-icon icon="lucide:wand-sparkles" class="text-[17px] text-[var(--pc-muted)]"></iconify-icon><span>Yardımcı araçlar</span></div>
                        <div class="pc-settings-group-body">
                            <div class="pc-tools">
                                <button type="button" class="pc-pill-btn" data-open-preview><iconify-icon icon="lucide:eye" class="text-[16px]"></iconify-icon>Ön izleme</button>
                                <button type="button" class="pc-pill-btn" data-ai-assist><iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[16px]"></iconify-icon>AI yardım</button>
                            </div>
                        </div>
                    </section>

                    <section class="pc-settings-group">
                        <div class="pc-settings-group-title"><iconify-icon icon="lucide:layout-list" class="text-[17px] text-[var(--pc-muted)]"></iconify-icon><span>Gönderi bilgileri</span></div>
                        <div class="pc-settings-group-body pc-stack">
                            <div>
                                <label for="category_id" class="pc-setting-label">Topluluk / kategori</label>
                                <select id="category_id" name="category_id" class="pc-field">
                                    <option value="">Kategori seç</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($initialCategoryId === (int) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="excerpt" class="pc-setting-label">Kısa açıklama</label>
                                <textarea id="excerpt" name="excerpt" class="pc-field" rows="3" placeholder="Gönderinin kısa özeti...">{{ old('excerpt') }}</textarea>
                            </div>
                            <div>
                                <label for="new_tags" class="pc-setting-label">Yeni etiketler</label>
                                <input id="new_tags" name="new_tags" type="text" class="pc-field" value="{{ old('new_tags') }}" placeholder="laravel, tasarım, teknoloji">
                            </div>
                            @if(isset($tags) && collect($tags)->isNotEmpty())
                                <div>
                                    <div class="pc-setting-label">Mevcut etiketler</div>
                                    <div class="flex max-h-36 flex-wrap gap-1.5 overflow-y-auto pr-1">
                                        @foreach($tags as $tag)
                                            <label class="pc-tag-chip">
                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id))>
                                                <span>#{{ $tag->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="pc-settings-group">
                        <div class="pc-settings-group-title"><iconify-icon icon="lucide:calendar-clock" class="text-[17px] text-[var(--pc-muted)]"></iconify-icon><span>Yayınlama</span></div>
                        <div class="pc-settings-group-body pc-stack">
                            <div>
                                <label for="published_at" class="pc-setting-label">Yayın tarihi</label>
                                <input id="published_at" name="published_at" type="datetime-local" class="pc-field" value="{{ old('published_at') }}">
                            </div>
                            <div class="pc-toggle-list">
                                <div class="pc-toggle-row">
                                    <div><div class="pc-toggle-title">Yorumları kapat</div><div class="pc-toggle-note">Yeni yorum alınmaz.</div></div>
                                    <x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" />
                                </div>
                                <div class="pc-toggle-row">
                                    <div><div class="pc-toggle-title">Hassas içerik</div><div class="pc-toggle-note">İçerik uyarısıyla gösterilir.</div></div>
                                    <x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" />
                                </div>
                                <div class="pc-toggle-row">
                                    <div><div class="pc-toggle-title">Gönderiyi sabitle</div><div class="pc-toggle-note">Uygun alanlarda üstte gösterilir.</div></div>
                                    <x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pc-settings-group">
                        <div class="pc-settings-group-title"><iconify-icon icon="lucide:search" class="text-[17px] text-[var(--pc-muted)]"></iconify-icon><span>SEO</span></div>
                        <div class="pc-settings-group-body pc-stack">
                            <div><label for="meta_title" class="pc-setting-label">SEO başlığı</label><input id="meta_title" name="meta_title" type="text" class="pc-field" value="{{ old('meta_title') }}" placeholder="Arama sonucunda görünecek başlık"></div>
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2"><label for="meta_description" class="pc-setting-label !mb-0">Meta açıklama</label><span class="text-[10px] text-[var(--pc-subtle)]" data-meta-description-count>0/160</span></div>
                                <textarea id="meta_description" name="meta_description" maxlength="160" class="pc-field" rows="3" placeholder="Arama sonucunda görünecek açıklama">{{ old('meta_description') }}</textarea>
                            </div>
                            <div><label for="slug" class="pc-setting-label">Özel bağlantı</label><input id="slug" name="slug" type="text" class="pc-field" value="{{ old('slug') }}" placeholder="ornek-gonderi"></div>
                            <div><label for="meta_keywords" class="pc-setting-label">Anahtar kelimeler</label><input id="meta_keywords" name="meta_keywords" type="text" class="pc-field" value="{{ old('meta_keywords') }}" placeholder="teknoloji, yazılım, gündem"></div>
                        </div>
                    </section>

                    <section class="pc-settings-group">
                        <div class="pc-settings-group-title"><iconify-icon icon="lucide:copyright" class="text-[17px] text-[var(--pc-muted)]"></iconify-icon><span>Görsel hakları</span></div>
                        <div class="pc-settings-group-body pc-stack">
                            <input id="image_creator_name" name="image_creator_name" type="text" class="pc-field" value="{{ old('image_creator_name') }}" placeholder="Görsel üreticisi / fotoğrafçı">
                            <input id="image_credit_text" name="image_credit_text" type="text" class="pc-field" value="{{ old('image_credit_text') }}" placeholder="Görsel kredisi">
                            <input id="image_copyright_notice" name="image_copyright_notice" type="text" class="pc-field" value="{{ old('image_copyright_notice') }}" placeholder="Telif bildirimi">
                            <input id="image_license_url" name="image_license_url" type="url" class="pc-field" value="{{ old('image_license_url') }}" placeholder="Lisans bağlantısı">
                            <input id="image_acquire_url" name="image_acquire_url" type="url" class="pc-field" value="{{ old('image_acquire_url') }}" placeholder="Kaynak / satın alma bağlantısı">
                        </div>
                    </section>
                </div>

                <div class="pc-settings-footer">
                    <button type="submit" class="pc-pill-btn" data-submit-intent="draft"><iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>Taslağa kaydet</button>
                </div>
            </aside>
        </form>

        <div class="pc-preview-modal" data-preview-modal aria-hidden="true">
            <div class="pc-preview-card pc-glass-strong">
                <div class="pc-settings-head">
                    <div><div class="pc-settings-title">Gönderi ön izlemesi</div><div class="pc-settings-subtitle">Yayınlamadan önce son görünüm</div></div>
                    <button type="button" class="pc-icon-btn !h-10 !w-10 !basis-10" data-close-preview aria-label="Kapat"><iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon></button>
                </div>
                <div class="pc-preview-body"><div data-preview-content class="prose prose-slate max-w-none dark:prose-invert"></div></div>
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
            const aiAssistButton = document.querySelector('[data-ai-assist]');
            const aiAssistIcon = aiAssistButton?.querySelector('[data-ai-assist-icon]');

            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            const showToast = (message, isError = false) => {
                document.querySelectorAll('.pc-toast').forEach((el) => el.remove());
                const toast = document.createElement('div');
                toast.className = 'pc-toast';
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
                titleField.style.height = `${Math.max(42, titleField.scrollHeight)}px`;
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
            settingsTrigger?.addEventListener('click', () => settingsPanel?.classList.contains('is-open') ? closeSettings() : openSettings());
            document.querySelector('[data-close-settings]')?.addEventListener('click', closeSettings);
            settingsBackdrop?.addEventListener('click', () => { if (!isDesktop()) closeSettings(); });

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
                if (metaDescription && metaDescriptionCount) metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
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

            const stripHtml = (value) => {
                const el = document.createElement('div');
                el.innerHTML = String(value || '');
                return el.textContent || '';
            };

            const readEditorData = async () => {
                if (wrapper?.__editorInstance?.save) {
                    try { return await wrapper.__editorInstance.save(); } catch {}
                }
                if (jsonField?.value) {
                    try { return JSON.parse(jsonField.value); } catch {}
                }
                return { blocks: [] };
            };

            const readEditorPlainText = async () => {
                const data = await readEditorData();
                const value = (data?.blocks || []).map(blockText).join(' ').trim();
                return value || stripHtml(fallbackTextarea?.value || '').trim();
            };

            const updateStats = async () => {
                const text = await readEditorPlainText();
                const words = (text.match(/\S+/g) || []).length;
                if (wordCountEl) wordCountEl.textContent = String(words);
                if (readingTimeEl) readingTimeEl.textContent = `${Math.max(1, Math.ceil(words / 200))} dk`;
            };
            let statsTimer = null;
            const scheduleStats = () => {
                window.clearTimeout(statsTimer);
                statsTimer = window.setTimeout(updateStats, 300);
            };
            wrapper?.addEventListener('input', scheduleStats);
            wrapper?.addEventListener('keyup', scheduleStats);
            jsonField?.addEventListener('input', scheduleStats);
            titleField?.addEventListener('input', scheduleStats);

            const showFallback = () => {
                holder?.classList.add('hidden');
                fallbackTextarea?.classList.remove('hidden');
            };

            const initEditor = async (attempt = 0) => {
                if (!wrapper) return;
                if (!window.initFilamentEditorJsField) {
                    if (attempt < 8) {
                        window.setTimeout(() => initEditor(attempt + 1), 250);
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
            document.querySelectorAll('[data-submit-intent]').forEach((button) => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    if (!form || submitLocked) return;
                    const intent = button.getAttribute('data-submit-intent') === 'draft' ? 'draft' : 'publish';
                    const title = String(titleField?.value || '').trim();
                    try { await syncEditorFields(); } catch {
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
                return `${image}<h1 class="text-[28px] font-semibold leading-tight tracking-[-0.018em] text-[var(--pc-text)]">${escapeHtml(title || 'Başlıksız gönderi')}</h1>${excerpt ? `<p class="mt-3 text-[15px] leading-6 text-[var(--pc-muted)]">${escapeHtml(excerpt)}</p>` : ''}<div class="mt-7 text-[16px] leading-7 text-[var(--pc-text)]">${contentHtml || '<p>Henüz içerik yok.</p>'}</div>`;
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
                if (!settingsPanel?.classList.contains('is-open') || isDesktop()) document.documentElement.classList.remove('overflow-hidden');
            };
            document.querySelector('[data-open-preview]')?.addEventListener('click', openPreview);
            document.querySelector('[data-close-preview]')?.addEventListener('click', closePreview);
            previewModal?.addEventListener('click', (event) => { if (event.target === previewModal) closePreview(); });

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
                    if (!response.ok || !data.ok) throw new Error(data.message || 'Yapay zeka isteği başarısız.');
                    const metaTitle = document.getElementById('meta_title');
                    const metaKeywords = document.getElementById('meta_keywords');
                    const excerptField = document.getElementById('excerpt');
                    if (data.meta_title && metaTitle) metaTitle.value = data.meta_title;
                    if (data.meta_description && metaDescription) { metaDescription.value = data.meta_description; syncMetaCount(); }
                    if (Array.isArray(data.meta_keywords) && metaKeywords) metaKeywords.value = data.meta_keywords.join(', ');
                    if (data.excerpt && excerptField && !excerptField.value.trim()) excerptField.value = data.excerpt;
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
        });
    </script>
@endpush
