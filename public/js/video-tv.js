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
                html body #video-reference-mobile-header .video-reference-actions {
                    position: relative !important;
                    display: grid !important;
                    grid-template-columns: 1fr 1fr !important;
                    align-items: center !important;
                    gap: 0 !important;
                    width: 90px !important;
                    min-width: 90px !important;
                    max-width: 90px !important;
                    height: 38px !important;
                    min-height: 38px !important;
                    max-height: 38px !important;
                    margin: 0 !important;
                    padding: 2px 4px !important;
                    overflow: visible !important;
                    border: 0 !important;
                    border-radius: 9999px !important;
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    box-shadow: 0 7px 20px rgba(15, 23, 42, .055) !important;
                    pointer-events: auto !important;
                }

                html body #video-reference-mobile-header .video-reference-compose,
                html body #video-reference-mobile-header .video-reference-more,
                html body #video-reference-mobile-header .video-reference-account {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 100% !important;
                    min-width: 0 !important;
                    max-width: none !important;
                    height: 34px !important;
                    min-height: 34px !important;
                    max-height: 34px !important;
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

                html body #video-reference-mobile-header .video-reference-more-dots {
                    position: relative !important;
                    display: block !important;
                    width: 18px !important;
                    height: 5px !important;
                    color: #090909 !important;
                }

                html body #video-reference-mobile-header .video-reference-more-dots::before {
                    content: '' !important;
                    position: absolute !important;
                    top: .5px !important;
                    left: 0 !important;
                    right: auto !important;
                    display: block !important;
                    width: 4px !important;
                    height: 4px !important;
                    border-radius: 9999px !important;
                    background: currentColor !important;
                    box-shadow: 7px 0 0 currentColor, 14px 0 0 currentColor !important;
                }

                html body #video-reference-mobile-header .video-reference-more-dots::after {
                    display: none !important;
                    content: none !important;
                }

                html.dark body #video-reference-mobile-header .video-reference-actions,
                html body.dark #video-reference-mobile-header .video-reference-actions {
                    background: #111827 !important;
                    background-color: #111827 !important;
                    box-shadow: 0 7px 20px rgba(0, 0, 0, .24) !important;
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