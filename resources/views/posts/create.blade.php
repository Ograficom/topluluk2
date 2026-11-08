@extends('layouts.app')

@section('title', isset($post) ? 'Yazıyı düzenle' : 'Yazı oluştur')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/libs/tagify/tagify.css') }}">
    <script src="{{ asset('assets/libs/tagify/tagify.js') }}"></script>
@endpush

@section('content')
    <div class="d-flex justify-content-end mb-3"><a class="btn btn-secondary-ghost btn-sm" href="{{ route('advertisements.create') }}">Reklam oluştur</a></div>
    <header class="feed-heading post-composer-heading">
        <div>
            <span class="post-composer-eyebrow">{{ isset($post) ? 'İÇERİK YÖNETİMİ' : 'O G R A F I · TOPLULUK' }}</span>
            <h1 class="h3">{{ isset($post) ? 'Yazıyı düzenle' : 'Yazı oluştur' }}</h1>
            <p class="color-gray">{{ isset($post) ? 'Yazındaki bilgileri güncelle ve yeniden kaydet.' : 'Fikrini, deneyimini veya hikâyeni toplulukla paylaş.' }}</p>
        </div>
    </header>

    @if ($errors->any())
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="addpost main__grid-feed">
        <form class="addpost__form card card-body" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($post)) @method('PATCH') @endif
            <header class="addpost__header">
                <img class="addpost__header-avatar" src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                <div class="addpost__author">
                    <span class="addpost__author-name">{{ $user->displayName() }}</span>
                    <span class="small color-gray">Yazı yayınlandıktan sonra profilinde görünür.</span>
                </div>
            </header>

            <div class="addpost__content">
                <div class="post-composer-section-heading">
                    <span class="post-composer-step">01</span>
                    <div><strong>İçeriğini oluştur</strong><span>Başlık ve metinle anlatmak istediklerini paylaş.</span></div>
                </div>
                <div class="addpost__intro small color-gray">Yeni bir yayın oluştur</div>
                <ul class="addpost__fields">
                    <li>
                        <label class="addpost__label" for="post-title">Başlık</label>
                        <input class="addpost__title" id="post-title" name="title" type="text" value="{{ old('title', $post->title ?? '') }}" maxlength="255" placeholder="Yazı başlığı" required data-post-title-input>
                        <div class="post-composer-field-meta"><span>Okuyucunun ilk göreceği başlık.</span><span><b data-title-count>0</b> / 255</span></div>
                    </li>
                    <li>
                        <label class="addpost__label" for="post-excerpt">Kısa açıklama</label>
                        <textarea id="post-excerpt" name="excerpt" rows="2" maxlength="500" placeholder="Yazını kısaca tanıt (en fazla 500 karakter)" required data-post-excerpt-input>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                        <div class="post-composer-field-meta"><span>Akışta başlığın altında görünür.</span><span><b data-excerpt-count>0</b> / 500</span></div>
                    </li>
                    <li class="addpost__editor">
                        <label class="addpost__label" for="post-body">Yazı içeriği</label>
                        <textarea id="post-body" name="body" rows="14" maxlength="100000" placeholder="Hikâyeni burada anlat..." required>{{ old('body', $post->body ?? '') }}</textarea>
                        <p class="post-composer-hint">Paragraflar kullan, önemli noktaları açıkça anlat. Yayınlandıktan sonra yazı detay sayfanda görüntülenir.</p>
                    </li>
                    <li>
                        <label class="addpost__label" for="post-video-url">Video bağlantısı (HTTPS)</label>
                        <input id="post-video-url" name="video_url" type="url" value="{{ old('video_url', $post->video_url ?? '') }}" maxlength="2048" placeholder="https://...">
                    </li>
                    <li class="post-composer-divider"><span class="post-composer-step">02</span><div><strong>Görsel ve video</strong><span>Yazını fotoğraflar ve video bağlantısıyla zenginleştir.</span></div></li>
                    <li class="addpost__actions">
                        <label class="addpost__label" for="post-media">Galeri görselleri (en fazla 10)</label>
                        <p class="post-composer-hint">JPG, PNG, GIF veya WebP · Her dosya en fazla 5 MB.</p>
                        <input class="form-attachments" id="post-media" name="media[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-post-gallery-input>
                        <div class="post-composer-file-list" data-post-gallery-list aria-live="polite"></div>
                        @if (isset($post) && filled($post->media))
                            <div class="small color-gray">Mevcut galeri görselleri</div>
                            @foreach ($post->media as $mediaPath)
                                <label class="form-check-label">
                                    <img src="{{ asset('storage/'.$mediaPath) }}" alt="" class="post__media-image" loading="lazy">
                                    <input class="form-check-input" type="checkbox" name="remove_media[]" value="{{ $mediaPath }}">
                                    <span>Bu görseli kaldır</span>
                                </label>
                            @endforeach
                        @endif
                    </li>
                    <li class="addpost__actions">
                        <label class="addpost__label" for="post-cover">Kapak görseli</label>
                        <p class="post-composer-hint">Yazının kapak görseli akışta ve detay sayfasında kullanılır.</p>
                        <input class="form-attachments" id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-post-cover-input>
                        <div class="post-composer-cover-preview" data-post-cover-preview aria-live="polite"></div>
                        @if (isset($post) && $post->coverImageUrl())
                            <div class="small color-gray">Mevcut kapak görseli</div>
                            <img src="{{ $post->coverImageUrl() }}" alt="" style="max-width:180px;height:auto;">
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="remove_cover" value="1"> <span>Kapak görselini kaldır</span></label>
                        @endif
                    </li>
                    <li class="post-composer-divider"><span class="post-composer-step">03</span><div><strong>Keşfedilmeyi kolaylaştır</strong><span>Kategori ve etiketlerle içeriğini doğru okuyucularla buluştur.</span></div></li>
                    <li>
                        <label class="addpost__label" for="tags">Etiketler</label>
                        <p class="post-composer-hint">Birden fazla etiketi virgülle ayır. Örnek: teknoloji, gezi, deneyim.</p>
                        <input class="addpost__ultratag" id="tags" name="tags" type="text" value="{{ old('tags', isset($post) ? implode(', ', $post->tags ?? []) : '') }}" maxlength="500" placeholder="Etiketleri virgülle ayır (ör. gezi, teknoloji)">
                    </li>
                    <li>
                        <label class="addpost__label" for="post-channel">Kategori</label>
                        <p class="post-composer-hint">Yazının konusuna en uygun kategoriyi seç.</p>
                        <select id="post-channel" name="channel_id">
                            <option value="">Kategori seçmeden yayınla</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel->id }}" @selected((string) old('channel_id', $post->channel_id ?? '') === (string) $channel->id)>{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </li>
                </ul>

                <div class="post-composer-divider"><span class="post-composer-step">04</span><div><strong>Yayın ayarları</strong><span>İçeriğinin hedef kitlesini belirle.</span></div></div>
                <div class="addpost__settings">
                    <div class="checkbox">
                        <label class="form-check-label" for="post-adult">
                            <input class="form-check-input" id="post-adult" type="checkbox" name="is_adult" value="1" @checked(old('is_adult', $post->is_adult ?? false))>
                            <span>18+ içerik olarak işaretle</span>
                        </label>
                    </div>
                </div>
                <div class="post-composer-submit-note">Yayınla butonuna bastığında içeriğin OgraFi topluluğunda paylaşılır.</div>
                <footer class="addpost__footer">
                    <button class="btn btn-primary" type="submit">{{ isset($post) ? 'Değişiklikleri kaydet' : 'Yayınla' }}</button>
                    <a class="btn btn-secondary-ghost" href="{{ route('posts.index') }}">Vazgeç</a>
                </footer>
            </div>
        </form>

        <aside class="addpost__sidebar">
            <section class="widget card card-body post-composer-checklist">
                <header><h2 class="widget__title h6">Yayınlamadan önce</h2></header>
                <div class="widget__content">
                    <ul>
                        <li>Açık ve açıklayıcı bir başlık seç.</li>
                        <li>İçeriğine uygun bir kategori belirle.</li>
                        <li>Yetişkinlere yönelik içeriği 18+ olarak işaretle.</li>
                    </ul>
                </div>
                <div class="post-composer-preview" aria-live="polite">
                    <span class="post-composer-eyebrow">CANLI ÖNİZLEME</span>
                    <h3 data-post-preview-title>{{ old('title', $post->title ?? 'Yazı başlığın burada görünecek') }}</h3>
                    <p data-post-preview-excerpt>{{ old('excerpt', $post->excerpt ?? 'Kısa açıklaman burada görünecek.') }}</p>
                </div>
                @if (auth()->user()->is_admin)
                    <footer class="widget__footer">
                        <a class="link-accent" href="{{ route('channels.create') }}">Yeni kategori oluştur</a>
                    </footer>
                @endif
            </section>
            @include('layouts._footer')
        </aside>
    </div>
@endsection
