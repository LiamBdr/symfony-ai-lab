import { marked } from 'marked';
import DOMPurify from 'dompurify';

let currentController = null;

export async function streamChat(message, outputElement, metaElement, signal) {
    const response = await fetch('/chat/stream', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message}),
        signal,
    });

    if (!response.ok) {
        outputElement.textContent = 'Erreur: ' + response.statusText;
        return false;
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';
    let rawText = '';

    const processLine = (line) => {
        if (!line.startsWith('data: ')) return null;

        let parsed;
        try {
            parsed = JSON.parse(line.slice(6));
        } catch {
            return null;
        }

        if (parsed.done === true) {
            renderMeta(metaElement, parsed);
            return 'done';
        }

        const {token} = parsed;
        if (token === undefined) return null;
        rawText += token;
        const fragment = DOMPurify.sanitize(marked.parse(rawText), {RETURN_DOM_FRAGMENT: true});
        outputElement.replaceChildren(fragment);
        scrollToBottom();
        return null;
    };

    while (true) {
        const {done, value} = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, {stream: true});
        const lines = buffer.split('\n');
        buffer = lines.pop();

        for (const line of lines) {
            if (processLine(line) === 'done') return true;
        }
    }

    if (buffer.length > 0) {
        processLine(buffer);
    }

    return true;
}

function renderMeta(el, {model, completionTokens, durationMs}) {
    if (!el) return;

    const parts = [];
    if (model) parts.push(model);
    if (typeof completionTokens === 'number') parts.push(`${completionTokens} tokens`);
    if (typeof durationMs === 'number') parts.push(`${(durationMs / 1000).toFixed(1)}s`);

    if (parts.length === 0) return;

    el.textContent = parts.join(' \u00B7 ');
    el.classList.remove('hidden');
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function createUserBubble(message) {
    const template = document.getElementById('tpl-user-bubble');
    const bubble = template.content.firstElementChild.cloneNode(true);
    bubble.querySelector('[data-message]').textContent = message;
    return bubble;
}

function createAiBubble() {
    const template = document.getElementById('tpl-ai-bubble');
    const bubble = template.content.firstElementChild.cloneNode(true);
    const outputElement = bubble.querySelector('[data-output]');
    const metaElement = bubble.querySelector('[data-meta]');
    return {bubble, outputElement, metaElement};
}

function setTextareaEnabled(enabled) {
    const textarea = document.getElementById('chat-textarea');
    if (textarea) textarea.disabled = !enabled;
}

function setSendButtonState(mode) {
    const sendBtn = document.getElementById('chat-send');
    if (!sendBtn) return;

    sendBtn.dataset.mode = mode;
    sendBtn.disabled = false;

    const iconSend = sendBtn.querySelector('[data-icon-send]');
    const iconStop = sendBtn.querySelector('[data-icon-stop]');
    const labelSend = sendBtn.querySelector('[data-send-label]');
    const labelStop = sendBtn.querySelector('[data-stop-label]');

    const streaming = mode === 'streaming';
    iconSend?.classList.toggle('hidden', streaming);
    iconStop?.classList.toggle('hidden', !streaming);
    labelSend?.classList.toggle('hidden', streaming);
    labelStop?.classList.toggle('hidden', !streaming);
}

function handleStop() {
    if (currentController) {
        currentController.abort();
    }
}

async function handleSubmit() {
    const sendBtn = document.getElementById('chat-send');
    if (sendBtn?.dataset.mode === 'streaming') {
        handleStop();
        return;
    }

    const textarea = document.getElementById('chat-textarea');
    const messages = document.getElementById('chat-messages');

    const message = textarea.value.trim();
    if (!message) return;

    textarea.value = '';
    textarea.style.height = 'auto';

    setTextareaEnabled(false);
    setSendButtonState('streaming');

    const userBubble = createUserBubble(message);
    messages.appendChild(userBubble);
    scrollToBottom();

    const {bubble: aiBubble, outputElement, metaElement} = createAiBubble();
    messages.appendChild(aiBubble);
    scrollToBottom();

    currentController = new AbortController();
    try {
        await streamChat(message, outputElement, metaElement, currentController.signal);
    } catch (error) {
        if (error.name !== 'AbortError') {
            throw error;
        }
    } finally {
        if (outputElement.childNodes.length === 0) {
            aiBubble.remove();
        }
        currentController = null;
        setSendButtonState('idle');
        setTextareaEnabled(true);
        textarea.focus();
    }
}

function init() {
    const textarea = document.getElementById('chat-textarea');
    const sendBtn = document.getElementById('chat-send');

    if (!textarea || !sendBtn) return;

    sendBtn.addEventListener('click', handleSubmit);

    textarea.addEventListener('keydown', (e) => {
        if (e.isComposing || e.keyCode === 229) return;
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSubmit();
        }
    });
}

document.addEventListener('DOMContentLoaded', init);
