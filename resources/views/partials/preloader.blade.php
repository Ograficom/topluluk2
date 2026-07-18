<style>
    html.ografi-preloading,
    html.ografi-preloading body {
        overflow: hidden !important;
    }

    .ografi-preloader {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: flex;
        justify-content: center;
        background: #eef2fb;
        opacity: 1;
        visibility: visible;
        padding: 16px;
    }

    .ografi-preloader.is-hiding {
        animation: ografiPreloaderOut 220ms ease forwards;
    }

    .ografi-preloader__shell {
        width: min(100%, 1228px);
        display: grid;
        grid-template-columns: 240px minmax(0, 656px) 304px;
        gap: 28px;
        align-items: start;
    }

    .ografi-preloader__side,
    .ografi-preloader__main,
    .ografi-preloader__right {
        min-width: 0;
    }

    .ografi-skeleton {
        position: relative;
        overflow: hidden;
        background: #ece2cd;
    }

    .ografi-skeleton::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-120%);
        background: linear-gradient(105deg, transparent 0%, rgba(255, 255, 255, .78) 45%, rgba(37, 99, 235, .12) 60%, transparent 82%);
        animation: ografiSkeletonWave 980ms ease-in-out infinite;
    }

    .ografi-preloader__nav {
        display: flex;
        flex-direction: column;
        gap: 18px;
        padding-top: 4px;
    }

    .ografi-preloader__nav-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        height: 34px;
    }

    .ografi-preloader__icon {
        width: 24px;
        height: 24px;
        border-radius: 999px;
    }

    .ografi-preloader__nav-line {
        width: 116px;
        height: 16px;
        border-radius: 999px;
    }

    .ografi-preloader__post {
        width: 100%;
        background: #ffffff;
        border-radius: 8px;
        padding: 20px;
    }

    .ografi-preloader__post-head {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr) 26px;
        gap: 10px;
        align-items: center;
        margin-bottom: 18px;
    }

    .ografi-preloader__avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
    }

    .ografi-preloader__name {
        width: 128px;
        height: 15px;
        border-radius: 999px;
        margin-bottom: 8px;
    }

    .ografi-preloader__meta {
        width: 92px;
        height: 12px;
        border-radius: 999px;
    }

    .ografi-preloader__dot {
        width: 24px;
        height: 24px;
        border-radius: 999px;
    }

    .ografi-preloader__title {
        height: 22px;
        border-radius: 999px;
        margin-bottom: 10px;
    }

    .ografi-preloader__title--short {
        width: 72%;
        margin-bottom: 20px;
    }

    .ografi-preloader__media {
        width: 100%;
        height: 348px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .ografi-preloader__text-line {
        height: 15px;
        border-radius: 999px;
        margin-bottom: 10px;
    }

    .ografi-preloader__text-line:nth-of-type(1) {
        width: 92%;
    }

    .ografi-preloader__text-line:nth-of-type(2) {
        width: 86%;
    }

    .ografi-preloader__text-line:nth-of-type(3) {
        width: 62%;
    }

    .ografi-preloader__right-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .ografi-preloader__right-row {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        padding: 10px 0;
    }

    .ografi-preloader__right-avatar {
        width: 34px;
        height: 34px;
        border-radius: 999px;
    }

    .ografi-preloader__right-line {
        height: 13px;
        border-radius: 999px;
        margin-bottom: 7px;
    }

    .ografi-preloader__right-line:last-child {
        width: 62%;
        margin-bottom: 0;
    }

    .ografi-preloader__tag {
        height: 16px;
        border-radius: 999px;
        margin: 14px 0;
    }

    .ografi-preloader__tag:nth-child(1) {
        width: 72%;
    }

    .ografi-preloader__tag:nth-child(2) {
        width: 88%;
    }

    .ografi-preloader__tag:nth-child(3) {
        width: 64%;
    }

    html.dark .ografi-preloader {
        background: #09090b;
    }

    html.dark .ografi-preloader__post,
    html.dark .ografi-preloader__right-card {
        background: #18181b;
    }

    html.dark .ografi-skeleton {
        background: #27272a;
    }

    html.dark .ografi-skeleton::after {
        background: linear-gradient(105deg, transparent 0%, rgba(255, 255, 255, .14) 45%, transparent 82%);
    }

    @keyframes ografiSkeletonWave {
        to {
            transform: translateX(120%);
        }
    }

    @keyframes ografiPreloaderOut {
        to {
            opacity: 0;
            visibility: hidden;
        }
    }

    @media (max-width: 1180px) {
        .ografi-preloader__shell {
            grid-template-columns: minmax(0, 656px) 304px;
            width: min(100%, 988px);
        }

        .ografi-preloader__side {
            display: none;
        }
    }

    @media (max-width: 960px) {
        .ografi-preloader {
            padding: 12px;
        }

        .ografi-preloader__shell {
            display: block;
            width: min(100%, 656px);
        }

        .ografi-preloader__right {
            display: none;
        }

        .ografi-preloader__post {
            padding: 16px;
        }

        .ografi-preloader__media {
            height: clamp(220px, 55vw, 348px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ografi-skeleton::after {
            animation: none;
            display: none;
        }

        .ografi-preloader.is-hiding {
            animation-duration: 1ms;
        }
    }
</style>

<script>
    document.documentElement.classList.add('ografi-preloading');
</script>

<div id="ografiPreloader" class="ografi-preloader" role="status" aria-live="polite" aria-label="Sayfa yukleniyor">
    <div class="ografi-preloader__shell" aria-hidden="true">
        <aside class="ografi-preloader__side">
            <div class="ografi-preloader__nav">
                @for ($i = 0; $i < 9; $i++)
                    <div class="ografi-preloader__nav-item">
                        <span class="ografi-skeleton ografi-preloader__icon"></span>
                        <span class="ografi-skeleton ografi-preloader__nav-line"></span>
                    </div>
                @endfor
            </div>
        </aside>

        <main class="ografi-preloader__main">
            <div class="ografi-preloader__post">
                <div class="ografi-preloader__post-head">
                    <span class="ografi-skeleton ografi-preloader__avatar"></span>
                    <span>
                        <span class="ografi-skeleton ografi-preloader__name"></span>
                        <span class="ografi-skeleton ografi-preloader__meta"></span>
                    </span>
                    <span class="ografi-skeleton ografi-preloader__dot"></span>
                </div>
                <div class="ografi-skeleton ografi-preloader__title"></div>
                <div class="ografi-skeleton ografi-preloader__title ografi-preloader__title--short"></div>
                <div class="ografi-skeleton ografi-preloader__media"></div>
                <div class="ografi-skeleton ografi-preloader__text-line"></div>
                <div class="ografi-skeleton ografi-preloader__text-line"></div>
                <div class="ografi-skeleton ografi-preloader__text-line"></div>
            </div>
        </main>

        <aside class="ografi-preloader__right">
            <div class="ografi-preloader__right-card">
                @for ($i = 0; $i < 4; $i++)
                    <div class="ografi-preloader__right-row">
                        <span class="ografi-skeleton ografi-preloader__right-avatar"></span>
                        <span>
                            <span class="ografi-skeleton ografi-preloader__right-line"></span>
                            <span class="ografi-skeleton ografi-preloader__right-line"></span>
                        </span>
                    </div>
                @endfor
            </div>

            <div class="ografi-preloader__right-card">
                <div class="ografi-skeleton ografi-preloader__tag"></div>
                <div class="ografi-skeleton ografi-preloader__tag"></div>
                <div class="ografi-skeleton ografi-preloader__tag"></div>
            </div>
        </aside>
    </div>
</div>

<script>
    (function () {
        var startedAt = Date.now();
        var minVisibleMs = 520;
        var maxVisibleMs = 2400;
        var preloader = document.getElementById('ografiPreloader');

        function hidePreloader() {
            if (!preloader || preloader.dataset.done === '1') {
                return;
            }

            preloader.dataset.done = '1';

            var elapsed = Date.now() - startedAt;
            var delay = Math.max(0, minVisibleMs - elapsed);

            window.setTimeout(function () {
                preloader.classList.add('is-hiding');
                document.documentElement.classList.remove('ografi-preloading');

                window.setTimeout(function () {
                    if (preloader && preloader.parentNode) {
                        preloader.parentNode.removeChild(preloader);
                    }
                }, 260);
            }, delay);
        }

        if (document.readyState === 'complete') {
            hidePreloader();
        } else {
            window.addEventListener('load', hidePreloader, { once: true });
            window.setTimeout(hidePreloader, maxVisibleMs);
        }
    })();
</script>

<noscript>
    <style>
        .ografi-preloader {
            display: none !important;
        }
    </style>
</noscript>
