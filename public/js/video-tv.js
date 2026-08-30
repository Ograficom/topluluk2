(() => {
    const cleanupLegacyVideoHeader = () => {
        document.documentElement.classList.remove('video-tv-mobile-header');
        document.getElementById('video-tv-mobile-header-style')?.remove();
    };

    const installCollapsedBrandFix = () => {
        if (document.getElementById('video-mobile-collapsed-brand-fix')) return;

        const style = document.createElement('style');
        style.id = 'video-mobile-collapsed-brand-fix';
        style.textContent = `
            @media (max-width: 767px) {
                #video-reference-mobile-header .video-mobile-brand.is-collapsed {
                    width: 26px !important;
                    min-width: 26px !important;
                    max-width: 26px !important;
                    height: 38px !important;
                    padding: 0 !important;
                    gap: 0 !important;
                    border: 0 !important;
                    border-radius: 0 !important;
                    background: transparent !important;
                    background-color: transparent !important;
                    box-shadow: none !important;
                    flex: 0 0 26px !important;
                }

                html.dark #video-reference-mobile-header .video-mobile-brand.is-collapsed {
                    background: transparent !important;
                    background-color: transparent !important;
                    box-shadow: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    };

    cleanupLegacyVideoHeader();
    installCollapsedBrandFix();

    const normalize = (value = '') => String(value).toLocaleLowerCase('tr-TR').trim();

    const initVideoTv = () => {
        cleanupLegacyVideoHeader();
        installCollapsedBrandFix();

        const root = document.querySelector('[data-video-tv-root]');
        if (!root || root.dataset.videoTvInitialized === '1') return;

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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVideoTv, { once: true });
    } else {
        initVideoTv();
    }

    document.addEventListener('livewire:navigated', initVideoTv);
    window.addEventListener('pageshow', initVideoTv);
})();
