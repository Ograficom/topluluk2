@extends('layouts.app')

@section('title', isset($post) ? 'Yazıyı düzenle' : 'Yazı oluştur')

@section('content')
    @if ($errors->any())
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="addpost post-create-simple post-create-modal-page" data-composer-design="repost-rebuilt">
        <style>

/* Standalone Repost composer. The legacy post-create-modal class and form id
   are deliberately not used here: their old layout rules must not apply. */
[data-composer-design="repost-rebuilt"] {
    display:flex !important; position:fixed !important; inset:0 !important;
    z-index:12000 !important; align-items:center !important; justify-content:center !important;
    padding:16px !important; box-sizing:border-box !important;
    background:var(--color-overlay-backdrop,rgba(15,23,42,.48)) !important;
    backdrop-filter:blur(4px) !important;
}
#ografi-composer-form {
    --compose-pad:24px;
    position:relative; display:flex; flex-direction:column;
    width:min(590px,100%); max-width:100%; min-width:0;
    max-height:calc(100dvh - 32px); margin:0; padding:0;
    border:1px solid var(--card-border-color); border-radius:24px;
    background:var(--card-bg); color:var(--text-color);
    box-shadow:var(--shadow-lg); box-sizing:border-box;
    font:400 .95rem/1.5 var(--font-primary); overflow:visible;
}
[data-composer-design="repost-rebuilt"].is-expanded > #ografi-composer-form {
    width:min(960px,100%); height:calc(100dvh - 32px);
}
[data-composer-design="repost-rebuilt"]:is(.is-gallery-open,.is-video-open,.is-widget-open) > #ografi-composer-form {display:none !important;}
#ografi-composer-form *, #ografi-composer-form *::before, #ografi-composer-form *::after {box-sizing:border-box;}
#ografi-composer-form [hidden] {display:none !important;}
#ografi-composer-form .compose-head {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    flex:none; min-height:65px; padding:16px var(--compose-pad);
    border-bottom:1px solid var(--border-color);
}
#ografi-composer-form .compose-head strong {font:600 1.125rem/1.25 var(--font-secondary);color:var(--title-color);}
#ografi-composer-form .compose-controls {display:flex;align-items:center;gap:4px;}
#ografi-composer-form .compose-icon,
#ografi-composer-form .compose-media-button {
    display:inline-flex;align-items:center;justify-content:center;gap:6px;
    flex:none; padding:0; width:34px;height:34px;border:0;border-radius:11px;
    background:transparent;color:var(--color-gray);cursor:pointer;text-decoration:none;
}
#ografi-composer-form .compose-icon:hover,
#ografi-composer-form .compose-media-button:hover,
#ografi-composer-form .compose-media-button[aria-expanded="true"] {background:var(--gray-150);color:var(--color-primary);}
#ografi-composer-form :is(.compose-icon,.compose-media-button,.composer-block-add,.composer-block-picker button,.compose-settings summary) svg {
    display:block;flex:none;width:20px;height:20px;fill:none;stroke:currentColor;
    stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;
}
#ografi-composer-form .composer-collapse-symbol,
[data-composer-design="repost-rebuilt"].is-expanded #ografi-composer-form .composer-expand-symbol {display:none;}
[data-composer-design="repost-rebuilt"].is-expanded #ografi-composer-form .composer-collapse-symbol {display:block;}
#ografi-composer-form .compose-main {
    display:flex;flex-direction:column;gap:20px;flex:1 1 auto;min-height:0;
    padding:22px var(--compose-pad) 18px;overflow:auto;overscroll-behavior:contain;
}
#ografi-composer-form .compose-author {display:flex;align-items:center;gap:11px;min-width:0;flex:none;}
#ografi-composer-form .compose-avatar {width:40px;height:40px;flex:none;object-fit:cover;border-radius:50%;}
#ografi-composer-form .compose-identity {display:flex;flex-direction:column;align-items:flex-start;gap:4px;min-width:0;}
#ografi-composer-form .compose-name {font:600 .95rem/1.2 var(--font-secondary);color:var(--title-color);max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
#ografi-composer-form #post-channel {
    width:auto;max-width:100%;min-height:25px;height:25px;padding:0 7px;margin:0;
    font:400 .8125rem/1.2 var(--font-primary);appearance:auto;
    border:1px solid var(--border-color);border-radius:7px;
    background:var(--gray-100);color:var(--color-gray);cursor:pointer;
}
#ografi-composer-form .compose-title-field {flex:none;min-width:0;}
#ografi-composer-form #post-title {
    display:block;width:100%;height:38px;min-height:38px;margin:0;padding:0;
    border:0;border-radius:0;outline:0;box-shadow:none;background:transparent;
    font:600 1.4rem/1.5 var(--font-primary);color:var(--title-color);
}
#ografi-composer-form #post-title::placeholder {color:var(--color-gray);opacity:1;}
#ografi-composer-form .compose-title-field:focus-within {box-shadow:0 1px 0 var(--color-primary);}
#ografi-composer-form .compose-visually-hidden {position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip-path:inset(50%);white-space:nowrap;border:0;}
#ografi-composer-form .compose-writing {
    position:relative;min-height:132px;flex:1 0 auto;padding:2px 0 0 38px;
}
#ografi-composer-form .composer-block-add {
    display:grid;place-items:center;position:absolute;top:0;left:0;
    width:28px;height:28px;padding:0;margin:0;border:0;border-radius:50%;
    color:var(--color-gray);background:var(--gray-150);cursor:pointer;
}
#ografi-composer-form .composer-block-add svg {width:18px;height:18px;}
#ografi-composer-form .composer-block-add:hover,
#ografi-composer-form .composer-block-add[aria-expanded="true"] {background:var(--blue-400);color:var(--color-primary);}
#ografi-composer-form #post-editorjs {position:relative;min-height:120px;min-width:0;overflow:visible;font:400 .95rem/1.6 var(--font-primary);color:var(--text-color);}
#ografi-composer-form #post-editorjs .codex-editor,
#ografi-composer-form #post-editorjs .codex-editor__redactor {height:auto;min-height:120px;overflow:visible;padding-bottom:0 !important;margin-right:0 !important;}
#ografi-composer-form #post-editorjs .ce-block__content,
#ografi-composer-form #post-editorjs .ce-toolbar__content {max-width:none;margin-left:0;margin-right:0;}
#ografi-composer-form #post-editorjs .ce-paragraph {font:inherit;min-height:24px;padding:3px 0;overflow-wrap:anywhere;}
#ografi-composer-form #post-editorjs [contenteditable="true"] {color:var(--text-color);outline:none;overflow-wrap:anywhere;}
#ografi-composer-form #post-editorjs:not(.is-ready):empty::before {content:attr(data-placeholder);color:var(--color-gray);}
#ografi-composer-form #post-editorjs .ce-paragraph[data-placeholder]::before,
#ografi-composer-form #post-editorjs .ce-paragraph[data-placeholder-active]::before {color:var(--color-gray);}
#ografi-composer-form #post-editorjs .ce-toolbar__plus {display:none !important;}
#ografi-composer-form #post-editorjs .ce-toolbar {z-index:10;}
#ografi-composer-form #post-editorjs .ce-inline-toolbar {z-index:45;overflow:visible;}
#ografi-composer-form #post-editorjs .ce-popover {
    --border-radius:12px;--color-border:var(--border-color);
    --color-background:var(--card-bg);--color-text-primary:var(--text-color);
    --color-text-secondary:var(--color-gray);--color-background-item-hover:var(--gray-150);
    --color-text-icon-active:var(--color-primary);--color-background-icon-active:var(--blue-400);
    overflow:visible;
}
#ografi-composer-form #post-editorjs .ce-popover--opened > .ce-popover__container {box-shadow:var(--shadow-lg);}
#ografi-composer-form .compose-main:has(.ce-inline-toolbar .ce-popover--opened) {padding-bottom:56px;}
#ografi-composer-form .compose-main:has(.ce-inline-toolbar .ce-popover--nested.ce-popover--opened) {padding-bottom:230px;}
#ografi-composer-form .compose-attachments {flex:none;display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:12px var(--compose-pad);border-top:1px solid var(--border-color);}
#ografi-composer-form .compose-attachments-label {color:var(--color-gray);font:500 .8125rem/1.3 var(--font-secondary);margin-right:auto;}
#ografi-composer-form .compose-media-button {background:var(--gray-100);width:36px;height:36px;}
#ografi-composer-form .compose-file-input {position:absolute;width:1px;height:1px;opacity:0;clip-path:inset(50%);pointer-events:none;}
#ografi-composer-form .compose-previews {display:flex;flex:1 0 100%;gap:8px;flex-wrap:wrap;}
#ografi-composer-form .compose-previews:not(:has(img,p,label, .post-composer-gallery-item)) {display:none;}
#ografi-composer-form .compose-previews img {width:54px;height:54px;object-fit:cover;border-radius:9px;}
#ografi-composer-form .post-composer-gallery-item {display:flex;align-items:center;gap:6px;font-size:12px;max-width:170px;overflow:hidden;}
#ografi-composer-form .post-composer-gallery-item span {white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
#ografi-composer-form .post-composer-location-panel:not([hidden]) {display:flex;align-items:center;gap:8px;flex:1 0 100%;flex-wrap:wrap;}
#ografi-composer-form .post-composer-location-panel label {font-size:13px;color:var(--color-gray);}
#ografi-composer-form .post-composer-location-panel input,
#ografi-composer-form .post-composer-source-field input {flex:1;min-width:0;width:100%;height:38px;border:1px solid var(--border-color);border-radius:11px;padding:0 10px;background:var(--gray-100);color:var(--text-color);font:inherit;}
#ografi-composer-form .post-composer-location-panel button {border:0;background:transparent;color:var(--color-gray);font-size:20px;cursor:pointer;}
#ografi-composer-form .compose-footer {display:flex;align-items:center;justify-content:space-between;gap:10px;flex:none;padding:14px var(--compose-pad);border-top:1px solid var(--border-color);}
#ografi-composer-form .compose-footer-actions {display:flex;align-items:center;gap:8px;flex:none;}
#ografi-composer-form .compose-footer .btn {min-height:40px;font:600 .86rem/1.25 var(--font-secondary);padding:0 15px;}
#ografi-composer-form .compose-settings {position:relative;min-width:0;}
#ografi-composer-form .compose-settings summary {display:flex;align-items:center;gap:7px;min-height:38px;cursor:pointer;list-style:none;color:var(--color-gray);font:500 .8125rem/1.25 var(--font-secondary);}
#ografi-composer-form .compose-settings summary::-webkit-details-marker {display:none;}
#ografi-composer-form .compose-settings summary svg {width:18px;height:18px;}
#ografi-composer-form .compose-settings[open] summary {color:var(--color-primary);}
#ografi-composer-form .post-composer-advanced-content {position:absolute;bottom:calc(100% + 8px);left:0;width:min(310px,calc(100vw - 56px));max-height:50dvh;overflow:auto;padding:14px;border:1px solid var(--border-color);border-radius:16px;background:var(--card-bg);box-shadow:var(--shadow-lg);z-index:65;}
#ografi-composer-form .compose-settings:not([open]) .post-composer-advanced-content {display:none;}
#ografi-composer-form .post-composer-setting-row {padding:6px 0;}
#ografi-composer-form .post-composer-setting-row label {display:flex;align-items:center;gap:9px;font-size:13px;cursor:pointer;}
#ografi-composer-form .post-composer-setting-row input {accent-color:var(--color-primary);}
#ografi-composer-form .post-composer-source-field {display:grid;gap:6px;margin-top:10px;font-size:13px;}
#ografi-composer-form .composer-block-picker:not([hidden]) {
    display:flex;flex-direction:column;gap:2px;position:absolute;z-index:100;
    width:214px;max-width:calc(100% - 24px);max-height:min(350px,calc(100% - 24px));overflow:auto;
    padding:6px;border:1px solid var(--border-color);border-radius:14px;
    background:var(--card-bg);color:var(--text-color);box-shadow:var(--shadow-lg);
}
#ografi-composer-form .composer-block-picker button {
    display:flex;align-items:center;gap:10px;padding:7px 10px;min-height:34px;flex:none;
    border:0;border-radius:9px;background:transparent;color:inherit;font:400 .875rem/1.3 var(--font-primary);
    text-align:left;cursor:pointer;
}
#ografi-composer-form .composer-block-picker button svg {width:18px;height:18px;color:var(--color-gray);}
#ografi-composer-form .composer-block-picker button:hover {background:var(--gray-150);color:var(--color-primary);}
#ografi-composer-form :is(button,a,summary,select):focus-visible {outline:2px solid var(--color-primary);outline-offset:2px;}
@media(max-width:640px){
    [data-composer-design="repost-rebuilt"] {padding:8px !important;}
    #ografi-composer-form {--compose-pad:16px;border-radius:20px;max-height:calc(100dvh - 16px);}
    [data-composer-design="repost-rebuilt"].is-expanded > #ografi-composer-form {height:calc(100dvh - 16px);}
    #ografi-composer-form .compose-main {gap:16px;padding-block:16px;}
    #ografi-composer-form .compose-attachments {gap:5px;}
    #ografi-composer-form .compose-attachments-label {flex-basis:100%;}
    #ografi-composer-form .compose-media-button {width:34px;height:34px;}
    #ografi-composer-form .compose-footer .compose-cancel {display:none;}
    #ografi-composer-form .compose-footer .btn {padding-inline:12px;}
    #ografi-composer-form .compose-settings summary {font-size:12px;}
}
        </style>
        <form id="ografi-composer-form" class="ografi-composer-form" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($post))
                @method('PATCH')
            @endif

            <header class="compose-head">
                <strong>{{ isset($post) ? 'Yayını düzenle' : 'Yeni yayın' }}</strong>
                <div class="compose-controls">
                    <button type="button" class="compose-icon addpost__modal-expand" data-post-composer-expand aria-label="Genişlet" aria-pressed="false">
                        <svg class="composer-expand-symbol" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M10 20H4v-6"/></svg>
                        <svg class="composer-collapse-symbol" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10h-6V4M4 14h6v6"/></svg>
                    </button>
                    <a href="{{ route('posts.index') }}" class="compose-icon addpost__modal-close" aria-label="Kapat">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </a>
                </div>
            </header>
            <div class="compose-main">
            <div class="compose-author">
                <img class="compose-avatar" src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                <div class="compose-identity">
                    <span class="compose-name">{{ $user->displayName() }}</span>
                    <select id="post-channel" name="channel_id" aria-label="Bir kanal seçin">
                        <option value="">Bir kanal seçin</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->id }}" @selected((string) old('channel_id', $post->channel_id ?? '') === (string) $channel->id)>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="compose-title-field">
                <label class="compose-visually-hidden" for="post-title">Gönderi başlığı</label>
                <input class="compose-title-input" id="post-title" name="title" type="text"
                    value="{{ old('title', $post->title ?? '') }}" maxlength="255"
                    placeholder="Gönderi başlığı" required>
                <div class="compose-visually-hidden"><span>Arama ve paylaşım için açık bir başlık seç.</span><span data-count-for="post-title">0 / 60</span></div>
            </div>

            <div class="compose-visually-hidden">
                <label class="post-composer-label" for="post-excerpt">Kısa açıklama</label>
                <textarea class="post-create-simple__excerpt" id="post-excerpt" name="excerpt" rows="3" maxlength="500"
                    placeholder="Yazını birkaç cümleyle özetle...">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                <div class="post-composer-count"><span>Gönderi kartlarında ve arama önizlemelerinde kullanılır.</span><span data-count-for="post-excerpt">0 / 160</span></div>
            </div>



            <input type="hidden" id="post-body" name="body" value="{{ old('body', $post->body ?? '') }}">
            <section class="compose-writing" aria-label="Yazı içeriği düzenleyicisi">
                <span class="compose-visually-hidden" data-editor-status role="status" aria-live="polite">Hazırlanıyor…</span>
                    <button type="button" class="composer-block-add" data-block-picker-toggle aria-label="İçerik bloğu ekle" aria-expanded="false" aria-controls="composer-block-picker" title="Blok ekle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <div id="post-editorjs" class="compose-editor" aria-label="Yazı içeriği" data-placeholder="En iyi yazını burada yaz"></div>
            </section>
            </div>

            <section class="compose-attachments" aria-label="Gönderiye ekle">
                <span class="compose-attachments-label">Gönderiye ekle</span>
                    <button type="button" class="compose-media-button" data-gallery-open aria-label="Görsel ekle" title="Görsel ekle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4" width="17" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m20.5 15-5-5L5 20"/></svg>
                    </button>
                    <input class="compose-file-input" id="post-media" name="media[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-post-gallery-input>

                    <button type="button" class="compose-media-button" data-composer-location-toggle aria-label="Konum ekle" title="Konum ekle" aria-expanded="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    </button>

                    <button type="button" class="compose-media-button" data-composer-video-toggle aria-label="Video bağlantısı ekle" title="Video bağlantısı ekle" aria-expanded="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3z"/></svg>
                    </button>
                    <label for="post-video-file" class="compose-media-button" aria-label="Video dosyası ekle" title="Dosya yükle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><path d="M13 3v7h7M8 14h8M8 17h8"/></svg>
                    </label>
                    <input class="compose-file-input" id="post-video-file" name="video_file" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-matroska">

                    <button type="button" class="compose-media-button" data-composer-widget-toggle aria-label="Widget ekle" title="Widget ekle" aria-expanded="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 4l-4 16"/></svg>
                    </button>
                    <button type="button" class="compose-media-button" data-composer-tags-toggle aria-label="Etiket ekle" title="Etiket ekle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9h16M4 15h16M9 4l-2 16M17 4l-2 16"/></svg>
                    </button>


                    <div class="post-composer-location-panel" data-composer-location-panel hidden>
                        <label for="post-location">Konum</label>
                        <input id="post-location" name="location" type="text" maxlength="255" value="{{ old('location', $post->location ?? '') }}" placeholder="Şehir, mekan veya adres">
                        <button type="button" data-composer-location-close aria-label="Konum alanını kapat">×</button>
                    </div>


                    <input class="compose-file-input" id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-post-cover-input>
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
            </section>

            <footer class="compose-footer">
            <details class="compose-settings post-composer-advanced">
                <summary>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/></svg>
                    <span>Yayın ayarları</span>
                </summary>
                <div class="post-composer-advanced-content">
                    <div class="post-composer-setting-row">
                        <label class="post-create-simple__adult" for="post-adult">
                            <input id="post-adult" type="checkbox" name="is_adult" value="1" @checked(old('is_adult', $post->is_adult ?? false))>
                            <span>18+ içerik</span>
                        </label>
                    </div>
                    <div class="post-composer-setting-row">
                        <label class="post-composer-ai" for="post-ai-content">
                            <input id="post-ai-content" type="checkbox" name="ai_content_disclosure" value="1" @checked(old('ai_content_disclosure', false))>
                            <span>İçerik yapay zeka verileri içermektedir.</span>
                        </label>
                    </div>
                    <div class="post-composer-source-field">
                        <label for="post-source">Lütfen kaynağı belirtin.</label>
                        <input id="post-source" name="content_source" type="text" value="{{ old('content_source', '') }}" maxlength="1000" placeholder="">
                    </div>
                </div>
            </details>
                <div class="compose-footer-actions">
                    <a class="btn btn-secondary compose-cancel addpost__modal-close" href="{{ route('posts.index') }}">Vazgeç</a>
                    <button class="btn btn-primary post-composer-publish" type="submit">{{ isset($post) ? 'Kaydet' : 'Yayınla' }}</button>
                </div>
            </footer>

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
            </div>


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
                    <input id="post-tags-input" name="tags" form="ografi-composer-form" type="text" maxlength="500"
                        value="{{ old('tags', $post->tags ?? '') }}" placeholder="Etiketleri girin" aria-label="Etiketler">
                </div>
            </div>
        </div>
        <div class="post-video-modal" data-video-modal hidden>
            <div class="post-video-dialog" role="dialog" aria-modal="true" aria-labelledby="post-video-title">
                <button type="button" class="post-video-back" data-video-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-video-title">Video</h2>
                <input id="post-video-url" name="video_url" form="ografi-composer-form" type="url"
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
        const selector = option.dataset.blockAction === 'gallery' ? '[data-gallery-open]' : '[data-composer-video-toggle]';
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
          placeholder: 'En iyi yazını burada yaz',
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
        holder.innerHTML = '<div class="post-create-simple__editor-error">Editör yüklenemedi. İnternet bağlantısını kontrol edip sayfayı yenile.</div>';
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
        const composerForm = composerOverlay?.querySelector('#ografi-composer-form');
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
