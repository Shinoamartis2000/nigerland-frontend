// Live Chat Implementation
class LiveChat {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.init();
    }
    
    init() {
        this.createChatWidget();
        this.bindEvents();
        this.loadChatHistory();
    }
    
    createChatWidget() {
        const chatHTML = `
            <div class="live-chat-widget">
                <div class="chat-toggle" id="chatToggle">
                    <i class="fas fa-comments"></i>
                    <span class="chat-badge">1</span>
                </div>
                <div class="chat-window" id="chatWindow">
                    <div class="chat-header">
                        <h4>Nigerland Support</h4>
                        <button class="chat-close" id="chatClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="chat-body">
                        <div class="chat-messages" id="chatMessages">
                            <div class="message bot-message">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <p>Hello! I'm here to help you. How can I assist you today?</p>
                                    <span class="message-time">${this.getCurrentTime()}</span>
                                </div>
                            </div>
                        </div>
                        <div class="chat-input">
                            <input type="text" id="chatInput" placeholder="Type your message...">
                            <button id="chatSend">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatHTML);
        this.addChatStyles();
    }
    
    addChatStyles() {
        const styles = `
            <style>
                .live-chat-widget {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    z-index: 10000;
                }
                
                .chat-toggle {
                    width: 60px;
                    height: 60px;
                    background: linear-gradient(135deg, #1a5276, #2e86c1);
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    transition: all 0.3s ease;
                    position: relative;
                }
                
                .chat-toggle:hover {
                    transform: scale(1.1);
                }
                
                .chat-badge {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    background: #e74c3c;
                    color: white;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    font-size: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .chat-window {
                    position: absolute;
                    bottom: 70px;
                    right: 0;
                    width: 350px;
                    height: 500px;
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    display: none;
                    flex-direction: column;
                    overflow: hidden;
                }
                
                .chat-window.active {
                    display: flex;
                    animation: slideUp 0.3s ease;
                }
                
                @keyframes slideUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                .chat-header {
                    background: linear-gradient(135deg, #1a5276, #2e86c1);
                    color: white;
                    padding: 15px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .chat-header h4 {
                    margin: 0;
                    font-size: 16px;
                }
                
                .chat-close {
                    background: none;
                    border: none;
                    color: white;
                    cursor: pointer;
                    font-size: 16px;
                }
                
                .chat-body {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                }
                
                .chat-messages {
                    flex: 1;
                    padding: 20px;
                    overflow-y: auto;
                    background: #f8f9fa;
                }
                
                .message {
                    display: flex;
                    margin-bottom: 15px;
                    animation: fadeIn 0.3s ease;
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                .bot-message {
                    justify-content: flex-start;
                }
                
                .user-message {
                    justify-content: flex-end;
                }
                
                .message-avatar {
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    background: #1a5276;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 10px;
                    flex-shrink: 0;
                }
                
                .user-message .message-avatar {
                    background: #f39c12;
                    margin-right: 0;
                    margin-left: 10px;
                    order: 2;
                }
                
                .message-content {
                    max-width: 70%;
                    background: white;
                    padding: 12px 15px;
                    border-radius: 18px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                
                .user-message .message-content {
                    background: #1a5276;
                    color: white;
                }
                
                .message-content p {
                    margin: 0 0 5px 0;
                    font-size: 14px;
                    line-height: 1.4;
                }
                
                .message-time {
                    font-size: 11px;
                    opacity: 0.7;
                }
                
                .chat-input {
                    padding: 15px;
                    border-top: 1px solid #e9ecef;
                    display: flex;
                    gap: 10px;
                }
                
                .chat-input input {
                    flex: 1;
                    padding: 12px 15px;
                    border: 1px solid #ddd;
                    border-radius: 25px;
                    outline: none;
                    font-size: 14px;
                }
                
                .chat-input input:focus {
                    border-color: #1a5276;
                }
                
                .chat-input button {
                    width: 45px;
                    height: 45px;
                    background: #1a5276;
                    color: white;
                    border: none;
                    border-radius: 50%;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                
                .chat-input button:hover {
                    background: #2e86c1;
                    transform: scale(1.05);
                }
                
                @media (max-width: 480px) {
                    .chat-window {
                        width: 300px;
                        right: -20px;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }
    
    bindEvents() {
        document.getElementById('chatToggle').addEventListener('click', () => this.toggleChat());
        document.getElementById('chatClose').addEventListener('click', () => this.closeChat());
        document.getElementById('chatSend').addEventListener('click', () => this.sendMessage());
        document.getElementById('chatInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.classList.toggle('active', this.isOpen);
    }
    
    closeChat() {
        this.isOpen = false;
        document.getElementById('chatWindow').classList.remove('active');
    }
    
    sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        this.addMessage(message, 'user');
        input.value = '';
        
        // Simulate bot response
        setTimeout(() => {
            this.generateBotResponse(message);
        }, 1000);
    }
    
    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageHTML = `
            <div class="message ${sender}-message">
                <div class="message-avatar">
                    <i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>
                </div>
                <div class="message-content">
                    <p>${this.escapeHtml(text)}</p>
                    <span class="message-time">${this.getCurrentTime()}</span>
                </div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Store message
        this.messages.push({ text, sender, time: new Date() });
        this.saveChatHistory();
    }
    
    generateBotResponse(userMessage) {
        const responses = {
            greeting: [
                "Hello! How can I help you with Nigerland's services today?",
                "Hi there! What can I assist you with?",
                "Welcome! How may I help you?"
            ],
            training: [
                "We offer various training programs in management, marketing, business administration, and more. Which area are you interested in?",
                "Our training programs are designed for professional development. Would you like to know about specific courses?",
                "We have comprehensive training in multiple fields. What type of training are you looking for?"
            ],
            conference: [
                "The Tax Conference is happening on December 9 & 10, 2025. Would you like registration details?",
                "Our upcoming conference focuses on tax reforms. I can help you with registration information.",
                "The conference provides insights into new tax laws. Interested in attending?"
            ],
            morelife: [
                "MoreLife sessions help with personal challenges like stress, depression, and anxiety. Would you like to know more?",
                "Our MoreLife program offers academic solutions to personal challenges. What specific area are you concerned about?",
                "MoreLife provides support for various personal challenges. How can I assist you with this?"
            ],
            ebook: [
                "We have several educational ebooks available. Which book are you interested in?",
                "Our ebook collection includes titles on various topics. What would you like to read about?",
                "We offer digital books on Nigerian history, personal development, and more. Any specific interest?"
            ],
            default: [
                "I understand you're asking about: " + userMessage + ". Could you provide more details so I can assist you better?",
                "Thank you for your question. Let me connect you with someone who can help with: " + userMessage,
                "I'd be happy to help with that. Could you tell me more about what you're looking for?"
            ]
        };
        
        const lowerMessage = userMessage.toLowerCase();
        let responseType = 'default';
        
        if (/(hello|hi|hey|good morning|good afternoon)/i.test(lowerMessage)) {
            responseType = 'greeting';
        } else if (/(training|course|program|workshop)/i.test(lowerMessage)) {
            responseType = 'training';
        } else if (/(conference|tax|seminar|event)/i.test(lowerMessage)) {
            responseType = 'conference';
        } else if (/(morelife|session|challenge|stress|depression|anxiety)/i.test(lowerMessage)) {
            responseType = 'morelife';
        } else if (/(book|ebook|publication|read)/i.test(lowerMessage)) {
            responseType = 'ebook';
        }
        
        const possibleResponses = responses[responseType];
        const response = possibleResponses[Math.floor(Math.random() * possibleResponses.length)];
        
        this.addMessage(response, 'bot');
    }
    
    getCurrentTime() {
        return new Date().toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    saveChatHistory() {
        localStorage.setItem('nigerland_chat_history', JSON.stringify(this.messages));
    }
    
    loadChatHistory() {
        const saved = localStorage.getItem('nigerland_chat_history');
        if (saved) {
            this.messages = JSON.parse(saved);
            // Optionally reload messages to UI
        }
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LiveChat();
});