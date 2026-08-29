@extends('layouts.app')

@section('title', __('post_create.page_title'))
@section('hide_global_header')@endsection
@section('no_container_padding')@endsection
@section('hide_feed_header')@endsection
@section('hide_mobile_bottom_nav')@endsection
@section('page_background_class', 'bg-[#f3f4f6]')

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        html, body { overflow: hidden; }
        .ografi-create, .ografi-create *:not(iconify-icon) { font-family: 'Roboto', Arial, Helvetica, sans-serif !important; }
        .ografi-create {
            --og-bg: #f3f4f6;
            --og-card: rgba(255,255,255,.88);
            --og-card-strong: rgba(255,255,255,.96);
            --og-soft: rgba(255,255,255,.58);
            --og-border: rgba(15,23,42,.13);
            --og-text: #17191d;
            --og-muted: #697386;
            --og-subtle: #98a2b3;
            --og-blue: #0a84ff;
            --og-shadow: inset 0 1px 0 rgba(255,255,255,.82), 0 8px 28px rgba(15,23,42,.06);
        }
        .og-shell { width: min(1180px, calc(100% - 24px)); margin: 0 auto; }
        .og-glass { border: .5px solid var(--og-border); background: var(--og-card); box-shadow: var(--og-shadow); backdrop-filter: blur(24px) saturate(155%); -webkit-backdrop-filter: blur(24px) saturate(155%); }
        .og-glass-strong { border: .5px solid var(--og-border); background: var(--og-card-strong); box-shadow: var(--og-shadow); backdrop-filter: blur(30px) saturate(165%); -webkit-backdrop-filter: blur(30px) saturate(165%); }
        .og-icon, .og-pill { border: .5px solid var(--og-border) !important; background: var(--og-soft) !important; color: var(--og-text) !important; box-shadow: none !important; outline: none !important; }
        .og-icon:hover, .og-pill:hover { background: var(--og-card-strong) !important; }
        .og-icon:active, .og-pill:active { transform: scale(.97); }
        .og-primary, .og-primary:hover { border-color: rgba(10,132,255,.86) !important; background: var(--og-blue) !important; color: #fff !important; }
        .og-field { width: 100% !important; border: .5px solid var(--og-border) !important; background: var(--og-soft) !important; color: var(--og-text) !important; box-shadow: none !important; outline: none !important; }
        .og-field:focus { border-color: rgba(10,132,255,.62) !important; background: var(--og-card-strong) !important; }
        .og-field::placeholder { color: var(--og-subtle) !important; }
        .og-info, .og-drawer, .og-backdrop, .og-preview { opacity: 0; visibility: hidden; pointer-events: none; }
        .og-info.is-open, .og-drawer.is-open, .og-backdrop.is-open, .og-preview.is-open { opacity: 1; visibility: visible; pointer-events: auto; }
        .og-info { transform: translateY(-4px) scale(.98); transition: opacity .16s ease, transform .16s ease, visibility .16s; }
        .og-info.is-open { transform: translateY(0) scale(1); }
        .og-drawer { width: min(400px, calc(100vw - 24px)); transform: translateX(calc(100% + 28px)); transition: transform .22s cubic-bezier(.32,.72,0,1), opacity .16s ease, visibility .22s; }
        .og-drawer.is-open { transform: translateX(0); }
        .og-backdrop, .og-preview { transition: opacity .18s ease, visibility .18s; }
        .ografi-create [data-editorjs-wrapper] .codex-editor,
        .ografi-create [data-editorjs-wrapper] .codex-editor *:not(iconify-icon) { font-family: 'Roboto', Arial, Helvetica, sans-serif !important; }
        .ografi-create [data-editorjs-wrapper] .codex-editor__redactor { padding-bottom: 140px !important; }
        .ografi-create [data-editorjs-wrapper] .ce-block__content,
        .ografi-create [data-editorjs-wrapper] .ce-toolbar__content { max-width: 760px !important; }
        .ografi-create [data-editorjs-wrapper] .ce-paragraph { color: var(--og-text) !important; font-size: 16px !important; font-weight: 400 !important; line-height: 1.72 !important; }
        .ografi-create [data-editorjs-wrapper] .ce-header { color: var(--og-text) !important; font-weight: 600 !important; line-height: 1.3 !important; }
        .ografi-create [data-editorjs-wrapper] .ce-toolbar__plus,
        .ografi-create [data-editorjs-wrapper] .ce-toolbar__settings-btn { width: 34px !important; height: 34px !important; border: .5px solid var(--og-border) !important; border-radius: 999px !important; background: var(--og-soft) !important; color: var(--og-muted) !important; box-shadow: none !important; }
        .ografi-create [data-editorjs-wrapper] .ce-popover,
        .ografi-create [data-editorjs-wrapper] .ce-inline-toolbar,
        .ografi-create [data-editorjs-wrapper] .ce-conversion-toolbar { border: .5px solid var(--og-border) !important; border-radius: 18px !important; background: var(--og-card-strong) !important; color: var(--og-text) !important; box-shadow: var(--og-shadow) !important; backdrop-filter: blur(24px) !important; -webkit-backdrop-filter: blur(24px) !important; }
        .ografi-create [data-editorjs-wrapper] .ce-popover-item__title,
        .ografi-create [data-editorjs-wrapper] .ce-popover-item__secondary-title,
        .ografi-create [data-editorjs-wrapper] .ce-inline-tool,
        .ografi-create [data-editorjs-wrapper] .ce-conversion-tool__label { color: var(--og-text) !important; }
        @media (max-width: 980px) { .og-workspace { grid-template-columns: 1fr !important; } .og-cover { position: static !important; } }
        @media (max-width: 640px) { .og-shell { width: calc(100% - 14px); } .og-publish iconify-icon { display: none; } }
        @media (prefers-reduced-motion: reduce) { .og-info, .og-drawer, .og-backdrop, .og-preview, .og-icon, .og-pill { transition: none !important; } }
    </style>
@endpush

@section('content')
@php
    $initialCategoryId = (int) old('category_id');
    $initialMetaContent = old('meta_description', old('excerpt', ''));
@endphp

<div class="ografi-create fixed inset-0 z-[99999] overflow-y-auto bg-[var(--og-bg)] text-[var(--og-text)]" data-create-page>
    <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="is_published" name="is_published" value="{{ old('is_published', 0) ? 1 : 0 }}">
        <input type="hidden" id="content_json" name="content_json" data-editor-json value="{{ old('content_json') }}">
        <input type="hidden" id="excerpt" name="excerpt" value="{{ old('excerpt', $initialMetaContent) }}">

        <div class="og-shell min-h-screen py-3 pb-10">
            <header class="og-glass-strong sticky top-2.5 z-[90] flex min-h-[60px] items-center justify-between gap-3 rounded-full p-2">
                <div class="flex min-w-0 items-center gap-2.5">
                    <a href="{{ route('blog.index') }}" class="og-icon inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full" aria-label="Geri" title="Geri">
                        <iconify-icon icon="lucide:chevron-left" class="text-[21px]"></iconify-icon>
                    </a>
                    <div class="min-w-0">
                        <div class="truncate text-[15px] font-semibold">Yeni gönderi</div>
                        <div class="truncate text-[12px] text-[var(--og-muted)]">Ografi Editor</div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    <div class="relative">
                        <button type="button" class="og-icon inline-flex h-[42px] w-[42px] items-center justify-center rounded-full" data-info-toggle aria-label="Yazı bilgisi" title="Yazı bilgisi" aria-expanded="false">
                            <iconify-icon icon="lucide:info" class="text-[19px]"></iconify-icon>
                        </button>
                        <div class="og-info og-glass-strong absolute right-0 top-[calc(100%+8px)] z-[130] w-[258px] overflow-hidden rounded-[22px]" data-info-popover aria-hidden="true">
                            <div class="border-b border-[var(--og-border)] px-3.5 py-3 text-[13px] font-semibold">Yazı bilgisi</div>
                            <div class="flex min-h-[42px] items-center justify-between gap-3 px-3.5 text-[13px] text-[var(--og-muted)]"><span>Okuma süresi</span><strong class="font-medium text-[var(--og-text)]" data-reading-time>1 dk okuma</strong></div>
                            <div class="flex min-h-[42px] items-center justify-between gap-3 border-t border-[var(--og-border)] px-3.5 text-[13px] text-[var(--og-muted)]"><span>Kelime</span><strong class="font-medium text-[var(--og-text)]" data-word-count>0 kelime</strong></div>
                            <div class="flex min-h-[42px] items-center justify-between gap-3 border-t border-[var(--og-border)] px-3.5 text-[13px] text-[var(--og-muted)]"><span>Kayıt</span><strong class="font-medium text-[var(--og-text)]">Taslak destekli</strong></div>
                        </div>
                    </div>

                    <button type="button" class="og-icon inline-flex h-[42px] w-[42px] items-center justify-center rounded-full" data-open-settings aria-label="Gelişmiş seçenekler" title="Gelişmiş seçenekler" aria-expanded="false">
                        <iconify-icon icon="lucide:settings" class="text-[19px]"></iconify-icon>
                    </button>

                    <button type="submit" class="og-pill og-primary og-publish inline-flex min-h-[42px] items-center gap-2 rounded-full px-4 text-[14px] font-medium" data-submit-intent="publish">
                        <iconify-icon icon="lucide:send" class="text-[16px]"></iconify-icon><span>Yayınla</span>
                    </button>
                </div>
            </header>

            @if ($errors->any())
                <div class="mt-3 rounded-[18px] border border-red-300/70 bg-red-50 px-4 py-3 text-[13px] text-red-800">
                    <div class="font-medium">Gönderi kaydedilemedi.</div>
                    <div class="mt-1">{{ $errors->first() }}</div>
                </div>
            @endif

            <div class="og-workspace mt-3.5 grid grid-cols-[minmax(0,1fr)_310px] items-start gap-3.5">
                <main class="og-glass-strong min-w-0 overflow-visible rounded-[28px] max-sm:rounded-[23px]">
                    <section class="px-7 pb-5 pt-6 max-sm:px-4 max-sm:pt-5">
                        <label for="title" class="mb-2 block text-[12px] font-medium text-[var(--og-muted)]">Başlık</label>
                        <textarea id="title" name="title" rows="1" required class="block min-h-[44px] w-full resize-none overflow-hidden border-0 bg-transparent p-0 text-[clamp(26px,2.45vw,32px)] font-semibold leading-[1.24] tracking-[-.018em] text-[var(--og-text)] outline-none placeholder:text-[var(--og-subtle)]" placeholder="Başlığını yaz...">{{ old('title') }}</textarea>
                    </section>

                    <section class="border-t border-[var(--og-border)] px-7 py-5 max-sm:px-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="meta_description" class="text-[12px] font-medium text-[var(--og-muted)]">Meta içerik</label>
                            <span class="text-[11px] text-[var(--og-subtle)]" data-meta-count>0/160</span>
                        </div>
                        <textarea id="meta_description" name="meta_description" maxlength="160" rows="3" class="og-field min-h-[86px] resize-none rounded-[16px] px-3.5 py-3 text-[14px] leading-6" placeholder="Gönderiyi kısa bir paragrafta özetle. Arama ve paylaşım açıklamasında kullanılır.">{{ $initialMetaContent }}</textarea>
                    </section>

                    <div class="flex min-h-[47px] items-center justify-between gap-3 border-y border-[var(--og-border)] px-[18px]">
                        <div class="flex items-center gap-2"><span class="text-[13px] font-semibold">Yazı</span><span class="text-[11px] text-[var(--og-muted)]">EditorJS</span></div>
                        <div class="flex items-center gap-2 text-[11px] text-[var(--og-muted)]"><span class="h-1.5 w-1.5 rounded-full bg-current opacity-50" data-editor-dot></span><span data-editor-status>EditorJS yükleniyor</span></div>
                    </div>

                    <section class="relative min-h-[62vh]" data-editorjs-wrapper>
                        <div x-ref="holder" class="min-h-[62vh] px-7 py-5 opacity-0 max-sm:px-4"></div>
                        <textarea id="content" name="content" data-editor-content data-mentionable="users" class="absolute inset-0 z-10 block min-h-[62vh] w-full resize-none border-0 bg-transparent px-7 py-5 text-[16px] font-normal leading-[1.72] text-[var(--og-text)] outline-none placeholder:text-[var(--og-subtle)] max-sm:px-4" placeholder="Gönderini yazmaya başla...">{{ old('content') }}</textarea>
                    </section>
                </main>

                <aside class="og-cover og-glass-strong sticky top-[84px] overflow-hidden rounded-[28px] max-sm:rounded-[23px]" aria-label="Kapak görseli">
                    <div class="flex min-h-[47px] items-center justify-between gap-3 border-b border-[var(--og-border)] px-[18px]">
                        <span class="text-[13px] font-semibold">Kapak görseli</span>
                        <iconify-icon icon="lucide:image" class="text-[17px] text-[var(--og-muted)]"></iconify-icon>
                    </div>
                    <div class="p-3">
                        <div class="relative min-h-[190px] overflow-hidden rounded-[20px] border border-[var(--og-border)] bg-[var(--og-soft)]">
                            <label for="featured_image" class="flex min-h-[190px] cursor-pointer flex-col items-center justify-center gap-2.5 p-5 text-center text-[var(--og-muted)]" data-cover-drop>
                                <span class="og-icon inline-flex h-[42px] w-[42px] items-center justify-center rounded-full"><iconify-icon icon="lucide:image-plus" class="text-[19px]"></iconify-icon></span>
                                <span class="text-[13px] font-medium text-[var(--og-text)]">Görsel seç</span>
                                <span class="text-[11px] leading-4">JPG, PNG veya WebP · en fazla 5 MB</span>
                            </label>
                            <div class="relative hidden min-h-[190px]" data-cover-preview>
                                <img data-cover-img alt="Kapak görseli ön izlemesi" class="block min-h-[190px] max-h-[360px] w-full object-cover">
                                <div class="absolute right-2.5 top-2.5 flex gap-1.5">
                                    <button type="button" class="og-icon inline-flex h-9 w-9 items-center justify-center rounded-full" data-cover-change aria-label="Görseli değiştir"><iconify-icon icon="lucide:pencil" class="text-[15px]"></iconify-icon></button>
                                    <button type="button" class="og-icon inline-flex h-9 w-9 items-center justify-center rounded-full" data-cover-remove aria-label="Görseli kaldır"><iconify-icon icon="lucide:x" class="text-[15px]"></iconify-icon></button>
                                </div>
                            </div>
                            <input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-cover-input>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div class="og-backdrop fixed inset-0 z-[108] bg-slate-950/35" data-settings-backdrop></div>
        <aside class="og-drawer og-glass-strong fixed bottom-3 right-3 top-3 z-[110] flex flex-col overflow-hidden rounded-[30px]" data-settings-panel aria-hidden="true">
            <header class="flex min-h-[68px] shrink-0 items-center justify-between gap-3 border-b border-[var(--og-border)] px-3.5 pl-[18px]">
                <div><div class="text-[15px] font-semibold">Gelişmiş seçenekler</div><div class="mt-0.5 text-[12px] text-[var(--og-muted)]">Yayın, SEO ve görünürlük</div></div>
                <button type="button" class="og-icon inline-flex h-10 w-10 items-center justify-center rounded-full" data-close-settings aria-label="Kapat"><iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon></button>
            </header>

            <div class="flex-1 space-y-2.5 overflow-y-auto p-3">
                <section class="overflow-hidden rounded-[20px] border border-[var(--og-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--og-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:wand-sparkles" class="text-[17px] text-[var(--og-muted)]"></iconify-icon>Araçlar</div>
                    <div class="grid grid-cols-2 gap-2 p-3">
                        <button type="button" class="og-pill inline-flex min-h-[41px] items-center justify-center gap-2 rounded-full px-3 text-[13px] font-medium" data-open-preview><iconify-icon icon="lucide:eye" class="text-[16px]"></iconify-icon>Ön izleme</button>
                        <button type="button" class="og-pill inline-flex min-h-[41px] items-center justify-center gap-2 rounded-full px-3 text-[13px] font-medium" data-ai-assist><iconify-icon icon="lucide:sparkles" data-ai-icon class="text-[16px]"></iconify-icon>AI yardım</button>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[20px] border border-[var(--og-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--og-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:layout-list" class="text-[17px] text-[var(--og-muted)]"></iconify-icon>Gönderi bilgileri</div>
                    <div class="space-y-3 p-3">
                        <div><label for="category_id" class="mb-1.5 block text-[12px] font-medium text-[var(--og-muted)]">Topluluk / kategori</label><select id="category_id" name="category_id" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]"><option value="">Kategori seç</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($initialCategoryId === (int) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                        <div><label for="new_tags" class="mb-1.5 block text-[12px] font-medium text-[var(--og-muted)]">Yeni etiketler</label><input id="new_tags" name="new_tags" type="text" value="{{ old('new_tags') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="laravel, tasarım, teknoloji"></div>
                        @if(isset($tags) && collect($tags)->isNotEmpty())
                            <div><div class="mb-1.5 text-[12px] font-medium text-[var(--og-muted)]">Mevcut etiketler</div><div class="flex max-h-36 flex-wrap gap-1.5 overflow-y-auto pr-1">@foreach($tags as $tag)<label class="relative inline-flex cursor-pointer"><input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer sr-only" @checked(collect(old('tags', []))->contains($tag->id))><span class="inline-flex min-h-[32px] items-center rounded-full border border-[var(--og-border)] bg-[var(--og-soft)] px-2.5 text-[12px] text-[var(--og-muted)] peer-checked:border-blue-400/40 peer-checked:bg-blue-500/10 peer-checked:text-blue-600">#{{ $tag->name }}</span></label>@endforeach</div></div>
                        @endif
                    </div>
                </section>

                <section class="overflow-hidden rounded-[20px] border border-[var(--og-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--og-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:calendar-clock" class="text-[17px] text-[var(--og-muted)]"></iconify-icon>Yayınlama</div>
                    <div class="space-y-3 p-3">
                        <div><label for="published_at" class="mb-1.5 block text-[12px] font-medium text-[var(--og-muted)]">Yayın tarihi</label><input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]"></div>
                        <div class="overflow-hidden rounded-[16px] border border-[var(--og-border)]">
                            <div class="flex min-h-[58px] items-center justify-between gap-3 px-3"><div><div class="text-[13px] font-medium">Yorumları kapat</div><div class="text-[11px] text-[var(--og-muted)]">Yeni yorum alınmaz.</div></div><x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" /></div>
                            <div class="flex min-h-[58px] items-center justify-between gap-3 border-t border-[var(--og-border)] px-3"><div><div class="text-[13px] font-medium">Hassas içerik</div><div class="text-[11px] text-[var(--og-muted)]">Uyarıyla gösterilir.</div></div><x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" /></div>
                            <div class="flex min-h-[58px] items-center justify-between gap-3 border-t border-[var(--og-border)] px-3"><div><div class="text-[13px] font-medium">Gönderiyi sabitle</div><div class="text-[11px] text-[var(--og-muted)]">Uygun alanlarda üstte gösterilir.</div></div><x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" /></div>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[20px] border border-[var(--og-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--og-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:search" class="text-[17px] text-[var(--og-muted)]"></iconify-icon>SEO</div>
                    <div class="space-y-3 p-3">
                        <div><label for="meta_title" class="mb-1.5 block text-[12px] font-medium text-[var(--og-muted)]">SEO başlığı</label><input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Arama sonucunda görünecek başlık"></div>
                        <div><label for="slug" class="mb-1.5 block text-[12px] font-medium text-[var(--og-muted)]">Özel bağlantı</label><input id="slug" name="slug" type="text" value="{{ old('slug') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="ornek-gonderi"></div>
                        <div><label for="meta_keywords" class="mb-1.5 block text-[12px] font-medium text-[var(--og-muted)]">Anahtar kelimeler</label><input id="meta_keywords" name="meta_keywords" type="text" value="{{ old('meta_keywords') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="teknoloji, yazılım, gündem"></div>
                        <p class="text-[11px] leading-4 text-[var(--og-muted)]">Meta içerik ana editörde tek kez bulunur.</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[20px] border border-[var(--og-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--og-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:copyright" class="text-[17px] text-[var(--og-muted)]"></iconify-icon>Görsel hakları</div>
                    <div class="space-y-2.5 p-3">
                        <input name="image_creator_name" type="text" value="{{ old('image_creator_name') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Görsel üreticisi / fotoğrafçı">
                        <input name="image_credit_text" type="text" value="{{ old('image_credit_text') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Görsel kredisi">
                        <input name="image_copyright_notice" type="text" value="{{ old('image_copyright_notice') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Telif bildirimi">
                        <input name="image_license_url" type="url" value="{{ old('image_license_url') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Lisans bağlantısı">
                        <input name="image_acquire_url" type="url" value="{{ old('image_acquire_url') }}" class="og-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Kaynak / satın alma bağlantısı">
                    </div>
                </section>
            </div>

            <footer class="shrink-0 border-t border-[var(--og-border)] p-3">
                <button type="submit" class="og-pill inline-flex min-h-[42px] w-full items-center justify-center gap-2 rounded-full px-4 text-[14px] font-medium" data-submit-intent="draft"><iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>Taslağa kaydet</button>
            </footer>
        </aside>
    </form>

    <div class="og-preview fixed inset-0 z-[140] overflow-y-auto bg-slate-950/45 p-4" data-preview-modal aria-hidden="true">
        <div class="og-glass-strong mx-auto my-[4vh] w-[min(760px,100%)] overflow-hidden rounded-[28px]">
            <header class="flex min-h-[66px] items-center justify-between gap-3 border-b border-[var(--og-border)] px-4">
                <div><div class="text-[15px] font-semibold">Gönderi ön izlemesi</div><div class="text-[12px] text-[var(--og-muted)]">Yayınlamadan önce son görünüm</div></div>
                <button type="button" class="og-icon inline-flex h-10 w-10 items-center justify-center rounded-full" data-close-preview aria-label="Kapat"><iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon></button>
            </header>
            <div class="max-h-[78vh] overflow-y-auto px-6 py-6 max-sm:px-4"><div data-preview-content></div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- EditorJS dosyalari burada dogrudan ve sirali yuklenir. Dinamik yukleyiciye bagimli kalmaz. --}}
    <script src="{{ asset('vendor/editorjs/editorjs.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/header.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/list.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/quote.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/table.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/image.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/checklist.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/code.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/delimiter.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/embed.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/link.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/inline-code.umd.js') }}"></script>
    <script src="{{ asset('vendor/editorjs/marker.umd.js') }}"></script>
    <script>window.List = window.List || window.EditorjsList;</script>

    {{-- Ografi'nin tum ozel EditorJS bloklarini da koru. --}}
    @include('filament.assets.editorjs')

    <script>
        (() => {
            const boot = async () => {
                const page = document.querySelector('[data-create-page]');
                if (!page || page.dataset.booted === '1') return;
                page.dataset.booted = '1';

                const form = document.getElementById('post-create-form');
                const wrapper = page.querySelector('[data-editorjs-wrapper]');
                const holder = wrapper?.querySelector('[x-ref="holder"]');
                const content = document.getElementById('content');
                const contentJson = document.getElementById('content_json');
                const title = document.getElementById('title');
                const meta = document.getElementById('meta_description');
                const excerpt = document.getElementById('excerpt');
                const published = document.getElementById('is_published');
                const status = page.querySelector('[data-editor-status]');
                const dot = page.querySelector('[data-editor-dot]');
                const words = page.querySelector('[data-word-count]');
                const reading = page.querySelector('[data-reading-time]');
                const infoToggle = page.querySelector('[data-info-toggle]');
                const info = page.querySelector('[data-info-popover]');
                const drawer = page.querySelector('[data-settings-panel]');
                const backdrop = page.querySelector('[data-settings-backdrop]');
                const drawerToggle = page.querySelector('[data-open-settings]');
                const preview = page.querySelector('[data-preview-modal]');
                const previewContent = page.querySelector('[data-preview-content]');
                const coverInput = page.querySelector('[data-cover-input]');
                const coverDrop = page.querySelector('[data-cover-drop]');
                const coverPreview = page.querySelector('[data-cover-preview]');
                const coverImg = page.querySelector('[data-cover-img]');
                const aiButton = page.querySelector('[data-ai-assist]');
                const aiIcon = page.querySelector('[data-ai-icon]');
                const metaCount = page.querySelector('[data-meta-count]');

                const toast = (message, isError = false) => {
                    document.querySelectorAll('[data-og-toast]').forEach((el) => el.remove());
                    const el = document.createElement('div');
                    el.dataset.ogToast = '1';
                    el.className = 'og-glass-strong fixed bottom-5 left-1/2 z-[180] w-[min(430px,94vw)] -translate-x-1/2 rounded-[18px] px-4 py-3 text-[13px]';
                    el.textContent = message;
                    if (isError) el.style.color = '#dc2626';
                    document.body.appendChild(el);
                    setTimeout(() => el.remove(), isError ? 5200 : 3200);
                };

                const autoGrowTitle = () => {
                    if (!title) return;
                    title.style.height = 'auto';
                    title.style.height = `${Math.max(44, title.scrollHeight)}px`;
                };
                title?.addEventListener('input', autoGrowTitle);
                autoGrowTitle();

                const syncMeta = () => {
                    if (!meta) return;
                    if (excerpt) excerpt.value = meta.value;
                    if (metaCount) metaCount.textContent = `${meta.value.length}/160`;
                };
                meta?.addEventListener('input', syncMeta);
                syncMeta();

                const escapeHtml = (value) => String(value ?? '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;').replaceAll("'", '&#039;');

                const simpleBlocksToHtml = (blocks = []) => blocks.map((block) => {
                    const data = block?.data || {};
                    if (block?.type === 'header') return `<h2>${data.text || ''}</h2>`;
                    if (block?.type === 'quote') return `<blockquote>${data.text || ''}</blockquote>`;
                    if (block?.type === 'list' && Array.isArray(data.items)) return `<ul>${data.items.map((item) => `<li>${typeof item === 'string' ? item : (item?.content || item?.text || '')}</li>`).join('')}</ul>`;
                    return data.text ? `<p>${data.text}</p>` : '';
                }).join('');

                const syncEditor = async () => {
                    if (!wrapper?.__editorInstance?.save) return null;
                    const output = await wrapper.__editorInstance.save();
                    if (contentJson) contentJson.value = JSON.stringify(output);
                    if (content) {
                        content.value = window.filamentEditorBlocksToHtml
                            ? window.filamentEditorBlocksToHtml(output.blocks || [])
                            : simpleBlocksToHtml(output.blocks || []);
                    }
                    return output;
                };

                const waitReady = async (editor) => {
                    if (!editor?.isReady?.then) return;
                    await Promise.race([
                        editor.isReady,
                        new Promise((_, reject) => setTimeout(() => reject(new Error('EditorJS timeout')), 7000)),
                    ]);
                };

                const editorReady = (label = 'EditorJS hazır') => {
                    holder?.classList.remove('opacity-0');
                    content?.classList.add('hidden');
                    if (status) status.textContent = label;
                    dot?.classList.remove('opacity-50');
                    dot?.classList.add('text-emerald-500');
                };

                const editorFallback = () => {
                    holder?.classList.add('opacity-0');
                    content?.classList.remove('hidden');
                    if (status) status.textContent = 'Temel yazı alanı';
                    dot?.classList.remove('opacity-50');
                    dot?.classList.add('text-amber-500');
                };

                const initMinimalEditor = async () => {
                    if (!window.EditorJS || !holder || !wrapper) throw new Error('EditorJS core missing');
                    holder.innerHTML = '';
                    const tools = {};
                    if (window.Header) tools.header = window.Header;
                    if (window.List || window.EditorjsList) tools.list = { class: window.List || window.EditorjsList, inlineToolbar: true };
                    if (window.Quote) tools.quote = { class: window.Quote, inlineToolbar: true };
                    if (window.Checklist) tools.checklist = { class: window.Checklist, inlineToolbar: true };
                    if (window.Table) tools.table = { class: window.Table, inlineToolbar: true };
                    if (window.CodeTool) tools.code = window.CodeTool;
                    if (window.Delimiter) tools.delimiter = window.Delimiter;
                    if (window.Embed) tools.embed = window.Embed;
                    if (window.InlineCode) tools.inlineCode = window.InlineCode;
                    if (window.Marker) tools.marker = window.Marker;
                    if (window.LinkTool) tools.linkTool = { class: window.LinkTool, config: { endpoint: "{{ route('blog.editorjs.link', [], false) }}" } };
                    if (window.ImageTool) tools.image = {
                        class: window.ImageTool,
                        config: {
                            captionPlaceholder: 'Açıklama (opsiyonel)',
                            uploader: {
                                uploadByFile: async (file) => {
                                    const data = new FormData();
                                    data.append('image', file);
                                    const response = await fetch("{{ route('blog.editorjs.image', [], false) }}", {
                                        method: 'POST', credentials: 'same-origin',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                                        body: data,
                                    });
                                    return response.json();
                                },
                            },
                        },
                    };

                    let initialData = { blocks: [] };
                    if (contentJson?.value) {
                        try { initialData = JSON.parse(contentJson.value); } catch {}
                    } else if (content?.value.trim()) {
                        initialData = { blocks: [{ type: 'paragraph', data: { text: content.value } }] };
                    }

                    const editor = new window.EditorJS({
                        holder,
                        data: initialData,
                        placeholder: 'Gönderini yazmaya başla...',
                        tools,
                        async onChange() {
                            try { await syncEditor(); scheduleStats(); } catch (error) { console.error('EditorJS sync error', error); }
                        },
                    });
                    wrapper.__editorInstance = editor;
                    await waitReady(editor);
                };

                const initEditor = async () => {
                    if (!wrapper || !holder || !content || !contentJson) { editorFallback(); return; }

                    try {
                        if (typeof window.initFilamentEditorJsField === 'function') {
                            await window.initFilamentEditorJsField(wrapper);
                            if (wrapper.__editorInstance) {
                                await waitReady(wrapper.__editorInstance);
                                editorReady();
                                return;
                            }
                        }
                    } catch (error) {
                        console.error('Full EditorJS init error', error);
                        try { await wrapper.__editorInstance?.destroy?.(); } catch {}
                        wrapper.__editorInstance = null;
                    }

                    try {
                        await initMinimalEditor();
                        editorReady();
                    } catch (error) {
                        console.error('Fallback EditorJS init error', error);
                        editorFallback();
                        toast('EditorJS açılamadı; içerik alanı kullanılabilir durumda bırakıldı.', true);
                    }
                };

                const stripHtml = (html) => {
                    const el = document.createElement('div');
                    el.innerHTML = String(html || '');
                    return el.textContent || '';
                };

                const readPlainText = async () => {
                    if (wrapper?.__editorInstance?.save) {
                        try {
                            const output = await wrapper.__editorInstance.save();
                            return (output.blocks || []).map((block) => {
                                const data = block?.data || {};
                                const values = [data.text, data.caption, data.question, data.answer, data.title, data.label, data.message, data.note];
                                if (Array.isArray(data.items)) data.items.forEach((item) => values.push(typeof item === 'string' ? item : (item?.content || item?.text || '')));
                                return values.filter((value) => typeof value === 'string').map(stripHtml).join(' ');
                            }).join(' ').trim();
                        } catch {}
                    }
                    return stripHtml(content?.value || '').trim();
                };

                const updateStats = async () => {
                    const text = await readPlainText();
                    const count = (text.match(/\S+/g) || []).length;
                    if (words) words.textContent = `${count} kelime`;
                    if (reading) reading.textContent = `${Math.max(1, Math.ceil(count / 200))} dk okuma`;
                };
                let statsTimer = null;
                const scheduleStats = () => { clearTimeout(statsTimer); statsTimer = setTimeout(updateStats, 260); };
                wrapper?.addEventListener('input', scheduleStats);
                content?.addEventListener('input', scheduleStats);
                title?.addEventListener('input', scheduleStats);

                const closeInfo = () => { info?.classList.remove('is-open'); info?.setAttribute('aria-hidden', 'true'); infoToggle?.setAttribute('aria-expanded', 'false'); };
                infoToggle?.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const open = !info?.classList.contains('is-open');
                    info?.classList.toggle('is-open', open);
                    info?.setAttribute('aria-hidden', open ? 'false' : 'true');
                    infoToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
                document.addEventListener('click', (event) => {
                    if (!info?.classList.contains('is-open')) return;
                    if (event.target instanceof Element && (info.contains(event.target) || infoToggle?.contains(event.target))) return;
                    closeInfo();
                });

                const openDrawer = () => { drawer?.classList.add('is-open'); backdrop?.classList.add('is-open'); drawer?.setAttribute('aria-hidden', 'false'); drawerToggle?.setAttribute('aria-expanded', 'true'); };
                const closeDrawer = () => { drawer?.classList.remove('is-open'); backdrop?.classList.remove('is-open'); drawer?.setAttribute('aria-hidden', 'true'); drawerToggle?.setAttribute('aria-expanded', 'false'); };
                drawerToggle?.addEventListener('click', () => drawer?.classList.contains('is-open') ? closeDrawer() : openDrawer());
                page.querySelector('[data-close-settings]')?.addEventListener('click', closeDrawer);
                backdrop?.addEventListener('click', closeDrawer);

                const setCover = (file) => {
                    if (!file || !coverInput || !coverImg) return;
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) { coverInput.value = ''; toast('Kapak JPG, PNG veya WebP olmalı.', true); return; }
                    if (file.size > 5 * 1024 * 1024) { coverInput.value = ''; toast('Kapak görseli en fazla 5 MB olabilir.', true); return; }
                    const reader = new FileReader();
                    reader.onload = () => { coverImg.src = String(reader.result || ''); coverDrop?.classList.add('hidden'); coverPreview?.classList.remove('hidden'); };
                    reader.readAsDataURL(file);
                };
                coverInput?.addEventListener('change', () => { const file = coverInput.files?.[0]; if (file) setCover(file); });
                page.querySelector('[data-cover-change]')?.addEventListener('click', () => coverInput?.click());
                page.querySelector('[data-cover-remove]')?.addEventListener('click', () => { if (coverInput) coverInput.value = ''; if (coverImg) coverImg.src = ''; coverPreview?.classList.add('hidden'); coverDrop?.classList.remove('hidden'); });

                const buildPreview = async () => {
                    try { await syncEditor(); } catch {}
                    const image = coverImg?.src ? `<img src="${escapeHtml(coverImg.src)}" alt="" class="mb-5 w-full rounded-[20px] object-cover">` : '';
                    const titleText = String(title?.value || '').trim();
                    const metaText = String(meta?.value || '').trim();
                    return `${image}<h1 class="text-[30px] font-semibold leading-tight">${escapeHtml(titleText || 'Başlıksız gönderi')}</h1>${metaText ? `<p class="mt-3 text-[15px] leading-6 text-[var(--og-muted)]">${escapeHtml(metaText)}</p>` : ''}<div class="mt-7 text-[16px] leading-7">${content?.value || '<p>Henüz içerik yok.</p>'}</div>`;
                };
                const openPreview = async () => { if (!preview || !previewContent) return; previewContent.innerHTML = await buildPreview(); preview.classList.add('is-open'); preview.setAttribute('aria-hidden', 'false'); closeDrawer(); };
                const closePreview = () => { preview?.classList.remove('is-open'); preview?.setAttribute('aria-hidden', 'true'); };
                page.querySelector('[data-open-preview]')?.addEventListener('click', openPreview);
                page.querySelector('[data-close-preview]')?.addEventListener('click', closePreview);
                preview?.addEventListener('click', (event) => { if (event.target === preview) closePreview(); });

                let aiBusy = false;
                aiButton?.addEventListener('click', async () => {
                    if (aiBusy) return;
                    const titleText = String(title?.value || '').trim();
                    const text = await readPlainText();
                    if (!titleText && !text) { toast('Önce başlık veya içerik yazın.', true); return; }
                    aiBusy = true; aiButton.disabled = true; aiIcon?.setAttribute('icon', 'lucide:loader-circle');
                    try {
                        const response = await fetch('{{ route('blog.ai-assist') }}', {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify({ title: titleText, content: text }),
                        });
                        const data = await response.json();
                        if (!response.ok || !data.ok) throw new Error(data.message || 'Yapay zeka isteği başarısız.');
                        const metaTitle = document.getElementById('meta_title');
                        const metaKeywords = document.getElementById('meta_keywords');
                        if (data.meta_title && metaTitle) metaTitle.value = data.meta_title;
                        if (data.meta_description && meta) { meta.value = String(data.meta_description).slice(0, 160); syncMeta(); }
                        if (Array.isArray(data.meta_keywords) && metaKeywords) metaKeywords.value = data.meta_keywords.join(', ');
                        toast('AI önerileri işlendi.');
                    } catch (error) { toast(error?.message || 'Yapay zeka isteği başarısız.', true); }
                    finally { aiBusy = false; aiButton.disabled = false; aiIcon?.setAttribute('icon', 'lucide:sparkles'); }
                });

                let submitting = false;
                page.querySelectorAll('[data-submit-intent]').forEach((button) => {
                    button.addEventListener('click', async (event) => {
                        event.preventDefault();
                        if (!form || submitting) return;
                        try { await syncEditor(); } catch {}
                        syncMeta();
                        if (!String(title?.value || '').trim()) { toast('Başlık boş olamaz.', true); title?.focus(); return; }
                        if (!String(content?.value || '').trim()) { toast('İçerik boş olamaz.', true); return; }
                        if (published) published.value = button.dataset.submitIntent === 'draft' ? '0' : '1';
                        submitting = true;
                        button.disabled = true;
                        form.submit();
                    });
                });

                document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeInfo(); closeDrawer(); closePreview(); } });

                await initEditor();
                updateStats();
            };

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
            else boot();
        })();
    </script>
@endpush
