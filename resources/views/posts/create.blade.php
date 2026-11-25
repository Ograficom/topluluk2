@extends('layouts.app')

@section('title', isset($post) ? 'Yazıyı düzenle' : 'Yazı oluştur')

@section('content')
    @unless(isset($post) || isset($quoteSource))
        <script src="{{ asset('assets/js/pwa-file-launch.js') }}" defer></script>
    @endunless
    @if ($errors->any() && ! isset($quoteSource))
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="repost-modal-backdrop addpost post-create-simple post-create-modal-page" id="createPostModal" style="display: flex;">
        <style>

/* Original supplied Repost modal CSS. */
.repost-modal-backdrop {position:fixed;inset:0;z-index:1050;background:rgba(15,23,42,.45);display:flex;align-items:center;justify-content:center;padding:16px;font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;}
.repost-modal-box {width:100%;max-width:600px;max-height:90vh;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;display:flex;flex-direction:column;transition:max-width .2s ease,min-height .2s ease;position:relative;}
.repost-modal-box.is-expanded {max-width:860px;min-height:75vh;}
.repost-modal-topbar {display:flex;align-items:center;justify-content:space-between;padding:18px 24px 10px;}
.repost-modal-heading {margin:0;font-size:19px;font-weight:600;color:#0f172a;letter-spacing:-.01em;}
.repost-modal-actions {display:flex;align-items:center;gap:6px;}
.repost-icon-btn {width:34px;height:34px;border-radius:10px;border:none;background:transparent;color:#475569;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s ease,color .15s ease;}
.repost-icon-btn:hover {background:#f1f5f9;color:#0f172a;}
.repost-modal-userbar {display:flex;align-items:center;gap:10px;padding:6px 24px 14px;}
.repost-modal-avatar {width:32px;height:32px;border-radius:50%;object-fit:cover;background:#f1f5f9;}
.repost-modal-username {font-size:14px;font-weight:600;color:#0f172a;}
.repost-modal-sep {font-size:16px;color:#94a3b8;line-height:1;}
.repost-modal-cat {font-size:13.5px;font-weight:400;color:#64748b;background:transparent;border:none;padding:2px 6px;border-radius:6px;cursor:pointer;outline:none;}
.repost-modal-cat:hover {background:#f1f5f9;color:#334155;}
.repost-modal-body {padding:4px 24px 18px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:14px;}
.repost-title-input {width:100%;border:none;outline:none;font-size:18px;font-weight:600;color:#0f172a;background:transparent;}
.repost-title-input::placeholder {color:#64748b;}
.repost-content-textarea {width:100%;border:none;outline:none;resize:none;overflow:hidden;font-family:inherit;font-size:15px;line-height:1.6;color:#334155;background:transparent;min-height:92px;}
.repost-content-textarea::placeholder {color:#94a3b8;}
.repost-modal-footer {padding:6px 24px 20px;display:flex;flex-direction:column;gap:16px;}
.repost-toolbar {display:flex;align-items:center;gap:14px;}
.repost-tool-btn {width:32px;height:32px;border-radius:8px;border:none;background:transparent;color:#64748b;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s ease,color .15s ease;}
.repost-tool-btn:hover {background:#f1f5f9;color:#0f172a;}
.repost-footer-bottom {display:flex;align-items:center;justify-content:space-between;}
.repost-settings-wrap {position:relative;}
.repost-settings-trigger {display:inline-flex;align-items:center;gap:8px;background:transparent;border:none;padding:6px 8px 6px 0;font-size:13.5px;color:#64748b;cursor:pointer;border-radius:8px;transition:color .15s ease;}
.repost-settings-trigger:hover,.repost-settings-trigger.is-active {color:#0f172a;}
.repost-settings-popover {position:absolute;bottom:calc(100% + 12px);left:0;width:340px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 10px 28px rgba(15,23,42,.08);display:none;flex-direction:column;gap:16px;z-index:1060;}
.repost-settings-popover.is-open {display:flex;}
.repost-switch-row {display:flex;align-items:center;gap:12px;cursor:pointer;user-select:none;}
.repost-switch-row input {display:none;}
.repost-switch-slider {width:40px;height:24px;background:#e2e8f0;border-radius:9999px;position:relative;flex-shrink:0;transition:background .2s ease;}
.repost-switch-slider::after {content:'';position:absolute;top:3px;left:3px;width:18px;height:18px;background:#fff;border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.12);transition:transform .2s ease;}
.repost-switch-row input:checked + .repost-switch-slider {background:#2563eb;}
.repost-switch-row input:checked + .repost-switch-slider::after {transform:translateX(16px);}
.repost-switch-label {font-size:14px;color:#334155;line-height:1.4;}
.repost-source-group {display:flex;flex-direction:column;gap:8px;}
.repost-source-label {font-size:13.5px;color:#64748b;}
.repost-source-input {width:100%;height:44px;padding:0 14px;border:1px solid #cbd5e1;border-radius:12px;font-size:14px;color:#0f172a;background:#fff;outline:none;box-sizing:border-box;transition:border-color .15s ease;}
.repost-source-input:focus {border-color:#2563eb;}
.repost-publish-btn {background:#0a84ff;color:#fff;border:none;border-radius:12px;padding:11px 22px;font-size:14px;font-weight:500;cursor:pointer;transition:background .15s ease;}
.repost-publish-btn:hover {background:#0070e0;}
/* Integration only: isolate from old modal CSS and preserve the supplied geometry. */
#createPostModal.repost-modal-backdrop {position:fixed!important;inset:0!important;z-index:12000!important;display:flex;align-items:center!important;justify-content:center!important;padding:16px!important;background:rgba(15,23,42,.45)!important;box-sizing:border-box!important;backdrop-filter:none!important;}
#createPostModal > #createPostBox {position:relative!important;display:flex;flex-direction:column!important;box-sizing:border-box!important;width:100%!important;max-width:600px!important;max-height:90dvh!important;min-height:0!important;height:auto!important;padding:0!important;margin:0!important;border:1px solid #e2e8f0!important;border-radius:20px!important;background:#fff!important;box-shadow:none!important;color:#334155!important;font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif!important;overflow:visible!important;}
#createPostModal > #createPostBox.is-expanded {max-width:860px!important;min-height:75dvh!important;}
#createPostModal:is(.is-gallery-open,.is-video-open,.is-widget-open) > #createPostBox {display:none!important;}
#createPostBox *,#createPostBox *::before,#createPostBox *::after {box-sizing:border-box;}
#createPostBox [hidden] {display:none!important;}
#createPostBox :is(.repost-modal-topbar,.repost-modal-userbar,.repost-modal-footer) {flex-shrink:0;}
#createPostBox .repost-modal-heading {line-height:1.3;}
#createPostBox .repost-modal-userbar {min-width:0;}
#createPostBox .repost-modal-avatar {flex:0 0 32px;}
#createPostBox .repost-modal-username {white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
#createPostBox .repost-modal-cat {width:auto;min-width:0;max-width:50%;height:auto;appearance:auto;box-shadow:none;}
#createPostBox .repost-modal-body {min-height:0;}
#createPostBox .repost-title-input {flex:none;padding:0;margin:0;height:28px;min-height:28px;line-height:1.5;box-shadow:none;border-radius:0;}
#createPostBox .repost-editor-wrap {position:relative;flex:none;min-width:0;padding-left:32px;}
#createPostBox #post-editorjs {overflow:visible;font-family:inherit;font-size:15px;font-weight:400;line-height:1.6;min-height:92px;}
#createPostBox #post-editorjs:not(.is-ready):empty::before {content:attr(data-placeholder);color:#94a3b8;}
#createPostBox #post-editorjs :is(.codex-editor,.codex-editor__redactor) {min-height:92px;height:auto;overflow:visible;padding-bottom:0!important;margin-right:0!important;}
#createPostBox #post-editorjs :is(.ce-block__content,.ce-toolbar__content) {max-width:none;margin-left:0;margin-right:0;}
#createPostBox #post-editorjs .ce-paragraph {font-size:15px;line-height:1.6;padding:2px 0;min-height:24px;overflow-wrap:anywhere;}
#createPostBox #post-editorjs .ce-block:not(:only-of-type) .ce-paragraph::before {content:none!important;}
#createPostBox #post-editorjs .ce-toolbar__content {position:relative;}
#createPostBox [data-editor-error] {margin:4px 0;color:#b91c1c;font-size:13px;line-height:1.45;}
#createPostBox #post-editorjs [contenteditable="true"] {outline:0;overflow-wrap:anywhere;}
#createPostBox #post-editorjs .ce-toolbar__plus {display:none!important;}
#createPostBox #post-editorjs .ce-inline-toolbar {z-index:45;overflow:visible;}
#createPostBox #post-editorjs .ce-popover {--border-radius:12px;--color-border:#e2e8f0;--color-background:#fff;--color-text-primary:#334155;--color-text-icon-active:#0a84ff;overflow:visible;}
#createPostBox .repost-modal-body:has(.ce-inline-toolbar .ce-popover--opened) {padding-bottom:60px;}
#createPostBox .repost-modal-body:has(.ce-inline-toolbar .ce-popover--nested.ce-popover--opened) {padding-bottom:230px;}
#createPostBox .composer-block-add {position:absolute;left:0;top:var(--composer-block-add-top,0px);z-index:4;width:26px;height:26px;display:grid;place-items:center;border:0;border-radius:50%;background:#f1f5f9;color:#64748b;padding:0;cursor:pointer;}
#createPostBox .composer-block-add:focus-visible {outline:2px solid #0a84ff;outline-offset:2px;}
#createPostBox .composer-block-add:disabled {opacity:.45;cursor:wait;}
#createPostBox .composer-block-add:hover,#createPostBox .composer-block-add[aria-expanded="true"] {background:#e2e8f0;color:#0a84ff;}
#createPostBox .composer-block-picker:not([hidden]) {position:absolute;z-index:1100;display:flex;flex-direction:column;gap:2px;width:214px;max-width:calc(100% - 24px);max-height:min(350px,calc(100% - 24px));overflow:auto;border:1px solid #e2e8f0;border-radius:12px;background:#fff;padding:6px;box-shadow:0 10px 28px rgba(15,23,42,.08);}
#createPostBox .composer-block-picker button {display:flex;align-items:center;gap:10px;border:0;border-radius:8px;background:transparent;padding:7px 9px;color:#334155;font-size:14px;cursor:pointer;text-align:left;flex:none;}
#createPostBox .composer-block-picker button:hover {background:#f1f5f9;}
#createPostBox .composer-block-picker button svg {width:18px;height:18px;flex:none;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;}
#createPostBox .repost-sr-only,#createPostBox .repost-switch-row input {position:absolute;width:1px;height:1px;clip-path:inset(50%);overflow:hidden;white-space:nowrap;display:block;padding:0;margin:0;}
#createPostBox .repost-switch-row input:focus-visible + .repost-switch-slider {outline:2px solid #2563eb;outline-offset:2px;}
#createPostBox .repost-settings-popover {max-width:calc(100vw - 64px);max-height:60dvh;overflow:auto;}
#createPostBox .repost-source-input {box-shadow:none;margin:0;}
#createPostBox .repost-publish-btn:disabled {opacity:.55;cursor:wait;}
#createPostBox .compose-previews {display:flex;flex-wrap:wrap;gap:8px;}
#createPostBox .compose-previews:not(:has(img,p,label)) {display:none;}
#createPostBox .compose-previews img {width:56px;height:56px;object-fit:cover;border-radius:8px;}
#createPostBox .post-composer-gallery-item {display:flex;gap:6px;align-items:center;font-size:12px;max-width:180px;overflow:hidden;}
#createPostBox [data-post-gallery-list] {gap:8px;margin:0;}
#createPostBox [data-post-gallery-list]:empty {display:none;}
#createPostBox [data-post-gallery-list] .post-composer-gallery-item {position:relative;padding:0;border:0;border-radius:9px;width:76px;height:76px;overflow:hidden;}
#createPostBox [data-post-gallery-list] .post-composer-gallery-item img {display:block;width:100%;height:100%;object-fit:cover;border-radius:inherit;}
#createPostBox .post-gallery-remove {position:absolute;right:4px;top:4px;display:grid;place-items:center;width:24px;height:24px;padding:0;border:0;border-radius:50%;background:rgba(15,23,42,.72);color:white;font:18px/1 Arial,sans-serif;cursor:pointer;}
#createPostBox .post-gallery-remove:hover {background:#dc2626;}
#createPostBox .editor-inline-gallery {display:grid;gap:8px;margin:8px 0;min-width:0;}
#createPostBox .editor-inline-gallery img {display:block;width:100%;max-height:280px;object-fit:contain;border-radius:10px;background:#f8fafc;}
#createPostBox .editor-inline-gallery-note {font-size:12px;color:#64748b;margin:2px 0;}
#createPostBox .editor-inline-image-tile {position:relative;min-width:0;}
#createPostBox .editor-inline-image-tile .post-gallery-remove {position:absolute;right:6px;top:6px;}
#createPostBox .post-create-simple__existing-media {display:flex;flex-wrap:wrap;gap:8px;}
#createPostBox [data-existing-image] {display:flex;align-items:center;}
#createPostBox .post-existing-image-tile {position:relative;width:76px;height:76px;border-radius:9px;overflow:hidden;}
#createPostBox .post-existing-image-tile img {display:block;width:100%;height:100%;object-fit:cover;border-radius:inherit;}
#createPostBox .post-existing-image-undo {display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:11px;color:#64748b;}
#createPostBox .post-existing-image-undo button {border:0;padding:4px;background:transparent;color:#2878f0;font:inherit;cursor:pointer;}
#createPostModal > .post-gallery-modal:not([hidden]) > .post-gallery-dialog {width:min(480px,calc(100vw - 24px))!important;min-width:0!important;max-height:calc(100dvh - 32px)!important;overflow-y:auto!important;}
/* Larger, readable gallery text on desktop and mobile. */
#createPostModal > .post-gallery-modal:not([hidden]) > .post-gallery-dialog {position:relative!important;padding:26px 18px 18px!important;}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-dialog h2 {
    display:flex!important;align-items:center!important;justify-content:center!important;
    min-height:36px!important;margin:0 0 16px!important;padding:0 48px!important;
    text-align:center!important;color:#202633!important;font:700 21px/1.25 Arial,sans-serif!important;
}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back {
    position:absolute!important;top:26px!important;left:18px!important;
    display:inline-flex!important;align-items:center!important;justify-content:center!important;
    width:36px!important;height:36px!important;margin:0!important;padding:0!important;
    border:0!important;border-radius:50%!important;background:#f0f3f7!important;color:#45546a!important;
    cursor:pointer!important;touch-action:manipulation;-webkit-tap-highlight-color:transparent;
    transition:background-color .15s ease,color .15s ease,transform .12s ease,box-shadow .12s ease;
}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back svg {
    display:block!important;width:23px!important;height:23px!important;fill:none!important;
    stroke:currentColor!important;stroke-width:2.3!important;stroke-linecap:round!important;
    stroke-linejoin:round!important;pointer-events:none;
}
@media (hover:hover) {
    #createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back:hover {background:#e6edf7!important;color:#2878f0!important;}
}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back:active {
    background:#dce8fa!important;color:#1769ff!important;transform:scale(.9)!important;
    box-shadow:inset 0 1px 4px rgba(25,50,85,.15);
}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back:focus-visible {outline:2px solid #2878f0!important;outline-offset:3px;}
@media (prefers-reduced-motion:reduce) {
    #createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back {transition:none!important;}
    #createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-back:active {transform:none!important;}
}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-dropzone {padding:18px 14px!important;gap:8px!important;}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-picture-icon {width:26px!important;height:26px!important;}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-dropzone strong {font-size:15px!important;line-height:1.45!important;}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-dropzone > span {font-size:14px!important;line-height:1.45!important;}
#createPostModal > .post-gallery-modal:not([hidden]) .post-gallery-upload {font-size:15px!important;min-height:40px!important;height:auto!important;padding:10px 14px!important;line-height:1.35!important;}
#createPostModal [data-gallery-picker] {display:none!important;}
#createPostModal .post-gallery-grid {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin:12px 0 0;}
#createPostModal .post-gallery-grid[hidden],#createPostModal .post-gallery-actions[hidden],#createPostModal [data-gallery-error][hidden] {display:none!important;}
#createPostModal .post-gallery-tile {position:relative;display:block;aspect-ratio:1;width:100%;min-width:0;padding:0;border:2px solid transparent;border-radius:11px;background:#eef2f7;overflow:hidden;cursor:pointer;}
#createPostModal .post-gallery-tile img {display:block;width:100%;height:100%;object-fit:cover;border-radius:8px;pointer-events:none;}
#createPostModal .post-gallery-tile[aria-pressed="true"] {border-color:#2878f0;}
#createPostModal .post-gallery-tile:focus-visible {outline:2px solid #2878f0;outline-offset:2px;}
#createPostModal .post-gallery-check {position:absolute;top:6px;right:6px;display:grid;place-items:center;width:23px;height:23px;border-radius:50%;border:1.5px solid #fff;background:rgba(15,23,42,.45);color:white;font:700 15px/1 Arial,sans-serif;box-shadow:0 1px 4px #0003;}
#createPostModal .post-gallery-tile[aria-pressed="true"] .post-gallery-check {background:#2878f0;}
#createPostModal .post-gallery-actions {display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;}
#createPostModal .post-gallery-actions [data-gallery-selected] {font-size:14px;color:#64748b!important;}
#createPostModal .post-gallery-apply {border:0;border-radius:22px;background:#2878f0;color:#fff;font-family:inherit;font-size:15px;font-weight:600;line-height:1.2;padding:11px 17px;cursor:pointer;white-space:nowrap;}
#createPostModal .post-gallery-apply:hover {background:#1769dc;}
#createPostModal [data-gallery-error] {margin:8px 0 0;color:#b91c1c;font-size:14px;}
@media(max-width:360px){#createPostModal .post-gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}

@media(max-width:640px){#createPostModal.repost-modal-backdrop{padding:12px!important;}#createPostBox :is(.repost-modal-topbar,.repost-modal-userbar,.repost-modal-body,.repost-modal-footer){padding-inline:16px;}#createPostBox .repost-toolbar{gap:10px;}#createPostBox .repost-settings-popover{max-width:calc(100vw - 58px);}}
@media(prefers-reduced-motion:reduce){#createPostBox{transition:none!important;}}

/* Compact search field only, for both Editor.js block and conversion menus. */
#createPostBox #post-editorjs .ce-popover .cdx-search-field {
    box-sizing:border-box!important;
    height:30px!important;min-height:30px!important;max-height:30px!important;
    padding:0 7px!important;gap:6px!important;align-items:center!important;
}
#createPostBox #post-editorjs .ce-popover .cdx-search-field__input {
    box-sizing:border-box!important;flex:1 1 0!important;
    width:100%!important;min-width:0!important;
    height:26px!important;min-height:0!important;max-height:26px!important;
    margin:0!important;padding:0!important;border:0!important;
    border-radius:0!important;background:transparent!important;
    box-shadow:none!important;outline:none!important;
    font-size:12px!important;line-height:26px!important;
}
#createPostBox #post-editorjs .ce-popover .cdx-search-field__icon {
    display:flex!important;align-items:center!important;justify-content:center!important;
    flex:0 0 16px!important;width:16px!important;height:16px!important;
    margin:0!important;
}
#createPostBox #post-editorjs .ce-popover .cdx-search-field__icon svg {
    width:16px!important;height:16px!important;
}

/* Channel selector: match Repost app.css Select2 dropdown, retaining single channel_id. */
#createPostBox .repost-channel-select {position:relative;min-width:0;max-width:100%;flex:0 1 auto;}
#createPostBox .repost-channel-select .repost-modal-cat {max-width:100%;}
#createPostBox .repost-channel-trigger {
    display:inline-flex;align-items:center;gap:5px;max-width:100%;
    min-height:26px;padding:2px 6px;margin:0;border:0;border-radius:6px;
    background:transparent;color:var(--color-gray,#64748b);
    font:400 13px/1.4 var(--font-primary,Inter,sans-serif);cursor:pointer;
}
#createPostBox .repost-channel-trigger span {overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
#createPostBox .repost-channel-trigger svg {flex:none;transition:transform .2s ease;}
#createPostBox .repost-channel-trigger[aria-expanded="true"] svg {transform:scaleY(-1);}
#createPostBox .repost-channel-trigger:hover,#createPostBox .repost-channel-trigger[aria-expanded="true"] {background:var(--gray-150,#eef0f5);color:var(--text-color,#334155);}
#createPostBox .repost-channel-menu {
    position:absolute;top:calc(100% + 6px);left:0;z-index:1150;
    width:min(280px,calc(100vw - 48px));max-width:280px;
    padding:10px;border:1px solid var(--border-color,#e2e8f0);border-radius:14px;
    background:var(--color-surface-overlay,#fff);box-shadow:var(--shadow-lg,0 12px 32px rgba(15,23,42,.12));
    color:var(--text-color,#334155);
}
#createPostBox .repost-channel-search {
    display:block;width:100%;height:38px!important;min-height:38px!important;
    margin:0 0 8px;padding:8px 12px!important;box-sizing:border-box;
    border:1px solid var(--border-color,#e2e8f0);border-radius:9px;
    background:var(--card-bg,#fff);color:var(--text-color,#334155);
    font:400 13px/20px var(--font-primary,Inter,sans-serif);outline:none;box-shadow:none;
}
#createPostBox .repost-channel-search:focus {border-color:var(--color-primary,#0d6efd);}
#createPostBox .repost-channel-options {display:flex;flex-direction:column;gap:3px;max-height:min(300px,35dvh);overflow-y:auto;scrollbar-width:thin;}
#createPostBox .repost-channel-option {
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    width:100%;min-height:34px;padding:8px 10px;margin:0;border:0;border-radius:10px;
    background:transparent;color:var(--text-color,#334155);text-align:left;
    font:500 13px/1.4 var(--font-secondary,Inter,sans-serif);cursor:pointer;
}
#createPostBox .repost-channel-option:hover,#createPostBox .repost-channel-option:focus-visible,#createPostBox .repost-channel-option[aria-pressed="true"] {
    background:var(--gray-100,#f7f9fd);color:var(--text-color,#334155);
}
#createPostBox .repost-channel-option[aria-pressed="true"]::after {content:"✓";color:var(--color-primary,#0d6efd);font-size:13px;}
#createPostBox .repost-channel-empty {margin:5px 0;text-align:center;font-size:12px;color:var(--color-gray,#64748b);}
        </style>
        <form class="repost-modal-box" id="createPostBox" @if(isset($quoteSource)) data-quote-composer @endif action="{{ isset($quoteSource) ? route('posts.reposts.toggle', $quoteSource) : (isset($post) ? route('posts.update', $post) : route('posts.store')) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if (isset($post))
        @method('PATCH')
    @endif

    <!-- 1. Üst Başlık Satırı -->
    <div class="repost-modal-topbar">
      <h2 class="repost-modal-heading">{{ isset($post) ? 'Gönderiyi düzenle' : 'Yeni gönderi' }}</h2>
      <div class="repost-modal-actions">
        <button type="button" class="repost-icon-btn" id="toggleExpandBtn" title="Büyüt / Küçült" aria-label="Büyüt / Küçült" aria-pressed="false">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line>
          </svg>
        </button>
        <button type="button" class="repost-icon-btn addpost__modal-close" id="closePostModalBtn" title="Kapat" aria-label="Kapat">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>

    <!-- 2. Kullanıcı + Kategori Seç Satırı -->
    <div class="repost-modal-userbar">
      <img src="{{ $user->avatarUrl() }}" alt="" class="repost-modal-avatar">
      <span class="repost-modal-username">{{ $user->displayName() }}</span>
      <span class="repost-modal-sep">›</span>
      <div class="repost-channel-select" data-repost-channel>
      <select class="repost-modal-cat" id="post-channel" name="channel_id" aria-label="Kategori seç">
                        <option value="">Kategori seç</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->id }}" @selected((string) old('channel_id', $post->channel_id ?? '') === (string) $channel->id)>{{ $channel->name }}</option>
                        @endforeach
                    </select>
      <button type="button" class="repost-channel-trigger" data-channel-trigger aria-label="Kategori seç" aria-expanded="false" aria-controls="repost-channel-menu" hidden>
        <span data-channel-label>Kategori seç</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 8 7 7 7-7"/></svg>
      </button>
      <div id="repost-channel-menu" class="repost-channel-menu" data-channel-menu hidden>
        <input type="search" class="repost-channel-search" data-channel-search placeholder="Kategori ara..." aria-label="Kategori ara" autocomplete="off">
        <div class="repost-channel-options" data-channel-options aria-label="Kategoriler"></div>
        <p class="repost-channel-empty" data-channel-empty hidden>Kategori bulunamadı.</p>
      </div>
    </div>
    </div>

    <!-- 3. Başlık ve Metin Alanı -->
    <div class="repost-modal-body">
      @if (isset($quoteSource) && $errors->any())
          <div class="color-red small" role="alert" data-quote-server-errors>
              @foreach ($errors->all() as $error)
                  <p>{{ $error }}</p>
              @endforeach
          </div>
      @endif
      <input type="text" class="repost-title-input" id="post-title" name="title" value="{{ old('title', $post->title ?? $sharedTitle ?? '') }}" maxlength="255" placeholder="Başlık" aria-label="Başlık" @if(!isset($quoteSource)) required @endif>
      <input type="hidden" id="post-body" name="body" value="{{ old('body', $post->body ?? $sharedBody ?? '') }}">
      <textarea id="post-excerpt" name="excerpt" hidden>{{ old('excerpt', $post->excerpt ?? $sharedExcerpt ?? '') }}</textarea>
      <div class="repost-editor-wrap">
        <span class="repost-sr-only" data-editor-status role="status" aria-live="polite"></span>
        <p data-editor-error role="alert" hidden></p>
        <button type="button" class="composer-block-add" data-block-picker-toggle aria-label="İçerik bloğu ekle" aria-expanded="false" aria-controls="composer-block-picker" title="Blok ekle"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></button>
        <div class="repost-content-textarea" id="post-editorjs" data-placeholder="Bir şeyler yazın..." aria-label="Gönderi içeriği"></div>
        <textarea class="repost-content-textarea" id="postTextarea" rows="4" placeholder="Bir şeyler yazın..." aria-label="Gönderi içeriği" hidden></textarea>
      </div>
      <div class="compose-previews">
                    <div class="post-composer-cover-preview" data-post-cover-preview aria-live="polite"></div>
                    <div class="post-composer-file-list" data-post-gallery-list aria-live="polite"></div>
                    @if (isset($post) && $post->coverImageUrl())
                        @php $coverRemoved = (bool) old('remove_cover', false); @endphp
                        <div data-existing-image>
                            <input type="hidden" name="remove_cover" value="1" data-existing-image-remove-input @disabled(! $coverRemoved)>
                            <div class="post-existing-image-tile" data-existing-image-tile @if ($coverRemoved) hidden @endif>
                                <img src="{{ $post->coverImageUrl() }}" alt="Mevcut kapak görseli">
                                <button type="button" class="post-gallery-remove" data-existing-image-remove aria-label="Kapak görselini kaldır" title="Kapak görselini kaldır">×</button>
                            </div>
                            <div class="post-existing-image-undo" data-existing-image-undo @unless ($coverRemoved) hidden @endunless>
                                <span>Kapak kaldırılacak.</span><button type="button" data-existing-image-restore>Geri al</button>
                            </div>
                        </div>
                    @endif
                    @if (isset($post) && filled($post->media))
                        <div class="post-create-simple__existing-media">
                            @foreach ($post->media as $mediaPath)
                                @php
                                    $mediaRemoved = in_array($mediaPath, (array) old('remove_media', []), true);
                                    $relativeMedia = ltrim($mediaPath, '/');
                                    $mediaUrl = filter_var($mediaPath, FILTER_VALIDATE_URL) ? $mediaPath : asset('storage/'.(str_starts_with($relativeMedia, 'storage/') ? substr($relativeMedia, 8) : $relativeMedia));
                                @endphp
                                <div data-existing-image>
                                    <input type="hidden" name="remove_media[]" value="{{ $mediaPath }}" data-existing-image-remove-input @disabled(! $mediaRemoved)>
                                    <div class="post-existing-image-tile" data-existing-image-tile @if ($mediaRemoved) hidden @endif>
                                        <img src="{{ $mediaUrl }}" alt="Mevcut gönderi görseli">
                                        <button type="button" class="post-gallery-remove" data-existing-image-remove aria-label="Görseli kaldır" title="Görseli kaldır">×</button>
                                    </div>
                                    <div class="post-existing-image-undo" data-existing-image-undo @unless ($mediaRemoved) hidden @endunless>
                                        <span>Görsel kaldırılacak.</span><button type="button" data-existing-image-restore>Geri al</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if (isset($post) && $post->video_path)
                        <p class="small color-gray">Mevcut video kayıtlı. Yeni video seçersen değiştirilir.</p>
                    @endif
                    </div>

        @if (isset($quoteSource))
            @include('posts._quoted-post', ['source' => $quoteSource])
        @elseif (isset($post) && $post->quoted_post_id)
            @include('posts._quoted-post', ['source' => $post->visibleQuotedPost()])
        @endif
    </div>

    <!-- 4. Alt Alan: Üstte 5 İkon, Altta Yayın Ayarları + Yayınla -->
    <div class="repost-modal-footer">
      <div class="repost-toolbar">
        <button type="button" class="repost-tool-btn" title="Görsel" aria-label="Görsel" data-gallery-open>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="16" rx="2.5"></rect><circle cx="8.5" cy="9" r="1.25" fill="currentColor" stroke="none"></circle><path stroke-linecap="round" stroke-linejoin="round" d="M4 17l5-4.5 3.5 3 3-2.5L20 17"></path></svg>
        </button>
        <button type="button" class="repost-tool-btn" title="Video" aria-label="Video" data-composer-video-toggle aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M10.25 9.5l4.5 2.5-4.5 2.5v-5z"></path></svg>
        </button>
        <button type="button" class="repost-tool-btn" title="Video dosyası yükle" aria-label="Video dosyası yükle" data-repost-file>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6M9 17h4"></path></svg>
        </button>
        <button type="button" class="repost-tool-btn" title="Etiket" aria-label="Etiket" data-composer-tags-toggle aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 4L7.5 20M16.5 4l-2 16M4.5 9h15M3.5 15h15"></path></svg>
        </button>
        <input class="repost-file-input" hidden id="post-media" name="media[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-post-gallery-input>
<input class="repost-file-input" hidden id="post-video-file" name="video_file" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-matroska">
<input class="repost-file-input" hidden id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-post-cover-input>
        <button type="button" data-composer-widget-toggle hidden aria-label="Widget ekle"></button>
      </div>
      <div class="repost-footer-bottom">
        <div class="repost-settings-wrap">
          <button type="button" class="repost-settings-trigger" id="postSettingsBtn" aria-expanded="false" aria-controls="postSettingsPopover">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="18" x2="20" y2="18"></line><circle cx="9" cy="6" r="2" fill="#ffffff"></circle><circle cx="15" cy="12" r="2" fill="#ffffff"></circle><circle cx="10" cy="18" r="2" fill="#ffffff"></circle></svg>
            <span>Yayın ayarları</span>
          </button>
          <div class="repost-settings-popover" id="postSettingsPopover">
            <label class="repost-switch-row">
              <input type="checkbox" id="post-adult" name="is_adult" value="1" @checked(old('is_adult', $post->is_adult ?? $quoteSource->is_adult ?? false))>
              <span class="repost-switch-slider"></span><span class="repost-switch-label">18+ içerik</span>
            </label>
            <label class="repost-switch-row">
              <input type="checkbox" id="post-ai-content" name="ai_content_disclosure" value="1" @checked(old('ai_content_disclosure', false))>
              <span class="repost-switch-slider"></span><span class="repost-switch-label">İçerik yapay zeka verileri içermektedir.</span>
            </label>
            <input type="hidden" name="show_toc" value="0">
            <label class="repost-switch-row">
              <input type="checkbox" id="post-show-toc" name="show_toc" value="1" @checked(old('show_toc', $post->show_toc ?? false))>
              <span class="repost-switch-slider" aria-hidden="true"></span><span class="repost-switch-label">İçindekiler göster</span>
            </label>
            <div class="repost-source-group">
              <label class="repost-source-label" for="post-source">Lütfen kaynağı belirtin.</label>
              <input type="text" id="post-source" name="content_source" class="repost-source-input" maxlength="1000" value="{{ old('content_source', $post->content_source ?? '') }}" placeholder="https://...">
            </div>
          </div>
        </div>
        <button type="submit" class="repost-publish-btn">{{ isset($post) ? 'Kaydet' : 'Yayınla' }}</button>
      </div>
    </div>
    <div id="composer-block-picker" class="composer-block-picker" data-block-picker hidden role="group" aria-label="İçerik blokları">
                <span data-block-picker-status role="status">Editör yükleniyor…</span>
                <button type="button" data-insert-block="paragraph" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v16M16 4v16M16 4H9a5 5 0 0 0 0 10h3"/></svg><span>Metin</span></button>
                <button type="button" data-insert-block="header" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5v14M19 5v14M5 12h14"/></svg><span>Başlık</span></button>
                <button type="button" data-insert-block="list" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4" cy="6" r=".7"/><circle cx="4" cy="12" r=".7"/><circle cx="4" cy="18" r=".7"/></svg><span>Liste</span></button>
                <button type="button" data-insert-block="list" data-list-style="checklist" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="16" height="16" rx="3"/><path d="m7 12 3 3 5-6"/></svg><span>Onay kutulu liste</span></button>
                <button type="button" data-insert-block="quote" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 7H4v7h6V7Zm0 7c0 3-2 4-4 5M20 7h-6v7h6V7Zm0 7c0 3-2 4-4 5"/></svg><span>Alıntı</span></button>
                <button type="button" data-insert-block="code" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 6-6 6 6 6m8-12 6 6-6 6m-2-15-4 18"/></svg><span>Kod</span></button>
                <button type="button" data-insert-block="delimiter" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h16"/></svg><span>Ayırıcı</span></button>
                <button type="button" data-insert-block="table" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M3 15h18M10 4v16"/></svg><span>Tablo</span></button>
                <button type="button" data-insert-block="embed" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a4 4 0 0 0 6 0l4-4a4 4 0 0 0-6-6l-2 2m2 6a4 4 0 0 0-6 0l-4 4a4 4 0 0 0 6 6l2-2"/></svg><span>Gömülü medya</span></button>
                <button type="button" data-block-action="gallery"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="9" r="1.5"/><path d="m3 17 5-5 4 4 4-5 5 6"/></svg><span>Görsel</span></button>
                <button type="button" data-block-action="video"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3z"/></svg><span>Video</span></button>
            <button type="button" data-block-action="widget"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m8 6-6 6 6 6m8-12 6 6-6 6"/></svg><span>Widget</span></button></div>
</form>
        <div class="post-gallery-modal" data-gallery-modal hidden>
                        <div class="post-gallery-dialog" role="dialog" aria-modal="true" aria-labelledby="post-gallery-title">
                            <button type="button" class="post-gallery-back" data-gallery-close aria-label="Geri dön">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <h2 id="post-gallery-title">Görseller</h2>
                            <div class="post-gallery-dropzone" data-gallery-dropzone>
                                <svg class="post-gallery-picture-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                <strong>Görselleri buraya sürükleyip bırakın.</strong>
                                <span>veya bunları cihazınızdan seçin</span>
                                <button type="button" class="post-gallery-upload" data-gallery-choose>Resimleri yükle</button>
                                <input type="file" data-gallery-picker accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden aria-label="Görsel seç">
                             </div>
                             <p data-gallery-error role="alert" hidden></p>
                             <div class="post-gallery-grid" data-gallery-grid role="group" aria-label="Gönderiye eklenecek görseller" hidden></div>
                             <div class="post-gallery-actions" data-gallery-actions hidden>
                                 <span class="post-gallery-selected" data-gallery-selected aria-live="polite"></span>
                                 <button type="button" class="post-gallery-apply" data-gallery-apply>Gönderiye ekle</button>
                             </div>
                        </div>
                    </div>
        <div class="post-widget-modal" data-widget-modal hidden>
            <div class="post-widget-dialog" role="dialog" aria-modal="true" aria-labelledby="post-widget-title">
                <button type="button" class="post-widget-back" data-widget-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-widget-title">Widget</h2>
                <textarea id="post-widget-code" maxlength="10000" placeholder="" aria-label="Widget kodu"></textarea>
                <p class="post-widget-hint">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                    <span>Buraya konu başlıkları, Twitter gönderileri, haritalar ve diğer widget kodlarını yapıştırabilirsiniz.</span>
                </p>
            </div>
        </div>
        <div class="post-tags-modal post-location-modal" data-location-modal hidden>
            <div class="post-tags-dialog post-location-dialog" role="dialog" aria-modal="true" aria-labelledby="post-location-title">
                <button type="button" class="post-tags-back" data-composer-location-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-location-title">Konum</h2>
                <div class="post-location-input-wrap">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21c-4-4.2-7-7.6-7-11a7 7 0 1114 0c0 3.4-3 6.8-7 11z"/><circle cx="12" cy="10" r="2.25"/></svg>
                    <input id="post-location" name="location" form="createPostBox" type="text" maxlength="255" value="{{ old('location', $post->location ?? '') }}" placeholder="Şehir, mekan veya adres" aria-label="Konum" autocomplete="off" enterkeyhint="done">
                    <button type="button" class="post-location-clear" data-location-clear aria-label="Konumu temizle" title="Konumu temizle">×</button>
                </div>
            </div>
        </div>
        <div class="post-tags-modal" data-tags-modal hidden>
            <div class="post-tags-dialog" role="dialog" aria-modal="true" aria-labelledby="post-tags-title">
                <button type="button" class="post-tags-back" data-tags-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-tags-title">Etiketler</h2>
                <div class="post-tags-input-wrap">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9h16M4 15h16M9 4l-2 16M17 4l-2 16"/></svg>
                    <input type="hidden" id="post-tags-value" name="tags" form="createPostBox" value="{{ old('tags', implode(', ', $post->tags ?? [])) }}">
                    <input id="post-tags-input" type="text" maxlength="100" enterkeyhint="done" placeholder="Etiketleri girin" aria-label="Etiketler" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="post-tag-suggestions" data-suggestions-url="{{ route('posts.tag-suggestions') }}">
                </div>
                <div class="post-tag-suggestions" data-tag-suggestions hidden>
                    <p class="post-tag-suggestions-title">Mevcut etiketler</p>
                    <div id="post-tag-suggestions" role="listbox" aria-label="Etiket önerileri"></div>
                </div>
                <p class="post-tag-suggestions-status" data-tag-suggestions-status role="status" aria-live="polite" hidden></p>
            </div>
        </div>
        <div class="post-video-modal" data-video-modal hidden>
            <div class="post-video-dialog" role="dialog" aria-modal="true" aria-labelledby="post-video-title">
                <button type="button" class="post-video-back" data-video-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-video-title">Video</h2>
                <input id="post-video-url" name="video_url" form="createPostBox" type="url"
                    value="{{ old('video_url', $post->video_url ?? '') }}" maxlength="2048"
                    placeholder="" aria-label="Video bağlantısı">
                <p class="post-video-hint">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                    <span>Video servislerinden bir video bağlantısı ekleyebilirsiniz.</span>
                </p>
            </div>
        </div>
    </div>




<style id="post-tags-modal-style">
.post-tags-modal[hidden] { display:none !important; }
.post-tags-modal.is-open {
    position:fixed !important;
    inset:0 !important;
    z-index:2147483647 !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    box-sizing:border-box !important;
    width:100vw !important;
    height:100vh !important;
    margin:0 !important;
    padding:12px !important;
    background:rgba(25,35,50,.48) !important;
    -webkit-backdrop-filter:blur(5px) !important;
    backdrop-filter:blur(5px) !important;
}
.post-tags-dialog {
    position:relative !important;
    left:auto !important;
    top:auto !important;
    display:block !important;
    flex:0 1 480px !important;
    box-sizing:border-box !important;
    width:min(480px, calc(100vw - 32px)) !important;
    min-width:0 !important;
    height:auto !important;
    min-height:143px !important;
    max-height:calc(100dvh - 32px) !important;
    overflow-y:auto !important;
    margin:0 !important;
    padding:26px 18px 24px !important;
    border:0 !important;
    border-radius:26px !important;
    background:#fff !important;
    box-shadow:0 20px 60px rgba(15,23,42,.2) !important;
    color:#202633 !important;
    font-family:Arial,sans-serif !important;
    transform:none !important;
}
.post-tags-dialog h2 {
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    min-height:36px !important;
    margin:0 0 16px !important;
    padding:0 48px !important;
    text-align:center !important;
    color:#202633 !important;
    font:700 21px/1.25 Arial,sans-serif !important;
}
.post-tags-dialog .post-tags-back {
    position:absolute !important;
    top:26px !important;
    left:18px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    width:36px !important;
    height:36px !important;
    margin:0 !important;
    padding:0 !important;
    border:0 !important;
    border-radius:50% !important;
    background:#f0f3f7 !important;
    color:#45546a !important;
    cursor:pointer !important;
    touch-action:manipulation;
    -webkit-tap-highlight-color:transparent;
    transition:background-color .15s ease,color .15s ease,transform .12s ease,box-shadow .12s ease;
}
.post-tags-dialog .post-tags-back svg {
    display:block !important;
    width:23px !important;
    height:23px !important;
    fill:none !important;
    stroke:currentColor !important;
    stroke-width:2.3 !important;
    stroke-linecap:round !important;
    stroke-linejoin:round !important;
    pointer-events:none;
}
@media (hover:hover) {
    .post-tags-dialog .post-tags-back:hover {background:#e6edf7!important;color:#2878f0!important;}
}
.post-tags-dialog .post-tags-back:active {
    background:#dce8fa!important;color:#1769ff!important;
    transform:scale(.9)!important;box-shadow:inset 0 1px 4px rgba(25,50,85,.15);
}
.post-tags-dialog .post-tags-back:focus-visible {outline:2px solid #2878f0!important;outline-offset:3px;}
@media (prefers-reduced-motion:reduce) {
    .post-tags-dialog .post-tags-back {transition:none!important;}
    .post-tags-dialog .post-tags-back:active {transform:none!important;}
}
.post-tags-input-wrap {
    display:flex !important;
    flex:0 0 44px !important;
    flex-wrap:nowrap !important;
    align-items:center !important;
    gap:10px !important;
    position:relative !important;
    box-sizing:border-box !important;
    width:100% !important;
    height:44px !important;
    min-height:44px !important;
    max-height:44px !important;
    margin:0 !important;
    padding:0 13px !important;
    overflow:hidden !important;
    border:1px solid transparent !important;
    border-radius:13px !important;
    background:#f7f8fb !important;
}
.post-tags-input-wrap > svg {
    display:block !important;
    position:static !important;
    flex:0 0 16px !important;
    width:16px !important;
    height:16px !important;
    margin:0 !important;
    padding:0 !important;
    fill:none !important;
    stroke:#71839d !important;
    stroke-width:1.8 !important;
    transform:none !important;
}
.post-tags-input-wrap input#post-tags-input {
    position:static !important;
    display:block !important;
    flex:1 1 0% !important;
    align-self:center !important;
    box-sizing:border-box !important;
    width:1% !important;
    min-width:0 !important;
    max-width:none !important;
    height:42px !important;
    min-height:42px !important;
    max-height:42px !important;
    margin:0 !important;
    padding:0 !important;
    border:0 !important;
    border-radius:0 !important;
    outline:0 !important;
    float:none !important;
    transform:none !important;
    background:transparent !important;
    box-shadow:none !important;
    color:#303944 !important;
    font:13px/42px Arial,sans-serif !important;
}
.post-tags-input-wrap:focus-within {
    border-color:#d5deea !important;
    background:#fff !important;
}
.post-tags-dialog .post-tags-input-wrap,
.post-tags-dialog .post-tags-input-wrap:focus-within {
    flex:0 0 auto!important;flex-wrap:wrap!important;gap:6px!important;
    height:auto!important;min-height:42px!important;max-height:none!important;
    padding:7px 14px!important;overflow:visible!important;
    border:0!important;border-radius:12px!important;background:#f7f8fa!important;
}
.post-tags-dialog .post-tags-input-wrap > svg {
    flex:0 0 16px!important;width:16px!important;height:16px!important;
    stroke:#8996a9!important;margin-right:6px!important;
}
.post-tags-dialog .post-tags-input-wrap:has(#post-tags-input:not(:placeholder-shown)) > svg {stroke:#2878f0!important;}
.post-tags-dialog .post-tags-input-wrap input#post-tags-input {
    flex:1 1 24px!important;width:24px!important;min-width:24px!important;
    height:28px!important;min-height:28px!important;max-height:28px!important;
    font:14px/28px Arial,sans-serif!important;
}
.post-tags-dialog .post-tags-input-wrap:not(.has-tags) input#post-tags-input {
    flex-basis:115px!important;min-width:100px!important;
}
.post-tags-dialog .post-tag-chip {
    display:inline-flex!important;align-items:center!important;gap:8px!important;
    flex:0 0 auto!important;min-width:0!important;max-width:100%!important;
    min-height:28px!important;box-sizing:border-box!important;
    padding:4px 7px 4px 11px!important;margin:0!important;
    border:0!important;border-radius:999px!important;background:#e9edf2!important;
    color:#30343b!important;font:14px/20px Arial,sans-serif!important;
}
.post-tags-dialog .post-tag-chip-label {min-width:0;overflow-wrap:anywhere;}
.post-tags-dialog .post-tag-chip-remove {
    display:inline-grid!important;place-items:center!important;flex:0 0 12px!important;
    width:12px!important;height:16px!important;min-width:0!important;min-height:0!important;
    padding:0!important;margin:0!important;border:0!important;border-radius:50%!important;
    background:transparent!important;color:#71839d!important;
    font:12px/1 Arial,sans-serif!important;cursor:pointer;
}
.post-tags-dialog .post-tag-chip-remove:hover {background:#d7e1ed!important;color:#303944!important;}
.post-tags-dialog .post-tag-chip-remove:focus-visible {outline:2px solid #2878f0;}
.post-tags-input-wrap input#post-tags-input::placeholder {
    color:#71839d !important;
    opacity:1 !important;
}
.post-tags-dialog .post-tag-suggestions[hidden],.post-tags-dialog .post-tag-suggestions-status[hidden]{display:none!important;}
.post-tags-dialog .post-tag-suggestions{margin-top:12px;}
.post-tags-dialog .post-tag-suggestions-title{margin:0 0 6px;padding:0 6px;color:#71839d;font:600 12px/1.5 Arial,sans-serif;}
.post-tags-dialog #post-tag-suggestions{display:flex;flex-direction:column;gap:2px;max-height:min(250px,40dvh);overflow-y:auto;}
.post-tags-dialog .post-tag-option{display:flex;align-items:center;gap:10px;width:100%;min-height:38px;margin:0;padding:8px 10px;border:0;border-radius:10px;background:transparent;color:#303944;font:14px/1.4 Arial,sans-serif;text-align:left;cursor:pointer;overflow-wrap:anywhere;}
.post-tags-dialog .post-tag-option:hover,.post-tags-dialog .post-tag-option[aria-selected="true"]{background:#edf4ff;color:#1769ff;}
.post-tags-dialog .post-tag-option-hash{color:#71839d;font-size:16px;flex:none;}
.post-tags-dialog .post-tag-suggestions-status{margin:10px 6px 0;color:#71839d;font:13px/1.4 Arial,sans-serif;}
.post-location-dialog .post-location-input-wrap {
    display:flex!important;align-items:center!important;gap:10px!important;
    box-sizing:border-box!important;width:100%!important;min-height:44px!important;
    padding:7px 14px!important;margin:0!important;border:0!important;border-radius:12px!important;
    background:#f7f8fa!important;
}
.post-location-dialog .post-location-input-wrap > svg {
    display:block!important;flex:0 0 19px!important;width:19px!important;height:19px!important;
    fill:none!important;stroke:#8996a9!important;stroke-width:1.8!important;stroke-linecap:round!important;stroke-linejoin:round!important;
}
.post-location-dialog .post-location-input-wrap:has(#post-location:not(:placeholder-shown)) > svg {stroke:#2878f0!important;}
.post-location-dialog input#post-location {
    display:block!important;flex:1 1 0%!important;min-width:0!important;width:1%!important;
    margin:0!important;padding:0!important;border:0!important;border-radius:0!important;outline:0!important;
    box-shadow:none!important;background:transparent!important;color:#303944!important;
    font:14px/30px Arial,sans-serif!important;
}
.post-location-dialog input#post-location::placeholder {color:#71839d!important;opacity:1!important;}
.post-location-dialog .post-location-clear {
    display:grid!important;place-items:center!important;flex:0 0 26px!important;width:26px!important;height:26px!important;
    padding:0!important;border:0!important;border-radius:50%!important;background:transparent!important;
    color:#71839d!important;font:20px/1 Arial,sans-serif!important;cursor:pointer;
}
.post-location-dialog .post-location-clear[hidden]{display:none!important;}
.post-location-dialog .post-location-clear:hover{background:#e8edf3!important;}
.post-location-dialog .post-location-clear:active{transform:scale(.9);}
</style>

<style id="post-widget-modal-style">
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal[hidden] { display:none !important; }
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal:not([hidden]) {
    position:fixed !important; inset:0 !important; z-index:2147483001 !important;
    display:flex !important; align-items:center !important; justify-content:center !important;
    box-sizing:border-box !important; width:100vw !important; height:100vh !important;
    margin:0 !important; padding:16px !important; background:rgba(25,35,50,.48) !important;
    -webkit-backdrop-filter:blur(5px) !important; backdrop-filter:blur(5px) !important;
}
.addpost.post-create-simple.post-create-modal-page.is-widget-open > form#post-create-form {
    display:none !important; visibility:hidden !important; pointer-events:none !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal:not([hidden]) > .post-widget-dialog {
    position:relative !important; display:block !important; flex:0 1 480px !important;
    box-sizing:border-box !important; width:min(480px, calc(100vw - 32px)) !important;
    min-width:0 !important; min-height:0 !important; margin:0 auto !important;
    padding:26px 18px 18px !important; border:0 !important; border-radius:26px !important;
    background:#fff !important; box-shadow:0 20px 60px rgba(15,23,42,.2) !important;
    color:#202633 !important; font-family:var(--font-primary, Arial, sans-serif) !important;
    transform:none !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal .post-widget-dialog h2 {
    display:block !important; margin:0 0 27px !important; padding:0 !important;
    text-align:center !important; color:#30343b !important;
    font:700 18px/1.25 var(--font-primary, Arial, sans-serif) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal .post-widget-back {
    position:absolute !important; top:27px !important; left:25px !important;
    display:inline-flex !important; align-items:center !important; justify-content:center !important;
    width:22px !important; height:22px !important; padding:0 !important;
    border:0 !important; background:transparent !important; color:#71839d !important; cursor:pointer !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal .post-widget-back svg {
    width:21px !important; height:21px !important; fill:none !important; stroke:currentColor !important;
    stroke-width:1.8 !important; stroke-linecap:round !important; stroke-linejoin:round !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal textarea#post-widget-code {
    display:block !important; box-sizing:border-box !important; width:100% !important;
    min-height:100px !important; height:100px !important; max-height:45vh !important;
    margin:0 !important; padding:12px !important; resize:vertical !important;
    border:1px solid #d5deea !important; border-radius:13px !important; outline:none !important;
    background:#fff !important; color:#303944 !important;
    font:13px/1.45 var(--font-primary, Arial, sans-serif) !important;
    box-shadow:0 2px 5px rgba(15,23,42,.04) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal textarea#post-widget-code:focus {
    border-color:#9bb9e8 !important; box-shadow:0 0 0 3px rgba(37,99,235,.08) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal .post-widget-hint {
    display:flex !important; align-items:flex-start !important; gap:9px !important;
    margin:15px 0 0 !important; color:#71839d !important;
    font:12px/1.55 var(--font-primary, Arial, sans-serif) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-widget-modal .post-widget-hint svg {
    display:block !important; flex:0 0 16px !important; width:16px !important; height:16px !important;
    margin-top:1px !important; fill:none !important; stroke:currentColor !important; stroke-width:1.8 !important;
}
</style>

<style id="post-video-modal-fix">
.addpost.post-create-simple.post-create-modal-page.is-video-open > form#post-create-form {
    display: none !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal[hidden] {
    display: none !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal:not([hidden]) {
    position: fixed !important;
    inset: 0 !important;
    z-index: 2147483000 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-sizing: border-box !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 16px !important;
    background: rgba(25,35,50,.48) !important;
    -webkit-backdrop-filter: blur(5px) !important;
    backdrop-filter: blur(5px) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal:not([hidden]) > .post-video-dialog {
    position: relative !important;
    inset: auto !important;
    display: block !important;
    flex: 0 1 480px !important;
    box-sizing: border-box !important;
    width: min(480px, calc(100vw - 32px)) !important;
    min-width: 0 !important;
    min-height: 0 !important;
    max-height: calc(100vh - 32px) !important;
    margin: 0 auto !important;
    padding: 26px 18px 18px !important;
    overflow: visible !important;
    border: 0 !important;
    border-radius: 26px !important;
    background: #fff !important;
    box-shadow: 0 20px 60px rgba(15,23,42,.2) !important;
    color: #202633 !important;
    font-family: var(--font-primary, Arial, sans-serif) !important;
    transform: none !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-dialog h2 {
    display:flex!important;align-items:center!important;justify-content:center!important;
    min-height:36px!important;margin:0 0 16px!important;padding:0 48px!important;
    text-align:center!important;color:#202633!important;font:700 21px/1.25 Arial,sans-serif!important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back {
    position:absolute!important;top:26px!important;left:18px!important;
    display:inline-flex!important;align-items:center!important;justify-content:center!important;
    width:36px!important;height:36px!important;margin:0!important;padding:0!important;
    border:0!important;border-radius:50%!important;background:#f0f3f7!important;color:#45546a!important;
    cursor:pointer!important;touch-action:manipulation;-webkit-tap-highlight-color:transparent;
    transition:background-color .15s ease,color .15s ease,transform .12s ease,box-shadow .12s ease;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back svg {
    display:block!important;width:23px!important;height:23px!important;fill:none!important;
    stroke:currentColor!important;stroke-width:2.3!important;stroke-linecap:round!important;
    stroke-linejoin:round!important;pointer-events:none;
}
@media (hover:hover) {
    .addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back:hover {background:#e6edf7!important;color:#2878f0!important;}
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back:active {
    background:#dce8fa!important;color:#1769ff!important;transform:scale(.9)!important;
    box-shadow:inset 0 1px 4px rgba(25,50,85,.15);
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back:focus-visible {outline:2px solid #2878f0!important;outline-offset:3px;}
@media (prefers-reduced-motion:reduce) {
    .addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back {transition:none!important;}
    .addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back:active {transform:none!important;}
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal input#post-video-url {
    display: block !important;
    box-sizing: border-box !important;
    width: 100% !important;
    height: 44px !important;
    margin: 0 !important;
    padding: 0 13px !important;
    border: 1px solid #d5deea !important;
    border-radius: 13px !important;
    background: #fff !important;
    color: #303944 !important;
    font: 13px/1.4 var(--font-primary, Arial, sans-serif) !important;
    box-shadow: 0 2px 5px rgba(15,23,42,.04) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-hint {
    display: flex !important;
    align-items: center !important;
    gap: 9px !important;
    margin: 15px 0 0 !important;
    color: #71839d !important;
    font: 12px/1.4 var(--font-primary, Arial, sans-serif) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-hint svg {
    display: block !important;
    flex: 0 0 16px !important;
    width: 16px !important;
    height: 16px !important;
    fill: none !important;
    stroke: currentColor !important;
    stroke-width: 1.8 !important;
}
</style>

<script>
(() => {
  const start = () => {
    const holder = document.getElementById('post-editorjs');
    const input = document.getElementById('post-body');
    const form = holder?.closest('form');
    const status = document.querySelector('[data-editor-status]');
    if (!holder || !input || !form || holder.dataset.editorStarted === '1') return;
    holder.dataset.editorStarted = '1';

    const blockToggle = form.querySelector('[data-block-picker-toggle]');
    const blockPicker = form.querySelector('[data-block-picker]');
    const blockStatus = blockPicker?.querySelector('[data-block-picker-status]');
    const blockOptions = Array.from(blockPicker?.querySelectorAll('[data-insert-block]') || []);
    let activeEditor = null;
    let insertionIndex = -1;
    let submitting = false;
    let disposed = false;
    const blockTracking = new AbortController();
    let blockResizeObserver = null;
    let blockMutationObserver = null;
    let blockFrame = 0;
    if (blockToggle) blockToggle.disabled = true;
    form.addEventListener('ografi:composer-close', () => {
      disposed = true;
      blockTracking.abort();
      blockResizeObserver?.disconnect();
      blockMutationObserver?.disconnect();
      if (blockFrame) cancelAnimationFrame(blockFrame);
      if (activeEditor) activeEditor.destroy();
      activeEditor = null;
    }, { once: true });

    // Original reference modal interactions, initialized when AJAX clones the dialog.
    const modalBackdrop = document.getElementById('createPostModal');
    const modalBox = document.getElementById('createPostBox');
    const textarea = document.getElementById('postTextarea');
    const expandBtn = document.getElementById('toggleExpandBtn');
    const closeBtn = document.getElementById('closePostModalBtn');
    const settingsBtn = document.getElementById('postSettingsBtn');
    const settingsPopover = document.getElementById('postSettingsPopover');
    const publishBtn = form.querySelector('[type="submit"]');
    if (publishBtn) publishBtn.disabled = true;
    textarea?.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = this.scrollHeight + 'px';
    });
    expandBtn?.addEventListener('click', () => {
      const expanded = modalBox.classList.toggle('is-expanded');
      expandBtn.setAttribute('aria-pressed', String(expanded));
    });
    settingsBtn?.addEventListener('click', e => {
      e.stopPropagation();
      settingsPopover.classList.toggle('is-open');
      settingsBtn.classList.toggle('is-active');
      settingsBtn.setAttribute('aria-expanded', String(settingsPopover.classList.contains('is-open')));
    });
    const closeSettings = () => {
      settingsPopover?.classList.remove('is-open');
      settingsBtn?.classList.remove('is-active');
      settingsBtn?.setAttribute('aria-expanded', 'false');
    };
    document.addEventListener('click', e => {
      if (form.isConnected && !settingsPopover.contains(e.target) && !settingsBtn.contains(e.target)) closeSettings();
    });
    form.addEventListener('keydown', e => {
      if (e.key === 'Escape' && settingsPopover.classList.contains('is-open')) {
        e.preventDefault(); e.stopPropagation(); closeSettings(); settingsBtn.focus();
      }
    });
    closeBtn?.addEventListener('click', () => {
      if (!modalBackdrop.dataset.liveComposer) window.location.href = @json(route('posts.index'));
      // The live modal is removed by the existing layout close handler.
    });
    form.querySelector('[data-repost-file]')?.addEventListener('click', () => form.querySelector('#post-video-file')?.click());

    const editorWrap = holder.closest('.repost-editor-wrap');
    const editorScroll = form.querySelector('.repost-modal-body');
    let trackedBlock = null;

    const editorBlocks = () => Array.from(holder.querySelectorAll('.codex-editor__redactor > .ce-block'));
    const findBlock = node => {
      const element = node?.nodeType === Node.ELEMENT_NODE ? node : node?.parentElement;
      const block = element?.closest('.ce-block');
      return block && holder.contains(block) ? block : null;
    };
    const caretBlock = () => findBlock(window.getSelection()?.focusNode);
    const visibleBounds = () => {
      const scrollRect = editorScroll?.getBoundingClientRect();
      return {
        top: Math.max(0, scrollRect?.top ?? 0),
        bottom: Math.min(window.innerHeight, scrollRect?.bottom ?? window.innerHeight)
      };
    };
    const positionBlockPicker = () => {
      if (!blockPicker || blockPicker.hidden || !blockToggle) return;
      const formRect = form.getBoundingClientRect();
      const toggleRect = blockToggle.getBoundingClientRect();
      const left = Math.max(12, Math.min(toggleRect.right - formRect.left + 7, form.clientWidth - blockPicker.offsetWidth - 12));
      const top = Math.max(12, Math.min(toggleRect.top - formRect.top, form.clientHeight - blockPicker.offsetHeight - 12));
      blockPicker.style.setProperty('left', left + 'px', 'important');
      blockPicker.style.setProperty('top', top + 'px', 'important');
    };
    const closeBlockPicker = (restoreFocus = false) => {
      if (blockPicker) blockPicker.hidden = true;
      blockToggle?.setAttribute('aria-expanded', 'false');
      if (restoreFocus) blockToggle?.focus();
    };
    const updateBlockToggle = () => {
      blockFrame = 0;
      if (!activeEditor || !blockToggle || !editorWrap || !form.isConnected || !holder.getClientRects().length) return;
      const blocks = editorBlocks();
      if (!blocks.length) return;
      const bounds = visibleBounds();
      const wrapRect = editorWrap.getBoundingClientRect();
      const isVisible = block => {
        if (!block || !blocks.includes(block)) return false;
        const rect = block.getBoundingClientRect();
        return rect.bottom > bounds.top && rect.top < bounds.bottom;
      };
      if (!isVisible(trackedBlock)) {
        if (blockPicker && !blockPicker.hidden) closeBlockPicker();
        const selected = caretBlock();
        trackedBlock = isVisible(selected) ? selected : blocks.find(isVisible) || null;
      }
      if (!trackedBlock || bounds.bottom - bounds.top < blockToggle.offsetHeight) {
        blockToggle.style.visibility = 'hidden';
        return;
      }
      const rect = trackedBlock.getBoundingClientRect();
      const preferredTop = rect.top + 2;
      const visibleTop = Math.min(
        Math.max(preferredTop, bounds.top + 2),
        bounds.bottom - blockToggle.offsetHeight - 2
      );
      const top = Math.max(0, Math.min(visibleTop - wrapRect.top, editorWrap.clientHeight - blockToggle.offsetHeight));
      blockToggle.style.setProperty('--composer-block-add-top', top + 'px');
      blockToggle.style.visibility = 'visible';
      if (!blockPicker?.hidden) positionBlockPicker();
    };
    const scheduleBlockToggle = () => {
      if (!blockFrame && !disposed) blockFrame = requestAnimationFrame(updateBlockToggle);
    };
    const trackBlock = node => {
      if (blockPicker && !blockPicker.hidden) return;
      const block = findBlock(node);
      if (!block) return;
      trackedBlock = block;
      scheduleBlockToggle();
    };
    const startBlockTracking = () => {
      if (!blockToggle) return;
      blockToggle.disabled = false;
      trackedBlock = caretBlock() || editorBlocks()[0] || null;
      const options = { signal: blockTracking.signal };
      holder.addEventListener('pointerover', event => {
        if (event.pointerType !== 'touch') trackBlock(event.target);
      }, options);
      holder.addEventListener('pointerdown', event => trackBlock(event.target), options);
      holder.addEventListener('focusin', event => trackBlock(event.target), options);
      document.addEventListener('selectionchange', () => {
        const selected = caretBlock();
        if (selected && holder.contains(document.activeElement)) trackBlock(selected);
      }, options);
      editorScroll?.addEventListener('scroll', scheduleBlockToggle, { ...options, passive: true });
      window.addEventListener('resize', scheduleBlockToggle, options);
      if (typeof ResizeObserver === 'function') {
        blockResizeObserver = new ResizeObserver(scheduleBlockToggle);
        blockResizeObserver.observe(holder);
        if (editorScroll) blockResizeObserver.observe(editorScroll);
      }
      if (typeof MutationObserver === 'function') {
        blockMutationObserver = new MutationObserver(scheduleBlockToggle);
        const redactor = holder.querySelector('.codex-editor__redactor');
        if (redactor) blockMutationObserver.observe(redactor, { childList: true, subtree: true });
      }
      scheduleBlockToggle();
    };

    // The moving plus belongs to the block next to it, not the previous text selection.
    const captureInsertionBlock = () => {
      const blocks = editorBlocks();
      const index = blocks.indexOf(trackedBlock);
      insertionIndex = index >= 0 ? index : (activeEditor?.blocks.getCurrentBlockIndex() ?? -1);
    };
    blockToggle?.addEventListener('pointerdown', event => {
      captureInsertionBlock();
      if (event.pointerType === 'mouse') event.preventDefault();
    });
    blockToggle?.addEventListener('click', () => {
      if (!blockPicker || !activeEditor) return;
      if (!blockPicker.hidden) { closeBlockPicker(); return; }
      captureInsertionBlock();
      blockPicker.hidden = false;
      blockToggle.setAttribute('aria-expanded', 'true');
      positionBlockPicker();
    });
    form.addEventListener('click', event => {
      if (!event.target.closest('[data-block-picker], [data-block-picker-toggle]')) closeBlockPicker();
    });
    form.addEventListener('keydown', event => {
      if (event.key === 'Escape' && blockPicker && !blockPicker.hidden) {
        event.preventDefault();
        event.stopPropagation();
        closeBlockPicker(true);
      }
    });
    blockOptions.forEach(option => option.addEventListener('click', () => {
      if (!activeEditor) return;
      const type = option.dataset.insertBlock;
      const count = activeEditor.blocks.getBlocksCount();
      const current = insertionIndex >= 0 && insertionIndex < count
        ? activeEditor.blocks.getBlockByIndex(insertionIndex) : null;
      const replaceEmpty = current?.name === 'paragraph' && current.isEmpty;
      const index = replaceEmpty ? insertionIndex : current ? insertionIndex + 1 : count;
      const data = type === 'header' ? { text: '', level: 2 } : type === 'list' && option.dataset.listStyle === 'checklist' ? { style: 'checklist', meta: {}, items: [] } : {};
      closeBlockPicker();
      const inserted = activeEditor.blocks.insert(type, data, {}, index, true, Boolean(replaceEmpty));
      if (type === 'embed') inserted?.holder?.querySelector('input')?.focus();
      else if (type !== 'delimiter') activeEditor.caret.setToBlock(index, 'start');
      trackedBlock = inserted?.holder || editorBlocks()[index] || trackedBlock;
      scheduleBlockToggle();
    }));

    blockPicker?.querySelectorAll('[data-block-action]').forEach(option => {
      option.addEventListener('click', () => {
        closeBlockPicker();
        const selector = option.dataset.blockAction === 'gallery' ? '[data-gallery-open]' : option.dataset.blockAction === 'widget' ? '[data-composer-widget-toggle]' : '[data-composer-video-toggle]';
        form.querySelector(selector)?.click();
      });
    });

    const fileKey = file => JSON.stringify([file.name, file.size, file.lastModified, file.type]);
    const gallerySources = () => [
      ...Array.from(form.querySelectorAll('[name="remove_media[]"]:disabled')).map(field => ({
        path: field.value, src: field.closest('[data-existing-image]')?.querySelector('img')?.getAttribute('src'),
        remove: field.closest('[data-existing-image]')?.querySelector('[data-existing-image-remove]')
      })),
      ...Array.from(form.querySelectorAll('[data-post-gallery-list] [data-gallery-file-key]')).map(item => ({
        uploadKey: item.dataset.galleryFileKey, src: item.querySelector('img')?.getAttribute('src'),
        remove: item.querySelector('button')
      }))
    ].filter(item => item.src);
    const imageKey = data => typeof data?.path === 'string' ? 'path:' + data.path
      : typeof data?.uploadKey === 'string' ? 'upload:' + data.uploadKey : '';
    const blockData = source => source.path !== undefined ? { path: source.path } : { uploadKey: source.uploadKey };
    let knownImages = new Set();

    class ComposerImageTool {
      static get isReadOnlySupported() { return true; }
      constructor({ data, readOnly }) { this.data = data || {}; this.readOnly = readOnly; }
      render() {
        const element = document.createElement('div');
        element.className = 'editor-inline-gallery';
        element.dataset.inlineImageKey = imageKey(this.data);
        const source = gallerySources().find(item => imageKey(item) === imageKey(this.data));
        if (source) {
          const tile = document.createElement('div');
          tile.className = 'editor-inline-image-tile';
          const image = document.createElement('img');
          image.src = source.src;
          image.alt = 'Gönderi görseli';
          tile.append(image);
          if (!this.readOnly) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'post-gallery-remove';
            button.textContent = '×';
            button.setAttribute('aria-label', 'Görseli kaldır');
            button.addEventListener('click', () => {
              gallerySources().find(item => imageKey(item) === imageKey(this.data))?.remove?.click();
            });
            tile.append(button);
          }
          element.append(tile);
        } else {
          element.textContent = 'Görsel artık mevcut değil.';
        }
        return element;
      }
      save() { return this.data; }
      validate(data) { return Boolean(imageKey(data)); }
    }

    const refreshInlineGallery = () => {
      if (!activeEditor || !form.isConnected) return;
      const sources = gallerySources();
      const keys = new Set(sources.map(imageKey));
      // Remove only the matching block when an attachment is removed.
      for (let i = activeEditor.blocks.getBlocksCount() - 1; i >= 0; i--) {
        const block = activeEditor.blocks.getBlockByIndex(i);
        if (block?.name !== 'postImage') continue;
        const key = block.holder?.querySelector('[data-inline-image-key]')?.dataset.inlineImageKey;
        if (!keys.has(key)) {
          activeEditor.blocks.delete(i);
          if (insertionIndex >= i) insertionIndex--;
        }
      }
      const added = sources.filter(source => !knownImages.has(imageKey(source)));
      knownImages = keys;
      if (!added.length) return;
      const count = activeEditor.blocks.getBlocksCount();
      const current = insertionIndex >= 0 && insertionIndex < count
        ? activeEditor.blocks.getBlockByIndex(insertionIndex) : null;
      const replaceEmpty = current?.name === 'paragraph' && current.isEmpty;
      const index = replaceEmpty ? insertionIndex : current ? insertionIndex + 1 : count;
      added.forEach((source, offset) => {
        activeEditor.blocks.insert('postImage', blockData(source), {}, index + offset, false, offset === 0 && Boolean(replaceEmpty));
      });
      const next = index + added.length;
      if (next === activeEditor.blocks.getBlocksCount() || activeEditor.blocks.getBlockByIndex(next)?.name !== 'paragraph') {
        activeEditor.blocks.insert('paragraph', {}, {}, next, false);
      }
      insertionIndex = next;
      requestAnimationFrame(() => {
        if (activeEditor && form.isConnected && !form.closest('.is-gallery-open')) {
          activeEditor.caret.setToBlock(next, 'start');
        }
      });
    };
    form.addEventListener('ografi:gallery-changed', refreshInlineGallery);
    form.querySelector('[data-gallery-open]')?.addEventListener('pointerdown', () => {
      const index = activeEditor?.blocks.getCurrentBlockIndex() ?? -1;
      if (index >= 0) insertionIndex = index;
    });

    // An editable URL block uses the existing safe server-side embed renderer.
    class ComposerEmbedTool {
      constructor({ data, readOnly }) { this.data = data || {}; this.readOnly = readOnly; }
      static get toolbox() { return { title: 'Gömülü medya', icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 7H7a5 5 0 0 0 0 10h3m4-10h3a5 5 0 0 1 0 10h-3M8 12h8"/></svg>' }; }
      static get isReadOnlySupported() { return true; }
      static get pasteConfig() { return { patterns: { media: /^https?:\/\/(?:[\w-]+\.)?(?:youtube\.com|youtu\.be|facebook\.com|x\.com|twitter\.com|instagram\.com|tiktok\.com|reddit\.com)\/.+$/i } }; }
      onPaste(event) { if (this.field) this.field.value = event.detail.data; }
      render() {
        const field = document.createElement('input');
        field.type = 'url';
        field.placeholder = 'YouTube, Instagram, X veya başka desteklenen medya bağlantısı';
        field.setAttribute('aria-label', 'Gömülü medya bağlantısı');
        field.value = this.data.source || '';
        field.readOnly = this.readOnly;
        field.style.cssText = 'display:block;width:100%;box-sizing:border-box;padding:9px 11px;border:1px solid #d5deea;border-radius:8px;background:transparent;color:inherit;font:inherit;';
        this.field = field;
        return field;
      }
      save() { return { ...this.data, source: this.field.value.trim() }; }
      validate(data) {
        try {
          const url = new URL(data.source);
          return ['https:', 'http:'].includes(url.protocol) && /^(?:[\w-]+\.)?(?:youtube\.com|youtu\.be|facebook\.com|x\.com|twitter\.com|instagram\.com|tiktok\.com|reddit\.com)$/i.test(url.hostname);
        } catch (_) { return false; }
      }
    }

    const errorField = form.querySelector('[data-editor-error]');
    const setStatus = (message, isError = false) => {
      if (status) status.textContent = message;
      if (errorField) {
        errorField.textContent = isError ? message : '';
        errorField.hidden = !isError;
      }
    };
    if (form.hasAttribute('data-quote-composer')) {
      form.addEventListener('invalid', event => {
        const field = event.target;
        setStatus(field.validationMessage || 'Lütfen gönderi alanlarını kontrol edin.', true);
        errorField?.scrollIntoView({block:'nearest'});
      }, true);
    }
    const escapeText = text => String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const plainTextBlocks = text => String(text).replace(/\r\n?/g, '\n').split(/\n{2,}/)
      .filter(part => part.trim()).map(part => ({
        type: 'paragraph', data: { text: escapeText(part).replace(/\n/g, '<br>') }
      }));
    const makeExcerpt = savedData => {
      const parts = [];
      const collectItems = items => {
        if (!Array.isArray(items)) return;
        items.forEach(item => {
          if (typeof item === 'string') parts.push(item);
          else if (item && typeof item === 'object') {
            if (typeof item.content === 'string') parts.push(item.content);
            collectItems(item.items);
          }
        });
      };
      for (const block of savedData.blocks || []) {
        for (const key of ['text', 'caption', 'code']) {
          if (typeof block.data?.[key] === 'string') parts.push(block.data[key]);
        }
        collectItems(block.data?.items);
        if (Array.isArray(block.data?.content)) {
          for (const row of block.data.content) if (Array.isArray(row)) parts.push(...row.filter(cell => typeof cell === 'string'));
        }
      }
      return parts.map(part => new DOMParser().parseFromString(part.replace(/<br\s*\/?\s*>/gi, ' '), 'text/html').body.textContent || '')
        .join(' ').replace(/\s+/g, ' ').trim().slice(0, 500);
    };
    const beginSubmit = () => {
      if (submitting) return false;
      submitting = true;
      if (publishBtn) {
        publishBtn.dataset.originalText = publishBtn.textContent;
        publishBtn.disabled = true;
        publishBtn.textContent = 'Kaydediliyor…';
      }
      return true;
    };
    const resetSubmit = () => {
      submitting = false;
      if (publishBtn) {
        publishBtn.disabled = false;
        publishBtn.textContent = publishBtn.dataset.originalText || publishBtn.textContent;
      }
    };
    const loadScript = (url, timeout = 12000) => new Promise((resolve, reject) => {
      const script = document.createElement('script');
      let settled = false;
      const finish = (error) => {
        if (settled) return;
        settled = true;
        clearTimeout(timer);
        error ? reject(error) : resolve();
      };
      const timer = setTimeout(() => {
        script.remove();
        finish(new Error('Zaman aşımı: ' + url));
      }, timeout);
      script.src = url;
      script.async = true;
      script.crossOrigin = 'anonymous';
      script.onload = () => finish();
      script.onerror = () => finish(new Error('Yüklenemedi: ' + url));
      document.head.appendChild(script);
    });
    const loadWithFallback = async path => {
      let lastError;
      for (const base of ['https://cdn.jsdelivr.net/npm/', 'https://unpkg.com/']) {
        try { await loadScript(base + path); return true; }
        catch (error) { lastError = error; }
      }
      throw lastError || new Error('Editör dosyası yüklenemedi.');
    };
    const loadOptionalTool = async path => {
      try { await loadWithFallback(path); return true; }
      catch (error) { console.warn('Editor.js aracı yüklenemedi:', path, error); return false; }
    };

    (async () => {
      try {
        setStatus('Editör yükleniyor…');
        let localTools = null;
        try {
          if (typeof window.ografiLoadEditorTools === 'function') localTools = await window.ografiLoadEditorTools();
        } catch (error) {
          console.warn('Yerel Editor.js dosyası yüklenemedi, CDN deneniyor:', error);
        }
        if (!localTools && !window.EditorJS) await loadWithFallback('@editorjs/editorjs@2.30.8');
        const Editor = localTools?.EditorJS || window.EditorJS;
        if (typeof Editor !== 'function') throw new Error('Editor.js başlatılamadı.');

        const optionalTools = [
          ['@editorjs/header@2.8.8', 'Header'],
          ['@editorjs/list@2.0.8', 'EditorjsList'],
          ['@editorjs/quote@2.7.6', 'Quote'],
          ['@editorjs/code@2.9.4', 'CodeTool'],
          ['@editorjs/delimiter@1.4.2', 'Delimiter'],
          ['@editorjs/table@2.4.5', 'Table'],
          ['@editorjs/inline-code@1.5.0', 'InlineCode'],
          ['@editorjs/marker@1.4.0', 'Marker']
        ];
        if (!localTools) await Promise.all(optionalTools.filter(([, name]) => !window[name]).map(([path]) => loadOptionalTool(path)));
        if (disposed || !form.isConnected) return;
        const available = localTools || window;

        const tools = {};
        if (available.Header) tools.header = { class: available.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } };
        if (available.EditorjsList) tools.list = { class: available.EditorjsList, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
        if (available.Quote) tools.quote = { class: available.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Alıntını yaz…', captionPlaceholder: 'Kaynak' } };
        if (available.CodeTool) tools.code = { class: available.CodeTool, config: { placeholder: 'Kodunu yaz…' } };
        if (available.Delimiter) tools.delimiter = available.Delimiter;
        if (available.Table) tools.table = { class: available.Table, inlineToolbar: true, config: { rows: 2, cols: 2 } };
        tools.embed = ComposerEmbedTool;
        tools.postImage = ComposerImageTool;
        if (available.InlineCode) tools.inlineCode = available.InlineCode;
        if (available.Marker) tools.marker = available.Marker;

        let initial = { blocks: [] };
        if (input.value.trim()) {
          try {
            const parsed = JSON.parse(input.value);
            initial = parsed && Array.isArray(parsed.blocks)
              ? parsed
              : { blocks: plainTextBlocks(input.value) };
          } catch (_) {
            initial = { blocks: plainTextBlocks(input.value) };
          }
        }
        // Upgrade the old grouped gallery to one independent block per attached image.
        const sources = gallerySources();
        const availableKeys = new Set(sources.map(imageKey));
        const placed = new Set(initial.blocks.filter(block => block?.type === 'postImage').map(block => imageKey(block.data)));
        const unplaced = sources.filter(source => !placed.has(imageKey(source)));
        let migrated = false;
        initial.blocks = initial.blocks.flatMap(block => {
          if (block?.type === 'postImage') return availableKeys.has(imageKey(block.data)) ? [block] : [];
          if (block?.type !== 'postGallery') return [block];
          if (migrated) return [];
          migrated = true;
          return unplaced.map(source => ({ type: 'postImage', data: blockData(source) }));
        });
        if (!migrated && unplaced.length) {
          const first = initial.blocks.findIndex(block => block.type === 'paragraph');
          initial.blocks.splice(first < 0 ? initial.blocks.length : first + 1, 0,
            ...unplaced.map(source => ({ type: 'postImage', data: blockData(source) })));
        }
        knownImages = availableKeys;
        // Never silently discard existing blocks when a required tool is unavailable.
        const unsupported = initial.blocks.filter(block => !block || (block.type !== 'paragraph' && !tools[block.type]));
        if (unsupported.length) throw new Error('Mevcut yazı için gerekli içerik araçları yüklenemedi.');
        initial = { ...initial, blocks: initial.blocks.filter(block => block.type !== 'paragraph' ||
          /[^\s\u00a0\u200b]/.test(new DOMParser().parseFromString(String(block.data?.text || ''), 'text/html').body.textContent || '')) };

        const editor = new Editor({
          holder,
          placeholder: 'Bir şeyler yazın...',
          minHeight: 84,
          autofocus: false,
          logLevel: 'ERROR',
          data: initial,
          tools,
          inlineToolbar: ['convertTo', 'link', 'bold', 'italic', ...(tools.inlineCode ? ['inlineCode'] : []), ...(tools.marker ? ['marker'] : [])],
          i18n: {
            messages: {
              ui: {
                popover: { Filter: 'Filtrele', 'Nothing found': 'Sonuç bulunamadı' }
              },
              toolNames: {
                Text: 'Metin', Heading: 'Başlık', List: 'Liste', Quote: 'Alıntı',
                'Ordered List': 'Numaralı liste', 'Unordered List': 'Madde işaretli liste',
                Checklist: 'Onay kutulu liste', Link: 'Bağlantı', Bold: 'Kalın', Italic: 'İtalik',
                'Inline Code': 'Satır içi kod', Marker: 'Vurgula'
              }
            }
          },
          onReady: () => {
            holder.classList.add('is-ready');
            setStatus('Editör hazır');
          }
        });

        await editor.isReady;
        if (disposed || !form.isConnected) {
          editor.destroy();
          return;
        }
        holder.classList.add('is-ready');
        activeEditor = editor;
        refreshInlineGallery();
        startBlockTracking();
        if (publishBtn) publishBtn.disabled = false;

        if (blockStatus) blockStatus.hidden = true;
        blockOptions.forEach(option => {
          const type = option.dataset.insertBlock;
          option.hidden = type !== 'paragraph' && !tools[type];
        });
        setStatus('Yazmaya başlayabilirsin');

        form.addEventListener('submit', async event => {
          event.preventDefault();
          if (!beginSubmit()) return;
          try {
            const savedData = await editor.save();
            const sources = gallerySources();
            const availableKeys = new Set(sources.map(imageKey));
            const seen = new Set();
            savedData.blocks = (savedData.blocks || []).filter(block => {
              if (block.type !== 'postImage') return true;
              const key = imageKey(block.data);
              if (!availableKeys.has(key) || seen.has(key)) return false;
              seen.add(key);
              return true;
            });
            // The editor order is authoritative, including native block deletion.
            const fileInput = form.querySelector('[data-post-gallery-input]');
            const files = Array.from(fileInput?.files || []).filter(file => seen.has('upload:' + fileKey(file)));
            const indices = new Map(files.map((file, index) => [fileKey(file), index]));
            for (const block of savedData.blocks) {
              if (block.type === 'postImage' && typeof block.data.uploadKey === 'string') {
                block.data = { uploadIndex: indices.get(block.data.uploadKey) };
              }
            }
            if (!savedData.blocks.length) {
              setStatus('Lütfen gönderi içeriğini yazın.', true);
              if (form.hasAttribute('data-quote-composer')) errorField?.scrollIntoView({block:'nearest'});
              resetSubmit();
              return;
            }
            const body = JSON.stringify(savedData);
            if (body.length > 100000) {
              setStatus('Yazı çok uzun. İçeriği kısaltıp tekrar deneyin.', true);
              resetSubmit();
              return;
            }
            if (fileInput && files.length !== fileInput.files.length) {
              const transfer = new DataTransfer();
              files.forEach(file => transfer.items.add(file));
              fileInput.files = transfer.files;
            }
            form.querySelectorAll('[name="remove_media[]"]').forEach(field => {
              field.disabled = seen.has('path:' + field.value);
            });
            input.value = body;
            const excerptField = form.querySelector('#post-excerpt');
            if (excerptField) excerptField.value = makeExcerpt(savedData) || form.querySelector('#post-title')?.value || 'Yeni yayın';
            HTMLFormElement.prototype.submit.call(form);
          } catch (error) {
            console.error('Editor.js kaydı başarısız:', error);
            setStatus('İçerik kaydedilemedi. Tekrar deneyin.', true);
            resetSubmit();
          }
        });
      } catch (error) {
        if (disposed || !form.isConnected) return;
        console.error('Editor.js başlatma hatası:', error);
        if (blockStatus) { blockStatus.hidden = false; blockStatus.textContent = 'Editör yüklenemedi. Sayfayı yenile.'; }
        setStatus('Editör yüklenemedi. Düz metin alanını kullanabilir veya sayfayı yenileyebilirsiniz.', true);
        // Keep the reference textarea usable if the Editor.js CDN is unavailable.
        holder.hidden = true;
        if (blockToggle) blockToggle.hidden = true;
        if (textarea) {
          textarea.hidden = false;
          let structuredBody = false;
          try {
            const data = JSON.parse(input.value);
            structuredBody = (data.blocks || []).some(b => b.type !== 'paragraph' || /<[^>]+>/.test(b.data?.text || ''));
            textarea.value = (data.blocks || []).map(b => new DOMParser().parseFromString(b.data?.text || '', 'text/html').body.textContent).join('\n\n');
          } catch (_) { textarea.value = input.value; }
          textarea.dispatchEvent(new Event('input'));
          // Preserve existing rich content if the editor failed to load.
          if (structuredBody) {
            textarea.readOnly = true;
            textarea.title = 'Biçimlendirilmiş içeriği düzenlemek için editörün yüklenmesi gerekir.';
            setStatus('Biçimlendirilmiş içeriği korumak için editör yüklenene kadar metin düzenlenemez. Sayfayı yenileyin.', true);
          }
          const originalText = textarea.value;
          form.addEventListener('submit', event => {
            if (!beginSubmit()) { event.preventDefault(); return; }
            if (!structuredBody && (textarea.value !== originalText || !input.value)) {
              const blocks = plainTextBlocks(textarea.value);
              if (!blocks.length) {
                event.preventDefault();
                setStatus('Lütfen gönderi içeriğini yazın.', true);
                resetSubmit();
                return;
              }
              input.value = JSON.stringify({ blocks });
              const excerpt = form.querySelector('#post-excerpt');
              if (excerpt) excerpt.value = makeExcerpt({ blocks }) || form.querySelector('#post-title')?.value || '';
            }
          });
        }
        if (publishBtn) publishBtn.disabled = false;
      }
    })();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
})();
</script>
<script>
(() => {
    const initComposerDetails = () => {
        // Initialize after AJAX insertion; the real select remains the submitted field.
        const channelWrap = document.querySelector('#createPostBox [data-repost-channel]');
        const channelSelect = channelWrap?.querySelector('#post-channel');
        const channelTrigger = channelWrap?.querySelector('[data-channel-trigger]');
        const channelMenu = channelWrap?.querySelector('[data-channel-menu]');
        const channelSearch = channelWrap?.querySelector('[data-channel-search]');
        const channelOptions = channelWrap?.querySelector('[data-channel-options]');
        const channelLabel = channelWrap?.querySelector('[data-channel-label]');
        const channelEmpty = channelWrap?.querySelector('[data-channel-empty]');
        if (channelSelect && channelTrigger && channelMenu && channelSearch && channelOptions && channelLabel && channelEmpty && !channelWrap.dataset.initialized) {
            channelWrap.dataset.initialized = '1';
            const syncChannel = () => {
                channelLabel.textContent = channelSelect.selectedOptions[0]?.textContent.trim() || 'Kategori seç';
                channelOptions.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.value === channelSelect.value)));
            };
            const closeChannel = (focus = false) => {
                channelMenu.hidden = true;
                channelTrigger.setAttribute('aria-expanded', 'false');
                if (focus) channelTrigger.focus();
            };
            for (const option of channelSelect.options) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'repost-channel-option';
                button.dataset.value = option.value;
                button.textContent = option.textContent.trim();
                button.disabled = option.disabled;
                button.addEventListener('click', () => {
                    channelSelect.value = option.value;
                    channelSelect.dispatchEvent(new Event('change', {bubbles:true}));
                    closeChannel(true);
                });
                channelOptions.appendChild(button);
            }
            const filterChannels = () => {
                const query = channelSearch.value.trim().toLocaleLowerCase('tr');
                let count = 0;
                channelOptions.querySelectorAll('button').forEach(button => {
                    button.hidden = !button.textContent.toLocaleLowerCase('tr').includes(query);
                    if (!button.hidden) count++;
                });
                channelEmpty.hidden = count > 0;
            };
            const openChannel = () => {
                channelSearch.value = '';
                filterChannels();
                channelMenu.hidden = false;
                channelTrigger.setAttribute('aria-expanded', 'true');
                channelMenu.style.left = '0px';
                const right = channelMenu.getBoundingClientRect().right;
                if (right > window.innerWidth - 12) channelMenu.style.left = -Math.max(0,right - window.innerWidth + 12) + 'px';
                channelSearch.focus();
            };
            channelSelect.addEventListener('change', syncChannel);
            channelSearch.addEventListener('input', filterChannels);
            channelTrigger.addEventListener('click', () => channelMenu.hidden ? openChannel() : closeChannel());
            channelWrap.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !channelMenu.hidden) {
                    event.preventDefault(); event.stopPropagation(); closeChannel(true);
                } else if (event.key === 'Enter' && event.target === channelSearch) {
                    event.preventDefault(); channelOptions.querySelector('button:not([hidden]):not([disabled])')?.click();
                } else if (['ArrowDown','ArrowUp'].includes(event.key)) {
                    event.preventDefault();
                    if (channelMenu.hidden) {openChannel(); return;}
                    const items = Array.from(channelOptions.querySelectorAll('button:not([hidden]):not([disabled])'));
                    const index = items.indexOf(document.activeElement);
                    const next = event.key === 'ArrowDown' ? Math.min(index+1,items.length-1) : Math.max(0,index-1);
                    items[next]?.focus();
                }
            });
            channelWrap.addEventListener('focusout', event => {
                if (!channelWrap.contains(event.relatedTarget)) closeChannel();
            });
            syncChannel();
            channelSelect.hidden = true;
            channelTrigger.hidden = false;
        }

        const title = document.getElementById('post-title');
        const excerpt = document.getElementById('post-excerpt');
        const meta = document.getElementById('post-meta-description');
        const previewTitle = document.querySelector('[data-seo-preview-title]');
        const previewDescription = document.querySelector('[data-seo-preview-description]');

        const updateCounter = input => {
            if (!input) return;
            const counter = document.querySelector('[data-count-for="' + input.id + '"]');
            if (counter) {
                const recommended = input.id === 'post-title' ? 60 : 160;
                counter.textContent = input.value.length + ' / ' + recommended + ' önerilen';
                counter.classList.toggle('is-over', input.value.length > recommended);
            }
        };
        const updateSeoPreview = () => {
            if (previewTitle) previewTitle.textContent = title?.value.trim() || 'Yazı başlığın burada görünecek';
            if (previewDescription) {
                previewDescription.textContent = meta?.value.trim() || excerpt?.value.trim() || 'Meta açıklaman veya kısa açıklaman burada görünecek.';
            }
        };
        [title, excerpt, meta].forEach(input => {
            if (!input) return;
            updateCounter(input);
            input.addEventListener('input', () => {
                updateCounter(input);
                updateSeoPreview();
            });
        });
        updateSeoPreview();

        const coverInput = document.querySelector('[data-post-cover-input]');
        const coverPreview = document.querySelector('[data-post-cover-preview]');
        if (coverInput && coverPreview) {
            coverInput.addEventListener('change', () => {
                coverPreview.replaceChildren();
                const file = coverInput.files?.[0];
                if (!file) return;
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = 'Seçilen kapak görseli';
                image.onload = () => URL.revokeObjectURL(image.src);
                const name = document.createElement('span');
                name.textContent = file.name;
                coverPreview.append(image, name);
            });
        }

        const composerOverlay = document.querySelector('.addpost.post-create-simple.post-create-modal-page');
        const composerForm = composerOverlay?.querySelector('#createPostBox');
        composerForm?.querySelectorAll('[data-existing-image]').forEach(item => {
            const input = item.querySelector('[data-existing-image-remove-input]');
            const tile = item.querySelector('[data-existing-image-tile]');
            const undo = item.querySelector('[data-existing-image-undo]');
            const remove = item.querySelector('[data-existing-image-remove]');
            const restore = item.querySelector('[data-existing-image-restore]');
            const setRemoved = removed => {
                input.disabled = !removed;
                tile.hidden = removed;
                undo.hidden = !removed;
                composerForm.dispatchEvent(new Event('ografi:gallery-changed'));
                (removed ? restore : remove)?.focus({ preventScroll: true });
            };
            remove?.addEventListener('click', () => setRemoved(true));
            restore?.addEventListener('click', () => setRemoved(false));
        });
        const galleryInput = composerForm?.querySelector('[data-post-gallery-input]');
        const galleryList = composerForm?.querySelector('[data-post-gallery-list]');
        const galleryModal = composerOverlay?.querySelector('[data-gallery-modal]');
        const galleryDropzone = galleryModal?.querySelector('[data-gallery-dropzone]');
        const galleryPicker = galleryModal?.querySelector('[data-gallery-picker]');
        const gallerySelected = galleryModal?.querySelector('[data-gallery-selected]');
        const galleryGrid = galleryModal?.querySelector('[data-gallery-grid]');
        const galleryActions = galleryModal?.querySelector('[data-gallery-actions]');
        const galleryApply = galleryModal?.querySelector('[data-gallery-apply]');
        const galleryError = galleryModal?.querySelector('[data-gallery-error]');
        const galleryOpen = composerForm?.querySelector('[data-gallery-open]');
        const galleryAbort = new AbortController();
        const allowedImages = new Set(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        let stagedGallery = [];
        const galleryUrls = new Map();
        const fileKey = file => JSON.stringify([file.name, file.size, file.lastModified, file.type]);
        const previewUrl = file => {
            const key = fileKey(file);
            if (!galleryUrls.has(key)) galleryUrls.set(key, URL.createObjectURL(file));
            return galleryUrls.get(key);
        };
        const attachedFiles = () => Array.from(galleryInput?.files || []);
        const pruneGalleryUrls = () => {
            const keep = new Set([...attachedFiles(), ...stagedGallery.map(entry => entry.file)].map(fileKey));
            for (const [key, url] of galleryUrls) {
                if (!keep.has(key)) { URL.revokeObjectURL(url); galleryUrls.delete(key); }
            }
        };
        const showGalleryError = message => {
            if (!galleryError) return;
            galleryError.textContent = message;
            galleryError.hidden = !message;
        };
        const updateSelection = () => {
            const count = stagedGallery.filter(entry => entry.selected).length;
            if (gallerySelected) gallerySelected.textContent = count + ' / ' + stagedGallery.length + ' görsel seçildi';
            if (galleryApply) galleryApply.textContent = count ? 'Gönderiye ekle (' + count + ')' : 'Seçimi uygula';
            if (galleryGrid) galleryGrid.hidden = !stagedGallery.length;
            if (galleryActions) galleryActions.hidden = !stagedGallery.length;
        };
        const renderGallery = () => {
            if (!galleryGrid) return;
            galleryGrid.replaceChildren();
            stagedGallery.forEach(entry => {
                const tile = document.createElement('button');
                tile.type = 'button';
                tile.className = 'post-gallery-tile';
                tile.setAttribute('aria-label', entry.file.name);
                tile.setAttribute('aria-pressed', String(entry.selected));
                tile.title = entry.file.name;
                const img = document.createElement('img');
                img.src = previewUrl(entry.file);
                img.alt = '';
                const check = document.createElement('span');
                check.className = 'post-gallery-check';
                check.setAttribute('aria-hidden', 'true');
                check.textContent = entry.selected ? '✓' : '';
                tile.append(img, check);
                tile.addEventListener('click', () => {
                    entry.selected = !entry.selected;
                    tile.setAttribute('aria-pressed', String(entry.selected));
                    check.textContent = entry.selected ? '✓' : '';
                    updateSelection();
                });
                galleryGrid.append(tile);
            });
            updateSelection();
        };
        const setAttachedFiles = files => {
            if (!galleryInput) return false;
            try {
                const transfer = new DataTransfer();
                files.forEach(file => transfer.items.add(file));
                galleryInput.files = transfer.files;
                galleryInput.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            } catch (error) {
                showGalleryError('Görseller gönderiye aktarılamadı. Tarayıcınızı güncelleyip tekrar deneyin.');
                console.error('Gallery file transfer failed:', error);
                return false;
            }
        };
        const showGalleryFiles = () => {
            if (!galleryList) return;
            galleryList.replaceChildren();
            attachedFiles().forEach(file => {
                const item = document.createElement('div');
                item.className = 'post-composer-gallery-item';
                item.dataset.galleryFileKey = fileKey(file);
                const image = document.createElement('img');
                image.src = previewUrl(file);
                image.alt = file.name;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'post-gallery-remove';
                remove.textContent = '×';
                remove.setAttribute('aria-label', file.name + ' görselini kaldır');
                remove.addEventListener('click', () => {
                    if (!setAttachedFiles(attachedFiles().filter(attached => fileKey(attached) !== fileKey(file)))) {
                        window.alert('Görsel kaldırılamadı. Lütfen tekrar deneyin.');
                    }
                });
                item.append(image, remove);
                galleryList.append(item);
            });
            pruneGalleryUrls();
            composerForm?.dispatchEvent(new Event('ografi:gallery-changed'));
        };
        const addGalleryCandidates = files => {
            let skipped = false;
            const known = new Set(stagedGallery.map(entry => fileKey(entry.file)));
            for (const file of files) {
                if (known.has(fileKey(file))) continue;
                if (!allowedImages.has(file.type) || file.size > 5 * 1024 * 1024 || file.size === 0 || stagedGallery.length >= 10) {
                    skipped = true;
                    continue;
                }
                stagedGallery.push({ file, selected: true });
                known.add(fileKey(file));
            }
            showGalleryError(skipped ? 'En fazla 10 görsel seçebilirsiniz. JPG, PNG, GIF veya WEBP; her biri en fazla 5 MB olmalıdır.' : '');
            renderGallery();
        };
        const openGallery = () => {
            if (!galleryModal) return;
            stagedGallery = attachedFiles().map(file => ({ file, selected: true }));
            showGalleryError('');
            renderGallery();
            galleryModal.hidden = false;
            composerOverlay.classList.add('is-gallery-open');
            composerForm.setAttribute('aria-hidden', 'true');
            galleryModal.querySelector('[data-gallery-choose]')?.focus({ preventScroll: true });
        };
        const closeGallery = () => {
            if (!galleryModal) return;
            galleryModal.hidden = true;
            composerOverlay.classList.remove('is-gallery-open');
            composerForm.removeAttribute('aria-hidden');
            stagedGallery = [];
            galleryGrid?.replaceChildren();
            pruneGalleryUrls();
            galleryOpen?.focus({ preventScroll: true });
        };
        galleryOpen?.addEventListener('click', openGallery);
        galleryModal?.querySelectorAll('[data-gallery-close]').forEach(button => button.addEventListener('click', closeGallery));
        galleryModal?.querySelector('[data-gallery-choose]')?.addEventListener('click', () => galleryPicker?.click());
        galleryPicker?.addEventListener('change', () => {
            addGalleryCandidates(Array.from(galleryPicker.files || []));
            galleryPicker.value = '';
        });
        galleryApply?.addEventListener('click', () => {
            if (setAttachedFiles(stagedGallery.filter(entry => entry.selected).map(entry => entry.file))) {
                closeGallery();
            }
        });
        galleryModal?.addEventListener('click', event => { if (event.target === galleryModal) closeGallery(); });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && galleryModal?.isConnected && !galleryModal.hidden) {
                event.preventDefault();
                event.stopImmediatePropagation();
                closeGallery();
            }
        }, { capture: true, signal: galleryAbort.signal });
        composerForm?.addEventListener('ografi:composer-close', () => {
            galleryAbort.abort();
            for (const url of galleryUrls.values()) URL.revokeObjectURL(url);
            galleryUrls.clear();
        }, { once: true });
        galleryInput?.addEventListener('change', showGalleryFiles);
        showGalleryFiles();
        ['dragenter', 'dragover'].forEach(type => galleryDropzone?.addEventListener(type, event => {
            event.preventDefault(); galleryDropzone.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach(type => galleryDropzone?.addEventListener(type, event => {
            event.preventDefault(); galleryDropzone.classList.remove('is-dragging');
        }));
        galleryDropzone?.addEventListener('drop', event => {
            addGalleryCandidates(Array.from(event.dataTransfer?.files || []));
        });

        const locationToggle = document.querySelector('[data-composer-location-toggle]');
        const locationModal = composerOverlay?.querySelector('[data-location-modal]');
        const locationClose = locationModal?.querySelector('[data-composer-location-close]');
        const locationInput = locationModal?.querySelector('#post-location');
        const locationClear = locationModal?.querySelector('[data-location-clear]');
        const locationPlaceholder = document.createComment('location-modal-position');
        locationModal?.parentNode?.insertBefore(locationPlaceholder, locationModal);
        let previousLocationDisplay = '';
        const videoToggle = document.querySelector('[data-composer-video-toggle]');
        const videoModal = document.querySelector('[data-video-modal]');
        const videoInput = document.getElementById('post-video-url');
        const widgetToggle = document.querySelector('[data-composer-widget-toggle]');
        const widgetModal = document.querySelector('[data-widget-modal]');
        const widgetInput = document.getElementById('post-widget-code');
        const tagsToggle = document.querySelector('[data-composer-tags-toggle]');
        const tagsModal = document.querySelector('[data-tags-modal]');
        const tagsInput = document.getElementById('post-tags-input');
        const syncLocation = () => {
            const hasValue = Boolean(locationInput?.value.trim());
            if (locationClear) locationClear.hidden = !hasValue;
            locationToggle?.classList.toggle('is-active', hasValue);
        };
        const closeLocationModal = (restoreFocus = true) => {
            if (!locationModal || locationModal.hidden) return;
            locationModal.hidden = true;
            locationModal.classList.remove('is-open');
            if (locationPlaceholder.parentNode) locationPlaceholder.parentNode.insertBefore(locationModal, locationPlaceholder);
            if (composerOverlay) {
                composerOverlay.style.removeProperty('display');
                if (previousLocationDisplay) composerOverlay.style.display = previousLocationDisplay;
            }
            composerForm?.removeAttribute('aria-hidden');
            locationToggle?.setAttribute('aria-expanded', 'false');
            syncLocation();
            if (restoreFocus) locationToggle?.focus({ preventScroll: true });
        };
        const closeComposerPanels = () => {
            closeLocationModal(false);
            videoToggle?.setAttribute('aria-expanded', 'false');
        };
        const openLocationModal = () => {
            if (!locationModal || !locationInput || !composerOverlay) return;
            closeComposerPanels();
            previousLocationDisplay = composerOverlay.style.display || '';
            document.body.appendChild(locationModal);
            locationModal.hidden = false;
            locationModal.classList.add('is-open');
            composerOverlay.style.setProperty('display', 'none', 'important');
            composerForm?.setAttribute('aria-hidden', 'true');
            locationToggle?.setAttribute('aria-expanded', 'true');
            syncLocation();
            locationInput.focus({ preventScroll: true });
        };
        locationToggle?.addEventListener('click', openLocationModal);
        locationClose?.addEventListener('click', () => closeLocationModal());
        locationClear?.addEventListener('click', () => {
            if (locationInput) locationInput.value = '';
            syncLocation();
            locationInput?.focus({ preventScroll: true });
        });
        locationInput?.addEventListener('input', syncLocation);
        locationModal?.addEventListener('click', event => {
            if (event.target === locationModal) closeLocationModal();
        });
        locationModal?.addEventListener('keydown', event => {
            if (event.isComposing) return;
            if (event.key === 'Escape' || (event.key === 'Enter' && event.target === locationInput)) {
                event.preventDefault();
                event.stopPropagation();
                closeLocationModal();
            } else if (event.key === 'Tab') {
                const controls = [locationClose, locationInput, locationClear].filter(control => control && !control.hidden);
                const index = controls.indexOf(document.activeElement);
                event.preventDefault();
                event.stopPropagation();
                controls[(index + (event.shiftKey ? -1 : 1) + controls.length) % controls.length]?.focus();
            }
        });
        composerForm?.addEventListener('ografi:composer-close', () => locationModal?.remove(), { once: true });
        syncLocation();
        const openVideoModal = () => {
            if (!videoModal) return;
            closeComposerPanels();
            videoModal.hidden = false;
            composerOverlay?.classList.add('is-video-open');
            composerForm?.setAttribute('aria-hidden', 'true');
            videoToggle?.setAttribute('aria-expanded', 'true');
            window.setTimeout(() => videoInput?.focus(), 0);
        };
        const closeVideoModal = () => {
            if (videoModal) videoModal.hidden = true;
            composerOverlay?.classList.remove('is-video-open');
            composerForm?.removeAttribute('aria-hidden');
            videoToggle?.setAttribute('aria-expanded', 'false');
        };
        videoToggle?.addEventListener('click', openVideoModal);
        document.querySelectorAll('[data-video-close]').forEach(button => button.addEventListener('click', closeVideoModal));
        videoModal?.addEventListener('click', event => { if (event.target === videoModal) closeVideoModal(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && videoModal && !videoModal.hidden) closeVideoModal(); });
        const widgetModalPlaceholder = widgetModal ? document.createComment('widget-modal-position') : null;
        const widgetOriginalParent = widgetModal?.parentNode || null;
        if (widgetModal && widgetOriginalParent && widgetModalPlaceholder) {
            widgetOriginalParent.insertBefore(widgetModalPlaceholder, widgetModal);
        }
        const previousComposerDisplay = composerForm?.style.display || '';
        const openWidgetModal = () => {
            if (!widgetModal || !widgetModalPlaceholder) return;
            closeComposerPanels();

            // Move the dialog to body so transformed composer wrappers cannot offset fixed positioning.
            if (widgetModal.parentNode !== document.body) document.body.appendChild(widgetModal);
            widgetModal.hidden = false;
            widgetModal.style.cssText = [
                'position:fixed', 'inset:0', 'z-index:2147483647',
                'display:flex', 'align-items:center', 'justify-content:center',
                'box-sizing:border-box', 'width:100vw', 'height:100vh',
                'margin:0', 'padding:12px',
                'background:rgba(25,35,50,.48)',
                'backdrop-filter:blur(5px)', '-webkit-backdrop-filter:blur(5px)'
            ].join(';') + ';';

            const dialog = widgetModal.querySelector('.post-widget-dialog');
            if (dialog) dialog.style.cssText = [
                'position:relative', 'display:block', 'box-sizing:border-box',
                'flex:0 1 480px', 'width:min(480px, calc(100vw - 24px))',
                'min-width:0', 'min-height:260px', 'margin:0 auto',
                'padding:26px 18px 20px', 'border:0', 'border-radius:26px',
                'background:#fff', 'box-shadow:0 20px 60px rgba(15,23,42,.2)',
                'color:#202633', 'font-family:Arial,sans-serif', 'transform:none'
            ].join(';') + ';';

            const title = widgetModal.querySelector('#post-widget-title');
            if (title) title.style.cssText = 'display:block;margin:0 0 27px;padding:0;text-align:center;color:#30343b;font:700 18px/1.25 Arial,sans-serif;';
            const back = widgetModal.querySelector('.post-widget-back');
            if (back) back.style.cssText = 'position:absolute;top:27px;left:25px;display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;padding:0;border:0;background:transparent;color:#71839d;cursor:pointer;';
            if (widgetInput) widgetInput.style.cssText = 'display:block;box-sizing:border-box;width:100%;height:100px;min-height:100px;max-height:45vh;margin:0;padding:12px;resize:vertical;border:1px solid #d5deea;border-radius:13px;outline:none;background:#fff;color:#303944;font:13px/1.45 Arial,sans-serif;box-shadow:0 2px 5px rgba(15,23,42,.04);';
            const hint = widgetModal.querySelector('.post-widget-hint');
            if (hint) hint.style.cssText = 'display:flex;align-items:flex-start;gap:9px;margin:15px 0 0;color:#71839d;font:12px/1.55 Arial,sans-serif;';
            const hintIcon = hint?.querySelector('svg');
            if (hintIcon) hintIcon.style.cssText = 'display:block;flex:0 0 16px;width:16px;height:16px;margin-top:1px;fill:none;stroke:currentColor;stroke-width:1.8;';

            if (composerForm) {
                composerForm.style.setProperty('display', 'none', 'important');
                composerForm.setAttribute('aria-hidden', 'true');
            }
            composerOverlay?.classList.add('is-widget-open');
            widgetToggle?.setAttribute('aria-expanded', 'true');
            window.setTimeout(() => widgetInput?.focus(), 0);
        };
        const closeWidgetModal = () => {
            if (!widgetModal) return;
            widgetModal.hidden = true;
            widgetModal.style.cssText = 'display:none !important;';
            if (widgetOriginalParent && widgetModalPlaceholder?.parentNode === widgetOriginalParent) {
                widgetOriginalParent.insertBefore(widgetModal, widgetModalPlaceholder);
            }
            if (composerForm) {
                composerForm.style.display = previousComposerDisplay;
                composerForm.removeAttribute('aria-hidden');
            }
            composerOverlay?.classList.remove('is-widget-open');
            widgetToggle?.setAttribute('aria-expanded', 'false');
        };
        widgetToggle?.addEventListener('click', openWidgetModal);
        document.querySelectorAll('[data-widget-close]').forEach(button => button.addEventListener('click', closeWidgetModal));
        widgetModal?.addEventListener('click', event => { if (event.target === widgetModal) closeWidgetModal(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && widgetModal && !widgetModal.hidden) closeWidgetModal(); });
        const suggestionsPanel = tagsModal?.querySelector('[data-tag-suggestions]');
        const suggestionsList = tagsModal?.querySelector('#post-tag-suggestions');
        const suggestionsStatus = tagsModal?.querySelector('[data-tag-suggestions-status]');
        let tagTimer = null;
        let tagRequest = null;
        let tagOptions = [];
        let activeTag = -1;
        const tagsValue = tagsModal?.querySelector('#post-tags-value');
        const tagsWrap = tagsInput?.closest('.post-tags-input-wrap');
        const cleanTag = name => String(name).trim().replace(/^#+/, '').trim();
        const normalizeTag = name => cleanTag(name).toLocaleLowerCase('tr-TR').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ı/g, 'i');
        const splitTags = value => String(value).split(/\s*,\s*|\s+(?=#)|[\r\n]+/u).map(cleanTag).filter(Boolean);
        let selectedTags = splitTags(tagsValue?.value || '').filter((name, index, all) =>
            all.findIndex(other => normalizeTag(other) === normalizeTag(name)) === index);
        const currentTag = () => ({ query: cleanTag(tagsInput?.value || '') });
        const syncTagValue = () => {
            if (tagsValue) tagsValue.value = selectedTags.join(', ');
        };
        const renderTagChips = () => {
            if (!tagsWrap || !tagsInput) return;
            tagsWrap.querySelectorAll('.post-tag-chip').forEach(chip => chip.remove());
            tagsWrap.classList.toggle('has-tags', selectedTags.length > 0);
            tagsInput.placeholder = selectedTags.length ? ' ' : 'Etiketleri girin';
            selectedTags.forEach((name, index) => {
                const chip = document.createElement('span');
                chip.className = 'post-tag-chip';
                const label = document.createElement('span');
                label.className = 'post-tag-chip-label';
                label.textContent = name;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'post-tag-chip-remove';
                remove.textContent = '×';
                remove.setAttribute('aria-label', name + ' etiketini kaldır');
                remove.addEventListener('click', () => {
                    selectedTags.splice(index, 1);
                    syncTagValue();
                    renderTagChips();
                    tagsInput.focus({ preventScroll: true });
                    searchTags();
                });
                chip.append(label, remove);
                tagsWrap.insertBefore(chip, tagsInput);
            });
            syncTagValue();
        };
        const setTagStatus = message => {
            if (!suggestionsStatus) return;
            suggestionsStatus.textContent = message;
            suggestionsStatus.hidden = !message;
        };
        const clearTagSuggestions = () => {
            tagOptions = [];
            activeTag = -1;
            suggestionsList?.replaceChildren();
            if (suggestionsPanel) suggestionsPanel.hidden = true;
            tagsInput?.setAttribute('aria-expanded', 'false');
            tagsInput?.removeAttribute('aria-activedescendant');
            setTagStatus('');
        };
        const cancelTagSearch = () => {
            clearTimeout(tagTimer);
            tagRequest?.abort();
            tagRequest = null;
        };
        const chooseTag = (name, focus = true) => {
            if (!tagsInput) return true;
            const names = splitTags(name);
            const next = [...selectedTags];
            for (const tag of names) {
                if (next.some(other => normalizeTag(other) === normalizeTag(tag))) continue;
                if (Array.from(tag).length > 100) {
                    setTagStatus('Bir etiket en fazla 100 karakter olabilir.');
                    return false;
                }
                next.push(tag);
            }
            if (next.length > 20 || Array.from(next.join(', ')).length > 500) {
                setTagStatus('En fazla 20 etiket ve toplam 500 karakter ekleyebilirsiniz.');
                return false;
            }
            selectedTags = next;
            tagsInput.value = '';
            renderTagChips();
            cancelTagSearch();
            clearTagSuggestions();
            if (focus) tagsInput.focus({ preventScroll: true });
            return true;
        };
        const highlightTag = index => {
            activeTag = index;
            Array.from(suggestionsList?.children || []).forEach((option, i) => {
                option.setAttribute('aria-selected', String(i === activeTag));
            });
            const option = suggestionsList?.children[activeTag];
            if (option) {
                tagsInput.setAttribute('aria-activedescendant', option.id);
                option.scrollIntoView({ block: 'nearest' });
            } else tagsInput?.removeAttribute('aria-activedescendant');
        };
        const renderTagSuggestions = tags => {
            clearTagSuggestions();
            if (!suggestionsList || !tagsInput) return;
            const used = new Set(selectedTags.map(normalizeTag));
            tagOptions = tags.filter(tag => typeof tag.name === 'string' && !used.has(normalizeTag(tag.name))).slice(0, 8);
            if (!tagOptions.length) {
                setTagStatus('Mevcut etiket bulunamadı. Yeni etiket yazabilirsiniz.');
                return;
            }
            tagOptions.forEach((tag, index) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.id = 'post-tag-option-' + index;
                option.className = 'post-tag-option';
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.tabIndex = -1;
                const hash = document.createElement('span');
                hash.className = 'post-tag-option-hash';
                hash.textContent = '#';
                const label = document.createElement('span');
                label.textContent = tag.name;
                option.append(hash, label);
                option.addEventListener('pointerdown', event => event.preventDefault());
                option.addEventListener('click', () => chooseTag(tag.name));
                suggestionsList.append(option);
            });
            suggestionsPanel.hidden = false;
            tagsInput.setAttribute('aria-expanded', 'true');
        };
        const searchTags = () => {
            cancelTagSearch();
            clearTagSuggestions();
            const query = currentTag().query;
            if (!query || !tagsInput?.dataset.suggestionsUrl || tagsModal?.hidden) return;
            tagTimer = setTimeout(async () => {
                const controller = new AbortController();
                tagRequest = controller;
                try {
                    const url = new URL(tagsInput.dataset.suggestionsUrl, window.location.origin);
                    url.searchParams.set('q', query.slice(0, 100));
                    const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Tag suggestions HTTP ' + response.status);
                    const tags = await response.json();
                    if (!controller.signal.aborted && !tagsModal.hidden && currentTag().query === query && Array.isArray(tags)) renderTagSuggestions(tags);
                } catch (error) {
                    if (!controller.signal.aborted) setTagStatus('Öneriler yüklenemedi. Etiketi yazarak ekleyebilirsiniz.');
                }
            }, 220);
        };
        renderTagChips();
        tagsInput?.addEventListener('input', event => {
            if (!event.isComposing && tagsInput.value.includes(',')) {
                const last = tagsInput.value.lastIndexOf(',');
                const pending = tagsInput.value.slice(last + 1);
                if (chooseTag(tagsInput.value.slice(0, last))) tagsInput.value = pending;
            }
            searchTags();
        });
        tagsInput?.addEventListener('click', searchTags);
        tagsInput?.addEventListener('compositionend', searchTags);
        tagsInput?.addEventListener('paste', event => {
            const text = event.clipboardData?.getData('text') || '';
            if (!/[,\r\n]|\s+#/.test(text)) return;
            event.preventDefault();
            const start = tagsInput.selectionStart ?? tagsInput.value.length;
            const end = tagsInput.selectionEnd ?? start;
            chooseTag(tagsInput.value.slice(0, start) + text + tagsInput.value.slice(end));
        });
        tagsInput?.addEventListener('keydown', event => {
            if (event.isComposing) return;
            if (event.key === 'ArrowDown' && tagOptions.length) {
                event.preventDefault();
                highlightTag((activeTag + 1) % tagOptions.length);
            } else if (event.key === 'ArrowUp' && tagOptions.length) {
                event.preventDefault();
                highlightTag(activeTag <= 0 ? tagOptions.length - 1 : activeTag - 1);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (activeTag >= 0 && tagOptions[activeTag]) chooseTag(tagOptions[activeTag].name);
                else if (currentTag().query) chooseTag(currentTag().query);
            } else if (event.key === ',') {
                event.preventDefault();
                if (currentTag().query) chooseTag(currentTag().query);
            } else if (event.key === 'Backspace' && !tagsInput.value && selectedTags.length) {
                event.preventDefault();
                selectedTags.pop();
                renderTagChips();
                cancelTagSearch();
                clearTagSuggestions();
            } else if (event.key === 'Escape' && tagOptions.length) {
                event.preventDefault();
                event.stopPropagation();
                cancelTagSearch();
                clearTagSuggestions();
            }
        });
        composerForm?.addEventListener('ografi:composer-close', () => {
            cancelTagSearch();
            tagsModal?.remove();
        }, { once: true });
        const tagsModalPlaceholder = tagsModal ? document.createComment('tags-modal-position') : null;
        const tagsModalOriginalParent = tagsModal?.parentNode || null;
        if (tagsModal && tagsModalOriginalParent && tagsModalPlaceholder) {
            tagsModalOriginalParent.insertBefore(tagsModalPlaceholder, tagsModal);
        }
        const previousComposerRootDisplay = composerOverlay?.style.display || '';
        const openTagsModal = () => {
            if (!tagsModal || !tagsModalPlaceholder) return;
            closeComposerPanels();

            // Portal the dialog to body. Never leave it inside the transformed composer wrapper.
            if (tagsModal.parentNode !== document.body) document.body.appendChild(tagsModal);
            tagsModal.hidden = false;
            tagsModal.removeAttribute('hidden');
            tagsModal.style.cssText = [
                'position:fixed', 'inset:0', 'z-index:2147483647',
                'display:flex', 'align-items:center', 'justify-content:center',
                'box-sizing:border-box', 'width:100vw', 'height:100vh',
                'margin:0', 'padding:12px',
                'background:rgba(25,35,50,.48)',
                'backdrop-filter:blur(5px)', '-webkit-backdrop-filter:blur(5px)'
            ].join(';') + ';';

            const dialog = tagsModal.querySelector('.post-tags-dialog');
            if (dialog) dialog.style.cssText = [
                'position:relative', 'inset:auto', 'left:auto', 'top:auto',
                'display:block', 'flex:0 1 480px', 'box-sizing:border-box',
                'width:min(480px, calc(100vw - 32px))',
                'min-width:0', 'height:auto', 'min-height:143px', 'max-height:calc(100dvh - 32px)', 'overflow-y:auto',
                'margin:0', 'padding:26px 18px 24px',
                'border:0', 'border-radius:26px', 'background:#fff',
                'box-shadow:0 20px 60px rgba(15,23,42,.2)',
                'color:#202633', 'font-family:Arial,sans-serif', 'transform:none'
            ].join(';') + ';';

            const title = tagsModal.querySelector('#post-tags-title');
            if (title) title.style.cssText = 'display:block;margin:0 0 27px;padding:0;text-align:center;color:#30343b;font:700 18px/1.25 Arial,sans-serif;';
            const back = tagsModal.querySelector('.post-tags-back');
            if (back) back.style.cssText = 'position:absolute;top:27px;left:25px;display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;margin:0;padding:0;border:0;background:transparent;color:#71839d;cursor:pointer;';
            const backSvg = back?.querySelector('svg');
            if (backSvg) backSvg.style.cssText = 'display:block;width:21px;height:21px;fill:none;stroke:#71839d;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;';
            const wrap = tagsModal.querySelector('.post-tags-input-wrap');
            if (wrap) wrap.style.cssText = 'display:flex;flex:0 0 44px;flex-wrap:nowrap;align-items:center;gap:10px;position:relative;box-sizing:border-box;width:100%;height:44px;min-height:44px;max-height:44px;margin:0;padding:0 13px;overflow:hidden;border:1px solid transparent;border-radius:13px;background:#f7f8fb;';
            const hash = wrap?.querySelector('svg');
            if (hash) hash.style.cssText = 'display:block;position:static;flex:0 0 16px;width:16px;height:16px;margin:0;padding:0;fill:none;stroke:#71839d;stroke-width:1.8;transform:none;';
            if (tagsInput) tagsInput.style.cssText = 'position:static;display:block;flex:1 1 0%;align-self:center;box-sizing:border-box;width:1%;min-width:0;max-width:none;height:42px;min-height:42px;max-height:42px;margin:0;padding:0;border:0;border-radius:0;outline:0;float:none;transform:none;background:transparent;box-shadow:none;color:#303944;font:13px/42px Arial,sans-serif;';

            // Hide the entire old composer layer; the page itself remains visible behind this modal.
            if (composerOverlay) composerOverlay.style.setProperty('display', 'none', 'important');
            composerForm?.setAttribute('aria-hidden', 'true');
            composerOverlay?.classList.add('is-tags-open');
            tagsToggle?.setAttribute('aria-expanded', 'true');
            window.setTimeout(() => tagsInput?.focus(), 0);
            searchTags();
        };
        const closeTagsModal = () => {
            if (currentTag().query && !chooseTag(currentTag().query, false)) return;
            cancelTagSearch();
            clearTagSuggestions();
            if (tagsModal) {
                tagsModal.hidden = true;
                tagsModal.style.cssText = 'display:none !important;';
            }
            if (tagsModal && tagsModalPlaceholder?.parentNode) {
                tagsModalPlaceholder.parentNode.insertBefore(tagsModal, tagsModalPlaceholder);
            }
            if (composerOverlay) {
                composerOverlay.style.removeProperty('display');
                if (previousComposerRootDisplay) composerOverlay.style.display = previousComposerRootDisplay;
                composerOverlay.classList.remove('is-tags-open');
            }
            composerForm?.removeAttribute('aria-hidden');
            tagsToggle?.setAttribute('aria-expanded', 'false');
        };
        tagsToggle?.addEventListener('click', openTagsModal);
        document.querySelectorAll('[data-tags-close]').forEach(button => button.addEventListener('click', closeTagsModal));
        tagsModal?.addEventListener('click', event => { if (event.target === tagsModal) closeTagsModal(); });
        tagsModal?.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !tagsModal.hidden) {
                event.preventDefault();
                event.stopPropagation();
                closeTagsModal();
                tagsToggle?.focus();
            }
        });

        // Editor initialization owns the submit state and the final content serialization.
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initComposerDetails, { once: true });
    } else {
        initComposerDetails();
    }
})();
</script>
@endsection
