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

        if ($request->routeIs('blog.create')) {
            $preferencesScript = $this->preferencesScript($post);
            $html = preg_replace('/<\/body>/i', $preferencesScript . "\n</body>", $html, 1) ?? ($html . $preferencesScript);
        } elseif ($request->routeIs('blog.post.edit')) {
            // Edit sayfasi create composer'in birebir HTML'ini kullaniyor. Edit verilerini
            // form kapandigi anda hydrate ederek EditorJS ve sayfa scriptlerinden ONCE
            // mevcut post degerlerinin DOM'a yerlesmesini garanti ediyoruz. Eski akista
            // script </body> oncesinde calistigi icin EditorJS bos verilerle baslayabiliyordu.
            $preferencesScript = $this->preferencesScript($post);
            $injected = preg_replace_callback(
                '/<\/form>/i',
                static fn () => "</form>\n" . $preferencesScript,
                $html,
                1
            );
            $html = is_string($injected) ? $injected : ($html . $preferencesScript);
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

    private function preferencesScript(?Post $post): string
    {
        $states = json_encode([
            'followers_only' => (bool) ($post?->followers_only ?? false),
            'noindex' => (bool) ($post?->noindex ?? false),
            'is_ai_product' => (bool) ($post?->is_ai_product ?? false),
            'hide_from_feeds' => (bool) ($post?->hide_from_feeds ?? false),
            'suppress_follower_notifications' => (bool) ($post?->suppress_follower_notifications ?? false),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';

        $editPayload = 'null';
        if ($post) {
            $post->loadMissing('tags');
            $editPayload = json_encode([
                'action' => route('blog.post.update', $post),
                'title' => (string) ($post->title ?? ''),
                'category_id' => $post->category_id,
                'tags' => $post->tags->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'content' => (string) ($post->content ?? ''),
                'content_json' => is_array($post->content_json) ? $post->content_json : null,
                'excerpt' => (string) ($post->excerpt ?? ''),
                'meta_title' => (string) ($post->meta_title ?? ''),
                'meta_description' => (string) ($post->meta_description ?? ''),
                'slug' => (string) ($post->slug ?? ''),
                'meta_keywords' => (string) ($post->meta_keywords ?? ''),
                'published_at' => $post->published_at?->format('Y-m-d\\TH:i'),
                'image_license_url' => (string) ($post->image_license_url ?? ''),
                'image_acquire_url' => (string) ($post->image_acquire_url ?? ''),
                'image_credit_text' => (string) ($post->image_credit_text ?? ''),
                'image_creator_name' => (string) ($post->image_creator_name ?? ''),
                'image_copyright_notice' => (string) ($post->image_copyright_notice ?? ''),
                'is_published' => (bool) $post->is_published,
                'comments_disabled' => (bool) $post->comments_disabled,
                'is_nsfw' => (bool) $post->is_nsfw,
                'is_pinned' => (bool) $post->is_pinned,
                'featured_image_url' => (string) ($post->featured_image_url ?? ''),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null';
        }

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
    const edit = {$editPayload};
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

    const setValue = (id, value) => {
        const field = document.getElementById(id);
        if (!field || value === undefined || value === null) return;
        field.value = String(value);
    };

    const applyEditComposer = () => {
        if (!edit) return true;

        const form = document.getElementById('post-create-form');
        if (!form) return false;

        form.action = edit.action;
        form.dataset.editMode = '1';
        form.dataset.serverDraftBound = '1';
        form.dataset.autosaveBound = '1';

        let method = form.querySelector('input[name="_method"]');
        if (!method) {
            method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            form.prepend(method);
        }
        method.value = 'PUT';

        setValue('title', edit.title || '');
        setValue('category_id', edit.category_id || '');
        setValue('content', edit.content || '');
        setValue('content_json', edit.content_json ? JSON.stringify(edit.content_json) : '');
        setValue('excerpt', edit.excerpt || '');
        setValue('meta_title', edit.meta_title || '');
        setValue('meta_description', edit.meta_description || '');
        setValue('slug', edit.slug || '');
        setValue('meta_keywords', edit.meta_keywords || '');
        setValue('published_at', edit.published_at || '');
        setValue('image_license_url', edit.image_license_url || '');
        setValue('image_acquire_url', edit.image_acquire_url || '');
        setValue('image_credit_text', edit.image_credit_text || '');
        setValue('image_creator_name', edit.image_creator_name || '');
        setValue('image_copyright_notice', edit.image_copyright_notice || '');
        setValue('is_published', edit.is_published ? '1' : '0');

        ['comments_disabled', 'is_nsfw', 'is_pinned'].forEach((name) => {
            const checkbox = form.querySelector(`input[type="checkbox"][name="\${name}"]`);
            if (checkbox) checkbox.checked = Boolean(edit[name]);
        });

        const selectedTags = new Set((edit.tags || []).map((id) => String(id)));
        form.querySelectorAll('input[type="checkbox"][name="tags[]"]').forEach((checkbox) => {
            checkbox.checked = selectedTags.has(String(checkbox.value));
        });

        const activeCategory = form.querySelector(`[data-category-option][data-value="\${String(edit.category_id || '')}"]`);
        const categoryLabel = document.querySelector('[data-category-label]');
        if (activeCategory && categoryLabel) {
            categoryLabel.textContent = activeCategory.getAttribute('data-label') || categoryLabel.textContent;
        }

        if (edit.featured_image_url) {
            const coverField = document.querySelector('[data-cover-field]');
            const coverImage = document.querySelector('[data-cover-preview-img]');
            if (coverField && coverImage) {
                coverImage.src = edit.featured_image_url;
                coverField.classList.add('has-image');
            }
        }

        const heading = document.querySelector('.create-page-fixed header .truncate.text-sm.font-semibold.text-slate-950');
        if (heading) heading.textContent = 'Gönderiyi düzenle';

        document.querySelectorAll('[data-submit-intent="publish"]').forEach((button) => {
            const label = button.querySelector('span');
            if (label) {
                label.textContent = 'Güncelle';
            } else if (!button.querySelector('iconify-icon')) {
                button.textContent = 'Güncelle';
            }
        });

        return true;
    };

    const mount = () => {
        const titles = Array.from(document.querySelectorAll('#settings-modal .settings-accordion-title'));
        const title = titles.find((node) => String(node.textContent || '').trim() === 'Tercihler');
        const details = title?.closest('details.settings-accordion');
        const content = details?.querySelector('.settings-accordion-content');
        const list = content?.querySelector('.divide-y');
        if (!list || list.dataset.distributionReady === '1') return Boolean(list);

        options.forEach(([name, label]) => {
            if (list.querySelector(`[name="\${name}"]`)) return;
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-4 px-3 py-3';
            row.innerHTML = `<span class="text-sm text-slate-800">\${label}</span>\${switchMarkup(name, Boolean(states[name]))}`;
            list.appendChild(row);
        });

        list.dataset.distributionReady = '1';
        return true;
    };

    const repairEditSettingsSave = () => {
        if (!edit) return;
        const saveButton = document.querySelector('[data-settings-save]');
        const form = document.getElementById('post-create-form');
        if (!saveButton || !form || saveButton.dataset.editSubmitBound === '1') return;

        const replacement = saveButton.cloneNode(true);
        replacement.disabled = false;
        replacement.textContent = 'Kaydet';
        replacement.dataset.editSubmitBound = '1';
        replacement.addEventListener('click', () => form.requestSubmit());
        saveButton.replaceWith(replacement);
    };

    const boot = () => {
        applyEditComposer();

        if (!mount()) {
            let attempts = 0;
            const timer = window.setInterval(() => {
                attempts += 1;
                if (mount() || attempts > 40) window.clearInterval(timer);
            }, 100);
        }

        if (edit) {
            window.setTimeout(repairEditSettingsSave, 120);
            window.setTimeout(repairEditSettingsSave, 400);
        }
    };

    applyEditComposer();

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
