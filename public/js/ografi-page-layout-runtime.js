(() => {
    'use strict';

    const readConfig = () => {
        const node = document.getElementById('ografi-page-layout-config');
        if (!node) return null;

        try {
            return JSON.parse(node.textContent || '{}');
        } catch (error) {
            console.error('[Ografi layout] Invalid layout config.', error);
            return null;
        }
    };

    const config = readConfig();
    if (!config || !config.pageKey || !config.containerSelector || !Array.isArray(config.blocks)) return;

    const isEditorPreview = config.editorPreview === true;
    const style = document.createElement('style');
    style.id = 'ografi-page-layout-runtime-style';
    style.textContent = `
        [data-ografi-layout-hidden="1"] { display: none !important; }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-editable="1"] {
            position: relative !important;
            outline: 2px dashed #3b82f6 !important;
            outline-offset: -3px !important;
            cursor: grab !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-editable="1"]::before {
            content: attr(data-ografi-layout-label);
            position: absolute !important;
            top: 4px !important;
            right: 4px !important;
            z-index: 2147483000 !important;
            max-width: calc(100% - 8px) !important;
            overflow: hidden !important;
            padding: 4px 7px !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 6px !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            font: 600 11px/1.2 ui-sans-serif, system-ui, sans-serif !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            pointer-events: none !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-editable="1"][data-ografi-layout-hidden="1"] {
            display: block !important;
            min-height: 32px !important;
            max-height: 56px !important;
            overflow: hidden !important;
            opacity: .48 !important;
            border: 2px dashed #9ca3af !important;
            background: repeating-linear-gradient(135deg, #fff, #fff 8px, #f3f4f6 8px, #f3f4f6 16px) !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-editable="1"][data-ografi-layout-hidden="1"] > * {
            visibility: hidden !important;
        }
        html[data-ografi-layout-editing="1"] [data-ografi-layout-editable="1"]:hover {
            outline-color: #1d4ed8 !important;
        }
        html[data-ografi-layout-editing="1"] body::before {
            content: 'DUZENLEME ONIZLEMESI — taslak degisiklikler yayina alinmaz';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 2147483647;
            padding: 5px 10px;
            background: #1d4ed8;
            color: #fff;
            text-align: center;
            font: 600 11px/1.3 ui-sans-serif, system-ui, sans-serif;
            pointer-events: none;
        }
        html[data-ografi-layout-editing="1"] body { padding-top: 28px !important; }
    `;
    document.head.appendChild(style);

    let currentOrder = Array.isArray(config.layout?.order) ? [...config.layout.order] : config.blocks.map((block) => block.id);
    let hiddenBlocks = Array.isArray(config.layout?.hidden) ? [...config.layout.hidden] : [];
    let blockMap = new Map(config.blocks.map((block) => [block.id, block]));

    const collectNodes = () => {
        const container = document.querySelector(config.containerSelector);
        if (!container) return { container: null, nodes: new Map(), slots: [] };

        const nodes = new Map();
        config.blocks.forEach((block) => {
            const node = document.querySelector(block.selector);
            if (!node) return;
            nodes.set(block.id, { node, block });
        });

        const directNodes = Array.from(nodes.values())
            .filter(({ node }) => node.parentElement === container)
            .sort((a, b) => {
                const relation = a.node.compareDocumentPosition(b.node);
                return relation & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : relation & Node.DOCUMENT_POSITION_PRECEDING ? 1 : 0;
            });

        return { container, nodes, slots: directNodes };
    };

    const markBlocks = (nodes) => {
        config.blocks.forEach((block) => {
            const entry = nodes.get(block.id);
            if (!entry) return;
            const { node } = entry;
            node.dataset.ografiLayoutSection = block.id;
            node.dataset.ografiLayoutEditable = isEditorPreview ? '1' : '0';
            node.dataset.ografiLayoutLabel = block.label || block.id;
            if (hiddenBlocks.includes(block.id)) {
                node.dataset.ografiLayoutHidden = '1';
            } else {
                delete node.dataset.ografiLayoutHidden;
            }
            node.draggable = isEditorPreview;
            node.setAttribute('aria-label', node.getAttribute('aria-label') || block.label || block.id);
        });
    };

    const applyOrder = () => {
        const { container, nodes, slots } = collectNodes();
        if (!container || slots.length < 2) {
            markBlocks(nodes);
            return;
        }

        markBlocks(nodes);

        // Only reorder explicitly tagged top-level siblings. DOM nodes are moved, not cloned,
        // so listeners and Livewire-owned descendants remain attached.
        const placeholders = slots.map(({ node }, index) => {
            const placeholder = document.createComment('ografi-layout-slot-' + index);
            node.replaceWith(placeholder);
            return placeholder;
        });

        const orderedNodes = currentOrder
            .map((id) => nodes.get(id)?.node)
            .filter((node) => node && node.parentElement === null || (node && !node.isConnected));

        // Blocks hidden by state are still moved into their intended slot; CSS controls visibility.
        const safeNodes = currentOrder.map((id) => nodes.get(id)?.node).filter(Boolean);
        const exactNodes = safeNodes.length === placeholders.length ? safeNodes : slots.map((slot) => slot.node);
        placeholders.forEach((placeholder, index) => {
            placeholder.replaceWith(exactNodes[index]);
        });

        markBlocks(nodes);
    };

    // Draft preview is admin-only (the config is server-gated as well as origin-checked).
    if (isEditorPreview) {
        document.documentElement.dataset.ografiLayoutEditing = '1';
        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-ografi-layout-editable="1"]')) return;
            const interactive = event.target.closest('a, button, input, select, textarea, form');
            if (!interactive) return;
            event.preventDefault();
            event.stopPropagation();
        }, true);

        document.addEventListener('dragstart', (event) => {
            const block = event.target.closest('[data-ografi-layout-editable="1"]');
            if (!block) return;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', block.dataset.ografiLayoutSection || '');
            block.style.opacity = '.55';
        });

        document.addEventListener('dragover', (event) => {
            if (!isEditorPreview) return;
            const draggedId = event.dataTransfer?.getData('text/plain');
            if (!draggedId) return;
            const target = event.target.closest('[data-ografi-layout-editable="1"]');
            if (!target || target.dataset.ografiLayoutSection === draggedId) return;
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        });

        document.addEventListener('drop', (event) => {
            if (!isEditorPreview) return;
            const draggedId = event.dataTransfer?.getData('text/plain');
            const target = event.target.closest('[data-ografi-layout-editable="1"]');
            if (!draggedId || !target) return;
            const targetId = target.dataset.ografiLayoutSection;
            if (!targetId || targetId === draggedId) return;

            event.preventDefault();
            const entry = collectNodes();
            const dragged = entry.nodes.get(draggedId)?.node;
            const targetNode = entry.nodes.get(targetId)?.node;
            if (!dragged || !targetNode || dragged.parentElement !== targetNode.parentElement) return;

            const rect = targetNode.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;
            targetNode.parentElement.insertBefore(dragged, before ? targetNode : targetNode.nextSibling);

            currentOrder = Array.from(entry.container.querySelectorAll('[data-ografi-layout-editable="1"]'))
                .map((node) => node.dataset.ografiLayoutSection)
                .filter((id) => blockMap.has(id));

            // Keep sections that are currently absent (e.g. empty-state alternatives) at the end.
            blockMap.forEach((block, id) => {
                if (!currentOrder.includes(id)) currentOrder.push(id);
            });

            window.parent.postMessage({
                type: 'ografi-layout-order-changed',
                pageKey: config.pageKey,
                order: currentOrder,
                hidden: hiddenBlocks,
            }, window.location.origin);
        });

        document.addEventListener('dragend', (event) => {
            const block = event.target.closest('[data-ografi-layout-editable="1"]');
            if (block) block.style.opacity = '';
        });
    }

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== window.parent || !event.data) return;
        if (event.data.type !== 'ografi-layout-apply-draft' || event.data.pageKey !== config.pageKey) return;

        if (Array.isArray(event.data.order)) {
            currentOrder = event.data.order.filter((id) => blockMap.has(id));
            blockMap.forEach((block, id) => {
                if (!currentOrder.includes(id)) currentOrder.push(id);
            });
        }
        if (Array.isArray(event.data.hidden)) {
            hiddenBlocks = event.data.hidden.filter((id) => blockMap.has(id));
        }

        applyOrder();
    });

    applyOrder();
    document.dispatchEvent(new CustomEvent('ografi:page-layout-ready', { detail: { pageKey: config.pageKey } }));
})();
