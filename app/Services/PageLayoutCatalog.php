<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Route;

final class PageLayoutCatalog
{
    /**
     * These selectors point at named, deliberately supported Blade sections.
     * Only these server-defined sections can be moved from the page editor.
     */
    public static function definitions(): array
    {
        return [
            'home' => [
                'label' => 'Ana sayfa',
                'route' => 'home',
                'container' => '[data-ografi-layout-container="home"]',
                'blocks' => [
                    'feed_filters' => ['label' => 'Akis filtreleri', 'selector' => '[data-ografi-layout-section="feed_filters"]'],
                    'read_filters' => ['label' => 'Okuma filtreleri', 'selector' => '[data-ografi-layout-section="read_filters"]'],
                    'post_feed' => ['label' => 'Gonderi akisi', 'selector' => '[data-ografi-layout-section="post_feed"]'],
                ],
            ],
            'discover' => [
                'label' => 'Kesfet',
                'route' => 'discover',
                'container' => '[data-ografi-layout-container="discover"]',
                'blocks' => [
                    'discover_search' => ['label' => 'Arama kutusu', 'selector' => '[data-ografi-layout-section="discover_search"]'],
                    'discover_ads_top' => ['label' => 'Ust reklam alani', 'selector' => '[data-ografi-layout-section="discover_ads_top"]'],
                    'discover_users' => ['label' => 'Onerilen kullanicilar', 'selector' => '[data-ografi-layout-section="discover_users"]'],
                    'discover_communities' => ['label' => 'Topluluklar', 'selector' => '[data-ografi-layout-section="discover_communities"]'],
                    'discover_posts' => ['label' => 'Onerilen gonderiler', 'selector' => '[data-ografi-layout-section="discover_posts"]'],
                ],
            ],
            'categories' => [
                'label' => 'Kategoriler',
                'route' => 'blog.categories',
                'container' => '[data-ografi-layout-container="categories"]',
                'blocks' => [
                    'categories_header' => ['label' => 'Kategori basligi ve filtreler', 'selector' => '[data-ografi-layout-section="categories_header"]'],
                    'categories_list' => ['label' => 'Kategori listesi', 'selector' => '[data-ografi-layout-section="categories_list"]'],
                    'categories_count' => ['label' => 'Kategori sayaci', 'selector' => '[data-ografi-layout-section="categories_count"]'],
                    'categories_empty' => ['label' => 'Bos kategori durumu', 'selector' => '[data-ografi-layout-section="categories_empty"]'],
                ],
            ],
            'tags' => [
                'label' => 'Etiketler',
                'route' => 'blog.tags',
                'container' => '[data-ografi-layout-container="tags"]',
                'blocks' => [
                    'tags_title' => ['label' => 'Sayfa basligi ve araclari', 'selector' => '[data-ografi-layout-section="tags_title"]'],
                    'tags_title_spacer' => ['label' => 'Baslik boslugu', 'selector' => '[data-ografi-layout-section="tags_title_spacer"]'],
                    'tags_search' => ['label' => 'Etiket arama paneli', 'selector' => '[data-ografi-layout-section="tags_search"]'],
                    'tags_search_spacer' => ['label' => 'Arama boslugu', 'selector' => '[data-ografi-layout-section="tags_search_spacer"]'],
                    'tags_list' => ['label' => 'Etiket listesi ve daha fazla yukle', 'selector' => '[data-ografi-layout-section="tags_list"]'],
                ],
            ],
            'blog_index' => [
                'label' => 'Gonderi listesi',
                'route' => 'blog.index',
                'container' => 'main.layout-main',
                'blocks' => [
                    'blog_ads_top' => ['label' => 'Ust reklam alani', 'selector' => '[data-ografi-layout-section="blog_ads_top"]'],
                    'blog_posts' => ['label' => 'Gonderi listesi ve sayfalama', 'selector' => '[data-ografi-layout-section="blog_posts"]'],
                ],
            ],
            'tag_feed' => [
                'label' => 'Tek etiket akis sayfasi',
                'route' => 'blog.index',
                'container' => 'main.layout-main',
                'blocks' => [
                    'tag_title' => ['label' => 'Etiket basligi', 'selector' => '[data-ografi-layout-section="tag_title"]'],
                    'blog_ads_top' => ['label' => 'Ust reklam alani', 'selector' => '[data-ografi-layout-section="blog_ads_top"]'],
                    'blog_posts' => ['label' => 'Gonderi listesi ve sayfalama', 'selector' => '[data-ografi-layout-section="blog_posts"]'],
                ],
            ],
            'category_show' => [
                'label' => 'Topluluk / kategori detay sayfasi',
                'route' => 'blog.category',
                'container' => '.profile-reference-shell',
                'blocks' => [
                    'category_header' => ['label' => 'Topluluk kapagi ve kimligi', 'selector' => '[data-ografi-layout-section="category_header"]'],
                    'category_tabs' => ['label' => 'Kategori sekmeleri', 'selector' => '[data-ografi-layout-section="category_tabs"]'],
                    'category_content' => ['label' => 'Gonderiler veya kategori bilgileri', 'selector' => '[data-ografi-layout-section="category_content"]'],
                ],
            ],
            'post_show' => [
                'label' => 'Gonderi detayi',
                'route' => 'blog.post',
                'container' => '[data-ografi-layout-container="post_show"]',
                'blocks' => [
                    'post_ad_top' => ['label' => 'Gonderi ust reklam alani', 'selector' => '[data-ografi-layout-section="post_ad_top"]'],
                    'post_content' => ['label' => 'Gonderi icerigi', 'selector' => '[data-ografi-layout-section="post_content"]'],
                    'post_ad_bottom' => ['label' => 'Yorumlar ustu reklam alani', 'selector' => '[data-ografi-layout-section="post_ad_bottom"]'],
                    'comments' => ['label' => 'Yorumlar ve yanitlar', 'selector' => '[data-ografi-layout-section="comments"]'],
                ],
            ],
            'users_index' => [
                'label' => 'Kullanicilar',
                'route' => 'users.index',
                'container' => '[data-ografi-layout-container="users_index"]',
                'blocks' => [
                    'users_title' => ['label' => 'Sayfa basligi ve araclar', 'selector' => '[data-ografi-layout-section="users_title"]'],
                    'users_list' => ['label' => 'Kullanici listesi', 'selector' => '[data-ografi-layout-section="users_list"]'],
                ],
            ],
            'messages_index' => [
                'label' => 'Mesajlar',
                'route' => 'messages.index',
                'container' => '[data-ografi-layout-container="messages_index"]',
                'blocks' => [
                    'messages_header' => ['label' => 'Mesajlar basligi ve ayarlari', 'selector' => '[data-ografi-layout-section="messages_header"]'],
                    'messages_threads' => ['label' => 'Sohbet listesi ve paneli', 'selector' => '[data-ografi-layout-section="messages_threads"]'],
                ],
            ],
            'notifications' => [
                'label' => 'Bildirimler',
                'route' => 'notifications.index',
                'container' => '[data-ografi-layout-container="notifications"]',
                'blocks' => [
                    'notifications_header' => ['label' => 'Bildirim basligi ve islemler', 'selector' => '[data-ografi-layout-section="notifications_header"]'],
                    'notifications_list' => ['label' => 'Bildirim listesi', 'selector' => '[data-ografi-layout-section="notifications_list"]'],
                ],
            ],
            'bookmarks' => [
                'label' => 'Kaydedilenler',
                'route' => 'blog.bookmarks',
                'container' => '[data-ografi-layout-container="bookmarks"]',
                'blocks' => [
                    'bookmarks_header' => ['label' => 'Sayfa basligi', 'selector' => '[data-ografi-layout-section="bookmarks_header"]'],
                    'bookmarks_posts' => ['label' => 'Kaydedilen gonderiler', 'selector' => '[data-ografi-layout-section="bookmarks_posts"]'],
                ],
            ],
            'drafts' => [
                'label' => 'Taslaklar',
                'route' => 'blog.drafts',
                'container' => '[data-ografi-layout-container="drafts"]',
                'blocks' => [
                    'drafts_header' => ['label' => 'Taslaklar basligi ve araclar', 'selector' => '[data-ografi-layout-section="drafts_header"]'],
                    'drafts_posts' => ['label' => 'Taslak gonderiler', 'selector' => '[data-ografi-layout-section="drafts_posts"]'],
                ],
            ],
            'faq' => [
                'label' => 'SSS',
                'route' => 'pages.sss',
                'container' => '[data-ografi-layout-container="faq"]',
                'blocks' => [
                    'faq_title' => ['label' => 'SSS basligi ve araclar', 'selector' => '[data-ografi-layout-section="faq_title"]'],
                    'faq_title_spacer' => ['label' => 'Baslik boslugu', 'selector' => '[data-ografi-layout-section="faq_title_spacer"]'],
                    'faq_search' => ['label' => 'SSS arama paneli', 'selector' => '[data-ografi-layout-section="faq_search"]'],
                    'faq_search_spacer' => ['label' => 'Arama boslugu', 'selector' => '[data-ografi-layout-section="faq_search_spacer"]'],
                    'faq_list' => ['label' => 'Soru ve cevap listesi', 'selector' => '[data-ografi-layout-section="faq_list"]'],
                ],
            ],
            'profile' => [
                'label' => 'Kullanici profili',
                'route' => 'users.show',
                'container' => '[data-ografi-layout-container="profile"]',
                'blocks' => [
                    'profile_header' => ['label' => 'Kapak, kimlik ve biyografi', 'selector' => '[data-ografi-layout-section="profile_header"]'],
                    'profile_tabs' => ['label' => 'Profil sekmeleri', 'selector' => '[data-ografi-layout-section="profile_tabs"]'],
                    'profile_content' => ['label' => 'Gonderiler, yorumlar ve listeler', 'selector' => '[data-ografi-layout-section="profile_content"]'],
                ],
            ],
        ];
    }

    public static function pageOptions(): array
    {
        $options = [];

        foreach (self::definitions() as $key => $definition) {
            $options[$key] = $definition['label'];
        }

        return $options;
    }

    public static function definition(string $pageKey): ?array
    {
        return self::definitions()[$pageKey] ?? null;
    }

    public static function currentPageKey(): ?string
    {
        $route = request()->route();

        if (!$route) {
            return null;
        }

        return match ($route->getName()) {
            'home' => 'home',
            'discover' => 'discover',
            'blog.categories' => 'categories',
            'blog.tags' => 'tags',
            'blog.category' => 'category_show',
            'blog.index', 'blog.posts' => request()->filled('tag')
                ? 'tag_feed'
                : (request()->filled('category') ? 'category_show' : 'blog_index'),
            'blog.post' => 'post_show',
            'users.show' => 'profile',
            'users.index' => 'users_index',
            'messages.index' => 'messages_index',
            'notifications.index' => 'notifications',
            'blog.bookmarks' => 'bookmarks',
            'blog.drafts' => 'drafts',
            'pages.sss' => 'faq',
            default => null,
        };
    }

    public static function normalizeLayout(string $pageKey, mixed $value): array
    {
        $definition = self::definition($pageKey);

        if (!$definition) {
            return ['order' => [], 'hidden' => [], 'custom_blocks' => []];
        }

        $input = is_array($value) ? $value : [];
        $customBlocks = self::normalizeCustomBlocks($input['custom_blocks'] ?? []);
        $allowed = [
            ...array_keys($definition['blocks']),
            ...array_column($customBlocks, 'id'),
        ];
        $orderInput = is_array($input['order'] ?? null) ? $input['order'] : [];
        $hiddenInput = is_array($input['hidden'] ?? null) ? $input['hidden'] : [];

        $order = [];
        foreach ($orderInput as $id) {
            if (is_string($id) && in_array($id, $allowed, true) && !in_array($id, $order, true)) {
                $order[] = $id;
            }
        }

        foreach ($allowed as $id) {
            if (!in_array($id, $order, true)) {
                $order[] = $id;
            }
        }

        $hidden = [];
        foreach ($hiddenInput as $id) {
            if (is_string($id) && in_array($id, $allowed, true) && !in_array($id, $hidden, true)) {
                $hidden[] = $id;
            }
        }

        return [
            'order' => $order,
            'hidden' => $hidden,
            'custom_blocks' => $customBlocks,
        ];
    }

    public static function normalizeCustomBlocks(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $allowedTypes = ['heading', 'text', 'image', 'button', 'callout', 'divider', 'spacer'];
        $blocks = [];
        $seen = [];

        foreach (array_slice($value, 0, 40) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = trim((string) ($item['id'] ?? ''));
            if (!preg_match('/^custom_[a-zA-Z0-9_-]{8,64}$/', $id) || in_array($id, $seen, true)) {
                continue;
            }

            $type = (string) ($item['type'] ?? 'text');
            if (!in_array($type, $allowedTypes, true)) {
                continue;
            }

            $title = self::plainText($item['title'] ?? '', 140);
            $text = self::plainText($item['text'] ?? '', 4000);
            $label = self::plainText($item['button_text'] ?? '', 100);
            $alt = self::plainText($item['alt'] ?? '', 180);
            $align = (string) ($item['align'] ?? 'left');
            if (!in_array($align, ['left', 'center', 'right'], true)) {
                $align = 'left';
            }

            $blocks[] = [
                'id' => $id,
                'type' => $type,
                'label' => $title !== '' ? $title : self::typeLabel($type),
                'title' => $title,
                'text' => $text,
                'image_url' => self::safeBuilderUrl($item['image_url'] ?? ''),
                'alt' => $alt,
                'url' => self::safeBuilderUrl($item['url'] ?? ''),
                'button_text' => $label !== '' ? $label : 'Detaylari gor',
                'align' => $align,
                'background' => self::safeHexColor($item['background'] ?? '', '#ffffff'),
                'text_color' => self::safeHexColor($item['text_color'] ?? '', '#111827'),
                'padding' => self::boundedInt($item['padding'] ?? 16, 0, 64, 16),
                'height' => self::boundedInt($item['height'] ?? 32, 8, 240, 32),
                'max_width' => self::boundedInt($item['max_width'] ?? 960, 240, 1600, 960),
            ];
            $seen[] = $id;
        }

        return $blocks;
    }

    public static function defaultCustomBlock(string $id, string $type): array
    {
        $allowedTypes = ['heading', 'text', 'image', 'button', 'callout', 'divider', 'spacer'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'text';
        }

        return self::normalizeCustomBlocks([[
            'id' => $id,
            'type' => $type,
            'title' => match ($type) {
                'heading' => 'Yeni baslik',
                'callout' => 'Vurgulu alan',
                default => '',
            },
            'text' => match ($type) {
                'text' => 'Metnini buraya yaz.',
                'callout' => 'Aciklamani buraya yaz.',
                default => '',
            },
            'button_text' => 'Detaylari gor',
            'align' => 'left',
            'background' => '#ffffff',
            'text_color' => '#111827',
            'padding' => 16,
            'height' => 32,
            'max_width' => 960,
        ]])[0] ?? [
            'id' => $id,
            'type' => 'text',
            'label' => 'Metin blogu',
            'title' => '',
            'text' => '',
            'image_url' => '',
            'alt' => '',
            'url' => '',
            'button_text' => 'Detaylari gor',
            'align' => 'left',
            'background' => '#ffffff',
            'text_color' => '#111827',
            'padding' => 16,
            'height' => 32,
            'max_width' => 960,
        ];
    }

    private static function typeLabel(string $type): string
    {
        return match ($type) {
            'heading' => 'Baslik',
            'text' => 'Metin blogu',
            'image' => 'Gorsel',
            'button' => 'Buton',
            'callout' => 'Vurgulu alan',
            'divider' => 'Ayirici',
            'spacer' => 'Bosluk',
            default => 'Icerik blogu',
        };
    }

    private static function plainText(mixed $value, int $maxLength): string
    {
        $text = is_scalar($value) ? (string) $value : '';
        $text = trim(strip_tags($text));
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? $text;

        return mb_substr($text, 0, $maxLength);
    }

    private static function safeBuilderUrl(mixed $value): string
    {
        $url = trim(is_scalar($value) ? (string) $value : '');

        if ($url === '' || str_starts_with($url, '#')) {
            return $url;
        }

        // Backslashes and control/space characters can be normalized by browsers
        // into URL separators, so reject them before accepting relative or HTTPS URLs.
        if (preg_match('/[\x00-\x20\\\\]/', $url) === 1) {
            return '';
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        return strtolower((string) $scheme) === 'https' && is_string($host) && $host !== ''
            ? $url
            : '';
    }

    private static function safeHexColor(mixed $value, string $fallback): string
    {
        $color = trim(is_scalar($value) ? (string) $value : '');

        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1 ? strtolower($color) : $fallback;
    }

    private static function boundedInt(mixed $value, int $min, int $max, int $fallback): int
    {
        $number = is_numeric($value) ? (int) $value : $fallback;

        return max($min, min($max, $number));
    }

    public static function normalizeAll(mixed $layouts): array
    {
        if (!is_array($layouts)) {
            return [];
        }

        $normalized = [];

        foreach (array_keys(self::definitions()) as $pageKey) {
            if (array_key_exists($pageKey, $layouts)) {
                $normalized[$pageKey] = self::normalizeLayout($pageKey, $layouts[$pageKey]);
            }
        }

        return $normalized;
    }

    public static function layoutUrl(string $pageKey, bool $preview = false): string
    {
        $definition = self::definition($pageKey);

        if (!$definition) {
            return route('home');
        }

        $url = match ($pageKey) {
            'home' => route('home'),
            'discover' => route('discover'),
            'categories' => route('blog.categories'),
            'tags' => route('blog.tags'),
            'blog_index' => route('blog.index'),
            'tag_feed' => self::tagPreviewUrl(),
            'category_show' => self::categoryPreviewUrl(),
            'post_show' => self::postPreviewUrl(),
            'profile' => self::profilePreviewUrl(),
            'users_index' => route('users.index'),
            'messages_index' => route('messages.index'),
            'notifications' => route('notifications.index'),
            'bookmarks' => route('blog.bookmarks'),
            'drafts' => route('blog.drafts'),
            'faq' => route('pages.sss'),
            default => route('home'),
        };

        if (!$preview) {
            return $url;
        }

        $query = [
            'ografi_layout_preview' => '1',
            'ografi_layout_page' => $pageKey,
        ];

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    }

    public static function publicConfig(string $pageKey, mixed $storedLayouts, bool $preview): ?array
    {
        $definition = self::definition($pageKey);

        if (!$definition) {
            return null;
        }

        $storedLayouts = is_array($storedLayouts) ? $storedLayouts : [];
        $hasSavedLayout = array_key_exists($pageKey, $storedLayouts);
        $isAuthorizedPreview = $preview
            && request()->boolean('ografi_layout_preview')
            && request()->query('ografi_layout_page') === $pageKey
            && (auth()->user()?->isAdmin() ?? false);

        if (!$hasSavedLayout && !$isAuthorizedPreview) {
            return null;
        }

        $layout = self::normalizeLayout($pageKey, $storedLayouts[$pageKey] ?? []);

        return [
            'pageKey' => $pageKey,
            'label' => $definition['label'],
            'containerSelector' => $definition['container'],
            'blocks' => [
                ...collect($definition['blocks'])
                    ->map(fn (array $block, string $id) => ['id' => $id, 'custom' => false, ...$block])
                    ->values()
                    ->all(),
                ...collect($layout['custom_blocks'])
                    ->map(fn (array $block) => [
                        'id' => $block['id'],
                        'label' => $block['label'],
                        'custom' => true,
                        'selector' => null,
                        'content' => $block,
                    ])
                    ->values()
                    ->all(),
            ],
            'customBlocks' => $layout['custom_blocks'],
            'layout' => $layout,
            'editorPreview' => $isAuthorizedPreview,
        ];
    }

    private static function tagPreviewUrl(): string
    {
        $slug = Tag::query()->orderBy('name')->value('slug');

        return filled($slug)
            ? route('blog.index', ['tag' => $slug])
            : route('blog.index');
    }

    private static function categoryPreviewUrl(): string
    {
        $slug = Category::query()->orderBy('name')->value('slug');

        return filled($slug)
            ? route('blog.category', ['category' => $slug])
            : route('blog.categories');
    }

    private static function postPreviewUrl(): string
    {
        $slug = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->value('slug');

        return $slug && Route::has('blog.post')
            ? route('blog.post', ['post' => $slug])
            : route('home');
    }

    private static function profilePreviewUrl(): string
    {
        $username = User::query()
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->orderByDesc('id')
            ->value('username');

        return $username && Route::has('users.show')
            ? route('users.show', ['user' => $username])
            : route('home');
    }
}
