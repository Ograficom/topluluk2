@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $breadcrumbs = $breadcrumbs ?? null;
    $breadcrumbItems = collect($breadcrumbs ?? [])->filter(function ($item) {
        return is_array($item) && !empty($item['name']) && !empty($item['url']);
    })->values();

    if ($breadcrumbItems->isEmpty()) {
        $breadcrumbItems = collect([
            ['name' => 'Ana sayfa', 'url' => route('home')],
        ]);
    }

    $posts = $posts ?? collect();
    $popularPosts = $popularPosts ?? collect();
    $categories = collect($categories ?? []);
    $reactionTypes = $reactionTypes ?? collect();
    $activeTagSlug = trim((string) ($activeTag ?? request()->query('tag', '')));
    $activeTagModel = collect($tags ?? [])->first(function ($item) use ($activeTagSlug) {
        return trim((string) ($item->slug ?? '')) === $activeTagSlug;
    });
    $activeTagName = trim((string) ($activeTagModel->name ?? $activeTagSlug));
    $isTagPage = $activeTagSlug !== '';

    $routeCategoryParam = request()->route('category');
    $routeCategorySlug = '';

    if (is_object($routeCategoryParam)) {
        $routeCategorySlug = trim((string) ($routeCategoryParam->slug ?? ''));
    } elseif (is_string($routeCategoryParam) || is_numeric($routeCategoryParam)) {
        $routeCategorySlug = trim((string) $routeCategoryParam);
    }

    if ($routeCategorySlug === '') {
        $segments = request()->segments();
        $lastSegment = end($segments);
        $routeCategorySlug = is_string($lastSegment) || is_numeric($lastSegment) ? trim((string) $lastSegment) : '';
    }

    $activeCategorySlug = trim((string) ($activeCategory ?? request()->query('category', $routeCategorySlug)));
    $categoryToShow = $category ?? null;

    if (!$categoryToShow && is_object($routeCategoryParam)) {
        $categoryToShow = $routeCategoryParam;
    }

    if (!$categoryToShow && $activeCategorySlug !== '') {
        $categoryToShow = $categories->first(function ($item) use ($activeCategorySlug) {
            $slug = trim((string) ($item->slug ?? ''));
            $nameSlug = Str::slug((string) ($item->name ?? ''));

            return $slug === $activeCategorySlug || $nameSlug === $activeCategorySlug;
        });
    }

    $isCategoryPage = !empty($categoryToShow)
        || $activeCategorySlug !== ''
        || request()->is('Categorys/*')
        || request()->is('categories/*')
        || request()->is('category/*');

    $postsCanPaginate = is_object($posts)
        && method_exists($posts, 'hasMorePages')
        && method_exists($posts, 'nextPageUrl');

    $postsHasPreviousPage = $postsCanPaginate
        && method_exists($posts, 'currentPage')
        && (int) $posts->currentPage() > 1;

    $postsPreviousPageUrl = $postsHasPreviousPage && method_exists($posts, 'previousPageUrl')
        ? $posts->previousPageUrl()
        : null;

    $postsNextPageUrl = $postsCanPaginate && method_exists($posts, 'nextPageUrl')
        ? $posts->nextPageUrl()
        : null;

    $loadedCount = $postsCanPaginate && method_exists($posts, 'lastItem')
        ? (int) ($posts->lastItem() ?? count($posts))
        : count($posts);

    $totalCount = $postsCanPaginate && method_exists($posts, 'total')
        ? (int) $posts->total()
        : count($posts);
@endphp

@if($isCategoryPage)
    @section('hide_feed_header', '1')
    @section('no_container_padding')
    @endsection
@endif

@if($isTagPage)
    @section('hide_feed_header', '1')
@endif

@section('title', $isCategoryPage && !empty($categoryToShow) && !empty($categoryToShow->name) ? $categoryToShow->name : 'Ografi Ana Sayfa')

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems->map(function ($item, $index) {
        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<style>
    .ografi-feed-loadmore {
        width: 100%;
        margin: 20px 0 30px;
        padding: 0 12px;
        box-sizing: border-box;
        text-align: center;
    }

    .tag-page-identity {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 38px;
        margin: 0 0 -16px;
        padding: 3px 17px;
        border: 1px solid #d9dde3;
        border-radius: 18px;
        background: #ffffff;
        color: #050505;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        box-sizing: border-box;
        box-shadow: none;
    }

    html.dark .tag-page-identity,
    .dark .tag-page-identity {
        border-color: #27272a;
        background: #18181b;
        color: #fafafa;
    }

    @media (max-width: 640px) {
        .tag-page-identity {
            width: 100vw;
            min-height: 34px;
            margin-right: calc(50% - 50vw);
            margin-bottom: -16px;
            margin-left: calc(50% - 50vw);
            padding: 2px 14px;
            border-right: 0;
            border-left: 0;
            border-radius: 16px;
            font-size: 13px;
        }
    }

    .ografi-feed-loadmore__button {
        display: inline-flex;
        width: min(100%, 310px);
        min-height: 52px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 0;
        border-radius: 14px;
        background: #3b82f6;
        color: #ffffff;
        font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 21px;
        font-weight: 500;
        line-height: 1;
        text-decoration: none;
        box-shadow: none;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .ografi-feed-loadmore__button:hover,
    .ografi-feed-loadmore__button:focus,
    .ografi-feed-loadmore__button:active {
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        outline: none;
    }

    .ografi-feed-loadmore__count {
        display: block;
        margin-top: 12px;
        color: #111827;
        font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 16px;
        font-weight: 500;
        line-height: 1.2;
        letter-spacing: 0.01em;
    }

    .ografi-feed-loadmore__spinner {
        display: none;
        width: 28px;
        height: 28px;
        border: 4px solid rgba(255, 255, 255, 0.38);
        border-top-color: #ffffff;
        border-radius: 999px;
        animation: ografi-loadmore-spin 0.8s linear infinite;
    }

    .ografi-feed-loadmore__button.is-loading {
        pointer-events: none;
        opacity: 0.95;
    }

    .ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__text {
        display: none;
    }

    .ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__spinner {
        display: inline-block;
    }

    @keyframes ografi-loadmore-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .category-index-feed__content {
        width: 100%;
        max-width: 100%;
        margin-top: 16px;
        padding: 0;
        box-sizing: border-box;
    }


    .category-index-tabs {
        display: flex;
        align-items: flex-end;
        gap: 18px;
        width: 100%;
        margin: 0;
        padding: 0 16px;
        background: #ffffff;
        border-radius: 0 0 16px 16px;
        box-sizing: border-box;
    }

    .category-index-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 0 12px;
        border: 0;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: #6b7280;
        font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        box-shadow: none;
        cursor: pointer;
    }

    .category-index-tab:hover,
    .category-index-tab:focus {
        color: #2563eb;
        text-decoration: none;
        outline: none;
    }

    .category-index-tab[aria-current="page"] {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    html.dark .category-index-tabs,
    .dark .category-index-tabs {
        background: #111827;
    }

    html.dark .category-index-tab,
    .dark .category-index-tab {
        color: #94a3b8;
    }

    html.dark .category-index-tab:hover,
    html.dark .category-index-tab:focus,
    html.dark .category-index-tab[aria-current="page"],
    .dark .category-index-tab:hover,
    .dark .category-index-tab:focus,
    .dark .category-index-tab[aria-current="page"] {
        color: #60a5fa;
        border-bottom-color: #60a5fa;
    }

    @media (max-width: 640px) {
        html,
        body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box !important;
        }

        body.route-category .main-grid,
        body.route-category .main-grid.main-grid--no-pad,
        body.route-category .layout-main,
        body.route-category main,
        body.alma-app.route-category .main-grid,
        body.alma-app.route-category .main-grid.main-grid--no-pad,
        body.alma-app.route-category .layout-main,
        body.alma-app.route-category main,
        body:has(.category-reference-card) .main-grid,
        body:has(.category-reference-card) .main-grid.main-grid--no-pad,
        body:has(.category-reference-card) .layout-main,
        body:has(.category-reference-card) main {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            box-sizing: border-box !important;
            overflow-x: hidden !important;
        }

        .profile-reference-page.category-reference-page {
            width: 100vw !important;
            max-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow-x: hidden !important;
        }

        .profile-reference-page.category-reference-page .profile-reference-shell,
        .category-index-feed__content {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            box-sizing: border-box !important;
        }

        body.route-category [data-post-card-shell],
        body.route-category .post-card,
        body.route-category article[data-post-card-shell],
        body:has(.category-reference-card) [data-post-card-shell],
        body:has(.category-reference-card) .post-card,
        body:has(.category-reference-card) article[data-post-card-shell] {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

        .ografi-feed-loadmore {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
            box-sizing: border-box !important;
        }

        .ografi-feed-loadmore__button {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 50px;
            border-radius: 13px;
            font-size: 20px;
        }

        .ografi-feed-loadmore__count {
            font-size: 15px;
        }
    }

    html.dark .ografi-feed-loadmore__count,
    .dark .ografi-feed-loadmore__count {
        color: #f8fafc;
    }

    html.dark .ografi-feed-loadmore__button,
    .dark .ografi-feed-loadmore__button {
        background: #2563eb;
        color: #ffffff;
    }

    html.dark .ografi-feed-loadmore__button:hover,
    html.dark .ografi-feed-loadmore__button:focus,
    html.dark .ografi-feed-loadmore__button:active,
    .dark .ografi-feed-loadmore__button:hover,
    .dark .ografi-feed-loadmore__button:focus,
    .dark .ografi-feed-loadmore__button:active {
        background: #1d4ed8;
        color: #ffffff;
    }


/* ========================================================================
   FINAL: DEVAMINI GOSTER BUTONU MODERN + SOL GERI ICONU
   ======================================================================== */
.ografi-feed-loadmore {
    width: 100% !important;
    margin: 22px 0 34px !important;
    padding: 0 12px !important;
    box-sizing: border-box !important;
    text-align: center !important;
}

.ografi-feed-loadmore__button {
    position: relative !important;
    display: inline-flex !important;
    width: min(100%, 330px) !important;
    min-height: 54px !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    border: 1px solid rgba(37, 99, 235, 0.22) !important;
    border-radius: 18px !important;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%) !important;
    color: #ffffff !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 18px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    text-decoration: none !important;
    box-shadow: 0 12px 26px rgba(37, 99, 235, 0.24) !important;
    cursor: pointer !important;
    overflow: hidden !important;
    -webkit-tap-highlight-color: transparent !important;
}

.ografi-feed-loadmore__button::before {
    content: "" !important;
    position: absolute !important;
    inset: 1px !important;
    border-radius: 17px !important;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.20), rgba(255, 255, 255, 0)) !important;
    pointer-events: none !important;
}

.ografi-feed-loadmore__button:hover,
.ografi-feed-loadmore__button:focus,
.ografi-feed-loadmore__button:active {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
    color: #ffffff !important;
    text-decoration: none !important;
    outline: none !important;
    box-shadow: 0 12px 26px rgba(37, 99, 235, 0.28) !important;
}

.ografi-feed-loadmore__icon,
.ografi-feed-loadmore__text,
.ografi-feed-loadmore__spinner {
    position: relative !important;
    z-index: 1 !important;
}

.ografi-feed-loadmore__icon {
    display: inline-flex !important;
    width: 30px !important;
    height: 30px !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 999px !important;
    background: rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    flex: 0 0 auto !important;
}

.ografi-feed-loadmore__icon svg {
    width: 17px !important;
    height: 17px !important;
    display: block !important;
}

.ografi-feed-loadmore__count {
    display: inline-flex !important;
    margin-top: 12px !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 30px !important;
    padding: 0 13px !important;
    border-radius: 999px !important;
    background: rgba(15, 23, 42, 0.05) !important;
    color: #111827 !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
}

.ografi-feed-loadmore__spinner {
    display: none !important;
    width: 28px !important;
    height: 28px !important;
    border: 4px solid rgba(255, 255, 255, 0.38) !important;
    border-top-color: #ffffff !important;
    border-radius: 999px !important;
    animation: ografi-loadmore-spin 0.8s linear infinite !important;
}

.ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__icon,
.ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__text {
    display: none !important;
}

.ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__spinner {
    display: inline-block !important;
}

@media (max-width: 640px) {
    .ografi-feed-loadmore {
        margin-top: 20px !important;
        margin-bottom: 32px !important;
        padding-left: 14px !important;
        padding-right: 14px !important;
    }

    .ografi-feed-loadmore__button {
        width: 100% !important;
        min-height: 52px !important;
        border-radius: 17px !important;
        font-size: 17px !important;
    }

    .ografi-feed-loadmore__icon {
        width: 29px !important;
        height: 29px !important;
    }
}

html.dark .ografi-feed-loadmore__count,
.dark .ografi-feed-loadmore__count {
    background: rgba(148, 163, 184, 0.12) !important;
    color: #f8fafc !important;
}

html.dark .ografi-feed-loadmore__button,
.dark .ografi-feed-loadmore__button {
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%) !important;
    border-color: rgba(96, 165, 250, 0.22) !important;
    color: #ffffff !important;
}



/* ========================================================================
   FINAL: GERI / ILERI IKI BUTONLU SAYFALANDIRMA
   ======================================================================== */
.ografi-feed-loadmore {
    width: 100% !important;
    margin: 22px 0 34px !important;
    padding: 0 12px !important;
    box-sizing: border-box !important;
    text-align: center !important;
}

.ografi-feed-loadmore__buttons {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 10px !important;
    width: min(100%, 430px) !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

.ografi-feed-page-button {
    position: relative !important;
    display: inline-flex !important;
    min-height: 52px !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 9px !important;
    border: 1px solid rgba(37, 99, 235, 0.18) !important;
    border-radius: 17px !important;
    color: #ffffff !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 16px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    text-decoration: none !important;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.20) !important;
    cursor: pointer !important;
    overflow: hidden !important;
    -webkit-tap-highlight-color: transparent !important;
}

.ografi-feed-page-button--prev {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
    border-color: rgba(100, 116, 139, 0.28) !important;
    box-shadow: 0 10px 22px rgba(71, 85, 105, 0.18) !important;
}

.ografi-feed-page-button--next {
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%) !important;
}

.ografi-feed-page-button::before {
    content: "" !important;
    position: absolute !important;
    inset: 1px !important;
    border-radius: 16px !important;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.20), rgba(255, 255, 255, 0)) !important;
    pointer-events: none !important;
}

.ografi-feed-page-button:hover,
.ografi-feed-page-button:focus,
.ografi-feed-page-button:active {
    color: #ffffff !important;
    text-decoration: none !important;
    outline: none !important;
}

.ografi-feed-page-button--prev:hover,
.ografi-feed-page-button--prev:focus,
.ografi-feed-page-button--prev:active {
    background: linear-gradient(135deg, #475569 0%, #334155 100%) !important;
}

.ografi-feed-page-button--next:hover,
.ografi-feed-page-button--next:focus,
.ografi-feed-page-button--next:active {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
}

.ografi-feed-page-button__icon,
.ografi-feed-page-button span,
.ografi-feed-loadmore__spinner {
    position: relative !important;
    z-index: 1 !important;
}

.ografi-feed-page-button__icon {
    display: inline-flex !important;
    width: 28px !important;
    height: 28px !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 999px !important;
    background: rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    flex: 0 0 auto !important;
}

.ografi-feed-page-button__icon svg {
    width: 17px !important;
    height: 17px !important;
    display: block !important;
}

.ografi-feed-page-button.is-disabled {
    cursor: not-allowed !important;
    opacity: 0.48 !important;
    box-shadow: none !important;
    pointer-events: none !important;
}

.ografi-feed-loadmore__count {
    display: inline-flex !important;
    margin-top: 12px !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 30px !important;
    padding: 0 13px !important;
    border-radius: 999px !important;
    background: rgba(15, 23, 42, 0.05) !important;
    color: #111827 !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
}

.ografi-feed-loadmore__spinner {
    display: none !important;
    width: 24px !important;
    height: 24px !important;
    border: 3px solid rgba(255, 255, 255, 0.38) !important;
    border-top-color: #ffffff !important;
    border-radius: 999px !important;
    animation: ografi-loadmore-spin 0.8s linear infinite !important;
}

.ografi-feed-page-button.is-loading > span:not(.ografi-feed-loadmore__spinner) {
    display: none !important;
}

.ografi-feed-page-button.is-loading .ografi-feed-loadmore__spinner {
    display: inline-block !important;
}

@media (max-width: 640px) {
    .ografi-feed-loadmore {
        margin-top: 20px !important;
        margin-bottom: 32px !important;
        padding-left: 14px !important;
        padding-right: 14px !important;
    }

    .ografi-feed-loadmore__buttons {
        width: 100% !important;
        gap: 9px !important;
    }

    .ografi-feed-page-button {
        min-height: 50px !important;
        border-radius: 16px !important;
        font-size: 15px !important;
    }

    .ografi-feed-page-button__icon {
        width: 27px !important;
        height: 27px !important;
    }
}

html.dark .ografi-feed-loadmore__count,
.dark .ografi-feed-loadmore__count {
    background: rgba(148, 163, 184, 0.12) !important;
    color: #f8fafc !important;
}



/* ========================================================================
   FINAL: SADE KUCUK GERI / SAYAC / ILERI SAYFALANDIRMA
   - Geri butonu beyaz
   - Sayac iki butonun ortasinda
   - Ileri butonu mavi
   - Butonlar ve sayac kucultuldu
   ======================================================================== */
.ografi-feed-loadmore {
    width: 100% !important;
    margin: 16px 0 24px !important;
    padding: 0 10px !important;
    box-sizing: border-box !important;
    text-align: center !important;
}

.ografi-feed-loadmore__buttons {
    display: grid !important;
    grid-template-columns: 1fr auto 1fr !important;
    align-items: center !important;
    gap: 8px !important;
    width: min(100%, 360px) !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

.ografi-feed-page-button {
    position: relative !important;
    display: inline-flex !important;
    min-height: 38px !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    border-radius: 12px !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    text-decoration: none !important;
    box-shadow: none !important;
    cursor: pointer !important;
    overflow: hidden !important;
    -webkit-tap-highlight-color: transparent !important;
}

.ografi-feed-page-button::before {
    display: none !important;
}

.ografi-feed-page-button--prev {
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    color: #111827 !important;
}

.ografi-feed-page-button--next {
    border: 1px solid #2563eb !important;
    background: #2563eb !important;
    color: #ffffff !important;
}

.ografi-feed-page-button--prev:hover,
.ografi-feed-page-button--prev:focus,
.ografi-feed-page-button--prev:active {
    background: #f9fafb !important;
    border-color: #d1d5db !important;
    color: #111827 !important;
    text-decoration: none !important;
    outline: none !important;
}

.ografi-feed-page-button--next:hover,
.ografi-feed-page-button--next:focus,
.ografi-feed-page-button--next:active {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
    text-decoration: none !important;
    outline: none !important;
}

.ografi-feed-page-button__icon {
    display: inline-flex !important;
    width: 20px !important;
    height: 20px !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: currentColor !important;
    flex: 0 0 auto !important;
}

.ografi-feed-page-button__icon svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
}

.ografi-feed-page-button.is-disabled {
    cursor: not-allowed !important;
    opacity: 0.48 !important;
    box-shadow: none !important;
    pointer-events: none !important;
}

.ografi-feed-loadmore__count {
    display: inline-flex !important;
    grid-column: auto !important;
    margin: 0 !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 64px !important;
    min-height: 30px !important;
    padding: 0 10px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 999px !important;
    background: #ffffff !important;
    color: #111827 !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    white-space: nowrap !important;
    box-shadow: none !important;
}

.ografi-feed-loadmore__spinner {
    display: none !important;
    width: 18px !important;
    height: 18px !important;
    border: 2px solid rgba(255, 255, 255, 0.38) !important;
    border-top-color: currentColor !important;
    border-radius: 999px !important;
    animation: ografi-loadmore-spin 0.8s linear infinite !important;
}

.ografi-feed-page-button.is-loading > span:not(.ografi-feed-loadmore__spinner) {
    display: none !important;
}

.ografi-feed-page-button.is-loading .ografi-feed-loadmore__spinner {
    display: inline-block !important;
}

@media (max-width: 640px) {
    .ografi-feed-loadmore {
        margin-top: 14px !important;
        margin-bottom: 22px !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .ografi-feed-loadmore__buttons {
        width: 100% !important;
        max-width: 340px !important;
        gap: 7px !important;
    }

    .ografi-feed-page-button {
        min-height: 36px !important;
        border-radius: 11px !important;
        font-size: 12px !important;
    }

    .ografi-feed-page-button__icon {
        width: 18px !important;
        height: 18px !important;
    }

    .ografi-feed-page-button__icon svg {
        width: 13px !important;
        height: 13px !important;
    }

    .ografi-feed-loadmore__count {
        min-width: 58px !important;
        min-height: 28px !important;
        padding: 0 8px !important;
        font-size: 11px !important;
    }
}

html.dark .ografi-feed-page-button--prev,
.dark .ografi-feed-page-button--prev {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #f8fafc !important;
}

html.dark .ografi-feed-page-button--prev:hover,
.dark .ografi-feed-page-button--prev:hover,
html.dark .ografi-feed-page-button--prev:focus,
.dark .ografi-feed-page-button--prev:focus,
html.dark .ografi-feed-page-button--prev:active,
.dark .ografi-feed-page-button--prev:active {
    background: #1f2937 !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
}

html.dark .ografi-feed-loadmore__count,
.dark .ografi-feed-loadmore__count {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #f8fafc !important;
}



/* ========================================================================
   FINAL FIX: SAYFALANDIRMA DUZENI TAM OLARAK [GERI] [SAYAC] [ILERI]
   ======================================================================== */
.ografi-feed-loadmore {
    width: 100% !important;
    margin: 14px 0 22px !important;
    padding: 0 10px !important;
    box-sizing: border-box !important;
    text-align: center !important;
}

.ografi-feed-loadmore__buttons {
    display: grid !important;
    grid-template-columns: minmax(86px, 1fr) auto minmax(86px, 1fr) !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: min(100%, 340px) !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

.ografi-feed-page-button {
    display: inline-flex !important;
    width: 100% !important;
    min-width: 0 !important;
    min-height: 34px !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    border-radius: 11px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

.ografi-feed-page-button--prev {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    color: #6b7280 !important;
}

.ografi-feed-page-button--next {
    background: #2563eb !important;
    border: 1px solid #2563eb !important;
    color: #ffffff !important;
}

.ografi-feed-page-button--prev:hover,
.ografi-feed-page-button--prev:focus,
.ografi-feed-page-button--prev:active {
    background: #f9fafb !important;
    border-color: #d1d5db !important;
    color: #111827 !important;
    outline: none !important;
}

.ografi-feed-page-button--next:hover,
.ografi-feed-page-button--next:focus,
.ografi-feed-page-button--next:active {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
    outline: none !important;
}

.ografi-feed-page-button::before {
    display: none !important;
}

.ografi-feed-page-button__icon {
    width: 17px !important;
    height: 17px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    color: currentColor !important;
    border-radius: 999px !important;
    flex: 0 0 auto !important;
}

.ografi-feed-page-button__icon svg {
    width: 12px !important;
    height: 12px !important;
    display: block !important;
}

.ografi-feed-page-button.is-disabled {
    opacity: 0.52 !important;
    pointer-events: none !important;
    cursor: not-allowed !important;
}

.ografi-feed-loadmore__count {
    display: inline-flex !important;
    width: auto !important;
    min-width: 58px !important;
    max-width: none !important;
    min-height: 28px !important;
    margin: 0 !important;
    padding: 0 9px !important;
    align-items: center !important;
    justify-content: center !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 999px !important;
    background: #ffffff !important;
    color: #111827 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    white-space: nowrap !important;
    box-shadow: none !important;
}

.ografi-feed-loadmore__spinner {
    display: none !important;
    width: 16px !important;
    height: 16px !important;
    border: 2px solid rgba(255, 255, 255, 0.38) !important;
    border-top-color: currentColor !important;
    border-radius: 999px !important;
}

.ografi-feed-page-button.is-loading > span:not(.ografi-feed-loadmore__spinner) {
    display: none !important;
}

.ografi-feed-page-button.is-loading .ografi-feed-loadmore__spinner {
    display: inline-block !important;
}

@media (max-width: 640px) {
    .ografi-feed-loadmore {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .ografi-feed-loadmore__buttons {
        width: min(100%, 330px) !important;
        grid-template-columns: minmax(82px, 1fr) auto minmax(82px, 1fr) !important;
        gap: 7px !important;
    }
}

html.dark .ografi-feed-page-button--prev,
.dark .ografi-feed-page-button--prev,
html.dark .ografi-feed-loadmore__count,
.dark .ografi-feed-loadmore__count {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #f8fafc !important;
}

.ografi-feed-loadmore {
    margin: 16px 0 28px;
    padding: 0;
}

.ografi-feed-loadmore__buttons {
    display: flex !important;
    justify-content: center !important;
}

.ografi-feed-page-button--prev,
.ografi-feed-loadmore__count,
.ografi-feed-page-button--next > span:not(.ografi-feed-page-button__icon):not(.ografi-feed-loadmore__spinner),
.ografi-feed-loadmore__spinner {
    display: none !important;
}

.ografi-feed-page-button--next {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    padding: 0;
    border-radius: 999px;
    background: #ffffff;
    color: #111111;
    box-shadow: none;
}

.ografi-feed-page-button--next:hover,
.ografi-feed-page-button--next:focus,
.ografi-feed-page-button--next:active,
html.dark .ografi-feed-page-button--next,
.dark .ografi-feed-page-button--next,
html.dark .ografi-feed-page-button--next:hover,
html.dark .ografi-feed-page-button--next:focus,
html.dark .ografi-feed-page-button--next:active,
.dark .ografi-feed-page-button--next:hover,
.dark .ografi-feed-page-button--next:focus,
.dark .ografi-feed-page-button--next:active {
    background: #ffffff;
    color: #111111;
}

.ografi-feed-page-button__icon,
.ografi-feed-page-button__icon svg {
    display: block;
    width: 18px;
    height: 18px;
}

.ografi-feed-page-button--next.is-loading {
    opacity: 0.65;
}

</style>
@endpush

@section('content')
    @if($isCategoryPage)
        <div class="profile-reference-page category-reference-page">
            <div class="profile-reference-shell">
                @include('blog.categories.categories-show', [
                    'categories' => $categories,
                    'activeCategory' => $activeCategorySlug,
                    'category' => $categoryToShow,
                    'categoryPostsCount' => $categoryPostsCount ?? null,
                    'categoryViews' => $categoryViews ?? null,
                    'followersCount' => $followersCount ?? 0,
                    'isCategoryJoined' => $isCategoryJoined ?? false,
                ])

                @php
                    $categoryCurrentSort = request()->query('sort', 'latest');
                    $categoryBaseUrl = !empty($categoryToShow)
                        ? route('blog.category', $categoryToShow)
                        : url()->current();

                    $categoryTabs = [
                        [
                            'label' => 'En sonuncu',
                            'sort' => 'latest',
                        ],
                        [
                            'label' => 'Tepe',
                            'sort' => 'top',
                        ],
                        [
                            'label' => 'Bilgi',
                            'sort' => 'info',
                        ],
                    ];
                @endphp

                <nav class="category-index-tabs" aria-label="Kategori sekmeleri">
                    @foreach($categoryTabs as $categoryTab)
                        @php
                            $tabUrl = $categoryTab['sort'] === 'latest'
                                ? $categoryBaseUrl
                                : $categoryBaseUrl . '?' . http_build_query(array_merge(request()->except('page'), ['sort' => $categoryTab['sort']]));

                            $isActiveTab = $categoryTab['sort'] === 'latest'
                                ? !in_array($categoryCurrentSort, ['top', 'info'], true)
                                : $categoryCurrentSort === $categoryTab['sort'];
                        @endphp

                        <a
                            href="{{ $tabUrl }}"
                            class="category-index-tab"
                            @if($isActiveTab) aria-current="page" @endif
                        >
                            {{ $categoryTab['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="category-index-feed__content profile-reference-content" data-category-post-panel>
                    @forelse($posts as $post)
                        @php
                            $featured = $post->featured_image_url
                                ?? $post->featured_image
                                ?? $post->cover_image
                                ?? null;

                            $reactionTypesAll = $reactionTypes ?? ($post->reactionTypes ?? collect());

                            $typeMap = collect($reactionTypesAll)->mapWithKeys(function ($type) {
                                $id = $type['id'] ?? ($type->id ?? null);

                                return $id ? [$id => [
                                    'id' => $id,
                                    'short_code' => $type['short_code'] ?? ($type->short_code ?? null),
                                    'emoji' => $type['emoji'] ?? ($type->emoji ?? null),
                                    'gif_url' => $type['gif_url'] ?? ($type->gif_url ?? null),
                                    'label' => $type['label'] ?? ($type->label ?? null),
                                ]] : [];
                            });

                            $reactionCounts = collect($post->reaction_counts ?? [])->mapWithKeys(fn ($cnt, $typeId) => [$typeId => $cnt]);

                            if ($reactionCounts->isEmpty() && method_exists($post, 'reactions')) {
                                $reactionCounts = $post->reactions()
                                    ->whereNotNull('reaction_type_id')
                                    ->selectRaw('reaction_type_id, count(*) as count')
                                    ->groupBy('reaction_type_id')
                                    ->pluck('count', 'reaction_type_id');
                            }

                            $reactionPills = $reactionCounts->map(function ($count, $typeId) use ($typeMap) {
                                $type = $typeMap->get($typeId);

                                if (!$type) {
                                    return null;
                                }

                                $icon = $type['emoji'] ?? $type['gif_url'] ?? null;

                                return [
                                    'type_id' => $type['id'] ?? $typeId,
                                    'count' => (int) $count,
                                    'icon' => $icon,
                                    'emoji' => $type['emoji'] ?? null,
                                    'gif_url' => $type['gif_url'] ?? null,
                                    'label' => $type['label'] ?? null,
                                    'short_code' => $type['short_code'] ?? null,
                                ];
                            })->filter()->values();
                        @endphp

                        <div class="profile-post-card-wrapper">
                            @include('blog.post-card', [
                                'post' => $post,
                                'title' => filled($post->title) ? $post->title : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
                                'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
                                'featuredImage' => $featured,
                                'createdAt' => $post->published_at,
                                'authorName' => optional($post->author)->name ?? 'Topluluk',
                                'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
                                'reactions' => $reactionPills,
                                'reactionTypes' => $reactionTypesAll,
                                'isHero' => $loop->first,
                            ])
                        </div>

                        @include('partials.ads.feed-breaks', [
                            'iteration' => $loop->iteration,
                            'isLast' => $loop->last,
                        ])
                    @empty
                        <div class="profile-reference-empty">
                            Henuz yazi bulunamadi.
                        </div>
                    @endforelse

                    @if($postsCanPaginate && ($postsHasPreviousPage || $posts->hasMorePages()))
                        <div class="ografi-feed-loadmore">
                            <div class="ografi-feed-loadmore__buttons">
                                @if($postsHasPreviousPage && $postsPreviousPageUrl)
                                    <a
                                        href="{{ $postsPreviousPageUrl }}"
                                        class="ografi-feed-page-button ografi-feed-page-button--prev"
                                        rel="prev"
                                        data-load-more-button
                                    >
                                        <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M15 5 8 12l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span>Geri</span>
                                        <span class="ografi-feed-loadmore__spinner" aria-hidden="true"></span>
                                    </a>
                                @else
                                    <span class="ografi-feed-page-button ografi-feed-page-button--prev is-disabled" aria-disabled="true">
                                        <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M15 5 8 12l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span>Geri</span>
                                    </span>
                                @endif

                                <span class="ografi-feed-loadmore__count">
                                    {{ $loadedCount }} / {{ $totalCount }}
                                </span>

                                @if($posts->hasMorePages() && $postsNextPageUrl)
                                    <a
                                        href="{{ $postsNextPageUrl }}"
                                        class="ografi-feed-page-button ografi-feed-page-button--next"
                                        rel="next"
                                        data-load-more-button
                                    >
                                        <span>İleri</span>
                                        <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="ografi-feed-loadmore__spinner" aria-hidden="true"></span>
                                    </a>
                                @else
                                    <span class="ografi-feed-page-button ografi-feed-page-button--next is-disabled" aria-disabled="true">
                                        <span>İleri</span>
                                        <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        @if($isTagPage)
            <h1 class="tag-page-identity">#{{ $activeTagName }}</h1>
        @endif

        @include('partials.ads.slot', [
            'slotKey' => 'ads_feed_top',
            'wrapperClass' => 'mb-4',
        ])

        @forelse($posts as $post)
            @php
                $featured = $post->featured_image_url
                    ?? $post->featured_image
                    ?? $post->cover_image
                    ?? null;

                $reactionTypesAll = $reactionTypes ?? ($post->reactionTypes ?? collect());

                $typeMap = collect($reactionTypesAll)->mapWithKeys(function ($type) {
                    $id = $type['id'] ?? ($type->id ?? null);

                    return $id ? [$id => [
                        'id' => $id,
                        'short_code' => $type['short_code'] ?? ($type->short_code ?? null),
                        'emoji' => $type['emoji'] ?? ($type->emoji ?? null),
                        'gif_url' => $type['gif_url'] ?? ($type->gif_url ?? null),
                        'label' => $type['label'] ?? ($type->label ?? null),
                    ]] : [];
                });

                $reactionCounts = collect($post->reaction_counts ?? [])->mapWithKeys(fn ($cnt, $typeId) => [$typeId => $cnt]);

                if ($reactionCounts->isEmpty() && method_exists($post, 'reactions')) {
                    $reactionCounts = $post->reactions()
                        ->whereNotNull('reaction_type_id')
                        ->selectRaw('reaction_type_id, count(*) as count')
                        ->groupBy('reaction_type_id')
                        ->pluck('count', 'reaction_type_id');
                }

                $reactionPills = $reactionCounts->map(function ($count, $typeId) use ($typeMap) {
                    $type = $typeMap->get($typeId);

                    if (!$type) {
                        return null;
                    }

                    $icon = $type['emoji'] ?? $type['gif_url'] ?? null;

                    return [
                        'type_id' => $type['id'] ?? $typeId,
                        'count' => (int) $count,
                        'icon' => $icon,
                        'emoji' => $type['emoji'] ?? null,
                        'gif_url' => $type['gif_url'] ?? null,
                        'label' => $type['label'] ?? null,
                        'short_code' => $type['short_code'] ?? null,
                    ];
                })->filter()->values();
            @endphp

            @include('blog.post-card', [
                'post' => $post,
                'title' => filled($post->title) ? $post->title : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
                'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
                'featuredImage' => $featured,
                'createdAt' => $post->published_at,
                'authorName' => optional($post->author)->name ?? 'Topluluk',
                'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
                'reactions' => $reactionPills,
                'reactionTypes' => $reactionTypesAll,
                'isHero' => $isTagPage ? false : $loop->first,
            ])

            @include('partials.ads.feed-breaks', [
                'iteration' => $loop->iteration,
                'isLast' => $loop->last,
            ])
        @empty
            <div class="rounded-xl bg-card-light p-6 text-center text-sm text-muted-light shadow-sm dark:bg-card-dark dark:text-muted-dark">
                Henuz yazi bulunamadi.
            </div>
        @endforelse

        @if($postsCanPaginate && ($postsHasPreviousPage || $posts->hasMorePages()))
            <div class="ografi-feed-loadmore">
                <div class="ografi-feed-loadmore__buttons">
                    @if($postsHasPreviousPage && $postsPreviousPageUrl)
                        <a
                            href="{{ $postsPreviousPageUrl }}"
                            class="ografi-feed-page-button ografi-feed-page-button--prev"
                            rel="prev"
                            data-load-more-button
                        >
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M15 5 8 12l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>Geri</span>
                            <span class="ografi-feed-loadmore__spinner" aria-hidden="true"></span>
                        </a>
                    @else
                        <span class="ografi-feed-page-button ografi-feed-page-button--prev is-disabled" aria-disabled="true">
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M15 5 8 12l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>Geri</span>
                        </span>
                    @endif

                    <span class="ografi-feed-loadmore__count">
                        {{ $loadedCount }} / {{ $totalCount }}
                    </span>

                    @if($posts->hasMorePages() && $postsNextPageUrl)
                        <a
                            href="{{ $postsNextPageUrl }}"
                            class="ografi-feed-page-button ografi-feed-page-button--next"
                            rel="next"
                            data-load-more-button
                        >
                            <span>İleri</span>
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="ografi-feed-loadmore__spinner" aria-hidden="true"></span>
                        </a>
                    @else
                        <span class="ografi-feed-page-button ografi-feed-page-button--next is-disabled" aria-disabled="true">
                            <span>İleri</span>
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                <path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <script>
        document.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-load-more-button]');

            if (!button || button.getAttribute('rel') !== 'next') {
                return;
            }

            event.preventDefault();

            if (button.classList.contains('is-loading')) {
                return;
            }

            const controls = button.closest('.ografi-feed-loadmore');
            const parent = controls?.parentElement;
            const url = button.getAttribute('href');

            if (!controls || !parent || !url) {
                window.location.href = button.href;
                return;
            }

            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    window.location.href = button.href;
                    return;
                }

                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const currentKeys = new Set(Array.from(document.querySelectorAll('[data-post-card-shell]')).map(function (card) {
                    return card.id || card.getAttribute('data-post-url') || '';
                }).filter(Boolean));

                const incoming = Array.from(doc.querySelectorAll('[data-post-card-shell]'))
                    .map(function (card) {
                        return card.closest('.profile-post-card-wrapper') || card;
                    })
                    .filter(function (node) {
                        const card = node.matches('[data-post-card-shell]') ? node : node.querySelector('[data-post-card-shell]');
                        const key = card?.id || card?.getAttribute('data-post-url') || '';
                        return !key || !currentKeys.has(key);
                    });

                incoming.forEach(function (node) {
                    parent.insertBefore(node, controls);
                });

                const nextControls = Array.from(doc.querySelectorAll('.ografi-feed-loadmore')).find(function (node) {
                    return node.querySelector('[data-load-more-button][rel="next"]');
                });

                if (nextControls) {
                    controls.replaceWith(nextControls);
                } else {
                    controls.remove();
                }
            } catch (error) {
                window.location.href = button.href;
            }
        });
    </script>
@endsection
