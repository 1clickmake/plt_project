<?php

use App\Core\View;

/**
 * Chatbot Plugin Hooks
 */

// 1. Inject Chatbot Settings into AI Manager Config Page
// 1. Inject Chatbot Settings into AI Manager Config Page
add_action('ai_config_form_bottom', function($config) {
?>
    <div class="col-md-12">
        <div class="p-4 rounded-4 mt-2" style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.1);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="use_chatbot" value="1" id="useChatbot" <?= ($config['use_chatbot'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label ms-2" for="useChatbot">
                            <strong class="d-block mb-1"><i class="fa-solid fa-robot me-1 text-primary"></i> 챗봇(Chatbot) 서비스 활성화</strong>
                            <span class="text-muted-small text-info">홈페이지 우측 하단에 AI 상담 챗봇 위젯을 노출합니다.</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="flex-fill">
                            <label class="form-label text-muted-small mb-1">비회원 1일 질문한도</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="chatbot_limit_guest" class="form-control" value="<?= $config['chatbot_limit_guest'] ?? 5 ?>" min="0">
                                <span class="input-group-text">회</span>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <label class="form-label text-muted-small mb-1">회원 1일 질문한도</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="chatbot_limit_member" class="form-control" value="<?= $config['chatbot_limit_member'] ?? 20 ?>" min="0">
                                <span class="input-group-text">회</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted-small mt-1 text-end text-warning" style="font-size: 0.7rem; opacity: 0.7;">* 0 입력 시 무제한</div>
                </div>
            </div>
        </div>
    </div>
<?php
});

// 2. Handle Saving Chatbot Settings
add_action('ai_config_update', function($postData) {
    $db = \App\Core\Database::getInstance();
    $useChatbot = isset($postData['use_chatbot']) ? 1 : 0;
    $limitGuest = intval($postData['chatbot_limit_guest'] ?? 5);
    $limitMember = intval($postData['chatbot_limit_member'] ?? 20);
    
    $db->prepare("UPDATE `ai_config` SET `use_chatbot` = ?, `chatbot_limit_guest` = ?, `chatbot_limit_member` = ? WHERE `id` = 1")
       ->execute([$useChatbot, $limitGuest, $limitMember]);
});

// 3. Inject Chatbot UI into the public footer (only if enabled)
add_action('public_footer', function() {
    $db = \App\Core\Database::getInstance();
    $config = $db->query("SELECT use_chatbot FROM `ai_config` WHERE `id` = 1")->fetch();
    
    if (empty($config['use_chatbot'])) return;

    $viewPath = __DIR__ . '/Views/chatbot_ui.php';
    if (file_exists($viewPath)) {
        include $viewPath;
    }
});
