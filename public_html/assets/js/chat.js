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
                        appendMessage(message);
                        lastMessageId = Math.max(lastMessageId, message.message_id);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
    }

    function appendMessage(message) {
        // avoid duplicate messages
        if (document.getElementById(`message-${message.message_id}`)) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${message.is_mine ? 'mine' : 'theirs'}`;
        messageDiv.id = `message-${message.message_id}`;
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
            if (data.success && data.message_data) {
                messageInput.value = '';
                appendMessage(data.message_data);
                lastMessageId = Math.max(lastMessageId, data.message_data.message_id);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    });

    // Initial load and polling
    loadMessages();
    setInterval(loadMessages, 5000);
});
