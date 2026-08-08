<!-- Chatbot Widget -->
<div id="ai-chatbot-widget" class="chatbot-closed">
    <div id="chatbot-header">
        <div class="d-flex align-items-center">
            <div class="ai-avatar me-2">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div>
                <div class="chatbot-name">AI Bot</div>
                <div class="chatbot-status">Online</div>
            </div>
        </div>
        <button id="close-chatbot"><i class="fa-solid fa-times"></i></button>
    </div>
    <div id="chatbot-messages">
        <div class="message bot-message">
            👋 안녕하세요<?= isset($_SESSION['user']['username']) ? ' ' . htmlspecialchars($_SESSION['user']['username']) . '님' : '' ?>! 무엇을 도와드릴까요?
        </div>
    </div>
    <div id="chatbot-input">
        <input type="text" id="chatbot-text" placeholder="메시지를 입력하세요..." autocomplete="off">
        <button id="chatbot-send"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<button id="chatbot-launcher">
    <i class="fa-solid fa-comment-dots"></i>
</button>

<style>
    /* Launcher */
    #chatbot-launcher {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        color: white;
        border: none;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
        cursor: pointer;
        z-index: 9999;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s;
    }
    #chatbot-launcher:hover {
        transform: scale(1.1);
    }

    /* Widget */
    #ai-chatbot-widget {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 350px;
        height: 500px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        z-index: 10000;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        transform-origin: bottom right;
    }
    #ai-chatbot-widget.chatbot-closed {
        opacity: 0;
        transform: scale(0.5);
        pointer-events: none;
    }

    #chatbot-header {
        background: linear-gradient(135deg, #6366f1, #a855f7);
        padding: 15px 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ai-avatar {
        width: 35px;
        height: 35px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .chatbot-name { font-weight: bold; font-size: 15px; }
    .chatbot-status { font-size: 11px; opacity: 0.8; }
    #close-chatbot { background: none; border: none; color: white; cursor: pointer; }

    #chatbot-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .message {
        padding: 10px 15px;
        border-radius: 15px;
        max-width: 80%;
        font-size: 14px;
        line-height: 1.5;
    }
    .bot-message {
        background: white;
        color: #1e293b;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .user-message {
        background: #6366f1;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }

    #chatbot-input {
        padding: 15px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
    }
    #chatbot-text {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 8px 15px;
        font-size: 14px;
        outline: none;
    }
    #chatbot-text:focus { border-color: #6366f1; }
    #chatbot-send {
        background: #6366f1;
        color: white;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
    }

    /* Typing indicator */
    .typing {
        font-style: italic;
        color: #64748b;
        font-size: 12px;
    }
</style>

<script>
$(document).ready(function() {
    const $widget = $('#ai-chatbot-widget');
    const $launcher = $('#chatbot-launcher');
    const $messages = $('#chatbot-messages');
    const $input = $('#chatbot-text');

    $launcher.on('click', () => $widget.removeClass('chatbot-closed'));
    $('#close-chatbot').on('click', () => $widget.addClass('chatbot-closed'));

    function addMessage(text, role) {
        const cls = role === 'bot' ? 'bot-message' : 'user-message';
        // Simple HTML stripping/handling for security if needed, but here we trust AI output (or use text for safety)
        const $msg = $('<div class="message"></div>').addClass(cls).text(text);
        if (role === 'bot') {
            // AI might send HTML if we asked for it, but for chat text is usually better
            // Let's use html() for bot just in case we want formatting
            $msg.html(text); 
        }
        $messages.append($msg);
        $messages.scrollTop($messages[0].scrollHeight);
    }

    function sendMessage() {
        const msg = $input.val().trim();
        if (!msg) return;

        addMessage(msg, 'user');
        $input.val('');

        const $typing = $('<div class="message bot-message typing">봇이 답장 중...</div>');
        $messages.append($typing);
        $messages.scrollTop($messages[0].scrollHeight);

        $.ajax({
            url: '/api/chatbot/ask',
            method: 'POST',
            data: { message: msg },
            success: function(response) {
                $typing.remove();
                if (response.success) {
                    addMessage(response.answer, 'bot');
                } else {
                    addMessage('죄송합니다. 오류가 발생했습니다: ' + response.message, 'bot');
                }
            },
            error: function(xhr) {
                $typing.remove();
                const errMsg = xhr.responseJSON ? xhr.responseJSON.message : '서버와 통신하는 중 에러가 발생했습니다.';
                addMessage('오류: ' + errMsg, 'bot');
            }
        });
    }

    $('#chatbot-send').on('click', sendMessage);
    $input.on('keypress', (e) => {
        if (e.which === 13) sendMessage();
    });
});
</script>
