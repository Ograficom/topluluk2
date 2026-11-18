(() => {
    'use strict';

    const alphabet = Array.from({ length: 0x2f }, (_, index) => String.fromCodePoint(0x10c00 + index));
    const root = document.createElement('div');
    root.className = 'gokturk-keyboard';
    root.innerHTML = '<button class="gokturk-keyboard__toggle" type="button" aria-expanded="false" aria-controls="gokturkKeyboardPanel"><span aria-hidden="true">𐰀</span><span>Göktürkçe</span></button><section class="gokturk-keyboard__panel" id="gokturkKeyboardPanel" aria-label="Sanal klavye" hidden><header class="gokturk-keyboard__header"><div><strong data-keyboard-title>Göktürkçe klavye</strong><small data-keyboard-subtitle>Orhun alfabesi · Unicode</small></div><button class="gokturk-keyboard__close" type="button" aria-label="Klavye panelini kapat">×</button></header><textarea class="gokturk-keyboard__input" data-keyboard-input rows="3" placeholder="Metnini buraya yaz..." aria-label="Göktürkçe veya Türkçe metin"></textarea><div class="gokturk-keyboard__keys" aria-label="Klavye harfleri"></div><footer class="gokturk-keyboard__actions"><button type="button" data-key-action="latin" aria-label="Türkçe klavyeye geç">Türkçe</button><button type="button" data-key-action="space">Boşluk</button><button type="button" data-key-action="backspace">Sil</button><button type="button" data-key-action="enter">Satır sonu</button></footer><p class="gokturk-keyboard__hint">Yazdıkların yalnızca bu kutuda görünür.</p></section>';
    document.body.appendChild(root);

    const toggle = root.querySelector('.gokturk-keyboard__toggle');
    const panel = root.querySelector('.gokturk-keyboard__panel');
    const keys = root.querySelector('.gokturk-keyboard__keys');
    const switchButton = root.querySelector('[data-key-action="latin"]');
    const title = root.querySelector('[data-keyboard-title]');
    const subtitle = root.querySelector('[data-keyboard-subtitle]');
    const input = root.querySelector('[data-keyboard-input]');
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

    function insert(value) {
        input.focus({ preventScroll: true });
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? start;
        input.setRangeText(value, start, end, 'end');
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: value === '\n' ? 'insertLineBreak' : 'insertText',
            data: value
        }));
    }

    function backspace() {
        input.focus({ preventScroll: true });
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? start;
        if (start === end && start === 0) return;
        input.setRangeText('', start === end ? start - 1 : start, end, 'end');
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: 'deleteContentBackward'
        }));
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
        if (!panel.hidden) input.focus({ preventScroll: true });
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