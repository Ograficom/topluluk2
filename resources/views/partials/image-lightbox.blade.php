<style>
    .ografi-image-lightbox {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: none;
        pointer-events: none;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.84);
        color: #ffffff;
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
    }

    .ografi-image-lightbox.is-open {
        display: block;
        pointer-events: auto;
    }

    .ografi-image-lightbox__stage {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 64px 74px 112px;
        touch-action: pan-y;
    }

    .ografi-image-lightbox__frame {
        display: flex;
        width: auto;
        max-width: min(82vw, 1080px);
        max-height: calc(100svh - 190px);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .ografi-image-lightbox__image {
        display: block;
        max-width: min(82vw, 1080px);
        max-height: calc(100svh - 230px);
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 14px;
        user-select: none;
        -webkit-user-drag: none;
        pointer-events: auto;
    }

    .ografi-image-lightbox__caption {
        display: none;
        max-width: min(82vw, 860px);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        padding: 10px 14px;
        font-size: 13px;
        line-height: 1.5;
        text-align: center;
        -webkit-backdrop-filter: blur(24px) saturate(170%);
        backdrop-filter: blur(24px) saturate(170%);
    }

    .ografi-image-lightbox__caption.is-visible {
        display: block;
    }

    .ografi-image-lightbox__close,
    .ografi-image-lightbox__nav {
        position: absolute;
        z-index: 100003;
        display: inline-flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.22) !important;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14) !important;
        color: #ffffff !important;
        box-shadow: none !important;
        outline: none !important;
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        backdrop-filter: blur(24px) saturate(180%);
        transition: background-color .18s ease, border-color .18s ease, opacity .18s ease !important;
    }

    .ografi-image-lightbox__close:hover,
    .ografi-image-lightbox__close:focus,
    .ografi-image-lightbox__close:focus-visible,
    .ografi-image-lightbox__nav:hover,
    .ografi-image-lightbox__nav:focus,
    .ografi-image-lightbox__nav:focus-visible {
        border-color: rgba(255, 255, 255, 0.34) !important;
        background: rgba(255, 255, 255, 0.20) !important;
        color: #ffffff !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .ografi-image-lightbox__close {
        top: max(14px, env(safe-area-inset-top));
        right: max(14px, env(safe-area-inset-right));
        width: 42px;
        height: 42px;
    }

    .ografi-image-lightbox__nav {
        bottom: calc(18px + env(safe-area-inset-bottom));
        width: 46px;
        height: 46px;
    }

    .ografi-image-lightbox__nav--prev {
        left: calc(50% - 250px);
    }

    .ografi-image-lightbox__nav--next {
        right: calc(50% - 250px);
    }

    .ografi-image-lightbox__close svg,
    .ografi-image-lightbox__nav svg {
        display: block !important;
        width: 20px;
        height: 20px;
        color: #ffffff !important;
        stroke: #ffffff !important;
        fill: none !important;
        opacity: 1 !important;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.32)) !important;
    }

    .ografi-image-lightbox__nav svg {
        width: 18px;
        height: 18px;
    }

    .ografi-image-lightbox__close svg path,
    .ografi-image-lightbox__nav svg path {
        stroke: #ffffff !important;
        opacity: 1 !important;
    }

    .ografi-image-lightbox__thumbs {
        position: absolute;
        z-index: 100002;
        left: 50%;
        bottom: calc(14px + env(safe-area-inset-bottom));
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: min(440px, calc(100vw - 132px));
        overflow-x: auto;
        scrollbar-width: none;
        transform: translateX(-50%);
        padding: 8px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.08);
        -webkit-backdrop-filter: blur(24px) saturate(170%);
        backdrop-filter: blur(24px) saturate(170%);
    }

    .ografi-image-lightbox__thumbs::-webkit-scrollbar {
        display: none;
    }

    .ografi-image-lightbox__thumb {
        display: inline-flex;
        width: 58px;
        height: 58px;
        flex: 0 0 auto;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.06) !important;
        opacity: 0.62;
        overflow: hidden;
        box-shadow: none !important;
        outline: none !important;
        -webkit-backdrop-filter: blur(18px) saturate(150%);
        backdrop-filter: blur(18px) saturate(150%);
        transition: opacity .18s ease, border-color .18s ease, background-color .18s ease !important;
    }

    .ografi-image-lightbox__thumb:hover,
    .ografi-image-lightbox__thumb:focus,
    .ografi-image-lightbox__thumb:focus-visible {
        border-color: rgba(255, 255, 255, 0.34) !important;
        background: rgba(255, 255, 255, 0.12) !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .ografi-image-lightbox__thumb.is-active {
        opacity: 1;
        border-color: rgba(255, 255, 255, 0.72) !important;
        background: rgba(255, 255, 255, 0.12) !important;
    }

    .ografi-image-lightbox__thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    body.alma-app .ografi-image-lightbox button,
    body.alma-app .ografi-image-lightbox button:hover,
    body.alma-app .ografi-image-lightbox button:focus,
    body.alma-app .ografi-image-lightbox button:focus-visible {
        box-shadow: none !important;
    }

    @media (min-width: 641px) {
        .ografi-image-lightbox__nav {
            bottom: calc(24px + env(safe-area-inset-bottom));
        }

        .ografi-image-lightbox__nav--prev {
            left: calc(50% - min(220px, 43vw) - 58px);
        }

        .ografi-image-lightbox__nav--next {
            right: calc(50% - min(220px, 43vw) - 58px);
        }
    }

    @media (max-width: 640px) {
        .ografi-image-lightbox {
            background: rgba(0, 0, 0, 0.86);
        }

        .ografi-image-lightbox__stage {
            padding: 64px 12px 142px;
            align-items: center;
        }

        .ografi-image-lightbox__frame {
            max-width: calc(100vw - 24px);
            max-height: calc(100svh - 214px);
            gap: 10px;
        }

        .ografi-image-lightbox__image {
            max-width: calc(100vw - 24px);
            max-height: calc(100svh - 258px);
            border-radius: 12px;
        }

        .ografi-image-lightbox__caption {
            max-width: calc(100vw - 34px);
            padding: 9px 12px;
            font-size: 12.5px;
            border-radius: 14px;
        }

        .ografi-image-lightbox__close {
            top: max(12px, env(safe-area-inset-top));
            right: max(12px, env(safe-area-inset-right));
            width: 42px;
            height: 42px;
        }

        .ografi-image-lightbox__nav {
            top: auto;
            bottom: calc(86px + env(safe-area-inset-bottom));
            width: 42px;
            height: 42px;
        }

        .ografi-image-lightbox__nav--prev {
            left: 18px;
            right: auto;
        }

        .ografi-image-lightbox__nav--next {
            right: 18px;
            left: auto;
        }

        .ografi-image-lightbox__thumbs {
            left: 50%;
            right: auto;
            bottom: calc(16px + env(safe-area-inset-bottom));
            max-width: calc(100vw - 36px);
            padding: 6px;
            border-radius: 16px;
        }

        .ografi-image-lightbox__thumb {
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }
    }

    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__close,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__nav,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__close:hover,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__nav:hover,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__close:focus,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__nav:focus,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__close:focus-visible,
    body.alma-app .ografi-image-lightbox .ografi-image-lightbox__nav:focus-visible {
        background: rgba(255, 255, 255, 0.14) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.22) !important;
    }

</style>

<script>
    (() => {
        if (window.ografiImageLightboxBooted) return;
        window.ografiImageLightboxBooted = true;

        const excludedSelector = [
            '.ografi-image-lightbox',
            '.site-header',
            '.sidebar-wrapper',
            '.layout-side--left',
            '.layout-side--right',
            '.site-search-panel',
            '.site-notifications-panel',
            '.alma-link-preview',
            '.profile-hover-card',
            '[data-mobile-bottom-nav]',
            '[data-no-image-lightbox]',
            'button',
            'summary'
        ].join(',');

        const excludedClassPattern = /(avatar|logo|icon|badge|reaction|emoji|pwa|verified|glyph|mark|fallback|thumbnail)/i;

        let overlay = null;
        let imageEl = null;
        let captionEl = null;
        let thumbsEl = null;
        let prevBtn = null;
        let nextBtn = null;
        let items = [];
        let activeIndex = 0;
        let previousOverflow = '';
        let previousBodyOverflow = '';

        const imageUrl = (img) => img.currentSrc || img.src || img.getAttribute('src') || '';

        const cleanText = (value) => String(value || '').replace(/\s+/g, ' ').trim();

        const captionForImage = (img) => {
            const explicitCaption = cleanText(
                img.getAttribute('data-caption') ||
                img.getAttribute('data-description') ||
                img.getAttribute('data-lightbox-caption') ||
                img.getAttribute('title')
            );

            if (explicitCaption) return explicitCaption;

            const figureCaption = img.closest('figure')?.querySelector('figcaption');
            const figureText = cleanText(figureCaption?.textContent || '');

            if (figureText) return figureText;

            const altText = cleanText(img.getAttribute('alt') || '');

            if (
                altText &&
                !/^image$/i.test(altText) &&
                !/^görsel$/i.test(altText) &&
                !/^resim$/i.test(altText)
            ) {
                return altText;
            }

            return '';
        };

        const isLightboxImage = (img) => {
            if (!(img instanceof HTMLImageElement)) return false;
            if (!imageUrl(img)) return false;
            if (img.closest(excludedSelector)) return false;

            const className = String(img.className || '');
            if (excludedClassPattern.test(className)) {
                if (!/(post-card__media-image|alma-post-card__image|ps-post-image|ogx-comment-media)/.test(className)) {
                    return false;
                }
            }

            const rect = img.getBoundingClientRect();
            const isKnownContentImage = !!img.closest(
                '[data-post-card-media-wrap], .post-card__media-wrap, .ps-post-image, .ps-post-body, .post-content, .ogx-comment-media, .profile-reference-posts, .category-reference-posts'
            );

            if (isKnownContentImage) return true;

            return rect.width >= 180 && rect.height >= 120;
        };

        const normalizeItem = (img) => ({
            src: imageUrl(img),
            alt: cleanText(img.getAttribute('alt') || ''),
            caption: captionForImage(img)
        });

        const uniqueItems = (images) => {
            const seen = new Set();

            return images
                .filter(isLightboxImage)
                .map(normalizeItem)
                .filter((item) => {
                    if (!item.src || seen.has(item.src)) return false;
                    seen.add(item.src);
                    return true;
                });
        };

        const groupForImage = (img) => {
            const mediaWrap = img.closest('[data-post-card-media-wrap], .post-card__media-wrap');
            if (mediaWrap) {
                return uniqueItems(Array.from(mediaWrap.querySelectorAll('[data-media-type="image"] img, .post-card__media-image')));
            }

            const body = img.closest('.ps-post-body, .post-content');
            if (body) {
                return uniqueItems(Array.from(body.querySelectorAll('img')));
            }

            const card = img.closest('[data-post-card-shell], article, .site-card, .community-card, .alma-panel');
            if (card) {
                return uniqueItems(Array.from(card.querySelectorAll('img')));
            }

            return uniqueItems([img]);
        };

        const ensureOverlay = () => {
            if (overlay) return;

            overlay = document.createElement('div');
            overlay.className = 'ografi-image-lightbox';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-label', 'Görsel görüntüleyici');
            overlay.innerHTML = `
                <button type="button" class="ografi-image-lightbox__close" aria-label="Kapat">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </button>

                <button type="button" class="ografi-image-lightbox__nav ografi-image-lightbox__nav--prev" aria-label="Önceki görsel">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 5 8 12l7 7" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <div class="ografi-image-lightbox__stage">
                    <div class="ografi-image-lightbox__frame">
                        <img class="ografi-image-lightbox__image" alt="">
                        <div class="ografi-image-lightbox__caption"></div>
                    </div>
                </div>

                <button type="button" class="ografi-image-lightbox__nav ografi-image-lightbox__nav--next" aria-label="Sonraki görsel">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m9 5 7 7-7 7" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <div class="ografi-image-lightbox__thumbs" aria-label="Görsel listesi"></div>
            `;

            document.body.appendChild(overlay);

            imageEl = overlay.querySelector('.ografi-image-lightbox__image');
            captionEl = overlay.querySelector('.ografi-image-lightbox__caption');
            thumbsEl = overlay.querySelector('.ografi-image-lightbox__thumbs');
            prevBtn = overlay.querySelector('.ografi-image-lightbox__nav--prev');
            nextBtn = overlay.querySelector('.ografi-image-lightbox__nav--next');

            overlay.querySelector('.ografi-image-lightbox__close').addEventListener('click', close);

            imageEl.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            prevBtn.addEventListener('click', () => show(activeIndex - 1));
            nextBtn.addEventListener('click', () => show(activeIndex + 1));

            overlay.addEventListener('click', (event) => {
                if (event.target === overlay || event.target === overlay.querySelector('.ografi-image-lightbox__stage')) {
                    close();
                }
            });
        };

        const renderThumbs = () => {
            thumbsEl.innerHTML = '';

            items.forEach((item, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'ografi-image-lightbox__thumb';
                button.setAttribute('aria-label', (index + 1) + '. görseli aç');
                button.addEventListener('click', () => show(index));

                const thumb = document.createElement('img');
                thumb.src = item.src;
                thumb.alt = item.alt || '';
                thumb.loading = 'lazy';
                thumb.decoding = 'async';

                button.appendChild(thumb);
                thumbsEl.appendChild(button);
            });
        };

        const show = (index) => {
            if (!items.length) return;

            activeIndex = (index + items.length) % items.length;
            const item = items[activeIndex];

            imageEl.src = item.src;
            imageEl.alt = item.alt || '';

            const caption = cleanText(item.caption || '');

            if (caption) {
                captionEl.textContent = caption;
                captionEl.classList.add('is-visible');
            } else {
                captionEl.textContent = '';
                captionEl.classList.remove('is-visible');
            }

            prevBtn.hidden = items.length < 2;
            nextBtn.hidden = items.length < 2;
            thumbsEl.hidden = items.length < 2;

            Array.from(thumbsEl.children).forEach((child, childIndex) => {
                child.classList.toggle('is-active', childIndex === activeIndex);
            });
        };

        const open = (clickedImg) => {
            const group = groupForImage(clickedImg);
            const clickedSrc = imageUrl(clickedImg);

            if (!group.length) return;

            ensureOverlay();
            items = group;
            activeIndex = Math.max(0, items.findIndex((item) => item.src === clickedSrc));

            renderThumbs();
            show(activeIndex);

            previousOverflow = document.documentElement.style.overflow;
            previousBodyOverflow = document.body.style.overflow;

            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';

            overlay.classList.add('is-open');
        };

        function close() {
            if (!overlay) return;

            overlay.classList.remove('is-open');

            if (imageEl) {
                imageEl.removeAttribute('src');
                imageEl.removeAttribute('alt');
            }

            if (captionEl) {
                captionEl.textContent = '';
                captionEl.classList.remove('is-visible');
            }

            items = [];
            activeIndex = 0;

            document.documentElement.style.overflow = previousOverflow || '';
            document.body.style.overflow = previousBodyOverflow || '';
        }

        document.addEventListener('click', (event) => {
            const img = event.target instanceof Element ? event.target.closest('img') : null;
            if (!img || !isLightboxImage(img)) return;

            event.preventDefault();
            event.stopPropagation();
            open(img);
        }, true);

        document.addEventListener('keydown', (event) => {
            if (!overlay || !overlay.classList.contains('is-open')) return;

            if (event.key === 'Escape') close();
            if (event.key === 'ArrowLeft') show(activeIndex - 1);
            if (event.key === 'ArrowRight') show(activeIndex + 1);
        });
    })();
</script>
