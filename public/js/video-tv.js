(() => {
    const VIDEO_HEADER_CLASS = 'video-tv-mobile-header';
    const VIDEO_HEADER_STYLE_ID = 'video-tv-mobile-header-style';

    const installVideoMobileHeaderSkin = () => {
        if (!document.querySelector('[data-video-tv-root]')) {
            document.documentElement.classList.remove(VIDEO_HEADER_CLASS);
            return;
        }

        document.documentElement.classList.add(VIDEO_HEADER_CLASS);

        if (!document.getElementById(VIDEO_HEADER_STYLE_ID)) {
            const style = document.createElement('style');
            style.id = VIDEO_HEADER_STYLE_ID;
            style.textContent = `
                @media (max-width: 767px) {
                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header {
                        --site-header-height: 126px !important;
                        position: fixed !important;
                        inset: 0 0 auto 0 !important;
                        width: 100% !important;
                        height: 126px !important;
                        min-height: 126px !important;
                        border: 0 !important;
                        border-bottom: 0 !important;
                        background: #ffffff !important;
                        background-color: #ffffff !important;
                        box-shadow: none !important;
                        backdrop-filter: none !important;
                        -webkit-backdrop-filter: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header > .site-header-shell {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        width: 100% !important;
                        max-width: none !important;
                        height: 126px !important;
                        min-height: 126px !important;
                        margin: 0 !important;
                        padding: 18px 18px 0 !important;
                        gap: 18px !important;
                        background: transparent !important;
                        box-sizing: border-box !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header > .site-header-shell > div:first-child {
                        display: flex !important;
                        align-items: center !important;
                        width: 64px !important;
                        min-width: 64px !important;
                        height: 64px !important;
                        min-height: 64px !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        gap: 0 !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-logo {
                        display: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-mobile-sidebar-toggle] {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 64px !important;
                        min-width: 64px !important;
                        max-width: 64px !important;
                        height: 64px !important;
                        min-height: 64px !important;
                        max-height: 64px !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: 0 !important;
                        border-radius: 9999px !important;
                        background: #ffffff !important;
                        background-color: #ffffff !important;
                        color: #111827 !important;
                        box-shadow: 0 15px 42px rgba(15, 23, 42, .08) !important;
                        transform: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-mobile-sidebar-toggle] > svg,
                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-mobile-sidebar-toggle] > iconify-icon {
                        width: 30px !important;
                        min-width: 30px !important;
                        height: 30px !important;
                        min-height: 30px !important;
                        font-size: 30px !important;
                        color: currentColor !important;
                        transform: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: auto !important;
                        min-width: 142px !important;
                        height: 64px !important;
                        min-height: 64px !important;
                        max-height: 64px !important;
                        margin: 0 0 0 auto !important;
                        padding: 0 8px !important;
                        gap: 2px !important;
                        border: 0 !important;
                        border-radius: 9999px !important;
                        background: #ffffff !important;
                        background-color: #ffffff !important;
                        box-shadow: 0 15px 42px rgba(15, 23, 42, .08) !important;
                        white-space: nowrap !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions > :not(.site-header-write-btn):not([data-user-menu]) {
                        display: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions > .site-header-write-btn {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 58px !important;
                        min-width: 58px !important;
                        max-width: 58px !important;
                        height: 54px !important;
                        min-height: 54px !important;
                        max-height: 54px !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        gap: 0 !important;
                        border: 0 !important;
                        border-radius: 9999px !important;
                        background: transparent !important;
                        background-color: transparent !important;
                        color: #050505 !important;
                        box-shadow: none !important;
                        font-size: 0 !important;
                        transform: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions > .site-header-write-btn > span {
                        display: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions > .site-header-write-btn > :is(svg, iconify-icon) {
                        display: block !important;
                        width: 30px !important;
                        min-width: 30px !important;
                        height: 30px !important;
                        min-height: 30px !important;
                        font-size: 30px !important;
                        color: currentColor !important;
                        margin: 0 !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions > [data-user-menu] {
                        position: relative !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 58px !important;
                        min-width: 58px !important;
                        max-width: 58px !important;
                        height: 54px !important;
                        min-height: 54px !important;
                        max-height: 54px !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-user-menu-btn] {
                        position: relative !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 58px !important;
                        min-width: 58px !important;
                        max-width: 58px !important;
                        height: 54px !important;
                        min-height: 54px !important;
                        max-height: 54px !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: 0 !important;
                        border-radius: 9999px !important;
                        background: transparent !important;
                        background-color: transparent !important;
                        color: #050505 !important;
                        box-shadow: none !important;
                        overflow: visible !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-user-menu-btn] > :is(img, .site-avatar-fallback, svg, iconify-icon) {
                        display: none !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-user-menu-btn]::before {
                        content: '' !important;
                        display: block !important;
                        width: 6px !important;
                        height: 6px !important;
                        border-radius: 9999px !important;
                        background: #111111 !important;
                        box-shadow: 11px 0 0 #111111, 22px 0 0 #111111 !important;
                        transform: translateX(-11px) !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header :is(
                        button[data-mobile-sidebar-toggle],
                        .site-header-write-btn,
                        button[data-user-menu-btn]
                    ):active {
                        background: #f3f4f6 !important;
                        background-color: #f3f4f6 !important;
                    }

                    html.${VIDEO_HEADER_CLASS} body [data-video-tv-root].video-tv-page {
                        padding-top: 84px !important;
                    }

                    html.dark.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header,
                    html.dark.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header > .site-header-shell {
                        background: #ffffff !important;
                        background-color: #ffffff !important;
                    }

                    html.dark.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header button[data-mobile-sidebar-toggle],
                    html.dark.${VIDEO_HEADER_CLASS} body header.site-header[data-site-header].site-header .site-header-actions {
                        background: #ffffff !important;
                        background-color: #ffffff !important;
                        color: #111827 !important;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    };

    installVideoMobileHeaderSkin();

    const normalize = (value = '') => String(value).toLocaleLowerCase('tr-TR').trim();

    const initVideoTv = () => {
        const root = document.querySelector('[data-video-tv-root]');
        if (!root || root.dataset.videoTvInitialized === '1') {
            if (!root) document.documentElement.classList.remove(VIDEO_HEADER_CLASS);
            return;
        }

        installVideoMobileHeaderSkin();
        root.dataset.videoTvInitialized = '1';

        if (typeof window.__ografiVideoTvDestroy === 'function') {
            window.__ografiVideoTvDestroy();
        }

        const dataNode = root.querySelector('[data-video-tv-data]');
        const player = root.querySelector('[data-video-player]');
        const loading = root.querySelector('[data-video-loading]');
        const errorBox = root.querySelector('[data-video-error]');
        const errorText = root.querySelector('[data-video-error-text]');
        const currentTitle = root.querySelector('[data-video-current-title]');
        const currentMeta = root.querySelector('[data-video-current-meta]');
        const healthStatus = root.querySelector('[data-video-health-status]');
        const sourceCount = root.querySelector('[data-video-source-count]');
        const checkedCount = root.querySelector('[data-video-checked-count]');
        const failedCount = root.querySelector('[data-video-failed-count]');
        const visibleCount = root.querySelector('[data-video-visible-count]');
        const nextButton = root.querySelector('[data-video-next]');
        const searchInput = root.querySelector('[data-video-search]');
        const emptyState = root.querySelector('[data-video-empty]');
        const filterButtons = Array.from(root.querySelectorAll('[data-video-filter]'));
        const cards = Array.from(root.querySelectorAll('[data-video-card]'));

        if (!dataNode || !player) return;

        let items = [];
        try {
            items = JSON.parse(dataNode.textContent || '[]');
        } catch {
            items = [];
        }

        if (!Array.isArray(items)) items = [];
        items.forEach((item) => { item.health = 'idle'; });

        let active = -1;
        let category = 'Tümü';
        let search = '';
        let hls = null;
        let timer = null;
        let switchingAfterFailure = false;
        let ignorePlayerErrorsUntil = 0;
        let destroyed = false;

        const safePoster = (value) => {
            try {
                const url = new URL(String(value || ''), window.location.href);
                return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
            } catch {
                return '';
            }
        };

        const clearTimer = () => {
            if (timer) window.clearTimeout(timer);
            timer = null;
        };

        const stopHls = () => {
            clearTimer();
            if (hls) hls.destroy();
            hls = null;
        };

        const counts = () => {
            const checked = items.filter((item) => item.health !== 'idle').length;
            const failed = items.filter((item) => item.health === 'bad').length;
            const usable = items.length - failed;

            if (healthStatus) healthStatus.textContent = `${usable} kanal kullanılabilir`;
            if (sourceCount) sourceCount.textContent = `${checked} kontrol edildi`;
            if (checkedCount) checkedCount.textContent = `${checked} kontrol edildi`;
            if (failedCount) failedCount.textContent = `${failed} bozuk`;
        };

        const cardFor = (index) => cards.find((card) => Number(card.dataset.index) === index) || null;

        const updateCardHealth = (index) => {
            const item = items[index];
            const card = cardFor(index);
            if (!item || !card) return;

            const badge = card.querySelector('[data-video-card-health]');
            card.classList.toggle('is-bad', item.health === 'bad');
            card.disabled = item.health === 'bad';

            if (badge) {
                badge.dataset.state = item.health;
                badge.textContent = item.health === 'bad'
                    ? 'Bozuk'
                    : (item.health === 'ok' ? 'Çalışıyor' : 'Hazır');
            }
        };

        const updateActiveCard = () => {
            cards.forEach((card) => {
                card.classList.toggle('is-active', Number(card.dataset.index) === active);
            });
        };

        const matchesFilters = (item) => {
            const itemCategory = item?.category || 'Genel';
            const categoryOk = category === 'Tümü' || itemCategory === category;
            if (!categoryOk) return false;

            if (!search) return true;
            return normalize(`${item?.name || ''} ${itemCategory}`).includes(search);
        };

        const applyFilters = () => {
            let visible = 0;

            cards.forEach((card) => {
                const index = Number(card.dataset.index);
                const item = items[index];
                const show = Boolean(item) && matchesFilters(item);
                card.hidden = !show;
                if (show) visible += 1;
            });

            if (visibleCount) visibleCount.textContent = `${visible} kanal`;
            if (emptyState) emptyState.hidden = visible > 0;

            filterButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.videoFilter === category);
            });
        };

        const markOk = (index) => {
            const item = items[index];
            if (!item || item.health === 'ok') return;
            item.health = 'ok';
            updateCardHealth(index);
            counts();
        };

        const markBad = (index) => {
            const item = items[index];
            if (!item || item.health === 'bad') return;
            item.health = 'bad';
            updateCardHealth(index);
            counts();
        };

        const nextIndex = (from) => {
            if (!items.length) return -1;

            for (let step = 1; step <= items.length; step += 1) {
                const index = (from + step + items.length) % items.length;
                const item = items[index];
                if (!item || item.health === 'bad' || !matchesFilters(item)) continue;
                return index;
            }

            return -1;
        };

        const hideError = () => {
            if (!errorBox) return;
            errorBox.hidden = true;
        };

        const showError = (message) => {
            if (loading) loading.hidden = true;
            if (errorText) errorText.textContent = message;
            if (errorBox) errorBox.hidden = false;
        };

        const fail = (message) => {
            if (destroyed || switchingAfterFailure || active < 0) return;

            switchingAfterFailure = true;
            const failedIndex = active;
            stopHls();
            markBad(failedIndex);
            showError(`${message} Sıradaki çalışan kanala geçiliyor.`);

            const next = nextIndex(failedIndex);
            if (next >= 0) {
                window.setTimeout(() => {
                    if (destroyed) return;
                    switchingAfterFailure = false;
                    play(next);
                }, 450);
            } else {
                switchingAfterFailure = false;
                if (currentTitle) currentTitle.textContent = 'Çalışan kanal bulunamadı';
                if (currentMeta) currentMeta.textContent = 'Filtreyi değiştirip tekrar deneyebilirsin.';
            }
        };

        const play = async (index) => {
            if (destroyed) return;
            const item = items[index];
            if (!item || item.health === 'bad') return;

            active = index;
            switchingAfterFailure = false;
            stopHls();
            hideError();
            updateActiveCard();

            if (loading) {
                loading.textContent = `${item.name || 'Kanal'} açılıyor…`;
                loading.hidden = false;
            }

            if (currentTitle) currentTitle.textContent = item.name || 'Canlı yayın';
            if (currentMeta) currentMeta.textContent = `${item.category || 'Genel'} · Canlı yayın`;

            ignorePlayerErrorsUntil = performance.now() + 800;
            player.pause();
            player.removeAttribute('src');
            player.removeAttribute('poster');

            const poster = safePoster(item.image);
            if (poster) player.poster = poster;

            timer = window.setTimeout(() => {
                if (!destroyed && player.readyState === 0) {
                    fail('Yayın sunucusundan yanıt alınamadı.');
                }
            }, 15000);

            if (item.type === 'hls') {
                if (player.canPlayType('application/vnd.apple.mpegurl')) {
                    player.src = item.url;
                    player.load();
                    try { await player.play(); } catch {}
                    return;
                }

                if (window.Hls?.isSupported()) {
                    hls = new window.Hls({
                        enableWorker: true,
                        lowLatencyMode: true,
                        manifestLoadingTimeOut: 12000,
                        levelLoadingTimeOut: 12000,
                    });

                    hls.loadSource(item.url);
                    hls.attachMedia(player);
                    hls.on(window.Hls.Events.MANIFEST_PARSED, () => {
                        if (destroyed) return;
                        markOk(index);
                        player.play().catch(() => {});
                    });
                    hls.on(window.Hls.Events.ERROR, (_event, data) => {
                        if (!destroyed && data?.fatal) fail('HLS kaynağı yanıt vermedi.');
                    });
                    return;
                }

                fail('Bu tarayıcı HLS oynatmayı desteklemiyor.');
                return;
            }

            player.src = item.url;
            player.load();
            try { await player.play(); } catch {}
        };

        const onLoadedMetadata = () => {
            clearTimer();
            markOk(active);
        };
        const onCanPlay = () => {
            clearTimer();
            if (loading) loading.hidden = true;
            markOk(active);
        };
        const onPlaying = () => {
            clearTimer();
            if (loading) loading.hidden = true;
            markOk(active);
        };
        const onWaiting = () => {
            if (loading) loading.hidden = false;
        };
        const onError = () => {
            if (performance.now() < ignorePlayerErrorsUntil) return;
            if (active >= 0) fail('Video kaynağı yüklenemedi.');
        };
        const onEnded = () => {
            const next = nextIndex(active);
            if (next >= 0) play(next);
        };

        player.addEventListener('loadedmetadata', onLoadedMetadata);
        player.addEventListener('canplay', onCanPlay);
        player.addEventListener('playing', onPlaying);
        player.addEventListener('waiting', onWaiting);
        player.addEventListener('error', onError);
        player.addEventListener('ended', onEnded);

        cards.forEach((card) => {
            card.addEventListener('click', () => play(Number(card.dataset.index)));
        });

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                category = button.dataset.videoFilter || 'Tümü';
                applyFilters();
            });
        });

        searchInput?.addEventListener('input', () => {
            search = normalize(searchInput.value);
            applyFilters();
        });

        nextButton?.addEventListener('click', () => {
            const next = nextIndex(active);
            if (next >= 0) play(next);
        });

        counts();
        applyFilters();

        if (items.length) {
            play(0);
        } else {
            if (loading) loading.hidden = true;
            showError('Kanal listesi yüklenemedi. Filament panelinden kanal ekleyebilirsin.');
        }

        window.__ografiVideoTvDestroy = () => {
            destroyed = true;
            stopHls();
            player.pause();
            player.removeEventListener('loadedmetadata', onLoadedMetadata);
            player.removeEventListener('canplay', onCanPlay);
            player.removeEventListener('playing', onPlaying);
            player.removeEventListener('waiting', onWaiting);
            player.removeEventListener('error', onError);
            player.removeEventListener('ended', onEnded);
        };
    };

    const navigateInit = () => {
        if (document.querySelector('[data-video-tv-root]')) {
            installVideoMobileHeaderSkin();
            initVideoTv();
        } else {
            document.documentElement.classList.remove(VIDEO_HEADER_CLASS);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVideoTv, { once: true });
    } else {
        initVideoTv();
    }

    document.addEventListener('livewire:navigated', navigateInit);
    window.addEventListener('pageshow', navigateInit);
})();
