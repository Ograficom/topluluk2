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
        <form class="addpost__form card card-body post-create-modal" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($post))
                @method('PATCH')
            @endif

            <header class="addpost__modal-head">
                <strong>{{ isset($post) ? 'Yayını düzenle' : 'Yeni yayın' }}</strong>
                <div class="addpost__modal-controls"><button type="button" class="addpost__modal-expand" data-post-composer-expand aria-label="Genişlet" aria-pressed="false"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M8 4H4v4M16 4h4v4M20 16v4h-4M4 16v4h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button><a href="{{ route('posts.index') }}" class="addpost__modal-close" aria-label="Kapat"><x-repost-icon name="close" size="22" /></a></div>
            </header>
            <div class="addpost__header">
                <img class="addpost__header-avatar" src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                <div class="addpost__author">
                    <span class="addpost__author-name">{{ $user->displayName() }}</span>
                    <select id="post-channel" name="channel_id" aria-label="Bir kanal seçin">
                        <option value="">Bir kanal seçin</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->id }}" @selected((string) old('channel_id', $post->channel_id ?? '') === (string) $channel->id)>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="post-composer-field">
                <label class="post-composer-label" for="post-title">Yazı başlığı</label>
                <input class="post-create-simple__title addpost__title" id="post-title" name="title" type="text"
                    value="{{ old('title', $post->title ?? '') }}" maxlength="255"
                    placeholder="Yazının başlığını yaz" required>
                <div class="post-composer-count"><span>Arama ve paylaşım için açık bir başlık seç.</span><span data-count-for="post-title">0 / 60</span></div>
            </div>

            <div class="post-composer-field">
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
                    <div id="post-editorjs" class="post-editorjs post-create-simple__editor" aria-label="Yazı içeriği" data-placeholder="Yazmaya başla..."></div>
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
                <div class="post-create-simple__media addpost__actions">
                    <label for="post-cover" class="post-create-simple__file"><x-repost-icon name="image" size="19" /> Kapak görseli</label>
                    <input id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-post-cover-input>
                    <div class="post-composer-cover-preview" data-post-cover-preview aria-live="polite"></div>
                    @if (isset($post) && $post->coverImageUrl())
                        <div class="post-composer-existing-cover">
                            <img src="{{ $post->coverImageUrl() }}" alt="Mevcut kapak görseli">
                            <label class="small"><input type="checkbox" name="remove_cover" value="1"> Kapağı kaldır</label>
                        </div>
                    @endif

                    <label for="post-media" class="post-create-simple__file"><x-repost-icon name="image" size="19" /> Fotoğraf ekle</label>
                    <input id="post-media" name="media[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-post-gallery-input>
                    <div class="post-composer-file-list" data-post-gallery-list aria-live="polite"></div>
                    @if (isset($post) && filled($post->media))
                        <div class="post-create-simple__existing-media">
                            @foreach ($post->media as $mediaPath)
                                <label><img src="{{ asset('storage/'.$mediaPath) }}" alt=""><input type="checkbox" name="remove_media[]" value="{{ $mediaPath }}"> Kaldır</label>
                            @endforeach
                        </div>
                    @endif

                    <label for="post-video-url" class="post-create-simple__file"><x-repost-icon name="video" size="19" /> Video bağlantısı</label>
                    <input id="post-video-url" name="video_url" type="url" value="{{ old('video_url', $post->video_url ?? '') }}"
                        maxlength="2048" placeholder="YouTube, Instagram, TikTok bağlantısı">
                    <label for="post-video-file" class="post-create-simple__file"><x-repost-icon name="file-text" size="19" /> Video dosyası yükle</label>
                    <input id="post-video-file" name="video_file" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-matroska">
                    @if (isset($post) && $post->video_path)
                        <p class="small color-gray">Mevcut video kayıtlı. Yeni video seçersen değiştirilir.</p>
                    @endif
                </div>
            </section>

            <details class="post-create-simple__options post-composer-advanced">
                <summary><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/></svg> Diğer ayarlar</summary>
                <div class="post-composer-advanced-content">
                    <label for="tags">Etiketler</label>
                    <input id="tags" name="tags" type="text" value="{{ old('tags', isset($post) ? implode(', ', $post->tags ?? []) : '') }}"
                        maxlength="500" placeholder="Örn. teknoloji, gezi, yazılım">
                    <p class="post-composer-field-help">Etiketleri virgülle ayır.</p>
            <details class="post-composer-seo">
                <summary>
                    <span><x-repost-icon name="search" size="18" /> SEO ayarları</span>
                    <small>Google arama görünümü</small>
                </summary>
                <div class="post-composer-seo-content">
                    <label class="post-composer-label" for="post-meta-description">Meta açıklaması <span>İsteğe bağlı</span></label>
                    <textarea class="post-create-simple__seo" id="post-meta-description" name="meta_description" rows="3" maxlength="500"
                        placeholder="Google'da görünecek açıklama...">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                    <div class="post-composer-count"><span>150–160 karakter hedefle.</span><span data-count-for="post-meta-description">0 / 160</span></div>
                    <div class="post-composer-google-preview" aria-live="polite">
                        <span>ografi.com › yazı</span>
                        <strong data-seo-preview-title>{{ old('title', $post->title ?? '') ?: 'Yazı başlığın burada görünecek' }}</strong>
                        <p data-seo-preview-description>{{ old('meta_description', $post->meta_description ?? '') ?: (old('excerpt', $post->excerpt ?? '') ?: 'Yazının açıklaması burada görünecek.') }}</p>
                    </div>
                </div>
            </details>
                    <label class="post-create-simple__adult"><input id="post-adult" type="checkbox" name="is_adult" value="1" @checked(old('is_adult', $post->is_adult ?? false))> 18+ içerik</label>
                </div>
            </details>

            <footer class="post-create-simple__footer addpost__footer">
                <a href="{{ route('posts.index') }}">Vazgeç</a>
                <button class="btn btn-primary" type="submit">{{ isset($post) ? 'Kaydet' : 'Yayınla' }}</button>
            </footer>
        </form>
    </div>

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
        if (galleryInput && galleryList) {
            galleryInput.addEventListener('change', () => {
                galleryList.replaceChildren();
                Array.from(galleryInput.files || []).forEach(file => {
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
            });
        }

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
