document.addEventListener('DOMContentLoaded', function () {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const claimId = document.getElementById('claimId').value;

    let lastMessageId = 0;

    function loadMessages() {
        fetch(`../chat/api/fetch.php?claim_id=${claimId}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        if (message.message_id > lastMessageId) {
                            appendMessage(message);
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
                if (data.success) {
                    messageInput.value = '';

                    // Manually add the sent message without waiting for polling
                    const now = new Date();
                    const timestamp = now.toISOString().slice(0, 19).replace('T', ' ');

                    appendMessage({
                        content: message,
                        created_at: timestamp,
                        is_mine: true,
                        message_id: lastMessageId + 1 // temporary increment
                    });

                    lastMessageId++; // prevent duplicate from server response
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
    });

    // Initial load and polling
    loadMessages();
    setInterval(loadMessages, 5000);
});
