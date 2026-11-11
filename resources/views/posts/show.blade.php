@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->meta_description ?: $post->excerpt)

@section('canonical', route('posts.show', $post))
@section('og_type', 'article')
@if ($post->coverImageUrl())
    @section('seo_image', $post->coverImageUrl())
@endif

@push('head')
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
            <div class="dropdown">
                <button class="btn btn-secondary-ghost btn-circle btn-xs" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Yazı menüsünü aç"><x-repost-icon name="more" size="16" /></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if (auth()->user()?->is($post->user) || auth()->user()?->is_admin)
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.edit', $post) }}">Yazıyı düzenle</a></li>
                    @endif
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><button type="button" class="post-report-trigger" data-post-report-open>Yazıyı bildir</button></li>
                </ul>
            </div>
        </header>

        @if ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url) || filled($post->video_path))
            <div class="post__media-full">@include('posts._media', ['post' => $post])</div>
        @endif

        <h1 class="post__title-full">{{ $post->title }}</h1>
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
            @endphp
            @if ($editorBlocks !== null)
                @foreach ($editorBlocks as $block)
                    @php $blockData = $block['data'] ?? []; @endphp
                    @switch($block['type'] ?? '')
                        @case('header')
                            <h{{ min(4, max(2, (int) ($blockData['level'] ?? 2))) }}>{{ $blockData['text'] ?? '' }}</h{{ min(4, max(2, (int) ($blockData['level'] ?? 2))) }}>
                            @break
                        @case('paragraph')
                            @if (filled($blockData['text'] ?? ''))<p>{!! nl2br(e($blockData['text'])) !!}</p>@endif
                            @break
                        @case('list')
                            @php $listTag = ($blockData['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul'; @endphp
                            <{{ $listTag }}>
                                @foreach (($blockData['items'] ?? []) as $item)
                                    <li>{{ is_array($item) ? ($item['content'] ?? '') : $item }}</li>
                                @endforeach
                            </{{ $listTag }}>
                            @break
                        @case('quote')
                            <blockquote><p>{{ $blockData['text'] ?? '' }}</p>@if (filled($blockData['caption'] ?? ''))<cite>{{ $blockData['caption'] }}</cite>@endif</blockquote>
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
                                    <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                                @endforeach
                            </table></div>
                            @break
                    @endswitch
                @endforeach
            @else
                @foreach (preg_split('/\\R{2,}/', trim($post->body)) ?: [] as $paragraph)
                    @if (filled($paragraph) && ! str_starts_with(trim($paragraph), 'Bu içerik RSS kaynağından alınmıştır. Kaynak:'))
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endif
                @endforeach
            @endif
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
        @include('posts._interactions', ['post' => $post])
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
        document.addEventListener('DOMContentLoaded',function(){
            const dialog=document.getElementById('post-report-dialog');
            if(!dialog)return;
            document.querySelectorAll('[data-post-report-open]').forEach(button=>button.addEventListener('click',()=>dialog.showModal()));
            dialog.querySelectorAll('[data-post-report-close]').forEach(button=>button.addEventListener('click',()=>dialog.close()));
            dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});

            const select=dialog.querySelector('[data-report-select]');
            if(!select)return;
            const trigger=select.querySelector('[data-report-select-trigger]');
            const menu=select.querySelector('[role="listbox"]');
            const valueInput=dialog.querySelector('#post-report-reason');
            const label=select.querySelector('[data-report-select-label]');
            const error=select.querySelector('[data-report-select-error]');
            const closeMenu=()=>{menu.hidden=true;select.classList.remove('is-open');trigger.setAttribute('aria-expanded','false');};
            trigger.addEventListener('click',()=>{
                const opening=menu.hidden;
                menu.hidden=!opening;
                select.classList.toggle('is-open',opening);
                trigger.setAttribute('aria-expanded',String(opening));
            });
            menu.querySelectorAll('[data-value]').forEach(option=>option.addEventListener('click',()=>{
                valueInput.value=option.dataset.value;
                label.textContent=option.textContent.trim();
                menu.querySelectorAll('[role="option"]').forEach(item=>item.setAttribute('aria-selected',String(item===option)));
                error.hidden=true;
                closeMenu();
            }));
            document.addEventListener('click',event=>{if(!select.contains(event.target))closeMenu();});
            dialog.querySelector('form')?.addEventListener('submit',event=>{
                if(!valueInput.value){
                    event.preventDefault();
                    error.hidden=false;
                    trigger.focus();
                    menu.hidden=false;
                    select.classList.add('is-open');
                    trigger.setAttribute('aria-expanded','true');
                }
            });
            dialog.addEventListener('close',()=>{
                valueInput.value='';
                label.textContent='Bir neden seç';
                error.hidden=true;
                menu.querySelectorAll('[role="option"]').forEach(item=>item.removeAttribute('aria-selected'));
                closeMenu();
            });
        });
    </script>
@endsection
