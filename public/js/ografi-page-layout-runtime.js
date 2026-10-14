(() => {
    'use strict';

    const configNode = document.getElementById('ografi-page-layout-config');
    if (!configNode) return;

    let config;
    try {
        config = JSON.parse(configNode.textContent || '{}');
    } catch (error) {
        console.error('[Ografi layout] Invalid layout config.', error);
        return;
    }

    if (config.mode !== 'universal-dom' || !config.pageKey) return;

    const EDITOR = config.editorPreview === true;
    const layout = config.layout && typeof config.layout === 'object' ? config.layout : {};
    const storedContainers = layout.containers && typeof layout.containers === 'object' ? layout.containers : {};
    const blockSelector = 'div, section, article, aside, nav, header, footer, main, ul, ol, form, fieldset, figure, table, blockquote, details, summary';
    const parentToGroup = new Map();
    const selectorToNode = new Map();
    const editorStyle = document.createElement('style');

    editorStyle.id = 'ografi-universal-page-editor-style';
    editorStyle.textContent = `
        [data-ografi-layout-hidden="1"] { display: none !important; }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-node] {
            position: relative !important;
            outline: 1px dashed rgba(37, 99, 235, .35) !important;
            outline-offset: -1px !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-node]:hover {
            outline: 2px solid #2563eb !important;
            outline-offset: -2px !important;
        }
        .ografi-layout-editor-tools {
            position: absolute !important;
            top: 4px !important;
            right: 4px !important;
            z-index: 2147483000 !important;
            display: none !important;
            align-items: center !important;
            gap: 4px !important;
            max-width: calc(100% - 8px) !important;
            padding: 4px !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 7px !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            font: 600 11px/1.2 ui-sans-serif, system-ui, sans-serif !important;
            box-shadow: none !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-node]:hover > .ografi-layout-editor-tools,
        html[data-ografi-layout-editing="1"] [data-ografi-layout-node]:focus-within > .ografi-layout-editor-tools {
            display: flex !important;
        }
        .ografi-layout-editor-tools button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 28px !important;
            min-height: 25px !important;
            padding: 4px 7px !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 5px !important;
            background: #ffffff !important;
            color: #1d4ed8 !important;
            font: inherit !important;
            cursor: pointer !important;
            box-shadow: none !important;
        }
        .ografi-layout-editor-tools button[data-ografi-layout-drag] {
            cursor: grab !important;
            font-size: 15px !important;
        }
        .ografi-layout-editor-tools button:hover {
            background: #dbeafe !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-hidden="1"] {
            display: block !important;
            position: relative !important;
            min-height: 48px !important;
            max-height: 92px !important;
            overflow: hidden !important;
            opacity: .65 !important;
            border: 2px dashed #94a3b8 !important;
            background: repeating-linear-gradient(135deg, #fff, #fff 8px, #f3f4f6 8px, #f3f4f6 16px) !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-hidden="1"] > :not(.ografi-layout-editor-tools) {
            visibility: hidden !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-hidden="1"] > .ografi-layout-editor-tools {
            visibility: visible !important;
        }
        html[data-ografi-layout-editing="1"] body::before {
            content: 'DUZENLEME ONIZLEMESI — degisiklikler kaydedilene kadar yayinda degil';
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 2147483647 !important;
            padding: 5px 10px !important;
            background: #1d4ed8 !important;
            color: #fff !important;
            text-align: center !important;
            font: 600 11px/1.3 ui-sans-serif, system-ui, sans-serif !important;
            pointer-events: none !important;
        }
        html[data-ografi-layout-editing="1"] body { padding-top: 28px !important; }
    `;
    document.head.appendChild(editorStyle);

    const simpleToken = /^[A-Za-z0-9_-]+$/;
    const skipTags = new Set(['SCRIPT', 'STYLE', 'TEMPLATE', 'SVG', 'PATH', 'META', 'LINK', 'BR', 'HR', 'INPUT', 'OPTION']);
    const escapeId = (value) => String(value).replace(/[^A-Za-z0-9_-]/g, '');

    const selectorFor = (element) => {
        if (!element || element.nodeType !== 1) return null;

        if (element.id && /^[A-Za-z][A-Za-z0-9_-]{0,100}$/.test(element.id)) {
            return '#' + element.id;
        }

        const parts = [];
        let current = element;
        let depth = 0;

        while (current && current.nodeType === 1 && current !== document.documentElement && depth < 14) {
            if (current === document.body) {
                parts.unshift('body');
                break;
            }

            const tag = current.tagName.toLowerCase();
            if (!/^[a-z][a-z0-9-]*$/.test(tag)) return null;

            const classTokens = Array.from(current.classList || [])
                .filter((token) => simpleToken.test(token))
                .filter((token) => !['dark', 'active', 'hidden', 'selected', 'open', 'show', 'is-active'].includes(token))
                .filter((token) => !/^(?:hover|focus|md|lg|xl|sm|dark|is|has)-/.test(token))
                .slice(0, 2);

            let segment = tag + classTokens.map((token) => '.' + token).join('');
            const parent = current.parentElement;

            if (parent) {
                const sameTag = Array.from(parent.children).filter((sibling) => sibling.tagName === current.tagName);
                if (sameTag.length > 1) {
                    segment += ':nth-of-type(' + (sameTag.indexOf(current) + 1) + ')';
                }
            }

            parts.unshift(segment);
            current = parent;
            depth++;
        }

        const selector = parts.join(' > ');
        return selector && selector.length <= 1800 ? selector : null;
    };

    const safeQuery = (selector) => {
        if (typeof selector !== 'string' || selector.length > 1800) return null;
        try {
            return document.querySelector(selector);
        } catch (error) {
            return null;
        }
    };

    const isVisibleBlock = (element) => {
        if (!element || skipTags.has(element.tagName) || !element.matches(blockSelector)) return false;
        if (element.closest('.ografi-layout-editor-tools, [data-ografi-layout-ignore]')) return false;
        if (element.hidden || element.getAttribute('aria-hidden') === 'true') return false;

        const computed = window.getComputedStyle(element);
        if (computed.display === 'none' || computed.display === 'contents' || computed.visibility === 'hidden') return false;
        if (computed.position === 'fixed' && !element.matches('header, nav, footer')) return false;

        const rect = element.getBoundingClientRect();
        return rect.width >= 36 && rect.height >= 16;
    };

    const domDepth = (element) => {
        let depth = 0;
        while (element && element.parentElement) {
            depth++;
            element = element.parentElement;
        }
        return depth;
    };

    const parentPriority = (parent) => {
        let score = 0;
        const classes = typeof parent.className === 'string' ? parent.className : '';
        if (parent.matches('main, [role="main"]')) score += 40;
        if (parent.closest('main, [role="main"]')) score += 22;
        if (/feed|content|posts|comments|profile|category|tag|page|layout-main|main-grid/i.test(classes)) score += 14;
        if (/sidebar|bottom-nav|editor-tools|modal|dropdown|popover/i.test(classes)) score -= 40;
        score += Math.min(12, domDepth(parent)) * 0.5;
        return score;
    };

    const captureBaselineGroups = () => {
        const candidates = Array.from(document.body.querySelectorAll(blockSelector))
            .filter(isVisibleBlock)
            .slice(0, 1600);

        candidates.forEach((node) => {
            const selector = selectorFor(node);
            if (!selector) return;
            node.dataset.ografiLayoutNode = '1';
            node.dataset.ografiLayoutNodeId = selector;
            selectorToNode.set(selector, node);
        });

        const rawGroups = new Map();

        candidates.forEach((node) => {
            const parent = node.parentElement;
            if (!parent || parent.closest('.ografi-layout-editor-tools, [data-ografi-layout-ignore]')) return;
            const selector = selectorFor(parent);
            if (!selector) return;

            if (!rawGroups.has(selector)) {
                rawGroups.set(selector, { parent, parentSelector: selector, nodes: [] });
            }
            rawGroups.get(selector).nodes.push(node);
        });

        return Array.from(rawGroups.values())
            .filter((group) => group.nodes.length >= 2)
            .map((group) => {
                const uniqueNodes = Array.from(new Set(group.nodes));
                uniqueNodes.sort((a, b) => {
                    const relation = a.compareDocumentPosition(b);
                    return relation & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : relation & Node.DOCUMENT_POSITION_PRECEDING ? 1 : 0;
                });
                return { ...group, nodes: uniqueNodes };
            })
            .sort((a, b) => parentPriority(b.parent) - parentPriority(a.parent))
            .slice(0, 80);
    };

    const originalGroups = captureBaselineGroups();

    originalGroups.forEach((group) => {
        group.nodes.forEach((node) => {
            node.dataset.ografiLayoutNode = '1';
            node.dataset.ografiLayoutNodeId = selectorFor(node) || node.dataset.ografiLayoutNodeId || '';
        });
        parentToGroup.set(group.parent, group);
    });

    const resolveSavedGroups = (containers) => {
        const resolved = [];

        Object.entries(containers || {}).slice(0, 80).forEach(([parentSelector, item]) => {
            if (!item || !Array.isArray(item.order)) return;
            const parent = safeQuery(parentSelector);
            if (!parent) return;

            const originalNodes = [];
            item.order.slice(0, 120).forEach((selector) => {
                const node = safeQuery(selector);
                if (node && node.parentElement === parent && !originalNodes.includes(node)) {
                    originalNodes.push(node);
                }
            });

            if (originalNodes.length >= 2) {
                resolved.push({
                    parent,
                    parentSelector,
                    nodes: originalNodes,
                    order: item.order,
                    hidden: Array.isArray(item.hidden) ? item.hidden : [],
                });
            }
        });

        return resolved;
    };

    const reorderGroup = (parent, nodes, desiredOrder) => {
        const eligible = nodes.filter((node) => node && node.parentElement === parent);
        if (eligible.length < 2) return;

        const seen = new Set();
        const originalSlots = eligible.slice().sort((a, b) => {
            const relation = a.compareDocumentPosition(b);
            return relation & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : relation & Node.DOCUMENT_POSITION_PRECEDING ? 1 : 0;
        });

        const desired = [];
        desiredOrder.forEach((selector) => {
            const node = selectorToNode.get(selector) || safeQuery(selector);
            if (node && node.parentElement === parent && eligible.includes(node) && !seen.has(node)) {
                desired.push(node);
                seen.add(node);
            }
        });
        originalSlots.forEach((node) => {
            if (!seen.has(node)) desired.push(node);
        });

        if (desired.length !== originalSlots.length) return;

        const placeholders = originalSlots.map((node, index) => {
            const placeholder = document.createComment('ografi-layout-position-' + index);
            node.replaceWith(placeholder);
            return placeholder;
        });

        placeholders.forEach((placeholder, index) => placeholder.replaceWith(desired[index]));
    };

    const applyStoredLayout = () => {
        // Resolve every selector before changing the DOM. Selectors are based on the
        // original Blade output and must not be re-evaluated after sibling positions change.
        const groups = resolveSavedGroups(storedContainers);
        groups.forEach((group) => reorderGroup(group.parent, group.nodes, group.order));

        Object.entries(storedContainers || {}).forEach(([, item]) => {
            if (!Array.isArray(item?.hidden)) return;
            item.hidden.forEach((selector) => {
                const node = selectorToNode.get(selector) || safeQuery(selector);
                if (node) node.dataset.ografiLayoutHidden = '1';
            });
        });

        originalGroups.forEach((group) => {
            group.nodes.forEach((node) => {
                if (node.dataset.ografiLayoutHidden !== '1') delete node.dataset.ografiLayoutHidden;
            });
        });
    };

    const currentSnapshot = () => {
        const containers = {};

        originalGroups.forEach((group) => {
            const nodeSelectors = group.nodes
                .filter((node) => node.parentElement === group.parent)
                .slice()
                .sort((a, b) => {
                    const relation = a.compareDocumentPosition(b);
                    return relation & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : relation & Node.DOCUMENT_POSITION_PRECEDING ? 1 : 0;
                })
                .map((node) => node.dataset.ografiLayoutNodeId)
                .filter((selector) => typeof selector === 'string' && selector.length > 0);

            const hidden = group.nodes
                .filter((node) => node.dataset.ografiLayoutHidden === '1')
                .map((node) => node.dataset.ografiLayoutNodeId)
                .filter((selector) => typeof selector === 'string' && selector.length > 0);

            if (nodeSelectors.length >= 2) {
                containers[group.parentSelector] = { order: nodeSelectors, hidden };
            }
        });

        return { containers };
    };

    const postState = (dirty) => {
        if (!EDITOR || window.parent === window) return;
        window.parent.postMessage({
            type: 'ografi-layout-editor-state',
            pageKey: config.pageKey,
            layout: currentSnapshot(),
            dirty: dirty === true,
        }, window.location.origin);
    };

    const updateHiddenStyles = (node, hidden) => {
        if (!node) return;
        if (hidden) {
            node.dataset.ografiLayoutHidden = '1';
        } else {
            delete node.dataset.ografiLayoutHidden;
        }

        const button = node.querySelector(':scope > .ografi-layout-editor-tools [data-ografi-layout-hide]');
        if (button) {
            button.textContent = hidden ? 'Goster' : 'Gizle';
            button.setAttribute('aria-label', hidden ? 'Bolumu yeniden goster' : 'Bolumu gizle');
        }
    };

    const createEditorTools = (node) => {
        if (node.querySelector(':scope > .ografi-layout-editor-tools')) return;

        const selector = node.dataset.ografiLayoutNodeId;
        if (!selector) return;

        const tools = document.createElement('div');
        tools.className = 'ografi-layout-editor-tools';
        tools.dataset.ografiLayoutIgnore = '1';

        const drag = document.createElement('button');
        drag.type = 'button';
        drag.draggable = true;
        drag.dataset.ografiLayoutDrag = '1';
        drag.setAttribute('aria-label', 'Bolumu surukle');
        drag.title = 'Bolumu surukle';
        drag.textContent = '⠿';

        const hide = document.createElement('button');
        hide.type = 'button';
        hide.dataset.ografiLayoutHide = '1';
        hide.setAttribute('aria-label', node.dataset.ografiLayoutHidden === '1' ? 'Bolumu yeniden goster' : 'Bolumu gizle');
        hide.title = hide.getAttribute('aria-label');
        hide.textContent = node.dataset.ografiLayoutHidden === '1' ? 'Goster' : 'Gizle';

        const label = document.createElement('span');
        const tag = node.tagName.toLowerCase();
        const classes = Array.from(node.classList || []).filter((name) => simpleToken.test(name)).slice(0, 1);
        label.textContent = tag + (classes.length ? '.' + classes[0] : '');
        label.title = selector;

        tools.append(drag, hide, label);
        node.appendChild(tools);
    };

    const dragNodeFor = (target) => {
        if (!(target instanceof Element)) return null;
        const handle = target.closest('[data-ografi-layout-drag]');
        return handle ? handle.closest('[data-ografi-layout-node]') : null;
    };

    const nodeForEvent = (target) => {
        if (!(target instanceof Element)) return null;
        if (target.closest('.ografi-layout-editor-tools')) {
            const tools = target.closest('.ografi-layout-editor-tools');
            return tools?.parentElement?.closest('[data-ografi-layout-node]') || tools?.parentElement || null;
        }
        return target.closest('[data-ografi-layout-node]');
    };

    applyStoredLayout();

    if (EDITOR) {
        document.documentElement.dataset.ografiLayoutEditing = '1';
        originalGroups.forEach((group) => group.nodes.forEach(createEditorTools));

        let draggedNode = null;
        let draggedGroup = null;

        document.addEventListener('click', (event) => {
            const target = event.target instanceof Element ? event.target : null;
            if (!target) return;
            if (target.closest('.ografi-layout-editor-tools')) {
                const hideButton = target.closest('[data-ografi-layout-hide]');
                if (hideButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    const node = hideButton.closest('[data-ografi-layout-node]');
                    updateHiddenStyles(node, node?.dataset.ografiLayoutHidden !== '1');
                    postState(true);
                }
                return;
            }

            const interactive = target.closest('a, button, input, select, textarea, form, [contenteditable="true"]');
            if (interactive) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);

        document.addEventListener('dragstart', (event) => {
            const node = dragNodeFor(event.target);
            if (!node) return;

            const group = parentToGroup.get(node.parentElement);
            if (!group || !group.nodes.includes(node)) {
                event.preventDefault();
                return;
            }

            draggedNode = node;
            draggedGroup = group;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', node.dataset.ografiLayoutNodeId || '');
            node.style.opacity = '.55';
        }, true);

        document.addEventListener('dragover', (event) => {
            if (!draggedNode || !draggedGroup) return;
            const target = nodeForEvent(event.target);
            if (!target || target === draggedNode || target.parentElement !== draggedGroup.parent) return;

            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        }, true);

        document.addEventListener('drop', (event) => {
            if (!draggedNode || !draggedGroup) return;
            const target = nodeForEvent(event.target);
            if (!target || target === draggedNode || target.parentElement !== draggedGroup.parent) return;

            event.preventDefault();
            const rect = target.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;
            target.parentElement.insertBefore(draggedNode, before ? target : target.nextSibling);
            draggedGroup.nodes.forEach((node) => { if (node) node.style.opacity = ''; });

            // Retain the pre-edit selector identities generated from the original Blade DOM.
            postState(true);
        }, true);

        document.addEventListener('dragend', () => {
            if (draggedNode) draggedNode.style.opacity = '';
            draggedNode = null;
            draggedGroup = null;
        }, true);

        document.querySelectorAll('[data-ografi-layout-node]').forEach((node) => {
            node.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowUp') {
                    event.preventDefault();
                    const group = parentToGroup.get(node.parentElement);
                    if (!group) return;
                    const siblings = group.nodes.filter((item) => item.parentElement === group.parent);
                    const index = siblings.indexOf(node);
                    if (index > 0) {
                        group.parent.insertBefore(node, siblings[index - 1]);
                        postState(true);
                    }
                }

                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    const group = parentToGroup.get(node.parentElement);
                    if (!group) return;
                    const siblings = group.nodes.filter((item) => item.parentElement === group);
                    const index = siblings.indexOf(node);
                    if (index >= 0 && index < siblings.length - 1) {
                        group.parent.insertBefore(node, siblings[index + 1].nextSibling);
                        postState(true);
                    }
                }
            });
        });

        postState(false);
    }

    // Saved layout is also applied for normal visitors; editor chrome is never injected there.
})();
