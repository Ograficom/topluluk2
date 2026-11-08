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

            <input type="hidden" id="post-body" name="body" value="{{ old('body', $post->body ?? '') }}">
            <div id="post-editorjs" class="post-editorjs post-create-simple__editor" aria-label="Yazı içeriği"></div>

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

@push('head')
<style>
.post-create-simple { display:block; }
.post-create-simple__author { display:flex; align-items:center; gap:10px; margin-bottom:18px; }
.post-create-simple__author img { width:38px; height:38px; border-radius:50%; object-fit:cover; }
.post-create-simple__title { width:100%; border:0; outline:0; padding:8px 0; font-size:1.45rem; font-weight:700; color:var(--title-color); background:transparent; }
.post-create-simple__excerpt { width:100%; min-height:58px; margin:8px 0 14px; padding:10px 0; border:0; border-bottom:1px solid var(--border-color); resize:vertical; background:transparent; }
.post-create-simple__editor { min-height:220px; border:0; border-bottom:1px solid var(--border-color); border-radius:0; padding:14px 0; }
.post-create-simple__media { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:16px 0; border-bottom:1px solid var(--border-color); }
.post-create-simple__media > input[type=file] { max-width:100%; font-size:12px; }
.post-create-simple__file { display:inline-flex; align-items:center; gap:6px; color:var(--text-color); font-size:13px; font-weight:600; cursor:pointer; }
.post-create-simple__file .icon { color:var(--color-primary,#1769ff); }
.post-create-simple__existing-media { display:flex; flex-wrap:wrap; gap:10px; width:100%; }
.post-create-simple__existing-media img { display:block; width:76px; height:60px; object-fit:cover; border-radius:7px; }
.post-create-simple__options { padding:14px 0; border-bottom:1px solid var(--border-color); }
.post-create-simple__options summary { cursor:pointer; font-weight:600; }
.post-create-simple__options label:not(.post-create-simple__adult) { display:block; margin-top:14px; font-size:13px; }
.post-create-simple__options select,.post-create-simple__options > input { width:100%; margin-top:6px; }
.post-create-simple__adult { display:flex; align-items:center; gap:8px; margin-top:14px; }
.post-create-simple__footer { display:flex; align-items:center; justify-content:flex-end; gap:16px; padding-top:16px; }
.post-create-simple__footer > a { color:var(--color-gray); }
@media(max-width:600px) {
 .post-create-simple .addpost__form { padding:16px; }
 .post-create-simple__media { align-items:flex-start; flex-direction:column; }
 .post-create-simple__footer { position:sticky; bottom:0; background:var(--white-900,#fff); padding:12px 0 calc(12px + env(safe-area-inset-bottom)); }
 .post-create-simple__footer .btn { min-width:112px; }
}
</style>
@endpush

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
      if (!input || !holder || !form || !window.EditorJS) return;

      const initial = input.value.trim();
      let data = { blocks: [] };
      if (initial) {
        try {
          const parsed = JSON.parse(initial);
          if (parsed && Array.isArray(parsed.blocks)) data = parsed;
          else data = { blocks: [{ type: 'paragraph', data: { text: initial.replace(/\\r?\\n/g, '<br>') } }] };
        } catch (_) {
          data = { blocks: initial.split(/\\n{2,}/).filter(Boolean).map(text => ({ type: 'paragraph', data: { text } })) };
        }
      }

      const editor = new EditorJS({
        holder: holder,
        placeholder: 'Hikâyeni burada anlat...',
        minHeight: 240,
        data,
        tools: {
          header: { class: window.Header, inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } },
          list: { class: window.EditorjsList, inlineToolbar: true, config: { defaultStyle: 'unordered' } },
          quote: { class: window.Quote, inlineToolbar: true, config: { quotePlaceholder: 'Alıntıyı yaz', captionPlaceholder: 'Kaynak' } },
          code: { class: window.CodeTool },
          delimiter: window.Delimiter,
          table: { class: window.Table, inlineToolbar: true, config: { rows: 2, cols: 3 } },
          inlineCode: window.InlineCode,
          marker: window.Marker,
          embed: {
            class: window.Embed,
            config: {
              services: [
                { name: 'youtube', regex: /(?:https?:\\/\\/)?(?:www\\.)?(?:youtube\\.com\\/(?:watch\\?v=|shorts\\/)|youtu\\.be\\/)([a-zA-Z0-9_-]{11})/, embedUrl: 'https://www.youtube.com/embed/<%= remote_id %>', html: '<iframe width="100%" height="360" src="<%= embed %>" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>', height: 360, width: 640 },
                { name: 'facebook', regex: /https?:\\/\\/(?:www\\.)?facebook\\.com\\/.+/, embedUrl: 'https://www.facebook.com/plugins/video.php?href=<%= encodeURIComponent(url) %>&show_text=0', html: '<iframe width="100%" height="420" src="<%= embed %>" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen></iframe>', height: 420, width: 640 },
                { name: 'x', regex: /https?:\\/\\/(?:www\\.)?(?:x\\.com|twitter\\.com)\\/.+\\/status\\/(\\d+)/, embedUrl: 'https://platform.twitter.com/embed/Tweet.html?id=<%= remote_id %>', html: '<iframe width="100%" height="550" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 550, width: 550 },
                { name: 'instagram', regex: /https?:\\/\\/(?:www\\.)?instagram\\.com\\/(?:p|reel|tv)\\/([a-zA-Z0-9_-]+)/, embedUrl: 'https://www.instagram.com/p/<%= remote_id %>/embed', html: '<iframe width="100%" height="540" src="<%= embed %>" frameborder="0" scrolling="no"></iframe>', height: 540, width: 400 },
                { name: 'tiktok', regex: /https?:\\/\\/(?:www\\.|vm\\.)?tiktok\\.com\\/(?:@[^/]+\\/video\\/|t/)?([a-zA-Z0-9]+)/, embedUrl: 'https://www.tiktok.com/embed/v2/<%= remote_id %>', html: '<iframe width="100%" height="740" src="<%= embed %>" frameborder="0" allowfullscreen></iframe>', height: 740, width: 325 },
                { name: 'reddit', regex: /https?:\\/\\/(?:www\\.)?reddit\\.com\\/r\\/.+\\/comments\\/([a-zA-Z0-9]+)/, embedUrl: 'https://www.redditmedia.com/r/<%= remote_id %>/comments/<%= remote_id %>/embed', html: '<iframe width="100%" height="500" src="<%= embed %>" frameborder="0" scrolling="no"></iframe>', height: 500, width: 640 }
              ]
            }
          }
        },
        onChange: async () => {
          try { input.value = JSON.stringify(await editor.save()); } catch (_) {}
        }
      });

      form.addEventListener('submit', async event => {
        event.preventDefault();
        try {
          const output = await editor.save();
          input.value = JSON.stringify(output);
          HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
          console.error('Editor.js kaydı başarısız:', error);
          alert('Yazı içeriği hazırlanamadı. Lütfen tekrar deneyin.');
        }
      });
    })();
    </script>
@endpush
