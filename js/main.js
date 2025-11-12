// Main initialization file
document.addEventListener('DOMContentLoaded', function() {
    // Hide loading screen after page loads
    setTimeout(function() {
        document.getElementById('loading').classList.add('hidden');
    }, 1000);

    // Initialize all modules
    initNavigation();
    initForms();
    initAnimations();
    initBlog();
    initLiveChat();
});

// Live Chat functionality
function initLiveChat() {
    const chatToggle = document.getElementById('chatToggle');
    const chatWindow = document.getElementById('chatWindow');
    const chatClose = document.getElementById('chatClose');
    const chatSend = document.getElementById('chatSend');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');

    if (!chatToggle) return;

    chatToggle.addEventListener('click', () => {
        chatWindow.classList.toggle('active');
    });

    chatClose.addEventListener('click', () => {
        chatWindow.classList.remove('active');
    });

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.innerHTML = `<p>${text}</p>`;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function handleUserMessage() {
        const message = chatInput.value.trim();
        if (message) {
            addMessage(message, true);
            chatInput.value = '';

            // Simulate bot response
            setTimeout(() => {
                const responses = [
                    "Thank you for your message. How can we assist you today?",
                    "We'll get back to you shortly. Is there anything specific you'd like to know?",
                    "Our team is available to help. What services are you interested in?",
                    "Thank you for reaching out. One of our consultants will contact you soon."
                ];
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                addMessage(randomResponse);
            }, 1000);
        }
    }

    chatSend.addEventListener('click', handleUserMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleUserMessage();
        }
    });
}