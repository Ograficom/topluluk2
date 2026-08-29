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
        .ografi-composer, .ografi-composer *:not(iconify-icon) { font-family: 'Roboto', Arial, Helvetica, sans-serif !important; }
        .ografi-composer { --oc-bg:#f3f4f6; --oc-surface:rgba(255,255,255,.82); --oc-surface-strong:rgba(255,255,255,.94); --oc-soft:rgba(255,255,255,.55); --oc-border:rgba(15,23,42,.13); --oc-text:#17191d; --oc-muted:#697386; --oc-subtle:#98a2b3; --oc-blue:#0a84ff; --oc-shadow:inset 0 1px 0 rgba(255,255,255,.8),0 8px 30px rgba(15,23,42,.06); }
        html.dark .ografi-composer, html[data-system-theme="dark"] .ografi-composer, html[data-theme="dark"] .ografi-composer { --oc-bg:#090d14; --oc-surface:rgba(15,23,42,.82); --oc-surface-strong:rgba(15,23,42,.94); --oc-soft:rgba(30,41,59,.62); --oc-border:rgba(255,255,255,.12); --oc-text:#f8fafc; --oc-muted:#a7b1c2; --oc-subtle:#748196; --oc-shadow:inset 0 1px 0 rgba(255,255,255,.07),0 10px 32px rgba(0,0,0,.2); }
        .oc-glass { border:.5px solid var(--oc-border); background:var(--oc-surface); box-shadow:var(--oc-shadow); backdrop-filter:blur(24px) saturate(155%); -webkit-backdrop-filter:blur(24px) saturate(155%); }
        .oc-glass-strong { border:.5px solid var(--oc-border); background:var(--oc-surface-strong); box-shadow:var(--oc-shadow); backdrop-filter:blur(30px) saturate(165%); -webkit-backdrop-filter:blur(30px) saturate(165%); }
        .oc-icon-btn, .oc-pill-btn { border:.5px solid var(--oc-border)!important; background:var(--oc-soft)!important; color:var(--oc-text)!important; box-shadow:none!important; outline:none!important; }
        .oc-icon-btn:hover, .oc-pill-btn:hover { background:var(--oc-surface-strong)!important; }
        .oc-icon-btn:active, .oc-pill-btn:active { transform:scale(.97); }
        .oc-primary, .oc-primary:hover { border-color:rgba(10,132,255,.85)!important; background:var(--oc-blue)!important; color:white!important; }
        .oc-field { width:100%!important; border:.5px solid var(--oc-border)!important; background:var(--oc-soft)!important; color:var(--oc-text)!important; box-shadow:none!important; outline:none!important; }
        .oc-field:focus { border-color:rgba(10,132,255,.62)!important; background:var(--oc-surface-strong)!important; }
        .oc-field::placeholder { color:var(--oc-subtle)!important; }
        .ografi-composer [data-editorjs-wrapper] .codex-editor,
        .ografi-composer [data-editorjs-wrapper] .codex-editor *:not(iconify-icon) { font-family:'Roboto',Arial,Helvetica,sans-serif!important; }
        .ografi-composer [data-editorjs-wrapper] .codex-editor__redactor { padding-bottom:140px!important; }
        .ografi-composer [data-editorjs-wrapper] .ce-block__content,
        .ografi-composer [data-editorjs-wrapper] .ce-toolbar__content { max-width:760px!important; }
        .ografi-composer [data-editorjs-wrapper] .ce-paragraph { color:var(--oc-text)!important; font-size:16px!important; font-weight:400!important; line-height:1.72!important; }
        .ografi-composer [data-editorjs-wrapper] .ce-header { color:var(--oc-text)!important; font-weight:600!important; line-height:1.3!important; }
        .ografi-composer [data-editorjs-wrapper] .ce-toolbar__plus,
        .ografi-composer [data-editorjs-wrapper] .ce-toolbar__settings-btn { width:34px!important; height:34px!important; border:.5px solid var(--oc-border)!important; border-radius:999px!important; background:var(--oc-soft)!important; color:var(--oc-muted)!important; box-shadow:none!important; }
        .ografi-composer [data-editorjs-wrapper] .ce-popover,
        .ografi-composer [data-editorjs-wrapper] .ce-inline-toolbar,
        .ografi-composer [data-editorjs-wrapper] .ce-conversion-toolbar { border:.5px solid var(--oc-border)!important; border-radius:18px!important; background:var(--oc-surface-strong)!important; color:var(--oc-text)!important; box-shadow:var(--oc-shadow)!important; backdrop-filter:blur(24px)!important; -webkit-backdrop-filter:blur(24px)!important; }
        .ografi-composer [data-editorjs-wrapper] .ce-popover-item__title,
        .ografi-composer [data-editorjs-wrapper] .ce-popover-item__secondary-title,
        .ografi-composer [data-editorjs-wrapper] .ce-inline-tool,
        .ografi-composer [data-editorjs-wrapper] .ce-conversion-tool__label { color:var(--oc-text)!important; }
        .oc-drawer, .oc-backdrop, .oc-info, .oc-preview { opacity:0; visibility:hidden; pointer-events:none; }
        .oc-drawer.is-open, .oc-backdrop.is-open, .oc-info.is-open, .oc-preview.is-open { opacity:1; visibility:visible; pointer-events:auto; }
        .oc-drawer { transform:translateX(calc(100% + 30px)); transition:transform .22s cubic-bezier(.32,.72,0,1),opacity .16s ease,visibility .22s; }
        .oc-drawer.is-open { transform:translateX(0); }
        .oc-info { transform:translateY(-4px) scale(.98); transition:opacity .16s ease,transform .16s ease,visibility .16s; }
        .oc-info.is-open { transform:translateY(0) scale(1); }
        .oc-backdrop, .oc-preview { transition:opacity .18s ease,visibility .18s; }
        html.dark .ografi-composer [data-editorjs-wrapper] :is(.bg-white,.bg-slate-50,.bg-gray-50) { background-color:rgba(15,23,42,.86)!important; }
        html.dark .ografi-composer [data-editorjs-wrapper] :is(.text-slate-900,.text-slate-800,.text-slate-700) { color:#e8edf5!important; }
        @media (prefers-reduced-motion: reduce) { .oc-drawer,.oc-backdrop,.oc-info,.oc-preview,.oc-icon-btn,.oc-pill-btn { transition:none!important; } }
    </style>
@endpush

@section('content')
@php
    $initialCategoryId = (int) old('category_id');
    $initialMeta = old('meta_description', old('excerpt', ''));
@endphp

<div class="ografi-composer fixed inset-0 z-[99999] overflow-y-auto bg-[var(--oc-bg)] text-[var(--oc-text)]" data-create-page>
    <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published', 0) ? 1 : 0 }}">
        <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">
        <input type="hidden" name="excerpt" id="excerpt" value="{{ old('excerpt', $initialMeta) }}">

        <div class="mx-auto min-h-screen w-[min(1180px,calc(100%-24px))] py-3 pb-10">
            <header class="oc-glass-strong sticky top-2.5 z-[90] flex min-h-[60px] items-center justify-between gap-3 rounded-full p-2">
                <div class="flex min-w-0 items-center gap-2.5">
                    <a href="{{ route('blog.index') }}" class="oc-icon-btn inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full" aria-label="Geri" title="Geri">
                        <iconify-icon icon="lucide:chevron-left" class="text-[21px]"></iconify-icon>
                    </a>
                    <div class="min-w-0">
                        <div class="truncate text-[15px] font-semibold">Yeni gönderi</div>
                        <div class="truncate text-[12px] text-[var(--oc-muted)]">Ografi Editor</div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    <div class="relative">
                        <button type="button" class="oc-icon-btn inline-flex h-[42px] w-[42px] items-center justify-center rounded-full" data-info-toggle aria-label="Yazı bilgisi" title="Yazı bilgisi" aria-expanded="false">
                            <iconify-icon icon="lucide:info" class="text-[19px]"></iconify-icon>
                        </button>
                        <div class="oc-info oc-glass-strong absolute right-0 top-[calc(100%+8px)] z-[130] w-[258px] overflow-hidden rounded-[22px]" data-info-popover aria-hidden="true">
                            <div class="border-b border-[var(--oc-border)] px-3.5 py-3 text-[13px] font-semibold">Yazı bilgisi</div>
                            <div class="flex min-h-[42px] items-center justify-between gap-3 px-3.5 text-[13px] text-[var(--oc-muted)]"><span>Okuma süresi</span><strong class="font-medium text-[var(--oc-text)]" data-reading-time>1 dk okuma</strong></div>
                            <div class="flex min-h-[42px] items-center justify-between gap-3 border-t border-[var(--oc-border)] px-3.5 text-[13px] text-[var(--oc-muted)]"><span>Kelime</span><strong class="font-medium text-[var(--oc-text)]" data-word-count>0 kelime</strong></div>
                            <div class="flex min-h-[42px] items-center justify-between gap-3 border-t border-[var(--oc-border)] px-3.5 text-[13px] text-[var(--oc-muted)]"><span>Kayıt</span><strong class="font-medium text-[var(--oc-text)]">Taslak destekli</strong></div>
                        </div>
                    </div>

                    <button type="button" class="oc-icon-btn inline-flex h-[42px] w-[42px] items-center justify-center rounded-full" data-open-settings aria-label="Gelişmiş seçenekler" title="Gelişmiş seçenekler" aria-expanded="false">
                        <iconify-icon icon="lucide:settings" class="text-[19px]"></iconify-icon>
                    </button>

                    <button type="submit" class="oc-pill-btn oc-primary inline-flex min-h-[42px] items-center gap-2 rounded-full px-4 text-[14px] font-medium" data-submit-intent="publish">
                        <iconify-icon icon="lucide:send" class="text-[16px]"></iconify-icon>
                        <span>Yayınla</span>
                    </button>
                </div>
            </header>

            @if ($errors->any())
                <div class="mt-3 rounded-[18px] border border-red-300/60 bg-red-50/90 px-4 py-3 text-[13px] text-red-800 dark:bg-red-950/30 dark:text-red-200">
                    <div class="font-medium">Gönderi kaydedilemedi.</div>
                    <div class="mt-1">{{ $errors->first() }}</div>
                </div>
            @endif

            <div class="mt-3.5 grid grid-cols-[minmax(0,1fr)_310px] items-start gap-3.5 max-[980px]:grid-cols-1">
                <main class="oc-glass-strong min-w-0 overflow-visible rounded-[28px] max-sm:rounded-[23px]">
                    <div class="px-7 pb-5 pt-6 max-sm:px-4 max-sm:pt-5">
                        <label for="title" class="mb-2 block text-[12px] font-medium text-[var(--oc-muted)]">Başlık</label>
                        <textarea id="title" name="title" rows="1" required data-autogrow class="block min-h-[44px] w-full resize-none overflow-hidden border-0 bg-transparent p-0 text-[clamp(26px,2.45vw,32px)] font-semibold leading-[1.24] tracking-[-.018em] text-[var(--oc-text)] outline-none placeholder:text-[var(--oc-subtle)]" placeholder="Başlığını yaz...">{{ old('title') }}</textarea>
                    </div>

                    <div class="border-t border-[var(--oc-border)] px-7 py-5 max-sm:px-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="meta_description" class="block text-[12px] font-medium text-[var(--oc-muted)]">Meta içerik</label>
                            <span class="text-[11px] text-[var(--oc-subtle)]" data-meta-description-count>0/160</span>
                        </div>
                        <textarea id="meta_description" name="meta_description" maxlength="160" rows="3" class="oc-field min-h-[86px] resize-none rounded-[16px] px-3.5 py-3 text-[14px] leading-6" placeholder="Gönderiyi tek paragrafta özetle. Arama ve paylaşım açıklamasında kullanılır.">{{ $initialMeta }}</textarea>
                    </div>

                    <div class="flex min-h-[47px] items-center justify-between gap-3 border-y border-[var(--oc-border)] px-[18px]">
                        <div class="flex items-center gap-2">
                            <div class="text-[13px] font-semibold">Yazı</div>
                            <span class="text-[11px] text-[var(--oc-muted)]">EditorJS</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-[var(--oc-muted)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-current opacity-50" data-editor-dot></span>
                            <span data-editor-status>EditorJS yükleniyor</span>
                        </div>
                    </div>

                    <div class="relative min-h-[62vh]" data-editorjs-wrapper>
                        <div x-ref="holder" class="min-h-[62vh] px-7 py-5 opacity-0 max-sm:px-4"></div>
                        <textarea id="content" name="content" data-editor-content data-mentionable="users" class="absolute inset-0 z-10 block min-h-[62vh] w-full resize-none border-0 bg-transparent px-7 py-5 text-[16px] font-normal leading-[1.72] text-[var(--oc-text)] outline-none placeholder:text-[var(--oc-subtle)] max-sm:px-4" placeholder="Gönderini yazmaya başla...">{{ old('content') }}</textarea>
                    </div>
                </main>

                <aside class="oc-glass-strong sticky top-[84px] overflow-hidden rounded-[28px] max-[980px]:static max-sm:rounded-[23px]" aria-label="Kapak görseli">
                    <div class="flex min-h-[47px] items-center justify-between gap-3 border-b border-[var(--oc-border)] px-[18px]">
                        <div class="text-[13px] font-semibold">Kapak görseli</div>
                        <iconify-icon icon="lucide:image" class="text-[17px] text-[var(--oc-muted)]"></iconify-icon>
                    </div>
                    <div class="p-3">
                        <div class="relative min-h-[190px] overflow-hidden rounded-[20px] border border-[var(--oc-border)] bg-[var(--oc-soft)]" data-cover-field>
                            <label for="featured_image" class="flex min-h-[190px] cursor-pointer flex-col items-center justify-center gap-2.5 p-5 text-center text-[var(--oc-muted)]" data-cover-drop>
                                <span class="oc-icon-btn inline-flex h-[42px] w-[42px] items-center justify-center rounded-full"><iconify-icon icon="lucide:image-plus" class="text-[19px]"></iconify-icon></span>
                                <span class="text-[13px] font-medium text-[var(--oc-text)]">Görsel seç</span>
                                <span class="text-[11px] leading-4">JPG, PNG veya WebP · en fazla 5 MB</span>
                            </label>
                            <div class="relative hidden min-h-[190px]" data-cover-preview>
                                <img data-cover-preview-img alt="Kapak görseli ön izlemesi" class="block min-h-[190px] max-h-[360px] w-full object-cover">
                                <div class="absolute right-2.5 top-2.5 flex gap-1.5">
                                    <button type="button" class="oc-icon-btn inline-flex h-9 w-9 items-center justify-center rounded-full" data-cover-change aria-label="Görseli değiştir" title="Görseli değiştir"><iconify-icon icon="lucide:pencil" class="text-[15px]"></iconify-icon></button>
                                    <button type="button" class="oc-icon-btn inline-flex h-9 w-9 items-center justify-center rounded-full" data-cover-remove aria-label="Görseli kaldır" title="Görseli kaldır"><iconify-icon icon="lucide:x" class="text-[15px]"></iconify-icon></button>
                                </div>
                            </div>
                            <input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-cover-input>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div class="oc-backdrop fixed inset-0 z-[108] bg-slate-950/35" data-settings-backdrop></div>
        <aside class="oc-drawer oc-glass-strong fixed bottom-3 right-3 top-3 z-[110] flex w-[min(400px,calc(100vw-24px))] flex-col overflow-hidden rounded-[30px]" data-settings-panel aria-label="Gelişmiş seçenekler" aria-hidden="true">
            <div class="flex min-h-[68px] shrink-0 items-center justify-between gap-3 border-b border-[var(--oc-border)] px-3.5 pl-[18px]">
                <div>
                    <div class="text-[15px] font-semibold">Gelişmiş seçenekler</div>
                    <div class="mt-0.5 text-[12px] text-[var(--oc-muted)]">Yayın, SEO ve görünürlük</div>
                </div>
                <button type="button" class="oc-icon-btn inline-flex h-10 w-10 items-center justify-center rounded-full" data-close-settings aria-label="Kapat" title="Kapat"><iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon></button>
            </div>

            <div class="flex-1 overflow-y-auto p-3">
                <section class="overflow-hidden rounded-[20px] border border-[var(--oc-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--oc-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:wand-sparkles" class="text-[17px] text-[var(--oc-muted)]"></iconify-icon>Araçlar</div>
                    <div class="grid grid-cols-2 gap-2 p-3">
                        <button type="button" class="oc-pill-btn inline-flex min-h-[41px] items-center justify-center gap-2 rounded-full px-3 text-[13px] font-medium" data-open-preview><iconify-icon icon="lucide:eye" class="text-[16px]"></iconify-icon>Ön izleme</button>
                        <button type="button" class="oc-pill-btn inline-flex min-h-[41px] items-center justify-center gap-2 rounded-full px-3 text-[13px] font-medium" data-ai-assist><iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[16px]"></iconify-icon>AI yardım</button>
                    </div>
                </section>

                <section class="mt-2.5 overflow-hidden rounded-[20px] border border-[var(--oc-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--oc-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:layout-list" class="text-[17px] text-[var(--oc-muted)]"></iconify-icon>Gönderi bilgileri</div>
                    <div class="space-y-3 p-3">
                        <div>
                            <label for="category_id" class="mb-1.5 block text-[12px] font-medium text-[var(--oc-muted)]">Topluluk / kategori</label>
                            <select id="category_id" name="category_id" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]">
                                <option value="">Kategori seç</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected($initialCategoryId === (int) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="new_tags" class="mb-1.5 block text-[12px] font-medium text-[var(--oc-muted)]">Yeni etiketler</label>
                            <input id="new_tags" name="new_tags" type="text" value="{{ old('new_tags') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="laravel, tasarım, teknoloji">
                        </div>
                        @if(isset($tags) && collect($tags)->isNotEmpty())
                            <div>
                                <div class="mb-1.5 text-[12px] font-medium text-[var(--oc-muted)]">Mevcut etiketler</div>
                                <div class="flex max-h-36 flex-wrap gap-1.5 overflow-y-auto pr-1">
                                    @foreach($tags as $tag)
                                        <label class="relative inline-flex cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer sr-only" @checked(collect(old('tags', []))->contains($tag->id))>
                                            <span class="inline-flex min-h-[32px] items-center rounded-full border border-[var(--oc-border)] bg-[var(--oc-soft)] px-2.5 text-[12px] text-[var(--oc-muted)] peer-checked:border-blue-400/40 peer-checked:bg-blue-500/10 peer-checked:text-blue-600">#{{ $tag->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="mt-2.5 overflow-hidden rounded-[20px] border border-[var(--oc-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--oc-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:calendar-clock" class="text-[17px] text-[var(--oc-muted)]"></iconify-icon>Yayınlama</div>
                    <div class="space-y-3 p-3">
                        <div>
                            <label for="published_at" class="mb-1.5 block text-[12px] font-medium text-[var(--oc-muted)]">Yayın tarihi</label>
                            <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]">
                        </div>
                        <div class="overflow-hidden rounded-[16px] border border-[var(--oc-border)]">
                            <div class="flex min-h-[58px] items-center justify-between gap-3 px-3"><div><div class="text-[13px] font-medium">Yorumları kapat</div><div class="mt-0.5 text-[11px] text-[var(--oc-muted)]">Yeni yorum alınmaz.</div></div><x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" /></div>
                            <div class="flex min-h-[58px] items-center justify-between gap-3 border-t border-[var(--oc-border)] px-3"><div><div class="text-[13px] font-medium">Hassas içerik</div><div class="mt-0.5 text-[11px] text-[var(--oc-muted)]">İçerik uyarısıyla gösterilir.</div></div><x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" /></div>
                            <div class="flex min-h-[58px] items-center justify-between gap-3 border-t border-[var(--oc-border)] px-3"><div><div class="text-[13px] font-medium">Gönderiyi sabitle</div><div class="mt-0.5 text-[11px] text-[var(--oc-muted)]">Uygun alanlarda üstte gösterilir.</div></div><x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" /></div>
                        </div>
                    </div>
                </section>

                <section class="mt-2.5 overflow-hidden rounded-[20px] border border-[var(--oc-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--oc-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:search" class="text-[17px] text-[var(--oc-muted)]"></iconify-icon>SEO</div>
                    <div class="space-y-3 p-3">
                        <div><label for="meta_title" class="mb-1.5 block text-[12px] font-medium text-[var(--oc-muted)]">SEO başlığı</label><input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Arama sonucunda görünecek başlık"></div>
                        <div><label for="slug" class="mb-1.5 block text-[12px] font-medium text-[var(--oc-muted)]">Özel bağlantı</label><input id="slug" name="slug" type="text" value="{{ old('slug') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="ornek-gonderi"></div>
                        <div><label for="meta_keywords" class="mb-1.5 block text-[12px] font-medium text-[var(--oc-muted)]">Anahtar kelimeler</label><input id="meta_keywords" name="meta_keywords" type="text" value="{{ old('meta_keywords') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="teknoloji, yazılım, gündem"></div>
                        <p class="text-[11px] leading-4 text-[var(--oc-muted)]">Meta içerik ana editörde tek alan olarak bulunur; burada ikinci kez gösterilmez.</p>
                    </div>
                </section>

                <section class="mt-2.5 overflow-hidden rounded-[20px] border border-[var(--oc-border)]">
                    <div class="flex min-h-[44px] items-center gap-2.5 border-b border-[var(--oc-border)] px-3.5 text-[13px] font-semibold"><iconify-icon icon="lucide:copyright" class="text-[17px] text-[var(--oc-muted)]"></iconify-icon>Görsel hakları</div>
                    <div class="space-y-2.5 p-3">
                        <input id="image_creator_name" name="image_creator_name" type="text" value="{{ old('image_creator_name') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Görsel üreticisi / fotoğrafçı">
                        <input id="image_credit_text" name="image_credit_text" type="text" value="{{ old('image_credit_text') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Görsel kredisi">
                        <input id="image_copyright_notice" name="image_copyright_notice" type="text" value="{{ old('image_copyright_notice') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Telif bildirimi">
                        <input id="image_license_url" name="image_license_url" type="url" value="{{ old('image_license_url') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Lisans bağlantısı">
                        <input id="image_acquire_url" name="image_acquire_url" type="url" value="{{ old('image_acquire_url') }}" class="oc-field min-h-[44px] rounded-[15px] px-3 text-[14px]" placeholder="Kaynak / satın alma bağlantısı">
                    </div>
                </section>
            </div>

            <div class="shrink-0 border-t border-[var(--oc-border)] p-3">
                <button type="submit" class="oc-pill-btn inline-flex min-h-[42px] w-full items-center justify-center gap-2 rounded-full px-4 text-[14px] font-medium" data-submit-intent="draft"><iconify-icon icon="lucide:save" class="text-[16px]"></iconify-icon>Taslağa kaydet</button>
            </div>
        </aside>
    </form>

    <div class="oc-preview fixed inset-0 z-[140] overflow-y-auto bg-slate-950/45 p-4" data-preview-modal aria-hidden="true">
        <div class="oc-glass-strong mx-auto my-[4vh] w-[min(760px,100%)] overflow-hidden rounded-[28px]">
            <div class="flex min-h-[66px] items-center justify-between gap-3 border-b border-[var(--oc-border)] px-4">
                <div><div class="text-[15px] font-semibold">Gönderi ön izlemesi</div><div class="mt-0.5 text-[12px] text-[var(--oc-muted)]">Yayınlamadan önce son görünüm</div></div>
                <button type="button" class="oc-icon-btn inline-flex h-10 w-10 items-center justify-center rounded-full" data-close-preview aria-label="Kapat"><iconify-icon icon="lucide:x" class="text-[18px]"></iconify-icon></button>
            </div>
            <div class="max-h-[78vh] overflow-y-auto px-6 py-6 max-sm:px-4"><div data-preview-content></div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
                const contentField = document.getElementById('content');
                const jsonField = document.getElementById('content_json');
                const titleField = document.getElementById('title');
                const metaField = document.getElementById('meta_description');
                const excerptField = document.getElementById('excerpt');
                const isPublished = document.getElementById('is_published');
                const editorStatus = page.querySelector('[data-editor-status]');
                const editorDot = page.querySelector('[data-editor-dot]');
                const wordEl = page.querySelector('[data-word-count]');
                const readEl = page.querySelector('[data-reading-time]');
                const infoToggle = page.querySelector('[data-info-toggle]');
                const info = page.querySelector('[data-info-popover]');
                const drawer = page.querySelector('[data-settings-panel]');
                const drawerBackdrop = page.querySelector('[data-settings-backdrop]');
                const drawerOpen = page.querySelector('[data-open-settings]');
                const preview = page.querySelector('[data-preview-modal]');
                const previewContent = page.querySelector('[data-preview-content]');
                const coverInput = page.querySelector('[data-cover-input]');
                const coverDrop = page.querySelector('[data-cover-drop]');
                const coverPreview = page.querySelector('[data-cover-preview]');
                const coverImg = page.querySelector('[data-cover-preview-img]');
                const aiButton = page.querySelector('[data-ai-assist]');
                const aiIcon = aiButton?.querySelector('[data-ai-assist-icon]');
                const metaCount = page.querySelector('[data-meta-description-count]');

                const toast = (message, error = false) => {
                    document.querySelectorAll('[data-oc-toast]').forEach((el) => el.remove());
                    const el = document.createElement('div');
                    el.dataset.ocToast = '1';
                    el.className = 'oc-glass-strong fixed bottom-5 left-1/2 z-[180] w-[min(430px,calc(100vw-28px))] -translate-x-1/2 rounded-[18px] px-4 py-3 text-[13px]';
                    el.textContent = message;
                    if (error) el.style.color = '#ef4444';
                    document.body.appendChild(el);
                    setTimeout(() => el.remove(), error ? 5200 : 3200);
                };

                const autoGrow = () => {
                    if (!titleField) return;
                    titleField.style.height = 'auto';
                    titleField.style.height = `${Math.max(44, titleField.scrollHeight)}px`;
                };
                titleField?.addEventListener('input', autoGrow);
                autoGrow();

                const syncMeta = () => {
                    if (!metaField) return;
                    if (excerptField) excerptField.value = metaField.value;
                    if (metaCount) metaCount.textContent = `${metaField.value.length}/160`;
                };
                metaField?.addEventListener('input', syncMeta);
                syncMeta();

                const escapeHtml = (value) => String(value ?? '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;').replaceAll("'", '&#039;');

                const plainBlocksToHtml = (blocks = []) => blocks.map((block) => {
                    const data = block?.data || {};
                    if (block?.type === 'header') return `<h2>${data.text || ''}</h2>`;
                    if (block?.type === 'quote') return `<blockquote>${data.text || ''}</blockquote>`;
                    if (block?.type === 'list' && Array.isArray(data.items)) return `<ul>${data.items.map((x) => `<li>${typeof x === 'string' ? x : (x?.content || x?.text || '')}</li>`).join('')}</ul>`;
                    return data.text ? `<p>${data.text}</p>` : '';
                }).join('');

                const syncFromEditor = async () => {
                    if (!wrapper?.__editorInstance?.save) return null;
                    const output = await wrapper.__editorInstance.save();
                    if (jsonField) jsonField.value = JSON.stringify(output);
                    if (contentField) {
                        contentField.value = window.filamentEditorBlocksToHtml
                            ? window.filamentEditorBlocksToHtml(output.blocks || [])
                            : plainBlocksToHtml(output.blocks || []);
                    }
                    return output;
                };

                const markReady = (mode = 'EditorJS hazır') => {
                    holder?.classList.remove('opacity-0');
                    contentField?.classList.add('hidden');
                    if (editorStatus) editorStatus.textContent = mode;
                    editorDot?.classList.remove('opacity-50');
                    editorDot?.classList.add('text-emerald-500');
                };

                const keepFallback = (message = 'Temel yazı alanı') => {
                    holder?.classList.add('opacity-0');
                    contentField?.classList.remove('hidden');
                    if (editorStatus) editorStatus.textContent = message;
                    editorDot?.classList.add('text-amber-500');
                };

                const waitReady = async (editor) => {
                    if (!editor?.isReady?.then) return;
                    await Promise.race([
                        editor.isReady,
                        new Promise((_, reject) => setTimeout(() => reject(new Error('EditorJS timeout')), 7000)),
                    ]);
                };

                const initMinimalEditor = async () => {
                    if (!window.EditorJS || !holder) throw new Error('EditorJS çekirdeği yüklenemedi');
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
                                    const fd = new FormData();
                                    fd.append('image', file);
                                    const response = await fetch("{{ route('blog.editorjs.image', [], false) }}", {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                                        body: fd,
                                    });
                                    return response.json();
                                },
                            },
                        },
                    };

                    let data = { blocks: [] };
                    if (jsonField?.value) {
                        try { data = JSON.parse(jsonField.value); } catch {}
                    } else if (contentField?.value.trim()) {
                        data = { blocks: [{ type: 'paragraph', data: { text: contentField.value } }] };
                    }

                    const editor = new window.EditorJS({
                        holder,
                        data,
                        placeholder: 'Gönderini yazmaya başla...',
                        tools,
                        async onChange() {
                            try {
                                await syncFromEditor();
                                scheduleStats();
                            } catch (error) {
                                console.error('EditorJS sync error', error);
                            }
                        },
                    });
                    wrapper.__editorInstance = editor;
                    await waitReady(editor);
                    return editor;
                };

                const initEditor = async () => {
                    if (!wrapper || !holder || !contentField || !jsonField) {
                        keepFallback('Yazı alanı');
                        return;
                    }
                    try {
                        if (typeof window.initFilamentEditorJsField === 'function') {
                            await window.initFilamentEditorJsField(wrapper);
                            if (wrapper.__editorInstance) {
                                await waitReady(wrapper.__editorInstance);
                                markReady('EditorJS hazır');
                                return;
                            }
                        }
                    } catch (error) {
                        console.error('Ografi EditorJS init error', error);
                        try { await wrapper.__editorInstance?.destroy?.(); } catch {}
                        wrapper.__editorInstance = null;
                    }

                    try {
                        await initMinimalEditor();
                        markReady('EditorJS hazır');
                    } catch (error) {
                        console.error('Minimal EditorJS init error', error);
                        keepFallback('Temel yazı alanı');
                        toast('EditorJS yüklenemedi; içerik alanı açık bırakıldı.', true);
                    }
                };

                const strip = (html) => {
                    const el = document.createElement('div');
                    el.innerHTML = String(html || '');
                    return el.textContent || '';
                };
                const readPlain = async () => {
                    if (wrapper?.__editorInstance?.save) {
                        try {
                            const output = await wrapper.__editorInstance.save();
                            return (output.blocks || []).map((block) => {
                                const d = block?.data || {};
                                const values = [d.text,d.caption,d.question,d.answer,d.title,d.label,d.message,d.note];
                                if (Array.isArray(d.items)) d.items.forEach((x) => values.push(typeof x === 'string' ? x : (x?.content || x?.text || '')));
                                return values.filter((x) => typeof x === 'string').map(strip).join(' ');
                            }).join(' ').trim();
                        } catch {}
                    }
                    return strip(contentField?.value || '').trim();
                };
                const updateStats = async () => {
                    const text = await readPlain();
                    const words = (text.match(/\S+/g) || []).length;
                    if (wordEl) wordEl.textContent = `${words} kelime`;
                    if (readEl) readEl.textContent = `${Math.max(1, Math.ceil(words / 200))} dk okuma`;
                };
                let statsTimer = null;
                const scheduleStats = () => { clearTimeout(statsTimer); statsTimer = setTimeout(updateStats, 260); };
                wrapper?.addEventListener('input', scheduleStats);
                contentField?.addEventListener('input', scheduleStats);
                titleField?.addEventListener('input', scheduleStats);

                const closeInfo = () => { info?.classList.remove('is-open'); info?.setAttribute('aria-hidden','true'); infoToggle?.setAttribute('aria-expanded','false'); };
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

                const openDrawer = () => { drawer?.classList.add('is-open'); drawerBackdrop?.classList.add('is-open'); drawer?.setAttribute('aria-hidden','false'); drawerOpen?.setAttribute('aria-expanded','true'); };
                const closeDrawer = () => { drawer?.classList.remove('is-open'); drawerBackdrop?.classList.remove('is-open'); drawer?.setAttribute('aria-hidden','true'); drawerOpen?.setAttribute('aria-expanded','false'); };
                drawerOpen?.addEventListener('click', () => drawer?.classList.contains('is-open') ? closeDrawer() : openDrawer());
                page.querySelector('[data-close-settings]')?.addEventListener('click', closeDrawer);
                drawerBackdrop?.addEventListener('click', closeDrawer);

                const setCover = (file) => {
                    if (!file || !coverInput || !coverImg) return;
                    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { coverInput.value=''; toast('Kapak JPG, PNG veya WebP olmalı.', true); return; }
                    if (file.size > 5 * 1024 * 1024) { coverInput.value=''; toast('Kapak görseli en fazla 5 MB olabilir.', true); return; }
                    const reader = new FileReader();
                    reader.onload = () => { coverImg.src = String(reader.result || ''); coverDrop?.classList.add('hidden'); coverPreview?.classList.remove('hidden'); };
                    reader.readAsDataURL(file);
                };
                coverInput?.addEventListener('change', () => { const file = coverInput.files?.[0]; if (file) setCover(file); });
                page.querySelector('[data-cover-change]')?.addEventListener('click', () => coverInput?.click());
                page.querySelector('[data-cover-remove]')?.addEventListener('click', () => { if (coverInput) coverInput.value=''; if (coverImg) coverImg.src=''; coverPreview?.classList.add('hidden'); coverDrop?.classList.remove('hidden'); });

                const buildPreview = async () => {
                    try { await syncFromEditor(); } catch {}
                    const title = String(titleField?.value || '').trim();
                    const meta = String(metaField?.value || '').trim();
                    const image = coverImg?.src ? `<img src="${escapeHtml(coverImg.src)}" alt="" class="mb-5 w-full rounded-[20px] object-cover">` : '';
                    return `${image}<h1 class="text-[30px] font-semibold leading-tight text-[var(--oc-text)]">${escapeHtml(title || 'Başlıksız gönderi')}</h1>${meta ? `<p class="mt-3 text-[15px] leading-6 text-[var(--oc-muted)]">${escapeHtml(meta)}</p>` : ''}<div class="mt-7 text-[16px] leading-7 text-[var(--oc-text)]">${contentField?.value || '<p>Henüz içerik yok.</p>'}</div>`;
                };
                const openPreview = async () => { if (!preview || !previewContent) return; previewContent.innerHTML = await buildPreview(); preview.classList.add('is-open'); preview.setAttribute('aria-hidden','false'); closeDrawer(); };
                const closePreview = () => { preview?.classList.remove('is-open'); preview?.setAttribute('aria-hidden','true'); };
                page.querySelector('[data-open-preview]')?.addEventListener('click', openPreview);
                page.querySelector('[data-close-preview]')?.addEventListener('click', closePreview);
                preview?.addEventListener('click', (event) => { if (event.target === preview) closePreview(); });

                let aiBusy = false;
                aiButton?.addEventListener('click', async () => {
                    if (aiBusy) return;
                    const title = String(titleField?.value || '').trim();
                    const content = await readPlain();
                    if (!title && !content) { toast('Önce başlık veya içerik yazın.', true); return; }
                    aiBusy = true; aiButton.disabled = true; aiIcon?.setAttribute('icon','lucide:loader-circle');
                    try {
                        const response = await fetch('{{ route('blog.ai-assist') }}', {
                            method:'POST', credentials:'same-origin', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || ''}, body:JSON.stringify({title,content})
                        });
                        const data = await response.json();
                        if (!response.ok || !data.ok) throw new Error(data.message || 'Yapay zeka isteği başarısız.');
                        const metaTitle = document.getElementById('meta_title');
                        const metaKeywords = document.getElementById('meta_keywords');
                        if (data.meta_title && metaTitle) metaTitle.value = data.meta_title;
                        if (data.meta_description && metaField) { metaField.value = data.meta_description.slice(0,160); syncMeta(); }
                        if (Array.isArray(data.meta_keywords) && metaKeywords) metaKeywords.value = data.meta_keywords.join(', ');
                        toast('AI önerileri işlendi.');
                    } catch (error) { toast(error?.message || 'Yapay zeka isteği başarısız.', true); }
                    finally { aiBusy=false; aiButton.disabled=false; aiIcon?.setAttribute('icon','lucide:sparkles'); }
                });

                let submitting = false;
                page.querySelectorAll('[data-submit-intent]').forEach((button) => {
                    button.addEventListener('click', async (event) => {
                        event.preventDefault();
                        if (!form || submitting) return;
                        try { await syncFromEditor(); } catch {}
                        syncMeta();
                        const title = String(titleField?.value || '').trim();
                        const content = String(contentField?.value || '').trim();
                        if (!title) { toast('Başlık boş olamaz.', true); titleField?.focus(); return; }
                        if (!content) { toast('İçerik boş olamaz.', true); return; }
                        if (isPublished) isPublished.value = button.dataset.submitIntent === 'draft' ? '0' : '1';
                        submitting = true;
                        button.disabled = true;
                        form.submit();
                    });
                });

                document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeInfo(); closeDrawer(); closePreview(); } });

                await initEditor();
                updateStats();
            };

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once:true });
            else boot();
        })();
    </script>
@endpush
