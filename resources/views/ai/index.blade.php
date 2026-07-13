@extends('layouts.app')

@section('content')
<style>
    .ografi-ai-page {
        width: 100%;
        max-width: 860px;
        margin: 0 auto;
        padding: 24px 14px;
        font-family: Roboto, Arial, sans-serif;
    }

    .ografi-ai-shell {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        overflow: hidden;
    }

    .ografi-ai-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        background: rgba(255, 255, 255, 0.92);
    }

    .ografi-ai-title {
        margin: 0;
        font-size: 18px;
        font-weight: 400;
        color: #111827;
    }

    .ografi-ai-desc {
        margin: 4px 0 0;
        font-size: 13px;
        color: #6b7280;
    }

    .ografi-ai-new {
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        color: #111827;
        border-radius: 999px;
        padding: 8px 13px;
        font-size: 13px;
        cursor: pointer;
        white-space: nowrap;
    }

    .ografi-ai-new:hover {
        background: #f3f4f6;
    }

    .ografi-ai-chat {
        height: calc(100vh - 290px);
        min-height: 420px;
        overflow-y: auto;
        padding: 18px 16px;
        background: #fafafa;
    }

    .ografi-ai-empty {
        height: 100%;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
    }

    .ografi-ai-message-row {
        display: flex;
        width: 100%;
        margin-bottom: 14px;
    }

    .ografi-ai-message-row.user {
        justify-content: flex-end;
    }

    .ografi-ai-message-row.assistant {
        justify-content: flex-start;
    }

    .ografi-ai-bubble {
        max-width: min(78%, 620px);
        padding: 12px 14px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.65;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .ografi-ai-message-row.user .ografi-ai-bubble {
        background: #111827;
        color: #ffffff;
        border-bottom-right-radius: 6px;
    }

    .ografi-ai-message-row.assistant .ografi-ai-bubble {
        background: #ffffff;
        color: #111827;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 6px;
    }

    .ografi-ai-typing {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .ografi-ai-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #9ca3af;
        animation: ografiAiPulse 1s infinite ease-in-out;
    }

    .ografi-ai-dot:nth-child(2) {
        animation-delay: .15s;
    }

    .ografi-ai-dot:nth-child(3) {
        animation-delay: .3s;
    }

    @keyframes ografiAiPulse {
        0%, 80%, 100% {
            opacity: .35;
            transform: translateY(0);
        }

        40% {
            opacity: 1;
            transform: translateY(-3px);
        }
    }

    .ografi-ai-form {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        padding: 14px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .ografi-ai-textarea {
        flex: 1;
        min-height: 46px;
        max-height: 160px;
        resize: none;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 13px 14px;
        font-size: 14px;
        line-height: 1.5;
        outline: none;
        color: #111827;
        background: #ffffff;
    }

    .ografi-ai-textarea:focus {
        border-color: #111827;
    }

    .ografi-ai-send {
        border: none;
        background: #111827;
        color: #ffffff;
        border-radius: 16px;
        padding: 13px 18px;
        font-size: 14px;
        cursor: pointer;
        min-width: 88px;
    }

    .ografi-ai-send:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    @media (max-width: 640px) {
        .ografi-ai-page {
            padding: 10px;
        }

        .ografi-ai-shell {
            border-radius: 18px;
        }

        .ografi-ai-header {
            padding: 14px;
        }

        .ografi-ai-title {
            font-size: 17px;
        }

        .ografi-ai-chat {
            height: calc(100vh - 250px);
            min-height: 360px;
            padding: 14px 10px;
        }

        .ografi-ai-bubble {
            max-width: 88%;
            font-size: 14px;
        }

        .ografi-ai-form {
            padding: 10px;
            gap: 8px;
        }

        .ografi-ai-send {
            padding: 13px 14px;
            min-width: auto;
        }
    }
</style>

<div class="ografi-ai-page">
    <div class="ografi-ai-shell">
        <div class="ografi-ai-header">
            <div>
                <h1 class="ografi-ai-title">Ografi AI</h1>
                <p class="ografi-ai-desc">Cevaba cevap verebilen sohbet alanı.</p>
            </div>

            <button type="button" id="ai-new-chat" class="ografi-ai-new">
                Yeni sohbet
            </button>
        </div>

        <div id="ai-chat" class="ografi-ai-chat">
            <div id="ai-empty" class="ografi-ai-empty">
                <div>
                    Bir mesaj yaz.<br>
                    Sonra cevabın üstüne tekrar yazabilirsin.
                </div>
            </div>
        </div>

        <form id="ai-form" class="ografi-ai-form">
            <textarea
                id="ai-message"
                class="ografi-ai-textarea"
                rows="1"
                placeholder="Mesaj yaz..."
                autocomplete="off"
            ></textarea>

            <button type="submit" id="ai-send" class="ografi-ai-send">
                Gönder
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('ai-chat');
    const emptyBox = document.getElementById('ai-empty');
    const form = document.getElementById('ai-form');
    const textarea = document.getElementById('ai-message');
    const sendButton = document.getElementById('ai-send');
    const newChatButton = document.getElementById('ai-new-chat');

    let messages = [];
    let isSending = false;

    function escapeHtml(value) {
        return value
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function hideEmpty() {
        if (emptyBox) {
            emptyBox.style.display = messages.length ? 'none' : 'flex';
        }
    }

    function appendMessage(role, content) {
        hideEmpty();

        const row = document.createElement('div');
        row.className = 'ografi-ai-message-row ' + role;

        const bubble = document.createElement('div');
        bubble.className = 'ografi-ai-bubble';
        bubble.innerHTML = escapeHtml(content);

        row.appendChild(bubble);
        chatBox.appendChild(row);

        scrollToBottom();
    }

    function appendTyping() {
        const row = document.createElement('div');
        row.className = 'ografi-ai-message-row assistant';
        row.id = 'ai-typing';

        const bubble = document.createElement('div');
        bubble.className = 'ografi-ai-bubble';
        bubble.innerHTML = `
            <span class="ografi-ai-typing">
                <span class="ografi-ai-dot"></span>
                <span class="ografi-ai-dot"></span>
                <span class="ografi-ai-dot"></span>
            </span>
        `;

        row.appendChild(bubble);
        chatBox.appendChild(row);

        scrollToBottom();
    }

    function removeTyping() {
        const typing = document.getElementById('ai-typing');

        if (typing) {
            typing.remove();
        }
    }

    function resetTextareaHeight() {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
    }

    async function sendMessage() {
        if (isSending) {
            return;
        }

        const content = textarea.value.trim();

        if (!content) {
            return;
        }

        isSending = true;
        sendButton.disabled = true;
        sendButton.innerText = 'Bekle';

        textarea.value = '';
        resetTextareaHeight();

        messages.push({
            role: 'user',
            content: content
        });

        appendMessage('user', content);
        appendTyping();

        try {
            const response = await fetch('{{ route('ai.ask') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    messages: messages
                })
            });

            const data = await response.json();

            removeTyping();

            if (!response.ok || !data.ok) {
                const errorMessage = data.message || 'Bir hata oluştu.';

                messages.push({
                    role: 'assistant',
                    content: errorMessage
                });

                appendMessage('assistant', errorMessage);
                return;
            }

            const answer = data.answer || 'Boş cevap geldi.';

            messages.push({
                role: 'assistant',
                content: answer
            });

            appendMessage('assistant', answer);

            if (messages.length > 24) {
                messages = messages.slice(-24);
            }
        } catch (error) {
            removeTyping();

            const errorMessage = 'Bağlantı hatası oluştu. Ollama veya Laravel loglarını kontrol et.';

            messages.push({
                role: 'assistant',
                content: errorMessage
            });

            appendMessage('assistant', errorMessage);
        } finally {
            isSending = false;
            sendButton.disabled = false;
            sendButton.innerText = 'Gönder';
            textarea.focus();
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        sendMessage();
    });

    textarea.addEventListener('input', resetTextareaHeight);

    textarea.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    newChatButton.addEventListener('click', function () {
        messages = [];
        chatBox.innerHTML = `
            <div id="ai-empty" class="ografi-ai-empty">
                <div>
                    Bir mesaj yaz.<br>
                    Sonra cevabın üstüne tekrar yazabilirsin.
                </div>
            </div>
        `;
        textarea.value = '';
        resetTextareaHeight();
        textarea.focus();
    });

    resetTextareaHeight();
});
</script>
@endsection