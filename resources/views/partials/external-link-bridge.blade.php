<script>
    (() => {
        const bridgeUrl = @json(route('external.bridge'));
        const bridgeLocation = new URL(bridgeUrl, window.location.href);
        const currentHost = window.location.hostname.toLowerCase();

        const parseUrl = (value) => {
            try {
                return new URL(value, window.location.href);
            } catch (error) {
                return null;
            }
        };

        const shouldBridge = (url) => {
            if (!url || !['http:', 'https:'].includes(url.protocol)) return false;
            if (url.hostname.toLowerCase() === currentHost) return false;
            if (url.hostname.toLowerCase() === bridgeLocation.hostname.toLowerCase() && url.pathname === bridgeLocation.pathname) return false;

            return true;
        };

        const rewriteExternalLinks = (root = document) => {
            root.querySelectorAll('a[href]').forEach((link) => {
                if (!(link instanceof HTMLAnchorElement)) return;
                if (link.dataset.externalBridge === 'off' || link.dataset.externalBridge === '1') return;

                const targetUrl = parseUrl(link.getAttribute('href'));
                if (!shouldBridge(targetUrl)) return;

                const nextUrl = new URL(bridgeUrl, window.location.href);
                nextUrl.searchParams.set('url', targetUrl.href);

                link.href = nextUrl.href;
                link.dataset.externalBridge = '1';
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            rewriteExternalLinks();

            new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType !== Node.ELEMENT_NODE) return;
                        rewriteExternalLinks(node);
                    });
                });
            }).observe(document.documentElement, {
                childList: true,
                subtree: true,
            });
        });
    })();
</script>
