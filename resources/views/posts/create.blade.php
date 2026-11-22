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

    <div class="addpost post-create-simple post-create-modal-page">
        <form id="post-create-form" class="addpost__form card card-body post-create-modal" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($post))
                @method('PATCH')
            @endif

            <header class="addpost__modal-head">
                <strong>{{ isset($post) ? 'Yayını düzenle' : 'Yeni yayın' }}</strong>
                <div class="addpost__modal-controls">
                    <button type="button" class="addpost__modal-expand" data-post-composer-expand aria-label="Genişlet" aria-pressed="false">
                        <img class="post-composer-expand-icon" src="https://media.hugeicons.com/previews/icons/expand-solid-rounded@3x.webp?x=1.0.2" width="20" height="20" alt="" aria-hidden="true">
                        <img class="post-composer-collapse-icon" src="https://media.hugeicons.com/previews/icons/collapse-stroke-rounded@3x.webp?x=1.0.2" width="20" height="20" alt="" aria-hidden="true">
                    </button>
                    <a href="{{ route('posts.index') }}" class="addpost__modal-close" aria-label="Kapat">
                        <img src="https://media.hugeicons.com/previews/icons/multiplication-sign-stroke-rounded@3x.webp?x=1.0.2" width="20" height="20" alt="" aria-hidden="true">
                    </a>
                </div>
            </header>
            <div class="addpost__header">
                <img class="addpost__header-avatar" src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                <div class="addpost__author">
                    <span class="addpost__author-name">{{ $user->displayName() }}</span>
                    <span class="addpost__channel-separator" aria-hidden="true">›</span>
                    <select id="post-channel" name="channel_id" aria-label="Bir kanal seçin">
                        <option value="">Bir kanal seçin</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->id }}" @selected((string) old('channel_id', $post->channel_id ?? '') === (string) $channel->id)>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="post-composer-field post-composer-title-field">
                <label class="post-composer-label" for="post-title">Gönderi başlığı</label>
                <input class="post-create-simple__title addpost__title" id="post-title" name="title" type="text"
                    value="{{ old('title', $post->title ?? '') }}" maxlength="255"
                    placeholder="Gönderi başlığı" required>
                <div class="post-composer-count"><span>Arama ve paylaşım için açık bir başlık seç.</span><span data-count-for="post-title">0 / 60</span></div>
            </div>

            <div class="post-composer-field post-composer-excerpt-field">
                <label class="post-composer-label" for="post-excerpt">Kısa açıklama</label>
                <textarea class="post-create-simple__excerpt" id="post-excerpt" name="excerpt" rows="3" maxlength="500"
                    placeholder="Yazını birkaç cümleyle özetle...">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                <div class="post-composer-count"><span>Gönderi kartlarında ve arama önizlemelerinde kullanılır.</span><span data-count-for="post-excerpt">0 / 160</span></div>
            </div>



            <input type="hidden" id="post-body" name="body" value="{{ old('body', $post->body ?? '') }}">
            <section class="post-composer-editor-section" aria-label="Yazı içeriği düzenleyicisi">
                <div class="post-composer-editor-title">
                    <div>
                        <label class="post-composer-label" for="post-editorjs">Yazı içeriği</label>
                        <p>Metin, başlık, liste, alıntı ve medya blokları ekle.</p>
                    </div>
                    <span class="post-create-simple__editor-status" data-editor-status role="status" aria-live="polite">Hazırlanıyor…</span>
                </div>
                <div class="post-create-simple__writing addpost__editor">
                    <div id="post-editorjs" class="post-editorjs post-create-simple__editor" aria-label="Yazı içeriği" data-placeholder="Напиши свой лучший текст"></div>
                    <div class="post-composer-editor-tip">Blok eklemek için sol taraftaki + simgesini kullan.</div>
                </div>
            </section>

            <section class="post-composer-media-section">
                <div class="post-composer-media-title">
                    <div>
                        <label class="post-composer-label">Görsel ve video</label>
                        <p>Yazına kapak, fotoğraf veya video ekle.</p>
                    </div>
                </div>
                <div class="post-create-simple__media addpost__actions post-composer-toolbar">
                    <button type="button" class="post-composer-tool" data-gallery-open aria-label="Görsel ekle" title="Görsel ekle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4" width="17" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m20.5 15-5-5L5 20"/></svg>
                    </button>
                    <input id="post-media" name="media[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-post-gallery-input>

                    <button type="button" class="post-composer-tool" data-composer-location-toggle aria-label="Konum ekle" title="Konum ekle" aria-expanded="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    </button>

                    <button type="button" class="post-composer-tool" data-composer-video-toggle aria-label="Video bağlantısı ekle" title="Video bağlantısı ekle" aria-expanded="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3z"/></svg>
                    </button>
                    <label for="post-video-file" class="post-composer-tool" aria-label="Video dosyası ekle" title="Dosya yükle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><path d="M13 3v7h7M8 14h8M8 17h8"/></svg>
                    </label>
                    <input id="post-video-file" name="video_file" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-matroska">

                    <button type="button" class="post-composer-tool" data-composer-widget-toggle aria-label="Widget ekle" title="Widget ekle" aria-expanded="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 4l-4 16"/></svg>
                    </button>
                    <button type="button" class="post-composer-tool" data-composer-tags-toggle aria-label="Etiket ekle" title="Etiket ekle">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9h16M4 15h16M9 4l-2 16M17 4l-2 16"/></svg>
                    </button>


                    <div class="post-composer-location-panel" data-composer-location-panel hidden>
                        <label for="post-location">Konum</label>
                        <input id="post-location" name="location" type="text" maxlength="255" value="{{ old('location', $post->location ?? '') }}" placeholder="Şehir, mekan veya adres">
                        <button type="button" data-composer-location-close aria-label="Konum alanını kapat">×</button>
                    </div>


                    <input id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-post-cover-input>
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

            <details class="post-create-simple__options post-composer-advanced">
                <summary>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/></svg>
                    <span>Yayınlama Seçenekleri</span>
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

            <footer class="post-create-simple__footer addpost__footer">
                <a href="{{ route('posts.index') }}">Vazgeç</a>
                <button class="btn btn-primary post-composer-publish" type="submit">{{ isset($post) ? 'Kaydet' : 'Yayınla' }}</button>
            </footer>
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
        <div class="post-video-modal" data-video-modal hidden>
            <div class="post-video-dialog" role="dialog" aria-modal="true" aria-labelledby="post-video-title">
                <button type="button" class="post-video-back" data-video-close aria-label="Geri dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h2 id="post-video-title">Video</h2>
                <input id="post-video-url" name="video_url" form="post-create-form" type="url"
                    value="{{ old('video_url', $post->video_url ?? '') }}" maxlength="2048"
                    placeholder="" aria-label="Video bağlantısı">
                <p class="post-video-hint">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                    <span>Video servislerinden bir video bağlantısı ekleyebilirsiniz.</span>
                </p>
            </div>
        </div>
    </div>


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
          ['@editorjs/embed@2.7.6', 'Embed'],
          ['@editorjs/inline-code@1.5.0', 'InlineCode'],
          ['@editorjs/marker@1.4.0', 'Marker']
        ];
        await Promise.all(optionalTools.map(([path]) => loadOptionalTool(path)));

        const tools = {};
        if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } };
        if (window.EditorjsList) tools.list = { class: window.EditorjsList, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
        if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Alıntını yaz…', captionPlaceholder: 'Kaynak' } };
        if (window.Embed) tools.embed = { class: window.Embed, config: { services: {
          youtube: true, facebook: true, twitter: true, instagram: true, tiktok: true
        } } };
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
          placeholder: 'Напиши свой лучший текст',
          minHeight: 84,
          autofocus: false,
          logLevel: 'ERROR',
          data: initial,
          tools,
          inlineToolbar: ['marker', 'inlineCode'],
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
        const composerForm = composerOverlay?.querySelector('form.post-create-modal');
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
        const tagsDetails = document.querySelector('.post-composer-advanced');
        const tagsInput = document.getElementById('tags');
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
        tagsToggle?.addEventListener('click', () => {
            closeComposerPanels();
            if (tagsDetails) tagsDetails.open = true;
            tagsInput?.focus();
        });

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
