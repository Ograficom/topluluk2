@extends('layouts.app')

@section('title', __('site.search.title'))
@section('meta_description', __('site.search.meta_description'))

<style>
    .og-search-page {
        display: flex;
        flex-direction: column;
        gap: 14px;
        width: 100%;
    }

    .og-search-bar {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        height: 52px;
        padding: 0 46px 0 46px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .og-search-bar-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: #94a3b8;
        pointer-events: none;
    }

    .og-search-bar-input {
        width: 100%;
        height: 100%;
        border: 0;
        background: transparent;
        font-size: 15px;
        color: #0f172a;
        outline: none;
    }

    .og-search-bar-input::placeholder {
        color: #94a3b8;
    }

    .og-search-bar-clear {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 9999px;
        border: 0 !important;
        background: #f1f5f9 !important;
        color: #64748b !important;
    }

    .og-search-bar-clear:hover {
        background: #e2e8f0 !important;
    }

    .og-search-bar-clear:active {
        background: #cbd5e1 !important;
    }

    .og-search-filters {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .og-search-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .og-search-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 34px;
        padding: 0 16px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        transition: background-color 120ms ease, color 120ms ease, border-color 120ms ease;
    }

    .og-search-pill iconify-icon {
        flex-shrink: 0;
        font-size: 15px;
        color: #64748b;
        transition: color 120ms ease;
    }

    .og-search-pill:hover {
        background: #f1f5f9 !important;
    }

    .og-search-pill:active {
        background: #e2e8f0 !important;
    }

    .og-search-pill.is-active {
        background: #e2e8f0 !important;
        border-color: #2563eb !important;
        color: #1d4ed8 !important;
    }

    .og-search-pill.is-active iconify-icon {
        color: #2563eb;
    }

    .og-search-types {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: none;
    }

    .og-search-types::-webkit-scrollbar {
        display: none;
    }

    .og-search-type-pill {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 32px;
        padding: 0 14px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        color: #0f172a !important;
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
        transition: background-color 120ms ease, color 120ms ease, border-color 120ms ease;
    }

    .og-search-type-pill iconify-icon {
        flex-shrink: 0;
        font-size: 15px;
        color: #64748b;
        transition: color 120ms ease;
    }

    .og-search-type-pill:hover {
        background: #f1f5f9 !important;
    }

    .og-search-type-pill:active {
        background: #e2e8f0 !important;
    }

    .og-search-type-pill.is-active {
        background: #e2e8f0 !important;
        border-color: #2563eb !important;
        color: #1d4ed8 !important;
    }

    .og-search-type-pill.is-active iconify-icon {
        color: #2563eb;
    }

    .og-search-results {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .og-search-message {
        padding: 28px 16px;
        text-align: center;
        font-size: 14px;
        color: #64748b;
        border-radius: var(--card-radius, 16px);
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .og-search-box {
        border-radius: var(--card-radius, 16px);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 16px;
    }

    .og-search-box-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .og-search-box-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
    }

    .og-search-box-count {
        font-size: 12px;
        color: #94a3b8;
    }

    .og-search-box-more {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 10px;
        width: 100%;
        padding: 9px 0;
        border-radius: 10px;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        color: #0f172a !important;
        font-size: 13px;
        font-weight: 600;
    }

    .og-search-box-more iconify-icon {
        font-size: 15px;
        color: #2563eb;
    }

    .og-search-box-more:hover {
        background: #f1f5f9 !important;
    }

    .og-search-box-more:active {
        background: #e2e8f0 !important;
    }

    .og-result-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .og-result-flow {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .og-result-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        text-decoration: none;
    }

    .og-result-row:hover {
        background: #f8fafc;
    }

    .og-result-row--post,
    .og-result-row--comment {
        align-items: flex-start;
    }

    .og-result-row-main {
        min-width: 0;
        flex: 1 1 auto;
    }

    .og-result-row-title {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .og-result-row-snippet {
        margin-top: 3px;
        font-size: 12.5px;
        line-height: 1.45;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .og-result-row-meta {
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11.5px;
        color: #94a3b8;
    }

    .og-result-avatar {
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
    }

    .og-result-row-user {
        min-width: 0;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    .og-result-row-subtitle {
        font-size: 12px;
        color: #94a3b8;
    }

    .og-result-row-badge {
        flex: 0 0 auto;
        font-size: 11.5px;
        color: #94a3b8;
    }

    .og-result-tag {
        display: inline-flex;
        align-items: center;
        height: 32px;
        padding: 0 14px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .og-result-tag:hover {
        background: #f1f5f9;
    }

    .og-nsfw-badge {
        display: inline-flex;
        align-items: center;
        height: 18px;
        padding: 0 7px;
        border-radius: 9999px;
        background: #fef2f2;
        color: #b91c1c;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .og-search-more-wrap {
        display: flex;
    }

    .og-search-more-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        padding: 12px 0;
        border-radius: 14px;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 14px;
        font-weight: 600;
    }

    .og-search-more-btn iconify-icon {
        font-size: 16px;
        color: #2563eb;
    }

    .og-search-more-btn:hover {
        background: #f8fafc !important;
    }

    .og-search-more-btn:active {
        background: #e2e8f0 !important;
    }

    @media (max-width: 640px) {
        .og-search-page {
            padding-left: 12px;
            padding-right: 12px;
        }

        .og-search-bar {
            height: 48px;
        }
    }

    html.dark .og-search-bar,
    html.dark .og-search-box,
    html.dark .og-search-message,
    html.dark .og-search-more-btn {
        background: #0f172a !important;
        border-color: #1e293b !important;
    }

    html.dark .og-search-pill,
    html.dark .og-search-type-pill,
    html.dark .og-search-box-more {
        background: #0f172a !important;
        border-color: #1e293b !important;
        color: #e2e8f0 !important;
    }

    html.dark .og-search-pill:hover,
    html.dark .og-search-type-pill:hover {
        background: #1e293b !important;
    }

    html.dark .og-search-pill:active,
    html.dark .og-search-type-pill:active {
        background: #334155 !important;
    }

    html.dark .og-search-pill.is-active,
    html.dark .og-search-type-pill.is-active {
        background: #1e293b !important;
        border-color: #3b82f6 !important;
        color: #60a5fa !important;
    }

    html.dark .og-search-pill.is-active iconify-icon,
    html.dark .og-search-type-pill.is-active iconify-icon {
        color: #3b82f6;
    }

    html.dark .og-search-bar-input {
        color: #e2e8f0;
    }

    html.dark .og-result-row:hover {
        background: #1e293b;
    }

    html.dark .og-result-row-title {
        color: #e2e8f0;
    }
</style>

@php
    $searchI18n = [
        'all' => __('site.search.all'),
        'posts' => __('site.search.posts'),
        'categories' => __('site.search.categories'),
        'tags' => __('site.search.tags'),
        'users' => __('site.search.users'),
        'comments' => __('site.search.comments'),
        'pages' => __('site.search.pages'),
        'sortRelevance' => __('site.search.sort_relevance'),
        'sortNewest' => __('site.search.sort_newest'),
        'sortPopular' => __('site.search.sort_popular'),
        'filterNsfw' => __('site.search.filter_nsfw'),
        'filterAi' => __('site.search.filter_ai'),
        'emptyQuery' => __('site.search.empty_query'),
        'tooShort' => __('site.search.too_short'),
        'disabled' => __('site.search.disabled'),
        'empty' => __('site.search.empty'),
        'showMore' => __('site.search.show_more'),
        'loading' => __('site.search.loading'),
        'views' => __('site.search.views'),
        'followersCount' => __('site.search.followers_count'),
        'postsCount' => __('site.search.posts_count'),
        'inPost' => __('site.search.in_post'),
        'categoryBadge' => __('site.search.category_badge'),
        'searchFailed' => __('site.mobile_nav.search_failed'),
    ];
@endphp

@section('content')
    <div class="og-search-page" data-search-page
         data-endpoint="{{ route('search') }}"
         data-initial-query="{{ $query }}"
         data-initial-type="{{ $meta['type'] }}"
         data-initial-sort="{{ $meta['sort'] }}"
         data-initial-nsfw="{{ $meta['nsfw'] ? '1' : '0' }}"
         data-initial-ai="{{ $meta['ai'] ? '1' : '0' }}"
         data-i18n="{{ json_encode($searchI18n, JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="og-search-bar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="og-search-bar-icon" aria-hidden="true">
                <path d="M13.78 12.72a6 6 0 10-1.06 1.06l3.75 3.75a.75.75 0 101.06-1.06l-3.75-3.75zM12 9a5 5 0 11-10 0 5 5 0 0110 0z" fill="currentColor"/>
            </svg>
            <input
                type="text"
                value="{{ $query }}"
                placeholder="{{ __('site.search.placeholder') }}"
                class="og-search-bar-input"
                autocomplete="off"
                autofocus
                data-search-query-input
            >
            <button type="button" class="og-search-bar-clear {{ $query === '' ? 'hidden' : '' }}" aria-label="{{ __('site.mobile_nav.clear') ?? 'Temizle' }}" data-search-query-clear>
                <iconify-icon icon="lucide:x"></iconify-icon>
            </button>
        </div>

        <div class="og-search-filters">
            <div class="og-search-pills" data-search-sort-pills>
                <button type="button" class="og-search-pill og-search-pill--sort" data-sort="relevance"><iconify-icon icon="lucide:sparkles"></iconify-icon>{{ __('site.search.sort_relevance') }}</button>
                <button type="button" class="og-search-pill og-search-pill--sort" data-sort="newest"><iconify-icon icon="lucide:clock"></iconify-icon>{{ __('site.search.sort_newest') }}</button>
                <button type="button" class="og-search-pill og-search-pill--sort" data-sort="popular"><iconify-icon icon="lucide:flame"></iconify-icon>{{ __('site.search.sort_popular') }}</button>
            </div>
            <div class="og-search-pills og-search-pills--toggles">
                <button type="button" class="og-search-pill og-search-pill--toggle" data-toggle="nsfw"><iconify-icon icon="lucide:eye-off"></iconify-icon>{{ __('site.search.filter_nsfw') }}</button>
                <button type="button" class="og-search-pill og-search-pill--toggle" data-toggle="ai"><iconify-icon icon="lucide:bot"></iconify-icon>{{ __('site.search.filter_ai') }}</button>
            </div>
        </div>

        <div class="og-search-types" data-search-type-pills>
            <button type="button" class="og-search-type-pill" data-type="all"><iconify-icon icon="lucide:layout-grid"></iconify-icon>{{ __('site.search.all') }}</button>
            <button type="button" class="og-search-type-pill" data-type="posts"><iconify-icon icon="lucide:file-text"></iconify-icon>{{ __('site.search.posts') }}</button>
            <button type="button" class="og-search-type-pill" data-type="categories"><iconify-icon icon="lucide:folder"></iconify-icon>{{ __('site.search.categories') }}</button>
            <button type="button" class="og-search-type-pill" data-type="tags"><iconify-icon icon="lucide:tag"></iconify-icon>{{ __('site.search.tags') }}</button>
            <button type="button" class="og-search-type-pill" data-type="users"><iconify-icon icon="lucide:users"></iconify-icon>{{ __('site.search.users') }}</button>
            <button type="button" class="og-search-type-pill" data-type="comments"><iconify-icon icon="lucide:message-circle"></iconify-icon>{{ __('site.search.comments') }}</button>
            <button type="button" class="og-search-type-pill" data-type="pages"><iconify-icon icon="lucide:file"></iconify-icon>{{ __('site.search.pages') }}</button>
        </div>

        <div class="og-search-results" data-search-results-container>
            <p class="og-search-message">{{ __('site.search.loading') }}</p>
        </div>

        <div class="og-search-more-wrap hidden" data-search-more-wrap>
            <button type="button" class="og-search-more-btn" data-search-more-btn><iconify-icon icon="lucide:chevron-down"></iconify-icon>{{ __('site.search.show_more') }}</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-search-page]');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const i18n = JSON.parse(root.dataset.i18n || '{}');

    const queryInput = root.querySelector('[data-search-query-input]');
    const queryClear = root.querySelector('[data-search-query-clear]');
    const sortPills = Array.from(root.querySelectorAll('[data-search-sort-pills] [data-sort]'));
    const togglePills = Array.from(root.querySelectorAll('[data-toggle]'));
    const typePills = Array.from(root.querySelectorAll('[data-search-type-pills] [data-type]'));
    const resultsContainer = root.querySelector('[data-search-results-container]');
    const moreWrap = root.querySelector('[data-search-more-wrap]');
    const moreBtn = root.querySelector('[data-search-more-btn]');

    const state = {
        q: root.dataset.initialQuery || '',
        type: root.dataset.initialType || 'all',
        sort: root.dataset.initialSort || 'relevance',
        nsfw: root.dataset.initialNsfw === '1',
        ai: root.dataset.initialAi === '1',
        offset: 0,
    };

    let requestId = 0;
    let debounceTimer = null;
    let accumulated = null;

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const syncPillStates = () => {
        sortPills.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.sort === state.sort));
        togglePills.forEach((btn) => btn.classList.toggle('is-active', Boolean(state[btn.dataset.toggle])));
        typePills.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.type === state.type));
        queryClear.classList.toggle('hidden', !queryInput.value.trim());
    };

    const syncUrl = () => {
        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.type !== 'all') params.set('type', state.type);
        if (state.sort !== 'relevance') params.set('sort', state.sort);
        if (state.nsfw) params.set('nsfw', '1');
        if (state.ai) params.set('ai', '1');
        const qs = params.toString();
        const url = qs ? `${endpoint}?${qs}` : endpoint;
        window.history.replaceState({}, '', url);
    };

    const avatarHtml = (avatar, label, sizeClass) => avatar
        ? `<img src="${escapeHtml(avatar)}" alt="" class="${sizeClass} rounded-full object-cover" loading="lazy">`
        : `<span class="${sizeClass} inline-flex items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">${escapeHtml((label || '?').trim().charAt(0).toUpperCase() || '?')}</span>`;

    const boxWrapper = (key, label, count, innerHtml, seeAllHtml = '') => `
        <section class="og-search-box" data-search-box="${key}">
            <div class="og-search-box-head">
                <h2 class="og-search-box-title">${escapeHtml(label)}</h2>
                <span class="og-search-box-count">${count}</span>
            </div>
            <div class="og-search-box-body">${innerHtml}</div>
            ${seeAllHtml}
        </section>
    `;

    const renderPosts = (items) => items.map((post) => `
        <a href="${escapeHtml(post.url)}" class="og-result-row og-result-row--post">
            <div class="og-result-row-main">
                <p class="og-result-row-title">${escapeHtml(post.title)}${post.is_nsfw ? '<span class="og-nsfw-badge">NSFW</span>' : ''}</p>
                ${post.snippet ? `<p class="og-result-row-snippet">${escapeHtml(post.snippet)}</p>` : ''}
                <div class="og-result-row-meta">
                    ${post.category ? `<span>${escapeHtml(post.category)}</span>` : ''}
                    ${post.author ? `<span>${escapeHtml(post.author)}</span>` : ''}
                    <span>${i18n.views.replace(':count', Number(post.views || 0).toLocaleString('tr-TR'))}</span>
                </div>
            </div>
        </a>
    `).join('');

    const renderCategories = (items) => items.map((cat) => `
        <a href="${escapeHtml(cat.url)}" class="og-result-row og-result-row--chip">
            ${avatarHtml(cat.avatar, cat.title, 'og-result-avatar')}
            <span class="og-result-row-title">${escapeHtml(cat.title)}</span>
            <span class="og-result-row-badge">${i18n.postsCount.replace(':count', Number(cat.posts_count || 0).toLocaleString('tr-TR'))}</span>
        </a>
    `).join('');

    const renderTags = (items) => items.map((tag) => `
        <a href="${escapeHtml(tag.url)}" class="og-result-tag">#${escapeHtml(tag.title)}</a>
    `).join('');

    const renderUsers = (items) => items.map((user) => `
        <a href="${escapeHtml(user.url || '#')}" class="og-result-row og-result-row--chip">
            ${avatarHtml(user.avatar, user.title, 'og-result-avatar')}
            <span class="og-result-row-user">
                <span class="og-result-row-title">${escapeHtml(user.title)}</span>
                ${user.subtitle ? `<span class="og-result-row-subtitle">${escapeHtml(user.subtitle)}</span>` : ''}
            </span>
            <span class="og-result-row-badge">${i18n.followersCount.replace(':count', Number(user.followers_count || 0).toLocaleString('tr-TR'))}</span>
        </a>
    `).join('');

    const renderComments = (items) => items.map((comment) => `
        <a href="${escapeHtml(comment.url || '#')}" class="og-result-row og-result-row--comment">
            ${avatarHtml(comment.author_avatar, comment.author, 'og-result-avatar')}
            <div class="og-result-row-main">
                <p class="og-result-row-snippet">${escapeHtml(comment.snippet)}</p>
                <div class="og-result-row-meta">
                    <span>${escapeHtml(comment.author || '')}</span>
                    ${comment.post_title ? `<span>${i18n.inPost.replace(':title', comment.post_title)}</span>` : ''}
                </div>
            </div>
        </a>
    `).join('');

    const renderPages = (items) => items.map((page) => `
        <a href="${escapeHtml(page.url)}" class="og-result-row og-result-row--post">
            <div class="og-result-row-main">
                <p class="og-result-row-title">${escapeHtml(page.title)}</p>
                ${page.snippet ? `<p class="og-result-row-snippet">${escapeHtml(page.snippet)}</p>` : ''}
            </div>
        </a>
    `).join('');

    const typeRenderers = {
        posts: { render: renderPosts, label: i18n.posts },
        categories: { render: renderCategories, label: i18n.categories },
        tags: { render: renderTags, label: i18n.tags },
        users: { render: renderUsers, label: i18n.users },
        comments: { render: renderComments, label: i18n.comments },
        pages: { render: renderPages, label: i18n.pages },
    };

    const renderMessage = (message) => {
        resultsContainer.innerHTML = `<p class="og-search-message">${escapeHtml(message)}</p>`;
        moreWrap.classList.add('hidden');
    };

    const renderAllTypes = (data) => {
        const boxes = [];
        let total = 0;

        Object.keys(typeRenderers).forEach((key) => {
            const items = Array.isArray(data[key]) ? data[key] : [];
            if (!items.length) return;
            total += items.length;
            const { render, label } = typeRenderers[key];
            const isFlow = key === 'tags';
            const inner = render(items);
            boxes.push(boxWrapper(
                key,
                label,
                items.length,
                `<div class="${isFlow ? 'og-result-flow' : 'og-result-list'}">${inner}</div>`,
                `<button type="button" class="og-search-box-more" data-search-jump-type="${key}"><iconify-icon icon="lucide:chevron-down"></iconify-icon>${i18n.showMore}</button>`,
            ));
        });

        if (!total) {
            renderMessage(i18n.empty);
            return;
        }

        resultsContainer.innerHTML = boxes.join('');
        moreWrap.classList.add('hidden');

        resultsContainer.querySelectorAll('[data-search-jump-type]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.type = btn.dataset.searchJumpType;
                state.offset = 0;
                accumulated = null;
                syncPillStates();
                syncUrl();
                runSearch();
            });
        });
    };

    const renderSingleType = (data, hasMore, append = false) => {
        const key = state.type;
        const { render, label } = typeRenderers[key];
        const items = Array.isArray(data[key]) ? data[key] : [];

        if (append && accumulated) {
            accumulated = accumulated.concat(items);
        } else {
            accumulated = items;
        }

        if (!accumulated.length) {
            renderMessage(i18n.empty);
            return;
        }

        const isFlow = key === 'tags';
        const inner = render(accumulated);
        resultsContainer.innerHTML = boxWrapper(key, label, accumulated.length, `<div class="${isFlow ? 'og-result-flow' : 'og-result-list'}">${inner}</div>`);
        moreWrap.classList.toggle('hidden', !hasMore);
    };

    const runSearch = async (append = false) => {
        const myRequestId = ++requestId;
        const q = state.q.trim();

        if (!q) {
            renderMessage(i18n.emptyQuery);
            return;
        }

        if (!append) {
            resultsContainer.innerHTML = `<p class="og-search-message">${escapeHtml(i18n.loading)}</p>`;
        }

        const params = new URLSearchParams({ q, type: state.type, sort: state.sort });
        if (state.nsfw) params.set('nsfw', '1');
        if (state.ai) params.set('ai', '1');
        if (append) params.set('offset', String(state.offset));

        try {
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await response.json();

            if (myRequestId !== requestId) return;

            if (json.meta && json.meta.too_short) {
                renderMessage(i18n.tooShort.replace(':min', String(json.meta.min_length ?? 2)));
                return;
            }

            if (json.meta && !json.meta.enabled) {
                renderMessage(i18n.disabled);
                return;
            }

            if (state.type === 'all') {
                renderAllTypes(json.data || {});
            } else {
                renderSingleType(json.data || {}, Boolean(json.meta && json.meta.has_more), append);
            }
        } catch (error) {
            if (myRequestId !== requestId) return;
            renderMessage(i18n.searchFailed || 'Sonuc alinamadi.');
        }
    };

    queryInput.addEventListener('input', () => {
        syncPillStates();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.q = queryInput.value;
            state.offset = 0;
            accumulated = null;
            syncUrl();
            runSearch();
        }, 220);
    });

    queryClear.addEventListener('click', () => {
        queryInput.value = '';
        state.q = '';
        syncPillStates();
        syncUrl();
        renderMessage(i18n.emptyQuery);
        queryInput.focus();
    });

    sortPills.forEach((btn) => btn.addEventListener('click', () => {
        state.sort = btn.dataset.sort;
        state.offset = 0;
        accumulated = null;
        syncPillStates();
        syncUrl();
        runSearch();
    }));

    togglePills.forEach((btn) => btn.addEventListener('click', () => {
        const key = btn.dataset.toggle;
        state[key] = !state[key];
        state.offset = 0;
        accumulated = null;
        syncPillStates();
        syncUrl();
        runSearch();
    }));

    typePills.forEach((btn) => btn.addEventListener('click', () => {
        state.type = btn.dataset.type;
        state.offset = 0;
        accumulated = null;
        syncPillStates();
        syncUrl();
        runSearch();
    }));

    moreBtn.addEventListener('click', () => {
        state.offset = (accumulated ? accumulated.length : 0);
        runSearch(true);
    });

    syncPillStates();
    if (state.q.trim()) {
        runSearch();
    } else {
        renderMessage(i18n.emptyQuery);
    }
});
</script>
@endpush
