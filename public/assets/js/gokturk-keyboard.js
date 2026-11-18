(() => {
    'use strict';

    const alphabet = Array.from({ length: 0x2f }, (_, index) => String.fromCodePoint(0x10c00 + index));
    const root = document.createElement('div');
    root.className = 'gokturk-keyboard';
    root.innerHTML = '<button class="gokturk-keyboard__toggle" type="button" aria-expanded="false" aria-controls="gokturkKeyboardPanel"><span aria-hidden="true">𐰀</span><span>Göktürkçe</span></button><section class="gokturk-keyboard__panel" id="gokturkKeyboardPanel" aria-label="Sanal klavye" hidden><header class="gokturk-keyboard__header"><div><strong data-keyboard-title>Göktürkçe klavye</strong><small data-keyboard-subtitle>Orhun alfabesi · Unicode</small></div><button class="gokturk-keyboard__close" type="button" aria-label="Klavye panelini kapat">×</button></header><textarea class="gokturk-keyboard__input" data-keyboard-input rows="3" placeholder="Metnini buraya yaz..." aria-label="Göktürkçe veya Türkçe metin"></textarea><div class="gokturk-keyboard__keys" aria-label="Klavye harfleri"></div><footer class="gokturk-keyboard__actions"><button type="button" data-key-action="latin" aria-label="Türkçe klavyeye geç">Türkçe</button><button type="button" data-key-action="space">Boşluk</button><button type="button" data-key-action="backspace">Sil</button><button type="button" data-key-action="enter">Satır sonu</button></footer><p class="gokturk-keyboard__hint">Alfabe değiştirdiğinde kutudaki metin de otomatik çevrilir.</p></section>';
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

    // Basitleştirilmiş, pratik Latin <-> Orhun harf eşlemesi.
    // Tarihî metinlerde ses uyumu ve harf varyantları ayrıca değerlendirilmelidir.
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
        const lower = character.toLocaleLowerCase('tr-TR');
        const converted = latinToOldTurkic[lower];
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
            button.addEventListener('click', () => insert(convertKey(character)));
            keys.appendChild(button);
        });

        const turkish = mode === 'turkish';
        switchButton.textContent = turkish ? 'Göktürkçe' : 'Türkçe';
        switchButton.setAttribute('aria-label', turkish ? 'Göktürkçe klavyeye geç' : 'Türkçe klavyeye geç');
        title.textContent = turkish ? 'Türkçe klavye' : 'Göktürkçe klavye';
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
        input.setRangeText('', start === end ? start - 1 : start, end, 'end');
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: 'deleteContentBackward'
        }));
    }

    function switchAlphabet() {
        const nextMode = mode === 'gokturk' ? 'turkish' : 'gokturk';
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? start;

        // Göktürk tuşları Latin çıktı üretir; Türkçe tuşları Göktürk çıktı üretir.
        // Geçişte mevcut metin de yeni görünür alfabeye dönüştürülür.
        const direction = mode === 'gokturk' ? 'toOldTurkic' : 'toLatin';
        const beforeCaret = input.value.slice(0, start);
        const converted = convertText(input.value, direction);
        const convertedCaret = convertText(beforeCaret, direction).length;

        input.value = converted;
        input.setSelectionRange(convertedCaret, convertedCaret + Math.max(0, end - start));
        mode = nextMode;
        renderKeys();
        input.dispatchEvent(new InputEvent('input', {
            bubbles: true,
            inputType: 'insertReplacementText',
            data: null
        }));
        input.focus({ preventScroll: true });
    }

    root.querySelectorAll('[data-key-action]').forEach(button => {
        button.addEventListener('pointerdown', event => event.preventDefault());
        button.addEventListener('click', () => {
            const action = button.dataset.keyAction;
            if (action === 'latin') {
                switchAlphabet();
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