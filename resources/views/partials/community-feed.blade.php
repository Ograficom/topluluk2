<div class="site-card alma-feed-promo" data-community-pulse>
    <div class="alma-feed-promo__dismiss-row">
        <button
            type="button"
            class="alma-feed-promo__dismiss"
            data-community-pulse-close
            aria-label="Community pulse kutusunu kapat"
        >
            <iconify-icon icon="lucide:x"></iconify-icon>
        </button>
    </div>

    <div class="flex flex-col gap-4 text-left sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="alma-feed-promo__eyebrow">{{ __('site.community_feed.pulse') }}</div>
            <div class="alma-feed-promo__copy">
                {{ __('site.home.promo_text') }}
            </div>
        </div>

        <a href="{{ route('discover') }}" class="alma-button-secondary alma-feed-promo__action shrink-0">
            <iconify-icon icon="lucide:compass" style="font-size: 16px;"></iconify-icon>
            <span>{{ __('site.home.promo_link') }}</span>
        </a>
    </div>
</div>

@once
    <style>
        .alma-feed-promo__dismiss-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }

        .alma-feed-promo__dismiss {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.75);
            color: #475569;
            box-shadow: none;
            transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .alma-feed-promo__dismiss:hover,
        .alma-feed-promo__dismiss:focus-visible {
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            outline: 0;
            transform: scale(1.04);
        }

        .alma-feed-promo__dismiss iconify-icon {
            font-size: 16px;
            color: currentColor;
        }

        html.dark .alma-feed-promo__dismiss {
            background: rgba(15, 23, 42, 0.78);
            color: #cbd5e1;
        }

        html.dark .alma-feed-promo__dismiss:hover,
        html.dark .alma-feed-promo__dismiss:focus-visible {
            background: rgba(15, 23, 42, 0.96);
            color: #f8fafc;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageKey = 'community-pulse-dismissed';

            document.querySelectorAll('[data-community-pulse]').forEach(function (panel) {
                if (!(panel instanceof HTMLElement)) {
                    return;
                }

                try {
                    if (window.localStorage.getItem(storageKey) === '1') {
                        panel.hidden = true;
                        return;
                    }
                } catch (error) {
                }

                const closeButton = panel.querySelector('[data-community-pulse-close]');
                if (!(closeButton instanceof HTMLElement)) {
                    return;
                }

                closeButton.addEventListener('click', function () {
                    panel.hidden = true;

                    try {
                        window.localStorage.setItem(storageKey, '1');
                    } catch (error) {
                    }
                });
            });
        });
    </script>
@endonce
