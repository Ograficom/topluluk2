<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base target="_blank">
    <style>
        html,
        body {
            width: 100%;
            min-width: 0;
            min-height: 100%;
            margin: 0;
            padding: 0;
            background: transparent;
            overflow: hidden;
        }

        .ad-frame-content {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .ad-frame-content iframe,
        .ad-frame-content img,
        .ad-frame-content ins {
            display: block;
            max-width: 100%;
            margin-inline: auto;
        }
    </style>
</head>
<body>
    <div class="ad-frame-content" data-ad-frame="{{ $slotKey }}">
        {!! $content !!}
    </div>

    <script>
        (function () {
            const slotKey = @json($slotKey);

            const sendHeight = function () {
                const content = document.querySelector('.ad-frame-content');
                const height = Math.max(
                    content ? content.scrollHeight : 0,
                    document.documentElement.scrollHeight,
                    document.body.scrollHeight
                );

                window.parent.postMessage({
                    type: 'alma-ad-frame-resize',
                    slotKey: slotKey,
                    height: height
                }, '*');
            };

            window.addEventListener('load', sendHeight);

            if ('ResizeObserver' in window) {
                const observer = new ResizeObserver(sendHeight);
                observer.observe(document.documentElement);
                observer.observe(document.body);
                const content = document.querySelector('.ad-frame-content');

                if (content) {
                    observer.observe(content);
                }
            }

            setTimeout(sendHeight, 100);
            setTimeout(sendHeight, 500);
            setTimeout(sendHeight, 1200);
        })();
    </script>
</body>
</html>
