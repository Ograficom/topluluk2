@extends('layouts.app')

@section('title', __('post_create.page_title'))

@section('hide_global_header')
@endsection

@section('no_container_padding')
@endsection

@section('page_background_class', 'bg-[#f6f7fb]')
@section('hide_feed_header')
@endsection
@section('hide_mobile_bottom_nav')
@endsection

@section('content')
    @php
        $user = auth()->user();
        $initialCategoryId = (int) old('category_id');
        $selectedCategory = collect($categories)->firstWhere('id', $initialCategoryId);
        $categoryPalette = ['#10b981', '#6366f1', '#ec4899', '#f97316', '#06b6d4', '#8b5cf6', '#ef4444', '#0ea5e9'];
    @endphp

    <style>
        .create-page-fixed {
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

        body > aside,
        body > nav,
        .sidebar,
        .left-sidebar,
        [data-sidebar],
        [data-left-sidebar],
        [data-app-sidebar],
        [data-feed-sidebar],
        [data-right-sidebar],
        [data-comments-sidebar] {
            display: none !important;
        }

        .create-card {
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 20px 70px -55px rgba(15, 23, 42, .55);
        }

        .create-input {
            width: 100%;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: .78rem .92rem;
            font-size: .875rem;
            color: #0f172a;
            outline: none;
            transition: .18s ease;
        }

        .create-input:focus {
            border-color: #93c5fd;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        label[data-tag-option] {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            cursor: pointer !important;
            border: 0 !important;
            background: transparent !important;
            padding: 0 !important;
        }

        label[data-tag-option] input[type="checkbox"] {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
        }

        label[data-tag-option] span {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 38px !important;
            border-radius: 999px !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            padding: .55rem .9rem !important;
            font-size: .78rem !important;
            font-weight: 500 !important;
            line-height: 1 !important;
            color: #334155 !important;
            transition: .18s ease !important;
        }

        label[data-tag-option]:hover span {
            border-color: #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        label[data-tag-option]:has(input[type="checkbox"]:checked) span {
            border-color: #3b82f6 !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            box-shadow: inset 0 0 0 1px #3b82f6 !important;
        }

        .settings-panel {
            position: fixed !important;
            inset-inline: 0 !important;
            bottom: 0 !important;
            top: auto !important;
            width: 100% !important;
            max-height: 86vh !important;
            overflow: hidden !important;
            border-radius: 22px 22px 0 0 !important;
            background: #ffffff !important;
            transform: translateY(105%) !important;
            transition: transform .28s ease !important;
            box-shadow: 0 -24px 70px -32px rgba(15, 23, 42, .55) !important;
        }

        #settings-modal.is-open .settings-panel {
            transform: translateY(0) !important;
        }

        .create-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            flex-shrink: 0;
        }

        .create-category-pill {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            border-radius: 999px !important;
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            padding: .3rem .7rem !important;
            font-size: .72rem !important;
            font-weight: 600 !important;
            transition: .18s ease !important;
        }

        .create-category-pill:hover {
            background: #dbeafe !important;
            border-color: #93c5fd !important;
        }

        .create-cover {
            position: relative;
            display: block;
            width: 100%;
            border-radius: 18px;
            overflow: hidden;
        }

        .create-cover-drop {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            width: 100%;
            min-height: 128px;
            border-radius: 18px;
            border: 1.5px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            transition: .18s ease;
            padding: 1.1rem;
        }

        .create-cover-drop:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .create-cover-preview {
            display: none;
            position: relative;
            width: 100%;
            max-height: 320px;
            border-radius: 18px;
            overflow: hidden;
            background: #0f172a;
        }

        .create-cover-preview img {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            display: block;
        }

        .create-cover-preview__actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 6px;
        }

        .create-cover-preview__actions button {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 999px;
            background: rgba(15, 23, 42, .62);
            color: #fff;
            font-size: .72rem;
            font-weight: 600;
            padding: .4rem .7rem;
            backdrop-filter: blur(6px);
            transition: .16s ease;
        }

        .create-cover-preview__actions button:hover {
            background: rgba(15, 23, 42, .8);
        }

        .create-cover.has-image .create-cover-drop { display: none; }
        .create-cover.has-image .create-cover-preview { display: block; }

        .create-title-input {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 0;
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.28;
            color: #0b0f19;
            outline: none;
        }

        .create-title-input::placeholder {
            color: #a8b0bd;
            font-weight: 700;
        }

        @media (min-width: 640px) {
            .create-title-input {
                font-size: 2.15rem;
            }
        }

        .create-meta-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: .65rem;
        }

        .create-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .76rem;
            font-weight: 500;
            color: #94a3b8;
        }

        .create-editor-hint {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            padding: 0 .1rem;
            font-size: .78rem;
            color: #b6bcc7;
            transition: opacity .18s ease;
        }

        .create-editor-hint iconify-icon {
            font-size: 15px;
            color: #c7cdd6;
        }

        [data-editorjs-wrapper] .ce-paragraph.cdx-block,
        [data-editorjs-wrapper] .ce-paragraph[data-empty] {
            background: transparent !important;
        }

        [data-editorjs-wrapper] .codex-editor__redactor {
            padding-bottom: 120px !important;
        }

        [data-editorjs-wrapper] .ce-block:first-child .ce-paragraph[data-placeholder-active]:empty::before,
        [data-editorjs-wrapper] .ce-paragraph:empty::before {
            color: #a8b0bd !important;
        }

        @media (min-width: 768px) {
            .settings-panel {
                left: auto !important;
                right: 24px !important;
                top: 24px !important;
                bottom: auto !important;
                width: 430px !important;
                max-width: calc(100vw - 48px) !important;
                max-height: calc(100vh - 48px) !important;
                border-radius: 22px !important;
                border: 1px solid #e2e8f0 !important;
                transform: translateX(calc(100% + 32px)) !important;
                box-shadow: 0 24px 70px -35px rgba(15, 23, 42, .32) !important;
            }

            #settings-modal.is-open .settings-panel {
                transform: translateX(0) !important;
            }
        }
    </style>

    <div class="create-page-fixed text-slate-950">
        <div class="mx-auto flex min-h-screen w-full max-w-[1280px] flex-col px-3 py-3 sm:px-5 lg:px-6">
            <header class="sticky top-3 z-40 mb-4 rounded-[28px] border border-slate-200 bg-white/95 px-3 py-3 shadow-[0_18px_60px_-50px_rgba(15,23,42,.65)] backdrop-blur sm:px-5 lg:px-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('blog.index') }}"
                           class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 active:bg-slate-100"
                           aria-label="{{ __('post_create.back') }}">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>

                        <div class="create-brand">
                            <iconify-icon icon="lucide:feather" class="text-[14px]"></iconify-icon>
                        </div>

                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-slate-950">Yeni gönderi</div>

                            <details class="relative" data-category-menu>
                                <summary class="create-category-pill mt-1 max-w-full cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                    <iconify-icon icon="lucide:tag" class="text-[12px]"></iconify-icon>
                                    <span class="truncate" data-category-label>{{ $selectedCategory?->name ?: __('post_create.select_category') }}</span>
                                    <svg viewBox="0 0 20 20" fill="none" class="h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>

                                <div class="absolute left-0 top-full z-50 mt-3 w-[320px] max-w-[calc(100vw-32px)] overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_22px_70px_-35px_rgba(15,23,42,.45)]">
                                    <div class="max-h-[320px] overflow-y-auto p-2">
                                        @foreach ($categories as $index => $category)
                                            @php($categoryAvatar = $category->profile_image_url ?? $category->profile_image ?? null)
                                            @php($fallbackColor = $categoryPalette[$index % count($categoryPalette)])
                                            <button
                                                type="button"
                                                data-category-option
                                                data-value="{{ $category->id }}"
                                                data-label="{{ $category->name }}"
                                                class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-slate-800 transition hover:bg-slate-100 {{ $initialCategoryId === (int) $category->id ? 'bg-slate-100' : '' }}"
                                            >
                                                @if ($categoryAvatar)
                                                    <img src="{{ $categoryAvatar }}" alt="{{ $category->name }}" class="h-9 w-9 rounded-full object-cover">
                                                @else
                                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-xs font-medium text-white" style="background-color: {{ $fallbackColor }};">
                                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 2)) }}
                                                    </span>
                                                @endif
                                                <span class="truncate text-sm font-medium">{{ $category->name }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <div class="hidden items-center gap-1.5 text-xs text-blue-700 sm:flex">
                            <iconify-icon icon="lucide:check" class="text-[14px]"></iconify-icon>
                            <span>Taslak hazır</span>
                        </div>

                        <button type="button" data-ai-assist class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 active:bg-slate-100" aria-label="Yapay zeka yardımcısı" title="Yapay zeka yardımcısı: SEO alanlarını doldur ve öneri al">
                            <iconify-icon icon="lucide:sparkles" data-ai-assist-icon class="text-[17px]"></iconify-icon>
                        </button>

                        <button type="button" data-open-settings class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 active:bg-slate-100" aria-label="{{ __('post_create.settings') }}">
                            <iconify-icon icon="lucide:sliders-horizontal" class="text-[17px]"></iconify-icon>
                        </button>

                        <button type="submit" form="post-create-form" data-submit-intent="publish"
                                class="inline-flex h-11 items-center justify-center rounded-2xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-[0_18px_34px_-18px_rgba(37,99,235,.9)] transition hover:bg-blue-700 active:bg-blue-800 sm:px-5">
                            <iconify-icon icon="lucide:send" class="text-base" aria-hidden="true"></iconify-icon>
                            <span class="sr-only sm:not-sr-only sm:ml-2">{{ __('post_create.publish') }}</span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[760px] flex-1">
                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <div class="font-medium">{{ __('post_create.validation_errors') }}</div>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="post-create-form" method="POST" action="{{ route('blog.store') }}" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published') ? 1 : 0 }}">
                    <input type="hidden" id="category_id" name="category_id" data-category-input value="{{ $initialCategoryId ?: '' }}">

                    <section class="create-card overflow-hidden rounded-[22px]">
                        <div class="border-b border-slate-100 px-4 py-4 sm:px-7 sm:py-6">
                            <div class="create-cover" data-cover-field>
                                <label for="featured_image" class="create-cover-drop">
                                    <iconify-icon icon="lucide:image-plus" class="text-2xl"></iconify-icon>
                                    <span class="text-sm font-medium">{{ __('post_create.featured_image') }}</span>
                                    <span class="text-xs text-slate-400">{{ __('post_create.max_file_size') }}</span>
                                </label>
                                <div class="create-cover-preview" data-cover-preview>
                                    <img data-cover-preview-img alt="">
                                    <div class="create-cover-preview__actions">
                                        <button type="button" data-cover-change><iconify-icon icon="lucide:pencil" class="text-[13px]"></iconify-icon>Değiştir</button>
                                        <button type="button" data-cover-remove><iconify-icon icon="lucide:x" class="text-[13px]"></iconify-icon>Kaldır</button>
                                    </div>
                                </div>
                                <input id="featured_image" name="featured_image" type="file" accept="image/*" class="sr-only" data-cover-input>
                            </div>

                            <textarea id="title" name="title" required rows="1" data-autogrow
                                   placeholder="{{ __('post_create.title_placeholder') }}"
                                   class="create-title-input mt-5"
                                   style="min-height:0 !important;border:0 !important;border-radius:0 !important;background:transparent !important;box-shadow:none !important;outline:none !important;padding:0 !important;resize:none !important;overflow:hidden !important;font-family:inherit !important;font-size:clamp(1.7rem, 1.45rem + 1.4vw, 2.15rem) !important;line-height:1.28 !important;font-weight:700 !important;color:#0b0f19 !important;letter-spacing:-0.01em !important;">{{ old('title') }}</textarea>

                            <div class="create-meta-row">
                                <span class="create-meta-chip">
                                    <iconify-icon icon="lucide:clock" class="text-[13px]"></iconify-icon>
                                    <span data-reading-time>0 dk okuma</span>
                                </span>
                                <span class="create-meta-chip">
                                    <iconify-icon icon="lucide:type" class="text-[13px]"></iconify-icon>
                                    <span data-word-count>0 kelime</span>
                                </span>
                            </div>
                        </div>

                        <div data-editorjs-wrapper class="bg-white">
                            <div x-ref="holder" class="min-h-[52vh] px-4 py-6 text-slate-800 sm:px-7 sm:py-7"></div>
                            <div class="create-editor-hint px-4 pb-6 sm:px-7">
                                <iconify-icon icon="lucide:sparkles"></iconify-icon>
                                <span>Blok eklemek için satır başındaki <strong class="font-semibold text-slate-400">“+”</strong> simgesine dokunun; biçimlendirmek için metni seçin.</span>
                            </div>
                            <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json') }}">
                            <textarea id="content" name="content" data-editor-content data-mentionable="users" class="hidden min-h-[52vh] w-full resize-none px-4 py-6 text-slate-800 focus:outline-none sm:px-7 sm:py-7" placeholder="Gönderinizi buraya yazmaya başlayın...">{{ old('content') }}</textarea>
                        </div>
                    </section>

                    <div id="settings-modal" class="fixed inset-0 z-[90] hidden" aria-hidden="true">
                        <div class="absolute inset-0 bg-slate-950/45 opacity-0 transition-opacity duration-300" data-settings-overlay data-settings-close></div>

                        <aside class="settings-panel flex flex-col" data-settings-panel role="dialog" aria-modal="true" aria-labelledby="settings-title">
                            <div class="mx-auto mt-2 h-1.5 w-14 rounded-full bg-slate-200 md:hidden"></div>

                            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-5">
                                <div class="min-w-0">
                                    <h2 id="settings-title" class="text-[15px] font-semibold text-slate-950 sm:text-base">Ayarlar</h2>
                                    <p class="mt-0.5 text-xs text-slate-500">Yayın detaylarını düzenle.</p>
                                </div>
                                <button type="button" data-settings-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200" aria-label="{{ __('post_create.close') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto bg-slate-50 p-3 sm:p-4">
                                <div class="space-y-3">
                                    <section class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="mb-3 flex items-center gap-3">
                                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">1</span>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-950">İçerik bilgileri</div>
                                                <div class="mt-0.5 text-xs text-slate-500">Etiket ve kısa açıklama.</div>
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            <input id="new_tags" name="new_tags" type="hidden" value="{{ old('new_tags') }}">
                                            <input id="tag_search" type="text" placeholder="Etiket ekle ve Enter'a basın..." class="create-input" autocomplete="off">

                                            @if(isset($tags) && collect($tags)->isNotEmpty())
                                                <div class="max-h-32 overflow-y-auto rounded-2xl border border-slate-100 bg-slate-50 p-2.5">
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($tags as $tag)
                                                            <label data-tag-option data-tag-name="{{ \Illuminate\Support\Str::lower($tag->name) }}" class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id)) class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-200">
                                                                <span>#{{ $tag->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div id="new-tags-chips" class="flex flex-wrap gap-2"></div>
                                            <button type="button" id="add-new-tag-btn" class="hidden rounded-full bg-slate-900 px-3 py-2 text-xs font-medium text-white transition hover:bg-slate-800 active:bg-slate-950">{{ __('post_create.add_new_tag') }}</button>

                                            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Altyazı eklemek için buraya yazın..." class="create-input resize-none">{{ old('excerpt') }}</textarea>
                                        </div>
                                    </section>

                                    <section class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm">
                                        <details class="group" open>
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">2</span>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-slate-950">SEO</div>
                                                        <div class="mt-0.5 text-xs text-slate-500">Arama görünümü ayarları.</div>
                                                    </div>
                                                </div>
                                                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" xmlns="http://www.w3.org/2000/svg"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </summary>
                                            <div class="mt-3 space-y-3">
                                                <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title') }}" placeholder="Arama sonuçlarında görünecek başlık" class="create-input">
                                                <div>
                                                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="160" placeholder="Arama sonuçlarında görünecek açıklama" class="create-input resize-none">{{ old('meta_description') }}</textarea>
                                                    <div class="mt-1 text-right text-xs text-slate-400" data-meta-description-count>0/160</div>
                                                </div>
                                                <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="https://ornek.com/gonderi" class="create-input">
                                                <textarea id="meta_keywords" name="meta_keywords" rows="2" placeholder="virgülle ayırın (ör. yazılım, php, laravel)" class="create-input resize-none">{{ old('meta_keywords') }}</textarea>
                                            </div>
                                        </details>
                                    </section>

                                    <section class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm">
                                        <details class="group">
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">3</span>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-slate-950">Yayın zamanlaması</div>
                                                        <div class="mt-0.5 text-xs text-slate-500">Hemen ya da ileri bir tarihte yayınla.</div>
                                                    </div>
                                                </div>
                                                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" xmlns="http://www.w3.org/2000/svg"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </summary>
                                            <div class="mt-3 space-y-3">
                                                <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at') }}" class="create-input">
                                            </div>
                                        </details>
                                    </section>

                                    <section class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm">
                                        <details class="group">
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">4</span>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-slate-950">Lisans bilgileri</div>
                                                        <div class="mt-0.5 text-xs text-slate-500">Görsel kaynak ve telif alanları.</div>
                                                    </div>
                                                </div>
                                                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" xmlns="http://www.w3.org/2000/svg"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </summary>
                                            <div class="mt-3 space-y-3">
                                                <input id="image_license_url" name="image_license_url" type="url" value="{{ old('image_license_url') }}" placeholder="https://example.com/license" class="create-input">
                                                <input id="image_acquire_url" name="image_acquire_url" type="url" value="{{ old('image_acquire_url') }}" placeholder="https://example.com/buy" class="create-input">
                                                <input id="image_credit_text" name="image_credit_text" type="text" value="{{ old('image_credit_text') }}" placeholder="{{ __('post_create.credit_placeholder') }}" class="create-input">
                                                <input id="image_creator_name" name="image_creator_name" type="text" value="{{ old('image_creator_name') }}" placeholder="{{ __('post_create.creator_placeholder') }}" class="create-input">
                                                <input id="image_copyright_notice" name="image_copyright_notice" type="text" value="{{ old('image_copyright_notice') }}" placeholder="{{ __('post_create.copyright_placeholder') }}" class="create-input">
                                            </div>
                                        </details>
                                    </section>

                                    <section class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="mb-3 flex items-center gap-3">
                                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">5</span>
                                            <div class="text-sm font-semibold text-slate-950">Tercihler</div>
                                        </div>
                                        <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                                            <div class="flex items-center justify-between gap-4 px-3 py-3">
                                                <span class="text-sm text-slate-800">{{ __('post_create.disable_comments') }}</span>
                                                <input type="hidden" name="comments_disabled" value="0">
                                                <x-ui.switch name="comments_disabled" value="1" :checked="old('comments_disabled', 0) == 1" />
                                            </div>
                                            <div class="flex items-center justify-between gap-4 px-3 py-3">
                                                <span class="text-sm text-slate-800">{{ __('post_create.nsfw') }}</span>
                                                <input type="hidden" name="is_nsfw" value="0">
                                                <x-ui.switch name="is_nsfw" value="1" :checked="old('is_nsfw', 0) == 1" />
                                            </div>
                                            <div class="flex items-center justify-between gap-4 px-3 py-3">
                                                <span class="text-sm text-slate-800">{{ __('post_create.pin_story') }}</span>
                                                <input type="hidden" name="is_pinned" value="0">
                                                <x-ui.switch name="is_pinned" value="1" :checked="old('is_pinned', 0) == 1" />
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 bg-white p-3 sm:p-4">
                                <div class="grid grid-cols-2 gap-2.5">
                                    <button type="button" data-settings-close class="inline-flex h-11 items-center justify-center rounded-full bg-slate-100 px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-200 active:bg-slate-300">{{ __('post_create.close') }}</button>
                                    <button type="submit" form="post-create-form" data-submit-intent="publish" class="inline-flex h-11 items-center justify-center rounded-full bg-blue-600 px-4 text-sm font-semibold text-white shadow-[0_14px_28px_-18px_rgba(37,99,235,.9)] transition hover:bg-blue-700 active:bg-blue-800">{{ __('post_create.publish') }}</button>
                                </div>
                            </div>
                        </aside>
                    </div>
                </form>
            </main>

            <div id="post-preview-modal" class="fixed inset-0 z-[95] hidden">
                <div class="fixed inset-0 bg-slate-950/55 backdrop-blur-sm" data-preview-close></div>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="mx-auto mt-4 w-full max-w-3xl px-3 sm:mt-10 sm:px-4">
                        <div class="overflow-hidden rounded-[18px] bg-white shadow-2xl">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                                <div class="text-sm font-medium text-slate-950">{{ __('post_create.preview_title') }}</div>
                                <button type="button" data-preview-close class="rounded-full p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="{{ __('post_create.close') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                            <div class="max-h-[75vh] overflow-auto p-4 sm:max-h-[70vh] sm:p-5">
                                <div id="post-preview-content" class="space-y-4 text-sm text-slate-700"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('filament.assets.editorjs')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('[data-editorjs-wrapper]');
            const fallbackTextarea = document.getElementById('content');
            const form = document.getElementById('post-create-form');
            const isPublishedInput = document.getElementById('is_published');
            const draftButton = document.querySelector('[data-submit-intent="draft"]');
            const publishButtons = document.querySelectorAll('[data-submit-intent="publish"]');
            const categoryInput = document.querySelector('[data-category-input]');
            const categoryLabel = document.querySelector('[data-category-label]');
            const categoryMenu = document.querySelector('[data-category-menu]');
            const categoryOptions = Array.from(document.querySelectorAll('[data-category-option]'));
            const metaTitleField = document.getElementById('meta_title');
            const metaDescription = document.getElementById('meta_description');
            const metaDescriptionCount = document.querySelector('[data-meta-description-count]');
            const metaKeywordsField = document.getElementById('meta_keywords');
            const tagSearchInput = document.getElementById('tag_search');
            const newTagsInput = document.getElementById('new_tags');
            const newTagsChips = document.getElementById('new-tags-chips');
            const addNewTagButton = document.getElementById('add-new-tag-btn');
            const existingTagOptions = Array.from(document.querySelectorAll('[data-tag-option]'));
            const previewModal = document.getElementById('post-preview-modal');
            const previewContent = document.getElementById('post-preview-content');
            const settingsModal = document.getElementById('settings-modal');
            const settingsOverlay = settingsModal?.querySelector('[data-settings-overlay]');
            let settingsTimer = null;

            const coverField = document.querySelector('[data-cover-field]');
            const coverInput = document.querySelector('[data-cover-input]');
            const coverPreview = document.querySelector('[data-cover-preview]');
            const coverPreviewImg = document.querySelector('[data-cover-preview-img]');

            const setCoverFile = (file) => {
                if (!file || !coverPreviewImg || !coverField) return;
                const reader = new FileReader();
                reader.onload = () => {
                    coverPreviewImg.src = String(reader.result || '');
                    coverField.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            };

            coverInput?.addEventListener('change', () => {
                const file = coverInput.files?.[0];
                if (file) setCoverFile(file);
            });

            document.querySelector('[data-cover-change]')?.addEventListener('click', () => coverInput?.click());
            document.querySelector('[data-cover-remove]')?.addEventListener('click', () => {
                if (coverInput) coverInput.value = '';
                if (coverPreviewImg) coverPreviewImg.src = '';
                coverField?.classList.remove('has-image');
            });

            const readingTimeEl = document.querySelector('[data-reading-time]');
            const wordCountEl = document.querySelector('[data-word-count]');
            const blockText = (block) => {
                const data = block?.data || {};
                return [data.text, data.caption, data.question, data.answer, data.title, ...(Array.isArray(data.items) ? data.items : [])]
                    .filter((value) => typeof value === 'string')
                    .join(' ');
            };
            const updateReadingStats = async () => {
                if (!readingTimeEl && !wordCountEl) return;
                let text = '';
                if (wrapper?.__editorInstance?.save) {
                    try {
                        const data = await wrapper.__editorInstance.save();
                        text = (data?.blocks || []).map(blockText).join(' ');
                    } catch { text = ''; }
                }
                if (!text) text = fallbackTextarea?.value || '';
                const words = (text.match(/\S+/g) || []).length;
                if (wordCountEl) wordCountEl.textContent = `${words} kelime`;
                if (readingTimeEl) readingTimeEl.textContent = `${Math.max(1, Math.round(words / 200))} dk okuma`;
            };
            let readingStatsTimer = null;
            const scheduleReadingStats = () => {
                if (readingStatsTimer) clearTimeout(readingStatsTimer);
                readingStatsTimer = window.setTimeout(updateReadingStats, 600);
            };
            wrapper?.addEventListener('input', scheduleReadingStats);
            wrapper?.addEventListener('keyup', scheduleReadingStats);

            const titleField = document.getElementById('title');
            const autoGrowTitle = () => {
                if (!titleField) return;
                titleField.style.height = 'auto';
                titleField.style.height = `${titleField.scrollHeight}px`;
            };
            titleField?.addEventListener('input', () => { autoGrowTitle(); scheduleReadingStats(); });
            titleField?.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                wrapper?.querySelector('[contenteditable]')?.focus();
            });
            autoGrowTitle();
            window.setTimeout(updateReadingStats, 1200);

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
                    if (!wrapper.__editorInstance) showFallback();
                } catch {
                    showFallback();
                }
            };
            initEditor();

            const syncScrollLock = () => {
                const anyOpen = [previewModal, settingsModal].some((modal) => modal && !modal.classList.contains('hidden'));
                document.documentElement.classList.toggle('overflow-hidden', anyOpen);
                document.body.classList.toggle('overflow-hidden', anyOpen);
            };

            const openSettings = () => {
                if (!settingsModal || !settingsOverlay) return;
                if (settingsTimer) clearTimeout(settingsTimer);
                settingsModal.classList.remove('hidden');
                settingsModal.setAttribute('aria-hidden', 'false');
                syncScrollLock();
                requestAnimationFrame(() => {
                    settingsOverlay.classList.remove('opacity-0');
                    settingsModal.classList.add('is-open');
                });
            };

            const closeSettings = () => {
                if (!settingsModal || !settingsOverlay) return;
                settingsOverlay.classList.add('opacity-0');
                settingsModal.classList.remove('is-open');
                settingsModal.setAttribute('aria-hidden', 'true');
                if (settingsTimer) clearTimeout(settingsTimer);
                settingsTimer = window.setTimeout(() => {
                    settingsModal.classList.add('hidden');
                    syncScrollLock();
                }, 280);
            };

            document.querySelectorAll('[data-open-settings]').forEach((button) => button.addEventListener('click', openSettings));
            settingsModal?.querySelectorAll('[data-settings-close]').forEach((el) => el.addEventListener('click', closeSettings));

            const syncMetaDescriptionCount = () => {
                if (!metaDescription || !metaDescriptionCount) return;
                metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
            };
            syncMetaDescriptionCount();
            metaDescription?.addEventListener('input', syncMetaDescriptionCount);

            const syncCategorySelection = () => {
                if (!categoryInput) return;
                const activeValue = String(categoryInput.value || '');
                const activeOption = categoryOptions.find((option) => String(option.getAttribute('data-value') || '') === activeValue);
                const activeLabel = activeOption?.getAttribute('data-label') || @js(__('post_create.select_category'));
                if (categoryLabel) categoryLabel.textContent = activeLabel;
                categoryOptions.forEach((option) => {
                    option.classList.toggle('bg-slate-100', String(option.getAttribute('data-value') || '') === activeValue);
                });
            };
            syncCategorySelection();
            categoryOptions.forEach((option) => {
                option.addEventListener('click', () => {
                    if (!categoryInput) return;
                    categoryInput.value = option.getAttribute('data-value') || '';
                    syncCategorySelection();
                    categoryMenu?.removeAttribute('open');
                });
            });

            const existingTagNames = existingTagOptions.map((el) => String(el.getAttribute('data-tag-name') || '').trim()).filter(Boolean);
            const newTagSet = new Set(String(newTagsInput?.value || '').split(',').map((item) => item.trim().replace(/^#/, '')).filter(Boolean));

            const syncNewTagsInput = () => {
                if (!newTagsInput) return;
                newTagsInput.value = Array.from(newTagSet).join(', ');
            };

            const renderNewTagChips = () => {
                if (!newTagsChips) return;
                const tags = Array.from(newTagSet);
                newTagsChips.innerHTML = tags.map((tag) => `<span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">#${tag}<button type="button" data-remove-new-tag="${tag}" class="rounded-full px-1 text-blue-700 hover:bg-blue-100">x</button></span>`).join('');
            };

            const filterExistingTags = (term) => {
                const query = term.trim().toLowerCase();
                existingTagOptions.forEach((option) => {
                    const name = String(option.getAttribute('data-tag-name') || '');
                    option.classList.toggle('hidden', Boolean(query && !name.includes(query)));
                });
            };

            const updateAddTagButton = (term) => {
                if (!addNewTagButton) return;
                const normalized = term.trim().replace(/^#/, '').toLowerCase();
                if (!normalized) {
                    addNewTagButton.classList.add('hidden');
                    addNewTagButton.textContent = @js(__('post_create.add_new_tag'));
                    return;
                }
                const existsInCurrent = Array.from(newTagSet).some((tag) => tag.toLowerCase() === normalized);
                const existsInDb = existingTagNames.includes(normalized);
                if (existsInCurrent || existsInDb) {
                    addNewTagButton.classList.add('hidden');
                    return;
                }
                addNewTagButton.classList.remove('hidden');
                addNewTagButton.textContent = @js(__('post_create.add_new_tag_with_value')).replace(':tag', term.trim().replace(/^#/, ''));
            };

            const normalizeTagText = (value) => String(value || '').trim().replace(/^#/, '').replace(/[.,;:!?]+$/g, '').trim();
            const tryAddNewTag = (rawValue) => {
                const value = normalizeTagText(rawValue);
                if (!value) return false;
                const normalized = value.toLowerCase();
                if (existingTagNames.includes(normalized)) return false;
                if (Array.from(newTagSet).some((tag) => tag.toLowerCase() === normalized)) return false;
                newTagSet.add(value);
                syncNewTagsInput();
                renderNewTagChips();
                return true;
            };

            const commitTagInput = (raw, consumeAll = false) => {
                const text = String(raw || '');
                const hasDelimiter = /[,.;:!?]/.test(text);
                if (!hasDelimiter && !consumeAll) return { added: false, remainder: text };
                const parts = text.split(/[,.;:!?]+/);
                const trailingDelimiter = /[,.;:!?]\s*$/.test(text);
                const candidates = (consumeAll || trailingDelimiter) ? parts : parts.slice(0, -1);
                const remainder = (consumeAll || trailingDelimiter) ? '' : (parts.at(-1) || '');
                let added = false;
                candidates.forEach((part) => { if (tryAddNewTag(part)) added = true; });
                return { added, remainder };
            };

            renderNewTagChips();
            syncNewTagsInput();

            tagSearchInput?.addEventListener('input', () => {
                const term = tagSearchInput.value || '';
                const committed = commitTagInput(term, false);
                if (committed.added || committed.remainder !== term) tagSearchInput.value = committed.remainder;
                filterExistingTags(tagSearchInput.value || '');
                updateAddTagButton(tagSearchInput.value || '');
            });

            tagSearchInput?.addEventListener('keydown', (e) => {
                const punctuationKeys = [',', '.', ';', ':', '!', '?'];
                if (e.key !== 'Enter' && !punctuationKeys.includes(e.key)) return;
                e.preventDefault();
                const committed = commitTagInput(tagSearchInput.value, true);
                if (committed.added) tagSearchInput.value = '';
                filterExistingTags(tagSearchInput.value || '');
                updateAddTagButton(tagSearchInput.value || '');
            });

            tagSearchInput?.addEventListener('blur', () => {
                const committed = commitTagInput(tagSearchInput.value, true);
                if (committed.added) tagSearchInput.value = '';
                filterExistingTags('');
                updateAddTagButton('');
            });

            addNewTagButton?.addEventListener('click', () => {
                if (!tagSearchInput) return;
                if (!tryAddNewTag(tagSearchInput.value)) return;
                tagSearchInput.value = '';
                filterExistingTags('');
                updateAddTagButton('');
            });

            newTagsChips?.addEventListener('click', (e) => {
                const button = e.target.closest('[data-remove-new-tag]');
                if (!button) return;
                const tag = button.getAttribute('data-remove-new-tag');
                if (!tag) return;
                newTagSet.delete(tag);
                syncNewTagsInput();
                renderNewTagChips();
            });

            draftButton?.addEventListener('click', () => {
                if (isPublishedInput) isPublishedInput.value = '0';
            });

            const isEditorContentPresent = async () => {
                if (wrapper?.__editorInstance?.save) {
                    try {
                        const data = await wrapper.__editorInstance.save();
                        const blocks = Array.isArray(data?.blocks) ? data.blocks : [];
                        if (blocks.length > 0) return true;
                    } catch {}
                }
                return Boolean((fallbackTextarea?.value || '').trim());
            };

            publishButtons?.forEach((button) => {
                button.addEventListener('click', async (e) => {
                    if (!form || !isPublishedInput) return;
                    e.preventDefault();
                    const titleValue = (document.getElementById('title')?.value || '').trim();
                    const hasContent = await isEditorContentPresent();
                    if (!titleValue || !hasContent) {
                        alert(@js(__('post_create.required_fields_alert')));
                        return;
                    }
                    isPublishedInput.value = '1';
                    form.submit();
                });
            });

            const closePreview = () => {
                previewModal?.classList.add('hidden');
                syncScrollLock();
            };
            previewModal?.querySelectorAll('[data-preview-close]').forEach((el) => el.addEventListener('click', closePreview));

            const escapeHtml = (value) => String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            const renderBlocks = (blocks = []) => blocks.map((block) => {
                if (!block || !block.type) return '';
                if (block.type === 'header') {
                    const level = Math.min(Math.max(parseInt(block.data?.level || 2, 10), 2), 4);
                    return `<h${level} class="text-base font-semibold text-slate-900">${escapeHtml(block.data?.text || '')}</h${level}>`;
                }
                if (block.type === 'paragraph') return `<p class="whitespace-pre-wrap">${escapeHtml(block.data?.text || '')}</p>`;
                if (block.type === 'quote') return `<blockquote class="border-l-4 border-slate-200 pl-4 italic">${escapeHtml(block.data?.text || '')}</blockquote>`;
                if (block.type === 'list') {
                    const style = block.data?.style === 'ordered' ? 'list-decimal' : 'list-disc';
                    const items = Array.isArray(block.data?.items) ? block.data.items : [];
                    return `<ul class="${style} pl-5 space-y-1">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`;
                }
                return `<pre class="overflow-auto rounded-lg bg-slate-950/5 p-3 text-xs text-slate-700">${escapeHtml(JSON.stringify(block, null, 2))}</pre>`;
            }).join('');

            const buildPreview = async () => {
                const title = document.getElementById('title')?.value || '';
                const excerpt = document.getElementById('excerpt')?.value || '';
                const newTags = document.getElementById('new_tags')?.value || '';
                let contentHtml = '';
                if (wrapper?.__editorInstance?.save) {
                    try {
                        const data = await wrapper.__editorInstance.save();
                        contentHtml = renderBlocks(data?.blocks || []);
                    } catch { contentHtml = ''; }
                }
                if (!contentHtml) {
                    const contentFallback = fallbackTextarea?.value || '';
                    contentHtml = contentFallback ? `<p class="whitespace-pre-wrap">${escapeHtml(contentFallback)}</p>` : `<p class="text-slate-500">EditorJS ön izleme için henüz hazır değil.</p>`;
                }
                return `
                    <div class="space-y-1"><div class="text-xs text-slate-500">Başlık</div><div class="text-base font-semibold text-slate-900">${escapeHtml(title || '-')}</div></div>
                    <div class="space-y-1"><div class="text-xs text-slate-500">Altyazı</div><div class="whitespace-pre-wrap">${escapeHtml(excerpt || '-')}</div></div>
                    <div class="space-y-1"><div class="text-xs text-slate-500">Yeni etiketler</div><div>${escapeHtml(newTags.trim() || '-')}</div></div>
                    <div class="space-y-2"><div class="text-xs text-slate-500">İçerik</div><div class="space-y-3">${contentHtml}</div></div>
                `;
            };

            document.querySelectorAll('[data-open-preview]').forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!previewModal || !previewContent) return;
                    previewModal.classList.remove('hidden');
                    syncScrollLock();
                    previewContent.innerHTML = '<p class="text-slate-500">Hazırlanıyor...</p>';
                    previewContent.innerHTML = await buildPreview();
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closePreview();
                    closeSettings();
                }
            });

            document.addEventListener('click', (e) => {
                const target = e.target;
                if (!(target instanceof Element)) return;
                if (categoryMenu && !categoryMenu.contains(target)) categoryMenu.removeAttribute('open');
            });

            const aiAssistButton = document.querySelector('[data-ai-assist]');
            const aiAssistIcon = document.querySelector('[data-ai-assist-icon]');
            let aiAssistBusy = false;

            const readEditorPlainText = () => (fallbackTextarea ? fallbackTextarea.value : '');

            const showAiToast = (message, isError = false) => {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-24 left-1/2 z-[9999] w-[min(92vw,420px)] -translate-x-1/2 rounded-2xl border px-4 py-3 text-sm shadow-lg sm:bottom-6 ${isError ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-blue-200 bg-blue-50 text-blue-900'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), isError ? 6000 : 8000);
            };

            aiAssistButton?.addEventListener('click', async () => {
                if (aiAssistBusy) return;

                const titleValue = (document.getElementById('title')?.value || '').trim();
                const contentValue = readEditorPlainText().trim();

                if (titleValue === '' && contentValue === '') {
                    showAiToast('Önce bir başlık veya içerik yazın, yapay zeka ondan sonra yardımcı olabilir.', true);
                    return;
                }

                aiAssistBusy = true;
                aiAssistIcon?.classList.add('animate-pulse');
                aiAssistButton.disabled = true;

                try {
                    const response = await fetch('{{ route('blog.ai-assist') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ title: titleValue, content: contentValue }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        showAiToast(data.message || 'Yapay zeka şu anda yardımcı olamadı.', true);
                        return;
                    }

                    if (data.meta_title && metaTitleField) metaTitleField.value = data.meta_title;
                    if (data.meta_description && metaDescription) {
                        metaDescription.value = data.meta_description;
                        metaDescription.dispatchEvent(new Event('input'));
                    }
                    if (Array.isArray(data.meta_keywords) && data.meta_keywords.length && metaKeywordsField) {
                        metaKeywordsField.value = data.meta_keywords.join(', ');
                    }
                    if (data.excerpt) {
                        const excerptField = document.getElementById('excerpt');
                        if (excerptField && excerptField.value.trim() === '') {
                            excerptField.value = data.excerpt;
                        }
                    }

                    openSettings();

                    if (data.suggestions) {
                        showAiToast(data.suggestions);
                    } else {
                        showAiToast('SEO alanları yapay zeka tarafından dolduruldu.');
                    }
                } catch (error) {
                    showAiToast('Yapay zeka isteği başarısız oldu. Bağlantınızı kontrol edip tekrar deneyin.', true);
                } finally {
                    aiAssistBusy = false;
                    aiAssistIcon?.classList.remove('animate-pulse');
                    aiAssistButton.disabled = false;
                }
            });
        });
    </script>
@endpush
