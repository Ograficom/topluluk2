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
                <div class="post-create-simple__editor-toolbar" role="toolbar" aria-label="İçerik araçları">
                    <button type="button" data-editor-tool="paragraph" title="Paragraf">Paragraf</button>
                    <button type="button" data-editor-tool="header" title="Başlık">Başlık</button>
                    <button type="button" data-editor-tool="list" title="Liste">Liste</button>
                    <button type="button" data-editor-tool="quote" title="Alıntı">Alıntı</button>
                    <button type="button" data-editor-tool="embed" title="Video veya sosyal medya">Medya</button>
                </div>
                <div id="post-editorjs" class="post-editorjs post-create-simple__editor" aria-label="Yazı içeriği" data-placeholder="Hikâyeni burada anlat…"></div>
                <p class="post-create-simple__editor-hint">Yeni blok eklemek için sol taraftaki + düğmesini veya yukarıdaki araçları kullan.</p>
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
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.7.6"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/code@2.9.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@1.4.2"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/table@2.4.3"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/inline-code@1.5.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@1.4.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@2.7.6"></script>
    <script>
    (() => {
      const input = document.getElementById('post-body');
      const holder = document.getElementById('post-editorjs');
      const form = holder?.closest('form');
      const status = document.querySelector('[data-editor-status]');
      if (!input || !holder || !form) return;

      if (!window.EditorJS) {
        if (status) status.textContent = 'Editör yüklenemedi. Sayfayı yenileyin.';
        holder.innerHTML = '<p class="post-create-simple__editor-error">Metin editörü yüklenemedi. İnternet bağlantınızı kontrol edip sayfayı yenileyin.</p>';
        return;
      }

      const initial = input.value.trim();
      let data = { blocks: [] };
      if (initial) {
        try {
          const parsed = JSON.parse(initial);
          data = parsed && Array.isArray(parsed.blocks)
            ? parsed
            : { blocks: [{ type: 'paragraph', data: { text: initial.replace(/\\r?\\n/g, '<br>') } }] };
        } catch (_) {
          data = { blocks: initial.split(/\\n{2,}/).filter(Boolean).map(text => ({ type: 'paragraph', data: { text } })) };
        }
      }

      const tools = {};
      if (window.Header) tools.header = { class: window.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } };
      if (window.EditorjsList) tools.list = { class: window.EditorjsList, inlineToolbar: true, config: { defaultStyle: 'unordered' } };
      if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Alıntıyı yaz', captionPlaceholder: 'Kaynak' } };
      if (window.CodeTool) tools.code = { class: window.CodeTool };
      if (window.Delimiter) tools.delimiter = window.Delimiter;
      if (window.Table) tools.table = { class: window.Table, inlineToolbar: true, config: { rows: 2, cols: 3 } };
      if (window.InlineCode) tools.inlineCode = window.InlineCode;
      if (window.Marker) tools.marker = window.Marker;
      if (window.Embed) tools.embed = { class: window.Embed, config: { services: [
        { name: 'youtube', regex: /(?:https?:\\/\\/)?(?:www\\.)?(?:youtube\\.com\\/(?:watch\\?v=|shorts\\/)|youtu\\.be\\/)([a-zA-Z0-9_-]{11})/, embedUrl: 'https://www.youtube.com/embed/<%= remote_id %>', html: '<iframe width="100%" height="360" src="<%= embed %>" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>', height: 360, width: 640 },
        { name: 'facebook', regex: /https?:\\/\\/(?:www\\.)?facebook\\.com\\/.+/, embedUrl: 'https://www.facebook.com/plugins/video.php?href=<%= encodeURIComponent(url) %>&show_text=0', html: '<iframe width="100%" height="420" src="<%= embed %>" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen></iframe>', height: 420, width: 640 },
        { name: 'x', regex: /https?:\\/\\/(?:www\\.)?(?:x\\.com|twitter\\.com)\\/.+\\/status\\/(\\d+)/, embedUrl: 'https://platform.twitter.com/embed/Tweet.html?id=<%= remote_id %>', html: '<iframe width="100%" height="550" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 550, width: 550 },
        { name: 'instagram', regex: /https?:\\/\\/(?:www\\.)?instagram\\.com\\/(?:p|reel|tv)\\/([a-zA-Z0-9_-]+)/, embedUrl: 'https://www.instagram.com/p/<%= remote_id %>/embed', html: '<iframe width="100%" height="540" src="<%= embed %>" frameborder="0" scrolling="no"></iframe>', height: 540, width: 400 },
        { name: 'tiktok', regex: /https?:\\/\\/(?:www\\.|vm\\.)?tiktok\\.com\\/(?:@[^/]+\\/video\\/|t/)?([a-zA-Z0-9]+)/, embedUrl: 'https://www.tiktok.com/embed/v2/<%= remote_id %>', html: '<iframe width="100%" height="740" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 740, width: 325 }
      ] } };

      let editor;
      try {
        editor = new EditorJS({
          holder,
          placeholder: 'Hikâyeni burada anlat…',
          minHeight: 180,
          autofocus: false,
          logLevel: 'ERROR',
          data,
          tools,
          inlineToolbar: ['marker', 'inlineCode', 'link'],
          onReady: () => {
            if (status) status.textContent = 'Editör hazır';
            holder.classList.add('is-ready');
          },
          onChange: async () => {
            try { input.value = JSON.stringify(await editor.save()); } catch (_) {}
          }
        });
      } catch (error) {
        console.error('Editor.js başlatılamadı:', error);
        if (status) status.textContent = 'Editör başlatılamadı';
      }

      editor?.isReady?.catch(error => {
        console.error('Editor.js yüklenemedi:', error);
        if (status) status.textContent = 'Editör yüklenemedi';
        holder.innerHTML = '<p class="post-create-simple__editor-error">Editör başlatılamadı. Lütfen sayfayı yenileyin.</p>';
      });

      document.querySelectorAll('[data-editor-tool]').forEach(button => {
        button.addEventListener('click', async () => {
          if (!editor) return;
          try {
            await editor.isReady;
            const type = button.dataset.editorTool;
            if (type === 'paragraph') editor.blocks.insert('paragraph', { text: '' });
            else if (type === 'header' && tools.header) editor.blocks.insert('header', { text: '', level: 2 });
            else if (type === 'list' && tools.list) editor.blocks.insert('list', { style: 'unordered', items: [''] });
            else if (type === 'quote' && tools.quote) editor.blocks.insert('quote', { text: '', caption: '' });
            else if (type === 'embed' && tools.embed) editor.blocks.insert('embed', { service: '', source: '', embed: '', width: 0, height: 0 });
            else return;
            holder.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } catch (error) { console.error('Editör bloğu eklenemedi:', error); }
        });
      });

      form.addEventListener('submit', async event => {
        event.preventDefault();
        try {
          await editor.isReady;
          input.value = JSON.stringify(await editor.save());
          HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
          console.error('Editor.js kaydı başarısız:', error);
          alert('Yazı içeriği hazırlanamadı. Lütfen tekrar deneyin.');
        }
      });
    })();
    </script>
@endpush
