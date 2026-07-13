document.addEventListener('DOMContentLoaded', function () {
    const enhanceNativeVideos = () => {
        const nativeVideos = Array.from(document.querySelectorAll('video'))
            .filter((video) => !video.closest('[data-og-player]') && !video.hasAttribute('data-og-ignore'));

        nativeVideos.forEach((video) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'og-video-wrapper';
            wrapper.setAttribute('data-og-player', '1');
            wrapper.dataset.qualities = '[]';

            const stage = document.createElement('div');
            stage.className = 'og-video-stage';

            video.removeAttribute('controls');
            video.setAttribute('data-og-video', '1');

            const bigPlay = document.createElement('button');
            bigPlay.className = 'og-big-play';
            bigPlay.type = 'button';
            bigPlay.setAttribute('data-og-play', '1');
            bigPlay.setAttribute('aria-label', 'Oynat / Duraklat');
            bigPlay.innerHTML = '<svg viewBox="0 0 32 32" aria-hidden="true" class="og-icon og-icon-xl og-icon-play"><path fill="currentColor" d="M12.225 4.462C9.89 3.142 7 4.827 7 7.508V24.5c0 2.682 2.892 4.367 5.226 3.045l14.997-8.498c2.367-1.341 2.366-4.751 0-6.091L12.224 4.462Z"/></svg><svg viewBox="0 0 20 20" aria-hidden="true" class="og-icon og-icon-xl og-icon-pause"><path fill="currentColor" fill-rule="evenodd" d="M6.75 3a2 2 0 0 0-2 2v10a2 2 0 1 0 4 0V5a2 2 0 0 0-2-2Zm6.5 0a2 2 0 0 0-2 2v10a2 2 0 1 0 4 0V5a2 2 0 0 0-2-2Z" clip-rule="evenodd"/></svg>';

            const topbar = document.createElement('div');
            topbar.className = 'og-topbar';
            topbar.innerHTML = '';

            const controls = document.createElement('div');
            controls.className = 'og-controls';
            controls.setAttribute('role', 'group');
            controls.setAttribute('aria-label', 'Video kontrolleri');
            controls.innerHTML = `
                <div class="og-progress">
                    <input type="range" class="og-range" data-og-progress min="0" max="100" value="0" step="0.1" aria-label="Ä°lerleme">
                    <div class="og-progress-bar" data-og-progress-bar></div>
                </div>
                <div class="og-controls-row">
                    <button class="og-btn" type="button" data-og-play aria-label="Oynat / Duraklat">
                        <svg viewBox="0 0 32 32" aria-hidden="true" class="og-icon og-icon-play"><path fill="currentColor" d="M12.225 4.462C9.89 3.142 7 4.827 7 7.508V24.5c0 2.682 2.892 4.367 5.226 3.045l14.997-8.498c2.367-1.341 2.366-4.751 0-6.091L12.224 4.462Z"/></svg>
                        <svg viewBox="0 0 20 20" aria-hidden="true" class="og-icon og-icon-pause"><path fill="currentColor" fill-rule="evenodd" d="M6.75 3a2 2 0 0 0-2 2v10a2 2 0 1 0 4 0V5a2 2 0 0 0-2-2Zm6.5 0a2 2 0 0 0-2 2v10a2 2 0 1 0 4 0V5a2 2 0 0 0-2-2Z" clip-rule="evenodd"/></svg>
                    </button>
                    <button class="og-btn" type="button" data-og-quality-down aria-label="Geri">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="og-icon"><g fill="currentColor"><path fill-rule="evenodd" d="M22 6.426v11.148c0 1.847-1.6 3.015-2.903 2.118L13 15.232V8.768l6.097-4.46C20.399 3.411 22 4.58 22 6.426Z" clip-rule="evenodd" opacity=".5"/><path d="M13 7.123v9.754c0 1.616-1.467 2.638-2.661 1.853L2.92 13.853c-1.228-.807-1.228-2.899 0-3.706l7.42-4.877c1.193-.785 2.66.237 2.66 1.853Z"/></g></svg>
                    </button>
                    <button class="og-btn" type="button" data-og-quality-up aria-label="Ä°leri">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="og-icon"><g fill="currentColor"><path fill-rule="evenodd" d="M2 6.426v11.148c0 1.847 1.6 3.015 2.903 2.118L11 15.232V8.768l-6.097-4.46C3.601 3.411 2 4.58 2 6.426Z" clip-rule="evenodd" opacity=".5"/><path d="M11 7.123v9.754c0 1.616 1.467 2.638 2.661 1.853l7.418-4.877c1.228-.807 1.228-2.899 0-3.706L13.66 5.27C12.467 4.485 11 5.507 11 7.123Z"/></g></svg>
                    </button>
                    <div class="og-time" data-og-time>00:00 / 00:00</div>
                    <div class="og-volume">
                    <button class="og-btn" type="button" data-og-mute aria-label="Ses">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="og-icon og-icon-mute"><path fill="currentColor" fill-rule="evenodd" d="M17.47 9.47a.75.75 0 0 1 1.06 0L20 10.94l1.47-1.47a.75.75 0 0 1 1.06 1.06L21.061 12l1.47 1.47a.75.75 0 0 1-1.061 1.06L20 13.06l-1.47 1.47a.75.75 0 0 1-1.06-1.06L18.94 12l-1.47-1.47a.75.75 0 0 1 0-1.06m-4.433-6.074c1.163-.767 2.713.068 2.713 1.461v14.286c0 1.394-1.55 2.228-2.713 1.461l-6-3.955a.25.25 0 0 0-.137-.042H4a2.75 2.75 0 0 1-2.75-2.75v-3.714A2.75 2.75 0 0 1 4 7.393h2.9a.25.25 0 0 0 .138-.041z" clip-rule="evenodd"/></svg>
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="og-icon og-icon-volume"><path fill="currentColor" fill-rule="evenodd" d="M18.97 6.97a.75.75 0 0 1 1.06 0l-.53.53l.53-.53h.001l.001.002l.003.002l.007.007l.02.02l.062.069c.05.057.12.138.201.241A6.87 6.87 0 0 1 21.75 11.5a6.87 6.87 0 0 1-1.425 4.189a5 5 0 0 1-.264.31l-.02.02l-.006.007l-.003.002v.001h-.001l-.51-.508l.51.51a.75.75 0 1 1-1.061-1.061l.53.53l-.53-.53h-.001v.001l-.002.001l.005-.005l.033-.036q.048-.052.139-.167a5.37 5.37 0 0 0 .448-5.843a5 5 0 0 0-.448-.685a3 3 0 0 0-.172-.203l-.005-.005a.75.75 0 0 1 .003-1.058m-5.933-3.574c1.163-.767 2.713.068 2.713 1.461v14.286c0 1.394-1.55 2.228-2.713 1.461l-6-3.955a.25.25 0 0 0-.137-.042H4a2.75 2.75 0 0 1-2.75-2.75v-3.714A2.75 2.75 0 0 1 4 7.393h2.9a.25.25 0 0 0 .138-.041z" clip-rule="evenodd"/></svg>
                    </button>
                        <div class="og-volume-panel">
                            <input type="range" class="og-range og-range-sm" data-og-volume min="0" max="1" step="0.05" value="1" aria-label="Ses">
                        </div>
                    </div>
                    <label class="og-label">
                        <span class="og-select-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="og-icon og-select-icon"><path fill="currentColor" d="M10.45 15.5q.625.625 1.575.588T13.4 15.4L19 7l-8.4 5.6q-.65.45-.712 1.362t.562 1.538ZM5.1 20q-.55 0-1.012-.238t-.738-.712q-.65-1.175-1-2.438T2 14q0-2.075.788-3.9t2.137-3.175q1.35-1.35 3.175-2.137T12 4q2.05 0 3.85.775T19 6.888q1.35 1.337 2.15 3.125t.825 3.837q.025 1.375-.313 2.688t-1.037 2.512q-.275.475-.738.713T18.875 20H5.1Z"/></svg>
                            <select class="og-select" data-og-speed aria-label="HÄ±z">
                            <option value="0.5">0.5x</option>
                            <option value="0.75">0.75x</option>
                            <option value="1" selected></option>
                            <option value="1.25">1.25x</option>
                            <option value="1.5">1.5x</option>
                            <option value="2">2x</option>
                        </select>
                        </span>
                    </label>

                    <div class="og-spacer"></div>
                    <button class="og-btn" type="button" data-og-subtitle aria-label="AltyazÄ± AÃ§/Kapat">
                        <svg viewBox="0 0 20 20" aria-hidden="true" class="og-icon"><path fill="currentColor" d="M2 6.75A2.75 2.75 0 0 1 4.75 4h10.5A2.75 2.75 0 0 1 18 6.75v6.5A2.75 2.75 0 0 1 15.25 16H4.75A2.75 2.75 0 0 1 2 13.25v-6.5Zm2 4.75a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5Zm.5 1.5a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3Zm8.5-1.5a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 0-1h-2a.5.5 0 0 0-.5.5ZM9.5 13a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1h-6Z"/></svg>
                    </button>
                    <button class="og-btn" type="button" data-og-fullscreen aria-label="Tam ekran">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="og-icon"><path d="M8 3H3v5M21 8V3h-5M3 16v5h5M16 21h5v-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            `;

            const parent = video.parentNode;
            if (!parent) return;

            parent.insertBefore(wrapper, video);
            wrapper.appendChild(stage);
            stage.appendChild(video);
            stage.appendChild(bigPlay);
            stage.appendChild(topbar);
            stage.appendChild(controls);
        });
    };

    enhanceNativeVideos();
    const players = document.querySelectorAll('[data-og-player]');
    if (!players.length) return;

    const formatTime = (sec) => {
        if (!isFinite(sec)) return '00:00';
        const s = Math.floor(sec % 60).toString().padStart(2, '0');
        const m = Math.floor((sec / 60) % 60).toString().padStart(2, '0');
        const h = Math.floor(sec / 3600);
        return h > 0 ? `${h}:${m}:${s}` : `${m}:${s}`;
    };

    players.forEach((wrapper) => {
        const video = wrapper.querySelector('[data-og-video]');
        if (!video) return;

        const qualities = JSON.parse(wrapper.dataset.qualities || '[]');
        let currentQuality = 0;
        let hideTimer = null;

        const qLabel = wrapper.querySelector('[data-og-quality-label]');
        const playBtns = wrapper.querySelectorAll('[data-og-play]');
        const qUp = wrapper.querySelector('[data-og-quality-up]');
        const qDown = wrapper.querySelector('[data-og-quality-down]');
        const speed = wrapper.querySelector('[data-og-speed]');
        const subToggle = wrapper.querySelector('[data-og-subtitle]');
        const pipButton = wrapper.querySelector('[data-og-pip]');
        const miniBtn = wrapper.querySelector('[data-og-mini]');
        const fullscreenBtn = wrapper.querySelector('[data-og-fullscreen]');
        const muteBtn = wrapper.querySelector('[data-og-mute]');
        const volumeWrap = wrapper.querySelector('.og-volume');
        const volume = wrapper.querySelector('[data-og-volume]');
        const progress = wrapper.querySelector('[data-og-progress]');
        const progressBar = wrapper.querySelector('[data-og-progress-bar]');
        const timeEl = wrapper.querySelector('[data-og-time]');
        const controls = wrapper.querySelector('.og-controls');

        function updateQualityLabel() {
            if (!qLabel) return;
            qLabel.textContent = qualities[currentQuality] ? qualities[currentQuality].label : '--';
        }

        function syncTime() {
            if (!timeEl) return;
            const cur = formatTime(video.currentTime);
            const dur = formatTime(video.duration);
            timeEl.textContent = `${cur} / ${dur}`;
        }

        function syncProgress() {
            if (!progress || !progressBar) return;
            const percent = video.duration ? (video.currentTime / video.duration) * 100 : 0;
            progress.value = percent;
            progressBar.style.width = `${percent}%`;
        }

        function togglePlay() {
            if (video.paused) video.play();
            else video.pause();
        }

        function changeQuality(index) {
            if (!qualities[index]) return;
            const wasPaused = video.paused;
            const time = video.currentTime || 0;
            const rate = video.playbackRate || 1;

            while (video.firstChild) video.removeChild(video.firstChild);
            const source = document.createElement('source');
            source.src = qualities[index].url;
            source.type = qualities[index].type || 'video/mp4';
            video.appendChild(source);
            video.load();
            video.currentTime = time;
            video.playbackRate = rate;
            if (!wasPaused) video.play();
            currentQuality = index;
            updateQualityLabel();
        }

        function showControls() {
            if (!controls) return;
            controls.classList.remove('og-hidden');
            clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                if (!video.paused) controls.classList.add('og-hidden');
            }, 2000);
        }

        updateQualityLabel();
        syncTime();
        syncProgress();

        playBtns.forEach((btn) => btn.addEventListener('click', togglePlay));

        if (qUp) qUp.addEventListener('click', () => {
            if (qualities.length < 2) return;
            if (currentQuality < qualities.length - 1) changeQuality(currentQuality + 1);
        });
        if (qDown) qDown.addEventListener('click', () => {
            if (qualities.length < 2) return;
            if (currentQuality > 0) changeQuality(currentQuality - 1);
        });

        if (speed) speed.addEventListener('change', (e) => {
            video.playbackRate = parseFloat(e.target.value);
        });

        if (subToggle) subToggle.addEventListener('click', () => {
            const track = video.querySelector('track');
            if (!track) return;
            const nextMode = (track.mode === 'showing') ? 'hidden' : 'showing';
            track.mode = nextMode;
            subToggle.classList.toggle('is-active', nextMode === 'showing');
        });

        if (pipButton) {
            pipButton.remove();
        }

        if (miniBtn) {
            miniBtn.remove();
        }

        if (fullscreenBtn) fullscreenBtn.addEventListener('click', async () => {
            try {
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                } else if (wrapper.requestFullscreen) {
                    await wrapper.requestFullscreen();
                }
            } catch (err) {
                console.error('Fullscreen hatasý', err);
            }
        });

        async function tryLandscapeLock() {
            try {
                if (screen.orientation && screen.orientation.lock) {
                    await screen.orientation.lock('landscape');
                }
            } catch (_) {}
        }

        function showRotateHint() {
            let hint = wrapper.querySelector('.og-rotate-hint');
            if (!hint) {
                hint = document.createElement('div');
                hint.className = 'og-rotate-hint';
                hint.innerHTML = '<div class="og-rotate-card">Daha iyi görünüm için telefonu yatay çevir.</div>';
                wrapper.appendChild(hint);
            }
            hint.classList.add('og-show');
            setTimeout(() => hint.classList.remove('og-show'), 2200);
        }

        function isLandscapeVideo() {
            const w = video.videoWidth || 0;
            const h = video.videoHeight || 0;
            return w > h;
        }

        if (fullscreenBtn) fullscreenBtn.addEventListener('click', async () => {
            const isPortrait = window.matchMedia('(orientation: portrait)').matches;
            if (isPortrait && isLandscapeVideo()) {
                await tryLandscapeLock();
                showRotateHint();
            }
        });

        if (muteBtn) muteBtn.addEventListener('click', () => {
            if (volumeWrap) volumeWrap.classList.toggle('open');
            video.muted = !video.muted;
            if (video.muted) wrapper.classList.add('og-muted');
            else wrapper.classList.remove('og-muted');
        });

        if (speed) {
            const speedWrap = speed.closest('.og-select-wrap');
            if (speedWrap) {
                speedWrap.addEventListener('click', (e) => {
                    e.preventDefault();
                    speed.focus();
                    speed.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
                    speed.dispatchEvent(new MouseEvent('click', { bubbles: true }));
                });
            }
        }

        if (volume) volume.addEventListener('input', (e) => {
            video.volume = parseFloat(e.target.value);
            video.muted = video.volume === 0;
            if (video.muted) wrapper.classList.add('og-muted');
            else wrapper.classList.remove('og-muted');
        });

        if (progress) {
            progress.addEventListener('input', (e) => {
                if (!video.duration) return;
                const pct = parseFloat(e.target.value) || 0;
                video.currentTime = (pct / 100) * video.duration;
                syncProgress();
            });
        }

        video.addEventListener('timeupdate', () => {
            syncTime();
            syncProgress();
        });
        video.addEventListener('loadedmetadata', () => {
            syncTime();
            syncProgress();
            if (subToggle) {
                const track = video.querySelector('track');
                if (track) subToggle.classList.toggle('is-active', track.mode === 'showing');
            }
        });
        video.addEventListener('play', () => {
            wrapper.classList.add('og-playing');
            showControls();
        });
        video.addEventListener('pause', () => {
            wrapper.classList.remove('og-playing');
            showControls();
        });

        wrapper.addEventListener('mousemove', showControls);
        wrapper.addEventListener('mouseenter', showControls);
        wrapper.addEventListener('mouseleave', showControls);

        video.addEventListener('contextmenu', (e) => e.preventDefault());
    });
});





