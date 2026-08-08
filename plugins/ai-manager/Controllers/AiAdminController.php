<?php

namespace Plugins\aimanager\Controllers;

use App\Core\Database;
use App\Core\View;
use Plugins\aimanager\Services\AiService;

class AiAdminController
{
    public function index()
    {
        $db = Database::getInstance();
        $config = $db->query("SELECT * FROM `ai_config` WHERE `id` = 1")->fetch();
        
        View::render('plugins/ai-manager/Views/config', [
            'title' => 'AI Settings',
            'config' => $config
        ]);
    }

    public function update()
    {
        $db = Database::getInstance();
        $openai_key = $_POST['openai_key'] ?? '';
        $claude_key = $_POST['claude_key'] ?? '';
        $gemini_key = $_POST['gemini_key'] ?? '';
        $groq_key   = $_POST['groq_key'] ?? '';
        $default_model = $_POST['default_model'] ?? 'gpt-4o';

        $stmt = $db->prepare("UPDATE `ai_config` SET openai_key = ?, claude_key = ?, gemini_key = ?, groq_key = ?, default_model = ? WHERE id = 1");
        $stmt->execute([$openai_key, $claude_key, $gemini_key, $groq_key, $default_model]);

        // Trigger hooks for other plugins to save their settings
        do_action('ai_config_update', $_POST);

        header('Location: /admin/ai/config?success=1');
        exit;
    }

    public function generate()
    {
        header('Content-Type: application/json');
        
        try {
            $prompt = $_POST['prompt'] ?? '';
            if (!$prompt) {
                throw new \Exception("Prompt is required.");
            }

            $aiService = new AiService();
            $content = $aiService->generate($prompt);

            echo json_encode(['success' => true, 'content' => $content]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

