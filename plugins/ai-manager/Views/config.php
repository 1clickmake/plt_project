<?php $title = 'AI Configuration'; include_admin_header($title); ?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>AI Settings</h1>
            <p class="text-muted-small">Manage your AI API keys and default models here.</p>
            <div class="mt-4 p-3 border-0 rounded-4" style="background: rgba(99, 102, 241, 0.05); border: 1px border rgba(99, 102, 241, 0.1) !important;">
                <h6 class="text-primary-weight mb-2"><i class="fa-solid fa-circle-info me-2"></i> AI 연동 가이드</h6>
                <ul class="text-muted-small mb-0" style="list-style: none; padding-left: 0;">
                    <li class="mb-1"><strong>OpenAI:</strong> <a href="https://platform.openai.com/api-keys" target="_blank" class="text-decoration-none">OpenAI 플랫폼</a> (유료)</li>
                    <li class="mb-1"><strong>Claude:</strong> <a href="https://console.anthropic.com/" target="_blank" class="text-decoration-none">Anthropic 콘솔</a> (유료)</li>
                    <li class="mb-1"><strong>Gemini (무료/강추):</strong> <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-decoration-none">Google AI Studio</a>에서 **무료**로 키를 발급받으세요.</li>
                    <li class="mb-1"><strong>Groq:</strong> <a href="https://console.groq.com/keys" target="_blank" class="text-decoration-none">Groq 콘솔</a>에서 매우 빠른 속도의 AI 키를 무료로 발급받을 수 있습니다.</li>
                    <li><strong>알림:</strong> 사용하실 서비스의 키 하나만 입력하고 하단의 '기본 모델'을 설정하시면 즉시 사용 가능합니다.</li>
                </ul>
            </div>
        </div>
        <div class="badge bg-primary">v1.0.0</div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success bg-success text-white border-0 mb-4" style="--bs-bg-opacity: .4;">
        <i class="fa-solid fa-check-circle me-2"></i> Settings updated successfully!
    </div>
    <?php endif; ?>

    <form action="/admin/ai/config" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="form-group mb-4">
                    <label class="form-label d-flex align-items-center">
                        <i class="fa-solid fa-gear me-2 text-warning"></i> Default Model
                    </label>
                    <select name="default_model" class="form-select">
                        <?php $currentModel = $config['default_model'] ?? 'gemini-2.5-flash'; ?>
                        <option value="gemini-2.5-flash" <?= $currentModel == 'gemini-2.5-flash' ? 'selected' : '' ?>>Gemini 2.5 Flash (Google/무료/최신)</option>
                        <option value="llama-3.3-70b-versatile" <?= $currentModel == 'llama-3.3-70b-versatile' ? 'selected' : '' ?>>Llama 3.3 70B (Groq/무료/성능)</option>
                        <option value="llama-3.1-8b-instant" <?= $currentModel == 'llama-3.1-8b-instant' ? 'selected' : '' ?>>Llama 3.1 8B (Groq/무료/초고속)</option>
                        <option value="gpt-4o" <?= $currentModel == 'gpt-4o' ? 'selected' : '' ?>>GPT-4o (OpenAI/유료)</option>
                        <option value="gpt-4o-mini" <?= $currentModel == 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o mini (OpenAI/유료)</option>
                        <option value="claude-3-5-sonnet-20240620" <?= $currentModel == 'claude-3-5-sonnet-20240620' ? 'selected' : '' ?>>Claude 3.5 Sonnet (Anthropic/유료)</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label d-flex align-items-center">
                        <i class="fa-solid fa-bolt me-2 text-primary"></i> OpenAI API Key
                    </label>
                    <input type="password" name="openai_key" class="form-control" value="<?= $config['openai_key'] ?? '' ?>" placeholder="sk-...">
                    <div class="form-text">GPT-4o, GPT-3.5 등</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label d-flex align-items-center">
                        <i class="fa-solid fa-brain me-2 text-info"></i> Anthropic (Claude) API Key
                    </label>
                    <input type="password" name="claude_key" class="form-control" value="<?= $config['claude_key'] ?? '' ?>" placeholder="sk-ant-...">
                    <div class="form-text">Claude 3.5 Sonnet 등</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label d-flex align-items-center">
                        <i class="fa-solid fa-star me-2 text-warning"></i> Google Gemini API Key (무료)
                    </label>
                    <input type="password" name="gemini_key" class="form-control" value="<?= $config['gemini_key'] ?? '' ?>" placeholder="AIza...">
                    <div class="form-text">Gemini 2.5 Flash</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label d-flex align-items-center">
                        <i class="fa-solid fa-rocket me-2 text-success"></i> Groq API Key (무료/고속)
                    </label>
                    <input type="password" name="groq_key" class="form-control" value="<?= $config['groq_key'] ?? '' ?>" placeholder="gsk_...">
                    <div class="form-text">Llama 3, Mixtral 등</div>
                </div>
            </div>

            
        </div>

        <!-- Dynamic settings from other plugins -->
        <?php do_action('ai_config_form_bottom', $config); ?>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="fa-solid fa-save me-2"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<script>
    $('#link-ai-config').addClass('active');
</script>

<?php include_admin_footer(); ?>
