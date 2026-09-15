<x-erp-layout title="Asistente IA" subtitle="Consulta datos del ERP en lenguaje natural.">
    <div class="chat-container" style="max-width:700px;margin:0 auto;">
        <div id="chatMessages" class="chat-messages" style="height:500px;overflow-y:auto;padding:1rem;background:#1a1d29;border-radius:8px;margin-bottom:1rem;">
            <div class="chat-msg bot">
                <div class="chat-bubble bot-bubble">Hola! Soy el asistente de PatPot. Pregúntame sobre stock, pedidos, producciones o lo que necesites.</div>
            </div>
        </div>
        <form id="chatForm" class="chat-input-form" style="display:flex;gap:0.5rem;">
            <input type="text" id="chatInput" class="form-control" placeholder="Escribe tu pregunta..." autocomplete="off" style="flex:1;">
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>

    <style>
        .chat-msg { display:flex; margin-bottom:0.75rem; }
        .chat-msg.user { justify-content:flex-end; }
        .chat-msg.bot { justify-content:flex-start; }
        .chat-bubble {
            max-width:80%; padding:0.6rem 1rem; border-radius:12px;
            font-size:0.9rem; line-height:1.4; white-space:pre-wrap;
        }
        .user-bubble { background:#df6403; color:#fff; border-bottom-right-radius:4px; }
        .bot-bubble { background:#2c2e39; color:#e0e0e0; border-bottom-left-radius:4px; }
        .chat-typing { display:flex; gap:4px; padding:0.6rem 1rem; }
        .chat-typing span { width:8px; height:8px; background:#888; border-radius:50%; animation: blink 1.4s infinite both; }
        .chat-typing span:nth-child(2) { animation-delay:0.2s; }
        .chat-typing span:nth-child(3) { animation-delay:0.4s; }
        @keyframes blink { 0%,80%,100%{opacity:0.3} 40%{opacity:1} }
    </style>

    <script>
        var chatMessages = document.getElementById('chatMessages');
        var chatForm = document.getElementById('chatForm');
        var chatInput = document.getElementById('chatInput');

        function addMessage(text, type) {
            var div = document.createElement('div');
            div.className = 'chat-msg ' + type;
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble ' + (type === 'user' ? 'user-bubble' : 'bot-bubble');
            bubble.textContent = text;
            div.appendChild(bubble);
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            var div = document.createElement('div');
            div.className = 'chat-msg bot';
            div.id = 'typingIndicator';
            div.innerHTML = '<div class="chat-typing"><span></span><span></span><span></span></div>';
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTyping() {
            var el = document.getElementById('typingIndicator');
            if (el) el.remove();
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var msg = chatInput.value.trim();
            if (!msg) return;

            addMessage(msg, 'user');
            chatInput.value = '';
            chatInput.disabled = true;
            showTyping();

            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ message: msg })
            })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                removeTyping();
                addMessage(json.reply || 'No pude responder.', 'bot');
            })
            .catch(function() {
                removeTyping();
                addMessage('Error de conexión. Intenta de nuevo.', 'bot');
            })
            .finally(function() { chatInput.disabled = false; chatInput.focus(); });
        });

        chatInput.focus();
    </script>
</x-erp-layout>
