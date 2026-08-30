(() => {
    const installVideoHeaderRightPill = () => {
        let style = document.getElementById('video-header-right-pill-fix');

        if (!style) {
            style = document.createElement('style');
            style.id = 'video-header-right-pill-fix';
            document.head.appendChild(style);
        }

        style.textContent = `
            @media (max-width: 767px) {
                html body #video-reference-mobile-header {
                    top: calc(12px + env(safe-area-inset-top, 0px)) !important;
                    height: 44px !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }

                html body #video-reference-mobile-header .video-mobile-left {
                    height: 44px !important;
                    gap: 7px !important;
                }

                html body #video-reference-mobile-header .video-mobile-sidebar-button {
                    position: relative !important;
                    isolation: isolate !important;
                    overflow: visible !important;
                    width: 44px !important;
                    min-width: 44px !important;
                    max-width: 44px !important;
                    height: 44px !important;
                    min-height: 44px !important;
                    max-height: 44px !important;
                    flex: 0 0 44px !important;
                    border-radius: 9999px !important;
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    box-shadow: 0 8px 24px rgba(15, 23, 42, .055) !important;
                }

                html body #video-reference-mobile-header .video-mobile-sidebar-button svg {
                    position: relative !important;
                    z-index: 2 !important;
                    width: 23px !important;
                    min-width: 23px !important;
                    height: 23px !important;
                    min-height: 23px !important;
                    stroke-width: 2.25 !important;
                }

                html body #video-reference-mobile-header .video-mobile-brand,
                html body #video-reference-mobile-header .video-mobile-brand.is-collapsed {
                    position: relative !important;
                    isolation: isolate !important;
                    overflow: visible !important;
                    width: 116px !important;
                    min-width: 116px !important;
                    max-width: 116px !important;
                    height: 44px !important;
                    min-height: 44px !important;
                    max-height: 44px !important;
                    padding: 0 10px 0 8px !important;
                    gap: 7px !important;
                    flex: 0 0 116px !important;
                    border-radius: 9999px !important;
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    box-shadow: 0 8px 24px rgba(15, 23, 42, .055) !important;
                }

                html body #video-reference-mobile-header .video-mobile-brand > * {
                    position: relative !important;
                    z-index: 2 !important;
                }

                html body #video-reference-mobile-header .video-mobile-brand-logo {
                    width: 29px !important;
                    min-width: 29px !important;
                    max-width: 29px !important;
                    height: 29px !important;
                    min-height: 29px !important;
                    max-height: 29px !important;
                    flex: 0 0 29px !important;
                }

                html body #video-reference-mobile-header .video-mobile-brand-word,
                html body #video-reference-mobile-header .video-mobile-brand.is-collapsed .video-mobile-brand-word {
                    display: inline-flex !important;
                    align-items: center !important;
                    max-width: none !important;
                    overflow: visible !important;
                    opacity: 1 !important;
                    font-size: 15.5px !important;
                    line-height: 1.3 !important;
                    font-weight: 600 !important;
                    white-space: nowrap !important;
                }

                html body #video-reference-mobile-header .video-reference-actions {
                    position: relative !important;
                    isolation: isolate !important;
                    overflow: visible !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                    gap: 2px !important;
                    width: 110px !important;
                    min-width: 110px !important;
                    max-width: 110px !important;
                    height: 44px !important;
                    min-height: 44px !important;
                    max-height: 44px !important;
                    flex: 0 0 110px !important;
                    margin: 0 !important;
                    padding: 2px 4px !important;
                    border: 0 !important;
                    border-radius: 9999px !important;
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    box-shadow: 0 8px 24px rgba(15, 23, 42, .055) !important;
                    pointer-events: auto !important;
                }

                html body #video-reference-mobile-header .video-reference-actions > * {
                    position: relative !important;
                    z-index: 2 !important;
                }

                html body #video-reference-mobile-header .video-reference-compose,
                html body #video-reference-mobile-header .video-reference-more,
                html body #video-reference-mobile-header .video-reference-account {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 48px !important;
                    min-width: 48px !important;
                    max-width: 48px !important;
                    height: 40px !important;
                    min-height: 40px !important;
                    max-height: 40px !important;
                    flex: 0 0 48px !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: 0 !important;
                    border-radius: 9999px !important;
                    background: transparent !important;
                    background-color: transparent !important;
                    color: #090909 !important;
                    box-shadow: none !important;
                    text-decoration: none !important;
                }

                html body #video-reference-mobile-header .video-reference-compose:hover,
                html body #video-reference-mobile-header .video-reference-compose:focus-visible,
                html body #video-reference-mobile-header .video-reference-compose:active,
                html body #video-reference-mobile-header .video-reference-more:hover,
                html body #video-reference-mobile-header .video-reference-more:focus-visible,
                html body #video-reference-mobile-header .video-reference-more:active,
                html body #video-reference-mobile-header .video-reference-more[aria-expanded="true"],
                html body #video-reference-mobile-header .video-reference-account:hover,
                html body #video-reference-mobile-header .video-reference-account:focus-visible,
                html body #video-reference-mobile-header .video-reference-account:active {
                    background: #f1f5f9 !important;
                    background-color: #f1f5f9 !important;
                }

                html body #video-reference-mobile-header .video-reference-compose svg {
                    width: 25px !important;
                    min-width: 25px !important;
                    height: 25px !important;
                    min-height: 25px !important;
                    stroke-width: 2.15 !important;
                }

                html body #video-reference-mobile-header .video-reference-more-dots {
                    position: relative !important;
                    display: block !important;
                    width: 20px !important;
                    height: 6px !important;
                    color: #090909 !important;
                }

                html body #video-reference-mobile-header .video-reference-more-dots::before {
                    content: '' !important;
                    position: absolute !important;
                    top: .5px !important;
                    left: 0 !important;
                    right: auto !important;
                    display: block !important;
                    width: 5px !important;
                    height: 5px !important;
                    border-radius: 9999px !important;
                    background: currentColor !important;
                    box-shadow: 7.5px 0 0 currentColor, 15px 0 0 currentColor !important;
                }

                html body #video-reference-mobile-header .video-reference-more-dots::after {
                    display: none !important;
                    content: none !important;
                }

                html body #video-reference-mobile-header .video-reference-avatar-image,
                html body #video-reference-mobile-header .video-reference-avatar-fallback {
                    width: 36px !important;
                    min-width: 36px !important;
                    max-width: 36px !important;
                    height: 36px !important;
                    min-height: 36px !important;
                    max-height: 36px !important;
                    font-size: 15px !important;
                }

                /* Eski görünümdeki gibi: beyaz kapsüllerin arkasında ince, aşağı doğru kaybolan blur. */
                html body #video-reference-mobile-header .video-mobile-sidebar-button::after,
                html body #video-reference-mobile-header .video-mobile-brand::after,
                html body #video-reference-mobile-header .video-reference-actions::after {
                    content: '' !important;
                    position: absolute !important;
                    left: -5px !important;
                    right: -5px !important;
                    top: 28px !important;
                    height: 34px !important;
                    z-index: 1 !important;
                    pointer-events: none !important;
                    border-radius: 999px !important;
                    background: linear-gradient(
                        180deg,
                        rgba(248, 246, 242, .78) 0%,
                        rgba(248, 246, 242, .56) 28%,
                        rgba(248, 246, 242, .30) 55%,
                        rgba(248, 246, 242, .12) 78%,
                        rgba(248, 246, 242, 0) 100%
                    ) !important;
                    filter: blur(8px) !important;
                    opacity: .92 !important;
                    transform: translateZ(0) !important;
                    -webkit-mask-image: linear-gradient(180deg, #000 0%, rgba(0,0,0,.82) 38%, rgba(0,0,0,.42) 70%, transparent 100%) !important;
                    mask-image: linear-gradient(180deg, #000 0%, rgba(0,0,0,.82) 38%, rgba(0,0,0,.42) 70%, transparent 100%) !important;
                }

                html body #video-reference-mobile-header .video-mobile-sidebar-button > *,
                html body #video-reference-mobile-header .video-mobile-brand > *,
                html body #video-reference-mobile-header .video-reference-actions > * {
                    position: relative !important;
                    z-index: 2 !important;
                }

                html.dark body #video-reference-mobile-header .video-mobile-sidebar-button,
                html.dark body #video-reference-mobile-header .video-mobile-brand,
                html.dark body #video-reference-mobile-header .video-reference-actions,
                html body.dark #video-reference-mobile-header .video-mobile-sidebar-button,
                html body.dark #video-reference-mobile-header .video-mobile-brand,
                html body.dark #video-reference-mobile-header .video-reference-actions {
                    background: #111827 !important;
                    background-color: #111827 !important;
                    color: #f8fafc !important;
                    box-shadow: 0 8px 24px rgba(0, 0, 0, .24) !important;
                }

                html.dark body #video-reference-mobile-header .video-reference-compose,
                html.dark body #video-reference-mobile-header .video-reference-more,
                html.dark body #video-reference-mobile-header .video-reference-account,
                html body.dark #video-reference-mobile-header .video-reference-compose,
                html body.dark #video-reference-mobile-header .video-reference-more,
                html body.dark #video-reference-mobile-header .video-reference-account {
                    background: transparent !important;
                    background-color: transparent !important;
                    color: #f8fafc !important;
                    box-shadow: none !important;
                }

                html.dark body #video-reference-mobile-header .video-mobile-sidebar-button::after,
                html.dark body #video-reference-mobile-header .video-mobile-brand::after,
                html.dark body #video-reference-mobile-header .video-reference-actions::after,
                html body.dark #video-reference-mobile-header .video-mobile-sidebar-button::after,
                html body.dark #video-reference-mobile-header .video-mobile-brand::after,
                html body.dark #video-reference-mobile-header .video-reference-actions::after {
                    background: linear-gradient(
                        180deg,
                        rgba(11, 18, 32, .78) 0%,
                        rgba(11, 18, 32, .56) 28%,
                        rgba(11, 18, 32, .30) 55%,
                        rgba(11, 18, 32, .12) 78%,
                        rgba(11, 18, 32, 0) 100%
                    ) !important;
                }

                html.dark body #video-reference-mobile-header .video-reference-compose:hover,
                html.dark body #video-reference-mobile-header .video-reference-compose:focus-visible,
                html.dark body #video-reference-mobile-header .video-reference-compose:active,
                html.dark body #video-reference-mobile-header .video-reference-more:hover,
                html.dark body #video-reference-mobile-header .video-reference-more:focus-visible,
                html.dark body #video-reference-mobile-header .video-reference-more:active,
                html.dark body #video-reference-mobile-header .video-reference-more[aria-expanded="true"],
                html.dark body #video-reference-mobile-header .video-reference-account:hover,
                html.dark body #video-reference-mobile-header .video-reference-account:focus-visible,
                html.dark body #video-reference-mobile-header .video-reference-account:active,
                html body.dark #video-reference-mobile-header .video-reference-compose:hover,
                html body.dark #video-reference-mobile-header .video-reference-compose:focus-visible,
                html body.dark #video-reference-mobile-header .video-reference-compose:active,
                html body.dark #video-reference-mobile-header .video-reference-more:hover,
                html body.dark #video-reference-mobile-header .video-reference-more:focus-visible,
                html body.dark #video-reference-mobile-header .video-reference-more:active,
                html body.dark #video-reference-mobile-header .video-reference-more[aria-expanded="true"],
                html body.dark #video-reference-mobile-header .video-reference-account:hover,
                html body.dark #video-reference-mobile-header .video-reference-account:focus-visible,
                html body.dark #video-reference-mobile-header .video-reference-account:active {
                    background: #1f2937 !important;
                    background-color: #1f2937 !important;
                }

                html.dark body #video-reference-mobile-header .video-reference-more-dots,
                html body.dark #video-reference-mobile-header .video-reference-more-dots {
                    color: #f8fafc !important;
                }
            }
        `;
    };

    const loadCore = () => {
        if (document.querySelector('script[data-video-tv-core]')) return;

        const script = document.createElement('script');
        script.src = '/js/video-tv-core.js?v=425';
        script.async = false;
        script.dataset.videoTvCore = '1';
        document.head.appendChild(script);
    };

    installVideoHeaderRightPill();
    loadCore();

    document.addEventListener('livewire:navigated', installVideoHeaderRightPill);
    window.addEventListener('pageshow', installVideoHeaderRightPill);
})();