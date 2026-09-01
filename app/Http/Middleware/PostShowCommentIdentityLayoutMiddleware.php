<?php

namespace App\Http\Middleware;

use App\Models\RecaptchaSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostShowCommentIdentityLayoutMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '' || ! str_contains($html, 'ogx-comments-panel')) {
            return $response;
        }

        // Remove the legacy bright-blue reply focus ring from the rendered source.
        $html = str_replace(
            "  .ogx-reply-compose:focus-within {\n    border-color: #2563eb !important;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10) !important;\n  }",
            "  .ogx-reply-compose:focus-within {\n    border-color: #d7dbe0 !important;\n    box-shadow: none !important;\n    outline: 0 !important;\n  }",
            $html
        );

        /*
         * Reply/edit textareas had two independent auto-grow implementations:
         * 1) inline oninput on the textarea
         * 2) the shared ogxGrowTextarea input listener
         * Both repeatedly wrote height:auto/scrollHeight while a CSS height
         * transition was active. Chromium can repaint the glyphs on top of
         * themselves during selection/input. Strip the inline implementation
         * and keep the shared grow handler as the single source of truth.
         */
        $html = preg_replace_callback(
            '/<textarea\b(?=[^>]*\bdata-ogx-autogrow\b)[^>]*>/i',
            static function (array $match): string {
                $tag = $match[0];
                $tag = preg_replace('/\s+style="[^"]*"/i', '', $tag) ?? $tag;
                $tag = preg_replace('/\s+oninput="[^"]*"/i', '', $tag) ?? $tag;

                if (! str_contains($tag, 'data-ografi-reply-textarea')) {
                    $tag = substr($tag, 0, -1) . ' data-ografi-reply-textarea>';
                }

                return $tag;
            },
            $html
        ) ?? $html;

        $recaptchaSettings = RecaptchaSetting::currentOrNull();
        $commentRecaptchaEnabled = $recaptchaSettings?->isEnabledFor('comment') ?? false;
        $commentRecaptchaSiteKey = $commentRecaptchaEnabled
            ? trim((string) $recaptchaSettings?->resolvedSiteKey())
            : '';

        if ($commentRecaptchaEnabled && $commentRecaptchaSiteKey !== '') {
            $escapedSiteKey = htmlspecialchars($commentRecaptchaSiteKey, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html = preg_replace_callback(
                '/<form\b(?=[^>]*(?:id="show-comment-form"|class="[^"]*\bogx-reply-form\b[^"]*"))[^>]*>/i',
                static function (array $match) use ($escapedSiteKey): string {
                    $tag = $match[0];

                    if (! str_contains($tag, 'data-recaptcha-v3')) {
                        $tag = substr($tag, 0, -1)
                            . ' data-recaptcha-v3 data-recaptcha-action="comment" data-recaptcha-site-key="'
                            . $escapedSiteKey
                            . '">';
                    }

                    return $tag . "\n" . '<input type="hidden" name="recaptcha_token" value="">';
                },
                $html
            ) ?? $html;
        }

        $recaptchaAssets = '';
        if ($commentRecaptchaEnabled && $commentRecaptchaSiteKey !== '') {
            $encodedSiteKey = json_encode($commentRecaptchaSiteKey, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $siteKeyQuery = rawurlencode($commentRecaptchaSiteKey);

            $recaptchaAssets = <<<HTML
<script data-ografi-comment-recaptcha="v2" src="https://www.google.com/recaptcha/api.js?render={$siteKeyQuery}" async defer></script>
<script data-ografi-comment-recaptcha="v2">
(() => {
    const siteKey = {$encodedSiteKey};

    const waitForRecaptcha = (timeout = 8000) => new Promise((resolve, reject) => {
        const started = Date.now();
        const timer = window.setInterval(() => {
            if (window.grecaptcha && typeof window.grecaptcha.execute === 'function') {
                window.clearInterval(timer);
                resolve(window.grecaptcha);
                return;
            }

            if (Date.now() - started >= timeout) {
                window.clearInterval(timer);
                reject(new Error('recaptcha_timeout'));
            }
        }, 50);
    });

    const showError = (form, message) => {
        let error = form.querySelector('[data-comment-recaptcha-error]');
        if (!error) {
            error = document.createElement('p');
            error.setAttribute('data-comment-recaptcha-error', '');
            error.style.margin = '8px 0 0';
            error.style.fontSize = '13px';
            error.style.lineHeight = '18px';
            error.style.color = '#dc2626';
            form.appendChild(error);
        }
        error.textContent = message;
    };

    const clearError = (form) => {
        form.querySelector('[data-comment-recaptcha-error]')?.remove();
    };

    document.addEventListener('input', (event) => {
        const textarea = event.target;
        if (!(textarea instanceof HTMLTextAreaElement) || textarea.id !== 'show-comment-input') return;

        const form = textarea.closest('#show-comment-form');
        const submit = form?.querySelector('[data-ogx-submit-comment]');
        if (!form || !(submit instanceof HTMLButtonElement)) return;

        const ready = textarea.value.trim().length > 0
            || !!form.querySelector('[data-ogx-preview]:not([hidden]), [data-gif-preview]:not([hidden])');

        form.classList.toggle('has-comment-ready', ready);
        submit.disabled = !ready;
        submit.setAttribute('aria-disabled', ready ? 'false' : 'true');
    }, true);

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.matches('[data-recaptcha-v3][data-recaptcha-action="comment"]')) return;

        const input = form.querySelector('input[name="recaptcha_token"]');
        if (!(input instanceof HTMLInputElement)) return;

        if (form.dataset.ografiRecaptchaBusy === '1') {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        form.dataset.ografiRecaptchaBusy = '1';
        clearError(form);

        try {
            const grecaptcha = await waitForRecaptcha();
            const token = await new Promise((resolve, reject) => {
                grecaptcha.ready(() => {
                    grecaptcha.execute(siteKey, { action: 'comment' }).then(resolve).catch(reject);
                });
            });

            if (!token) throw new Error('recaptcha_empty_token');

            input.value = token;
            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            form.dataset.ografiRecaptchaBusy = '0';
            showError(form, 'Güvenlik doğrulaması tamamlanamadı. Sayfayı yenileyip tekrar deneyin.');
        }
    }, true);
})();
</script>
HTML;
        }

        $assets = <<<'HTML'
<span data-ografi-comment-ui-fix="v5" hidden></span>
<style data-ografi-comment-ui-fix="v5">
/* Comment identity row */
html body .ogx-comments-panel [data-ogx-comment].ogx-comment {
    display: block !important;
    grid-template-columns: none !important;
    column-gap: 0 !important;
}

html body .ogx-comments-panel [data-ogx-comment] > .ogx-comment-main {
    width: 100% !important;
    min-width: 0 !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 5px !important;
    min-height: 30px !important;
    margin: 0 !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-avatar {
    display: inline-grid !important;
    place-items: center !important;
    width: 28px !important;
    min-width: 28px !important;
    max-width: 28px !important;
    height: 28px !important;
    min-height: 28px !important;
    max-height: 28px !important;
    margin: 0 3px 0 0 !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    flex: 0 0 28px !important;
    font-size: 10px !important;
    line-height: 1 !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-avatar img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-username {
    display: inline-flex !important;
    align-items: center !important;
    min-width: 0 !important;
    margin: 0 !important;
    line-height: 28px !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > [role="img"] {
    display: inline-flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    align-items: center !important;
    justify-content: center !important;
    width: 16px !important;
    min-width: 16px !important;
    max-width: 16px !important;
    height: 16px !important;
    min-height: 16px !important;
    max-height: 16px !important;
    margin: 0 !important;
    flex: 0 0 16px !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > [role="img"] :is(svg, img, span) {
    display: block !important;
    width: 16px !important;
    height: 16px !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-author-label {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 20px !important;
    margin: 0 !important;
    padding: 0 5px !important;
    color: #2563eb !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    line-height: 20px !important;
    white-space: nowrap !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-submeta,
html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-text,
html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-actions,
html body .ogx-comments-panel [data-ogx-comment] .ogx-edit-form,
html body .ogx-comments-panel [data-ogx-comment] .ogx-reply-form,
html body .ogx-comments-panel [data-ogx-comment] > .ogx-comment-main > .ogx-replies {
    margin-left: 36px !important;
}

/* Menus share the same typography and icon scale. */
html body .ogx-comments-panel .ogx-filter-item,
html body .ogx-comments-panel .ogx-comment-menu button,
html body .ogx-comments-panel .ogx-comment-menu a {
    min-height: 36px !important;
    padding: 8px 10px !important;
    gap: 8px !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 20px !important;
}

html body .ogx-comments-panel .ogx-filter-item {
    justify-content: flex-start !important;
}

html body .ogx-comments-panel .ogx-filter-item::after {
    margin-left: auto !important;
}

html body .ogx-comments-panel .ogx-filter-item > iconify-icon,
html body .ogx-comments-panel .ogx-comment-menu-icon {
    display: inline-flex !important;
    width: 16px !important;
    min-width: 16px !important;
    height: 16px !important;
    font-size: 16px !important;
    flex: 0 0 16px !important;
}

/* Votes */
html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible) {
    background: #dcfce7 !important;
    color: #16a34a !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğen"]:active {
    background: #bbf7d0 !important;
    color: #15803d !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible) {
    background: #fee2e2 !important;
    color: #ef4444 !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğenme"]:active {
    background: #fecaca !important;
    color: #dc2626 !important;
}

/* Reply/edit composer: one sizing engine, stable glyph rendering. */
html body .ogx-comments-panel .ogx-reply-form .ogx-reply-compose,
html body .ogx-comments-panel .ogx-edit-form .ogx-reply-compose,
html body .ogx-comments-panel .ogx-reply-form .ogx-reply-compose:focus-within,
html body .ogx-comments-panel .ogx-edit-form .ogx-reply-compose:focus-within {
    border: 1px solid #d7dbe0 !important;
    background: #ffffff !important;
    box-shadow: none !important;
    outline: 0 !important;
}

html body .ogx-comments-panel textarea[data-ografi-reply-textarea] {
    position: static !important;
    display: block !important;
    flex: 1 1 100% !important;
    width: 100% !important;
    min-width: 0 !important;
    height: auto !important;
    min-height: 36px !important;
    max-height: 420px !important;
    margin: 0 !important;
    padding: 2px 0 !important;
    box-sizing: border-box !important;
    border: 0 !important;
    outline: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    -webkit-text-fill-color: #111827 !important;
    box-shadow: none !important;
    text-shadow: none !important;
    font-family: inherit !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    font-style: normal !important;
    line-height: 20px !important;
    letter-spacing: normal !important;
    text-align: left !important;
    text-indent: 0 !important;
    direction: ltr !important;
    writing-mode: horizontal-tb !important;
    white-space: pre-wrap !important;
    overflow-wrap: anywhere !important;
    overflow-y: hidden !important;
    resize: none !important;
    transform: none !important;
    transition: none !important;
    animation: none !important;
    caret-color: #111827 !important;
    text-rendering: auto !important;
    -webkit-font-smoothing: antialiased;
}

html body .ogx-comments-panel textarea[data-ografi-reply-textarea]:focus,
html body .ogx-comments-panel textarea[data-ografi-reply-textarea]:focus-visible {
    outline: 0 !important;
    border: 0 !important;
    box-shadow: none !important;
}

html body .ogx-comments-panel textarea[data-ografi-reply-textarea]::placeholder {
    color: #71717a !important;
    -webkit-text-fill-color: #71717a !important;
    opacity: 1 !important;
}

html.dark body .ogx-comments-panel .ogx-reply-form .ogx-reply-compose,
html.dark body .ogx-comments-panel .ogx-edit-form .ogx-reply-compose,
html.dark body .ogx-comments-panel .ogx-reply-form .ogx-reply-compose:focus-within,
html.dark body .ogx-comments-panel .ogx-edit-form .ogx-reply-compose:focus-within {
    border-color: #3f3f46 !important;
    background: #18181b !important;
}

html.dark body .ogx-comments-panel textarea[data-ografi-reply-textarea] {
    color: #f4f4f5 !important;
    -webkit-text-fill-color: #f4f4f5 !important;
    caret-color: #f4f4f5 !important;
}

html.dark body .ogx-comments-panel textarea[data-ografi-reply-textarea]::placeholder {
    color: #a1a1aa !important;
    -webkit-text-fill-color: #a1a1aa !important;
}

html.dark body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible) {
    background: rgba(34, 197, 94, .16) !important;
    color: #4ade80 !important;
}

html.dark body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible) {
    background: rgba(239, 68, 68, .16) !important;
    color: #f87171 !important;
}

@media (max-width: 640px) {
    html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-avatar {
        width: 26px !important;
        min-width: 26px !important;
        max-width: 26px !important;
        height: 26px !important;
        min-height: 26px !important;
        max-height: 26px !important;
        flex-basis: 26px !important;
    }

    html body .ogx-comments-panel [data-ogx-comment] .ogx-submeta,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-text,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-actions,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-edit-form,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-reply-form,
    html body .ogx-comments-panel [data-ogx-comment] > .ogx-comment-main > .ogx-replies {
        margin-left: 34px !important;
    }
}
</style>
<script data-ografi-comment-ui-fix="v5">
(() => {
    const directChild = (parent, className) => {
        if (!parent) return null;
        return Array.from(parent.children).find((child) => child.classList?.contains(className)) || null;
    };

    const cleanOwnershipUi = (comment, main) => {
        if (comment.getAttribute('data-ogx-mine') === '1') return;

        const actions = directChild(main, 'ogx-comment-actions');
        const menu = actions?.querySelector('[data-comment-more-menu]');

        if (menu) {
            menu.querySelectorAll('[data-comment-edit-toggle]').forEach((node) => node.remove());
            menu.querySelectorAll('form').forEach((form) => {
                const method = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
                if (method === 'DELETE') form.remove();
            });
        }

        directChild(main, 'ogx-edit-form')?.remove();
    };

    const decorateComment = (comment) => {
        if (!(comment instanceof Element)) return;

        const main = directChild(comment, 'ogx-comment-main');
        const meta = directChild(main, 'ogx-meta');
        if (!main || !meta) return;

        const avatar = Array.from(comment.children).find((child) => child.classList?.contains('ogx-avatar')) || null;
        const username = meta.querySelector('.ogx-username');

        if (avatar && avatar.parentElement !== meta) {
            if (username) meta.insertBefore(avatar, username);
            else meta.prepend(avatar);
        }

        const submeta = directChild(main, 'ogx-submeta');
        if (submeta) {
            Array.from(submeta.children)
                .filter((child) => child.classList?.contains('ogx-author-label'))
                .forEach((badge) => meta.appendChild(badge));
        }

        cleanOwnershipUi(comment, main);
    };

    const decorateFilterMenu = (root = document) => {
        const icons = {
            popular: 'lucide:flame',
            new: 'lucide:clock-3',
        };

        root.querySelectorAll?.('.ogx-comments-panel [data-ogx-sort]').forEach((item) => {
            if (item.querySelector(':scope > iconify-icon[data-comment-filter-icon]')) return;

            const icon = document.createElement('iconify-icon');
            icon.setAttribute('icon', icons[item.getAttribute('data-ogx-sort')] || 'lucide:list-filter');
            icon.setAttribute('data-comment-filter-icon', '');
            icon.setAttribute('aria-hidden', 'true');
            item.prepend(icon);
        });
    };

    const apply = (root = document) => {
        root.querySelectorAll?.('.ogx-comments-panel [data-ogx-comment]').forEach(decorateComment);
        if (root.matches?.('.ogx-comments-panel [data-ogx-comment]')) decorateComment(root);
        decorateFilterMenu(root);
    };

    const boot = () => {
        apply(document);

        const panel = document.querySelector('.ogx-comments-panel');
        if (!panel || !('MutationObserver' in window)) return;

        new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node instanceof Element) apply(node);
                });
            });
        }).observe(panel, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
</script>
HTML;

        $html = preg_replace('/<\/body>/i', $recaptchaAssets . "\n" . $assets . "\n</body>", $html, 1) ?? ($html . $recaptchaAssets . $assets);
        $response->setContent($html);
        $response->headers->set('X-Ografi-Comment-UI', 'v5');

        return $response;
    }
}
