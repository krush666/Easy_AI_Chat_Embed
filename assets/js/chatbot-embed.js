document.addEventListener('DOMContentLoaded', function() {
    var chatbotContainer = document.getElementById('easy-ai-chat');
    if (chatbotContainer) {
        // Replace the following line with the actual script to embed the AI chatbot
        chatbotContainer.innerHTML = '<script src="' + easyAIChatSettings.plugin_url + '/assets/js/aiscript.html"><\/script>';
    }

    // Get the API key from settings
    var apiKey = easyAIChatSettings.api_key;

    // Get the terms checkbox enabled setting
    var termsCheckboxEnabled = easyAIChatSettings.terms_checkbox_enabled;

    // Get the terms text from settings
    var termsText = easyAIChatSettings.terms_text;

    // Apply button color from settings
    var sendButton = document.getElementById('sendButton');
    if (sendButton) {
        sendButton.style.backgroundColor = easyAIChatSettings.button_color;
    }

    // Function to send message
    async function sendMessage() {
        if (termsCheckboxEnabled && !termsCheckbox.checked) return;

        const userText = userInput.value.trim();
        if (!userText) return;

        // Add user message to chat
        addMessage(userText, true);
        userInput.value = '';

        // Add loading message
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'message ai-message loading';
        loadingDiv.textContent = 'Thinking...';
        chatBox.appendChild(loadingDiv);

        try {
            // Prepare headers
            const headers = {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiKey}`
            };

            const response = await fetch('/wp-json/gemini-chat/v1/query', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    message: userText
                })
            });

            const data = await response.json();

            // Remove loading message
            chatBox.removeChild(loadingDiv);

            // Add AI response to chat
            if (data.candidates && data.candidates[0].content) {
                addMessage(data.candidates[0].content.parts[0].text, false);
            } else {
                addMessage('Sorry, I could not generate a response.', false);
            }
        } catch (error) {
            // Remove loading message
            chatBox.removeChild(loadingDiv);
            addMessage('Error: Could not connect to the AI service.', false);
            console.error('Error:', error);
        }
    }

    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && (!termsCheckboxEnabled || termsCheckbox.checked)) {
            sendMessage();
        }
    });

    // Apply terms checkbox settings
    if (!termsCheckboxEnabled) {
        termsContainer.style.display = 'none';
        disabledMessage.style.display = 'none';
        inputContainer.classList.add('enabled');
        userInput.disabled = false;
        sendButton.disabled = false;
    } else {
        termsCheckboxLabel.textContent = termsText;
    }
});
