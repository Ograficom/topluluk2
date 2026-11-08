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

    <div class="addpost post-create-simple">
        <form class="addpost__form card card-body" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($post))
                @method('PATCH')
            @endif

            <div class="post-create-simple__author">
                <img src="{{ $user->avatarUrl() }}" alt="" width="38" height="38">
                <strong>{{ $user->displayName() }}</strong>
            </div>

            <input class="post-create-simple__title" id="post-title" name="title" type="text"
                value="{{ old('title', $post->title ?? '') }}" maxlength="255"
                placeholder="Başlık" aria-label="Başlık" required>

            <textarea class="post-create-simple__excerpt" id="post-excerpt" name="excerpt"
                rows="2" maxlength="500" placeholder="Kısaca anlat..." aria-label="Kısa açıklama"
                required>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>

            <label class="post-create-simple__seo-label" for="post-meta-description">SEO meta açıklaması</label>
            <textarea class="post-create-simple__seo" id="post-meta-description" name="meta_description" rows="2" maxlength="500"
                placeholder="Google arama sonuçlarında görünecek açıklama">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>

            <input type="hidden" id="post-body" name="body" value="{{ old('body', $post->body ?? '') }}">
            <section class="post-create-simple__writing" aria-label="Yazı içeriği düzenleyicisi">
                <div class="post-create-simple__writing-head">
                    <div>
                        <strong>Yazı içeriği</strong>
                        <span>Metnini yaz, başlık ve medya blokları ekle.</span>
                    </div>
                    <span class="post-create-simple__editor-status" data-editor-status>Hazırlanıyor…</span>
                </div>
                <div id="post-editorjs" class="post-editorjs post-create-simple__editor" aria-label="Yazı içeriği" data-placeholder="Hikâyeni burada anlat…"></div>
                <p class="post-create-simple__editor-hint">Yazmaya başla. Yeni blok eklemek için sol taraftaki + simgesini kullan.</p>
            </section>

            <div class="post-create-simple__media">
                <label for="post-cover" class="post-create-simple__file">
                    <span class="icon icon-image" aria-hidden="true"></span>
                    Kapak görseli
                </label>
                <input id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-post-cover-input>
                <div class="post-composer-cover-preview" data-post-cover-preview aria-live="polite"></div>
                @if (isset($post) && $post->coverImageUrl())
                    <img src="{{ $post->coverImageUrl() }}" alt="" style="max-width:120px;height:auto;border-radius:8px;">
                    <label class="small"><input type="checkbox" name="remove_cover" value="1"> Mevcut kapağı kaldır</label>
                @endif

                <label for="post-media" class="post-create-simple__file">
                    <span class="icon icon-images" aria-hidden="true"></span>
                    Fotoğraf ekle
                </label>
                <input id="post-media" name="media[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-post-gallery-input>
                <div class="post-composer-file-list" data-post-gallery-list aria-live="polite"></div>
                @if (isset($post) && filled($post->media))
                    <div class="post-create-simple__existing-media">
                        @foreach ($post->media as $mediaPath)
                            <label><img src="{{ asset('storage/'.$mediaPath) }}" alt=""><input type="checkbox" name="remove_media[]" value="{{ $mediaPath }}"> Kaldır</label>
                        @endforeach
                    </div>
                @endif

                <label for="post-video-url" class="post-create-simple__file">
                    <span class="icon icon-video" aria-hidden="true"></span>
                    Video bağlantısı
                </label>
                <input id="post-video-url" name="video_url" type="url" value="{{ old('video_url', $post->video_url ?? '') }}"
                    maxlength="2048" placeholder="YouTube, Instagram, TikTok vb. bağlantı">

                <label for="post-video-file" class="post-create-simple__file">
                    <span class="icon icon-upload" aria-hidden="true"></span>
                    Video dosyası yükle
                </label>
                <input id="post-video-file" name="video_file" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-matroska">
                @if (isset($post) && $post->video_path)
                    <p class="small color-gray">Mevcut video kayıtlı. Yeni video seçersen değiştirilir.</p>
                @endif
            </div>

            <details class="post-create-simple__options">
                <summary>Diğer ayarlar</summary>
                <label for="post-channel">Kategori</label>
                <select id="post-channel" name="channel_id">
                    <option value="">Kategori seç</option>
                    @foreach ($channels as $channel)
                        <option value="{{ $channel->id }}" @selected((string) old('channel_id', $post->channel_id ?? '') === (string) $channel->id)>{{ $channel->name }}</option>
                    @endforeach
                </select>

                <label for="tags">Etiketler</label>
                <input id="tags" name="tags" type="text" value="{{ old('tags', isset($post) ? implode(', ', $post->tags ?? []) : '') }}"
                    maxlength="500" placeholder="Örn. teknoloji, gezi">

                <label class="post-create-simple__adult">
                    <input id="post-adult" type="checkbox" name="is_adult" value="1" @checked(old('is_adult', $post->is_adult ?? false))>
                    18+ içerik
                </label>
            </details>

            <footer class="post-create-simple__footer">
                <a href="{{ route('posts.index') }}">Vazgeç</a>
                <button class="btn btn-primary" type="submit">{{ isset($post) ? 'Kaydet' : 'Yayınla' }}</button>
            </footer>
        </form>
    </div>
<script>
(async () => {
  const holder = document.getElementById('post-editorjs');
  const input = document.getElementById('post-body');
  const form = holder?.closest('form');
  const status = document.querySelector('[data-editor-status]');
  if (!holder || !input || !form) return;

  const setStatus = message => { if (status) status.textContent = message; };
  const loadScript = src => new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = src;
    script.async = false;
    script.onload = resolve;
    script.onerror = () => reject(new Error('Dosya yüklenemedi: ' + src));
    document.head.appendChild(script);
  });

  try {
    setStatus('Editör yükleniyor…');
    const scripts = [
      'https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8',
      'https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8',
      'https://cdn.jsdelivr.net/npm/@editorjs/list@2.0.8',
      'https://cdn.jsdelivr.net/npm/@editorjs/quote@2.7.6',
      'https://cdn.jsdelivr.net/npm/@editorjs/embed@2.7.6',
      'https://cdn.jsdelivr.net/npm/@editorjs/inline-code@1.5.0',
      'https://cdn.jsdelivr.net/npm/@editorjs/marker@1.4.0'
    ];
    for (const src of scripts) await loadScript(src);
    if (!window.EditorJS) throw new Error('Editor.js ana kütüphanesi yüklenemedi.');

    const tools = {
      header: { class: window.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } },
      list: { class: window.EditorjsList, inlineToolbar: true, config: { defaultStyle: 'unordered' } },
      quote: { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Alıntını yaz…', captionPlaceholder: 'Kaynak' } },
      inlineCode: window.InlineCode,
      marker: window.Marker,
      embed: { class: window.Embed, config: { services: [
        { name: 'youtube', regex: /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/, embedUrl: 'https://www.youtube.com/embed/<%= remote_id %>', html: '<iframe width="100%" height="360" src="<%= embed %>" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>', height: 360, width: 640 },
        { name: 'facebook', regex: /https?:\/\/(?:www\.)?facebook\.com\/.+/, embedUrl: 'https://www.facebook.com/plugins/video.php?href=<%= encodeURIComponent(url) %>&show_text=0', html: '<iframe width="100%" height="420" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 420, width: 640 },
        { name: 'x', regex: /https?:\/\/(?:www\.)?(?:x\.com|twitter\.com)\/.+\/status\/(\d+)/, embedUrl: 'https://platform.twitter.com/embed/Tweet.html?id=<%= remote_id %>', html: '<iframe width="100%" height="550" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 550, width: 550 },
        { name: 'instagram', regex: /https?:\/\/(?:www\.)?instagram\.com\/(?:p|reel|tv)\/([a-zA-Z0-9_-]+)/, embedUrl: 'https://www.instagram.com/p/<%= remote_id %>/embed', html: '<iframe width="100%" height="540" src="<%= embed %>" frameborder="0" scrolling="no"></iframe>', height: 540, width: 400 },
        { name: 'tiktok', regex: /https?:\/\/(?:www\.|vm\.)?tiktok\.com\/(?:@[^/]+\/video\/|t/)?([a-zA-Z0-9]+)/, embedUrl: 'https://www.tiktok.com/embed/v2/<%= remote_id %>', html: '<iframe width="100%" height="740" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 740, width: 325 }
      ] } }
    };

    let initial = { blocks: [] };
    if (input.value.trim()) {
      try {
        const parsed = JSON.parse(input.value);
        initial = parsed && Array.isArray(parsed.blocks) ? parsed : { blocks: [{ type: 'paragraph', data: { text: input.value } }] };
      } catch (_) {
        initial = { blocks: input.value.split(/\n{2,}/).filter(Boolean).map(text => ({ type: 'paragraph', data: { text } })) };
      }
    }

    let editor;
    editor = new window.EditorJS({
      holder,
      placeholder: 'Hikâyeni burada anlat…',
      minHeight: 260,
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
        try { input.value = JSON.stringify(await editor.save()); } catch (error) { console.error(error); }
      }
    });

    await editor.isReady;
    setStatus('Editör hazır');
    form.addEventListener('submit', async event => {
      event.preventDefault();
      try {
        await editor.isReady;
        input.value = JSON.stringify(await editor.save());
        HTMLFormElement.prototype.submit.call(form);
      } catch (error) {
        console.error('Editor.js kaydı başarısız:', error);
        alert('Yazı içeriği kaydedilemedi. Lütfen tekrar deneyin.');
      }
    });
  } catch (error) {
    console.error('Editor.js yükleme/başlatma hatası:', error);
    setStatus('Editör yüklenemedi');
    holder.innerHTML = '<div class="post-create-simple__editor-error">Editör yüklenemedi. Sayfayı yenileyin veya bağlantınızı kontrol edin.</div>';
  }
})();
</script>
@endsection



