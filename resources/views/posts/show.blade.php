@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->meta_description ?: $post->excerpt)

@section('canonical', route('posts.show', $post))
@section('og_type', 'article')
@if ($post->coverImageUrl())
    @section('seo_image', $post->coverImageUrl())
@endif

@push('head')
    <style>
        .similar-posts{display:block;width:100%;max-width:none;margin:24px 0 0;padding:0;background:transparent;border:0;box-shadow:none;border-radius:0}
        .similar-posts__grid{display:flex;flex-direction:column;gap:14px;width:100%;min-width:0}
        .similar-posts__grid>.card.post{width:100%;max-width:none;min-width:0;margin:0}
        .editorjs-content :is(b,strong){font-weight:700;}
        .editorjs-content :is(i,em){font-style:italic;}
        .editorjs-content u{text-decoration:underline;}
        .editorjs-content :is(s,strike,del){text-decoration:line-through;}
        .editorjs-content mark{background:#fff0a3;color:#202633;}
        .editorjs-content code.inline-code{background:#f0f3f7;border-radius:4px;padding:1px 4px;}
        .editorjs-content a:not([data-fancybox]){text-decoration:underline;}
        .editorjs-content .post-inline-gallery{display:grid;gap:12px;margin:16px 0;clear:both;}
        .editorjs-content .post-inline-gallery figure{margin:0;min-width:0;}
        .editorjs-content .post-inline-gallery a{display:block;}
        .editorjs-content .post-inline-gallery img{display:block;width:100%;height:auto;max-height:650px;object-fit:contain;border-radius:12px;}
    </style>

    <meta property="article:published_time" content="{{ $post->published_at?->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at?->toAtomString() }}">
    @if ($post->user)
        <meta property="article:author" content="{{ $post->user->displayName() }}">
    @endif
    @if ($post->channel)
        <meta property="article:section" content="{{ $post->channel->name }}">
    @endif
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('posts.show', $post)],
            'headline' => $post->title,
            'description' => \Illuminate\Support\Str::limit(trim(strip_tags($post->meta_description ?: $post->excerpt)), 300, ''),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => ($post->updated_at ?: $post->published_at)?->toAtomString(),
            'author' => $post->user ? ['@type' => 'Person', 'name' => $post->user->displayName(), 'url' => route('profiles.show', $post->user)] : ['@type' => 'Organization', 'name' => 'OgraFi'],
            'publisher' => ['@type' => 'Organization', 'name' => 'OgraFi', 'url' => url('/')],
            'image' => $post->coverImageUrl() ? [$post->coverImageUrl()] : [asset('assets/img/meta/logo.svg')],
            'articleSection' => $post->channel?->name,
            'inLanguage' => 'tr-TR',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endpush


@section('content')
    <div class="speedbar">
        <a href="{{ route('posts.index') }}">OgraFi</a>
        @if ($post->channel)
            / <a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>
        @endif
        / <span>{{ $post->title }}</span>
    </div>

    <article class="card card-body">
        <header class="post__header">
            <div class="user-box">
                @if ($post->user)
                    <a href="{{ route('profiles.show', $post->user) }}" class="avatar"><img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40"></a>
                @else
                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                @endif
                <div class="user-box-content">
                    <span class="name">
                        @if ($post->user)
                            <a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->displayName() }}</a>
                        @else
                            <span class="name-text">Repost yazarı</span>
                        @endif
                        @if ($post->user?->hasVerifiedCompanyBadge())
                    <x-company-verified-badge size="14" />
                @endif
                @if ($post->user?->hasVerifiedBadge())<span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>@endif
                    </span>
                    <div class="meta">
                        @if ($post->channel)<a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>@endif
                        <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
            <div class="post-actions-menu" data-post-actions-menu>
                <button class="btn btn-secondary-ghost btn-circle btn-xs" type="button" data-post-actions-toggle aria-expanded="false" aria-label="Yazı menüsünü aç">
                    <x-repost-icon name="more" size="16" />
                </button>
                <ul class="post-actions-menu__list" data-post-actions-list hidden>
                    @if (auth()->user()?->is($post->user) || auth()->user()?->is_admin)
                        <li><a href="{{ route('posts.edit', $post) }}">Yazıyı düzenle</a></li>
                    @endif
                    <li><button type="button" class="post-report-trigger" data-post-report-open>Yazıyı bildir</button></li>
                </ul>
            </div>
        </header>

        <h1 class="post__title-full">{{ $post->title }}</h1>

        @if ($post->coverImageUrl() || filled($post->video_url) || filled($post->video_path))
            <div class="post__media-full">@include('posts._media', ['post' => $post, 'coverOnly' => true])</div>
        @endif

        <section class="editor editorjs-content">
            @php
                $editorContent = json_decode($post->body, true);
                $editorBlocks = is_array($editorContent) && isset($editorContent['blocks']) && is_array($editorContent['blocks']) ? array_values(array_filter(
                    $editorContent['blocks'],
                    fn ($block) => ! str_starts_with(
                        trim((string) data_get($block, 'data.text', '')),
                        'Bu içerik RSS kaynağından alınmıştır. Kaynak:'
                    )
                )) : null;
                $inlineGalleryUrls = array_values(array_diff($post->galleryImageUrls(), array_filter([$post->coverImageUrl()])));
                $galleryHasPosition = collect($editorBlocks ?? [])->contains(fn ($block) => ($block['type'] ?? '') === 'postGallery');
                $inlineGalleryShown = false;
            @endphp
            @if ($editorBlocks !== null)
                @foreach ($editorBlocks as $block)
                    @php $blockData = $block['data'] ?? []; @endphp
                    @switch($block['type'] ?? '')
                        @case('header')
                            <h{{ min(4, max(2, (int) ($blockData['level'] ?? 2))) }}>{!! \App\Support\EditorInlineHtml::render($blockData['text'] ?? '') !!}</h{{ min(4, max(2, (int) ($blockData['level'] ?? 2))) }}>
                            @break
                        @case('paragraph')
                            @if (filled($blockData['text'] ?? ''))<p>{!! \App\Support\EditorInlineHtml::render($blockData['text']) !!}</p>@endif
                            @if (! $galleryHasPosition && ! $inlineGalleryShown && filled($blockData['text'] ?? ''))
                                @include('posts._inline-gallery', ['urls' => $inlineGalleryUrls, 'post' => $post])
                                @php $inlineGalleryShown = true; @endphp
                            @endif
                            @break
                        @case('postGallery')
                            @if (! $inlineGalleryShown)
                                @include('posts._inline-gallery', ['urls' => $inlineGalleryUrls, 'post' => $post])
                                @php $inlineGalleryShown = true; @endphp
                            @endif
                            @break
                        @case('list')
                            @php
                                $listStyle = $blockData['style'] ?? 'unordered';
                                $listTag = $listStyle === 'ordered' ? 'ol' : 'ul';
                            @endphp
                            @if ($listStyle === 'checklist')
                                <ul style="list-style:none;padding-inline-start:0">
                                    @foreach (($blockData['items'] ?? []) as $item)
                                        <li style="display:flex;align-items:baseline;gap:8px">
                                            <span role="img" aria-label="{{ data_get($item, 'meta.checked', false) ? 'Tamamlandı' : 'Tamamlanmadı' }}" style="flex-shrink:0">{{ data_get($item, 'meta.checked', false) ? '☑' : '☐' }}</span>
                                            <span>{!! \App\Support\EditorInlineHtml::render(is_array($item) ? ($item['content'] ?? '') : $item) !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <{{ $listTag }}>
                                    @foreach (($blockData['items'] ?? []) as $item)
                                        <li>{!! \App\Support\EditorInlineHtml::render(is_array($item) ? ($item['content'] ?? '') : $item) !!}</li>
                                    @endforeach
                                </{{ $listTag }}>
                            @endif
                            @break
                        @case('quote')
                            <blockquote><p>{!! \App\Support\EditorInlineHtml::render($blockData['text'] ?? '') !!}</p>@if (filled($blockData['caption'] ?? ''))<cite>{!! \App\Support\EditorInlineHtml::render($blockData['caption']) !!}</cite>@endif</blockquote>
                            @break
                        @case('code')
                            <pre><code>{{ $blockData['code'] ?? '' }}</code></pre>
                            @break
                        @case('embed')
                            @php
                                $embedSource = $blockData['source'] ?? '';
                                $embedHost = strtolower(parse_url($embedSource, PHP_URL_HOST) ?: '');
                                $safeEmbedUrl = null;
                                if (in_array($embedHost, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'], true)) {
                                    preg_match('~(?:v=|youtu\\.be/|shorts/)([a-zA-Z0-9_-]{11})~', $embedSource, $match);
                                    if (!empty($match[1])) $safeEmbedUrl = 'https://www.youtube-nocookie.com/embed/'.$match[1];
                                } elseif (in_array($embedHost, ['facebook.com', 'www.facebook.com', 'm.facebook.com'], true)) {
                                    $safeEmbedUrl = 'https://www.facebook.com/plugins/video.php?href='.urlencode($embedSource).'&show_text=0';
                                } elseif (in_array($embedHost, ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'], true)) {
                                    if (preg_match('~/status/(\\d+)~', $embedSource, $match)) $safeEmbedUrl = 'https://platform.twitter.com/embed/Tweet.html?id='.$match[1];
                                } elseif (in_array($embedHost, ['instagram.com', 'www.instagram.com'], true)) {
                                    if (preg_match('~/(?:p|reel|tv)/([a-zA-Z0-9_-]+)~', $embedSource, $match)) $safeEmbedUrl = 'https://www.instagram.com/p/'.$match[1].'/embed';
                                } elseif (in_array($embedHost, ['tiktok.com', 'www.tiktok.com', 'vm.tiktok.com'], true)) {
                                    if (preg_match('~/video/(\\d+)~', $embedSource, $match)) $safeEmbedUrl = 'https://www.tiktok.com/embed/v2/'.$match[1];
                                } elseif (in_array($embedHost, ['reddit.com', 'www.reddit.com', 'old.reddit.com'], true)) {
                                    if (preg_match('~(/r/[^/]+/comments/[^/]+)~', $embedSource, $match)) $safeEmbedUrl = 'https://www.redditmedia.com'.$match[1].'/embed';
                                }
                            @endphp
                            @if ($safeEmbedUrl)
                                <div class="post-embedded-video">
                                    <iframe src="{{ $safeEmbedUrl }}" title="Gömülü video" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                </div>
                            @endif
                            @break
                        @case('delimiter')
                            <hr>
                            @break
                        @case('table')
                            <div class="editorjs-table-wrap"><table>
                                @foreach (($blockData['content'] ?? []) as $row)
                                    <tr>@foreach ($row as $cell)<td>{!! \App\Support\EditorInlineHtml::render($cell) !!}</td>@endforeach</tr>
                                @endforeach
                            </table></div>
                            @break
                    @endswitch
                @endforeach
            @else
                @foreach (preg_split('/\\R{2,}/', trim($post->body)) ?: [] as $paragraph)
                    @if (filled($paragraph) && ! str_starts_with(trim($paragraph), 'Bu içerik RSS kaynağından alınmıştır. Kaynak:'))
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                        @if (! $inlineGalleryShown)
                            @include('posts._inline-gallery', ['urls' => $inlineGalleryUrls, 'post' => $post])
                            @php $inlineGalleryShown = true; @endphp
                        @endif
                    @endif
                @endforeach
            @endif
            @unless ($inlineGalleryShown)
                @include('posts._inline-gallery', ['urls' => $inlineGalleryUrls, 'post' => $post])
            @endunless
        </section>

        @if ($rssItem && filled($rssItem->link))
            @php
                $rssSourceUrl = $rssItem->link;
                $rssSourceHost = parse_url($rssSourceUrl, PHP_URL_HOST);
                $rssSourceHost = $rssSourceHost ? preg_replace('/^www\\./i', '', $rssSourceHost) : $rssSourceUrl;
            @endphp
            <aside class="post-source-card" aria-label="İçerik kaynağı">
                <div class="post-source-card__meta">KAYNAK</div>
                <a class="post-source-card__link" href="{{ $rssSourceUrl }}" target="_blank" rel="nofollow noopener noreferrer">
                    <span class="post-source-card__text">
                        <strong>{{ $rssSourceHost }}</strong>
                    </span>
                    <x-repost-icon name="external-link" size="16" />
                </a>
            </aside>
        @endif

        @if ($post->tagRecords->isNotEmpty())
            <div class="post__tags post__tags-full">
                @foreach ($post->tagRecords as $tag)
                    <a href="{{ route('tags.show', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
                @endforeach
            </div>
        @elseif ($post->tags)
            <div class="post__tags post__tags-full">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('tags.show', ['tag' => \Illuminate\Support\Str::slug($tag)]) }}">{{ $tag }}</a>
                @endforeach
            </div>
        @endif

        <footer class="post__footer post__footer-full">
            <div class="post__footer-stats small color-gray">
                <span>{{ number_format($post->likes_count) }} beğeni</span>
                <span>{{ number_format($post->views) }} görüntülenme</span>
            </div>
            @if ($post->channel)<a class="link small" href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>@endif
        </footer>
        @include('posts._interactions', ['post' => $post, 'showPostStatistics' => true])
    </article>

    <section class="card card-body" id="comments">
        <h2 class="h5">Yorumlar ({{ $post->comments_count }})</h2>
        @if ($errors->any())
            <div class="color-red" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
        @endif
        @auth
            <form class="addcomment" action="{{ route('posts.comments.store', $post) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $replyTo?->id }}">
                @if ($replyTo)
                    <p class="small color-gray">{{ $replyTo->user?->displayName() ?? 'Repost kullanıcısı' }} kullanıcısına yanıt yazıyorsunuz. <a class="link" href="{{ route('posts.show', $post) }}#comments">İptal</a></p>
                @endif
                <textarea id="comment-body" name="body" rows="8" maxlength="5000" placeholder="Yorum yaz" aria-label="Yorum yaz">{{ old('body') }}</textarea>
                <input id="comment-images" type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden>
                <div><button class="btn-link link" type="button" data-comment-images-trigger>Resim yükle</button> <span class="small color-gray" data-comment-images-selection aria-live="polite"></span></div>
                <div><button class="btn btn-primary" type="submit">Yorum gönder</button></div>
            </form>
        @else
            <form class="addcomment" action="{{ route('login') }}" method="GET">
                <textarea id="comment-body" rows="8" placeholder="Yorum yazabilmek için giriş yapın" aria-label="Yorum yazabilmek için giriş yapın" disabled></textarea>
                <div><button class="btn btn-primary" type="submit">Giriş yap ve yorum yaz</button></div>
            </form>
        @endauth
        <div class="post__comments comments-tree-list">
            @forelse ($post->comments as $comment)
                @include('posts._comment', ['comment' => $comment, 'post' => $post])
            @empty
                <p class="color-gray">Henüz yorum yok.</p>
            @endforelse
        </div>
        @if (auth()->user()?->is_admin)
            <form class="mass_comments_action" id="comment-moderation-form" action="{{ route('posts.comments.moderate', $post) }}" method="POST">
                @csrf
                <label for="post-comments-action">Seçili yorumlar:</label>
                <select class="form-select" id="post-comments-action" name="action" required>
                    <option value="">— İşlem —</option>
                    <option value="delete">Sil</option>
                </select>
                <button class="btn btn-primary btn-md" type="submit">Uygula</button>
            </form>
        @endif
    </section>

    @if ($similarPosts->isNotEmpty())
        <section class="similar-posts">
            <div class="similar-posts__grid">
                @foreach ($similarPosts as $similarPost)
                    @include('posts._card', ['post' => $similarPost])
                @endforeach
            </div>
        </section>
    @endif

    @php
        $statisticsLikes = (int) $post->likes_count;
        $statisticsDislikes = (int) $post->dislikes_count;
        $statisticsDate = $post->published_at ?? $post->created_at;
        $statistics = [
            'views' => ['Görüntülenme', (int) $post->views],
            'comments' => ['Yorumlar', (int) $post->comments_count],
            'ratings' => ['Değerlendirme', $statisticsLikes + $statisticsDislikes],
            'likes' => ['Beğeniler', $statisticsLikes],
            'dislikes' => ['Beğenmeme', $statisticsDislikes],
            'score' => ['Toplam puan', $statisticsLikes - $statisticsDislikes],
        ];
    @endphp
    <dialog id="post-statistics-dialog" class="post-statistics-dialog" aria-labelledby="post-statistics-title" aria-describedby="post-statistics-heading">
        <div class="post-statistics__header">
            <div>
                <h2 id="post-statistics-title">Gönderi istatistikleri</h2>
                <p id="post-statistics-heading">{{ $post->title }}</p>
            </div>
            <button class="post-statistics__close" type="button" data-post-statistics-close aria-label="İstatistik penceresini kapat" autofocus>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <dl class="post-statistics__grid">
            @foreach ($statistics as $key => [$label, $value])
                <div class="post-statistics__tile">
                    <dt>{{ $label }}</dt>
                    <dd data-post-stat="{{ $key }}">{{ number_format($value, 0, ',', '.') }}</dd>
                </div>
            @endforeach
        </dl>
        <dl class="post-statistics__details">
            <div class="post-statistics__row">
                <dt>Yazar</dt>
                <dd>
                    @if ($post->user)
                        <a href="{{ route('profiles.show', $post->user) }}" class="post-statistics__author">{{ $post->user->displayName() }}</a>
                        @if ($post->user->hasVerifiedCompanyBadge())<x-company-verified-badge size="14" />@endif
                        @if ($post->user->hasVerifiedBadge())<span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>@endif
                    @else
                        Repost yazarı
                    @endif
                </dd>
            </div>
            <div class="post-statistics__row">
                <dt>Tarih</dt>
                <dd><time datetime="{{ $statisticsDate->toAtomString() }}">{{ $statisticsDate->copy()->locale('tr')->translatedFormat('j F Y') }}</time></dd>
            </div>
        </dl>
    </dialog>
    <style>
        .post-statistics-trigger:hover,.post-statistics-trigger:focus-visible,.post-statistics-trigger[aria-expanded="true"]{color:var(--color-primary,#1769ff);background:var(--content-item-hover-bg,#f4f7fb)}
        .post-statistics-dialog{position:fixed;inset:0;margin:auto;box-sizing:border-box;width:min(356px,calc(100% - 32px));max-width:none;max-height:calc(100% - 32px);overflow:auto;padding:20px 18px 16px;border:0;border-radius:20px;background:var(--card-bg,#fff);color:var(--text-color,#212529);box-shadow:0 16px 48px rgba(15,23,42,.15);font:inherit;text-align:start}
        .post-statistics-dialog:not([open]){display:none}
        .post-statistics-dialog::backdrop{background:rgba(30,41,59,.48);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px)}
        html:has(.post-statistics-dialog[open]){overflow:hidden}
        .post-statistics__header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:18px}
        .post-statistics__header>div{min-width:0;flex:1}
        #post-statistics-title{margin:0 0 6px;font-size:12px;font-weight:600;line-height:1.3;color:var(--color-gray,#64748b)}
        #post-statistics-heading{margin:0;font-size:17px;font-weight:700;line-height:1.3;color:var(--title-color,#111);overflow-wrap:anywhere}
        .post-statistics__close{flex:0 0 32px;display:grid;place-items:center;width:32px;height:32px;margin:-6px -4px 0 0;padding:0;border:0;border-radius:50%;background:var(--gray-200,#e7ebef);color:var(--color-gray,#64748b);cursor:pointer}
        .post-statistics__close:hover{color:var(--color-primary,#1769ff)}
        .post-statistics__close:focus-visible,.post-statistics__author:focus-visible{outline:2px solid var(--color-primary,#1769ff);outline-offset:2px}
        .post-statistics__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:0 0 14px;padding:0}
        .post-statistics__tile{min-width:0;min-height:58px;padding:9px 10px;border-radius:14px;background:var(--gray-100,#f7f9fd);box-sizing:border-box}
        [data-theme="dark"] .post-statistics__tile{background:var(--gray-200,#232833)}
        .post-statistics__tile dt{margin:0 0 3px;color:var(--color-gray,#64748b);font-size:11px;font-weight:400;line-height:1.3;overflow-wrap:anywhere}
        .post-statistics__tile dd{margin:0;color:var(--title-color,#111);font-size:18px;font-weight:700;line-height:1.2;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
        .post-statistics__details{margin:0;padding:0}
        .post-statistics__row{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:46px;padding:12px 0;border-top:1px solid var(--border-color,#e7ebef);box-sizing:border-box}
        .post-statistics__row dt{flex:none;margin:0;color:var(--color-gray,#64748b);font-size:13px;font-weight:400}
        .post-statistics__row dd{display:flex;justify-content:flex-end;align-items:center;gap:4px;min-width:0;margin:0;font-size:12px;text-align:end;overflow-wrap:anywhere}
        .post-statistics__row dd .verify{flex:none}
        .post-statistics__author{min-width:0;color:var(--color-primary,#1769ff);text-decoration:none}
        @media(max-width:340px){.post-statistics-dialog{padding:16px 12px}.post-statistics__grid{gap:6px}.post-statistics__tile{padding:8px 6px}.post-statistics__tile dd{font-size:16px}}
    </style>
    <script>
        (() => {
            const dialog = document.getElementById('post-statistics-dialog');
            const opener = document.querySelector('[data-post-statistics-open]');
            if (!dialog || !opener) return;
            const closer = dialog.querySelector('[data-post-statistics-close]');
            opener.setAttribute('aria-expanded', 'false');
            opener.addEventListener('click', () => {
                if (dialog.open) return;
                dialog.showModal();
                opener.setAttribute('aria-expanded', 'true');
            });
            closer.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const bounds = dialog.getBoundingClientRect();
                if (event.clientX < bounds.left || event.clientX > bounds.right ||
                    event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
            });
            dialog.addEventListener('close', () => {
                opener.setAttribute('aria-expanded', 'false');
                opener.focus({preventScroll: true});
            });
        })();
    </script>

    <dialog id="post-report-dialog" aria-labelledby="post-report-title" style="width:min(520px,calc(100% - 32px));max-width:520px;border:1px solid #e5e7eb;border-radius:14px;padding:0;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #e5e7eb">
            <h2 id="post-report-title" style="margin:0;font-size:18px;font-weight:700">Yazıyı bildir</h2>
            <button type="button" data-post-report-close aria-label="Pencereyi kapat" style="border:0;background:transparent;font-size:22px;line-height:1;cursor:pointer">×</button>
        </div>
        @auth
            <form method="POST" action="{{ route('posts.report', $post) }}" style="padding:20px">
                @csrf
                <p style="margin:0 0 14px;color:#64748b;font-size:14px">Bu yazının topluluk kurallarını ihlal ettiğini düşünüyorsan nedenini seçerek yönetime bildirebilirsin.</p>
                <label for="post-report-reason" style="display:block;margin-bottom:7px;font-weight:600">Bildirim nedeni</label>
                <input type="hidden" id="post-report-reason" name="reason" value="">
                <div class="post-report-select" data-report-select>
                    <button type="button" class="post-report-select__trigger" aria-haspopup="listbox" aria-expanded="false" data-report-select-trigger>
                        <span data-report-select-label>Bir neden seç</span>
                        <svg viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><path d="m5 7.5 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="post-report-select__menu" role="listbox" aria-label="Bildirim nedeni" hidden>
                        <button type="button" role="option" data-value="spam">Spam veya reklam</button>
                        <button type="button" role="option" data-value="misinformation">Yanlış veya yanıltıcı bilgi</button>
                        <button type="button" role="option" data-value="harassment">Hakaret veya taciz</button>
                        <button type="button" role="option" data-value="copyright">Telif hakkı ihlali</button>
                        <button type="button" role="option" data-value="inappropriate">Uygunsuz içerik</button>
                        <button type="button" role="option" data-value="other">Diğer</button>
                    </div>
                    <p class="post-report-select__error" data-report-select-error hidden>Lütfen bir bildirim nedeni seç.</p>
                </div>
                <label for="post-report-details" style="display:block;margin:14px 0 7px;font-weight:600">Açıklama (isteğe bağlı)</label>
                <textarea id="post-report-details" name="details" rows="4" maxlength="2000" placeholder="Bildiriminle ilgili ek bilgi yazabilirsin..." style="width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:10px;resize:vertical"></textarea>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                    <button type="button" data-post-report-close class="btn btn-secondary">Vazgeç</button>
                    <button type="submit" class="btn btn-primary">Bildirimi gönder</button>
                </div>
            </form>
        @else
            <div style="padding:20px">
                <p>Yazı bildirmek için önce hesabına giriş yapmalısın.</p>
                <a class="btn btn-primary" href="{{ route('login') }}">Giriş yap</a>
            </div>
        @endauth
    </dialog>
    <style>
        .post-actions-menu{position:relative;display:inline-flex;flex:none}
        .post-actions-menu__list{position:absolute;z-index:1000;top:calc(100% + 7px);right:0;min-width:190px;margin:0;padding:6px;list-style:none;border:1px solid #e5e7eb;border-radius:11px;background:#fff;box-shadow:0 10px 28px rgba(15,23,42,.14)}
        .post-actions-menu__list[hidden]{display:none!important}
        .post-actions-menu__list li{display:block;margin:0;padding:0}
        .post-actions-menu__list a,.post-actions-menu__list button{display:block;width:100%;padding:10px 11px;border:0;border-radius:7px;background:transparent;color:#263247;text-align:left;text-decoration:none;font:inherit;cursor:pointer}
        .post-actions-menu__list a:hover,.post-actions-menu__list button:hover{background:#eff6ff;color:#1d4ed8}
        #post-report-dialog::backdrop{background:rgba(15,23,42,.55)}
        #post-report-dialog .post-report-trigger{cursor:pointer}
        .post-report-trigger{width:100%;border:0;background:transparent;text-align:left;padding:0;color:inherit;font:inherit;cursor:pointer}
        .post-report-select{position:relative;width:100%}
        .post-report-select__trigger{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:46px;padding:0 13px;border:1px solid #d7dee8;border-radius:9px;background:#fff;color:#172033;text-align:left;font:inherit;cursor:pointer;transition:border-color .15s,box-shadow .15s}
        .post-report-select__trigger:hover,.post-report-select.is-open .post-report-select__trigger{border-color:#2563eb}
        .post-report-select__trigger:focus-visible{outline:3px solid rgba(37,99,235,.15);outline-offset:1px}
        .post-report-select__trigger svg{flex:none;color:#64748b;transition:transform .15s}
        .post-report-select.is-open .post-report-select__trigger svg{transform:rotate(180deg)}
        .post-report-select__menu{position:absolute;z-index:20;top:calc(100% + 6px);left:0;right:0;padding:5px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.12)}
        .post-report-select__menu[hidden]{display:none}
        .post-report-select__menu button{display:block;width:100%;padding:10px 11px;border:0;border-radius:7px;background:transparent;color:#263247;text-align:left;font:inherit;cursor:pointer}
        .post-report-select__menu button:hover,.post-report-select__menu button:focus-visible{outline:0;background:#eff6ff;color:#1d4ed8}
        .post-report-select__menu button[aria-selected="true"]{background:#eff6ff;color:#1d4ed8;font-weight:600}
        .post-report-select__error{margin:6px 0 0;color:#dc2626;font-size:12px}
        #post-report-dialog textarea:focus{outline:0;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
    </style>
    <script>
        (() => {
            const initPostReport = () => {
                const dialog = document.getElementById('post-report-dialog');
                if (!dialog || dialog.dataset.initialized === 'true') return;
                dialog.dataset.initialized = 'true';

                document.addEventListener('click', (event) => {
                    const toggle = event.target.closest('[data-post-actions-toggle]');
                    if (toggle) {
                        event.preventDefault();
                        const root = toggle.closest('[data-post-actions-menu]');
                        const list = root?.querySelector('[data-post-actions-list]');
                        if (!root || !list) return;
                        const opening = list.hidden;
                        document.querySelectorAll('[data-post-actions-list]').forEach((other) => {
                            if (other !== list) {
                                other.hidden = true;
                                other.closest('[data-post-actions-menu]')?.querySelector('[data-post-actions-toggle]')?.setAttribute('aria-expanded', 'false');
                            }
                        });
                        list.hidden = !opening;
                        toggle.setAttribute('aria-expanded', String(opening));
                        return;
                    }

                    const opener = event.target.closest('[data-post-report-open]');
                    if (!opener) {
                        if (!event.target.closest('[data-post-actions-menu]')) {
                            document.querySelectorAll('[data-post-actions-list]').forEach((list) => {
                                list.hidden = true;
                                list.closest('[data-post-actions-menu]')?.querySelector('[data-post-actions-toggle]')?.setAttribute('aria-expanded', 'false');
                            });
                        }
                        return;
                    }
                    const menuRoot = opener.closest('[data-post-actions-menu]');
                    const menuList = menuRoot?.querySelector('[data-post-actions-list]');
                    if (menuList) {
                        menuList.hidden = true;
                        menuRoot.querySelector('[data-post-actions-toggle]')?.setAttribute('aria-expanded', 'false');
                    }
                    event.preventDefault();
                    if (typeof dialog.showModal === 'function') {
                        if (!dialog.open) dialog.showModal();
                    } else {
                        dialog.setAttribute('open', '');
                    }
                });

                dialog.querySelectorAll('[data-post-report-close]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (typeof dialog.close === 'function') dialog.close();
                        else dialog.removeAttribute('open');
                    });
                });

                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) {
                        if (typeof dialog.close === 'function') dialog.close();
                        else dialog.removeAttribute('open');
                    }
                });

                const select = dialog.querySelector('[data-report-select]');
                if (!select) return;
                const trigger = select.querySelector('[data-report-select-trigger]');
                const menu = select.querySelector('[role="listbox"]');
                const valueInput = dialog.querySelector('#post-report-reason');
                const label = select.querySelector('[data-report-select-label]');
                const error = select.querySelector('[data-report-select-error]');
                if (!trigger || !menu || !valueInput || !label || !error) return;

                const closeMenu = () => {
                    menu.hidden = true;
                    select.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                };

                trigger.addEventListener('click', () => {
                    const opening = menu.hidden;
                    menu.hidden = !opening;
                    select.classList.toggle('is-open', opening);
                    trigger.setAttribute('aria-expanded', String(opening));
                });

                menu.querySelectorAll('[data-value]').forEach((option) => {
                    option.addEventListener('click', () => {
                        valueInput.value = option.dataset.value;
                        label.textContent = option.textContent.trim();
                        menu.querySelectorAll('[role="option"]').forEach((item) => {
                            item.setAttribute('aria-selected', String(item === option));
                        });
                        error.hidden = true;
                        closeMenu();
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!select.contains(event.target)) closeMenu();
                });

                dialog.querySelector('form')?.addEventListener('submit', (event) => {
                    if (!valueInput.value) {
                        event.preventDefault();
                        error.hidden = false;
                        trigger.focus();
                        menu.hidden = false;
                        select.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });

                dialog.addEventListener('close', () => {
                    valueInput.value = '';
                    label.textContent = 'Bir neden seç';
                    error.hidden = true;
                    menu.querySelectorAll('[role="option"]').forEach((item) => item.removeAttribute('aria-selected'));
                    closeMenu();
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPostReport, { once: true });
            } else {
                initPostReport();
            }
        })();
    </script>
@endsection
