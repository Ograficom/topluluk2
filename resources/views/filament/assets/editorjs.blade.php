<script>
    (() => {
        const deps = [
            "{{ asset('vendor/editorjs/editorjs.umd.js') }}",
            "{{ asset('vendor/editorjs/header.umd.js') }}",
            "{{ asset('vendor/editorjs/list.umd.js') }}",
            "{{ asset('vendor/editorjs/quote.umd.js') }}",
            "{{ asset('vendor/editorjs/table.umd.js') }}",
            "{{ asset('vendor/editorjs/image.umd.js') }}",
            "{{ asset('vendor/editorjs/checklist.umd.js') }}",
            "{{ asset('vendor/editorjs/code.umd.js') }}",
            "{{ asset('vendor/editorjs/delimiter.umd.js') }}",
            "{{ asset('vendor/editorjs/embed.umd.js') }}",
            "{{ asset('vendor/editorjs/link.umd.js') }}",
            "{{ asset('vendor/editorjs/inline-code.umd.js') }}",
            "{{ asset('vendor/editorjs/marker.umd.js') }}",
        ];

        function ensureGlobals() {
            if (!window.List && window.EditorjsList) {
                window.List = window.EditorjsList;
            }
        }

        function loadDeps() {
            const hasDeps = () =>
                window.EditorJS &&
                window.Header &&
                (window.List || window.EditorjsList) &&
                window.Quote &&
                window.Table &&
                window.ImageTool &&
                window.Checklist &&
                window.CodeTool &&
                window.Delimiter &&
                window.Embed &&
                window.LinkTool &&
                window.InlineCode &&
                window.Marker;

            if (hasDeps()) {
                ensureGlobals();
                return Promise.resolve();
            }

            return Promise.all(
                deps.map(
                    (src) =>
                        new Promise((resolve) => {
                            if (document.querySelector(`script[src="${src}"]`)) return resolve();
                            const script = document.createElement('script');
                            script.src = src;
                            script.defer = true;
                            script.onload = resolve;
                            script.onerror = resolve;
                            document.head.appendChild(script);
                        })
                )
            ).then(() => {
                ensureGlobals();
            });
        }

        window.filamentEditorBlocksToHtml =
            window.filamentEditorBlocksToHtml ||
            function (blocks = []) {
                const escapeHtml = (str) =>
                    (str || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\"/g, '&quot;')
                        .replace(/'/g, '&#039;');

                const safeUrl = (url) => {
                    try {
                        const parsed = new URL(url, window.location.origin);
                        if (!['http:', 'https:'].includes(parsed.protocol)) return null;
                        return parsed.toString();
                    } catch {
                        return null;
                    }
                };

                const extractUrlFromEmbedInput = (value) => {
                    const text = String(value || '').trim();
                    if (!text) return '';
                    const srcMatch = text.match(/\bsrc\s*=\s*(?:"(https?:\/\/[^"]+)"|'(https?:\/\/[^']+)'|(https?:\/\/[^\s>]+))/i);
                    if (srcMatch) return srcMatch[1] || srcMatch[2] || srcMatch[3] || '';
                    const urlMatch = text.match(/https?:\/\/[^\s"'<>]+/i);
                    const url = urlMatch?.[0] || text;
                    if (urlMatch && url !== text) return text;
                    return /^https?:\/\//i.test(url) ? url : `https://${url.replace(/^\/+/, '')}`;
                };

                const isAllowedEmbedHost = (hostname) => {
                    const host = String(hostname || '').toLowerCase();
                    const allowed = [
                        'youtube.com',
                        'www.youtube.com',
                        'youtube-nocookie.com',
                        'www.youtube-nocookie.com',
                        'instagram.com',
                        'www.instagram.com',
                        'tiktok.com',
                        'www.tiktok.com',
                        'player.vimeo.com',
                        'vimeo.com',
                        'www.vimeo.com',
                        'dailymotion.com',
                        'www.dailymotion.com',
                        'player.twitch.tv',
                        'twitch.tv',
                        'www.twitch.tv',
                        'clips.twitch.tv',
                        'facebook.com',
                        'www.facebook.com',
                        'fb.watch',
                        'twitter.com',
                        'www.twitter.com',
                        'x.com',
                        'www.x.com',
                        'twitframe.com',
                        'vine.co',
                    ];

                    return allowed.includes(host);
                };

                const safeSocialEmbedUrl = (url) => {
                    const safe = safeUrl(url);
                    if (!safe) return null;

                    try {
                        const parsed = new URL(safe);
                        if (!isAllowedEmbedHost(parsed.hostname)) return null;
                        return parsed.toString();
                    } catch {
                        return null;
                    }
                };

                const socialEmbedFrameStyle = (url) => {
                    try {
                        const host = new URL(url).hostname.toLowerCase();
                        if (host.includes('tiktok.com') || host.includes('instagram.com')) {
                            return 'aspect-ratio: 9 / 16; max-width: 425px; margin-left: auto; margin-right: auto;';
                        }
                    } catch {}

                    return 'aspect-ratio: 16 / 9;';
                };

                const mentionUrlBase = @js(rtrim(url('/u'), '/'));

                const linkifyMentionNodes = (root) => {
                    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
                    const textNodes = [];

                    while (walker.nextNode()) {
                        const node = walker.currentNode;
                        if (!node?.nodeValue || !node.parentElement) {
                            continue;
                        }

                        if (node.parentElement.closest('a, code, pre, script, style, textarea')) {
                            continue;
                        }

                        textNodes.push(node);
                    }

                    textNodes.forEach((node) => {
                        const text = node.nodeValue || '';
                        const regex = /(^|[^A-Za-z0-9._%+\-])@([A-Za-z0-9._-]{2,255})/g;
                        const fragment = document.createDocumentFragment();
                        let cursor = 0;
                        let matched = false;
                        let match;

                        while ((match = regex.exec(text)) !== null) {
                            matched = true;
                            const prefix = match[1] || '';
                            const username = match[2] || '';
                            const matchStart = match.index;
                            const mentionStart = matchStart + prefix.length;

                            fragment.append(document.createTextNode(text.slice(cursor, matchStart)));
                            if (prefix) {
                                fragment.append(document.createTextNode(prefix));
                            }

                            const anchor = document.createElement('a');
                            anchor.href = `${mentionUrlBase}/${encodeURIComponent(username)}`;
                            anchor.className = 'mention-link';
                            anchor.setAttribute('data-mentioned-user', username);
                            anchor.textContent = `@${username}`;
                            fragment.append(anchor);

                            cursor = mentionStart + username.length + 1;
                        }

                        if (!matched) {
                            return;
                        }

                        fragment.append(document.createTextNode(text.slice(cursor)));
                        node.parentNode?.replaceChild(fragment, node);
                    });
                };

                const sanitizeInlineHtml = (html) => {
                    const template = document.createElement('template');
                    template.innerHTML = String(html || '');

                    const allowedTags = new Set(['B', 'STRONG', 'I', 'EM', 'U', 'S', 'A', 'MARK', 'CODE', 'BR', 'SPAN']);
                    const walker = document.createTreeWalker(template.content, NodeFilter.SHOW_ELEMENT);
                    const nodes = [];
                    while (walker.nextNode()) nodes.push(walker.currentNode);

                    nodes.forEach((el) => {
                        if (!allowedTags.has(el.tagName)) {
                            el.replaceWith(...Array.from(el.childNodes));
                            return;
                        }

                        Array.from(el.attributes).forEach((attr) => {
                            const name = attr.name.toLowerCase();
                            if (name.startsWith('on')) {
                                el.removeAttribute(attr.name);
                                return;
                            }

                            if (el.tagName === 'A') {
                                if (name === 'href') {
                                    const safe = safeUrl(attr.value);
                                    if (!safe) el.removeAttribute('href');
                                    else el.setAttribute('href', safe);
                                    el.setAttribute('rel', 'nofollow noopener noreferrer');
                                    el.setAttribute('target', '_blank');
                                    return;
                                }

                                if (name === 'rel' || name === 'target') return;
                                el.removeAttribute(attr.name);
                                return;
                            }

                            if (el.tagName === 'MARK' || el.tagName === 'CODE') {
                                if (name === 'class') return;
                                el.removeAttribute(attr.name);
                                return;
                            }

                            el.removeAttribute(attr.name);
                        });
                    });

                    linkifyMentionNodes(template.content);

                    return template.innerHTML;
                };

                return blocks
                    .map((block) => {
                        const data = block.data || {};

                        switch (block.type) {
                            case 'header': {
                                const level = Math.min(Math.max(data.level || 2, 1), 6);
                                return `<h${level} class="mt-4 mb-2 font-semibold">${sanitizeInlineHtml(data.text)}</h${level}>`;
                            }
                            case 'quote':
                                return `<blockquote class="border-l-4 pl-3 italic text-slate-700">${sanitizeInlineHtml(
                                    data.text
                                )}${data.caption ? `<footer class="mt-1 text-sm text-slate-500">â€” ${sanitizeInlineHtml(data.caption)}</footer>` : ''}</blockquote>`;
                            case 'list': {
                                if (!Array.isArray(data.items)) return '';
                                const tag = data.style === 'ordered' ? 'ol' : 'ul';
                                const items = data.items
                                    .map((item) => `<li class="ml-5 list-disc">${sanitizeInlineHtml(item)}</li>`)
                                    .join('');
                                return `<${tag} class="my-2 space-y-1">${items}</${tag}>`;
                            }
                            case 'checklist': {
                                const items = Array.isArray(data.items) ? data.items : [];
                                const html = items
                                    .map((item) => {
                                        const checked = item?.checked ? 'checked' : '';
                                        return `
                                            <label class="flex items-start gap-2">
                                                <input type="checkbox" disabled ${checked} class="mt-1 h-4 w-4">
                                                <span>${sanitizeInlineHtml(item?.text || '')}</span>
                                            </label>
                                        `;
                                    })
                                    .join('');
                                return `<div class="my-2 space-y-2">${html}</div>`;
                            }
                            case 'table': {
                                const rows = Array.isArray(data.content) ? data.content : [];
                                const body = rows
                                    .map((row) => {
                                        const cols = Array.isArray(row) ? row : [];
                                        const cells = cols
                                            .map(
                                                (cell) =>
                                                    `<td class="border px-2 py-1 align-top">${sanitizeInlineHtml(
                                                        cell
                                                    )}</td>`
                                            )
                                            .join('');
                                        return `<tr>${cells}</tr>`;
                                    })
                                    .join('');
                                return `<div class="my-3 overflow-auto"><table class="w-full text-sm">${body}</table></div>`;
                            }
                            case 'image': {
                                const url = safeUrl(data.file?.url);
                                if (!url) return '';
                                const caption = data.caption
                                    ? `<figcaption class="mt-2 text-sm text-slate-500">${sanitizeInlineHtml(data.caption)}</figcaption>`
                                    : '';
                                return `<figure class="my-4"><img src="${escapeHtml(url)}" alt="" class="max-w-full rounded-lg" />${caption}</figure>`;
                            }
                            case 'code':
                                return `<pre class="my-3 overflow-auto rounded-lg bg-slate-950/5 p-3 text-xs text-slate-800"><code>${escapeHtml(
                                    data.code || ''
                                )}</code></pre>`;
                            case 'delimiter':
                                return `<hr class="my-6" />`;
                            case 'embed': {
                                const embedUrl = safeSocialEmbedUrl(extractUrlFromEmbedInput(data.embed));
                                if (!embedUrl) return '';
                                return `
                                    <div class="my-4">
                                        <div class="w-full overflow-hidden rounded-xl bg-black/5" style="${socialEmbedFrameStyle(embedUrl)}">
                                            <iframe
                                                class="h-full w-full"
                                                src="${escapeHtml(embedUrl)}"
                                                loading="lazy"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen
                                            ></iframe>
                                        </div>
                                    </div>
                                `;
                            }
                            case 'socialEmbed': {
                                const embedUrl = safeSocialEmbedUrl(extractUrlFromEmbedInput(data.src));
                                if (!embedUrl) return '';
                                return `
                                    <div class="my-4">
                                        <div class="w-full overflow-hidden rounded-xl bg-black/5" style="${socialEmbedFrameStyle(embedUrl)}">
                                            <iframe
                                                class="h-full w-full"
                                                src="${escapeHtml(embedUrl)}"
                                                loading="lazy"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen
                                            ></iframe>
                                        </div>
                                    </div>
                                `;
                            }
                            case 'linkTool': {
                                const link = safeUrl(data.link);
                                if (!link) return '';
                                const title = data.meta?.title ? sanitizeInlineHtml(data.meta.title) : escapeHtml(link);
                                return `<p class="my-2"><a class="text-amber-700 underline" href="${escapeHtml(
                                    link
                                )}" target="_blank" rel="nofollow noopener noreferrer">${title}</a></p>`;
                            }
                            case 'downloadButton': {
                                const url = safeUrl(data.url);
                                if (!url) return '';
                                const label = sanitizeInlineHtml(data.text || 'Indir');
                                return `
                                    <div class="my-3">
                                        <a
                                            href="${escapeHtml(url)}"
                                            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                                            target="_blank"
                                            rel="nofollow noopener noreferrer"
                                        >
                                            ${label}
                                        </a>
                                    </div>
                                `;
                            }
                            case 'warning':
                            case 'infoBox':
                            case 'successBox':
                            case 'tipBox':
                            case 'noteBox': {
                                const noticeMap = {
                                    warning: {
                                        surface: 'border-amber-300 bg-amber-50 text-amber-900',
                                        badge: 'bg-amber-500/15 text-amber-700',
                                        label: 'Uyari',
                                    },
                                    infoBox: {
                                        surface: 'border-gray-300 bg-gray-50 text-gray-900',
                                        badge: 'bg-gray-500/15 text-gray-700',
                                        label: 'Bilgi',
                                    },
                                    successBox: {
                                        surface: 'border-emerald-300 bg-emerald-50 text-emerald-900',
                                        badge: 'bg-emerald-500/15 text-emerald-700',
                                        label: 'Basarili',
                                    },
                                    tipBox: {
                                        surface: 'border-violet-300 bg-violet-50 text-violet-900',
                                        badge: 'bg-violet-500/15 text-violet-700',
                                        label: 'Ipucu',
                                    },
                                    noteBox: {
                                        surface: 'border-slate-300 bg-slate-50 text-slate-900',
                                        badge: 'bg-slate-500/15 text-slate-700',
                                        label: 'Not',
                                    },
                                };
                                const preset = noticeMap[block.type] || noticeMap.noteBox;
                                const title = sanitizeInlineHtml(data.title || preset.label);
                                const message = sanitizeInlineHtml(data.message || '');
                                return `
                                    <div class="my-4 rounded-2xl border p-4 ${preset.surface}">
                                        <div class="mb-2 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] ${preset.badge}">${title}</div>
                                        <div class="text-sm leading-6">${message}</div>
                                    </div>
                                `;
                            }
                            case 'faq': {
                                const question = sanitizeInlineHtml(data.question || 'Soru');
                                const answer = sanitizeInlineHtml(data.answer || '');
                                return `
                                    <details class="my-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                                        <summary class="cursor-pointer text-sm font-semibold text-slate-900">${question}</summary>
                                        <div class="mt-3 text-sm leading-6 text-slate-700">${answer}</div>
                                    </details>
                                `;
                            }
                            case 'steps': {
                                const items = Array.isArray(data.items) ? data.items : [];
                                const title = data.title ? `<h3 class="mb-3 text-base font-semibold">${sanitizeInlineHtml(data.title)}</h3>` : '';
                                const list = items
                                    .filter((item) => String(item || '').trim() !== '')
                                    .map(
                                        (item, index) => `
                                            <li class="flex gap-3">
                                                <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">${index + 1}</span>
                                                <span class="pt-0.5">${sanitizeInlineHtml(item)}</span>
                                            </li>
                                        `
                                    )
                                    .join('');
                                return `<div class="my-4 rounded-2xl border border-slate-200 bg-white/80 p-4">${title}<ol class="space-y-3 text-sm text-slate-700">${list}</ol></div>`;
                            }
                            case 'prosCons': {
                                const pros = Array.isArray(data.pros) ? data.pros : [];
                                const cons = Array.isArray(data.cons) ? data.cons : [];
                                const renderColumn = (title, items, tone) => `
                                    <div class="rounded-2xl border p-4 ${tone}">
                                        <div class="mb-3 text-sm font-semibold">${title}</div>
                                        <ul class="space-y-2 text-sm leading-6">
                                            ${items
                                                .filter((item) => String(item || '').trim() !== '')
                                                .map((item) => `<li>${sanitizeInlineHtml(item)}</li>`)
                                                .join('')}
                                        </ul>
                                    </div>
                                `;
                                return `
                                    <div class="my-4 grid gap-3 md:grid-cols-2">
                                        ${renderColumn('Artılar', pros, 'border-emerald-200 bg-emerald-50 text-emerald-900')}
                                        ${renderColumn('Eksiler', cons, 'border-rose-200 bg-rose-50 text-rose-900')}
                                    </div>
                                `;
                            }
                            case 'statsCard': {
                                const value = sanitizeInlineHtml(data.value || '0');
                                const label = sanitizeInlineHtml(data.label || 'Baslik');
                                const note = data.note ? `<div class="mt-2 text-xs text-slate-500">${sanitizeInlineHtml(data.note)}</div>` : '';
                                return `
                                    <div class="my-4 rounded-2xl border border-slate-200 bg-white/90 p-5 text-center shadow-sm">
                                        <div class="text-3xl font-black tracking-tight text-slate-900">${value}</div>
                                        <div class="mt-2 text-sm font-medium text-slate-700">${label}</div>
                                        ${note}
                                    </div>
                                `;
                            }
                            case 'ctaBox': {
                                const url = safeUrl(data.url);
                                const label = sanitizeInlineHtml(data.label || 'Devam et');
                                const title = data.title ? `<h3 class="text-base font-semibold text-slate-900">${sanitizeInlineHtml(data.title)}</h3>` : '';
                                const text = data.text ? `<p class="mt-2 text-sm leading-6 text-slate-600">${sanitizeInlineHtml(data.text)}</p>` : '';
                                const action = url
                                    ? `<a href="${escapeHtml(url)}" class="mt-4 inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" target="_blank" rel="nofollow noopener noreferrer">${label}</a>`
                                    : '';
                                return `<div class="my-4 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5">${title}${text}${action}</div>`;
                            }
                            case 'audioNarration': {
                                const title = data.title ? `<div class="text-sm font-semibold text-slate-900">${sanitizeInlineHtml(data.title)}</div>` : '';
                                const text = sanitizeInlineHtml(data.text || '');
                                const buttonLabel = sanitizeInlineHtml(data.buttonLabel || 'Sesli oku');
                                const rate = Number(data.rate || 1);
                                const pitch = Number(data.pitch || 1);
                                return `
                                    <div class="my-4 rounded-2xl border border-slate-200 bg-white/90 p-4">
                                        ${title}
                                        <div class="mt-2 text-sm leading-6 text-slate-700">${text}</div>
                                        <button
                                            type="button"
                                            class="mt-4 inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                                            data-audio-read-btn
                                            data-audio-read-text="${escapeHtml(data.text || '')}"
                                            data-audio-read-rate="${escapeHtml(String(rate))}"
                                            data-audio-read-pitch="${escapeHtml(String(pitch))}"
                                            data-audio-read-lang="${escapeHtml(data.lang || 'tr-TR')}"
                                        >${buttonLabel}</button>
                                    </div>
                                `;
                            }
                            case 'paragraph':
                                return `<p class="my-2 leading-7">${sanitizeInlineHtml(data.text)}</p>`;
                            case 'video': {
                                const url = safeUrl(data.url);
                                if (!url) return '';
                                const caption = data.caption
                                    ? `<figcaption class="mt-2 text-sm text-slate-500">${sanitizeInlineHtml(data.caption)}</figcaption>`
                                    : '';
                                const subtitles = Array.isArray(data.subtitles) ? data.subtitles : [];
                                const trackTags = subtitles
                                    .filter((entry) => entry?.url)
                                    .map(
                                        (entry) =>
                                            `<track kind="subtitles" src="${escapeHtml(entry.url)}" srclang="${escapeHtml(
                                                entry.lang || 'tr'
                                            )}" label="${escapeHtml(entry.label || 'Altyazı')}" />`
                                    )
                                    .join('');
                                return `
                                    <figure class="my-4">
                                        <video
                                            class="my-3 w-full rounded-xl bg-slate-900"
                                            controls
                                            playsinline
                                            preload="metadata"
                                        >
                                            ${trackTags}
                                            <source src="${escapeHtml(url)}" />
                                            Video yüklenemedi veya tarayıcı desteklemiyor.
                                        </video>
                                        ${caption}
                                    </figure>
                                `;
                            }
                            case 'poll':
                                return '';
                            default:
                                return `<p class="my-2 leading-7">${sanitizeInlineHtml(data.text)}</p>`;
                        }
                    })
                    .join('');
            };

        window.initFilamentEditorJsField = async function (root) {
            const holder = root.querySelector('[x-ref="holder"]');
            const form = root.closest('form');
            const htmlField = form?.querySelector('[data-editor-content]');
            const jsonField = form?.querySelector('[data-editor-json]');

            if (!holder || !htmlField || !jsonField) return;

            await loadDeps();

            if (typeof EditorJS === 'undefined') {
                console.error('EditorJS could not be loaded.');
                return;
            }

            let initialData = { blocks: [] };
            try {
                if (jsonField.value) {
                    initialData = JSON.parse(jsonField.value);
                } else if (htmlField.value) {
                    initialData = { blocks: [{ type: 'paragraph', data: { text: htmlField.value } }] };
                }
            } catch {
                initialData = { blocks: [] };
            }

            const normalizeEditorBlocks = (payload) => {
                const data = payload && typeof payload === 'object' ? payload : { blocks: [] };
                const blocks = Array.isArray(data.blocks) ? data.blocks : [];
                const allowedTypes = new Set([
                    'paragraph',
                    'header',
                    'list',
                    'quote',
                    'checklist',
                    'table',
                    'code',
                    'delimiter',
                    'embed',
                    'video',
                    'socialEmbed',
                    'poll',
                    'downloadButton',
                    'warning',
                    'infoBox',
                    'successBox',
                    'tipBox',
                    'noteBox',
                    'faq',
                    'steps',
                    'prosCons',
                    'statsCard',
                    'ctaBox',
                    'audioNarration',
                    'image',
                ]);

                const parseMaybeJson = (value) => {
                    if (typeof value !== 'string') return value;
                    const trimmed = value.trim();
                    if (!trimmed || (!trimmed.startsWith('{') && !trimmed.startsWith('['))) return value;
                    try {
                        return JSON.parse(trimmed);
                    } catch {
                        return value;
                    }
                };

                const readText = (input) => {
                    const parsed = parseMaybeJson(input);
                    if (typeof parsed === 'string') return parsed;
                    if (parsed && typeof parsed === 'object') {
                        if (typeof parsed.text === 'string') return parsed.text;
                        if (typeof parsed.content === 'string') return parsed.content;
                        if (typeof parsed.html === 'string') return parsed.html;
                    }
                    return '';
                };

                const readVideoUrl = (input) => {
                    const parsed = parseMaybeJson(input);
                    if (typeof parsed === 'string') return parsed.trim();
                    if (parsed && typeof parsed === 'object') {
                        const candidates = [
                            parsed.url,
                            parsed.src,
                            parsed.video,
                            parsed.file?.url,
                            parsed.file?.src,
                            parsed.data?.url,
                            parsed.data?.src,
                            parsed.data?.file?.url,
                            parsed.data?.file?.src,
                        ];
                        const found = candidates.find((v) => typeof v === 'string' && v.trim() !== '');
                        return typeof found === 'string' ? found.trim() : '';
                    }
                    return '';
                };

                data.blocks = blocks
                    .map((block) => {
                        if (!block || typeof block !== 'object') return null;
                        const type = String(block.type || '');
                        if (!allowedTypes.has(type)) {
                            return null;
                        }
                        const raw = block.data;
                        const normalized = {
                            ...block,
                            data: raw && typeof raw === 'object'
                                ? { ...raw }
                                : (typeof raw === 'string' ? { text: raw } : {}),
                        };

                        if (type === 'paragraph') {
                            const text = readText(normalized.data?.text ?? raw);
                            const safeText = String(text || '').trim();
                            if (safeText === '') {
                                return null;
                            }
                            normalized.data = { text: safeText };
                        }

                        if (type === 'video') {
                            const legacyUrl = readVideoUrl(normalized.data ?? raw);
                            const subtitles = Array.isArray(normalized.data?.subtitles)
                                ? normalized.data.subtitles
                                      .filter((entry) => entry && typeof entry === 'object' && typeof entry.url === 'string')
                                      .map((entry) => ({
                                          url: entry.url,
                                          lang: entry.lang || 'tr',
                                          label: entry.label || 'Altyazı',
                                          default: Boolean(entry.default),
                                      }))
                                : [];

                            normalized.data = {
                                url: legacyUrl,
                                subtitles: subtitles,
                            };
                            normalized.data.url = normalized.data.url.trim();
                            if (!normalized.data.url) {
                                return null;
                            }
                        }

                        return { type: normalized.type, data: normalized.data };
                    })
                    .filter((block) => block !== null);

                return data;
            };

            initialData = normalizeEditorBlocks(initialData);

            if (root.__editorInstance) {
                try {
                    await root.__editorInstance.destroy();
                } catch {
                    // ignore
                }
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const createField = ({ tag = 'input', type = 'text', value = '', placeholder = '', rows = 4 } = {}) => {
                const field = document.createElement(tag);
                field.className = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200';
                if (tag === 'textarea') {
                    field.rows = rows;
                    field.value = value || '';
                } else {
                    field.type = type;
                    field.value = value || '';
                }
                if (placeholder) {
                    field.placeholder = placeholder;
                }
                return field;
            };

            const createBlockShell = (title, description) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-3';

                const header = document.createElement('div');
                header.className = 'space-y-1';

                const heading = document.createElement('div');
                heading.className = 'text-sm font-semibold text-slate-900';
                heading.textContent = title;

                const desc = document.createElement('p');
                desc.className = 'text-xs text-slate-500';
                desc.textContent = description;

                header.append(heading, desc);
                wrapper.append(header);

                return wrapper;
            };

            const linesToItems = (value) =>
                String(value || '')
                    .split('\n')
                    .map((item) => item.trim())
                    .filter(Boolean);

            const createNoticeTool = ({ type, title, description, defaultTitle }) =>
                class {
                    static get toolbox() {
                        return { title, icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>' };
                    }

                    constructor({ data }) {
                        this.data = data || {};
                    }

                    render() {
                        const wrapper = createBlockShell(title, description);
                        this.titleInput = createField({
                            value: this.data.title || defaultTitle,
                            placeholder: 'Baslik',
                        });
                        this.messageInput = createField({
                            tag: 'textarea',
                            value: this.data.message || '',
                            placeholder: 'Mesaj',
                            rows: 5,
                        });
                        wrapper.append(this.titleInput, this.messageInput);
                        return wrapper;
                    }

                    save() {
                        return {
                            type,
                            title: (this.titleInput?.value || '').trim(),
                            message: (this.messageInput?.value || '').trim(),
                        };
                    }

                    validate(savedData) {
                        return Boolean(savedData?.title || savedData?.message);
                    }
                };

            class FaqBlock {
                static get toolbox() {
                    return { title: 'SSS', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="10"></circle><path d="M9.1 9a3 3 0 1 1 5.8 1c0 2-3 2-3 4"></path><path d="M12 17h.01"></path></svg>' };
                }

                constructor({ data }) {
                    this.data = data || {};
                }

                render() {
                    const wrapper = createBlockShell('SSS', 'Soru-cevap bolumu ekle.');
                    this.questionInput = createField({ value: this.data.question || '', placeholder: 'Soru' });
                    this.answerInput = createField({ tag: 'textarea', value: this.data.answer || '', placeholder: 'Cevap', rows: 5 });
                    wrapper.append(this.questionInput, this.answerInput);
                    return wrapper;
                }

                save() {
                    return {
                        question: (this.questionInput?.value || '').trim(),
                        answer: (this.answerInput?.value || '').trim(),
                    };
                }
            }

            class StepsBlock {
                static get toolbox() {
                    return { title: 'Adimlar', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 6h11"></path><path d="M9 12h11"></path><path d="M9 18h11"></path><path d="M4 6h.01"></path><path d="M4 12h.01"></path><path d="M4 18h.01"></path></svg>' };
                }

                constructor({ data }) {
                    this.data = data || {};
                }

                render() {
                    const wrapper = createBlockShell('Adimlar', 'Her satir bir adim olacak sekilde sirali liste ekle.');
                    this.titleInput = createField({ value: this.data.title || '', placeholder: 'Baslik (opsiyonel)' });
                    this.itemsInput = createField({
                        tag: 'textarea',
                        value: Array.isArray(this.data.items) ? this.data.items.join('\n') : '',
                        placeholder: '1. adim\n2. adim\n3. adim',
                        rows: 6,
                    });
                    wrapper.append(this.titleInput, this.itemsInput);
                    return wrapper;
                }

                save() {
                    return {
                        title: (this.titleInput?.value || '').trim(),
                        items: linesToItems(this.itemsInput?.value),
                    };
                }
            }

            class ProsConsBlock {
                static get toolbox() {
                    return { title: 'Arti / Eksi', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 10V5"></path><path d="M17 19v-5"></path><path d="M5 7h4"></path><path d="M15 17h4"></path><path d="M5 17l4 4"></path><path d="M9 17l-4 4"></path><path d="M15 7h4"></path></svg>' };
                }

                constructor({ data }) {
                    this.data = data || {};
                }

                render() {
                    const wrapper = createBlockShell('Arti / Eksi', 'Arti ve eksi maddeleri ayri ayri gir.');
                    this.prosInput = createField({
                        tag: 'textarea',
                        value: Array.isArray(this.data.pros) ? this.data.pros.join('\n') : '',
                        placeholder: 'Arti 1\nArti 2',
                        rows: 5,
                    });
                    this.consInput = createField({
                        tag: 'textarea',
                        value: Array.isArray(this.data.cons) ? this.data.cons.join('\n') : '',
                        placeholder: 'Eksi 1\nEksi 2',
                        rows: 5,
                    });
                    wrapper.append(this.prosInput, this.consInput);
                    return wrapper;
                }

                save() {
                    return {
                        pros: linesToItems(this.prosInput?.value),
                        cons: linesToItems(this.consInput?.value),
                    };
                }
            }

            class StatsCardBlock {
                static get toolbox() {
                    return { title: 'Istatistik', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19h16"></path><path d="M7 16V8"></path><path d="M12 16V5"></path><path d="M17 16v-3"></path></svg>' };
                }

                constructor({ data }) {
                    this.data = data || {};
                }

                render() {
                    const wrapper = createBlockShell('Istatistik karti', 'Tek bir sayisal vurgu kutusu.');
                    this.valueInput = createField({ value: this.data.value || '', placeholder: 'Deger' });
                    this.labelInput = createField({ value: this.data.label || '', placeholder: 'Aciklama' });
                    this.noteInput = createField({ value: this.data.note || '', placeholder: 'Alt not (opsiyonel)' });
                    wrapper.append(this.valueInput, this.labelInput, this.noteInput);
                    return wrapper;
                }

                save() {
                    return {
                        value: (this.valueInput?.value || '').trim(),
                        label: (this.labelInput?.value || '').trim(),
                        note: (this.noteInput?.value || '').trim(),
                    };
                }
            }

            class CtaBoxBlock {
                static get toolbox() {
                    return { title: 'Cagri', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>' };
                }

                constructor({ data }) {
                    this.data = data || {};
                }

                render() {
                    const wrapper = createBlockShell('Cagri', 'Butonlu yonlendirme kutusu.');
                    this.titleInput = createField({ value: this.data.title || '', placeholder: 'Baslik' });
                    this.textInput = createField({ tag: 'textarea', value: this.data.text || '', placeholder: 'Aciklama', rows: 4 });
                    this.labelInput = createField({ value: this.data.label || '', placeholder: 'Buton etiketi' });
                    this.urlInput = createField({ type: 'url', value: this.data.url || '', placeholder: 'https://example.com' });
                    wrapper.append(this.titleInput, this.textInput, this.labelInput, this.urlInput);
                    return wrapper;
                }

                save() {
                    return {
                        title: (this.titleInput?.value || '').trim(),
                        text: (this.textInput?.value || '').trim(),
                        label: (this.labelInput?.value || '').trim(),
                        url: (this.urlInput?.value || '').trim(),
                    };
                }
            }

            class AudioNarrationBlock {
                static get toolbox() {
                    return { title: 'Sesli okuma', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M11 5 6 9H3v6h3l5 4V5Z"></path><path d="M15.5 8.5a5 5 0 0 1 0 7"></path><path d="M18.5 5.5a9 9 0 0 1 0 13"></path></svg>' };
                }

                constructor({ data }) {
                    this.data = data || {};
                }

                render() {
                    const wrapper = createBlockShell('Sesli okuma', 'Metni ziyaretcinin tarayicisinda sesli okut.');
                    this.titleInput = createField({ value: this.data.title || '', placeholder: 'Baslik (opsiyonel)' });
                    this.textInput = createField({ tag: 'textarea', value: this.data.text || '', placeholder: 'Okunacak metin', rows: 6 });
                    this.buttonInput = createField({ value: this.data.buttonLabel || 'Sesli oku', placeholder: 'Buton metni' });
                    this.langInput = createField({ value: this.data.lang || 'tr-TR', placeholder: 'Dil kodu' });
                    this.rateInput = createField({ type: 'number', value: this.data.rate || 1, placeholder: 'Hiz' });
                    this.pitchInput = createField({ type: 'number', value: this.data.pitch || 1, placeholder: 'Ton' });
                    this.rateInput.step = '0.1';
                    this.rateInput.min = '0.5';
                    this.rateInput.max = '2';
                    this.pitchInput.step = '0.1';
                    this.pitchInput.min = '0';
                    this.pitchInput.max = '2';
                    wrapper.append(this.titleInput, this.textInput, this.buttonInput, this.langInput, this.rateInput, this.pitchInput);
                    return wrapper;
                }

                save() {
                    return {
                        title: (this.titleInput?.value || '').trim(),
                        text: (this.textInput?.value || '').trim(),
                        buttonLabel: (this.buttonInput?.value || '').trim() || 'Sesli oku',
                        lang: (this.langInput?.value || '').trim() || 'tr-TR',
                        rate: Number(this.rateInput?.value || 1),
                        pitch: Number(this.pitchInput?.value || 1),
                    };
                }
            }

            const WarningBlock = createNoticeTool({
                type: 'warning',
                title: 'Uyari',
                description: 'Uyari veya dikkat kutusu ekle.',
                defaultTitle: 'Dikkat',
            });

            const InfoBoxBlock = createNoticeTool({
                type: 'infoBox',
                title: 'Bilgi',
                description: 'Bilgilendirici kutu ekle.',
                defaultTitle: 'Bilgi',
            });

            const SuccessBoxBlock = createNoticeTool({
                type: 'successBox',
                title: 'Basarili',
                description: 'Basari veya olumlu sonuc kutusu ekle.',
                defaultTitle: 'Basarili',
            });

            const TipBoxBlock = createNoticeTool({
                type: 'tipBox',
                title: 'Tip',
                description: 'Kisa ipucu kutusu ekle.',
                defaultTitle: 'Ipucu',
            });

            const NoteBoxBlock = createNoticeTool({
                type: 'noteBox',
                title: 'Note',
                description: 'Not veya yan bilgi kutusu ekle.',
                defaultTitle: 'Not',
            });

            class SocialEmbed {
                constructor({ data, api }) {
                    this.api = api;
                    this.data = data || {};
                    this.wrapper = null;
                }

                static get toolbox() {
                    return {
                        title: 'Sosyal Medya',
                        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21L12 12M12 12L15 15.3333M12 12L9 15.3333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.5 7V13C20.5 16.7712 20.5 18.6569 19.3284 19.8284C18.1569 21 16.2712 21 12.5 21H11.5C7.72876 21 5.84315 21 4.67157 19.8284C3.5 18.6569 3.5 16.7712 3.5 13V7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M2 5C2 4.05719 2 3.58579 2.29289 3.29289C2.58579 3 3.05719 3 4 3H20C20.9428 3 21.4142 3 21.7071 3.29289C22 3.58579 22 4.05719 22 5C22 5.94281 22 6.41421 21.7071 6.70711C21.4142 7 20.9428 7 20 7H4C3.05719 7 2.58579 7 2.29289 6.70711C2 6.41421 2 5.94281 2 5Z" stroke="currentColor" stroke-width="1.5"/></svg>`,
                    };
                }

                static get pasteConfig() {
                    return {
                        tags: ['IFRAME'],
                        patterns: {
                            youtube: /https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)[^\s]+/i,
                            instagram: /https?:\/\/(?:www\.)?instagram\.com\/(?:p|reel|tv)\/[^\s]+/i,
                            tiktok: /https?:\/\/(?:www\.)?tiktok\.com\/@[^\/]+\/video\/\d+/i,
                            vimeo: /https?:\/\/(?:www\.)?vimeo\.com\/\d+/i,
                            dailymotion: /https?:\/\/(?:www\.)?dailymotion\.com\/video\/[^\s]+|https?:\/\/dai\.ly\/[^\s]+/i,
                            twitch: /https?:\/\/(?:www\.)?twitch\.tv\/videos\/\d+|https?:\/\/clips\.twitch\.tv\/[^\s]+/i,
                            facebook: /https?:\/\/(?:www\.)?(?:facebook\.com|fb\.watch)\/[^\s]+/i,
                            twitterx: /https?:\/\/(?:(?:www|mobile)\.)?(?:twitter\.com|x\.com)\/[^\s]+\/status(?:es)?\/\d+/i,
                            vine: /https?:\/\/(?:www\.)?vine\.co\/v\/[^\s]+/i,
                        },
                    };
                }

                buildEmbedSrcFromUrl(value) {
                    value = this.extractUrlFromEmbedInput(value);
                    const safe = (() => {
                        try {
                            const parsed = new URL(value);
                            if (!['http:', 'https:'].includes(parsed.protocol)) return null;
                            return parsed;
                        } catch {
                            return null;
                        }
                    })();

                    if (!safe) return null;

                    const host = safe.hostname.toLowerCase();
                    const path = safe.pathname || '/';

                    if (host === 'youtu.be') {
                        const id = path.split('/').filter(Boolean)[0];
                        return id ? `https://www.youtube.com/embed/${encodeURIComponent(id)}` : null;
                    }

                    if (host.endsWith('youtube.com')) {
                        if (path.startsWith('/watch')) {
                            const id = safe.searchParams.get('v');
                            return id ? `https://www.youtube.com/embed/${encodeURIComponent(id)}` : null;
                        }

                        if (path.startsWith('/shorts/')) {
                            const id = path.split('/').filter(Boolean)[1];
                            return id ? `https://www.youtube.com/embed/${encodeURIComponent(id)}` : null;
                        }

                        if (path.startsWith('/embed/')) {
                            const id = path.split('/').filter(Boolean)[1];
                            return id ? `https://www.youtube.com/embed/${encodeURIComponent(id)}` : null;
                        }
                    }

                    if (host.endsWith('instagram.com')) {
                        const parts = path.split('/').filter(Boolean);
                        const kind = parts[0];
                        const code = parts[1];
                        if (!kind || !code) return null;
                        if (!['p', 'reel', 'tv'].includes(kind)) return null;
                        return `https://www.instagram.com/${kind}/${encodeURIComponent(code)}/embed`;
                    }

                    if (host.endsWith('tiktok.com')) {
                        const parts = path.split('/').filter(Boolean);
                        if (parts[0] === 'embed' && parts[1] === 'v2' && parts[2]) {
                            return `https://www.tiktok.com/embed/v2/${encodeURIComponent(parts[2])}`;
                        }
                        const videoIndex = parts.indexOf('video');
                        const id = videoIndex >= 0 ? parts[videoIndex + 1] : null;
                        return id ? `https://www.tiktok.com/embed/v2/${encodeURIComponent(id)}` : null;
                    }

                    if (host === 'vimeo.com' || host === 'www.vimeo.com' || host === 'player.vimeo.com') {
                        const parts = path.split('/').filter(Boolean);
                        const id = parts[0] === 'video' ? parts[1] : parts[0];
                        return id && /^\d+$/.test(id) ? `https://player.vimeo.com/video/${id}` : null;
                    }

                    if (host.endsWith('dailymotion.com')) {
                        const parts = path.split('/').filter(Boolean);
                        const id = parts[0] === 'video' ? parts[1] : null;
                        return id ? `https://www.dailymotion.com/embed/video/${encodeURIComponent(id)}` : null;
                    }

                    if (host === 'dai.ly') {
                        const id = path.split('/').filter(Boolean)[0];
                        return id ? `https://www.dailymotion.com/embed/video/${encodeURIComponent(id)}` : null;
                    }

                    if (host === 'twitch.tv' || host === 'www.twitch.tv') {
                        const parent = window.location.hostname || 'localhost';
                        const parts = path.split('/').filter(Boolean);
                        if (parts[0] === 'videos' && parts[1]) {
                            return `https://player.twitch.tv/?video=v${encodeURIComponent(parts[1])}&parent=${encodeURIComponent(parent)}`;
                        }
                        if (parts[1] === 'clip' && parts[2]) {
                            return `https://player.twitch.tv/?clip=${encodeURIComponent(parts[2])}&parent=${encodeURIComponent(parent)}`;
                        }
                    }

                    if (host === 'clips.twitch.tv') {
                        const parent = window.location.hostname || 'localhost';
                        const clip = path.split('/').filter(Boolean)[0];
                        return clip ? `https://player.twitch.tv/?clip=${encodeURIComponent(clip)}&parent=${encodeURIComponent(parent)}` : null;
                    }

                    if (host.endsWith('facebook.com') || host.endsWith('fb.watch')) {
                        if (path.startsWith('/plugins/')) {
                            return this.safeSocialEmbedUrl(safe.toString());
                        }
                        return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(safe.toString())}&show_text=false`;
                    }

                    if (host.endsWith('twitter.com') || host.endsWith('.twitter.com') || host === 'x.com' || host === 'www.x.com' || host === 'mobile.x.com' || host.endsWith('.x.com')) {
                        const parts = path.split('/').filter(Boolean);
                        if (parts.includes('status') || parts.includes('statuses')) {
                            return `https://twitframe.com/show?url=${encodeURIComponent(safe.toString())}`;
                        }
                    }

                    if (host.endsWith('vine.co')) {
                        const parts = path.split('/').filter(Boolean);
                        if (parts[0] === 'v' && parts[1]) {
                            return `https://vine.co/v/${encodeURIComponent(parts[1])}/embed/simple`;
                        }
                    }

                    return null;
                }

                extractUrlFromEmbedInput(value) {
                    const text = String(value || '').trim();
                    if (!text) return '';
                    const srcMatch = text.match(/\bsrc\s*=\s*(?:"(https?:\/\/[^"]+)"|'(https?:\/\/[^']+)'|(https?:\/\/[^\s>]+))/i);
                    if (srcMatch) return srcMatch[1] || srcMatch[2] || srcMatch[3] || '';
                    const urlMatch = text.match(/https?:\/\/[^\s"'<>]+/i);
                    const url = urlMatch?.[0] || text;
                    if (urlMatch && url !== text) return text;
                    return /^https?:\/\//i.test(url) ? url : `https://${url.replace(/^\/+/, '')}`;
                }

                resolveEmbedSrc(value) {
                    const input = this.extractUrlFromEmbedInput(value);
                    const built = this.buildEmbedSrcFromUrl(input);
                    const safe = built ? this.safeSocialEmbedUrl(built) : this.safeSocialEmbedUrl(input);
                    return safe || null;
                }

                embedFrameStyle(url) {
                    try {
                        const host = new URL(url).hostname.toLowerCase();
                        if (host.includes('tiktok.com') || host.includes('instagram.com')) {
                            return {
                                aspectRatio: '9 / 16',
                                maxWidth: '425px',
                                marginLeft: 'auto',
                                marginRight: 'auto',
                            };
                        }
                    } catch {}

                    return { aspectRatio: '16 / 9' };
                }

                safeSocialEmbedUrl(url) {
                    try {
                        const parsed = new URL(url, window.location.origin);
                        if (!['http:', 'https:'].includes(parsed.protocol)) return null;
                        const allowed = [
                            'youtube.com',
                            'www.youtube.com',
                            'youtube-nocookie.com',
                            'www.youtube-nocookie.com',
                            'instagram.com',
                            'www.instagram.com',
                            'tiktok.com',
                            'www.tiktok.com',
                            'player.vimeo.com',
                            'vimeo.com',
                            'www.vimeo.com',
                            'dailymotion.com',
                            'www.dailymotion.com',
                            'player.twitch.tv',
                            'twitch.tv',
                            'www.twitch.tv',
                            'clips.twitch.tv',
                            'facebook.com',
                            'www.facebook.com',
                            'fb.watch',
                            'twitter.com',
                            'www.twitter.com',
                            'x.com',
                            'www.x.com',
                            'twitframe.com',
                            'vine.co',
                        ];
                        if (!allowed.includes(parsed.hostname.toLowerCase())) return null;
                        return parsed.toString();
                    } catch {
                        return null;
                    }
                }

                render() {
                    this.wrapper = document.createElement('div');
                    this.wrapper.className = 'my-2';
                    this.renderContent();
                    return this.wrapper;
                }

                renderContent() {
                    if (!this.wrapper) return;
                    this.wrapper.innerHTML = '';

                    const src = this.resolveEmbedSrc(this.data?.src || this.data?.input);

                    if (src) {
                        const outer = document.createElement('div');
                        outer.className = 'w-full overflow-hidden rounded-xl bg-black/5';
                        Object.assign(outer.style, this.embedFrameStyle(src));

                        const iframe = document.createElement('iframe');
                        iframe.className = 'h-full w-full';
                        iframe.src = src;
                        iframe.loading = 'lazy';
                        iframe.allow =
                            'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                        iframe.allowFullscreen = true;

                        outer.appendChild(iframe);
                        this.wrapper.appendChild(outer);
                        return;
                    }

                    const input = document.createElement('textarea');
                    input.className = 'w-full px-3 py-2 text-sm text-slate-800';
                    input.rows = 3;
                    input.placeholder = 'Sosyal medya linki veya iframe kodu';
                    input.value = this.data?.input || this.data?.src || '';
                    input.addEventListener('input', () => {
                        const src = this.resolveEmbedSrc(input.value);
                        this.data = { input: input.value, src: src || '' };
                    });
                    input.addEventListener('change', () => {
                        const src = this.resolveEmbedSrc(input.value);
                        this.data = { input: input.value, src: src || '' };
                        this.renderContent();
                    });
                    input.addEventListener('paste', () => {
                        window.setTimeout(() => {
                            const src = this.resolveEmbedSrc(input.value);
                            this.data = { input: input.value, src: src || '' };
                            this.renderContent();
                        }, 0);
                    });

                    this.wrapper.appendChild(input);

                    const hint = document.createElement('div');
                    hint.className = 'rounded-lg px-3 py-3 text-sm text-slate-600';
                    hint.textContent = 'YouTube, Instagram, TikTok, Vimeo, Dailymotion, Twitch, Facebook, X veya Twitter linki ya da iframe kodu yapistirin.';
                    this.wrapper.appendChild(hint);
                }

                onPaste(event) {
                    const detail = event?.detail || {};

                    if (detail.type === 'tag') {
                        const iframe = detail.data;
                        const src = iframe?.getAttribute?.('src') || '';
                        const safe = this.resolveEmbedSrc(src);
                        if (safe) {
                            this.data = { input: src, src: safe };
                            this.renderContent();
                        }
                        return;
                    }

                    if (detail.type === 'pattern') {
                        const url = String(detail.data || '');
                        const safe = this.resolveEmbedSrc(url);
                        if (safe) {
                            this.data = { input: url, src: safe };
                            this.renderContent();
                        }
                    }
                }

                save() {
                    const src = this.resolveEmbedSrc(this.data?.src || this.data?.input);
                    return { src: src || '' };
                }

                validate(savedData) {
                    return Boolean(this.resolveEmbedSrc(savedData?.src));
                }
            }

            class PollBlock {
                constructor({ data, api }) {
                    this.api = api;
                    this.data = data || {};
                    this.wrapper = null;
                    this.questionInput = null;
                    this.durationInput = null;
                    this.optionsWrap = null;
                }

                static get toolbox() {
                    return {
                        title: 'Anket',
                        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16M4 12h10M4 18h7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M16 10.5v6M19 12.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>`,
                    };
                }

                render() {
                    this.wrapper = document.createElement('div');
                    this.wrapper.className = 'rounded-2xl p-4 space-y-3';

                    const title = document.createElement('div');
                    title.className = 'text-[0.6rem] font-semibold uppercase tracking-[0.4em] text-slate-400';
                    title.textContent = 'Anket';

                    this.questionInput = document.createElement('input');
                    this.questionInput.type = 'text';
                    this.questionInput.placeholder = 'Soru yazin';
                    this.questionInput.value = this.data?.question || '';
                    this.questionInput.className = 'w-full rounded-xl px-3 py-2 text-sm text-slate-700';

                    const durationRow = document.createElement('div');
                    durationRow.className = 'grid grid-cols-1 gap-2 sm:grid-cols-2';
                    const durationLabel = document.createElement('p');
                    durationLabel.className = 'text-[0.55rem] font-semibold uppercase tracking-[0.4em] text-slate-400';
                    durationLabel.textContent = 'Sure (dakika)';
                    this.durationInput = document.createElement('input');
                    this.durationInput.type = 'number';
                    this.durationInput.min = '0';
                    this.durationInput.value = this.data?.duration_minutes ?? 0;
                    this.durationInput.className = 'w-full rounded-xl px-3 py-2 text-sm text-slate-700';
                    const durationHint = document.createElement('p');
                    durationHint.className = 'text-xs text-slate-400';
                    durationHint.textContent = '0 = suresiz';
                    durationRow.append(durationLabel, this.durationInput);

                    this.optionsWrap = document.createElement('div');
                    this.optionsWrap.className = 'space-y-2';
                    const options = Array.isArray(this.data?.options) ? this.data.options : [];
                    if (options.length) {
                        options.forEach((opt) => this.addOption(opt));
                    } else {
                        this.addOption('');
                        this.addOption('');
                    }

                    const addButton = document.createElement('button');
                    addButton.type = 'button';
                    addButton.className = 'inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800';
                    addButton.textContent = 'Secenek ekle';
                    addButton.addEventListener('click', () => this.addOption(''));

                    this.wrapper.append(title, this.questionInput, durationRow, durationHint, this.optionsWrap, addButton);
                    return this.wrapper;
                }

                addOption(value) {
                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-2';
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = 'Secenek';
                    input.value = value || '';
                    input.className = 'w-full rounded-xl px-3 py-2 text-sm text-slate-700';
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'h-9 w-9 rounded-full text-xs text-slate-500 hover:bg-slate-50';
                    remove.textContent = '×';
                    remove.addEventListener('click', () => {
                        row.remove();
                    });
                    row.append(input, remove);
                    this.optionsWrap.appendChild(row);
                }

                save() {
                    const options = Array.from(this.optionsWrap.querySelectorAll('input'))
                        .map((input) => input.value.trim())
                        .filter((opt) => opt !== '');

                    return {
                        question: (this.questionInput?.value || '').trim(),
                        options,
                        duration_minutes: parseInt(this.durationInput?.value || '0', 10) || 0,
                    };
                }

                validate(savedData) {
                    return Boolean(savedData?.question) && Array.isArray(savedData?.options) && savedData.options.length >= 2;
                }
            }

            class DownloadButton {
                constructor({ data }) {
                    this.data = data || {};
                    this.wrapper = null;
                    this.textInput = null;
                    this.urlInput = null;
                }

                static get toolbox() {
                    return {
                        title: 'Indirme Butonu',
                        icon: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4v10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 20h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>`,
                    };
                }

                render() {
                    this.wrapper = document.createElement('div');
                    this.wrapper.className = 'space-y-2 rounded-2xl p-3';

                    this.textInput = document.createElement('input');
                    this.textInput.type = 'text';
                    this.textInput.placeholder = 'Buton metni (or. Indir)';
                    this.textInput.value = this.data?.text || '';
                    this.textInput.className = 'w-full rounded-xl px-3 py-2 text-sm text-slate-700';

                    this.urlInput = document.createElement('input');
                    this.urlInput.type = 'url';
                    this.urlInput.placeholder = 'https://...';
                    this.urlInput.value = this.data?.url || '';
                    this.urlInput.className = 'w-full rounded-xl px-3 py-2 text-sm text-slate-700';

                    this.wrapper.append(this.textInput, this.urlInput);
                    return this.wrapper;
                }

                save() {
                    return {
                        text: (this.textInput?.value || '').trim(),
                        url: (this.urlInput?.value || '').trim(),
                    };
                }

                validate(savedData) {
                    return Boolean(savedData?.url);
                }
            }

            class VideoUpload {
                constructor({ data }) {
                    this.data = data || {};
                    if (!this.data.url && this.data?.file?.url) {
                        this.data.url = this.data.file.url;
                    }
                    this.wrapper = document.createElement('div');
                    this.fileInput = null;
                    this.preview = null;
                    this.statusText = null;
                    this.subtitleLangInput = null;
                    this.subtitleLabelInput = null;
                    this.subtitleFileInput = null;
                    this.subtitleTextArea = null;
                    this.subtitleList = null;
                    this.subtitles = Array.isArray(this.data?.subtitles) ? [...this.data.subtitles] : [];
                }

                static get toolbox() {
                    return {
                        title: 'Video',
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#3b3b3b"><g fill="none" stroke="#3b3b3b" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><rect width="13.5" height="12" x="2.75" y="6" rx="3.5"/><path d="m16.25 9.74 3.554-1.77a1 1 0 0 1 1.446.895v6.268a1 1 0 0 1-1.447.895l-3.553-1.773z"/></g></svg>`,
                    };
                }

                render() {
                    this.wrapper.className = 'space-y-3';
                    this.statusText = document.createElement('p');
                    this.statusText.className = 'text-xs font-semibold text-slate-500';

                    const header = document.createElement('div');
                    header.className = 'text-4xl font-semibold text-slate-500';
                    header.textContent = 'Baslik';
                    const uploadRow = document.createElement('div');
                    uploadRow.className = 'rounded-md border border-slate-300 bg-white shadow-sm';
                    this.fileInput = document.createElement('input');
                    const fileInputId = `video-upload-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
                    this.fileInput.id = fileInputId;
                    this.fileInput.type = 'file';
                    this.fileInput.accept = 'video/*';
                    this.fileInput.className = 'sr-only';
                    this.fileInput.addEventListener('change', (event) => {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        this.statusText.textContent = 'Video yükleniyor...';
                        this.handleFile(file);
                    });
                    const uploadLabel = document.createElement('label');
                    uploadLabel.setAttribute('for', fileInputId);
                    uploadLabel.className = 'flex h-12 w-full cursor-pointer items-center justify-center gap-3 text-2xl font-medium text-slate-900';
                    uploadLabel.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="4"></rect>
                            <circle cx="11" cy="11" r="3"></circle>
                            <path d="M20 7h-4"></path>
                        </svg>
                        <span>Video sec</span>
                    `;
                    uploadRow.append(this.fileInput, uploadLabel);
                    this.preview = document.createElement('div');
                    this.preview.className = 'rounded-2xl p-3 text-center text-xs text-slate-400';
                    this.updatePreview(this.data?.url || '');

                    this.wrapper.append(
                        header,
                        uploadRow,
                        this.preview,
                        this.statusText
                    );

                    return this.wrapper;
                }

                renderSubtitleList() {
                    if (!this.subtitleList) return;
                    if (!this.subtitles.length) {
                        this.subtitleList.textContent = 'Henüz altyazı eklenmedi.';
                        return;
                    }

                    this.subtitleList.innerHTML = '';
                    const list = document.createElement('div');
                    list.className = 'space-y-1 text-xs text-slate-500';
                    this.subtitles.forEach((entry, idx) => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center justify-between gap-2 rounded-xl bg-slate-50 px-3 py-2';
                        const label = document.createElement('span');
                        label.textContent = `${entry.label || 'Altyazı'} (${entry.lang || 'tr'})`;
                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'text-[11px] font-semibold text-slate-500 hover:text-slate-900';
                        remove.textContent = 'Kaldır';
                        remove.addEventListener('click', () => {
                            this.subtitles.splice(idx, 1);
                            this.ensureDefaultSubtitle();
                            this.renderSubtitleList();
                            this.updatePreview(this.data?.url || '');
                        });
                        row.append(label, remove);
                        list.appendChild(row);
                    });
                    this.subtitleList.appendChild(list);
                }

                async handleFile(file) {
                    this.setUploading(true);
                    try {
                        const url = await this.uploadVideo(file);
                        this.setVideoUrl(url);
                        this.statusText.textContent = 'Video yüklendi.';
                    } catch (error) {
                        console.error('Video upload failed', error);
                        this.statusText.textContent = error?.message || 'Video yüklenemedi.';
                    } finally {
                        this.setUploading(false);
                        if (this.fileInput) {
                            this.fileInput.value = '';
                        }
                    }
                }

                async handleSubtitleFile(file) {
                    try {
                        const lang = this.subtitleLangInput?.value.trim() || 'tr';
                        const label = this.subtitleLabelInput?.value.trim() || 'Altyazı';
                        const formData = new FormData();
                        formData.append('subtitle', file);
                        formData.append('filename', label || 'subtitle');
                        const url = await this.uploadSubtitle(formData);
                        this.addSubtitleEntry({ url, lang, label });
                        this.statusText.textContent = 'Altyazı yüklendi.';
                    } catch (error) {
                        console.error(error);
                        this.statusText.textContent = 'Altyazı yüklenemedi.';
                    } finally {
                        if (this.subtitleFileInput) {
                            this.subtitleFileInput.value = '';
                        }
                    }
                }

                async handleSubtitleText() {
                    if (!this.subtitleTextArea) return;
                    const content = (this.subtitleTextArea.value || '').trim();
                    if (!content) {
                        this.statusText.textContent = 'Altyazı içeriği boş olamaz.';
                        return;
                    }
                    try {
                        const lang = this.subtitleLangInput?.value.trim() || 'tr';
                        const label = this.subtitleLabelInput?.value.trim() || 'Altyazı';
                        const formData = new FormData();
                        formData.append('subtitle_content', content);
                        formData.append('filename', label || 'subtitle');
                        const url = await this.uploadSubtitle(formData);
                        this.addSubtitleEntry({ url, lang, label });
                        this.statusText.textContent = 'Altyazı kaydedildi.';
                        this.subtitleTextArea.value = '';
                    } catch (error) {
                        console.error(error);
                        this.statusText.textContent = 'Altyazı kaydedilemedi.';
                    }
                }

                async uploadSubtitle(formData) {
                    const subtitleEndpoint = new URL("{{ route('blog.editorjs.subtitle', [], false) }}", window.location.origin).toString();
                    const response = await fetch(subtitleEndpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: csrf ? { 'X-CSRF-TOKEN': csrf } : undefined,
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('subtitle upload failed');
                    }

                    const payload = await response.json();
                    if (!payload.success || !payload.file?.url) {
                        throw new Error('subtitle upload failed');
                    }

                    return payload.file.url;
                }

                ensureDefaultSubtitle() {
                    if (!this.subtitles.length) return;
                    const hasDefault = this.subtitles.some((subtitle) => subtitle.default);
                    if (hasDefault) return;
                    this.subtitles[0].default = true;
                }

                addSubtitleEntry(entry) {
                    if (!entry?.url) return;
                    const normalized = {
                        url: entry.url,
                        lang: entry.lang || 'tr',
                        label: entry.label || 'Altyazı',
                        default: !this.subtitles.length,
                    };
                    this.subtitles = [...this.subtitles, normalized];
                    this.ensureDefaultSubtitle();
                    this.renderSubtitleList();
                    this.updatePreview(this.data?.url || '');
                }

                async uploadVideo(file) {
                    const initEndpoint = new URL("{{ route('api.blog.editorjs.video.init', [], false) }}", window.location.origin).toString();
                    const chunkEndpoint = new URL("{{ route('api.blog.editorjs.video.chunk', [], false) }}", window.location.origin).toString();
                    const completeEndpoint = new URL("{{ route('api.blog.editorjs.video.complete', [], false) }}", window.location.origin).toString();

                    const uploadChunked = async () => {
                        const initResp = await fetch(initEndpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                            },
                            body: JSON.stringify({
                                name: file.name || 'video.mp4',
                                mime: file.type || 'video/mp4',
                                size: file.size || 0,
                            }),
                        });
                        const initPayload = await initResp.json();
                        if (!initResp.ok || !initPayload?.upload_id) {
                            throw new Error(initPayload?.message || 'Chunk init basarisiz.');
                        }

                        const uploadId = initPayload.upload_id;
                        const fileSize = Number(file?.size || 0);
                        if (!fileSize) {
                            throw new Error('Video boyutu okunamadi.');
                        }
                        const chunkSize = 512 * 1024;
                        const total = Math.ceil(fileSize / chunkSize);

                        for (let i = 0; i < total; i++) {
                            const start = i * chunkSize;
                            const end = Math.min(start + chunkSize, fileSize);
                            const chunk = file.slice(start, end);
                            const chunkForm = new FormData();
                            chunkForm.append('upload_id', uploadId);
                            chunkForm.append('index', String(i));
                            chunkForm.append('total', String(total));
                            chunkForm.append('chunk', chunk, `chunk-${i}.part`);

                            const chunkResp = await fetch(chunkEndpoint, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                                },
                                body: chunkForm,
                            });
                            const chunkPayload = await chunkResp.json();
                            if (!chunkResp.ok || !chunkPayload?.success) {
                                throw new Error(chunkPayload?.message || `Chunk yuklenemedi (${i + 1}/${total}).`);
                            }
                        }

                        const completeResp = await fetch(completeEndpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                            },
                            body: JSON.stringify({
                                upload_id: uploadId,
                                total,
                                name: file.name || 'video.mp4',
                                mime: file.type || 'video/mp4',
                            }),
                        });
                        const completePayload = await completeResp.json();
                        if (!completeResp.ok || !completePayload?.success || !completePayload?.file?.url) {
                            throw new Error(completePayload?.message || 'Video birlestirme basarisiz.');
                        }
                        return completePayload;
                    };

                    const payload = await uploadChunked();

                    if (!payload.success || !payload.file?.url) {
                        throw new Error(payload?.message || 'Video yuklenemedi.');
                    }

                    return payload.file.url;
                }

                setVideoUrl(url) {
                    this.data.url = url || '';
                    if (this.urlInput) {
                        this.urlInput.value = url || '';
                    }
                    this.updatePreview(this.data.url);
                }

                updatePreview(url) {
                    if (!this.preview) return;

                    this.preview.innerHTML = '';
                    if (!url) {
                        this.preview.classList.add('text-xs', 'text-slate-400');
                        this.preview.textContent = 'Video önizlemesi burada görünecek.';
                        return;
                    }

                    this.preview.classList.remove('text-xs', 'text-slate-400');
                    const video = document.createElement('video');
                    video.controls = true;
                    video.className = 'w-full rounded-xl bg-slate-900';
                    video.src = url;
                    video.setAttribute('preload', 'metadata');
                    video.setAttribute('playsinline', '');
                    this.subtitles.forEach((subtitle) => {
                        if (!subtitle.url) return;
                        const track = document.createElement('track');
                        track.kind = 'subtitles';
                        track.src = subtitle.url;
                        track.srclang = subtitle.lang || 'tr';
                        track.label = subtitle.label || 'Altyazı';
                        if (subtitle.default) {
                            track.default = true;
                            track.mode = 'showing';
                        } else {
                            track.mode = 'hidden';
                        }
                        video.appendChild(track);
                    });
                    video.innerHTML = 'Video oynatılamıyor.';
                    this.preview.appendChild(video);
                }

                setUploading(enabled) {
                    if (this.fileInput) {
                        this.fileInput.disabled = enabled;
                    }
                }

                save() {
                    return {
                        url: (this.data.url || '').trim(),
                        file: { url: (this.data.url || '').trim() },
                    };
                }

                validate(savedData) {
                    return Boolean(savedData?.url || savedData?.file?.url);
                }
            }

            let editorReady = false;
            holder.classList.add('pointer-events-none', 'opacity-80');
            holder.addEventListener('mousedown', (event) => {
                if (!editorReady) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);

            const editor = new EditorJS({
                holder: holder,
                data: initialData,
                placeholder: 'Metninizi bloklar halinde yazin...',
                onReady() {
                    editorReady = true;
                    holder.classList.remove('pointer-events-none', 'opacity-80');
                },
                tools: {
                    header: window.Header,
                    list: { class: window.List, inlineToolbar: true },
                    quote: {
                        class: window.Quote,
                        inlineToolbar: true,
                        config: { quotePlaceholder: 'Alinti metni', captionPlaceholder: 'Yazar' },
                    },
                    warning: { class: WarningBlock },
                    infoBox: { class: InfoBoxBlock },
                    successBox: { class: SuccessBoxBlock },
                    tipBox: { class: TipBoxBlock },
                    noteBox: { class: NoteBoxBlock },
                    faq: { class: FaqBlock },
                    steps: { class: StepsBlock },
                    prosCons: { class: ProsConsBlock },
                    statsCard: { class: StatsCardBlock },
                    ctaBox: { class: CtaBoxBlock },
                    audioNarration: { class: AudioNarrationBlock },
                    checklist: { class: window.Checklist, inlineToolbar: true },
                    table: { class: window.Table, inlineToolbar: true },
                    code: window.CodeTool,
                    delimiter: window.Delimiter,
                    embed: {
                        class: window.Embed,
                        config: {
                            services: {
                                youtube: true,
                                instagram: true,
                                tiktok: true,
                            },
                        },
                    },
                      video: {
                          class: VideoUpload,
                          inlineToolbar: true,
                      },
                      socialEmbed: { class: SocialEmbed },
                      poll: { class: PollBlock },
                      downloadButton: { class: DownloadButton },
                      inlineCode: window.InlineCode,
                    marker: window.Marker,
                    linkTool: {
                        class: window.LinkTool,
                        config: {
                            endpoint: "{{ route('blog.editorjs.link', [], false) }}",
                        },
                    },
                    image: {
                        class: window.ImageTool,
                        config: {
                            captionPlaceholder: 'Aciklama (opsiyonel)',
                            uploader: {
                                uploadByFile: async (file) => {
                                    const formData = new FormData();
                                    formData.append('image', file);

                                    const imageEndpoint = new URL("{{ route('blog.editorjs.image', [], false) }}", window.location.origin).toString();
                                    const resp = await fetch(imageEndpoint, {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        headers: csrf ? { 'X-CSRF-TOKEN': csrf } : undefined,
                                        body: formData,
                                    });

                                    return resp.json();
                                },
                            },
                        },
                    },
                },
                async onChange() {
                    try {
                        const output = await editor.save();
                        jsonField.value = JSON.stringify(output);
                        htmlField.value = window.filamentEditorBlocksToHtml(output.blocks || []);
                        jsonField.dispatchEvent(new Event('input', { bubbles: true }));
                        htmlField.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (err) {
                        console.error('EditorJS save error', err);
                    }
                },
            });

            root.__editorInstance = editor;
        };

        document.addEventListener('livewire:load', () => {
            setTimeout(() => {
                document.querySelectorAll('[data-editorjs-wrapper]').forEach((el) => {
                    window.initFilamentEditorJsField(el);
                });
            }, 150);
        });

        document.addEventListener('filament.forms.rendered', () => {
            setTimeout(() => {
                document.querySelectorAll('[data-editorjs-wrapper]').forEach((el) => {
                    if (!el.__editorInstance) window.initFilamentEditorJsField(el);
                });
            }, 100);
        });

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-audio-read-btn]');
            if (!button) return;

            const text = button.getAttribute('data-audio-read-text') || '';
            if (!text.trim() || !('speechSynthesis' in window)) return;

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = button.getAttribute('data-audio-read-lang') || 'tr-TR';
            utterance.rate = Number(button.getAttribute('data-audio-read-rate') || 1);
            utterance.pitch = Number(button.getAttribute('data-audio-read-pitch') || 1);
            window.speechSynthesis.speak(utterance);
        });
    })();
</script>








