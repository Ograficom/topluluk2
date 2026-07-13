@extends('layouts.app')

@section('title', 'Alinti Paylas')

@section('hide_global_header')
@endsection

@section('no_container_padding')
@endsection

@section('page_background_class', 'bg-white')
@section('hide_feed_header')
@endsection
@section('hide_mobile_bottom_nav')
@endsection

@section('content')
    @php
        $user = auth()->user();
        $quoteAuthor = $repostPost?->author;
        $quoteCategory = $repostPost?->category;
        $quoteAuthorName = trim((string) ($quoteAuthor?->name ?? 'Ografi'));
        $quoteAuthorAvatar = $quoteAuthor?->profile_photo_url ?? null;
        $quoteCategoryAvatar = $quoteCategory?->profile_image_url ?? $quoteCategory?->profile_image ?? null;
        $normalizeQuoteImageUrl = function ($url): ?string {
            $url = trim(html_entity_decode((string) $url));

            if ($url === '') {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://', '//', 'data:image/'])) {
                return $url;
            }

            if (\Illuminate\Support\Str::startsWith($url, '/')) {
                return url($url);
            }

            if (\Illuminate\Support\Str::startsWith($url, 'storage/')) {
                return url('/' . $url);
            }

            if (preg_match('/\.(png|jpe?g|gif|webp|svg)(?:\?.*)?$/i', $url)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($url);
            }

            return null;
        };

        $quoteImage = $normalizeQuoteImageUrl($repostPost?->featured_image_url ?? $repostPost?->featured_image ?? null);

        $quoteContentJson = $repostPost?->content_json ?? null;
        if (is_string($quoteContentJson)) {
            $decodedQuoteContent = json_decode($quoteContentJson, true);
            $quoteContentJson = is_array($decodedQuoteContent) ? $decodedQuoteContent : null;
        }

        if (!$quoteImage && is_array($quoteContentJson)) {
            $quoteImage = collect($quoteContentJson['blocks'] ?? [])
                ->flatMap(function ($block) {
                    $type = (string) data_get($block, 'type', '');
                    $data = data_get($block, 'data', []);
                    $urls = [];

                    if ($type === 'image') {
                        $urls[] = data_get($data, 'file.url') ?? data_get($data, 'url') ?? data_get($data, 'src') ?? data_get($data, 'image');
                    }

                    if (in_array($type, ['gallery', 'carousel', 'slider'], true)) {
                        foreach ((array) (data_get($data, 'images') ?? data_get($data, 'items') ?? data_get($data, 'slides') ?? []) as $entry) {
                            $urls[] = is_array($entry)
                                ? (data_get($entry, 'file.url') ?? data_get($entry, 'url') ?? data_get($entry, 'src') ?? data_get($entry, 'image'))
                                : $entry;
                        }
                    }

                    return $urls;
                })
                ->map($normalizeQuoteImageUrl)
                ->filter()
                ->first();
        }

        if (!$quoteImage) {
            $quoteContentHtml = (string) ($repostPost?->content ?? '');
            if ($quoteContentHtml !== '' && preg_match('/<img[^>]+(?:src|data-src|data-original)=["\']([^"\']+)["\']/i', $quoteContentHtml, $matches)) {
                $quoteImage = $normalizeQuoteImageUrl($matches[1] ?? null);
            }
        }

        $quoteSubtitle = $repostPost?->published_at?->diffForHumans()
            ?? $repostPost?->created_at?->diffForHumans()
            ?? 'Alinti';
        $quoteTitle = trim((string) ($repostTitle ?: ($repostPost?->title ?? 'Baslik yok')));
        $quoteExcerpt = trim(strip_tags((string) ($repostPost?->excerpt ?? $repostPost?->content ?? '')));
        $quoteExcerpt = $quoteExcerpt !== '' ? \Illuminate\Support\Str::limit($quoteExcerpt, 190) : '';
        $fallbackContent = trim((string) $repostContent) !== '' ? $repostContent : 'Alinti paylasimi';
    @endphp

    <style>
        .repost-create-page {
            position: fixed;
            inset: 0;
            z-index: 99999;
            overflow: auto;
            background: #f6f7fb;
        }

        body,
        html {
            overflow: hidden;
        }

        .repost-create-card {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: none;
        }

        .repost-quote-card {
            border: 1px solid #d9dee7;
            background: #ffffff;
        }

        .repost-quote-card img {
            display: block;
        }

        .repost-quote-avatar-wrap {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
        }

        .repost-quote-avatar {
            width: 44px;
            height: 44px;
        }

        .repost-quote-category {
            position: absolute;
            right: -3px;
            bottom: -3px;
            width: 24px;
            height: 24px;
            background: #ffffff;
        }

    </style>

    <div class="repost-create-page text-slate-950">
        <div class="mx-auto flex min-h-screen w-full max-w-[1280px] flex-col px-3 py-3 sm:px-5 lg:px-6">
        <header class="sticky top-3 z-40 mb-4 rounded-[28px] border border-slate-200 bg-white/95 px-3 py-3 sm:px-5 lg:px-6">
            <div>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 text-slate-900 dark:text-white">
                        <a href="{{ route('blog.index') }}"
                           class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600"
                           aria-label="Geri">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>

                        <div class="min-w-0 leading-tight">
                            <div class="truncate text-sm font-semibold text-slate-950">Alinti paylas</div>
                            <div class="text-xs text-slate-500">Create sayfasi gorunumunde duzenle.</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <details class="relative">
                            <summary class="inline-flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-full text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900 [&::-webkit-details-marker]:hidden">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="5" cy="12" r="1.8"/>
                                    <circle cx="12" cy="12" r="1.8"/>
                                    <circle cx="19" cy="12" r="1.8"/>
                                </svg>
                            </summary>
                            <div class="absolute right-0 mt-2 w-44 rounded-xl p-1 shadow-lg dark:bg-slate-900">
                                <button type="submit" form="post-repost-form" data-submit-intent="draft"
                                        class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                                    Taslak kaydet
                                </button>
                                <button type="button" data-open-preview
                                        class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                                    On izleme
                                </button>
                                <button type="button" data-open-settings
                                        class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                                    Ayarlar
                                </button>
                            </div>
                        </details>

                        <button type="submit" form="post-repost-form" data-submit-intent="publish"
                                class="inline-flex h-11 items-center rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white sm:px-5">
                            Paylas
                        </button>
                    </div>
                </div>
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-100">
                <div class="font-semibold">Duzeltmeniz gereken alanlar var:</div>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="mx-auto w-full max-w-[760px] flex-1">
        <form id="post-repost-form" method="POST" action="{{ route('blog.store') }}" class="space-y-4" enctype="multipart/form-data" data-has-repost-card="{{ ($repostTitle || $repostUrl) ? 'true' : 'false' }}">
            @csrf
            <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published') ? 1 : 0 }}">
            <input type="hidden" name="repost_url" value="{{ $repostUrl }}">
            <input type="hidden" name="repost_title" value="{{ $repostTitle }}">
            <input type="hidden" name="repost_post_id" value="{{ $repostPost?->id }}">

            <section class="repost-create-card overflow-hidden rounded-[30px]">
                <div class="border-b border-slate-100 px-4 py-4 sm:px-7 sm:py-6">
                    <input id="title" name="title" type="text" required value="{{ old('title') }}"
                           placeholder="Baslik"
                           class="w-full rounded-2xl bg-slate-100/80 px-4 py-3 text-[1.65rem] font-semibold leading-tight text-slate-950 placeholder:text-slate-400 focus:outline-none sm:text-[2.1rem]">
                </div>

                <div data-editorjs-wrapper class="bg-white">
                    <div x-ref="holder" class="min-h-[36vh] px-4 py-6 text-slate-800 sm:px-7 sm:py-7"></div>
                    <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">
                    <textarea id="content" name="content" data-editor-content data-mentionable="users"
                              class="hidden min-h-[36vh] w-full resize-none px-4 py-6 text-slate-800 focus:outline-none sm:px-7 sm:py-7">{{ old('content') }}</textarea>
                </div>

                @if ($repostTitle || $repostUrl)
                    <div class="px-4 pb-4 sm:px-7 sm:pb-7">
                        <article class="repost-quote-card overflow-hidden rounded-[18px] p-4">
                            <div class="mb-3 flex items-center gap-3">
                                <div class="repost-quote-avatar-wrap relative">
                                    @if ($quoteAuthorAvatar)
                                        <img src="{{ $quoteAuthorAvatar }}" alt="{{ $quoteAuthorName }}" class="repost-quote-avatar rounded-full object-cover" loading="lazy">
                                    @else
                                        <span class="repost-quote-avatar inline-flex items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($quoteAuthorName, 0, 2)) }}</span>
                                    @endif
                                    @if ($quoteCategoryAvatar)
                                        <img src="{{ $quoteCategoryAvatar }}" alt="{{ $quoteCategory?->name ?? 'Kategori' }}" class="repost-quote-category rounded-full object-cover" loading="lazy">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-950">{{ $quoteAuthorName }}</div>
                                    <div class="truncate text-xs text-slate-500">{{ $quoteSubtitle }}</div>
                                </div>
                            </div>

                            <h2 class="mb-2 text-[1.08rem] font-semibold leading-snug text-slate-950 sm:text-[1.18rem]">{{ $quoteTitle }}</h2>

                            @if ($quoteExcerpt !== '')
                                <p class="mb-3 text-[0.95rem] leading-relaxed text-slate-800">{{ $quoteExcerpt }}</p>
                            @endif

                            @if ($quoteImage)
                                <div class="overflow-hidden rounded-[14px] bg-slate-100">
                                    <img src="{{ $quoteImage }}" alt="{{ $quoteTitle }}" class="aspect-[16/9] w-full object-cover" loading="lazy">
                                </div>
                            @endif
                        </article>
                    </div>
                @endif
            </section>

            <div id="settings-modal" class="fixed inset-0 z-[70] hidden" aria-hidden="true">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-xl" data-settings-close></div>
                <div class="fixed inset-y-0 right-0 w-full sm:max-w-xl">
                    <div class="flex h-full flex-col shadow-2xl dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-3 px-3 py-3 sm:px-4">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Ayarlar</div>
                            <button type="button" data-settings-close class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Kapat">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-auto">
                            <div class="space-y-6 p-4 sm:p-5">
                                <div class="space-y-2">
                                    <label for="new_tags" class="block text-sm font-semibold text-slate-900 dark:text-white">Etiketler</label>
                                    <input
                                        id="new_tags"
                                        name="new_tags"
                                        type="text"
                                        value="{{ old('new_tags') }}"
                                        placeholder="Add tags"
                                        class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white"
                                    >
                                </div>

                                <div class="space-y-2">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Var olan etiketler</div>
                                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                                        <div class="flex max-h-40 flex-wrap gap-2 overflow-auto pr-1">
                                            @forelse ($tags as $tag)
                                                <label class="inline-flex cursor-pointer select-none items-center gap-2 rounded-full px-3 py-1 text-xs text-slate-700 shadow-sm dark:bg-slate-900/70 dark:text-slate-200">
                                                    <input
                                                        type="checkbox"
                                                        name="tags[]"
                                                        value="{{ $tag->id }}"
                                                        @checked(collect(old('tags', []))->contains($tag->id))
                                                        class="h-4 w-4 rounded text-emerald-600 dark:bg-slate-800"
                                                    >
                                                    <span>#{{ $tag->name }}</span>
                                                </label>
                                            @empty
                                                <span class="text-sm text-slate-500 dark:text-slate-300">Henuz etiket yok.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="excerpt" class="block text-sm font-semibold text-slate-900 dark:text-white">Altyazi</label>
                                    <textarea id="excerpt" name="excerpt" rows="2"
                                              class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">{{ old('excerpt') }}</textarea>
                                </div>

                                <div class="space-y-2">
                                    <label for="meta_title" class="block text-sm font-semibold text-slate-900 dark:text-white">SEO basligi</label>
                                    <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title') }}"
                                           class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                </div>

                                <div class="space-y-2">
                                    <label for="meta_description" class="block text-sm font-semibold text-slate-900 dark:text-white">SEO aciklamasi</label>
                                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="160"
                                              class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">{{ old('meta_description') }}</textarea>
                                    <div class="text-xs text-slate-400 text-right" data-meta-description-count>0/160</div>
                                </div>

                                <div class="space-y-2">
                                    <label for="slug" class="block text-sm font-semibold text-slate-900 dark:text-white">Kanonik URL</label>
                                    <input id="slug" name="slug" type="text" value="{{ old('slug') }}"
                                           class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                </div>

                                <div class="space-y-2">
                                    <label for="meta_keywords" class="block text-sm font-semibold text-slate-900 dark:text-white">SEO anahtar kelimeler</label>
                                    <textarea id="meta_keywords" name="meta_keywords" rows="2"
                                              class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">{{ old('meta_keywords') }}</textarea>
                                    <p class="text-xs text-slate-500 dark:text-slate-300">Virgul ile ayirin (or. yazilim, php, laravel).</p>
                                </div>

                                <div class="space-y-4 pt-4">
                                    <div class="space-y-2">
                                        <label for="category_id" class="block text-sm font-semibold text-slate-900 dark:text-white">Kategori</label>
                                        <select id="category_id" name="category_id"
                                                class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                            <option value="">Secim yap</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="published_at" class="block text-sm font-semibold text-slate-900 dark:text-white">Yayin tarihi (opsiyonel)</label>
                                        <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at') }}"
                                               class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="featured_image" class="block text-sm font-semibold text-slate-900 dark:text-white">One cikan gorsel</label>
                                        <input id="featured_image" name="featured_image" type="file" accept="image/*"
                                               class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                        <p class="text-xs text-slate-500 dark:text-slate-300">Maks 5 MB.</p>
                                    </div>
                                </div>

                                <div class="space-y-4 pt-4">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Gorsel lisans bilgileri</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-300">Bu bilgiler, yazidaki gorseller icin lisans meta verisi olarak kullanilir.</div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label for="image_license_url" class="block text-sm font-semibold text-slate-900 dark:text-white">Lisans URL</label>
                                            <input id="image_license_url" name="image_license_url" type="url" value="{{ old('image_license_url') }}"
                                                   placeholder="https://example.com/license"
                                                   class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                        </div>
                                        <div class="space-y-2">
                                            <label for="image_acquire_url" class="block text-sm font-semibold text-slate-900 dark:text-white">Lisans alma sayfasi</label>
                                            <input id="image_acquire_url" name="image_acquire_url" type="url" value="{{ old('image_acquire_url') }}"
                                                   placeholder="https://example.com/buy"
                                                   class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label for="image_credit_text" class="block text-sm font-semibold text-slate-900 dark:text-white">Kredi metni</label>
                                            <input id="image_credit_text" name="image_credit_text" type="text" value="{{ old('image_credit_text') }}"
                                                   placeholder="Or: Foto: Ografi Studio"
                                                   class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                        </div>
                                        <div class="space-y-2">
                                            <label for="image_creator_name" class="block text-sm font-semibold text-slate-900 dark:text-white">Yaratici adi</label>
                                            <input id="image_creator_name" name="image_creator_name" type="text" value="{{ old('image_creator_name') }}"
                                                   placeholder="Or: Ografi Studio"
                                                   class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="image_copyright_notice" class="block text-sm font-semibold text-slate-900 dark:text-white">Telif notu</label>
                                        <input id="image_copyright_notice" name="image_copyright_notice" type="text" value="{{ old('image_copyright_notice') }}"
                                               placeholder="Or: © 2026 Ografi"
                                               class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none dark:bg-slate-800 dark:text-white">
                                    </div>
                                </div>

                                <div class="space-y-4 pt-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Yorumlari devre disi birak</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-300">Bu, hikayenizin altindaki yorumlar bolumunu gizleyecektir.</div>
                                        </div>
                                        <div class="shrink-0">
                                            <input type="hidden" name="comments_disabled" value="0">
                                            <x-ui.switch
                                                name="comments_disabled"
                                                value="1"
                                                :checked="old('comments_disabled', 0) == 1"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Isyerinde izlenmesi uygun degil (NSFW)</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-300">Yetiskinlere yonelik icerik icermektedir.</div>
                                        </div>
                                        <div class="shrink-0">
                                            <input type="hidden" name="is_nsfw" value="0">
                                            <x-ui.switch
                                                name="is_nsfw"
                                                value="1"
                                                :checked="old('is_nsfw', 0) == 1"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Pin hikayesi</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-300">Sabitlediginiz hikayeler yalnizca profilinizde gorunur.</div>
                                        </div>
                                        <div class="shrink-0">
                                            <input type="hidden" name="is_pinned" value="0">
                                            <x-ui.switch
                                                name="is_pinned"
                                                value="1"
                                                :checked="old('is_pinned', 0) == 1"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div id="post-preview-modal" class="fixed inset-0 z-[70] hidden">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-xl" data-preview-close></div>
            <div class="fixed inset-0 overflow-y-auto">
                <div class="mx-auto mt-4 w-full max-w-3xl px-3 sm:mt-10 sm:px-4">
                    <div class="rounded-xl shadow-xl dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-3 px-3 py-3 sm:px-4">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">On izleme</div>
                            <button type="button" data-preview-close class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Kapat">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="max-h-[75vh] overflow-auto p-3 sm:max-h-[70vh] sm:p-4">
                            <div id="post-preview-content" class="space-y-4 text-sm text-slate-700 dark:text-slate-200"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </main>
        </div>
    </div>
@endsection

@push('scripts')
    @include('filament.assets.editorjs')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('[data-editorjs-wrapper]');
            const fallbackTextarea = document.getElementById('content');
            const repostFallbackContent = @json($fallbackContent);

            const showFallback = () => {
                wrapper?.classList.add('hidden');
                fallbackTextarea?.classList.remove('hidden');
            };

            const initEditor = async () => {
                if (!wrapper || !window.initFilamentEditorJsField) {
                    showFallback();
                    return;
                }

                try {
                    await window.initFilamentEditorJsField(wrapper);
                    if (!wrapper.__editorInstance) {
                        showFallback();
                    }
                } catch {
                    showFallback();
                }
            };

            initEditor();

            const form = document.getElementById('post-repost-form');
            const isPublishedInput = document.getElementById('is_published');
            const hasRepostCard = form?.dataset?.hasRepostCard === 'true';
            const draftButton = document.querySelector('[data-submit-intent="draft"]');
            const publishButton = document.querySelector('[data-submit-intent="publish"]');
            const metaDescription = document.getElementById('meta_description');
            const metaDescriptionCount = document.querySelector('[data-meta-description-count]');

            const syncMetaDescriptionCount = () => {
                if (!metaDescription || !metaDescriptionCount) return;
                metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
            };

            syncMetaDescriptionCount();
            metaDescription?.addEventListener('input', syncMetaDescriptionCount);

            draftButton?.addEventListener('click', () => {
                if (!isPublishedInput) return;
                isPublishedInput.value = '0';
            });

            const openSettingsButton = document.querySelector('[data-open-settings]');
            const settingsModal = document.getElementById('settings-modal');
            const previewModal = document.getElementById('post-preview-modal');

            const syncScrollLock = () => {
                const anyOpen = [settingsModal, previewModal].some((modal) => modal && !modal.classList.contains('hidden'));
                document.documentElement.classList.toggle('overflow-hidden', anyOpen);
                document.body.classList.toggle('overflow-hidden', anyOpen);
            };

            const closeSettings = () => {
                settingsModal?.classList.add('hidden');
                syncScrollLock();
            };

            settingsModal?.querySelectorAll('[data-settings-close]').forEach((el) => el.addEventListener('click', closeSettings));
            openSettingsButton?.addEventListener('click', () => {
                settingsModal?.classList.remove('hidden');
                syncScrollLock();
                const firstAccordion = settingsModal?.querySelector('details');
                if (firstAccordion) firstAccordion.open = true;
            });

            const isEditorContentPresent = async () => {
                if (wrapper?.__editorInstance?.save) {
                    try {
                        const data = await wrapper.__editorInstance.save();
                        const blocks = Array.isArray(data?.blocks) ? data.blocks : [];
                        if (blocks.length > 0) return true;
                    } catch {
                        // ignore
                    }
                }

                if (hasRepostCard) {
                    return true;
                }

                return Boolean((fallbackTextarea?.value || '').trim());
            };

            const ensureRepostFallbackContent = () => {
                if (!hasRepostCard || !fallbackTextarea) return;
                if ((fallbackTextarea.value || '').trim() === '') {
                    fallbackTextarea.value = repostFallbackContent || 'Alinti paylasimi';
                }
            };

            form?.addEventListener('submit', ensureRepostFallbackContent);

            publishButton?.addEventListener('click', async (e) => {
                if (!form || !isPublishedInput) return;
                isPublishedInput.value = '1';
                e.preventDefault();
                ensureRepostFallbackContent();

                const titleValue = (document.getElementById('title')?.value || '').trim();
                const hasContent = await isEditorContentPresent();

                if (!titleValue || !hasContent) {
                    alert('* ile isaretli alanlar bos birakilamaz.');
                    return;
                }

                const ok = confirm('Yayinlamak istiyor musunuz? (*) ile isaretli alanlar bos birakilamaz.');
                if (!ok) return;

                form.requestSubmit();
            });

            const previewContent = document.getElementById('post-preview-content');

            const closePreview = () => {
                previewModal?.classList.add('hidden');
                syncScrollLock();
            };
            previewModal?.querySelectorAll('[data-preview-close]').forEach((el) => el.addEventListener('click', closePreview));

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closePreview();
                if (e.key === 'Escape') closeSettings();
            });

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const renderBlocks = (blocks = []) => {
                const parts = [];

                const safeUrl = (url) => {
                    try {
                        const parsed = new URL(url, window.location.origin);
                        if (!['http:', 'https:'].includes(parsed.protocol)) return null;
                        return parsed.toString();
                    } catch {
                        return null;
                    }
                };

                const isAllowedEmbedHost = (hostname) => {
                    const host = String(hostname || '').toLowerCase();
                    const allowed = [
                        'youtube.com',
                        'www.youtube.com',
                        'youtube-nocookie.com',
                        'www.youtube-nocookie.com',
                        'instagram.com',
                        'www.instagram.com',
                        'tiktok.com',
                        'www.tiktok.com',
                        'player.vimeo.com',
                        'vimeo.com',
                        'www.vimeo.com',
                        'dailymotion.com',
                        'www.dailymotion.com',
                        'player.twitch.tv',
                        'twitch.tv',
                        'www.twitch.tv',
                        'clips.twitch.tv',
                        'facebook.com',
                        'www.facebook.com',
                        'fb.watch',
                        'twitter.com',
                        'www.twitter.com',
                        'x.com',
                        'www.x.com',
                        'twitframe.com',
                        'vine.co',
                    ];

                    return allowed.includes(host);
                };

                const safeSocialEmbedUrl = (url) => {
                    const safe = safeUrl(url);
                    if (!safe) return null;
                    try {
                        const parsed = new URL(safe);
                        if (!isAllowedEmbedHost(parsed.hostname)) return null;
                        return parsed.toString();
                    } catch {
                        return null;
                    }
                };

                const buildEmbedSrcFromUrl = (value) => {
                    const safe = safeUrl(value);
                    if (!safe) return null;
                    const parsed = new URL(safe);
                    const host = parsed.hostname.toLowerCase();
                    const parts = parsed.pathname.split('/').filter(Boolean);

                    if (host === 'youtu.be') return parts[0] ? `https://www.youtube.com/embed/${encodeURIComponent(parts[0])}` : null;
                    if (host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) {
                        if (parts[0] === 'watch') {
                            const id = parsed.searchParams.get('v');
                            return id ? `https://www.youtube.com/embed/${encodeURIComponent(id)}` : null;
                        }
                        if (parts[0] === 'shorts' || parts[0] === 'embed') {
                            return parts[1] ? `https://www.youtube.com/embed/${encodeURIComponent(parts[1])}` : null;
                        }
                    }
                    if (host.endsWith('instagram.com')) {
                        const kind = parts[0], code = parts[1];
                        if (['p', 'reel', 'tv'].includes(kind) && code) return `https://www.instagram.com/${kind}/${encodeURIComponent(code)}/embed`;
                    }
                    if (host === 'vimeo.com' || host === 'www.vimeo.com' || host === 'player.vimeo.com') {
                        const id = parts[0] === 'video' ? parts[1] : parts[0];
                        if (id && /^\d+$/.test(id)) return `https://player.vimeo.com/video/${id}`;
                    }
                    if (host.endsWith('dailymotion.com')) {
                        if (parts[0] === 'video' && parts[1]) return `https://www.dailymotion.com/embed/video/${encodeURIComponent(parts[1])}`;
                    }
                    if (host === 'dai.ly' && parts[0]) return `https://www.dailymotion.com/embed/video/${encodeURIComponent(parts[0])}`;
                    if (host === 'twitch.tv' || host === 'www.twitch.tv') {
                        const parent = window.location.hostname || 'localhost';
                        if (parts[0] === 'videos' && parts[1]) return `https://player.twitch.tv/?video=v${encodeURIComponent(parts[1])}&parent=${encodeURIComponent(parent)}`;
                        if (parts[1] === 'clip' && parts[2]) return `https://player.twitch.tv/?clip=${encodeURIComponent(parts[2])}&parent=${encodeURIComponent(parent)}`;
                    }
                    if (host === 'clips.twitch.tv' && parts[0]) {
                        const parent = window.location.hostname || 'localhost';
                        return `https://player.twitch.tv/?clip=${encodeURIComponent(parts[0])}&parent=${encodeURIComponent(parent)}`;
                    }
                    if (host.endsWith('facebook.com') || host.endsWith('fb.watch')) return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(parsed.toString())}&show_text=false`;
                    if (host.endsWith('twitter.com') || host.endsWith('.twitter.com') || host === 'x.com' || host === 'www.x.com' || host === 'mobile.x.com' || host.endsWith('.x.com')) {
                        if (parts.includes('status') || parts.includes('statuses')) return `https://twitframe.com/show?url=${encodeURIComponent(parsed.toString())}`;
                    }
                    if (host.endsWith('vine.co') && parts[0] === 'v' && parts[1]) return `https://vine.co/v/${encodeURIComponent(parts[1])}/embed/simple`;
                    return null;
                };

                for (const block of blocks) {
                    if (!block || !block.type) continue;

                    if (block.type === 'header') {
                        const level = Math.min(Math.max(parseInt(block.data?.level || 2, 10), 2), 4);
                        parts.push(`<h${level} class="text-base font-semibold text-slate-900 dark:text-white">${escapeHtml(block.data?.text || '')}</h${level}>`);
                        continue;
                    }

                    if (block.type === 'paragraph') {
                        const text = String(block.data?.text || '').trim();
                        const autoEmbed = buildEmbedSrcFromUrl(text);
                        const safeEmbed = autoEmbed ? safeSocialEmbedUrl(autoEmbed) : null;
                        if (safeEmbed) {
                            parts.push(`
                                <div class="overflow-hidden rounded-xl bg-black/5">
                                    <div class="aspect-video">
                                        <iframe class="h-full w-full" src="${escapeHtml(safeEmbed)}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                </div>
                            `);
                        } else {
                            parts.push(`<p class="whitespace-pre-wrap">${escapeHtml(block.data?.text || '')}</p>`);
                        }
                        continue;
                    }

                    if (block.type === 'list') {
                        const style = block.data?.style === 'ordered' ? 'list-decimal' : 'list-disc';
                        const items = Array.isArray(block.data?.items) ? block.data.items : [];
                        const listItems = items.map((item) => `<li>${escapeHtml(item)}</li>`).join('');
                        parts.push(`<ul class="${style} pl-5 space-y-1">${listItems}</ul>`);
                        continue;
                    }

                    if (block.type === 'quote') {
                        parts.push(`<blockquote class="border-l-4 pl-4 italic">${escapeHtml(block.data?.text || '')}</blockquote>`);
                        continue;
                    }

                    if (block.type === 'embed') {
                        const embedUrl = safeSocialEmbedUrl(block.data?.embed);
                        if (embedUrl) {
                            parts.push(`
                                <div class="overflow-hidden rounded-xl bg-black/5">
                                    <div class="aspect-video">
                                        <iframe class="h-full w-full" src="${escapeHtml(embedUrl)}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                </div>
                            `);
                        } else if (block.data?.source) {
                            const sourceUrl = safeUrl(block.data.source);
                            if (sourceUrl) parts.push(`<a class="text-amber-700 underline" href="${escapeHtml(sourceUrl)}" target="_blank" rel="nofollow noopener noreferrer">${escapeHtml(sourceUrl)}</a>`);
                        }
                        continue;
                    }

                    if (block.type === 'socialEmbed') {
                        const embedUrl = safeSocialEmbedUrl(block.data?.src);
                        if (embedUrl) {
                            parts.push(`
                                <div class="overflow-hidden rounded-xl bg-black/5">
                                    <div class="aspect-video">
                                        <iframe class="h-full w-full" src="${escapeHtml(embedUrl)}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                </div>
                            `);
                        }
                        continue;
                    }

                    if (block.type === 'downloadButton') {
                        const url = safeUrl(block.data?.url);
                        if (url) {
                            const label = escapeHtml(block.data?.text || 'Indir');
                            parts.push(`
                                <div>
                                    <a class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" href="${escapeHtml(url)}" target="_blank" rel="nofollow noopener noreferrer">${label}</a>
                                </div>
                            `);
                        }
                        continue;
                    }

                    if (block.type === 'poll') {
                        const question = escapeHtml(block.data?.question || 'Anket');
                        const options = Array.isArray(block.data?.options) ? block.data.options : [];
                        const duration = parseInt(block.data?.duration_minutes || 0, 10);
                        const durationLabel = duration > 0 ? `${duration} dk` : 'Suresiz';
                        const items = options.map((opt) => `<li class="rounded-lg px-3 py-2">${escapeHtml(opt)}</li>`).join('');
                        parts.push(`
                            <div class="rounded-xl p-3 space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span class="font-semibold text-slate-700">Anket</span>
                                    <span>${durationLabel}</span>
                                </div>
                                <div class="font-semibold text-slate-900">${question}</div>
                                <ul class="space-y-2">${items}</ul>
                            </div>
                        `);
                        continue;
                    }
 
                    parts.push(`<pre class="overflow-auto rounded-lg bg-slate-950/5 p-3 text-xs text-slate-700 dark:bg-white/5 dark:text-slate-200">${escapeHtml(JSON.stringify(block, null, 2))}</pre>`);
                }

                return parts.join('');
            };

            const buildPreview = async () => {
                const title = document.getElementById('title')?.value || '';
                const excerpt = document.getElementById('excerpt')?.value || '';
                const newTags = document.getElementById('new_tags')?.value || '';
                const selectedTags = Array.from(document.querySelectorAll('input[name="tags[]"]:checked'))
                    .map((input) => input.closest('label')?.innerText?.trim())
                    .filter(Boolean);

                let contentHtml = '';
                if (wrapper?.__editorInstance?.save) {
                    try {
                        const data = await wrapper.__editorInstance.save();
                        contentHtml = renderBlocks(data?.blocks || []);
                    } catch {
                        contentHtml = '';
                    }
                }

                if (!contentHtml) {
                    const contentFallback = fallbackTextarea?.value || '';
                    contentHtml = contentFallback
                        ? `<p class="whitespace-pre-wrap">${escapeHtml(contentFallback)}</p>`
                        : `<p class="text-slate-500 dark:text-slate-300">EditorJS on izleme icin henuz hazir degil (icerigi doldurup tekrar deneyin).</p>`;
                }

                const tagLine = selectedTags.length ? selectedTags.join(', ') : '—';
                const newTagLine = newTags.trim() ? escapeHtml(newTags.trim()) : '—';

                return `
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500 dark:text-slate-300">Baslik</div>
                        <div class="text-base font-semibold text-slate-900 dark:text-white">${escapeHtml(title || '—')}</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500 dark:text-slate-300">Ozet</div>
                        <div class="whitespace-pre-wrap">${escapeHtml(excerpt || '—')}</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500 dark:text-slate-300">Etiketler</div>
                        <div>${escapeHtml(tagLine)}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-300">Yeni etiket</div>
                        <div>${newTagLine}</div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs text-slate-500 dark:text-slate-300">Icerik</div>
                        <div class="space-y-3">${contentHtml}</div>
                    </div>
                `;
            };

            const openPreviewButton = document.querySelector('[data-open-preview]');
            openPreviewButton?.addEventListener('click', async () => {
                if (!previewModal || !previewContent) return;
                previewModal.classList.remove('hidden');
                syncScrollLock();
                previewContent.innerHTML = '<p class="text-slate-500 dark:text-slate-300">Hazirlaniyor...</p>';
                previewContent.innerHTML = await buildPreview();
            });
        });
    </script>
@endpush





