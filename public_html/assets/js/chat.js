document.addEventListener('DOMContentLoaded', function () {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const claimId = document.getElementById('claimId').value;

    let lastMessageId = 0;
    const shownMessageIds = new Set(); // prevent duplicate messages

    function loadMessages() {
        fetch(`../chat/api/fetch.php?claim_id=${claimId}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                const messages = data.data || data.messages || [];
                if (data.success && messages.length > 0) {
                    messages.forEach(message => {
                        if (!shownMessageIds.has(message.message_id)) {
                            appendMessage(message);
                            shownMessageIds.add(message.message_id);
                            lastMessageId = Math.max(lastMessageId, message.message_id);
                        }
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
    }

    function appendMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${message.is_mine ? 'mine' : 'theirs'}`;
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-text">${escapeHtml(message.content)}</div>
                <div class="message-meta">
                    <span class="message-time">${message.created_at}</span>
                </div>
            </div>
        `;
        chatMessages.appendChild(messageDiv);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        fetch('../chat/api/send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                claim_id: claimId,
                content: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messageData) {
                messageInput.value = '';

                // use shownMessageIds to prevent duplicates
                if (!shownMessageIds.has(data.messageData.message_id)) {
                    appendMessage(data.messageData);
                    shownMessageIds.add(data.messageData.message_id);
                    lastMessageId = Math.max(lastMessageId, data.messageData.message_id);
                }

                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    });

    // start loading messages
    loadMessages();
    setInterval(loadMessages, 5000);
});
