document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.getElementById('typingIndicator');
    const clearChatBtn = document.getElementById('clearChatBtn');

    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();

        if (!message) {
            alert('Please enter a message');
            return;
        }

        sendMessage(message);
    });

    // Handle clear chat
    clearChatBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the chat history?')) {
            fetch('api/clear-chat.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error clearing chat: ' + data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });

    function sendMessage(message) {
        // Disable input
        sendBtn.disabled = true;
        messageInput.disabled = true;

        // Add user message to chat
        addMessageToChat('user', message);
        messageInput.value = '';

        // Add typing indicator message
        const typingMsgId = 'typing-' + Date.now();
        const msgDiv = document.createElement('div');
        msgDiv.id = typingMsgId;
        msgDiv.className = 'message message-assistant mb-3';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content typing-indicator';
        contentDiv.innerHTML = '<span></span><span></span><span></span>';

        msgDiv.appendChild(contentDiv);
        chatMessages.appendChild(msgDiv);
        scrollToBottom();

        // Send to API
        const formData = new FormData();
        formData.append('message', message);

        fetch('api/chat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            const typingMsg = document.getElementById(typingMsgId);
            if (typingMsg) {
                typingMsg.remove();
            }

            sendBtn.disabled = false;
            messageInput.disabled = false;
            messageInput.focus();

            if (data.success) {
                addMessageToChat('assistant', data.message);
            } else {
                addMessageToChat('assistant', '❌ Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            // Remove typing indicator
            const typingMsg = document.getElementById(typingMsgId);
            if (typingMsg) {
                typingMsg.remove();
            }

            sendBtn.disabled = false;
            messageInput.disabled = false;
            console.error('Error:', error);
            addMessageToChat('assistant', '❌ Error sending message. Please try again.');
        });
    }

    function addMessageToChat(role, content) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message message-${role} mb-3`;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerHTML = formatMessage(content);

        const timeDiv = document.createElement('small');
        timeDiv.className = 'text-muted d-block mt-1';
        timeDiv.textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });

        msgDiv.appendChild(contentDiv);
        msgDiv.appendChild(timeDiv);

        // Remove empty state message if it exists
        const emptyState = chatMessages.querySelector('.text-center.text-muted');
        if (emptyState) {
            emptyState.remove();
        }

        chatMessages.appendChild(msgDiv);
        scrollToBottom();
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function formatMessage(content) {
        // Escape HTML but preserve newlines and basic formatting
        let formatted = content
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        // Convert markdown tables to HTML first (before converting newlines)
        formatted = convertMarkdownTablesToHTML(formatted);

        // Preserve newlines
        formatted = formatted.replace(/\n/g, '<br>');

        // Convert headers (##, ###, etc.)
        formatted = formatted.replace(/<br>### (.*?)<br>/g, '<br><h5>$1</h5>');
        formatted = formatted.replace(/<br>## (.*?)<br>/g, '<br><h4>$1</h4>');
        formatted = formatted.replace(/<br># (.*?)<br>/g, '<br><h3>$1</h3>');

        // Convert bold
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/__(.*?)__/g, '<strong>$1</strong>');

        // Convert italics
        formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
        formatted = formatted.replace(/_(.*?)_/g, '<em>$1</em>');

        // Convert code blocks
        formatted = formatted.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

        // Convert inline code
        formatted = formatted.replace(/`(.*?)`/g, '<code>$1</code>');

        // Convert URLs to links
        formatted = formatted.replace(
            /(https?:\/\/[^\s<]+)/g,
            '<a href="$1" target="_blank">$1</a>'
        );

        return formatted;
    }

    function convertMarkdownTablesToHTML(content) {
        // Match markdown tables (| header | header |)
        const tableRegex = /\|(.+)\|\n\|[-:\s|]+\|\n((?:\|.+\|\n?)*)/g;

        return content.replace(tableRegex, (match) => {
            const lines = match.trim().split('\n');
            if (lines.length < 2) return match;

            // Parse header
            const headerCells = lines[0]
                .split('|')
                .map(cell => cell.trim())
                .filter(cell => cell.length > 0);

            // Parse body rows
            const bodyRows = [];
            for (let i = 2; i < lines.length; i++) {
                const cells = lines[i]
                    .split('|')
                    .map(cell => cell.trim())
                    .filter(cell => cell.length > 0);
                if (cells.length > 0) {
                    bodyRows.push(cells);
                }
            }

            if (headerCells.length === 0) return match;

            // Build HTML table
            let html = '<table class="chat-table"><thead><tr>';
            headerCells.forEach(cell => {
                html += `<th>${cell}</th>`;
            });
            html += '</tr></thead><tbody>';

            bodyRows.forEach(row => {
                html += '<tr>';
                // Ensure we have the same number of cells as headers
                for (let i = 0; i < headerCells.length; i++) {
                    const cellContent = row[i] || '';
                    html += `<td>${cellContent}</td>`;
                }
                html += '</tr>';
            });

            html += '</tbody></table>';
            return html;
        });
    }

    // Auto-scroll on load
    scrollToBottom();

    // Focus input on load
    messageInput.focus();
});
