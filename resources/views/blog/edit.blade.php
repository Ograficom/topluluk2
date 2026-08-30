@extends('layouts.app')

@section('title', 'Yazi Duzenle')

@section('hide_global_header')
@endsection

@section('no_container_padding')
@endsection

@section('page_background_class', 'bg-[#f7f8fa]')
@section('hide_feed_header')
@endsection
@section('hide_mobile_bottom_nav')
@endsection

@section('content')
    @php
        $user = auth()->user();
        $initialCategoryId = (int) old('category_id', $post->category_id);
        $selectedCategory = collect($categories)->firstWhere('id', $initialCategoryId);
        $categoryPalette = ['#ef4444', '#ec4899', '#f97316', '#84cc16', '#06b6d4', '#8b5cf6', '#e11d48', '#0ea5e9'];
    @endphp

    <div class="relative min-h-screen w-full">
        <header class="border-b border-slate-200 bg-[#f7f8fa]">
            <div class="mx-auto w-full max-w-[45rem] px-3 py-3 sm:px-6">
                <div class="rounded-[28px] border border-slate-200 bg-white px-4 py-4 shadow-sm sm:px-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3 text-gray-900">
                            <a href="{{ route('blog.index') }}"
                               class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-gray-700 shadow-sm transition hover:bg-slate-100"
                               aria-label="Geri">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>

                            @if ($user && method_exists($user, 'profile_photo_url'))
                                <img src="{{ $user->profile_photo_url }}" class="h-10 w-10 rounded-full object-cover" alt="{{ $user->name }}">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-700">
                                    {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'U' }}
                                </div>
                            @endif

                            <div class="min-w-0 leading-tight">
                                <div class="text-sm font-semibold truncate">{{ $user->name ?? 'Kullanici' }}</div>
                                <details class="relative max-w-full" data-category-menu>
                                    <summary class="inline-flex max-w-full cursor-pointer list-none items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-gray-700 [&::-webkit-details-marker]:hidden">
                                        <span class="truncate" data-category-label>{{ $selectedCategory?->name ?: 'Kategori sec' }}</span>
                                        <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </summary>

                                    <div class="absolute left-0 top-full z-40 mt-3 w-[340px] max-w-[calc(100vw-32px)] overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_36px_-18px_rgba(15,23,42,0.32)]">
                                        <div class="max-h-[320px] overflow-y-auto p-2">
                                            @foreach ($categories as $index => $category)
                                                @php($categoryAvatar = $category->profile_image_url ?? $category->profile_image ?? null)
                                                @php($fallbackColor = $categoryPalette[$index % count($categoryPalette)])
                                                <button
                                                    type="button"
                                                    data-category-option
                                                    data-value="{{ $category->id }}"
                                                    data-label="{{ $category->name }}"
                                                    class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-slate-800 transition hover:bg-slate-100 {{ $initialCategoryId === (int) $category->id ? 'bg-slate-50' : '' }}"
                                                >
                                                    @if ($categoryAvatar)
                                                        <img src="{{ $categoryAvatar }}" alt="{{ $category->name }}" class="h-10 w-10 rounded-full object-cover">
                                                    @else
                                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold text-white" style="background-color: {{ $fallbackColor }};">
                                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 2)) }}
                                                        </span>
                                                    @endif
                                                    <span class="truncate text-[15px] font-medium">{{ $category->name }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </details>
                                <input type="hidden" id="category_id" name="category_id" form="post-edit-form" data-category-input value="{{ $initialCategoryId ?: '' }}">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 self-end sm:self-auto">
                            <button
                                type="button"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-100 sm:hidden"
                                data-open-settings
                                aria-label="Ayarlar"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true">
                                    <path fill="#000000" d="m12 .845l9.66 5.578v11.154L12 23.155l-9.66-5.578V6.423L12 .845Zm0 2.31L4.34 7.577v8.846L12 20.845l7.66-4.422V7.577L12 3.155ZM12 9a3 3 0 1 0 0 6a3 3 0 0 0 0-6Zm-5 3a5 5 0 1 1 10 0a5 5 0 0 1-10 0Z"/>
                                </svg>
                            </button>

                            <details class="relative hidden sm:block" data-create-actions-menu>
                                <summary class="inline-flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-100 [&::-webkit-details-marker]:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true">
                                        <path fill="#000000" d="m12 .845l9.66 5.578v11.154L12 23.155l-9.66-5.578V6.423L12 .845Zm0 2.31L4.34 7.577v8.846L12 20.845l7.66-4.422V7.577L12 3.155ZM12 9a3 3 0 1 0 0 6a3 3 0 0 0 0-6Zm-5 3a5 5 0 1 1 10 0a5 5 0 0 1-10 0Z"/>
                                    </svg>
                                </summary>
                                <div class="absolute right-0 mt-2 w-52 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-lg">
                                    <button type="submit" form="post-edit-form" data-submit-intent="draft"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-100">
                                        <iconify-icon icon="lucide:file-clock" class="text-base text-slate-400"></iconify-icon>
                                        <span>Taslak kaydet</span>
                                    </button>
                                    <button type="button" data-open-preview
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-100">
                                        <iconify-icon icon="lucide:eye" class="text-base text-slate-400"></iconify-icon>
                                        <span>On izleme</span>
                                    </button>
                                    <button type="button" data-open-settings
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-100">
                                        <iconify-icon icon="lucide:settings-2" class="text-base text-slate-400"></iconify-icon>
                                        <span>Ayarlar</span>
                                    </button>
                                </div>
                            </details>

                            <form method="POST" action="{{ route('blog.post.destroy', $post) }}" onsubmit="return confirm('Bu gonderiyi silmek istediginize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-white text-rose-600 shadow-sm transition hover:bg-rose-50"
                                        aria-label="Sil">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-[18px] w-[18px]" aria-hidden="true"><path fill="currentColor" d="M13.5 6.5V7h5v-.5a2.5 2.5 0 0 0-5 0Zm-2 .5v-.5a4.5 4.5 0 1 1 9 0V7H28a1 1 0 1 1 0 2h-1.508L24.6 25.568A5 5 0 0 1 19.63 30h-7.26a5 5 0 0 1-4.97-4.432L5.508 9H4a1 1 0 0 1 0-2h7.5ZM9.388 25.34a3 3 0 0 0 2.98 2.66h7.263a3 3 0 0 0 2.98-2.66L24.48 9H7.521l1.867 16.34ZM13 12.5a1 1 0 0 1 1 1v10a1 1 0 1 1-2 0v-10a1 1 0 0 1 1-1Zm7 1a1 1 0 1 0-2 0v10a1 1 0 1 0 2 0v-10Z"/></svg>
                                </button>
                            </form>

                            <button type="submit" form="post-edit-form" data-submit-intent="publish"
                                    class="inline-flex h-11 items-center justify-center rounded-2xl bg-[#00bfa5] px-5 text-sm font-semibold text-white shadow-[0_18px_34px_-18px_rgba(0,191,165,0.85)] transition hover:bg-[#00a892] focus:outline-none">
                                Guncelle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto w-full max-w-[45rem] px-3 sm:px-6">
            <div class="space-y-4 py-4 sm:space-y-6">
                @if ($errors->any())
                    <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <div class="font-semibold">Duzeltmeniz gereken alanlar var:</div>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="post-edit-form" method="POST" action="{{ route('blog.post.update', $post) }}" class="w-full space-y-4 sm:space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @php($selectedTags = collect(old('tags', $post->tags->pluck('id')->all())))
            @foreach ($selectedTags as $tagId)
                <input type="hidden" name="tags[]" value="{{ $tagId }}">
            @endforeach
            <input type="hidden" name="is_published" id="is_published" value="{{ old('is_published', $post->is_published ? 1 : 0) }}">

                    <div class="w-full space-y-4">
                        <div class="rounded-[28px] border border-slate-200 bg-white px-4 py-4 shadow-sm sm:px-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <input id="title" name="title" type="text" required value="{{ old('title', $post->title) }}"
                                           placeholder="Baslik ekle"
                                           class="w-full rounded-lg bg-transparent px-0 py-2 text-2xl font-bold leading-tight text-slate-900 placeholder:text-slate-400 focus:outline-none sm:text-3xl">
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm">
                            <div data-editorjs-wrapper>
                                <div x-ref="holder" class="min-h-[50vh] px-4 py-4 sm:min-h-[70vh] sm:px-6"></div>
                                <input type="hidden" name="content_json" id="content_json" data-editor-json value="{{ old('content_json', $post->content_json ? json_encode($post->content_json) : '') }}">
                                <textarea id="content" name="content" data-editor-content data-mentionable="users" class="hidden w-full px-4 py-4 text-slate-900 focus:outline-none sm:px-6">{{ old('content', $post->content) }}</textarea>
                            </div>
                        </div>
                    </div>

            <div id="settings-modal" class="fixed inset-0 z-[80] hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-slate-900/40 opacity-0 transition duration-300" data-settings-overlay data-settings-close></div>
                <div class="fixed inset-x-0 bottom-0 z-[81] h-[82vh] w-full translate-y-full rounded-t-3xl bg-white shadow-[0_-24px_60px_-24px_rgba(15,23,42,0.45)] transition duration-300 ease-out sm:inset-y-0 sm:right-0 sm:bottom-auto sm:left-auto sm:h-full sm:max-w-[420px] sm:rounded-t-none" data-settings-drawer>
                    <div class="flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-4">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Ayarlar</div>
                                <div class="mt-1 text-xs text-slate-500">Yayin ayarlarini cekmeceden yonet.</div>
                            </div>
                            <button type="button" data-settings-close class="rounded-2xl border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Kapat">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-auto bg-[#f7f8fa]">
                            <div class="space-y-6 p-4 sm:p-5">
                                <div class="space-y-2">
                                    <label for="tag_search" class="block text-sm font-semibold text-slate-900">Etiketler</label>
                                    <input
                                        id="new_tags"
                                        name="new_tags"
                                        type="hidden"
                                        value="{{ old('new_tags') }}"
                                    >
                                    <input
                                        id="tag_search"
                                        type="text"
                                        placeholder="Etiket yazin ve Enter'a basin"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none"
                                        autocomplete="off"
                                    >
                                    <div id="new-tags-chips" class="flex flex-wrap gap-2"></div>
                                    <button type="button" id="add-new-tag-btn" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                        Yeni etiket ekle
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <label for="excerpt" class="block text-sm font-semibold text-slate-900">Altyazi</label>
                                    <textarea id="excerpt" name="excerpt" rows="2"
                                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">{{ old('excerpt', $post->excerpt) }}</textarea>
                                </div>

                                <div class="space-y-2">
                                    <label for="meta_title" class="block text-sm font-semibold text-slate-900">SEO basligi</label>
                                    <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $post->meta_title) }}"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                </div>

                                <div class="space-y-2">
                                    <label for="meta_description" class="block text-sm font-semibold text-slate-900">SEO aciklamasi</label>
                                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="160"
                                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">{{ old('meta_description', $post->meta_description) }}</textarea>
                                    <div class="text-xs text-slate-400 text-right" data-meta-description-count>0/160</div>
                                </div>

                                <div class="space-y-2">
                                    <label for="slug" class="block text-sm font-semibold text-slate-900">Kanonik URL</label>
                                    <input id="slug" name="slug" type="text" value="{{ old('slug', $post->slug) }}"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                </div>

                                <div class="space-y-2">
                                    <label for="meta_keywords" class="block text-sm font-semibold text-slate-900">SEO anahtar kelimeler</label>
                                    <textarea id="meta_keywords" name="meta_keywords" rows="2"
                                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">{{ old('meta_keywords', $post->meta_keywords) }}</textarea>
                                    <p class="text-xs text-slate-500">Virgul ile ayirin (or. yazilim, php, laravel).</p>
                                </div>

                                <div class="space-y-4 pt-4">
                                    <div class="space-y-2">
                                        <label for="published_at" class="block text-sm font-semibold text-slate-900">Yayin tarihi (opsiyonel)</label>
                                        <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\\\\TH:i')) }}"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="featured_image" class="block text-sm font-semibold text-slate-900">One cikan gorsel</label>
                                        <input id="featured_image" name="featured_image" type="file" accept="image/*"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                        <p class="text-xs text-slate-500">Maks 5 MB.</p>
                                    </div>
                                </div>

                                <div class="space-y-4 pt-4">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">Gorsel lisans bilgileri</div>
                                        <div class="text-xs text-slate-500">Bu bilgiler, yazidaki gorseller icin lisans meta verisi olarak kullanilir.</div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label for="image_license_url" class="block text-sm font-semibold text-slate-900">Lisans URL</label>
                                            <input id="image_license_url" name="image_license_url" type="url"
                                                   value="{{ old('image_license_url', $post->image_license_url) }}"
                                                   placeholder="https://example.com/license"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                        </div>
                                        <div class="space-y-2">
                                            <label for="image_acquire_url" class="block text-sm font-semibold text-slate-900">Lisans alma sayfasi</label>
                                            <input id="image_acquire_url" name="image_acquire_url" type="url"
                                                   value="{{ old('image_acquire_url', $post->image_acquire_url) }}"
                                                   placeholder="https://example.com/buy"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label for="image_credit_text" class="block text-sm font-semibold text-slate-900">Kredi metni</label>
                                            <input id="image_credit_text" name="image_credit_text" type="text"
                                                   value="{{ old('image_credit_text', $post->image_credit_text) }}"
                                                   placeholder="Or: Foto: Ografi Studio"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                        </div>
                                        <div class="space-y-2">
                                            <label for="image_creator_name" class="block text-sm font-semibold text-slate-900">Yaratici adi</label>
                                            <input id="image_creator_name" name="image_creator_name" type="text"
                                                   value="{{ old('image_creator_name', $post->image_creator_name) }}"
                                                   placeholder="Or: Ografi Studio"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="image_copyright_notice" class="block text-sm font-semibold text-slate-900">Telif notu</label>
                                        <input id="image_copyright_notice" name="image_copyright_notice" type="text"
                                               value="{{ old('image_copyright_notice', $post->image_copyright_notice) }}"
                                               placeholder="Or: © 2026 Ografi"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none">
                                    </div>
                                </div>

                                <div class="space-y-4 pt-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Yorumlari devre disi birak</div>
                                            <div class="text-xs text-slate-500">Bu, hikayenizin altindaki yorumlar bolumunu gizleyecektir.</div>
                                        </div>
                                        <div class="shrink-0">
                                            <input type="hidden" name="comments_disabled" value="0">
                                            <x-ui.switch
                                                name="comments_disabled"
                                                value="1"
                                                :checked="old('comments_disabled', $post->comments_disabled ? 1 : 0) == 1"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Isyerinde izlenmesi uygun degil (NSFW)</div>
                                            <div class="text-xs text-slate-500">Yetiskinlere yonelik icerik icermektedir.</div>
                                        </div>
                                        <div class="shrink-0">
                                            <input type="hidden" name="is_nsfw" value="0">
                                            <x-ui.switch
                                                name="is_nsfw"
                                                value="1"
                                                :checked="old('is_nsfw', $post->is_nsfw ? 1 : 0) == 1"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Pin hikayesi</div>
                                            <div class="text-xs text-slate-500">Sabitlediginiz hikayeler yalnizca profilinizde gorunur.</div>
                                        </div>
                                        <div class="shrink-0">
                                            <input type="hidden" name="is_pinned" value="0">
                                            <x-ui.switch
                                                name="is_pinned"
                                                value="1"
                                                :checked="old('is_pinned', $post->is_pinned ? 1 : 0) == 1"
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
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" data-preview-close></div>
            <div class="fixed inset-0 overflow-y-auto">
                <div class="mx-auto mt-4 w-full max-w-3xl px-3 sm:mt-10 sm:px-4">
                    <div class="rounded-xl bg-white shadow-xl">
                        <div class="flex items-center justify-between gap-3 px-3 py-3 sm:px-4">
                            <div class="text-sm font-semibold text-slate-900">On izleme</div>
                            <button type="button" data-preview-close class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="Kapat">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="max-h-[75vh] overflow-auto p-3 sm:max-h-[70vh] sm:p-4">
                            <div id="post-preview-content" class="space-y-4 text-sm text-slate-700"></div>
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

            const form = document.getElementById('post-edit-form');
            const isPublishedInput = document.getElementById('is_published');
            const draftButton = document.querySelector('[data-submit-intent="draft"]');
            const publishButton = document.querySelector('[data-submit-intent="publish"]');
            const categoryInput = document.querySelector('[data-category-input]');
            const categoryLabel = document.querySelector('[data-category-label]');
            const categoryMenu = document.querySelector('[data-category-menu]');
            const categoryOptions = Array.from(document.querySelectorAll('[data-category-option]'));
            const metaDescription = document.getElementById('meta_description');
            const metaDescriptionCount = document.querySelector('[data-meta-description-count]');
            const tagSearchInput = document.getElementById('tag_search');
            const newTagsInput = document.getElementById('new_tags');
            const newTagsChips = document.getElementById('new-tags-chips');
            const addNewTagButton = document.getElementById('add-new-tag-btn');
            const existingTagOptions = Array.from(document.querySelectorAll('[data-tag-option]'));

            const syncMetaDescriptionCount = () => {
                if (!metaDescription || !metaDescriptionCount) return;
                metaDescriptionCount.textContent = `${metaDescription.value.length}/160`;
            };

            syncMetaDescriptionCount();
            metaDescription?.addEventListener('input', syncMetaDescriptionCount);

            const syncCategorySelection = () => {
                if (!categoryInput || !categoryLabel) return;
                const activeValue = String(categoryInput.value || '');
                const activeOption = categoryOptions.find((option) => String(option.getAttribute('data-value') || '') === activeValue);
                categoryLabel.textContent = activeOption?.getAttribute('data-label') || 'Kategori sec';

                categoryOptions.forEach((option) => {
                    const isActive = String(option.getAttribute('data-value') || '') === activeValue;
                    option.classList.toggle('bg-slate-50', isActive);
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

            // Tag UX: yazdikca var olan etiketleri filtreler ve yeni etiket eklemeye izin verir.
            const existingTagNames = existingTagOptions.map((el) => String(el.getAttribute('data-tag-name') || '').trim()).filter(Boolean);
            const newTagSet = new Set(
                String(newTagsInput?.value || '')
                    .split(',')
                    .map((item) => item.trim().replace(/^#/, ''))
                    .filter(Boolean)
            );

            const syncNewTagsInput = () => {
                if (!newTagsInput) return;
                newTagsInput.value = Array.from(newTagSet).join(', ');
            };

            const renderNewTagChips = () => {
                if (!newTagsChips) return;
                const tags = Array.from(newTagSet);
                if (!tags.length) {
                    newTagsChips.innerHTML = '';
                    return;
                }
                newTagsChips.innerHTML = tags.map((tag) => `
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        #${tag}
                        <button type="button" data-remove-new-tag="${tag}" class="rounded-full px-1 text-emerald-700 hover:bg-emerald-100">x</button>
                    </span>
                `).join('');
            };

            const filterExistingTags = (term) => {
                const query = term.trim().toLowerCase();
                existingTagOptions.forEach((option) => {
                    const name = String(option.getAttribute('data-tag-name') || '');
                    const visible = !query || name.includes(query);
                    option.classList.toggle('hidden', !visible);
                });
            };

            const updateAddTagButton = (term) => {
                if (!addNewTagButton) return;
                const normalized = term.trim().replace(/^#/, '').toLowerCase();
                if (!normalized) {
                    addNewTagButton.classList.add('hidden');
                    addNewTagButton.textContent = 'Yeni etiket ekle';
                    return;
                }
                const existsInCurrent = Array.from(newTagSet).some((tag) => tag.toLowerCase() === normalized);
                const existsInDb = existingTagNames.includes(normalized);
                if (existsInCurrent || existsInDb) {
                    addNewTagButton.classList.add('hidden');
                    return;
                }
                addNewTagButton.classList.remove('hidden');
                addNewTagButton.textContent = `Yeni etiket ekle: #${term.trim().replace(/^#/, '')}`;
            };

            const normalizeTagText = (value) => String(value || '')
                .trim()
                .replace(/^#/, '')
                .replace(/[.,;:!?]+$/g, '')
                .trim();

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
                if (!hasDelimiter && !consumeAll) {
                    return { added: false, remainder: text };
                }

                const parts = text.split(/[,.;:!?]+/);
                const trailingDelimiter = /[,.;:!?]\s*$/.test(text);
                const candidates = (consumeAll || trailingDelimiter) ? parts : parts.slice(0, -1);
                const remainder = (consumeAll || trailingDelimiter) ? '' : (parts.at(-1) || '');

                let added = false;
                candidates.forEach((part) => {
                    if (tryAddNewTag(part)) {
                        added = true;
                    }
                });

                return { added, remainder };
            };

            renderNewTagChips();
            syncNewTagsInput();

            tagSearchInput?.addEventListener('input', () => {
                const term = tagSearchInput.value || '';
                const committed = commitTagInput(term, false);
                if (committed.added || committed.remainder !== term) {
                    tagSearchInput.value = committed.remainder;
                }
                filterExistingTags(tagSearchInput.value || '');
                updateAddTagButton(tagSearchInput.value || '');
            });

            tagSearchInput?.addEventListener('keydown', (e) => {
                const punctuationKeys = [',', '.', ';', ':', '!', '?'];
                if (e.key !== 'Enter' && !punctuationKeys.includes(e.key)) return;
                e.preventDefault();
                const committed = commitTagInput(tagSearchInput.value, true);
                if (committed.added) {
                    tagSearchInput.value = '';
                }
                filterExistingTags(tagSearchInput.value || '');
                updateAddTagButton(tagSearchInput.value || '');
            });

            tagSearchInput?.addEventListener('blur', () => {
                const committed = commitTagInput(tagSearchInput.value, true);
                if (committed.added) {
                    tagSearchInput.value = '';
                }
                filterExistingTags('');
                updateAddTagButton('');
            });

            addNewTagButton?.addEventListener('click', () => {
                if (!tagSearchInput) return;
                const added = tryAddNewTag(tagSearchInput.value);
                if (!added) return;
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
                if (!isPublishedInput) return;
                isPublishedInput.value = '0';
            });

            const openSettingsButtons = document.querySelectorAll('[data-open-settings]');
            const settingsModal = document.getElementById('settings-modal');
            const settingsDrawer = settingsModal?.querySelector('[data-settings-drawer]');
            const settingsOverlay = settingsModal?.querySelector('[data-settings-overlay]');
            const headerActionMenus = document.querySelectorAll('[data-create-actions-menu]');
            const previewModal = document.getElementById('post-preview-modal');
            let settingsHideTimer = null;
            const desktopSettingsMedia = window.matchMedia('(min-width: 640px)');

            const syncScrollLock = () => {
                const anyOpen = [previewModal, settingsModal].some((modal) => modal && !modal.classList.contains('hidden'));
                document.documentElement.classList.toggle('overflow-hidden', anyOpen);
                document.body.classList.toggle('overflow-hidden', anyOpen);
            };

            const closeHeaderActionMenus = () => {
                headerActionMenus.forEach((menu) => menu.removeAttribute('open'));
            };

            const closeCategoryMenu = () => {
                categoryMenu?.removeAttribute('open');
            };

            const setSettingsDrawerState = (isOpen) => {
                if (!settingsDrawer) return;

                if (desktopSettingsMedia.matches) {
                    settingsDrawer.classList.toggle('translate-x-full', !isOpen);
                    settingsDrawer.classList.remove('translate-y-full');
                    return;
                }

                settingsDrawer.classList.toggle('translate-y-full', !isOpen);
                settingsDrawer.classList.remove('translate-x-full');
            };

            const openSettings = () => {
                if (!settingsModal || !settingsDrawer || !settingsOverlay) return;
                if (settingsHideTimer) {
                    clearTimeout(settingsHideTimer);
                    settingsHideTimer = null;
                }
                closeHeaderActionMenus();
                closeCategoryMenu();
                settingsModal.classList.remove('hidden');
                settingsModal.setAttribute('aria-hidden', 'false');
                syncScrollLock();
                requestAnimationFrame(() => {
                    settingsOverlay.classList.remove('opacity-0');
                    setSettingsDrawerState(true);
                });
            };

            const closeSettings = () => {
                if (!settingsModal || !settingsDrawer || !settingsOverlay) return;
                settingsOverlay.classList.add('opacity-0');
                setSettingsDrawerState(false);
                settingsModal.setAttribute('aria-hidden', 'true');
                if (settingsHideTimer) clearTimeout(settingsHideTimer);
                settingsHideTimer = window.setTimeout(() => {
                    settingsModal.classList.add('hidden');
                    syncScrollLock();
                }, 280);
                syncScrollLock();
            };

            setSettingsDrawerState(false);

            settingsModal?.querySelectorAll('[data-settings-close]').forEach((el) => el.addEventListener('click', closeSettings));
            openSettingsButtons?.forEach((button) => {
                button.addEventListener('click', openSettings);
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

                return Boolean((fallbackTextarea?.value || '').trim());
            };

            publishButton?.addEventListener('click', async (e) => {
                if (!form || !isPublishedInput) return;
                e.preventDefault();

                const titleValue = (document.getElementById('title')?.value || '').trim();
                const hasContent = await isEditorContentPresent();

                if (!titleValue || !hasContent) {
                    alert('* ile isaretli alanlar bos birakilamaz.');
                    return;
                }

                isPublishedInput.value = '1';
                form.submit();
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

            document.addEventListener('click', (e) => {
                const target = e.target;
                if (!(target instanceof Element)) return;

                if (categoryMenu && !categoryMenu.contains(target)) {
                    closeCategoryMenu();
                }

                const clickedInActionMenu = Array.from(headerActionMenus).some((menu) => menu.contains(target));
                if (!clickedInActionMenu) {
                    closeHeaderActionMenus();
                }
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

                    if (host === 'youtu.be') {
                        return parts[0] ? `https://www.youtube.com/embed/${encodeURIComponent(parts[0])}` : null;
                    }
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
                        const kind = parts[0];
                        const code = parts[1];
                        if (['p', 'reel', 'tv'].includes(kind) && code) {
                            return `https://www.instagram.com/${kind}/${encodeURIComponent(code)}/embed`;
                        }
                    }
                    if (host.endsWith('tiktok.com')) {
                        if (parts[0] === 'embed' && parts[1] === 'v2' && parts[2]) {
                            return `https://www.tiktok.com/embed/v2/${encodeURIComponent(parts[2])}`;
                        }
                        if (parts[1] === 'video' && parts[2]) {
                            return `https://www.tiktok.com/embed/v2/${encodeURIComponent(parts[2])}`;
                        }
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
                    if (host.endsWith('facebook.com') || host.endsWith('fb.watch')) {
                        return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(parsed.toString())}&show_text=false`;
                    }
                    if (host.endsWith('twitter.com') || host.endsWith('.twitter.com') || host === 'x.com' || host === 'www.x.com' || host === 'mobile.x.com' || host.endsWith('.x.com')) {
                        if (parts.includes('status') || parts.includes('statuses')) return `https://twitframe.com/show?url=${encodeURIComponent(parsed.toString())}`;
                    }
                    if (host.endsWith('vine.co') && parts[0] === 'v' && parts[1]) {
                        return `https://vine.co/v/${encodeURIComponent(parts[1])}/embed/simple`;
                    }
                    return null;
                };

                for (const block of blocks) {
                    if (!block || !block.type) continue;

                    if (block.type === 'header') {
                        const level = Math.min(Math.max(parseInt(block.data?.level || 2, 10), 2), 4);
                        parts.push(`<h${level} class="text-base font-semibold text-slate-900">${escapeHtml(block.data?.text || '')}</h${level}>`);
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
 
                    parts.push(`<pre class="overflow-auto rounded-lg bg-slate-950/5 p-3 text-xs text-slate-700">${escapeHtml(JSON.stringify(block, null, 2))}</pre>`);
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
                        : `<p class="text-slate-500">EditorJS on izleme icin henuz hazir degil (icerigi doldurup tekrar deneyin).</p>`;
                }

                const tagLine = selectedTags.length ? selectedTags.join(', ') : '-';
                const newTagLine = newTags.trim() ? escapeHtml(newTags.trim()) : '-';

                return `
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500">Baslik</div>
                        <div class="text-base font-semibold text-slate-900">${escapeHtml(title || '-')}</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500">Ozet</div>
                        <div class="whitespace-pre-wrap">${escapeHtml(excerpt || '-')}</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500">Etiketler</div>
                        <div>${escapeHtml(tagLine)}</div>
                        <div class="text-xs text-slate-500">Yeni etiket</div>
                        <div>${newTagLine}</div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-xs text-slate-500">Icerik</div>
                        <div class="space-y-3">${contentHtml}</div>
                    </div>
                `;
            };

            const openPreviewButtons = document.querySelectorAll('[data-open-preview]');
            openPreviewButtons?.forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!previewModal || !previewContent) return;
                    closeHeaderActionMenus();
                    closeCategoryMenu();
                    previewModal.classList.remove('hidden');
                    syncScrollLock();
                    previewContent.innerHTML = '<p class="text-slate-500">Hazirlaniyor...</p>';
                    previewContent.innerHTML = await buildPreview();
                });
            });
        });
    </script>
@endpush


