(() => {
    'use strict';

    const alphabet = Array.from({ length: 0x2f }, (_, index) => String.fromCodePoint(0x10c00 + index));
    const root = document.createElement('div');
    root.className = 'gokturk-keyboard';
    root.innerHTML = '<button class="gokturk-keyboard__toggle" type="button" aria-expanded="false" aria-controls="gokturkKeyboardPanel"><span aria-hidden="true">𐰀</span><span>Göktürkçe</span></button><section class="gokturk-keyboard__panel" id="gokturkKeyboardPanel" aria-label="Sanal klavye" hidden><header class="gokturk-keyboard__header"><div class="gokturk-keyboard__heading"><div><strong data-keyboard-title>Göktürkçe klavye</strong><small data-keyboard-subtitle>Orhun alfabesi · Unicode</small></div><div class="gokturk-keyboard__translate-wrap"><button class="gokturk-keyboard__translate" type="button" data-translate-menu-toggle aria-label="Çeviri seçeneklerini aç" title="Çeviri seçeneklerini aç" aria-expanded="false" aria-controls="gokturkTranslateMenu"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 5h9M8.5 3v2c0 5-2.5 8-5.5 10M5 9c1.2 2 3 3.7 5.5 5M14 19l3.5-9 3.5 9M15.2 16h4.6M14 5h7M17.5 5c0 5 2 8 4.5 10"/></svg></button><div class="gokturk-keyboard__translate-menu" id="gokturkTranslateMenu" hidden><button type="button" data-key-action="latin" aria-label="Göktürkçe klavyeye geç">Göktürkçe</button></div></div></div><button class="gokturk-keyboard__close" type="button" aria-label="Klavye panelini kapat">×</button></header><textarea class="gokturk-keyboard__input" data-keyboard-input rows="3" placeholder="Metnini buraya yaz..." aria-label="Göktürkçe veya Türkçe metin"></textarea><div class="gokturk-keyboard__keys" aria-label="Klavye harfleri"></div><footer class="gokturk-keyboard__actions"><button type="button" data-key-action="space">Boşluk</button><button type="button" data-key-action="enter">Satır sonu</button></footer><p class="gokturk-keyboard__hint">Alfabe değiştirdiğinde kutudaki metin de otomatik çevrilir.</p></section>';
    document.body.appendChild(root);

    const toggle = root.querySelector('.gokturk-keyboard__toggle');
    const panel = root.querySelector('.gokturk-keyboard__panel');
    const keys = root.querySelector('.gokturk-keyboard__keys');
    const switchButton = root.querySelector('[data-key-action="latin"]');
    const title = root.querySelector('[data-keyboard-title]');
    const subtitle = root.querySelector('[data-keyboard-subtitle]');
    const input = root.querySelector('[data-keyboard-input]');
    let mode = 'gokturk';
    let shiftOn = false;

    const layouts = {
        gokturk: alphabet,
        turkish: [
            ['q','w','e','r','t','y','u','ı','o','p','ğ','ü'],
            ['a','s','d','f','g','h','j','k','l','ş','i'],
            [{ action: 'shift' },'z','x','c','v','b','n','m','ö','ç',{ action: 'backspace' }]
        ]
    };

    const latinToOldTurkic = {
        a: '𐰀', b: '𐰉', c: '𐰲', ç: '𐰲', d: '𐰑', e: '𐰅',
        f: '𐰯', g: '𐰍', ğ: '𐰏', h: '𐰴', ı: '𐰃', i: '𐰃',
        j: '𐰔', k: '𐰚', l: '𐰞', m: '𐰢', n: '𐰣', o: '𐰆',
        ö: '𐰇', p: '𐰯', r: '𐰺', s: '𐰽', ş: '𐱁', t: '𐱃',
        u: '𐰆', ü: '𐰇', v: '𐰉', y: '𐰖', z: '𐰔'
    };

    const oldTurkicToLatin = {
        '𐰀': 'a', '𐰁': 'a', '𐰂': 'e', '𐰅': 'e', '𐰃': 'ı',
        '𐰄': 'ı', '𐰆': 'o', '𐰇': 'ö', '𐰈': 'ü',
        '𐰉': 'b', '𐰊': 'b', '𐰋': 'b', '𐰌': 'b',
        '𐰍': 'g', '𐰎': 'g', '𐰏': 'ğ', '𐰐': 'g',
        '𐰑': 'd', '𐰒': 'd', '𐰓': 'd', '𐰔': 'z',
        '𐰕': 'z', '𐰖': 'y', '𐰗': 'y', '𐰘': 'y', '𐰙': 'y',
        '𐰚': 'k', '𐰛': 'k', '𐰜': 'k', '𐰝': 'k',
        '𐰞': 'l', '𐰟': 'l', '𐰠': 'l', '𐰡': 'l',
        '𐰢': 'm', '𐰣': 'n', '𐰤': 'n', '𐰥': 'n',
        '𐰦': 'n', '𐰧': 'n', '𐰨': 'ç', '𐰩': 'ç',
        '𐰪': 'ny', '𐰫': 'ny', '𐰬': 'ng', '𐰭': 'ng',
        '𐰮': 'ng', '𐰯': 'p', '𐰰': 'p', '𐰲': 'ç',
        '𐰳': 'ç', '𐰴': 'h', '𐰵': 'q', '𐰶': 'q',
        '𐰷': 'q', '𐰸': 'q', '𐰹': 's', '𐰽': 's',
        '𐰾': 's', '𐰿': 'ş', '𐱀': 'ş', '𐱁': 'ş',
        '𐱂': 'ş', '𐱃': 't', '𐱄': 't', '𐱅': 't',
        '𐱆': 't', '𐱇': 't', '𐱈': 'd', '𐰺': 'r',
        '𐰻': 'r', '𐰼': 'r'
    };

    function preserveCase(source, converted) {
        return source !== source.toLocaleLowerCase('tr-TR')
            ? converted.toLocaleUpperCase('tr-TR')
            : converted;
    }

    function latinCharacterToOldTurkic(character) {
        const converted = latinToOldTurkic[character.toLocaleLowerCase('tr-TR')];
        return converted ? preserveCase(character, converted) : character;
    }

    function oldTurkicCharacterToLatin(character) {
        const converted = oldTurkicToLatin[character];
        return converted ? preserveCase(character, converted) : character;
    }

    function convertText(value, direction) {
        const converter = direction === 'toOldTurkic'
            ? latinCharacterToOldTurkic
            : oldTurkicCharacterToLatin;
        return Array.from(value, character => converter(character)).join('');
    }

    function convertKey(character) {
        return mode === 'turkish'
            ? latinCharacterToOldTurkic(character)
            : oldTurkicCharacterToLatin(character);
    }

    function makeKey(label, className, onClick, ariaLabel) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'gokturk-keyboard__key' + (className ? ' ' + className : '');
        button.textContent = label;
        button.setAttribute('aria-label', ariaLabel || label);
        button.addEventListener('pointerdown', event => event.preventDefault());
        button.addEventListener('click', onClick);
        return button;
    }

    function renderTurkishKeys() {
        layouts.turkish.forEach((row, rowIndex) => {
            const rowElement = document.createElement('div');
            rowElement.className = 'gokturk-keyboard__row gokturk-keyboard__row--' + (rowIndex + 1);

            row.forEach(item => {
                if (typeof item === 'object' && item.action === 'shift') {
                    const shift = makeKey('', 'gokturk-keyboard__special gokturk-keyboard__icon-key' + (shiftOn ? ' is-active' : ''), () => {
                        shiftOn = !shiftOn;
                        renderKeys();
                    }, 'Büyük harf');
                    shift.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3 4 11h4v8h8v-8h4L12 3Z"/></svg>';
                    rowElement.appendChild(shift);
                    return;
                }

                if (typeof item === 'object' && item.action === 'backspace') {
                    const back = makeKey('', 'gokturk-keyboard__special gokturk-keyboard__icon-key', backspace, 'Geri sil');
                    back.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 4H20V20H9L2 12 9 4Z"/><path d="m12 9 5 6m0-6-5 6"/></svg>';
                    rowElement.appendChild(back);
                    return;
                }

                const shown = shiftOn ? item.toLocaleUpperCase('tr-TR') : item;
                const key = makeKey(shown, '', () => {
                    insert(convertKey(shown));
                    if (shiftOn) {
                        shiftOn = false;
                        renderKeys();
                    }
                }, shown);
                rowElement.appendChild(key);
            });

            keys.appendChild(rowElement);
        });
    }

    function renderKeys() {
        keys.replaceChildren();
        keys.classList.toggle('is-turkish', mode === 'turkish');
        keys.classList.toggle('is-gokturk', mode === 'gokturk');

        if (mode === 'turkish') {
            renderTurkishKeys();
        } else {
            alphabet.forEach((character, index) => {
                const button = makeKey(character, '', () => insert(oldTurkicCharacterToLatin(character)));
                button.title = 'Unicode U+' + (0x10c00 + index).toString(16).toUpperCase();
                keys.appendChild(button);
            });
            const back = makeKey('', 'gokturk-keyboard__icon-key gokturk-keyboard__gokturk-backspace', backspace, 'Geri sil');
            back.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 4H20V20H9L2 12 9 4Z"/><path d="m12 9 5 6m0-6-5 6"/></svg>';
            keys.appendChild(back);
        }

        const turkish = mode === 'turkish';
        switchButton.textContent = turkish ? 'Göktürkçe' : 'Türkçe';
        switchButton.setAttribute('aria-label', turkish ? 'Göktürkçe klavyeye geç' : 'Türkçe klavyeye geç');
        title.textContent = turkish ? 'Göktürkçe çeviri' : 'Göktürkçe klavye';
        subtitle.textContent = turkish ? 'Türkçe Q · Göktürkçe çıktı' : 'Orhun alfabesi · Latin çıktı';
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
        let deleteStart = start;
        if (start === end) {
            const previousCharacter = Array.from(input.value.slice(0, start)).pop() || '';
            deleteStart = start - previousCharacter.length;
        }
        input.setRangeText('', deleteStart, end, 'end');
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: 'deleteContentBackward'
        }));
    }

    function translateToOldTurkic() {
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? start;
        const direction = 'toOldTurkic';
        const convertedCaret = convertText(input.value.slice(0, start), direction).length;
        const convertedSelectionEnd = convertText(input.value.slice(0, end), direction).length;

        input.value = convertText(input.value, direction);
        input.setSelectionRange(convertedCaret, convertedSelectionEnd);
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: 'insertReplacementText',
            data: null
        }));
        input.focus({ preventScroll: true });
    }

    function switchAlphabet() {
        const nextMode = mode === 'gokturk' ? 'turkish' : 'gokturk';
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? start;
        const direction = mode === 'gokturk' ? 'toOldTurkic' : 'toLatin';
        const convertedCaret = convertText(input.value.slice(0, start), direction).length;

        input.value = convertText(input.value, direction);
        input.setSelectionRange(convertedCaret, convertedCaret + Math.max(0, end - start));
        mode = nextMode;
        shiftOn = false;
        renderKeys();
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: 'insertReplacementText',
            data: null
        }));
        input.focus({ preventScroll: true });
    }

    const translateToggle = root.querySelector('[data-translate-menu-toggle]');
    const translateMenu = root.querySelector('.gokturk-keyboard__translate-menu');

    function closeTranslateMenu() {
        translateMenu.hidden = true;
        translateToggle.setAttribute('aria-expanded', 'false');
    }

    translateToggle.addEventListener('click', () => {
        const open = translateMenu.hidden;
        translateMenu.hidden = !open;
        translateToggle.setAttribute('aria-expanded', String(open));
    });

    translateMenu.querySelector('button').addEventListener('click', closeTranslateMenu);

    root.querySelectorAll('[data-key-action]').forEach(button => {
        button.addEventListener('pointerdown', event => event.preventDefault());
        button.addEventListener('click', () => {
            const action = button.dataset.keyAction;
            if (action === 'latin') switchAlphabet();
            else if (action === 'translate') translateToOldTurkic();
            else if (action === 'space') insert(' ');
            else if (action === 'enter') insert('\n');
            else if (action === 'backspace') backspace();
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
    document.addEventListener('pointerdown', event => {
        if (!event.target.closest('.gokturk-keyboard__translate-wrap')) closeTranslateMenu();
    });
        document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !translateMenu.hidden) {
            closeTranslateMenu();
            return;
        }
        if (event.key === 'Escape' && !panel.hidden) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    renderKeys();
})();