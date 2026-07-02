/**
 * AI Assistant - NotesMaster
 * Assistant intelligent pour aider les utilisateurs à naviguer dans l'application
 */

const AIAssistant = (function () {
    "use strict";

    let isOpen = false;
    let chatContainer = null;
    let messagesContainer = null;
    let inputField = null;
    let sendButton = null;
    let toggleButton = null;
    let isTyping = false;

    const CONFIG = {
        apiUrl: '/api/ai-assistant',
        maxMessages: 50,
        typingDelay: 1000
    };

    /**
     * Initialise l'assistant IA
     */
    function init() {
        createToggle();
        createChatInterface();
        bindEvents();
        loadConversationHistory();
    }

    /**
     * Crée le bouton de toggle
     */
    function createToggle() {
        toggleButton = document.createElement('button');
        toggleButton.id = 'ai-assistant-toggle';
        toggleButton.innerHTML = '<i class="bi bi-robot"></i>';
        toggleButton.setAttribute('aria-label', 'Assistant IA');
        toggleButton.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            z-index: 9999;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        toggleButton.addEventListener('mouseenter', () => {
            toggleButton.style.transform = 'scale(1.1)';
            toggleButton.style.boxShadow = '0 6px 25px rgba(102, 126, 234, 0.6)';
        });

        toggleButton.addEventListener('mouseleave', () => {
            toggleButton.style.transform = 'scale(1)';
            toggleButton.style.boxShadow = '0 4px 20px rgba(102, 126, 234, 0.4)';
        });

        toggleButton.addEventListener('click', toggleChat);
        document.body.appendChild(toggleButton);
    }

    /**
     * Crée l'interface de chat
     */
    function createChatInterface() {
        chatContainer = document.createElement('div');
        chatContainer.id = 'ai-assistant-chat';
        chatContainer.style.cssText = `
            position: fixed;
            bottom: 150px;
            right: 20px;
            width: 400px;
            max-width: calc(100vw - 40px);
            height: 500px;
            max-height: calc(100vh - 200px);
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            display: none;
            flex-direction: column;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;

        chatContainer.innerHTML = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="bi bi-robot" style="font-size: 24px;"></i>
                    <div>
                        <div style="font-weight: bold; font-size: 16px;">Assistant NotesMaster</div>
                        <div style="font-size: 12px; opacity: 0.9;">Je suis là pour vous aider</div>
                    </div>
                </div>
                <button id="ai-assistant-close" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background 0.2s;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div id="ai-assistant-messages" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; background: #f8fafc;">
                <!-- Messages will be inserted here -->
            </div>
            <div style="padding: 16px; background: white; border-top: 1px solid #e2e8f0;">
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="ai-assistant-input" placeholder="Posez votre question..." style="flex: 1; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 14px; outline: none; transition: border-color 0.2s;">
                    <button id="ai-assistant-send" style="padding: 12px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: transform 0.2s;">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(chatContainer);

        messagesContainer = document.getElementById('ai-assistant-messages');
        inputField = document.getElementById('ai-assistant-input');
        sendButton = document.getElementById('ai-assistant-send');

        // Style pour les messages
        const style = document.createElement('style');
        style.textContent = `
            .ai-message {
                max-width: 85%;
                padding: 12px 16px;
                border-radius: 16px;
                font-size: 14px;
                line-height: 1.5;
                animation: messageSlide 0.3s ease;
            }
            @keyframes messageSlide {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .ai-message-user {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                align-self: flex-end;
                margin-left: auto;
            }
            .ai-message-assistant {
                background: white;
                color: #1e293b;
                align-self: flex-start;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }
            .ai-message-assistant a {
                color: #667eea;
                text-decoration: none;
                font-weight: 500;
            }
            .ai-message-assistant a:hover {
                text-decoration: underline;
            }
            .ai-typing {
                display: flex;
                gap: 4px;
                padding: 12px 16px;
                background: white;
                border-radius: 16px;
                align-self: flex-start;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }
            .ai-typing-dot {
                width: 8px;
                height: 8px;
                background: #667eea;
                border-radius: 50%;
                animation: typingBounce 1.4s infinite ease-in-out;
            }
            .ai-typing-dot:nth-child(1) { animation-delay: 0s; }
            .ai-typing-dot:nth-child(2) { animation-delay: 0.2s; }
            .ai-typing-dot:nth-child(3) { animation-delay: 0.4s; }
            @keyframes typingBounce {
                0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
                40% { transform: scale(1); opacity: 1; }
            }
            .ai-research-spinner {
                width: 32px;
                height: 32px;
                position: relative;
            }
            .spinner-ring {
                width: 100%;
                height: 100%;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .ai-action-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 12px;
                background: #f1f5f9;
                border-radius: 8px;
                margin-top: 8px;
                text-decoration: none;
                color: #1e293b;
                font-size: 13px;
                font-weight: 500;
                transition: all 0.2s;
            }
            .ai-action-link:hover {
                background: #e2e8f0;
                transform: translateY(-1px);
            }
            .ai-action-link i {
                color: #667eea;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Lie les événements
     */
    function bindEvents() {
        document.getElementById('ai-assistant-close').addEventListener('click', toggleChat);
        sendButton.addEventListener('click', sendMessage);
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
        inputField.addEventListener('focus', () => {
            inputField.style.borderColor = '#667eea';
        });
        inputField.addEventListener('blur', () => {
            inputField.style.borderColor = '#e2e8f0';
        });
    }

    /**
     * Ouvre/ferme le chat
     */
    function toggleChat() {
        isOpen = !isOpen;
        chatContainer.style.display = isOpen ? 'flex' : 'none';
        if (isOpen) {
            inputField.focus();
        }
    }

    /**
     * Envoie un message avec streaming des étapes
     */
    async function sendMessage() {
        const message = inputField.value.trim();
        if (!message || isTyping) return;

        // Ajouter le message utilisateur
        addMessage(message, 'user');
        inputField.value = '';
        
        // Afficher l'indicateur de recherche avec étapes
        showResearchIndicator();
        isTyping = true;

        try {
            const response = await fetch(CONFIG.apiUrl + '?stream=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message })
            });

            if (!response.ok) {
                throw new Error('Erreur de connexion');
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let finalResponse = null;
            let finalActions = [];

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value);
                const lines = chunk.split('\n');

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const data = JSON.parse(line.substring(6));
                        
                        if (data.type === 'step') {
                            updateResearchStep(data.step, data.detail);
                        } else if (data.type === 'complete') {
                            finalResponse = data.response;
                            finalActions = data.actions || [];
                        }
                    }
                }
            }

            hideResearchIndicator();
            isTyping = false;

            if (finalResponse) {
                addMessage(finalResponse, 'assistant', finalActions);
                saveConversationHistory(message, finalResponse);
            } else {
                addMessage("Désolé, je n'ai pas pu traiter votre demande. Veuillez réessayer.", 'assistant');
            }
        } catch (error) {
            hideResearchIndicator();
            isTyping = false;
            addMessage("Une erreur s'est produite. Veuillez contacter le support technique.", 'assistant');
        }
    }

    /**
     * Ajoute un message au chat
     */
    function addMessage(text, type, actions = []) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-message ai-message-${type}`;
        
        let content = text;
        
        // Ajouter les actions si présentes
        if (actions && actions.length > 0) {
            content += '<div style="margin-top: 12px;">';
            actions.forEach(action => {
                content += `
                    <a href="${action.url}" class="ai-action-link">
                        <i class="bi ${action.icon || 'bi-arrow-right'}"></i>
                        ${action.label}
                    </a>
                `;
            });
            content += '</div>';
        }
        
        messageDiv.innerHTML = content;
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Limiter le nombre de messages
        while (messagesContainer.children.length > CONFIG.maxMessages) {
            messagesContainer.removeChild(messagesContainer.firstChild);
        }
    }

    /**
     * Affiche l'indicateur de recherche avec étapes
     */
    function showResearchIndicator() {
        const researchDiv = document.createElement('div');
        researchDiv.id = 'ai-research-indicator';
        researchDiv.className = 'ai-research';
        researchDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                <div class="ai-research-spinner">
                    <div class="spinner-ring"></div>
                </div>
                <div style="flex: 1;">
                    <div id="ai-research-step" style="font-weight: 600; color: #1e293b; font-size: 14px;">Analyse de votre question...</div>
                    <div id="ai-research-detail" style="color: #64748b; font-size: 12px; margin-top: 4px;">Préparation de la recherche</div>
                </div>
            </div>
        `;
        messagesContainer.appendChild(researchDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * Met à jour l'étape de recherche
     */
    function updateResearchStep(step, detail) {
        const stepElement = document.getElementById('ai-research-step');
        const detailElement = document.getElementById('ai-research-detail');
        if (stepElement) stepElement.textContent = step;
        if (detailElement) detailElement.textContent = detail;
    }

    /**
     * Cache l'indicateur de recherche
     */
    function hideResearchIndicator() {
        const indicator = document.getElementById('ai-research-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    /**
     * Sauvegarde l'historique de conversation
     */
    function saveConversationHistory(userMessage, assistantResponse) {
        const history = JSON.parse(localStorage.getItem('ai_assistant_history') || '[]');
        history.push({
            userMessage,
            assistantResponse,
            timestamp: new Date().toISOString()
        });
        
        // Garder seulement les 100 derniers messages
        if (history.length > 100) {
            history.splice(0, history.length - 100);
        }
        
        localStorage.setItem('ai_assistant_history', JSON.stringify(history));
    }

    /**
     * Charge l'historique de conversation
     */
    function loadConversationHistory() {
        const history = JSON.parse(localStorage.getItem('ai_assistant_history') || '[]');
        
        // Afficher le message de bienvenue
        addMessage("Bonjour ! Je suis votre assistant NotesMaster. Je peux vous aider à naviguer dans l'application, trouver des informations, ou répondre à vos questions. Comment puis-je vous aider ?", 'assistant');
    }

    // API publique
    return {
        init,
        toggle: toggleChat,
        isOpen: () => isOpen
    };
})();

// Auto-initialisation
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si l'utilisateur est connecté
    if (typeof window.userLoggedIn !== 'undefined' && window.userLoggedIn) {
        AIAssistant.init();
    }
});

// Exposer globalement
window.AIAssistant = AIAssistant;
