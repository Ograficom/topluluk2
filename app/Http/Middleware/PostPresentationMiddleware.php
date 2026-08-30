<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostPresentationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $post = $this->resolveRoutePost($request);
        $shouldNoindex = $post && ((bool) $post->noindex || (bool) $post->followers_only);

        if ($shouldNoindex) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '') {
            return $response;
        }

        if ($shouldNoindex && ! preg_match('/<meta\s+name=["\']robots["\']/i', $html)) {
            $html = preg_replace(
                '/<\/head>/i',
                "    <meta name=\"robots\" content=\"noindex,nofollow,noarchive\">\n</head>",
                $html,
                1
            ) ?? $html;
        }

        if ($request->routeIs('blog.post.edit') && $post) {
            // edit.blade.php, create.blade.php'nin birebir kopyasidir. Burada formu
            // TARAYICIYA GONDERILMEDEN ONCE update moduna ceviriyoruz. Boylece
            // EditorJS ilk kez baslarken mevcut post verilerini dogrudan DOM'dan okur;
            // sonradan JS ile alan doldurma/race-condition yoktur.
            $html = $this->prepareEditComposerHtml($html, $request, $post);
        }

        if ($request->routeIs('blog.create', 'blog.post.edit')) {
            $preferencesScript = $this->preferencesScript($request, $post);
            $html = preg_replace('/<\/body>/i', $preferencesScript . "\n</body>", $html, 1) ?? ($html . $preferencesScript);
        }

        $aiIds = [];
        if (preg_match_all('/data-post-id=["\'](\d+)["\']/i', $html, $matches)) {
            $ids = array_values(array_unique(array_map('intval', $matches[1] ?? [])));
            if ($ids !== []) {
                $aiIds = Post::withoutGlobalScopes()
                    ->whereIn('id', $ids)
                    ->where('is_ai_product', true)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            }
        }

        $currentAi = (bool) ($post?->is_ai_product ?? false);
        if ($aiIds !== [] || $currentAi) {
            $script = $this->badgeScript($aiIds, $currentAi);
            $html = preg_replace('/<\/body>/i', $script . "\n</body>", $html, 1) ?? ($html . $script);
        }

        $response->setContent($html);

        return $response;
    }

    private function resolveRoutePost(Request $request): ?Post
    {
        $routePost = $request->route('post');
        if ($routePost instanceof Post) {
            return $routePost;
        }

        $slug = trim((string) $routePost);
        if ($slug === '') {
            return null;
        }

        return Post::withoutGlobalScopes()->where('slug', $slug)->first();
    }

    private function prepareEditComposerHtml(string $html, Request $request, Post $post): string
    {
        $post->loadMissing('tags');

        $contentJson = is_array($post->content_json)
            ? (json_encode($post->content_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
            : (string) ($post->content_json ?? '');

        $field = static function (Request $request, string $key, mixed $fallback): mixed {
            return $request->session()->hasOldInput($key)
                ? $request->old($key)
                : $fallback;
        };

        $categoryId = $field($request, 'category_id', $post->category_id);
        $selectedTags = collect($field($request, 'tags', $post->tags->pluck('id')->all()))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $html = preg_replace_callback(
            '/<form\b[^>]*\bid="post-create-form"[^>]*>/i',
            function (array $matches) use ($post): string {
                $tag = $matches[0];
                $action = htmlspecialchars(route('blog.post.update', $post), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                if (preg_match('/\baction="[^"]*"/i', $tag)) {
                    $tag = preg_replace('/\baction="[^"]*"/i', 'action="' . $action . '"', $tag, 1) ?? $tag;
                } else {
                    $tag = substr($tag, 0, -1) . ' action="' . $action . '">';
                }

                if (! str_contains($tag, 'data-edit-mode=')) {
                    $tag = substr($tag, 0, -1) . ' data-edit-mode="1">';
                }

                return $tag . "\n                    <input type=\"hidden\" name=\"_method\" value=\"PUT\">";
            },
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/(<div class="truncate text-sm font-semibold text-slate-950">)Yeni gönderi(<\/div>)/u',
            '$1Gönderiyi düzenle$2',
            $html,
            1
        ) ?? $html;

        $html = $this->setInputValueById($html, 'is_published', $field($request, 'is_published', $post->is_published ? '1' : '0'));
        $html = $this->setInputValueById($html, 'category_id', $categoryId ?? '');
        $html = $this->setInputValueById($html, 'content_json', $field($request, 'content_json', $contentJson));
        $html = $this->setInputValueById($html, 'new_tags', $field($request, 'new_tags', ''));
        $html = $this->setInputValueById($html, 'meta_title', $field($request, 'meta_title', $post->meta_title ?? ''));
        $html = $this->setInputValueById($html, 'slug', $field($request, 'slug', $post->slug ?? ''));
        $html = $this->setInputValueById($html, 'published_at', $field($request, 'published_at', $post->published_at?->format('Y-m-d\TH:i') ?? ''));
        $html = $this->setInputValueById($html, 'image_license_url', $field($request, 'image_license_url', $post->image_license_url ?? ''));
        $html = $this->setInputValueById($html, 'image_acquire_url', $field($request, 'image_acquire_url', $post->image_acquire_url ?? ''));
        $html = $this->setInputValueById($html, 'image_credit_text', $field($request, 'image_credit_text', $post->image_credit_text ?? ''));
        $html = $this->setInputValueById($html, 'image_creator_name', $field($request, 'image_creator_name', $post->image_creator_name ?? ''));
        $html = $this->setInputValueById($html, 'image_copyright_notice', $field($request, 'image_copyright_notice', $post->image_copyright_notice ?? ''));

        $html = $this->setTextareaValueById($html, 'title', $field($request, 'title', $post->title ?? ''));
        $html = $this->setTextareaValueById($html, 'content', $field($request, 'content', $post->content ?? ''));
        $html = $this->setTextareaValueById($html, 'excerpt', $field($request, 'excerpt', $post->excerpt ?? ''));
        $html = $this->setTextareaValueById($html, 'meta_description', $field($request, 'meta_description', $post->meta_description ?? ''));
        $html = $this->setTextareaValueById($html, 'meta_keywords', $field($request, 'meta_keywords', $post->meta_keywords ?? ''));

        $html = $this->setCheckboxByName($html, 'comments_disabled', (bool) $field($request, 'comments_disabled', $post->comments_disabled));
        $html = $this->setCheckboxByName($html, 'is_nsfw', (bool) $field($request, 'is_nsfw', $post->is_nsfw));
        $html = $this->setCheckboxByName($html, 'is_pinned', (bool) $field($request, 'is_pinned', $post->is_pinned));
        $html = $this->setTagCheckboxes($html, $selectedTags);

        $featuredImageUrl = trim((string) ($post->featured_image_url ?? ''));
        if ($featuredImageUrl !== '') {
            $html = preg_replace(
                '/class="create-cover"\s+data-cover-field/i',
                'class="create-cover has-image" data-cover-field',
                $html,
                1
            ) ?? $html;

            $escapedImageUrl = htmlspecialchars($featuredImageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html = preg_replace_callback(
                '/<img\b[^>]*\bdata-cover-preview-img\b[^>]*>/i',
                static function (array $matches) use ($escapedImageUrl): string {
                    $tag = $matches[0];
                    if (preg_match('/\bsrc="[^"]*"/i', $tag)) {
                        return preg_replace('/\bsrc="[^"]*"/i', 'src="' . $escapedImageUrl . '"', $tag, 1) ?? $tag;
                    }

                    return substr($tag, 0, -1) . ' src="' . $escapedImageUrl . '">';
                },
                $html,
                1
            ) ?? $html;
        }

        $html = preg_replace_callback(
            '/(<button\b[^>]*\bdata-submit-intent="publish"[^>]*>)(.*?)(<\/button>)/is',
            static function (array $matches): string {
                $inner = $matches[2];
                if (preg_match('/<span\b/i', $inner)) {
                    $inner = preg_replace('/(<span\b[^>]*>).*?(<\/span>)/is', '$1Güncelle$2', $inner, 1) ?? $inner;
                } else {
                    $inner = 'Güncelle';
                }

                return $matches[1] . $inner . $matches[3];
            },
            $html
        ) ?? $html;

        return $html;
    }

    private function setInputValueById(string $html, string $id, mixed $value): string
    {
        $escaped = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $quotedId = preg_quote($id, '/');

        return preg_replace_callback(
            '/<input\b[^>]*\bid="' . $quotedId . '"[^>]*>/i',
            static function (array $matches) use ($escaped): string {
                $tag = $matches[0];
                if (preg_match('/\bvalue="[^"]*"/i', $tag)) {
                    return preg_replace('/\bvalue="[^"]*"/i', 'value="' . $escaped . '"', $tag, 1) ?? $tag;
                }

                return substr($tag, 0, -1) . ' value="' . $escaped . '">';
            },
            $html,
            1
        ) ?? $html;
    }

    private function setTextareaValueById(string $html, string $id, mixed $value): string
    {
        $escaped = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $quotedId = preg_quote($id, '/');

        return preg_replace_callback(
            '/(<textarea\b[^>]*\bid="' . $quotedId . '"[^>]*>).*?(<\/textarea>)/is',
            static fn (array $matches): string => $matches[1] . $escaped . $matches[2],
            $html,
            1
        ) ?? $html;
    }

    private function setCheckboxByName(string $html, string $name, bool $checked): string
    {
        $quotedName = preg_quote($name, '/');

        return preg_replace_callback(
            '/<input\b[^>]*\bname="' . $quotedName . '"[^>]*>/i',
            static function (array $matches) use ($checked): string {
                $tag = $matches[0];
                if (! preg_match('/\btype="checkbox"/i', $tag)) {
                    return $tag;
                }

                $tag = preg_replace('/\schecked(?:="checked")?/i', '', $tag) ?? $tag;
                if ($checked) {
                    $tag = substr($tag, 0, -1) . ' checked>';
                }

                return $tag;
            },
            $html
        ) ?? $html;
    }

    private function setTagCheckboxes(string $html, array $selectedTags): string
    {
        $selected = array_fill_keys(array_map('strval', $selectedTags), true);

        return preg_replace_callback(
            '/<input\b[^>]*\bname="tags\[\]"[^>]*>/i',
            static function (array $matches) use ($selected): string {
                $tag = $matches[0];
                $tag = preg_replace('/\schecked(?:="checked")?/i', '', $tag) ?? $tag;

                if (preg_match('/\bvalue="([^"]+)"/i', $tag, $valueMatch) && isset($selected[(string) $valueMatch[1]])) {
                    $tag = substr($tag, 0, -1) . ' checked>';
                }

                return $tag;
            },
            $html
        ) ?? $html;
    }

    private function preferencesScript(Request $request, ?Post $post): string
    {
        $oldOrPost = static function (Request $request, string $key, bool $fallback): bool {
            return $request->session()->hasOldInput($key)
                ? (bool) $request->old($key)
                : $fallback;
        };

        $states = json_encode([
            'followers_only' => $oldOrPost($request, 'followers_only', (bool) ($post?->followers_only ?? false)),
            'noindex' => $oldOrPost($request, 'noindex', (bool) ($post?->noindex ?? false)),
            'is_ai_product' => $oldOrPost($request, 'is_ai_product', (bool) ($post?->is_ai_product ?? false)),
            'hide_from_feeds' => $oldOrPost($request, 'hide_from_feeds', (bool) ($post?->hide_from_feeds ?? false)),
            'suppress_follower_notifications' => $oldOrPost($request, 'suppress_follower_notifications', (bool) ($post?->suppress_follower_notifications ?? false)),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';

        return <<<HTML
<style data-ografi-preference-interactions>
#settings-modal .settings-panel > .border-t button[data-settings-close] {
    cursor: pointer !important;
}

#settings-modal .settings-panel > .border-t button[data-settings-close]:hover,
#settings-modal .settings-panel > .border-t button[data-settings-close]:active {
    background: #f3f4f6 !important;
}

#settings-modal input[role="switch"]:not(:checked) + span {
    background: #e2e8f0 !important;
    border-color: #cbd5e1 !important;
}

#settings-modal label.group:hover input[role="switch"]:not(:checked) + span,
#settings-modal label.group:active input[role="switch"]:not(:checked) + span {
    background: #cbd5e1 !important;
    border-color: #cbd5e1 !important;
}

#settings-modal input[role="switch"]:checked + span {
    background: #2563eb !important;
    border-color: #2563eb !important;
}

@keyframes ografiAiRgbFlow {
    0% { background-position: 0 0, 0% 0%; }
    100% { background-position: 0 0, 300% 0%; }
}

button[data-ai-assist] {
    position: fixed !important;
    right: 18px !important;
    bottom: 18px !important;
    top: auto !important;
    left: auto !important;
    z-index: 100200 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    width: 52px !important;
    min-width: 52px !important;
    max-width: 52px !important;
    height: 32px !important;
    min-height: 32px !important;
    padding: 0 8px !important;
    border: 2px solid transparent !important;
    border-radius: 999px !important;
    background:
        linear-gradient(#0a0a0c, #0a0a0c) padding-box,
        linear-gradient(90deg, #ff0000, #ff7f00, #ffff00, #00ff00, #00ffff, #0000ff, #8b00ff, #ff0000) border-box !important;
    background-size: 100% 100%, 300% 100% !important;
    color: #ffffff !important;
    box-shadow:
        0 0 9px rgba(0, 255, 255, .22),
        0 0 14px rgba(139, 0, 255, .18) !important;
    cursor: pointer !important;
    transform: none !important;
    animation: ografiAiRgbFlow 7s linear infinite !important;
    transition: transform .16s ease, box-shadow .16s ease !important;
}

button[data-ai-assist]::after {
    content: 'AI';
    display: inline-block;
    color: #ffffff;
    font-size: 10px;
    line-height: 1;
    font-weight: 700;
    letter-spacing: .04em;
    white-space: nowrap;
}

button[data-ai-assist] iconify-icon[data-ai-assist-icon] {
    display: inline-flex !important;
    width: 14px !important;
    height: 14px !important;
    font-size: 14px !important;
    color: #ffffff !important;
    filter: drop-shadow(0 0 4px rgba(255, 255, 255, .75));
}

button[data-ai-assist]:hover {
    transform: scale(1.05) !important;
    box-shadow:
        0 0 12px rgba(0, 255, 255, .34),
        0 0 18px rgba(139, 0, 255, .28) !important;
}

button[data-ai-assist]:active {
    transform: scale(.96) !important;
}

button[data-ai-assist]:disabled {
    cursor: wait !important;
    opacity: .78 !important;
}

@media (max-width: 640px) {
    button[data-ai-assist] {
        right: 12px !important;
        bottom: 12px !important;
        width: 48px !important;
        min-width: 48px !important;
        max-width: 48px !important;
        height: 30px !important;
        min-height: 30px !important;
        padding: 0 7px !important;
    }

    button[data-ai-assist]::after {
        font-size: 9px;
    }

    button[data-ai-assist] iconify-icon[data-ai-assist-icon] {
        width: 13px !important;
        height: 13px !important;
        font-size: 13px !important;
    }
}
</style>
<script data-ografi-post-distribution-settings>
(() => {
    const states = {$states};
    const options = [
        ['followers_only', 'Sadece takipçiler'],
        ['noindex', 'Arama motorlarından sakla'],
        ['is_ai_product', 'Bu bir yapay zeka ürünü'],
        ['hide_from_feeds', 'Akıştan uzak tut'],
        ['suppress_follower_notifications', 'Takipçilere bildirme'],
    ];

    const switchMarkup = (name, checked) => `
        <label class="group relative inline-flex cursor-pointer items-center">
            <input type="hidden" name="\${name}" value="0">
            <input type="checkbox" name="\${name}" id="\${name}" value="1" role="switch" class="peer sr-only" \${checked ? 'checked' : ''}>
            <span class="relative h-7 w-12 rounded-full border border-slate-300 bg-slate-200 transition-all duration-200 group-hover:bg-slate-300 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-500/15 peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:group-hover:bg-blue-600" aria-hidden="true"></span>
            <span class="pointer-events-none absolute left-[3px] top-[3px] h-5 w-5 rounded-full bg-white shadow-[0_2px_8px_rgba(15,23,42,0.18)] transition-all duration-200 peer-checked:translate-x-5" aria-hidden="true"></span>
        </label>`;

    const mount = () => {
        const titles = Array.from(document.querySelectorAll('#settings-modal .settings-accordion-title'));
        const title = titles.find((node) => String(node.textContent || '').trim() === 'Tercihler');
        const details = title?.closest('details.settings-accordion');
        const content = details?.querySelector('.settings-accordion-content');
        const list = content?.querySelector('.divide-y');

        // Akordiyon donusumu henuz yapilmadiysa create blade'deki Tercihler
        // kartini dogrudan bul. Boylece create/edit iki sayfada da ayni ayarlar calisir.
        let targetList = list;
        if (!targetList) {
            const preferenceHeading = Array.from(document.querySelectorAll('#settings-modal .text-sm.font-semibold.text-slate-950'))
                .find((node) => String(node.textContent || '').trim() === 'Tercihler');
            targetList = preferenceHeading?.closest('section')?.querySelector('.divide-y') || null;
        }

        if (!targetList || targetList.dataset.distributionReady === '1') return Boolean(targetList);

        options.forEach(([name, label]) => {
            if (targetList.querySelector(`[name="\${name}"]`)) return;
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-4 px-3 py-3';
            row.innerHTML = `<span class="text-sm text-slate-800">\${label}</span>\${switchMarkup(name, Boolean(states[name]))}`;
            targetList.appendChild(row);
        });

        targetList.dataset.distributionReady = '1';
        return true;
    };

    const boot = () => {
        if (!mount()) {
            let attempts = 0;
            const timer = window.setInterval(() => {
                attempts += 1;
                if (mount() || attempts > 40) window.clearInterval(timer);
            }, 100);
        }
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
    else boot();
})();
</script>
HTML;
    }

    private function badgeScript(array $aiIds, bool $currentAi): string
    {
        $ids = json_encode(array_values($aiIds), JSON_UNESCAPED_SLASHES) ?: '[]';
        $current = $currentAi ? 'true' : 'false';

        return <<<HTML
<script data-ografi-ai-product-badges>
(() => {
    const ids = {$ids};
    const currentAi = {$current};
    const makeBadge = () => {
        const badge = document.createElement('span');
        badge.setAttribute('data-ai-product-badge', '');
        badge.textContent = 'Yapay zeka ürünü';
        badge.style.cssText = 'display:inline-flex;align-items:center;width:max-content;margin:0 0 8px;padding:4px 8px;border:1px solid #dbe2ea;border-radius:999px;background:#f8fafc;color:#475569;font-size:11px;line-height:1.2;font-weight:600;';
        return badge;
    };
    const place = (root) => {
        if (!root || root.querySelector('[data-ai-product-badge]')) return;
        const target = root.querySelector('.alma-post-card__title,.post-card__title,.blog-post-card__title,.entry-title,h1,h2,h3');
        const badge = makeBadge();
        if (target?.parentElement) target.parentElement.insertBefore(badge, target);
        else root.prepend(badge);
    };
    const run = () => {
        ids.forEach((id) => document.querySelectorAll(`[data-post-id="\${id}"]`).forEach(place));
        if (currentAi) {
            const root = document.querySelector('article') || document.querySelector('main') || document.body;
            place(root);
        }
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run, { once: true });
    else run();
})();
</script>
HTML;
    }
}
