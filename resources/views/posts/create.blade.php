@extends('layouts.app')

@section('title', isset($post) ? 'Yazıyı düzenle' : 'Yazı oluştur')

@section('content')
    @unless(isset($post))
        <script src="{{ asset('assets/js/pwa-file-launch.js') }}" defer></script>
    @endunless
    @if ($errors->any())
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
#createPostBox #post-editorjs [contenteditable="true"] {outline:0;overflow-wrap:anywhere;}
#createPostBox #post-editorjs .ce-toolbar__plus {display:none!important;}
#createPostBox #post-editorjs .ce-inline-toolbar {z-index:45;overflow:visible;}
#createPostBox #post-editorjs .ce-popover {--border-radius:12px;--color-border:#e2e8f0;--color-background:#fff;--color-text-primary:#334155;--color-text-icon-active:#0a84ff;overflow:visible;}
#createPostBox .repost-modal-body:has(.ce-inline-toolbar .ce-popover--opened) {padding-bottom:60px;}
#createPostBox .repost-modal-body:has(.ce-inline-toolbar .ce-popover--nested.ce-popover--opened) {padding-bottom:230px;}
#createPostBox .composer-block-add {position:absolute;left:0;top:0;width:26px;height:26px;display:grid;place-items:center;border:0;border-radius:50%;background:#f1f5f9;color:#64748b;padding:0;cursor:pointer;}
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
#createPostBox .post-composer-location-panel:not([hidden]) {display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
#createPostBox .post-composer-location-panel input {min-width:0;flex:1;border:1px solid #cbd5e1;border-radius:12px;padding:8px;background:#fff;color:#334155;}
#createPostBox .post-composer-location-panel button {border:0;background:transparent;color:#64748b;font-size:20px;cursor:pointer;}
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
        <form class="repost-modal-box" id="createPostBox" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
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
      <input type="text" class="repost-title-input" id="post-title" name="title" value="{{ old('title', $post->title ?? $sharedTitle ?? '') }}" maxlength="255" placeholder="Başlık" aria-label="Başlık" required>
      <input type="hidden" id="post-body" name="body" value="{{ old('body', $post->body ?? $sharedBody ?? '') }}">
      <textarea id="post-excerpt" name="excerpt" hidden>{{ old('excerpt', $post->excerpt ?? $sharedExcerpt ?? '') }}</textarea>
      <div class="repost-editor-wrap">
        <span class="repost-sr-only" data-editor-status role="status" aria-live="polite"></span>
        <button type="button" class="composer-block-add" data-block-picker-toggle aria-label="İçerik bloğu ekle" aria-expanded="false" aria-controls="composer-block-picker" title="Blok ekle"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></button>
        <div class="repost-content-textarea" id="post-editorjs" data-placeholder="Bir şeyler yazın..." aria-label="Gönderi içeriği"></div>
        <textarea class="repost-content-textarea" id="postTextarea" rows="4" placeholder="Bir şeyler yazın..." aria-label="Gönderi içeriği" hidden></textarea>
      </div>
      <div class="compose-previews">
                    <div class="post-composer-cover-preview" data-post-cover-preview aria-live="polite"></div>
                    <div class="post-composer-file-list" data-post-gallery-list aria-live="polite"></div>
                    @if (isset($post) && $post->coverImageUrl())
                        <div class="post-composer-existing-cover">
                            <img src="{{ $post->coverImageUrl() }}" alt="Mevcut kapak görseli">
                            <label class="small"><input type="checkbox" name="remove_cover" value="1"> Kapağı kaldır</label>
                        </div>
                    @endif
                    @if (isset($post) && filled($post->media))
                        <div class="post-create-simple__existing-media">
                            @foreach ($post->media as $mediaPath)
                                <label><img src="{{ asset('storage/'.$mediaPath) }}" alt=""><input type="checkbox" name="remove_media[]" value="{{ $mediaPath }}"> Kaldır</label>
                            @endforeach
                        </div>
                    @endif
                    @if (isset($post) && $post->video_path)
                        <p class="small color-gray">Mevcut video kayıtlı. Yeni video seçersen değiştirilir.</p>
                    @endif
                    </div>
      <div class="post-composer-location-panel" data-composer-location-panel hidden>
                        <label for="post-location">Konum</label>
                        <input id="post-location" name="location" type="text" maxlength="255" value="{{ old('location', $post->location ?? '') }}" placeholder="Şehir, mekan veya adres">
                        <button type="button" data-composer-location-close aria-label="Konum alanını kapat">×</button>
                    </div>
    </div>

    <!-- 4. Alt Alan: Üstte 5 İkon, Altta Yayın Ayarları + Yayınla -->
    <div class="repost-modal-footer">
      <div class="repost-toolbar">
        <button type="button" class="repost-tool-btn" title="Görsel" aria-label="Görsel" data-gallery-open>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="16" rx="2.5"></rect><circle cx="8.5" cy="9" r="1.25" fill="currentColor" stroke="none"></circle><path stroke-linecap="round" stroke-linejoin="round" d="M4 17l5-4.5 3.5 3 3-2.5L20 17"></path></svg>
        </button>
        <button type="button" class="repost-tool-btn" title="Konum" aria-label="Konum" data-composer-location-toggle aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4-4.2-7-7.6-7-11a7 7 0 1114 0c0 3.4-3 6.8-7 11z"></path><circle cx="12" cy="10" r="2.25"></circle></svg>
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
              <input type="checkbox" id="post-adult" name="is_adult" value="1" @checked(old('is_adult', $post->is_adult ?? false))>
              <span class="repost-switch-slider"></span><span class="repost-switch-label">18+ içerik</span>
            </label>
            <label class="repost-switch-row">
              <input type="checkbox" id="post-ai-content" name="ai_content_disclosure" value="1" @checked(old('ai_content_disclosure', false))>
              <span class="repost-switch-slider"></span><span class="repost-switch-label">İçerik yapay zeka verileri içermektedir.</span>
            </label>
            <div class="repost-source-group">
              <label class="repost-source-label" for="post-source">Lütfen kaynağı belirtin.</label>
              <input type="text" id="post-source" name="content_source" class="repost-source-input" maxlength="1000" value="{{ old('content_source', '') }}">
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
                                <div class="post-gallery-selected" data-gallery-selected aria-live="polite"></div>
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
        <div class="post-tags-modal" data-tags-modal hidden>
            <div class="post-tags-dialog" role="dialog" aria-modal="true" aria-labelledby="post-tags-title">
                <button type="button" class="post-tags-back" data-tags-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-tags-title">Etiketler</h2>
                <div class="post-tags-input-wrap">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9h16M4 15h16M9 4l-2 16M17 4l-2 16"/></svg>
                    <input id="post-tags-input" name="tags" form="createPostBox" type="text" maxlength="500" value="{{ old('tags', implode(', ', $post->tags ?? [])) }}" placeholder="Etiketleri girin" aria-label="Etiketler">
                </div>
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
    height:143px !important;
    min-height:143px !important;
    max-height:143px !important;
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
    display:block !important;
    margin:0 0 27px !important;
    padding:0 !important;
    text-align:center !important;
    color:#30343b !important;
    font:700 18px/1.25 Arial,sans-serif !important;
}
.post-tags-back {
    position:absolute !important;
    top:27px !important;
    left:25px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    width:22px !important;
    height:22px !important;
    margin:0 !important;
    padding:0 !important;
    border:0 !important;
    background:transparent !important;
    color:#71839d !important;
    cursor:pointer !important;
}
.post-tags-back svg {
    display:block !important;
    width:21px !important;
    height:21px !important;
    fill:none !important;
    stroke:currentColor !important;
    stroke-width:1.8 !important;
    stroke-linecap:round !important;
    stroke-linejoin:round !important;
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
.post-tags-input-wrap input#post-tags-input::placeholder {
    color:#71839d !important;
    opacity:1 !important;
}
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
    display: block !important;
    margin: 0 0 27px !important;
    padding: 0 !important;
    text-align: center !important;
    color: #30343b !important;
    font: 700 18px/1.25 var(--font-primary, Arial, sans-serif) !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back {
    position: absolute !important;
    top: 27px !important;
    left: 25px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 22px !important;
    height: 22px !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    color: #71839d !important;
    cursor: pointer !important;
}
.addpost.post-create-simple.post-create-modal-page > .post-video-modal .post-video-back svg {
    width: 21px !important;
    height: 21px !important;
    fill: none !important;
    stroke: currentColor !important;
    stroke-width: 1.8 !important;
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

    const closeBlockPicker = (restoreFocus = false) => {
      if (blockPicker) blockPicker.hidden = true;
      blockToggle?.setAttribute('aria-expanded', 'false');
      if (restoreFocus) blockToggle?.focus();
    };
    blockToggle?.addEventListener('click', () => {
      if (!blockPicker) return;
      if (!blockPicker.hidden) { closeBlockPicker(); return; }
      insertionIndex = activeEditor?.blocks.getCurrentBlockIndex() ?? -1;
      blockPicker.hidden = false;
      blockToggle.setAttribute('aria-expanded', 'true');
      const formRect = form.getBoundingClientRect();
      const toggleRect = blockToggle.getBoundingClientRect();
      const left = Math.max(12, Math.min(toggleRect.right - formRect.left + 7, form.clientWidth - blockPicker.offsetWidth - 12));
      const top = Math.max(12, Math.min(toggleRect.top - formRect.top, form.clientHeight - blockPicker.offsetHeight - 12));
      blockPicker.style.setProperty('left', left + 'px', 'important');
      blockPicker.style.setProperty('top', top + 'px', 'important');
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
      const index = insertionIndex >= 0 && insertionIndex < count ? insertionIndex + 1 : count;
      const data = type === 'header' ? { text: '', level: 2 } : type === 'list' && option.dataset.listStyle === 'checklist' ? { style: 'checklist', meta: {}, items: [] } : {};
      closeBlockPicker();
      const inserted = activeEditor.blocks.insert(type, data, {}, index, true);
      if (type === 'embed') inserted?.holder?.querySelector('input')?.focus();
      else if (type !== 'delimiter') activeEditor.caret.setToBlock(index, 'start');
    }));

    blockPicker?.querySelectorAll('[data-block-action]').forEach(option => {
      option.addEventListener('click', () => {
        closeBlockPicker();
        const selector = option.dataset.blockAction === 'gallery' ? '[data-gallery-open]' : option.dataset.blockAction === 'widget' ? '[data-composer-widget-toggle]' : '[data-composer-video-toggle]';
        form.querySelector(selector)?.click();
      });
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

    const setStatus = message => { if (status) status.textContent = message; };
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
        if (!window.EditorJS) {
          await loadWithFallback('@editorjs/editorjs@2.30.8');
        }
        if (typeof window.EditorJS !== 'function') {
          throw new Error('Editor.js ana dosyası yüklendi ancak EditorJS nesnesi bulunamadı.');
        }

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
        await Promise.all(optionalTools.map(([path]) => loadOptionalTool(path)));

        const tools = {};
        if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } };
        if (window.EditorjsList) tools.list = { class: window.EditorjsList, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
        if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Alıntını yaz…', captionPlaceholder: 'Kaynak' } };
        if (window.CodeTool) tools.code = { class: window.CodeTool, config: { placeholder: 'Kodunu yaz…' } };
        if (window.Delimiter) tools.delimiter = window.Delimiter;
        if (window.Table) tools.table = { class: window.Table, inlineToolbar: true, config: { rows: 2, cols: 2 } };
        tools.embed = ComposerEmbedTool;
        if (window.InlineCode) tools.inlineCode = window.InlineCode;
        if (window.Marker) tools.marker = window.Marker;

        let initial = { blocks: [] };
        if (input.value.trim()) {
          try {
            const parsed = JSON.parse(input.value);
            initial = parsed && Array.isArray(parsed.blocks)
              ? parsed
              : { blocks: [{ type: 'paragraph', data: { text: input.value } }] };
          } catch (_) {
            initial = { blocks: input.value.split(/\n{2,}/).filter(Boolean).map(text => ({ type: 'paragraph', data: { text } })) };
          }
        }

        const editor = new window.EditorJS({
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
          },
          onChange: async () => {
            try { input.value = JSON.stringify(await editor.save()); }
            catch (error) { console.error('Editor.js içerik senkronizasyonu:', error); }
          }
        });

        await editor.isReady;
        holder.classList.add('is-ready');
        activeEditor = editor;
        if (publishBtn) publishBtn.disabled = false;

        if (blockStatus) blockStatus.hidden = true;
        blockOptions.forEach(option => {
          const type = option.dataset.insertBlock;
          option.hidden = type !== 'paragraph' && !tools[type];
        });
        setStatus('Yazmaya başlayabilirsin');

        form.addEventListener('submit', async event => {
          event.preventDefault();
          const submitButton = form.querySelector('button[type="submit"]');
          if (submitButton) submitButton.disabled = true;
          try {
            const savedData = await editor.save();
            input.value = JSON.stringify(savedData);
            const excerptField = document.getElementById('post-excerpt');
            if (excerptField && !excerptField.value.trim()) {
              const textFromBlocks = (savedData.blocks || [])
                .map(block => block.data?.text || block.data?.caption || '')
                .join(' ')
                .replace(/<[^>]*>/g, ' ')
                .replace(/\\s+/g, ' ')
                .trim();
              excerptField.value = (textFromBlocks || document.getElementById('post-title')?.value || 'Yeni yayın').slice(0, 500);
            }
            HTMLFormElement.prototype.submit.call(form);
          } catch (error) {
            console.error('Editor.js kaydı başarısız:', error);
            setStatus('İçerik kaydedilemedi. Tekrar dene.');
            if (submitButton) submitButton.disabled = false;
          }
        });
      } catch (error) {
        console.error('Editor.js başlatma hatası:', error);
        if (blockStatus) { blockStatus.hidden = false; blockStatus.textContent = 'Editör yüklenemedi. Sayfayı yenile.'; }
        setStatus('Editör yüklenemedi — bağlantıyı kontrol edip sayfayı yenile.');
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
          }
          const originalText = textarea.value;
          form.addEventListener('submit', () => {
            if (!structuredBody && (textarea.value !== originalText || !input.value)) {
              input.value = JSON.stringify({ blocks: [{ type: 'paragraph', data: { text: textarea.value.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') } }] });
            }
            const excerpt = form.querySelector('#post-excerpt');
            if (excerpt && !excerpt.value.trim()) excerpt.value = (textarea.value || form.querySelector('#post-title')?.value || '').slice(0,500);
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

        const galleryInput = document.querySelector('[data-post-gallery-input]');
        const galleryList = document.querySelector('[data-post-gallery-list]');
        const galleryModal = document.querySelector('[data-gallery-modal]');
        const galleryDropzone = document.querySelector('[data-gallery-dropzone]');
        const gallerySelected = document.querySelector('[data-gallery-selected]');
        const composerOverlay = document.querySelector('.addpost.post-create-simple.post-create-modal-page');
        const composerForm = composerOverlay?.querySelector('#createPostBox');
        const openGallery = () => {
            if (!galleryModal) return;
            galleryModal.hidden = false;
            composerOverlay?.classList.add('is-gallery-open');
            composerForm?.setAttribute('aria-hidden', 'true');
        };
        const closeGallery = () => {
            if (galleryModal) galleryModal.hidden = true;
            composerOverlay?.classList.remove('is-gallery-open');
            composerForm?.removeAttribute('aria-hidden');
        };
        document.querySelector('[data-gallery-open]')?.addEventListener('click', openGallery);
        document.querySelectorAll('[data-gallery-close]').forEach(button => button.addEventListener('click', closeGallery));
        document.querySelector('[data-gallery-choose]')?.addEventListener('click', () => galleryInput?.click());
        galleryModal?.addEventListener('click', event => { if (event.target === galleryModal) closeGallery(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && galleryModal && !galleryModal.hidden) closeGallery(); });
        const showGalleryFiles = () => {
            if (!galleryInput || !gallerySelected) return;
            gallerySelected.replaceChildren();
            const files = Array.from(galleryInput.files || []);
            if (!files.length) return;
            const status = document.createElement('span');
            status.className = 'post-gallery-file-count';
            status.textContent = files.length + ' görsel seçildi';
            gallerySelected.append(status);
            if (galleryList) {
                galleryList.replaceChildren();
                files.forEach(file => {
                    const item = document.createElement('div');
                    item.className = 'post-composer-gallery-item';
                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = '';
                    image.onload = () => URL.revokeObjectURL(image.src);
                    const name = document.createElement('span');
                    name.textContent = file.name;
                    item.append(image, name);
                    galleryList.append(item);
                });
            }
        };
        galleryInput?.addEventListener('change', showGalleryFiles);
        ['dragenter', 'dragover'].forEach(type => galleryDropzone?.addEventListener(type, event => {
            event.preventDefault(); galleryDropzone.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach(type => galleryDropzone?.addEventListener(type, event => {
            event.preventDefault(); galleryDropzone.classList.remove('is-dragging');
        }));
        galleryDropzone?.addEventListener('drop', event => {
            const files = Array.from(event.dataTransfer?.files || []).filter(file => file.type.startsWith('image/'));
            if (!files.length || !galleryInput) return;
            const transfer = new DataTransfer();
            [...Array.from(galleryInput.files || []), ...files].slice(0, 10).forEach(file => transfer.items.add(file));
            galleryInput.files = transfer.files;
            galleryInput.dispatchEvent(new Event('change', { bubbles: true }));
        });

        const locationToggle = document.querySelector('[data-composer-location-toggle]');
        const locationPanel = document.querySelector('[data-composer-location-panel]');
        const locationClose = document.querySelector('[data-composer-location-close]');
        const videoToggle = document.querySelector('[data-composer-video-toggle]');
        const videoModal = document.querySelector('[data-video-modal]');
        const videoInput = document.getElementById('post-video-url');
        const widgetToggle = document.querySelector('[data-composer-widget-toggle]');
        const widgetModal = document.querySelector('[data-widget-modal]');
        const widgetInput = document.getElementById('post-widget-code');
        const tagsToggle = document.querySelector('[data-composer-tags-toggle]');
        const tagsModal = document.querySelector('[data-tags-modal]');
        const tagsInput = document.getElementById('post-tags-input');
        const closeComposerPanels = () => {
            if (locationPanel) locationPanel.hidden = true;
            locationToggle?.setAttribute('aria-expanded', 'false');
            videoToggle?.setAttribute('aria-expanded', 'false');
        };
        locationToggle?.addEventListener('click', () => {
            const opening = locationPanel?.hidden;
            closeComposerPanels();
            if (locationPanel) locationPanel.hidden = !opening;
            locationToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (opening) document.getElementById('post-location')?.focus();
        });
        locationClose?.addEventListener('click', () => {
            if (locationPanel) locationPanel.hidden = true;
            locationToggle?.setAttribute('aria-expanded', 'false');
        });
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
                'min-width:0', 'height:143px', 'min-height:143px', 'max-height:143px',
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
        };
        const closeTagsModal = () => {
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
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && tagsModal && !tagsModal.hidden) closeTagsModal(); });

        document.addEventListener('click', event => {
            if (locationPanel?.hidden) return;
            if (event.target.closest('[data-composer-location-panel], [data-composer-location-toggle]')) return;
            closeComposerPanels();
        });

        const form = document.querySelector('.post-create-simple form');
        form?.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Hazırlanıyor…';
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initComposerDetails, { once: true });
    } else {
        initComposerDetails();
    }
})();
</script>
@endsection
