(() => {
    'use strict';

    const configNode = document.getElementById('ografi-page-layout-config');
    if (!configNode) return;

    let config;
    try {
        config = JSON.parse(configNode.textContent || '{}');
    } catch (error) {
        console.error('[Ografi layout] Yapilandirma okunamadi.', error);
        return;
    }

    if (!config || config.mode !== 'universal-dom' || !config.pageKey) return;

    const editorMode = config.editorPreview === true;
    const layout = config.layout && typeof config.layout === 'object' ? config.layout : {};
    let currentContainers = normalizeContainers(layout.containers);
    let customBlocks = Array.isArray(layout.custom_blocks) ? layout.custom_blocks : [];
    const nodeBySelector = new Map();
    const originalGroups = [];
    const groupByParent = new Map();
    const groupByNode = new Map();
    const uiStyle = document.createElement('style');

    uiStyle.id = 'ografi-universal-layout-style';
    uiStyle.textContent = [
        '[data-ografi-layout-hidden="1"]{display:none!important}',
        'html[data-ografi-layout-editing="1"] [data-ografi-layout-node]{outline:1px dashed transparent!important;outline-offset:-2px!important}',
        'html[data-ografi-layout-editing="1"] [data-ografi-layout-node]:hover{outline:2px dashed #2563eb!important;outline-offset:-2px!important}',
        '.ografi-layout-editor-tools{position:fixed!important;top:28px!important;left:8px!important;right:auto!important;z-index:2147483000!important;display:none!important;align-items:center!important;gap:4px!important;max-width:calc(100vw - 16px)!important;padding:4px!important;border:1px solid #bfdbfe!important;border-radius:7px!important;background:#eff6ff!important;color:#1d4ed8!important;font:600 11px/1.2 ui-sans-serif,system-ui,sans-serif!important;box-shadow:none!important}',
        '.ografi-layout-editor-tools.is-visible{display:flex!important}',
        '.ografi-layout-editor-tools button{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-width:28px!important;min-height:25px!important;padding:4px 7px!important;border:1px solid #bfdbfe!important;border-radius:5px!important;background:#fff!important;color:#1d4ed8!important;font:inherit!important;cursor:pointer!important;box-shadow:none!important}',
        '.ografi-layout-editor-tools button[data-ografi-layout-drag]{cursor:grab!important;font-size:15px!important}',
        '.ografi-layout-editor-tools button:hover{background:#dbeafe!important}',
        'html[data-ografi-layout-editing="1"] [data-ografi-layout-hidden="1"]{display:block!important;position:relative!important;min-height:44px!important;max-height:86px!important;overflow:hidden!important;opacity:.65!important;border:2px dashed #94a3b8!important;background:repeating-linear-gradient(135deg,#fff,#fff 8px,#f3f4f6 8px,#f3f4f6 16px)!important}',
        'html[data-ografi-layout-editing="1"] [data-ografi-layout-hidden="1"] > :not(.ografi-layout-editor-tools){visibility:hidden!important}',
        'html[data-ografi-layout-editing="1"] [data-ografi-layout-hidden="1"] > .ografi-layout-editor-tools{visibility:visible!important}',
        'html[data-ografi-layout-editing="1"] body::before{content:"DUZENLEME ONIZLEMESI — degisiklikler kaydedilene kadar yayinda degil";position:fixed!important;top:0!important;left:0!important;right:0!important;z-index:2147483647!important;padding:5px 10px!important;background:#1d4ed8!important;color:#fff!important;text-align:center!important;font:600 11px/1.3 ui-sans-serif,system-ui,sans-serif!important;pointer-events:none!important}',
        'html[data-ografi-layout-editing="1"] body{padding-top:28px!important}',
        '.ografi-builder-content-block{box-sizing:border-box!important;width:100%;margin:12px auto!important;border-radius:8px!important}',
        '.ografi-builder-content-block img{display:block!important;width:100%!important;height:auto!important;max-height:720px!important;object-fit:contain!important;margin:0 auto!important}'
    ].join('\n');
    document.head.appendChild(uiStyle);

    const blockTags = new Set([
        'DIV', 'SECTION', 'ARTICLE', 'ASIDE', 'NAV', 'HEADER', 'FOOTER', 'MAIN',
        'UL', 'OL', 'FORM', 'FIELDSET', 'FIGURE', 'TABLE', 'BLOCKQUOTE', 'DETAILS',
        'SUMMARY', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'
    ]);
    const excludedTags = new Set(['SCRIPT', 'STYLE', 'TEMPLATE', 'SVG', 'PATH', 'META', 'LINK', 'INPUT', 'OPTION']);
    const tokenPattern = /^[A-Za-z0-9_-]+$/;
    const safeIdPattern = /^[A-Za-z][A-Za-z0-9_-]{0,100}$/;
    let activeDragNode = null;
    let activeDragGroup = null;
    let activeToolbarNode = null;
    let floatingToolbar = null;
    let floatingHideButton = null;
    let floatingLabel = null;
    let stateDirty = false;

    function normalizeContainers(value) {
        if (!value || typeof value !== 'object' || Array.isArray(value)) return {};
        const result = {};
        Object.keys(value).slice(0, 80).forEach((parentSelector) => {
            const entry = value[parentSelector];
            if (!entry || !Array.isArray(entry.order)) return;
            result[parentSelector] = {
                order: entry.order.filter((s) => typeof s === 'string').slice(0, 120),
                hidden: Array.isArray(entry.hidden) ? entry.hidden.filter((s) => typeof s === 'string').slice(0, 120) : []
            };
        });
        return result;
    }

    function validSelector(selector) {
        return typeof selector === 'string'
            && selector.length > 0
            && selector.length <= 1800
            && /^[A-Za-z0-9_.:#() >-]+$/.test(selector);
    }

    function safeQuery(selector) {
        if (!validSelector(selector)) return null;
        try {
            return document.querySelector(selector);
        } catch (error) {
            return null;
        }
    }

    function selectorFor(element) {
        if (!(element instanceof Element)) return null;

        if (element.id && safeIdPattern.test(element.id)) {
            const byId = document.querySelectorAll('#' + element.id);
            if (byId.length === 1) return '#' + element.id;
        }

        const parts = [];
        let current = element;
        let depth = 0;

        while (current && current instanceof Element && current !== document.documentElement && depth < 16) {
            if (current === document.body) {
                parts.unshift('body');
                break;
            }

            const tag = current.tagName.toLowerCase();
            if (!/^[a-z][a-z0-9-]*$/.test(tag)) return null;

            const classes = Array.from(current.classList || [])
                .filter((name) => tokenPattern.test(name))
                .filter((name) => ![
                    'active', 'hidden', 'selected', 'open', 'show', 'dark', 'light',
                    'is-active', 'is-open', 'is-hidden', 'is-loading', 'is-selected'
                ].includes(name))
                .filter((name) => !/^(hover|focus|sm|md|lg|xl|dark|is|has)-/.test(name))
                .slice(0, 2);

            let segment = tag + classes.map((name) => '.' + name).join('');
            const parent = current.parentElement;

            if (parent) {
                const sameTag = Array.from(parent.children).filter((sibling) => sibling.tagName === current.tagName);
                if (sameTag.length > 1) segment += ':nth-of-type(' + (sameTag.indexOf(current) + 1) + ')';
            }

            parts.unshift(segment);
            current = parent;
            depth++;
        }

        const selector = parts.join(' > ');
        return validSelector(selector) ? selector : null;
    }

    function visibleBlock(element) {
        if (!(element instanceof Element) || !blockTags.has(element.tagName) || excludedTags.has(element.tagName)) return false;
        if (element.closest('[data-ografi-layout-ignore],.ografi-layout-editor-tools')) return false;
        if (element.hasAttribute('data-ografi-custom-block-id')) return true;
        if (element.hidden || element.getAttribute('aria-hidden') === 'true') return false;

        const css = window.getComputedStyle(element);
        if (css.display === 'none' || css.display === 'contents' || css.visibility === 'hidden') return false;
        if (css.position === 'fixed' && !/^(HEADER|NAV|FOOTER)$/.test(element.tagName)) return false;

        const rect = element.getBoundingClientRect();
        return rect.width >= 36 && rect.height >= 16;
    }

    function defaultContainer() {
        return document.querySelector('main.layout-main')
            || document.querySelector('main')
            || document.querySelector('.home-feed-shell')
            || document.querySelector('.post-show-shell')
            || document.body;
    }

    function plainText(value) {
        return String(value == null ? '' : value);
    }

    function color(value, fallback) {
        return /^#[0-9a-f]{6}$/i.test(String(value || '')) ? String(value).toLowerCase() : fallback;
    }

    function safeUrl(value) {
        const url = plainText(value).trim();
        if (!url || /[\u0000-\u0020\\]/.test(url)) return '';
        if (url.charAt(0) === '#' && url.length < 200) return url;
        if (url.charAt(0) === '/' && url.charAt(1) !== '/') return url;
        try {
            const parsed = new URL(url);
            return parsed.protocol === 'https:' && !!parsed.hostname ? url : '';
        } catch (error) {
            return '';
        }
    }

    function addTextElement(parent, tag, text, styles) {
        const element = document.createElement(tag);
        element.textContent = plainText(text);
        Object.keys(styles || {}).forEach((key) => { element.style[key] = styles[key]; });
        parent.appendChild(element);
        return element;
    }

    function renderCustomBlock(block) {
        const node = document.createElement('section');
        node.className = 'ografi-builder-content-block';
        node.dataset.ografiCustomBlockId = block.id;
        node.dataset.ografiLayoutNodeId = block.id;
        node.dataset.ografiLayoutNode = '1';
        node.style.maxWidth = Math.max(240, Math.min(1600, Number(block.max_width) || 960)) + 'px';
        node.style.padding = Math.max(0, Math.min(64, Number(block.padding) || 0)) + 'px';
        node.style.textAlign = ['left', 'center', 'right'].includes(block.align) ? block.align : 'left';
        node.style.backgroundColor = color(block.background, '#ffffff');
        node.style.color = color(block.text_color, '#111827');
        node.dataset.ografiLayoutLabel = block.label || block.title || block.type || 'Icerik blogu';

        switch (block.type) {
            case 'heading':
                addTextElement(node, 'h2', block.title || 'Yeni baslik', {
                    margin: '0', fontSize: 'clamp(22px, 3vw, 32px)', fontWeight: '700', lineHeight: '1.25'
                });
                break;
            case 'text':
                addTextElement(node, 'p', block.text || 'Metin blogu', {
                    margin: '0', fontSize: '16px', lineHeight: '1.7', whiteSpace: 'pre-wrap'
                });
                break;
            case 'image': {
                const url = safeUrl(block.image_url);
                if (url) {
                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = plainText(block.alt);
                    image.loading = 'lazy';
                    image.decoding = 'async';
                    node.appendChild(image);
                } else if (editorMode) {
                    addTextElement(node, 'p', 'Gorsel eklemek icin HTTPS gorsel adresini saga yaz.', {
                        margin: '0', padding: '22px 10px', color: '#64748b', border: '1px dashed #cbd5e1'
                    });
                } else {
                    node.hidden = true;
                }
                break;
            }
            case 'button': {
                const url = safeUrl(block.url);
                const button = document.createElement(url ? 'a' : 'span');
                if (url) button.href = url;
                button.textContent = plainText(block.button_text || 'Detaylari gor');
                Object.assign(button.style, {
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                    padding: '10px 18px', borderRadius: '8px', backgroundColor: '#2563eb',
                    color: '#ffffff', fontWeight: '600', textDecoration: 'none'
                });
                node.appendChild(button);
                break;
            }
            case 'callout':
                if (block.title) addTextElement(node, 'h3', block.title, { margin: '0 0 8px', fontSize: '20px', fontWeight: '700' });
                addTextElement(node, 'p', block.text || 'Vurgulu alan metni', { margin: '0', lineHeight: '1.65', whiteSpace: 'pre-wrap' });
                node.style.border = '1px solid ' + color(block.text_color, '#111827') + '33';
                break;
            case 'divider': {
                node.style.padding = '8px 0';
                const hr = document.createElement('hr');
                hr.style.border = '0';
                hr.style.borderTop = '1px solid #e5e7eb';
                hr.style.margin = '0';
                node.appendChild(hr);
                break;
            }
            case 'spacer':
                node.style.minHeight = Math.max(8, Math.min(240, Number(block.height) || 32)) + 'px';
                node.style.padding = '0';
                node.style.backgroundColor = 'transparent';
                if (editorMode) node.style.border = '1px dashed #cbd5e1';
                break;
            default:
                addTextElement(node, 'p', block.text || '', { margin: '0' });
        }

        return node;
    }

    function findCustomNode(id) {
        const safe = String(id).replace(/[^A-Za-z0-9_-]/g, '');
        if (!safe) return null;
        return document.querySelector('[data-ografi-custom-block-id="' + safe + '"]');
    }

    function ensureCustomBlocks() {
        const liveIds = new Set(customBlocks.map((block) => block.id));
        document.querySelectorAll('[data-ografi-custom-block-id]').forEach((node) => {
            if (!liveIds.has(node.dataset.ografiCustomBlockId)) node.remove();
        });

        customBlocks.forEach((block) => {
            if (!block || !/^custom_[A-Za-z0-9_-]{8,64}$/.test(String(block.id || ''))) return;
            const existing = findCustomNode(block.id);
            const signature = JSON.stringify(block);

            if (existing && existing.dataset.ografiBlockSignature === signature) return;

            const replacement = renderCustomBlock(block);
            replacement.dataset.ografiBlockSignature = signature;

            let parent = block.container_selector ? safeQuery(block.container_selector) : null;

            if (!parent) {
                const savedGroup = Object.values(currentContainers).find((group) => Array.isArray(group.order) && group.order.includes(block.id));
                const parentSelector = savedGroup ? Object.keys(currentContainers).find((key) => currentContainers[key] === savedGroup) : null;
                if (parentSelector) parent = safeQuery(parentSelector);
            }

            if (!parent && existing && existing.parentElement) parent = existing.parentElement;
            if (!parent) parent = defaultContainer();
            if (!parent) return;

            if (existing) existing.replaceWith(replacement);
            else parent.appendChild(replacement);

            block.container_selector = selectorFor(parent) || block.container_selector || '';
            nodeBySelector.set(block.id, replacement);
        });
    }

    function captureGroups() {
        const candidates = Array.from(document.body.querySelectorAll(Array.from(blockTags).map((tag) => tag.toLowerCase()).join(',')))
            .filter(visibleBlock)
            .slice(0, 1800);

        nodeBySelector.clear();
        nodeBySelector.set('body', document.body);

        candidates.forEach((node) => {
            const selector = node.dataset.ografiLayoutNodeId || selectorFor(node);
            if (!selector) return;
            node.dataset.ografiLayoutNode = '1';
            node.dataset.ografiLayoutNodeId = selector;
            nodeBySelector.set(selector, node);
        });

        // Parent nodes are indexed separately so saved containers still resolve after
        // sibling order is applied later in this same browser session.
        const allGroups = new Map();
        candidates.forEach((node) => {
            const parent = node.parentElement;
            if (!parent || parent.closest('[data-ografi-layout-ignore],.ografi-layout-editor-tools')) return;
            // Keep the selector captured from the original Blade output after sibling moves;
            // recomputing nth-of-type selectors after drag/drop would change the storage key.
            const parentSelector = parent.dataset.ografiLayoutNodeId || selectorFor(parent);
            if (!parentSelector) return;
            nodeBySelector.set(parentSelector, parent);

            if (!allGroups.has(parentSelector)) {
                allGroups.set(parentSelector, { parent, parentSelector, nodes: [] });
            }
            const group = allGroups.get(parentSelector);
            if (!group.nodes.includes(node)) group.nodes.push(node);
        });

        const groups = Array.from(allGroups.values())
            .filter((group) => group.nodes.length >= 2)
            .sort((a, b) => priority(b.parent) - priority(a.parent))
            .slice(0, 80);

        originalGroups.length = 0;
        groupByParent.clear();
        groupByNode.clear();

        groups.forEach((group) => {
            group.nodes.sort(domOrder);
            originalGroups.push(group);
            groupByParent.set(group.parent, group);
            group.nodes.forEach((node) => groupByNode.set(node, group));
        });
    }

    function domOrder(a, b) {
        const relation = a.compareDocumentPosition(b);
        if (relation & Node.DOCUMENT_POSITION_FOLLOWING) return -1;
        if (relation & Node.DOCUMENT_POSITION_PRECEDING) return 1;
        return 0;
    }

    function domDepth(element) {
        let depth = 0;
        while (element && element.parentElement) {
            depth++;
            element = element.parentElement;
        }
        return depth;
    }

    function priority(parent) {
        let score = 0;
        const classes = typeof parent.className === 'string' ? parent.className : '';
        if (parent.matches('main,[role="main"]')) score += 40;
        if (parent.closest('main,[role="main"]')) score += 20;
        if (/feed|content|posts|comments|profile|category|tag|page|layout-main|main-grid/i.test(classes)) score += 14;
        if (/sidebar|bottom-nav|editor-tools|modal|dropdown|popover/i.test(classes)) score -= 40;
        return score + Math.min(12, domDepth(parent)) * 0.4;
    }

    function applyCurrentContainers() {
        const groups = [];

        Object.entries(currentContainers).forEach(([parentSelector, saved]) => {
            const parent = nodeBySelector.get(parentSelector) || safeQuery(parentSelector);
            if (!parent || !Array.isArray(saved.order)) return;

            const nodes = [];
            saved.order.forEach((selector) => {
                let node = nodeBySelector.get(selector) || findCustomNode(selector) || safeQuery(selector);
                if (node && node.parentElement === parent && !nodes.includes(node)) nodes.push(node);
            });

            if (nodes.length >= 2) groups.push({ parent, parentSelector, nodes, order: saved.order, hidden: saved.hidden || [] });
        });

        groups.forEach((group) => reorder(group.parent, group.nodes, group.order));

        Object.values(currentContainers).forEach((saved) => {
            (saved.hidden || []).forEach((selector) => {
                const node = nodeBySelector.get(selector) || findCustomNode(selector) || safeQuery(selector);
                if (node) node.dataset.ografiLayoutHidden = '1';
            });
        });

        originalGroups.forEach((group) => group.nodes.forEach((node) => {
            const selectors = currentContainers[group.parentSelector];
            const isHidden = selectors && Array.isArray(selectors.hidden) && selectors.hidden.includes(node.dataset.ografiLayoutNodeId);
            if (!isHidden) delete node.dataset.ografiLayoutHidden;
        }));
    }

    function reorder(parent, nodes, desiredOrder) {
        const eligible = nodes.filter((node) => node && node.parentElement === parent);
        if (eligible.length < 2) return;

        const original = eligible.slice().sort(domOrder);
        const seen = new Set();
        const desired = [];

        desiredOrder.forEach((selector) => {
            const node = nodeBySelector.get(selector) || findCustomNode(selector) || safeQuery(selector);
            if (node && node.parentElement === parent && eligible.includes(node) && !seen.has(node)) {
                desired.push(node);
                seen.add(node);
            }
        });
        original.forEach((node) => {
            if (!seen.has(node)) desired.push(node);
        });
        if (desired.length !== original.length) return;

        const placeholders = original.map((node, index) => {
            const placeholder = document.createComment('ografi-layout-slot-' + index);
            node.replaceWith(placeholder);
            return placeholder;
        });
        placeholders.forEach((placeholder, index) => placeholder.replaceWith(desired[index]));
    }

    function ensureGroupForCustomBlocks() {
        customBlocks.forEach((block) => {
            const node = findCustomNode(block.id);
            if (!node || !node.parentElement) return;

            const parent = node.parentElement;
            const parentSelector = selectorFor(parent);
            if (!parentSelector) return;
            nodeBySelector.set(block.id, node);

            let group = groupByParent.get(parent);
            if (!group) {
                group = { parent, parentSelector, nodes: [] };
                groupByParent.set(parent, group);
                originalGroups.push(group);
            }

            if (!group.nodes.includes(node)) group.nodes.push(node);
            group.nodes.sort(domOrder);
            groupByNode.set(node, group);
            node.dataset.ografiLayoutNode = '1';
            node.dataset.ografiLayoutNodeId = block.id;
        });
    }

    function currentSnapshot() {
        const containers = {};

        originalGroups.forEach((group) => {
            const order = group.nodes
                .filter((node) => node && node.parentElement === group.parent)
                .slice()
                .sort(domOrder)
                .map((node) => node.dataset.ografiLayoutNodeId)
                .filter((selector) => typeof selector === 'string' && selector.length > 0);

            const hidden = group.nodes
                .filter((node) => node.dataset.ografiLayoutHidden === '1')
                .map((node) => node.dataset.ografiLayoutNodeId)
                .filter((selector) => typeof selector === 'string' && selector.length > 0);

            if (order.length >= 2) containers[group.parentSelector] = { order, hidden };
        });

        return {
            containers,
            custom_blocks: customBlocks.map((block) => Object.assign({}, block))
        };
    }

    function postState(dirty) {
        if (!editorMode || window.parent === window) return;
        stateDirty = dirty === true;
        window.parent.postMessage({
            type: 'ografi-layout-editor-state',
            pageKey: config.pageKey,
            layout: currentSnapshot(),
            dirty: stateDirty
        }, window.location.origin);
    }

    function ensureFloatingToolbar() {
        if (!editorMode || floatingToolbar) return;

        floatingToolbar = document.createElement('div');
        floatingToolbar.className = 'ografi-layout-editor-tools';
        floatingToolbar.dataset.ografiLayoutIgnore = '1';

        const drag = document.createElement('button');
        drag.type = 'button';
        drag.draggable = true;
        drag.dataset.ografiLayoutDrag = '1';
        drag.title = 'Bolumu surukle';
        drag.setAttribute('aria-label', 'Bolumu surukle');
        drag.textContent = '⠿';

        floatingHideButton = document.createElement('button');
        floatingHideButton.type = 'button';
        floatingHideButton.dataset.ografiLayoutHide = '1';

        floatingLabel = document.createElement('span');
        floatingToolbar.append(drag, floatingHideButton, floatingLabel);
        document.body.appendChild(floatingToolbar);
    }

    function showFloatingToolbar(node) {
        if (!editorMode || !node || !floatingToolbar || activeDragNode) return;
        activeToolbarNode = node;

        const hidden = node.dataset.ografiLayoutHidden === '1';
        floatingHideButton.textContent = hidden ? 'Goster' : 'Gizle';
        floatingHideButton.title = floatingHideButton.textContent;
        floatingHideButton.setAttribute('aria-label', floatingHideButton.textContent);

        const classes = Array.from(node.classList || []).filter((name) => tokenPattern.test(name)).slice(0, 1);
        floatingLabel.textContent = node.tagName.toLowerCase() + (classes.length ? '.' + classes[0] : '');
        floatingLabel.title = node.dataset.ografiLayoutNodeId || '';

        floatingToolbar.classList.add('is-visible');
        const toolbarRect = floatingToolbar.getBoundingClientRect();
        const rect = node.getBoundingClientRect();
        const topMax = Math.max(28, window.innerHeight - toolbarRect.height - 8);
        const leftMax = Math.max(8, window.innerWidth - toolbarRect.width - 8);
        floatingToolbar.style.top = Math.min(topMax, Math.max(28, rect.top + 4)) + 'px';
        floatingToolbar.style.left = Math.min(leftMax, Math.max(8, rect.right - toolbarRect.width - 4)) + 'px';
    }

    function hideFloatingToolbar() {
        if (!floatingToolbar || activeDragNode) return;
        floatingToolbar.classList.remove('is-visible');
        activeToolbarNode = null;
    }

    function toolsFor(node) {
        if (!editorMode || !node) return;
        node.dataset.ografiLayoutNode = '1';
    }

    function nodeForEvent(target) {
        if (!(target instanceof Element)) return null;
        if (target.closest('.ografi-layout-editor-tools')) return null;
        return target.closest('[data-ografi-layout-node="1"]');
    }

    function markHidden(node, hidden) {
        if (!node) return;
        if (hidden) node.dataset.ografiLayoutHidden = '1';
        else delete node.dataset.ografiLayoutHidden;

        if (node === activeToolbarNode && floatingHideButton) {
            floatingHideButton.textContent = hidden ? 'Goster' : 'Gizle';
            floatingHideButton.title = floatingHideButton.textContent;
            floatingHideButton.setAttribute('aria-label', floatingHideButton.textContent);
        }
    }

    function refreshCustomBlockContents() {
        customBlocks.forEach((block) => {
            let node = findCustomNode(block.id);
            const desiredSignature = JSON.stringify(block);

            if (node && node.dataset.ografiBlockSignature === desiredSignature) return;

            const newNode = renderCustomBlock(block);
            newNode.dataset.ografiBlockSignature = desiredSignature;

            // When editing an existing custom block, its live parent node is the
            // reliable target after other sections have been reordered. Resolve the saved
            // selector only for a block that has not yet been inserted on this page.
            let parent = node && node.parentElement
                ? node.parentElement
                : (block.container_selector ? safeQuery(block.container_selector) : null);
            if (!parent) parent = defaultContainer();
            if (!parent) return;

            if (node) {
                node.replaceWith(newNode);
                if (activeToolbarNode === node) activeToolbarNode = newNode;
            } else {
                parent.appendChild(newNode);
            }

            const selector = block.id;
            newNode.dataset.ografiLayoutNodeId = selector;
            newNode.dataset.ografiLayoutNode = '1';
            nodeBySelector.set(selector, newNode);
            block.container_selector = selectorFor(parent) || block.container_selector || '';

            const group = groupByParent.get(parent);
            if (group && !group.nodes.includes(newNode)) {
                group.nodes.push(newNode);
                group.nodes.sort(domOrder);
                groupByNode.set(newNode, group);
            } else if (!group) {
                const parentSelector = selectorFor(parent);
                if (parentSelector) {
                    const newGroup = { parent, parentSelector, nodes: [newNode] };
                    originalGroups.push(newGroup);
                    groupByParent.set(parent, newGroup);
                    groupByNode.set(newNode, newGroup);
                }
            }

            if (editorMode) toolsFor(newNode);
        });
    }

    function applyLayoutAndEditorState() {
        refreshCustomBlockContents();
        captureGroups();
        // Retain the custom block map after baseline selector discovery.
        customBlocks.forEach((block) => {
            const node = findCustomNode(block.id);
            if (!node) return;
            node.dataset.ografiLayoutNodeId = block.id;
            node.dataset.ografiLayoutNode = '1';
            nodeBySelector.set(block.id, node);
            const parent = node.parentElement;
            const group = groupByParent.get(parent);
            if (group) {
                if (!group.nodes.includes(node)) group.nodes.push(node);
                group.nodes.sort(domOrder);
                groupByNode.set(node, group);
            }
        });

        applyCurrentContainers();
        ensureGroupForCustomBlocks();
        if (editorMode) {
            document.documentElement.dataset.ografiLayoutEditing = '1';
            originalGroups.forEach((group) => group.nodes.forEach(toolsFor));
        }
    }

    // Normal page load: create saved blocks and apply saved layout. On preview load,
    // the admin can drag blocks directly over the existing page.
    applyLayoutAndEditorState();

    if (editorMode) {
        ensureFloatingToolbar();

        document.addEventListener('pointerover', (event) => {
            if (!(event.target instanceof Element)) return;
            if (event.target.closest('.ografi-layout-editor-tools')) return;
            if (activeDragNode) return;

            const node = event.target.closest('[data-ografi-layout-node="1"]');
            if (node) showFloatingToolbar(node);
            else hideFloatingToolbar();
        }, true);

        window.addEventListener('scroll', () => {
            if (activeToolbarNode && !activeDragNode) showFloatingToolbar(activeToolbarNode);
        }, true);
        window.addEventListener('resize', () => {
            if (activeToolbarNode && !activeDragNode) showFloatingToolbar(activeToolbarNode);
        });

        document.addEventListener('click', (event) => {
            const target = event.target instanceof Element ? event.target : null;
            if (!target) return;

            if (target.closest('.ografi-layout-editor-tools')) {
                const button = target.closest('[data-ografi-layout-hide]');
                if (button && activeToolbarNode) {
                    event.preventDefault();
                    event.stopPropagation();
                    markHidden(activeToolbarNode, activeToolbarNode.dataset.ografiLayoutHidden !== '1');
                    showFloatingToolbar(activeToolbarNode);
                    postState(true);
                }
                return;
            }

            const interactive = target.closest('a,button,input,select,textarea,form,[contenteditable="true"]');
            if (interactive) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);

        document.addEventListener('dragstart', (event) => {
            if (!(event.target instanceof Element)) return;
            const handle = event.target.closest('[data-ografi-layout-drag]');
            if (!handle) return;

            const node = activeToolbarNode;
            const group = node ? groupByParent.get(node.parentElement) : null;
            if (!node || !group || !group.nodes.includes(node)) {
                event.preventDefault();
                return;
            }

            activeDragNode = node;
            activeDragGroup = group;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', node.dataset.ografiLayoutNodeId || '');
            floatingToolbar.classList.remove('is-visible');
            node.style.opacity = '.55';
        }, true);

        document.addEventListener('dragover', (event) => {
            if (!activeDragNode || !activeDragGroup || !(event.target instanceof Element)) return;
            const target = nodeForEvent(event.target);
            if (!target || target === activeDragNode || target.parentElement !== activeDragGroup.parent) return;
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        }, true);

        document.addEventListener('drop', (event) => {
            if (!activeDragNode || !activeDragGroup || !(event.target instanceof Element)) return;
            const target = nodeForEvent(event.target);
            if (!target || target === activeDragNode || target.parentElement !== activeDragGroup.parent) return;

            event.preventDefault();
            const rect = target.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;
            target.parentElement.insertBefore(activeDragNode, before ? target : target.nextSibling);
            activeDragGroup.nodes.forEach((node) => { node.style.opacity = ''; });
            activeDragGroup.nodes.sort(domOrder);
            postState(true);
        }, true);

        document.addEventListener('dragend', () => {
            const node = activeDragNode;
            if (node) node.style.opacity = '';
            activeDragNode = null;
            activeDragGroup = null;
            if (node) showFloatingToolbar(node);
        }, true);

        // Keyboard alternative: Alt + ArrowUp / ArrowDown reorders within siblings.
        document.addEventListener('keydown', (event) => {
            if (!event.altKey || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
            if (!(event.target instanceof Element)) return;
            const node = event.target.closest('[data-ografi-layout-node="1"]');
            const group = node ? groupByParent.get(node.parentElement) : null;
            if (!node || !group) return;

            const siblings = group.nodes.filter((item) => item.parentElement === group.parent);
            const index = siblings.indexOf(node);
            if (event.key === 'ArrowUp' && index > 0) {
                event.preventDefault();
                group.parent.insertBefore(node, siblings[index - 1]);
            } else if (event.key === 'ArrowDown' && index >= 0 && index < siblings.length - 1) {
                event.preventDefault();
                group.parent.insertBefore(node, siblings[index + 1].nextSibling);
            } else {
                return;
            }
            group.nodes.sort(domOrder);
            postState(true);
        });

        postState(false);
    }

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== window.parent || !event.data) return;
        if (event.data.type !== 'ografi-layout-apply-draft' || event.data.pageKey !== config.pageKey) return;
        if (!event.data.layout || typeof event.data.layout !== 'object') return;

        const incoming = event.data.layout;
        currentContainers = normalizeContainers(incoming.containers);
        customBlocks = Array.isArray(incoming.custom_blocks) ? incoming.custom_blocks : [];
        config.layout = { containers: currentContainers, custom_blocks: customBlocks };
        applyLayoutAndEditorState();
        postState(false);
    });

    // Notify parent that the preview is ready and what route this URL actually resolves to.
    postState(false);
})();
