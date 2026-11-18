(() => {
    'use strict';

    // Orhun alfabesinin klavye tuşları ve metin ekleme davranışı korunur.
    const alphabet = Array.from({ length: 0x2f }, (_, index) => String.fromCodePoint(0x10c00 + index));
    const root = document.createElement('div');
    root.className = 'gokturk-keyboard';
    root.innerHTML = '<button class="gokturk-keyboard__toggle" type="button" aria-expanded="false" aria-controls="gokturkKeyboardPanel"><span aria-hidden="true">𐰀</span><span>Göktürkçe</span></button><section class="gokturk-keyboard__panel" id="gokturkKeyboardPanel" aria-label="Sanal klavye" hidden><header class="gokturk-keyboard__header"><div><strong data-keyboard-title>Göktürkçe klavye</strong><small data-keyboard-subtitle>Orhun alfabesi · Unicode</small></div><button class="gokturk-keyboard__close" type="button" aria-label="Klavye panelini kapat">×</button></header><div class="gokturk-keyboard__keys" aria-label="Klavye harfleri"></div><footer class="gokturk-keyboard__actions"><button type="button" data-key-action="latin" aria-label="Türkçe klavyeye geç">Türkçe</button><button type="button" data-key-action="space">Boşluk</button><button type="button" data-key-action="backspace">Sil</button><button type="button" data-key-action="enter">Satır sonu</button></footer><p class="gokturk-keyboard__hint">Önce yazı alanına tıkla, ardından harfleri seç.</p></section>';
    document.body.appendChild(root);

    const toggle = root.querySelector('.gokturk-keyboard__toggle');
    const panel = root.querySelector('.gokturk-keyboard__panel');
    const keys = root.querySelector('.gokturk-keyboard__keys');
    const switchButton = root.querySelector('[data-key-action="latin"]');
    const title = root.querySelector('[data-keyboard-title]');
    const subtitle = root.querySelector('[data-keyboard-subtitle]');
    let lastTarget = null;
    let mode = 'gokturk';

    const layouts = {
        gokturk: alphabet,
        turkish: Array.from('QWERTYUIOPĞÜASDFGHJKLŞİZXCVBNMÖÇ')
    };

    function renderKeys() {
        keys.replaceChildren();
        layouts[mode].forEach((character, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'gokturk-keyboard__key';
            button.textContent = character;
            button.title = mode === 'gokturk'
                ? 'Unicode U+' + (0x10c00 + index).toString(16).toUpperCase()
                : 'Türkçe ' + character;
            button.setAttribute('aria-label', (mode === 'gokturk' ? 'Göktürk harfi ' : 'Türkçe harf ') + character);
            button.addEventListener('pointerdown', event => event.preventDefault());
            button.addEventListener('click', () => insert(character));
            keys.appendChild(button);
        });

        const turkish = mode === 'turkish';
        switchButton.textContent = turkish ? 'Göktürkçe' : 'Türkçe';
        switchButton.setAttribute('aria-label', turkish ? 'Göktürkçe klavyeye geç' : 'Türkçe klavyeye geç');
        title.textContent = turkish ? 'Türkçe klavye' : 'Göktürkçe klavye';
        subtitle.textContent = turkish ? 'Türkçe Q · Latin alfabesi' : 'Orhun alfabesi · Unicode';
        toggle.querySelector('span:last-child').textContent = turkish ? 'Türkçe' : 'Göktürkçe';
        toggle.querySelector('span:first-child').textContent = turkish ? 'A' : '𐰀';
    }

    function isEditable(element) {
        if (!(element instanceof HTMLElement)) return false;
        if (element.isContentEditable) return true;
        if (element instanceof HTMLTextAreaElement) return !element.disabled && !element.readOnly;
        if (element instanceof HTMLInputElement) {
            return !element.disabled && !element.readOnly &&
                !['button','checkbox','color','file','hidden','image','radio','range','reset','submit'].includes(element.type);
        }
        return false;
    }

    document.addEventListener('focusin', event => {
        if (isEditable(event.target)) lastTarget = event.target;
    });

    function insert(value) {
        const target = lastTarget;
        if (!isEditable(target) || !target.isConnected) return;
        target.focus({ preventScroll: true });

        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
            const start = target.selectionStart ?? target.value.length;
            const end = target.selectionEnd ?? start;
            if (value === '\n' && target instanceof HTMLInputElement) return;
            target.setRangeText(value, start, end, 'end');
            target.dispatchEvent(new InputEvent('input', { bubbles: true, inputType: 'insertText', data: value }));
            return;
        }

        const selection = window.getSelection();
        if (!selection) return;
        let range = selection.rangeCount ? selection.getRangeAt(0) : null;
        if (!range || !target.contains(range.commonAncestorContainer)) {
            range = document.createRange();
            range.selectNodeContents(target);
            range.collapse(false);
        }
        range.deleteContents();
        const node = document.createTextNode(value);
        range.insertNode(node);
        range.setStartAfter(node);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
        target.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: value === '\n' ? 'insertParagraph' : 'insertText',
            data: value
        }));
    }

    function backspace() {
        const target = lastTarget;
        if (!isEditable(target)) return;
        target.focus({ preventScroll: true });
        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
            const start = target.selectionStart ?? target.value.length;
            const end = target.selectionEnd ?? start;
            target.setRangeText('', start === end ? Math.max(0, start - 1) : start, end, 'end');
            target.dispatchEvent(new InputEvent('input', { bubbles: true, inputType: 'deleteContentBackward' }));
        } else {
            document.execCommand('delete');
            target.dispatchEvent(new InputEvent('input', { bubbles: true, inputType: 'deleteContentBackward' }));
        }
    }

    root.querySelectorAll('[data-key-action]').forEach(button => {
        button.addEventListener('pointerdown', event => event.preventDefault());
        button.addEventListener('click', () => {
            const action = button.dataset.keyAction;
            if (action === 'latin') {
                mode = mode === 'gokturk' ? 'turkish' : 'gokturk';
                renderKeys();
            } else if (action === 'space') {
                insert(' ');
            } else if (action === 'enter') {
                insert('\n');
            } else if (action === 'backspace') {
                backspace();
            }
        });
    });

    toggle.addEventListener('click', () => {
        panel.hidden = !panel.hidden;
        toggle.setAttribute('aria-expanded', String(!panel.hidden));
    });
    root.querySelector('.gokturk-keyboard__close').addEventListener('click', () => {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    });
    document.addEventListener('pointerdown', event => {
        if (!root.contains(event.target)) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    renderKeys();
})();