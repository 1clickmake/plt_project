<?php

/**
 * AI Manager Plugin Hooks
 */

// Check if any AI key is configured
function has_any_ai_key() {
    static $has_key = null;
    if ($has_key !== null) return $has_key;
    
    $db = \App\Core\Database::getInstance();
    $config = $db->query("SELECT * FROM `ai_config` WHERE `id` = 1")->fetch();
    $has_key = (!empty($config['openai_key']) || !empty($config['claude_key']) || !empty($config['gemini_key']) || !empty($config['groq_key']));
    return $has_key;
}

// 1. Add AI Writing button to the page form
add_action('admin_page_form_content_tools', function() {
    if (!has_any_ai_key()) return;
    echo '
    <button type="button" id="btn-ai-write" class="btn btn-sm" style="background: linear-gradient(45deg, #6366f1, #a855f7); color: white; border: none; padding: 0.35rem 1rem; border-radius: 8px;">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Writing
    </button>';
});

// 2. Add the AI Prompt Modal
add_action('admin_page_form_after_form', function() {
    if (!has_any_ai_key()) return;
    echo '
    <div id="aiModal" class="admin-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
        <div class="modal-content-solid" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 500px; padding: 2.5rem; background: #1e1e2d; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <h3 class="mb-3 d-flex align-items-center">
                <i class="fa-solid fa-wand-magic-sparkles me-2 text-primary"></i> AI Content Helper
            </h3>
            <p class="text-muted-small mb-4">어떤 내용을 작성할까요? 구체적인 지시를 내리면 더 좋은 결과가 나옵니다.</p>
            
            <div class="form-group mb-4">
                <textarea id="ai_instruction" class="form-control" style="height: 150px; background: rgba(0,0,0,0.2); color: white; border: 1px solid rgba(255,255,255,0.1);" placeholder="예: 우리 회사의 연혁과 비전을 포함해서 아주 전문적이고 신뢰감 있는 톤으로 회사 소개 페이지를 작성해줘."></textarea>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn" onclick="$(\'#aiModal\').fadeOut(200)" style="background: rgba(255,255,255,0.1); color: white;">취소</button>
                <button type="button" id="btn-ai-submit" class="btn btn-primary">작성 시작</button>
            </div>
        </div>
    </div>';
});

// 3. Add Javascript handlers
add_action('admin_page_form_scripts', function() {
    if (!has_any_ai_key()) return;
    // Note: We use global window.pageEditor which was set in page_form.php
    ?>
    // AI Writing Handler - Open Modal
    $('#btn-ai-write').on('click', function() {
        const title = $('#page_title').val();
        if (!title) {
            alert('페이지 제목을 먼저 입력해주세요.');
            $('#page_title').focus();
            return;
        }
        
        // Set default instruction based on title
        $('#ai_instruction').val(`"${title}" 페이지 내용을 전문적인 톤으로 작성해줘.`);
        $('#aiModal').fadeIn(200);
    });

    // AI Writing Submit
    $('#btn-ai-submit').on('click', function() {
        const instruction = $('#ai_instruction').val();
        const title = $('#page_title').val();
        
        if (!instruction) {
            alert('지시사항을 입력해주세요.');
            return;
        }

        const prompt = `Page Title: ${title}\nInstruction: ${instruction}\n\nOutput only clean HTML structure (h2, p, ul, li). No markdown blocks.`;

        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fa-solid fa-spinner fa-spin me-1"></i> 작성 중...').prop('disabled', true);

        $.ajax({
            url: '/admin/ai/generate',
            method: 'POST',
            data: {
                prompt: prompt,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#aiModal').fadeOut(200);
                    if (confirm('AI가 내용을 생성했습니다. 에디터에 적용할까요?')) {
                        const mode = $('input[name="editor_mode"]:checked').val();
                        if (mode === 'visual') {
                            window.pageEditor.clipboard.dangerouslyPasteHTML(response.content);
                        } else {
                            $('#html_content').val(response.content);
                        }
                    }
                } else {
                    alert('AI 에러: ' + response.message);
                }
            },
            error: function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : '서버 오류가 발생했습니다.';
                alert('에러: ' + err);
            },
            complete: function() {
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
    <?php
});
